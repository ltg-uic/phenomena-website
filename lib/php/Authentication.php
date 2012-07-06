<?php
namespace PhenLib;

class Authentication
{
//TODO - finish implementing singleton here and elsewhere
//	use Singleton;

	private static $instance = NULL;
	private static $session = NULL;

	private function __construct()
	{
		if( self::$instance !== NULL )
			throw new \Exception( "Authentication should never instantiate more than once" );

		Session::start();

		//link static vars to session storage
		if( ! isset( $_SESSION[__CLASS__] ) )
			$_SESSION[__CLASS__] = array(
				"authenticated" => FALSE
				);
		self::$session =& $_SESSION[__CLASS__];
	}

	private static function init()
	{
		if( self::$instance === NULL )
			self::$instance = new Authentication();
	}

	public static function doLogin( $user, $pass )
	{
		self::init();

		$db = Database::connect();

                $sql = "SELECT `user_password`
                        FROM `users`
                        WHERE `user_login` = ?";

                $stmt = $db->prepare( $sql );
                $stmt->bind_param( "s", $user );
                $stmt->execute();
                $stmt->bind_result( $stored_hash );
                $stmt->fetch();
                $stmt->close();

                return self::$session['authenticated'] = HashSSHA512::verify( $pass, $stored_hash );
	}

	public static function isAuthenticated()
	{
		self::init();

		return self::$session['authenticated'];
	}
}
?>
