<?php
namespace PhenLib;

abstract class Password
{
	public static function generateRandom()
	{
		$pass = "";
		for( $x=0; $x<32; $x++ )
			$pass .= chr( mt_rand( 32, 126 ) );
		return $pass;
	}

	public static function isStrong( $password )
	{
		if( strlen( $password ) < 8 )
			return false;
		return true;
	}
}
?>
