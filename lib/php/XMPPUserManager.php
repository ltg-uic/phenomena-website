<?php
namespace PhenLib;

class XMPPUserMAnager
{
	private $jaxl;
	private $user;
	private $pass;
	private $stop;
	private $noException;

	public function __construct()
	{
		//init jaxl
		$this->jaxl = new \JAXL( array(
			'user'=>$GLOBALS['xmppUser'],
			'pass'=>$GLOBALS['xmppPass'],
			'domain'=>$GLOBALS['xmppDomain'],
			'logLevel'=>9999990,
			'logPath'=>$GLOBALS['xmppLogPath'],
			'pidPath'=>$GLOBALS['xmppPidPath'],
//TODO - should be cgi (i think), but buggy - patched jaxl to not exit after cli for now
			'mode'=>'cli'
			) );

		//require XEP-0077: In-Band Registration
		$this->jaxl->requires('JAXL0077');

		$this->user = NULL;
		$this->pass = NULL;
		$this->stop = FALSE;
		$this->noException = FALSE;
	}
	

	public function register( $user, $pass )
	{
		$this->user = $user;
		$this->pass = $pass;
		$registered = FALSE;

		//register callback
		$this->jaxl->addPlugin( 'jaxl_post_connect', function( $payload, $jaxl ) use ( & $registered )
	        {
			//state: connected
			$this->jaxl_post_connect( $payload, $jaxl );
	                $jaxl->JAXL0077( 'getRegistrationForm', '', 'climax-linux.datacenter.fredk.com', function( $payload, $jaxl ) use ( & $registered )
			{ 
				//state: form requested
				if( $this->error( $payload ) )
				{
					echo "getreg - quit";
					return;
				}
				//at this point, $payload is the registration form, if we want it
				$jaxl->JAXL0077( 'register', '', 'climax-linux.datacenter.fredk.com', function( $payload, $jaxl ) use ( & $registered )
					{
						//state: registration submitted
						if( $payload['type'] === "result" )
							$registered = true;
						$this->stop();
					}, array(
			                        'username' => $this->user,
		        	                'password' => $this->pass
			                ) );
			} );
	        } );

		//start connection
		$this->start();
		return $registered;
	}

	public function remove( $user, $pass )
	{
		//login as user to remove
		$this->jaxl->user = $user;
		$this->jaxl->pass = $pass;
		$removed = FALSE;

		//register callbacks
		$this->jaxl->addPlugin('jaxl_post_connect', function( $p, $j ){ $this->jaxl_post_connect( $p , $j ); } );
		$this->jaxl->addPlugin('jaxl_get_auth_mech', function( $m, $j ){ $this->jaxl_get_auth_mech( $m , $j ); } );
		$this->jaxl->addPlugin('jaxl_post_auth_failure', function( $p, $j ){ $this->jaxl_post_auth_failure( $p , $j ); } );
		$this->jaxl->addPlugin('jaxl_post_auth', function( $payload, $jaxl ) use ( & $removed )
                {
			//state: authenticated
			$this->jaxl_post_auth( $payload, $jaxl );
			$jaxl->JAXL0077( 'register', '', '', function( $payload, $jaxl ) use ( & $removed )
			{
				//state: remove registration submitted
				if( $payload['type'] === "result" )
				{
					$this->noException = TRUE;
					$removed = TRUE;
				}
				$this->stop();
			}, array( "remove" => NULL ) );
                } );

		//start connection
		$this->start();
		return $removed;
	}

	private function start()
	{
		try
		{
			if( $this->jaxl->connect() !== FALSE )
			{
				while( $this->jaxl->stream !== FALSE && $this->stop === FALSE )
					$this->jaxl->getXML();
			}
		}
		catch( \Exception $e )
		{
			if( ! $this->noException === TRUE )
				echo "Phen Handler: " . $e->getMessage();
		}
		$this->jaxl->shutdown();
	}

	private function stop()
	{
		if( $this->stop === FALSE )
			$this->stop = TRUE;
	}

	private function error( & $payload )
	{
		if( $payload['type'] === "error" )
		{
			echo "error\n";
			print_r( $payload );
			$this->stop();
			return true;
		}
		return false;
	}

	// COMMON JAXL CALLBACKS \\
	private function jaxl_post_connect( & $payload, \JAXL & $jaxl )
	{
		if( $payload === FALSE )
			throw new \Exception( "XMPP connection failed" );
		$jaxl->startStream();
	}

	private function jaxl_get_auth_mech( & $mechanism, \JAXL & $jaxl )
	{
		if( ! in_array( "SCRAM-SHA-1", $mechanism ) )
			throw new \Exception( "XMPP server doesn't support secure authentication protocol" );
		$jaxl->auth('SCRAM-SHA-1');
	}

	private function jaxl_post_auth_failure( & $payload, \JAXL & $jaxl )
	{
		throw new \Exception( "XMPP authentication failed for: {$jaxl->user}" );
	}

	private function jaxl_post_auth( & $payload, \JAXL & $jaxl )
	{
		//payload appears to always be FALSE
		//no-op
	}
}
?>
