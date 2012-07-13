<?php
namespace PhenLib;

abstract class PHPInternals
{
	private static $exceptionOnError = TRUE;

	public static function registerExceptionHandler()
	{
		\set_exception_handler( function( $e )
		{
			header( "HTTP/1.1 500 Internal Server Error" );
		
			//core exception info
			$message = "<h1>HTTP/1.1 500 Internal Server Error</h1>\n\n" .
					"<pre>\n" .
					"=== Exception Information ===\n\n" .
					"Type:\t\t" . get_class( $e ) . "\n" .
					"Message:\t" . $e->getMessage() . "\n" .
					"Code:\t\t" . $e->getCode() . "\n" .
					"File:\t\t" . $e->getFile() . "\n" .
					"Line Number:\t" . $e->getLine() . "\n";

			//conditional exception info
			if( $e instanceof \ErrorException )
				$message .= "Severity:\t" . $e->getSeverity() . "\n";

			//stack trace
			$message .= "\nStack Trace:\n" .
					$e->getTraceAsString() . "\n" .
					"</pre>";

			exit( $message );
		} );
	}

	public static function setExceptionOnError( $doExceptionOnError )
	{
		self::$exceptionOnError = $doExceptionOnError;
	}

	public static function registerErrorHandler()
	{
		\set_error_handler( function( $no, $str, $file, $line )
		{
			if( self::$exceptionOnError )
				throw new \ErrorException( $str, $no, 0, $file, $line );
			else
				return false;
		} );
	}

	public static function registerClassAutoloader()
	{
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
				throw new \Exception( "Can't include class file for '{$name}'." );
		
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
	}

	public static function registerShutdownHandler()
	{
		\register_shutdown_function( function()
		{
			//close database if class is present
			if( class_exists( "\PhenLib\Database", FALSE ) )
				Database::close();
		} );
	}
}
?>
