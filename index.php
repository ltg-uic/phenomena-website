<?php
namespace Phen;

//read config file
require_once( "config.php" );

//set default time zone
\date_default_timezone_set( $GLOBALS['timezone'] );

//hook into php internals to provide functionality
require( "lib/php/PHPInternals.php" );
\PhenLib\PHPInternals::registerExceptionHandler();
\PhenLib\PHPInternals::registerErrorHandler();
\PhenLib\PHPInternals::registerClassAutoloader();
\PhenLib\PHPInternals::registerShutdownHandler();

//include 3rd party libraries
require_once( "lib/php/jaxl/core/jaxl.class.php" );
require_once( "lib/php/recaptcha/recaptchalib-1.11.php" );

//TODO - SOMEWHERE IMPLEMENT ACCESS RULES BASED ON AUTH

//init page controller
\PhenLib\PageController::init( ( isset( $_GET['uri'] ) ? $_GET['uri'] : NULL ), "home" );

//ua
$agent = get_browser()->browser;

//=== GENERATE OUTPUT ===

//link resources into header
$burl = \PhenLib\PageController::getBaseURL();
if( $agent === "IE" )
	\PhenLib\Template::appendDOM( "head", \PhenLib\Template::HTMLtoDOM( "<meta name=\"X-UA-Compatible\" content=\"IE=edge\" />" ) );
\PhenLib\Template::linkCSS( "{$burl}lib/css/phenomena.css" );
\PhenLib\Template::appendDOM( "head", \PhenLib\Template::HTMLtoDOM( "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\" />" ) );
\PhenLib\Template::linkCSS( "{$burl}lib/css/jquery/jquery.mobile.css" );
\PhenLib\Template::scriptExternal( "{$burl}lib/js/jquery/jquery-1.7.2.js" );
\PhenLib\Template::scriptExternal( "{$burl}lib/js/jquery/jquery.mobile.js" );
\PhenLib\Template::scriptExternal( "{$burl}lib/js/recaptcha/recaptcha_ajax.js" );

//add jquery mobile template
\PhenLib\Template::integrate( "body", new JQueryMobileTemplate() );

//add breadcrumbs
\PhenLib\Template::integrate( "header", new BreadCrumbNavigation() );

//add login status (or copyright if not logged in)
if( \PhenLib\Authentication::isAuthenticated() )
	\PhenLib\Template::integrate( "footer", new LoginStatus() );
else
	\PhenLib\Template::appendDOM( "footer", \PhenLib\Template::HTMLtoDOM( "Copyright © 2011\n" .
                        "The Board of Trustees of the University of Illinois" ) );

//main body
\PhenLib\Template::integrate( "content", \PhenLib\PageController::getResourceQueue() );

//debug / display
//\PhenLib\Template::integrate( "content", new Debug() );
\PhenLib\Template::display();
?>
