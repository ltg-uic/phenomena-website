<?php
namespace PhenLib;

//TODO - password minimum complexity

class XMPPInBandRegistration extends XMPPJAXL
{
	//constructor
	public function __construct( $xeps = array() )
	{
		parent::__construct( array( "0077" ) );
	}

	
	//XEP-0077 register username + password
	public function registerEntity( $user, $pass )
	{
		//re-init jaxl to register
		$this->init();

		//local return var for callback
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
					$this->errors[] = "Error getting registration form:\n\$payload = " . var_export( $payload, TRUE );
					$this->stop();
					return $registered;
				}

				//at this point, $payload is the registration form, if we want it
				$jaxl->JAXL0077( 'register', '', $GLOBALS['xmppDomain'], function( $payload, $jaxl ) use ( & $registered )
					{
						//state: registration submitted
						if( $payload['type'] === "error" )
							$this->errors[] = "Error registering:\n\$payload = " . var_export( $payload, TRUE );
						else if( $payload['type'] === "result" )
							$registered = TRUE;
						$this->stop();
					}, array( 'username' => $user, 'password' => $pass ) );
			} );
	        } );

		//start connection
		$this->start();
		return $registered;
	}

	//XEP-0077 cancel register username + password
	public function cancelRegisterEntity( $user, $pass )
	{
		//re-init jaxl to cancel register
		$this->init( $user, $pass );
		
		//local return var for callback
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
					$this->errors[] = "Error cancelling registration:\n\$payload = " . var_export( $payload, TRUE );
				else if( $payload['type'] === "result" )
					$removed = TRUE;
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
		//re-init jaxl to change password
		$this->init( $user, $oldPass );

		//local return var for callback
		$changed = FALSE;

		//register callbacks
		$this->jaxl->addPlugin('jaxl_post_connect', function( $p, $j ){ $this->jaxl_post_connect( $p , $j ); } );
		$this->jaxl->addPlugin('jaxl_get_auth_mech', function( $m, $j ){ $this->jaxl_get_auth_mech( $m , $j ); } );
//TODO - in cli mode this wont get called - figure out cgi mode
		$this->jaxl->addPlugin('jaxl_post_auth_failure', function( $p, $j ){ $this->jaxl_post_auth_failure( $p , $j ); } );
		$this->jaxl->addPlugin('jaxl_post_auth', function( $payload, $jaxl ) use ( & $newPass, & $changed )
                {
			//state: authenticated
			$jaxl->JAXL0077( 'register', '', '', function( $payload, $jaxl ) use ( & $changed )
			{
				//state: change password submitted
				if( $payload['type'] === "error" )
					$this->errors[] = "Error changing password:\n\$payload = " . var_export( $payload, TRUE );
				else if( $payload['type'] === "result" )
					$changed = TRUE;
				$this->stop();
			}, array( "username" => $this->jaxl->user, "password" => $newPass ) );
                } );

		//start connection
		$this->start();
		return $changed;
	}

	//run test sequence
	public static function runTests()
	{
		echo "<pre>";
		echo "REGISTER(test,test): ";
		$xmppum = new XMPPInBandRegistration();
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
