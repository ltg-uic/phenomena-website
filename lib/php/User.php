<?php
namespace PhenLib;

abstract class User
{
	public static function create( $user, $pass, $email )
	{
//TODO - password minimum complexity
//TODO - validation
		/**** SETUP WEB USER ****/

		$db = Database::connect();

		$sql = "INSERT INTO `phen_website`.`users`
			(
				`user_id`,
				`user_login`,
				`user_password`,
				`user_email`
			)
			VALUES
			(
				NULL, ?, ?, ?
			)";

		$stmt = $db->prepare( $sql );
		
		$pass = HashSSHA512::hash( $pass );
		$stmt->bind_param( "sss", $user, $pass, $email );

		$result = $stmt->execute();

		if( $result === TRUE )
			$id = $stmt->insert_id;
		
		//$stmt->close();

		if( $result === FALSE ) 
		{
			return FALSE;
		}

		/**** SETUP XMPP USERS FOR WEB USER ****/

		$windows = 4;

		//echo "Setting up user id: {$id}<br />";
		//get phenomenas from pod server, update db
		//right now we just get from db until a query to the server works

		$db->real_query( "SELECT * FROM `phenomenas`" );

		$res = $db->store_result();

		$xmpp = new XMPPJAXL();
		while( $row = $res->fetch_assoc() )
			for( $x=0; $x<$windows; $x++ )
			{
				$xmpp_user = "{$row['phenomena_name']}_{$id}_{$x}";
				XMPPServiceAdministration::addUser( $xmpp, $xmpp_user, $GLOBALS['xmppDomain'], Password::generateRandom(), $added[$xmpp_user] );
			}
		$xmpp->execute();
		//echo "users added:<br />\n";
		//var_export( $added );
		//echo $xmpp->getErrors();
		$res->free();

		//exit();
		//check each XMPP user createdreturn remove user and return false if there was an error
		foreach( $added as $checkXMPPuser ) 
			if ( $checkXMPPuser === FALSE ) 
			{
				//remove the user that was just added
				$sql = "DELETE FROM `phen_website`.`users` WHERE `user_id`= ?";
				$stmt = $db->prepare( $sql );
				$stmt->bind_param("i", $id);
				$stmt->execute();
				$stmt->close();
				return FALSE;
			}
		//otherwise close the statement and return true
		$stmt->close();
		return TRUE;
	}

	public static function recoverInitialize( $email, $new_password )
	{
		//get user id, insert id + recovery into table

		//hash and store new password into recovery table

		//send recovery email with link
		//need to probably urlescape the key string
		//PageController::getBaseURL()."/LoginRegisterRecoverWhatever?recover_key=saodfnaoiudsgbaouggafdharfdjhwrtjh

		//return true/false if email is on file
	}

	public static function recoverFinalize( $key )
	{
		//called from execute block $_GET['recover_key'] in loginregrec class
		//if matches database entry, overwrite password hash in user table with one in recovery table, popup and advise user that password is reset
	}

	public static function mailRecoveryKey( $name, $email, $url, $key )
	{
		$key = rawurlencode( $key );
		$message = 
			"{$user},\n" .
			"\n" .
			"You have requested to reset your password for the Phenomenon Server.\n" .
			"\n" .
			"Please click the following link to confirm your request:\n" .
			"{$url}?recovery_key={$key}\n" .
			"\n" .
			"Thank you,\n" . 
			"The Phenomenon Server";

		return mail( "{$user} <{$email}>", "Phenomenon Server Password Recovery", $message, "From: Phenomenon Server <server@phenomena.evl.uic.edu>\r\n" );
	}
}
?>
