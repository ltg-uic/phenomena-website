<?php
namespace PhenLib;

//TODO: Change to batch processing of commands with one connection
class XMPPJAXL
{
	private $jaxl;
	protected $errors;
	private $stopped;
	private $commands;

	//constructor
	public function __construct()
	{
		//init instance, init errors array
		$this->jaxl = NULL;
		$this->errors = array();
		$this->stopped = TRUE;
		$this->commands = new \SplQueue();
	}

	//TODO - for now, this only works with post_auth hook, later might want post_connect?
	final public function registerCommand( $xeps, \Closure $callback, $user = NULL, $pass = NULL )
	{
		//if no user/pass sent, use from config
		$user = ( $user !== NULL ) ? $user : $GLOBALS['xmppUser'];
                $pass = ( $pass !== NULL ) ? $pass : $GLOBALS['xmppPass'];

		//batched commands are stored in a queue of queues, stored in like ( user, pass ) groups
		$cq = ( $this->commands->isEmpty() ) ? NULL : $this->commands->top();

		//if we have no main queue, or this command has different user, pass than the last, add new main queue
		if( $cq === NULL || ( $cq !== NULL && ( $cq->user !== $user || $cq->pass !== $pass ) ) )
			$this->commands->enqueue( $cq = (object) array( "xeps" => array(), "commands" => new \SplQueue(), "user" => $user, "pass" => $pass ) );

		//update xep map
		foreach( $xeps as $xep )
			$cq->xeps[$xep] = TRUE;

		//otherwise, just add callback to the current command queue
		$cq->commands->enqueue( $callback );
	}

	final private function processCommand()
	{
		try
		{
			//get command off current command subqueue
			$command = $this->commands->bottom()->commands->dequeue();
		}
		catch( \Exception $e )
		{
			//if empty, remove command queue, pass back to execute
			$this->commands->dequeue();
			$this->execute();
			return;
		}

		$command( $this->jaxl, function( & $errors )
		{
			foreach( $errors as $error )
				$this->errors[] = $error;
			$this->processCommand();
		} );
	}

	final public function execute()
	{
		try
		{
			$cq = $this->commands->bottom();
		}
		catch( \Exception $e )
		{
			//finished if no more
			$this->stop();
			return;
		}

		$this->init( $cq->user, $cq->pass );

		//require xeps
		foreach( $cq->xeps as $xep => $_ )
			$this->jaxl->requires( "JAXL{$xep}" );

		//start execution,
		//once we hit the callback requested 
		$this->jaxl->addPlugin( 'jaxl_post_auth', function( $_, \JAXL $jaxl ) { $this->processCommand(); } );
		$this->start();
	}

	//setup xmpp management connection
	final private function init( $user = NULL, $pass = NULL )
	{
		//stop if not stopped
		$this->stop();

		//init jaxl
		$this->jaxl = new \JAXL( array(
			'user'		=> ( $user !== NULL ) ? $user : $GLOBALS['xmppUser'],
			'pass'		=> ( $pass !== NULL ) ? $pass : $GLOBALS['xmppPass'],
			'domain'	=> $GLOBALS['xmppDomain'],
			'logPath'	=> $GLOBALS['xmppLogPath'],
			'mode'		=> 'cgi-stateless',
			'sendRate'	=> PHP_INT_MAX
			,'logLevel'	=> 100000
			) );

		//hook in callbacks
		//(add callbacks using anonymous functions so we can keep them private)
		$this->jaxl->addPlugin( 'jaxl_post_connect', function( $stream_exists, \JAXL $jaxl ) { self::callback_jaxl_post_connect( $stream_exists, $jaxl ); } );
		$this->jaxl->addPlugin( 'jaxl_get_auth_mech', function( $mechanisms, \JAXL $jaxl ) { self::callback_jaxl_get_auth_mech( $mechanisms, $jaxl ); } );
		$this->jaxl->addPlugin( 'jaxl_post_auth_failure', function( $auth_success, \JAXL $jaxl ) { self::callback_jaxl_post_auth_failure( $auth_success, $jaxl ); } );
	}

	//start transaction
	final private function start()
	{
		//flush output before starting
//TODO - set debug flag and only do this when in debug
//		ob_flush();flush();

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
//TODO - set debug flag and only do this when in debug
//		ob_flush();flush();
	}

	//stop transaction
	final private function stop()
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
