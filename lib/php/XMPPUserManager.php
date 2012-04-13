<?php
namespace PhenLib;

//TODO - password minimum complexity

class XMPPUserMAnager
{
	private $jaxl;
	private $stop;
	private $noException;
	private $errors;

	//constructor
	public function __construct()
	{
		$this->_init();
		$this->errors = array();
	}

	//setup xmpp management connection
	private function _init()
	{
		//init jaxl
		$this->jaxl = new \JAXL( array(
			'user'=>$GLOBALS['xmppUser'],
			'pass'=>$GLOBALS['xmppPass'],
			'domain'=>$GLOBALS['xmppDomain'],
			'logPath'=>$GLOBALS['xmppLogPath'],
			'pidPath'=>$GLOBALS['xmppPidPath'],
//TODO - should be cgi (i think), but buggy - patched jaxl to not exit after cli for now
			'mode'=>'cli'
//			,'logLevel'=>100000
			) );

		//init class vars
		$this->stop = FALSE;
		$this->noException = FALSE;

		//require XEP-0077: In-Band Registration
		$this->jaxl->requires('JAXL0077');
	}
	
	//XEP-0077 register username + password
	public function registerEntity( $user, $pass )
	{
		//init vars
		$registered = FALSE;

		//register callback
		$this->jaxl->addPlugin( 'jaxl_post_connect', function( $payload, $jaxl ) use ( & $user, & $pass, & $registered )
	        {
			//state: connected
			$this->jaxl_post_connect( $payload, $jaxl );
	                $jaxl->JAXL0077( 'getRegistrationForm', '', $GLOBALS['xmppDomain'], function( $payload, $jaxl ) use ( & $user, & $pass, & $registered )
			{ 
				//state: form requested
				if( $payload['type'] === "error" )
				{
					$this->errors[] = "Error getting registration form:\n\$payload = " . var_export( $payload, true );
					$this->stop();
					return;
				}

				//at this point, $payload is the registration form, if we want it
				$jaxl->JAXL0077( 'register', '', $GLOBALS['xmppDomain'], function( $payload, $jaxl ) use ( & $user, & $pass, & $registered )
					{
						//state: registration submitted
						if( $payload['type'] === "error" )
						{
							$this->errors[] = "Error registering:\n\$payload = " . var_export( $payload, true );
							$this->stop();
							return;
						}
						if( $payload['type'] === "result" )
							$registered = true;
						$this->stop();
					}, array(
			                        'username' => $user,
		        	                'password' => $pass
			                ) );
			} );
	        } );

		//start connection
		$this->start();
		return $registered;
	}

