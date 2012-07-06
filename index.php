<?php
namespace PhenLib;

//read config file
require_once( "config.php" );

#default exception handler
\set_exception_handler( function( $e )
{
	header( "HTTP/1.1 500 Internal Server Error" );
	exit(	"<h1>HTTP/1.1 500 Internal Server Error</h1>\n\n" .
		"<pre>\n" .
		$e->getMessage() . "\n\n" . $e->getTraceAsString() .
		"\n</pre>" );
} );

#autoload classes
\spl_autoload_register( function( $name )
{
	//TODO validate / sanitize this input
	$name_arr = explode( "\\", $name );
	switch( sizeof( $name_arr ) )
	{
		case 2:
			$ns = $name_arr[0];
			$cl = $name_arr[1];
			break;
		case 1:
			$cl = $name_arr[0];
		default:
			$ns = NULL;
	}
	switch( $ns )
	{
		case "Phen":
			$file = "res/{$cl}.php";
			break;
		case "PhenLib":
			$file = "lib/php/{$cl}.php";
			break;
		default:
			throw new \Exception( "Unknown namespace for class '{$name}'." );
			return;
	}

	//TODO - detect when this should thow a 404 error (between autoload/controller)
	//extra paren needed for language construct
	if( ! is_readable( $file ) || ( include_once( $file ) ) === FALSE )
	{
		throw new \Exception( "Can't include class file for '{$name}'." );
	}


	//TODO - check for traits, to enforce singleton rule - maybe unify this logic since its done in displayable
	//	- check if traits from parent items show on inherited - might not need recursion..
	$classReflect = new \ReflectionClass( $name ); 
	$traits = array();
	do
	{
		foreach( $classReflect->getTraitNames() as $trait )
			$traits[] = $trait;
	} while( $classReflect = $classReflect->getParentClass() );
	if( in_array( "PhenLib\Singleton", $traits, TRUE ) )
	{
		$classReflect = new \ReflectionClass( $name );
		if( $classReflect->isInstantiable() )
			throw new \Exception( "Class using Singleton trait is instantiable: {$name}" );
	}
} );

//executes when script stops
register_shutdown_function( function()
{
	if( class_exists( "\PhenLib\Database", FALSE ) )
		Database::close();
} );

\date_default_timezone_set( $GLOBALS['timezone'] );

require_once( "lib/php/jaxl/core/jaxl.class.php" );

//get uri and build queue of it
//default set to home
$uri = ( isset( $_GET['uri'] ) ) ? $_GET['uri'] : "home";
$uri = ( $uri !== "" ) ? $uri : "home";
$uq = new URIQueue( $uri );

//init page controller
PageController::init( $uq );

//TODO - SOMEWHERE IMPLEMENT ACCESS RULES BASED ON AUTH

//generate output
Template::setBaseURL( PageController::getBaseURL() );
Template::linkCSS( "lib/css/phenomena.css" );
Template::appendDOM( "head", Template::HTMLtoDOM( "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\" />" ) );
Template::linkCSS( "lib/css/jquery/jquery.mobile.theme.android-1.1.0.css" );
Template::linkCSS( "lib/css/jquery/jquery.mobile.structure.css" );
Template::scriptExternal( "lib/js/jquery/jquery-1.7.2.js" );
Template::scriptExternal( "lib/js/jquery/jquery.mobile.js" );
if( stripos( $uri, "home" ) !== 0 )
{
	Template::appendDom( "header", Template::HTMLtoDOM( "<h1>".PageController::getRootResource()->getTitle()."</h1>" ) );
	Template::appendDom( "body", Template::HTMLtoDOM( <<<EOHTML
		<div style="float: right;">Login Status/Logout</div>
EOHTML
		) );
}
Template::integrate( "body", PageController::getRootResource() );
Template::display();
?>
