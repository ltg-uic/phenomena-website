<?php
namespace PhenLib;

//TODO - password minimum complexity

class XMPPServiceAdministration extends XMPPJAXL
{
	//constructor
	public function __construct( $xeps = array() )
	{
		//XEP-0133: Service Administration
		parent::__construct( array( "0133" ) );
	}

	//XEP-0133 add user
	public function addUser( $user, $pass )
	{
		//re-init jaxl to register
		$this->init();

		//local return var for callback
		$added = FALSE;

		//register callbacks
		$this->jaxl->addPlugin('jaxl_post_connect', function( $p, $j ){ $this->jaxl_post_connect( $p , $j ); } );
		$this->jaxl->addPlugin('jaxl_get_auth_mech', function( $m, $j ){ $this->jaxl_get_auth_mech( $m , $j ); } );
//TODO - in cli mode this wont get called - figure out cgi mode
		$this->jaxl->addPlugin('jaxl_post_auth_failure', function( $p, $j ){ $this->jaxl_post_auth_failure( $p , $j ); } );
		$this->jaxl->addPlugin('jaxl_post_auth', function( $payload, $jaxl ) use ( & $user, & $pass, & $added )
                {
//			var_dump( $payload );
//			var_dump( $jaxl );
//			$this->stop();
			//state: authenticated
			$userArr = array( "jid" => $user, "pass" => $pass );
			$jaxl->JAXL0133( "addUser", $userArr, "climax-linux.datacenter.fredk.com", function( $payload, $jaxl ) use ( & $added )
			{
die( 'now' );
				var_export( $payload );
				var_export( $jaxl );
				//state: remove registration submitted
//				if( $payload['type'] === "error" )
//					$this->errors[] = "Error cancelling registration:\n\$payload = " . var_export( $payload, TRUE );
//				else if( $payload['type'] === "result" )
//					$removed = TRUE;
				$this->stop();
			} );
                } );

		//start connection
		
		$this->start();
		return $added;
	}

	//run test sequence
	public static function runTests()
	{
		echo "<pre>";
		echo "ADDUSER(test,test): ";
		$xmppum = new XMPPServiceAdministration();
		var_export( $xmppum->addUser("test","test") );
		echo "\n";
		echo "LAST ERROR:\n" . $xmppum->getErrors() . "\n\n";
//		echo "ADDUSER(test,test): ";
//		var_export( $xmppum->addUser("test","test") );
//		echo "\n";
//		echo "LAST ERROR:\n" . $xmppum->getErrors() . "\n";
		echo "</pre>";
	}
}
?>
