<?php
namespace PhenLib;

class Database
{
	private static $db = NULL;

	private function __construct()
	{
		//don't instantiate
	}

	public static function connect()
	{
		if( self::$db === NULL )
		{
			self::$db = \mysqli_init();
			self::$db->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);
			self::$db->real_connect( "p:{$GLOBALS['dbHost']}", $GLOBALS['dbUser'], $GLOBALS['dbPass'], $GLOBALS['dbName'] );
			self::$db->autocommit( TRUE );
		}
		return self::$db;
	}

	public static function close()
	{
		if( self::$db !== NULL )
		{
			self::$db->close();
			self::$db = NULL;
		}
	}
}
?>
