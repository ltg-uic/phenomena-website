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
				try
				{
					PHPInternals::setExceptionOnError( FALSE );
					if( self::$db->real_connect( "p:{$GLOBALS['dbHost']}", $GLOBALS['dbUser'], $GLOBALS['dbPass'], $GLOBALS['dbName'] ) === FALSE )
					{
						PHPInternals::setExceptionOnError( TRUE );
						throw new \Exception( "Database: mysqli_real_connect failed. Mysqli connect error: " . self::$db->connect_error . " (" . self::$db->connect_errno . ")" );
					}
					PHPInternals::setExceptionOnError( TRUE );
				}
				catch( \Exception $e )
				{
					//Check if "MySQL server has gone away"
					if( self::$db->connect_errno === 2006 )
					{
						//Try to reconnect to "gone away" server
						if( self::$db->ping() !== TRUE )
							throw new \Exception( "Database: ping failed to reconnect to \"gone away\" server." );
					}
					//Re-throw all other exceptions
					else
						throw $e;
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
