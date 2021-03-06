<?php
namespace PhenLib;

class XMPPServiceAdministration
{
	//XEP-0133 add user
	static public function addUser( XMPPJAXL $xmppjaxl, $user, $domain, $pass, & $added ) 
	{
		//referenced retrun var for callback
		$added = FALSE;

		//register callback
		$xmppjaxl->registerCommand( array( "0133" ), function( $jaxl, $callback ) use ( & $user, & $domain, & $pass, & $added )
                {
			//state: authenticated
			$userArr = array( "jid" => $user, "pass" => $pass );
			$jaxl->JAXL0133( "addUser", $userArr, $domain, function( $payload, $jaxl ) use ( $callback, & $added )
			{
				//state: add user command executed
				$errors = array();
				if( $payload['type'] === "result" && (string)$payload['xml']->command['status'] === "completed" )
					$added = TRUE;
				else
					$errors[] = "Error adding user: {$payload['xml']->command->note}<br />\n";
				$callback( $errors );
			} );
                } );
	}

	//XEP-0133 change user password
	public function changeUserPassword( $user, $domain, $pass )
	{
		//re-init jaxl to register
		$this->init();

		//local return var for callback
		$changed = FALSE;

		//register callback
		$this->jaxl->addPlugin('jaxl_post_auth', function( $_, $jaxl ) use ( & $user, & $domain, & $pass, & $changed )
                {
			//state: authenticated
			$userArr = array( "jid" => $user, "pass" => $pass );
			$jaxl->JAXL0133( "changeUserPassword", $userArr, $domain, function( $payload, $jaxl ) use ( & $changed )
			{
				//state: add user command executed
				if( $payload['type'] === "result" && (string)$payload['xml']->command['status'] === "completed" )
					$changed = TRUE;
				else
					$this->errors[] = "Error changing user password: {$payload['xml']->command->note}";
				$this->stop();
			} );
                } );

		//start connection
		$this->start();
		return $changed;
	}

	//XEP-0133 delete user
	public function deleteUser( $user, $domain, $pass )
	{
		//re-init jaxl to register
		$this->init();

		//local return var for callback
		$deleted = FALSE;

		//register callback
		$this->jaxl->addPlugin('jaxl_post_auth', function( $_, $jaxl ) use ( & $user, & $domain, & $pass, & $deleted )
                {
			//state: authenticated
			$userArr = array( "jid" => $user, "pass" => $pass );
			$jaxl->JAXL0133( "deleteUser", $userArr, $domain, function( $payload, $jaxl ) use ( & $deleted )
			{
				//state: add user command executed
				if( $payload['type'] === "result" && (string)$payload['xml']->command['status'] === "completed" )
					$deleted = TRUE;
				else
					$this->errors[] = "Error changing user password: {$payload['xml']->command->note}";
				$this->stop();
			} );
                } );

		//start connection
		$this->start();
		return $deleted;
	}

	//run test sequence
	public static function runTests()
	{
		echo "<pre>";
		$xmppsa = new XMPPServiceAdministration();
		echo "ADDUSER( test, test ): ";
		var_export( $xmppsa->addUser( "test", "climax-linux.datacenter.fredk.com", "test" ) );
		echo "\n";
		echo "LAST ERROR: " . $xmppsa->getErrors() . "\n\n";
		echo "ADDUSER( test, test ): ";
		var_export( $xmppsa->addUser( "test", "climax-linux.datacenter.fredk.com", "test" ) );
		echo "\n";
		echo "LAST ERROR: " . $xmppsa->getErrors() . "\n\n";
		echo "CHANGEUSERPASSWORD( test, test2 ): ";
		var_export( $xmppsa->changeUserPassword( "test", "climax-linux.datacenter.fredk.com", "test2" ) );
		echo "\n";
		echo "LAST ERROR: " . $xmppsa->getErrors() . "\n\n";
		echo "CHANGEUSERPASSWORD( test, test ): ";
		var_export( $xmppsa->changeUserPassword( "test", "climax-linux.datacenter.fredk.com", "test" ) );
		echo "\n";
		echo "LAST ERROR: " . $xmppsa->getErrors() . "\n\n";
		echo "DELETEUSER( test, test ): ";
		var_export( $xmppsa->deleteUser( "test", "climax-linux.datacenter.fredk.com", "test" ) );
		echo "\n";
		echo "LAST ERROR: " . $xmppsa->getErrors() . "\n\n";
		echo "DELETEUSER( test, test ): ";
		var_export( $xmppsa->deleteUser( "test", "climax-linux.datacenter.fredk.com", "test" ) );
		echo "\n";
		echo "LAST ERROR: " . $xmppsa->getErrors() . "\n";
		echo "</pre>";
	}
}
?>
