<?php
namespace PhenLib;

class Database
{
	private static $db = NULL;

	private function __construct()
	{
		//don't instantiate
//TODO - singleton
	}

	public static function connect()
	{
		if( self::$db === NULL )
		{
			self::$db = \mysqli_init();
			try
			{
				if( self::$db->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1) === FALSE )
					throw new \Exception( "Database: mysqli_options failed." );
				//try here to catch error exceptions
				try
				{
					if( @self::$db->real_connect( "p:{$GLOBALS['dbHost']}", $GLOBALS['dbUser'], $GLOBALS['dbPass'], $GLOBALS['dbName'] ) === FALSE )
						throw new \Exception();
				}
				catch( \Exception $e )
				{
					$hint = "";
					if( self::$db->connect_errno === 2002 )
						$hint = " - Hint: Try restarting apache.";
					throw new \Exception( "Database: mysqli_real_connect failed. Mysqli connect error: " . self::$db->connect_error . $hint );
				}
				if( self::$db->autocommit( TRUE ) === FALSE )
					throw new \Exception( "Database: mysqli_autocommit failed." );
			}
			catch( \Exception $e )
			{
				self::$db = NULL;
				throw $e;
			}
		}
		return self::$db;
	}

	public static function close()
	{
		if( self::$db !== NULL )
		{
			$failed = ! self::$db->close();
			self::$db = NULL;
			if( $failed )
				throw new \Exception( "Database: mysqli_close failed." );
		}
	}
}
?>