	//XEP-0077 cancel register username + password
	public function cancelRegisterEntity( $user, $pass )
	{
		//login as user to remove
		$this->jaxl->user = $user;
		$this->jaxl->pass = $pass;
		$removed = FALSE;

		//register callbacks
		$this->jaxl->addPlugin('jaxl_post_connect', function( $p, $j ){ $this->jaxl_post_connect( $p , $j ); } );
		$this->jaxl->addPlugin('jaxl_get_auth_mech', function( $m, $j ){ $this->jaxl_get_auth_mech( $m , $j ); } );
//TODO - in cli mode this wont get called - figure out cgi mode
		$this->jaxl->addPlugin('jaxl_post_auth_failure', function( $p, $j ){ $this->jaxl_post_auth_failure( $p , $j ); } );
		$this->jaxl->addPlugin('jaxl_post_auth', function( $payload, $jaxl ) use ( & $removed )
                {
			//state: authenticated
			$jaxl->JAXL0077( 'register', '', '', function( $payload, $jaxl ) use ( & $removed )
			{
				//state: remove registration submitted
				if( $payload['type'] === "error" )
				{
					$this->errors[] = "Error cancelling registration:\n\$payload = " . var_export( $payload, true );
					$this->stop();
					return;
				}
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

	//XEP-0077 change password username + password + new password
	public function changePassword( $user, $oldPass, $newPass )
	{
		//login as user to remove
		$this->jaxl->user = $user;
		$this->jaxl->pass = $oldPass;
		$changed = FALSE;

		//register callbacks
		$this->jaxl->addPlugin('jaxl_post_connect', function( $p, $j ){ $this->jaxl_post_connect( $p , $j ); } );
		$this->jaxl->addPlugin('jaxl_get_auth_mech', function( $m, $j ){ $this->jaxl_get_auth_mech( $m , $j ); } );
//TODO - in cli mode this wont get called - figure out cgi mode
		$this->jaxl->addPlugin('jaxl_post_auth_failure', function( $p, $j ){ $this->jaxl_post_auth_failure( $p , $j ); } );
		$this->jaxl->addPlugin('jaxl_post_auth', function( $payload, $jaxl ) use ( & $newPass, & $changed )
                {
			//state: authenticated
			$jaxl->JAXL0077( 'register', '', '', function( $payload, $jaxl ) use ( & $newPass, & $changed )
			{
				//state: remove registration submitted
				if( $payload['type'] === "error" )
				{
					$this->errors[] = "Error changing password:\n\$payload = " . var_export( $payload, true );
					$this->stop();
					return;
				}
				if( $payload['type'] === "result" )
				{
					$this->jaxl->pass = $newPass;
					$changed = TRUE;
				}
				$this->stop();
			}, array( "username" => $this->jaxl->user, "password" => $newPass ) );
                } );

		//start connection
		$this->start();
		return $changed;
	}

	//get transaction errors
	public function getErrors()
	{
		return implode( "\n", $this->errors );
	}

	//start transaction
	private function start()
	{
		//flush output before starting
		ob_flush(); flush();

		//reset errors array
		$this->errors = array();

		//main loop
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
				$this->errors[] = $e->getMessage();
		}

		//shutdown & reset loop control var
		$this->jaxl->shutdown();
		$this->_init();

		//flush output once finished
		ob_flush(); flush();
	}

	//stop transaction
	private function stop()
	{
		//just sets a flag for the loop in start to stop
		if( $this->stop === FALSE )
			$this->stop = TRUE;
	}

	// COMMON JAXL CALLBACKS \\
	private function jaxl_post_connect( & $payload, \JAXL $jaxl )
	{
		if( $payload === FALSE )
			throw new \Exception( "XMPP connection failed" );
		$jaxl->startStream();
	}

	private function jaxl_get_auth_mech( & $mechanism, \JAXL $jaxl )
	{
		if( ! in_array( "SCRAM-SHA-1", $mechanism ) )
			throw new \Exception( "XMPP server doesn't support secure authentication protocol" );
		$jaxl->auth('SCRAM-SHA-1');
	}

//TODO - in cli mode this wont get called - figure out cgi mode
	private function jaxl_post_auth_failure( & $payload, \JAXL $jaxl )
	{
		throw new \Exception( "XMPP authentication failed for: {$jaxl->user}" );
	}

	//run test sequence
	public static function runTests()
	{
		echo "<pre>";
		echo "REGISTER(test,test): ";
		$xmppum = new XMPPUserManager();
		var_export( $xmppum->registerEntity("test","test") );
		echo "\n";
		echo "LAST ERROR:\n" . $xmppum->getErrors() . "\n\n";
		echo "REGISTER(test,test): ";
		var_export( $xmppum->registerEntity("test","test") );
		echo "\n";
		echo "LAST ERROR:\n" . $xmppum->getErrors() . "\n\n";
		echo "CHANGE PASSWORD(test,test,test2): ";
		var_export( $xmppum->changePassword("test","test","test2") );
		echo "\n";
		echo "LAST ERROR:\n" . $xmppum->getErrors() . "\n\n";
		echo "CHANGE PASSWORD(test,test2,test): ";
		var_export( $xmppum->changePassword("test","test2","test") );
		echo "\n";
		echo "LAST ERROR:\n" . $xmppum->getErrors() . "\n\n";
		echo "REMOVE(test,test): ";
		var_export($xmppum->cancelRegisterEntity("test","test"));
		echo "\n";
		echo "LAST ERROR:\n" . $xmppum->getErrors() . "\n\n";
		echo "REMOVE(test,test): ";
		var_export($xmppum->cancelRegisterEntity("test","test"));
		echo "\n";
		echo "LAST ERROR:\n" . $xmppum->getErrors() . "\n";
		echo "</pre>";
	}
}
?>
