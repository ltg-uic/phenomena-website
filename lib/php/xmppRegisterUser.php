<?php

//TODO - make into class

//init xmpp
$xmpp = new JAXL( array(
	'user'=>$GLOBALS['xmppUser'],
	'pass'=>$GLOBALS['xmppPass'],
	'domain'=>$GLOBALS['xmppDomain'],
	'logLevel'=>4,
	'logPath'=>$GLOBALS['xmppLogPath'],
	'pidPath'=>$GLOBALS['xmppPidPath'],
	'streamTimeout' => 15,
	'mode'=>'cli'
	) );

//require XEP-0077: In-Band Registration
$xmpp->requires('JAXL0077');

//register callback
$xmpp->addPlugin('jaxl_post_connect', 'handle_post_connect');

// Fire start JAXL Core
$xmpp->startCore();

// CALLBACKS \\

//after connection
function handle_post_connect($payload, $xmpp)
{
	global $xmpp;
	$xmpp->startStream();
	$xmpp->JAXL0077( 'getRegistrationForm', '', 'climax-linux.datacenter.fredk.com', 'handle_getRegistrationForm' );
}

//handle registratin form
function handle_getRegistrationForm($payload, $jaxl)
{ 
	global $xmpp;
	$user_register = array(
		'username' => "test",
		'password' => "test"
	);
	$xmpp->JAXL0077( 'register', '', 'climax-linux.datacenter.fredk.com', 'handle_register', $user_register );
} 

//handle register
function handle_register( $payload, $jaxl )
{ 
	global $xmpp;
	$xmpp->shutdown(); 
}
?>
