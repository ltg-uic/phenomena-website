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
	list( $ns, $cl ) = explode( "\\", $name );
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

//load controller
$cont = new Controller( $uq );
$res = $cont->getResource( $uq );

//generate output
Template::linkcss( "lib/css/phenomena.css" );
Template::integrate( "body", $res );
Template::integrate( "body", new \Phen\Debug );
Template::display();
XMPPUserManager::runTests();
?>
