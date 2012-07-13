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

//TODO - SOMEWHERE IMPLEMENT ACCESS RULES BASED ON AUTH

//init page controller
\PhenLib\PageController::init( ( isset( $_GET['uri'] ) ? $_GET['uri'] : NULL ), "home" );

//=== GENERATE OUTPUT ===

//link resources into header
$burl = \PhenLib\PageController::getBaseURL();
\PhenLib\Template::linkCSS( "{$burl}lib/css/phenomena.css" );
\PhenLib\Template::appendDOM( "head", \PhenLib\Template::HTMLtoDOM( "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\" />" ) );
\PhenLib\Template::linkCSS( "{$burl}lib/css/jquery/jquery.mobile.theme.android-1.1.0.css" );
\PhenLib\Template::linkCSS( "{$burl}lib/css/jquery/jquery.mobile.structure.css" );
\PhenLib\Template::scriptExternal( "{$burl}lib/js/jquery/jquery-1.7.2.js" );
\PhenLib\Template::scriptExternal( "{$burl}lib/js/jquery/jquery.mobile.js" );

//add jquery mobile template
\PhenLib\Template::integrate( "body", new JQueryMobileTemplate() );

//add elements to template
$rq = \PhenLib\PageController::getResourceQueue();
$rqc = $rq->count();


//TODO - split this off into a displayable resource
//breadcrumb navigation
$prefix = "";
for( $x=0; $x<$rqc-1; $x++ )
	$prefix .= "../";

$rq->rewind();
$title = "<a href=\"{$prefix}\">" . $rq->current()->getTitle() . "</a>";
$rq->next();
while( $rq->valid() )
{
	$prefix = substr( $prefix, 3 );
	$title .= " - " . "<a href=\"{$prefix}\">" . $rq->current()->getTitle() . "</a>";
	$rq->next();
}

\PhenLib\Template::appendDom( "header", \PhenLib\Template::HTMLtoDOM( "<h1>{$title}</h1>" ) );

//TODO - split this off into a displayable resource
//login status box
if( \PhenLib\PageController::getRootResourceName() !== "Home" )
{
	\PhenLib\Template::appendDom( "content", \PhenLib\Template::HTMLtoDOM( <<<EOHTML
		<div style="float: right;">Login Status/Logout</div>
		<div style="clear: both;"></div>
EOHTML
		) );
}

//main body
\PhenLib\Template::integrate( "content", \PhenLib\PageController::getResourceQueue() );

//debug / display
//\PhenLib\Template::integrate( "content", new Debug() );
\PhenLib\Template::display();
?>
