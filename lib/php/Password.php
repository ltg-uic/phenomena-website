<?php
namespace PhenLib;

abstract class Password
{
	public static function generateRandom( $chars=32 )
	{
		$pass = "";
		for( $x=0; $x<$chars; $x++ )
			$pass .= chr( mt_rand( 32, 126 ) );
		return $pass;
	}

	public static function generateRandomWebSafe( $chars=32 )
	{
		$asciiSafe = array (
		  33, 39, 40, 41, 42, 45, 46, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57,
		  65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81,
		  82, 83, 84, 85, 86, 87, 88, 89, 90, 95, 97, 98, 99, 100, 101, 102,
		  103, 104, 105, 106, 107, 108, 109, 110, 111, 112, 113, 114, 115,
		  116, 117, 118, 119, 120, 121, 122 );
		$l = sizeof($asciiSafe);
		$pass = "";
		for( $x=0; $x<$chars; $x++ )
			$pass .= chr( $asciiSafe[mt_rand( 0, $l-1 )] );
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
