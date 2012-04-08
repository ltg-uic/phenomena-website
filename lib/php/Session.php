<?php
namespace PhenLib;

class Session
{
	public static function start()
	{
		//TOOD - session_set_cookie_parameters

		$stat = session_status();
		switch( $stat )
		{
			case PHP_SESSION_DISABLED:
				throw new \Exception( "PHP sessions are disabled" );
				break;
			case PHP_SESSION_NONE:
				session_start();
				break;
			case PHP_SESSION_ACTIVE:
				break;
			default:
				throw new \Exception( "Unexpected PHP session status: {$stat}" );
				break;
		}
	}
}
?>
