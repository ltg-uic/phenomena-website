<?php
namespace PhenLib;

abstract class reCAPTCHA
{
	public static function validate( $privatekey, $userip, $recaptcha_challenge, $recaptcha_response )
	{
		//send recatchpa
		$resp = recaptcha_check_answer ( $privatekey,
						 $userip, 
						 $recaptcha_challenge,
						 $recaptcha_response );
		return $resp;
	}
}
class RecaptchaException extends \Exception {}
?>
