<?php
namespace PhenLib;

abstract class HashSSHA512
{
	//generates a standards compliant salted ssha512 hash
	public static function hash( $password, $salt=NULL )
	{
		//create random salt
		if( $salt === NULL )
			$salt = mcrypt_create_iv(4, MCRYPT_DEV_URANDOM);

		//return hash string
		return "{SSHA512}" . base64_encode( hash( "sha512", $password . $salt, TRUE ) . $salt );
	}

	//verifies
	public static function verify( $password, $hash )
	{
		//extract random salt
		$salt = substr( base64_decode( substr( $hash, 9 ) ), 64 );

		//compare hash with newly generated hash
		return $hash === self::hash( $password, $salt );
	}
}
?>
