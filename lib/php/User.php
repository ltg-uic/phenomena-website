<?php
namespace PhenLib;

abstract class User
{
//TODO - this could be a trait i think....
	private static $session = NULL;

	private static function init()
	{
		Session::start();

		//link static vars to session storage
		if( ! isset( $_SESSION[__CLASS__] ) )
			$_SESSION[__CLASS__] = array(
				"recoveryResult" => NULL
				);
		self::$session =& $_SESSION[__CLASS__];
	}

	public static function create( $user, $pass, $email )
	{
		self::init();

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
		
		if( $result === FALSE ) 
		{
			throw new \Exception( "Error Adding New User to Database" );	
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
		//TODO - fix JAXL library so error handling does not need to be turned off
		PHPInternals::setExceptionOnError( FALSE );	
		$xmpp->execute();
		PHPInternals::setExceptionOnError( TRUE );	
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
				throw new \Exception( "New User Creation, XMPP Error" );
			}
		//otherwise close the statement and return true
		$stmt->close();
		return TRUE;
	}

	public static function recoverInitialize( $email, $new_password )
	{
		self::init();

		$db = Database::connect();

		$sql = "SELECT `user_id`, `user_login` FROM `phen_website`.`users` WHERE `user_email`= ?";

		$stmt = $db->prepare( $sql );
		if( $stmt === FALSE )
			throw new \Exception( "Password Recovery Error, SQL Error for `users`" );
		$stmt->bind_param("s", $email);
		$result = $stmt->execute();
		//check for valid user
		if( $result === FALSE )
		{
			$stmt->close();
			throw new \Exception( "Password Recovery Error, User Not Found" );
		}
		//get ID and user name		
		$stmt->bind_result($id, $user);
		$stmt->fetch();
		$stmt->close();

		//generate key and urlencode
		
		$key = Password::generateRandomWebSafe();
		//hash password
		$pass = HashSSHA512::hash( $new_password );
		//insert into password_recover table
		$sql = "INSERT INTO `phen_website`.`password_recover`
			(
				`user_id`,
				`password_recover_key`,
				`password_recover_new_password`,
				`password_recover_time`
			)
			VALUES
			(
				?, ?, ?, ?
			)";
		$stmt = $db->prepare( $sql );
		if( $stmt === FALSE )
			throw new \Exception( "Password Recovery Error, SQL Error for `password_recover`" );
		$time = time();
		$stmt->bind_param("issi", $id, $key, $pass, $time);
		$result = $stmt->execute();
		$stmt->close();
		
		if( $result === FALSE )
			throw new \Exception( "Password Recovery Error, SQL insert into `password_recover`" );
		
		$url = PageController::getBaseURL()."LoginRecoverRegister/";
		//send recovery email with link
		if( !self::MailRecoveryKey( $user, $email, $url, $key ) )
			throw new \Exception( "Password Recovery Error, Error Mailing Key" );
		
		return TRUE;
	}

	public static function recoverFinalize( $key )
	{
		self::init();

		$db = Database::connect();

		//check for valid request
		$sql = "SELECT `password_recover_id`, `user_id`, `password_recover_new_password`, `password_recover_time`  FROM `phen_website`.`password_recover` WHERE `password_recover_key`=?";
		$stmt = $db->prepare( $sql );
		if( $stmt === FALSE )
		{	
			self::$session['recoveryResult'] = FALSE;
			return FALSE;
		}
		$stmt->bind_param( "s", $key );
		$result = $stmt->execute();
		if( $result === FALSE )
		{
			self::$session['recoveryResult'] = FALSE;
			$stmt->close();
			return FALSE;
		}
		//get the user id, new password, and request time
		$stmt->bind_result( $pr_id, $id, $pass, $time );
		$stmt->fetch();
		$stmt->close();
		//check for expired request
		if( ($time+3600) < time() )
		{
			self::$session['recoveryResult'] = FALSE;
			return FALSE;
		}
		//update user password
		$sql = "UPDATE `phen_website`.`users` 
			SET `user_password`= ? 
			WHERE `user_id`= ?";
		$stmt = $db->prepare( $sql );
		if( $stmt === FALSE )
		{
			self::$session['recoveryResult'] = FALSE;
			return FALSE;
		}
		$stmt->bind_param("si", $pass, $id );
		$result = $stmt->execute();
		$stmt->close();
		if( $result === FALSE )
		{
			self::$session['recoveryResult'] = FALSE;
			return FALSE;
		}
		//delete password recovery record
		$sql = "DELETE FROM `phen_website`.`password_recover` WHERE `password_recover_id`= ?";
		$stmt = $db->prepare( $sql );
		$stmt->bind_param("i", $pr_id);
		$result = $stmt->execute(); 
		self::$session['recoveryResult'] = TRUE;
		return TRUE;
	}

	public static function getRecoveryResult()
	{
		self::init();

		return self::$session['recoveryResult'];
	}

	public static function clearRecoveryResult()
	{
		self::init();

		self::$session['recoveryResult'] = NULL;
	}

	public static function mailRecoveryKey( $user, $email, $url, $key )
	{
		self::init();

		$key = urlencode( $key );
		$message = 
			"{$user},\n" .
			"\n" .
			"You have requested to reset your password for the Phenomenon Server.\n" .
			"\n" .
			"Please click the following link to confirm your request:\n" .
			"{$url}recover/{$key}\n" .
			"\n" .
			"Thank you,\n" . 
			"The Phenomenon Server";

		return mail( "{$user} <{$email}>", "Phenomenon Server Password Recovery", $message, "From: Phenomenon Server <server@phenomena.evl.uic.edu>\r\n" );
	}
}
?>
