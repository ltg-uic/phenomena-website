<?php
namespace PhenLib;

abstract class Authentication
{
//TODO - this could be a trait i think....
	private static $session = NULL;

	private static function init()
	{
		Session::start();

		//link static vars to session storage
		if( ! isset( $_SESSION[__CLASS__] ) )
			$_SESSION[__CLASS__] = array(
				"authenticated" => FALSE,
				"authenticatedUser" => NULL
				);
		self::$session =& $_SESSION[__CLASS__];
	}

	public static function doLogin( $user, $pass )
	{
		self::init();

		try 
		{
			$db = Database::connect();
		} 
		catch( \Exception $e ) 
		{
			return false;
		}
                $sql = "SELECT `user_login`, `user_password`
                        FROM `users`
                        WHERE `user_login` = ?";

                $stmt = $db->prepare( $sql );
                $stmt->bind_param( "s", $user );
                $stmt->execute();
                $stmt->bind_result( $username, $stored_hash );
                $stmt->fetch();
                $stmt->close();
                self::$session['authenticated'] = HashSSHA512::verify( $pass, $stored_hash );
		if(self::$session['authenticated'])
			self::$session['authenticatedUser'] = $username;
		return self::$session['authenticated'];
	}

	public static function doLogout()
	{
		self::init();

		self::$session['authenticatedUser'] = NULL;
                self::$session['authenticated'] = FALSE;
	}

	public static function isAuthenticated()
	{
		self::init();

		return self::$session['authenticated'];
	}

	public static function getAuthenticatedUser()
	{
		self::init();

		return self::$session['authenticatedUser'];
	}
}
?>
