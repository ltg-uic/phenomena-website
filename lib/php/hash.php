<?php
function HashPassword($password, $salt=NULL)
{
	if( $salt === NULL )
		$salt = mcrypt_create_iv(4, MCRYPT_DEV_URANDOM);
	echo "salt: " . bin2hex( $salt ) . "\n";
	$hash = '{SSHA512}' . base64_encode(hash('sha512', $password . $salt, TRUE) . $salt);
	return $hash;
}

function ValidatePassword($password, $correctHash)
{
	$salt = substr( base64_decode( substr( $correctHash, 9 ) ), 64 );
	echo $testHash = HashPassword( $password, $salt );

	//if the hashes are exactly the same, the password is valid
	return $testHash === $correctHash;
}
?>
