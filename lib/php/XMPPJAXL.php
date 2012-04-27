<?php
namespace PhenLib;

//TODO: Change to batch processing of commands with one connection
abstract class XMPPJAXL
{
	protected $jaxl;
	private $xeps;
	protected $errors;
	private $stopped;

	//constructor
	public function __construct( $xeps = array() )
	{
		//init instance, init errors array
		$this->jaxl = NULL;
		$this->errors = array();
		$this->stopped = TRUE;

		if( ! is_array( $xeps ) )
			throw new \Exception( "Constructor expects array of xeps" );
		$this->xeps = $xeps;
	}

	//setup xmpp management connection
	final protected function init( $user = NULL, $pass = NULL )
	{
		//stop if not stopped
		$this->stop();

		//init jaxl
		$this->jaxl = new \JAXL( array(
			'user'    => ( $user !== NULL ) ? $user : $GLOBALS['xmppUser'],
			'pass'    => ( $pass !== NULL ) ? $pass : $GLOBALS['xmppPass'],
			'domain'  => $GLOBALS['xmppDomain'],
			'logPath' => $GLOBALS['xmppLogPath'],
			'mode'    => 'cgi-stateless'
			,'logLevel'=>100000
			) );

		//require xeps
		for( $x = 0; $x < sizeof( $this->xeps ); $x++ )
			$this->jaxl->requires( "JAXL{$this->xeps[$x]}" );

		//hook in callbacks
		//(add callbacks using anonymous functions so we can keep them private)
		$this->jaxl->addPlugin( 'jaxl_post_connect', function( $stream_exists, \JAXL $jaxl ) { self::callback_jaxl_post_connect( $stream_exists, $jaxl ); } );
		$this->jaxl->addPlugin( 'jaxl_get_auth_mech', function( $mechanisms, \JAXL $jaxl ) { self::callback_jaxl_get_auth_mech( $mechanisms, $jaxl ); } );
		$this->jaxl->addPlugin( 'jaxl_post_auth_failure', function( $auth_success, \JAXL $jaxl ) { self::callback_jaxl_post_auth_failure( $auth_success, $jaxl ); } );
	}

	//start transaction
	final protected function start()
	{
		//flush output before starting
		ob_flush(); flush();

		//reset errors array
		$this->errors = array();

		//init jaxl if not done already
		if( $this->jaxl === NULL )
			$this->init();

		//main loop
		try
		{
			$this->stopped = FALSE;
			$this->jaxl->startCore();
		}
		catch( \Exception $e )
		{
			$this->errors[] = $e->getMessage();
		}

		//stop if not stopped
		$this->stop();

		//flush output once finished
		ob_flush(); flush();
	}

	//stop transaction
	final protected function stop()
	{
		if( $this->stopped === FALSE )
		{
			$this->stopped = TRUE;
			$this->jaxl->shutdown();
		}
	}

	//get transaction errors
	final public function getErrors()
	{
		return implode( "\n", $this->errors );
	}

	// JAXL CALLBACKS \\

	//stream_exists reports if stream was able to connect
	final static private function callback_jaxl_post_connect( & $stream_exists, \JAXL $jaxl )
	{
		if( $stream_exists === FALSE )
			throw new \Exception( "XMPP connection failed" );
		$jaxl->startStream();
	}

	//mechanisms is supported auth mechs
	final static private function callback_jaxl_get_auth_mech( & $mechanisms, \JAXL $jaxl )
	{
		//require secure auth mechanism from server
		if( ! in_array( "SCRAM-SHA-1", $mechanisms ) )
			throw new \Exception( "XMPP server doesn't support secure authentication protocol" );
		$jaxl->auth('SCRAM-SHA-1');
	}

	//auth_success is always false
	final static private function callback_jaxl_post_auth_failure( & $auth_success, \JAXL $jaxl )
	{
		//throw exception for failed login
		throw new \Exception( "XMPP authentication failed for: {$jaxl->user}" );
	}
}
?>
