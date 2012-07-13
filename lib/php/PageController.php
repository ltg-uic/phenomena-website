<?php
namespace PhenLib;

abstract class PageController
{
	private static $session = NULL;
	private static $initDone = FALSE;
	private static $URIQueue = NULL;
	private static $URIRawDepth = NULL;
	private static $baseURL = NULL;
	private static $baseRelativePath = NULL;
	private static $resourceQueue = NULL;
	private static $rootResource = NULL;
	private static $rootResourceName = NULL;

	public static function init( $uri = NULL, $default = NULL )
	{
		Session::start();

		//link static vars to session storage
		if( ! isset( $_SESSION[__CLASS__] ) )
			$_SESSION[__CLASS__] = array(
				'lastPage' => NULL
				);
		self::$session =& $_SESSION[__CLASS__];

		//one time init
		if( self::$initDone === FALSE )
		{
			//validity checks
			if( $default === NULL )
				throw new \Exception( "PageController must be initialized before use" );

			//build queue of uri
			self::$URIRawDepth = 0;
			if( $uri === NULL || $uri === "" )
			{
				$uri = $default;
				self::$URIRawDepth--;
			}
			$uri = explode( "/", $uri );
			self::$URIQueue = new \SPLQueue();
			for( $x=0; $x<sizeof($uri); $x++ )
				if( $uri[$x] !== "" )
					self::$URIQueue->enqueue( $uri[$x] );
			self::$URIRawDepth += self::$URIQueue->count();

			//determine paths
			self::calculatePaths();
			self::$initDone = TRUE;
			self::loadResources();
		}
	}

	private static function calculatePaths()
	{
		//get the raw URI path depth and create relative path to root
		self::$baseRelativePath = "";
		for( $x=0; $x<self::$URIRawDepth; $x++ )
			self::$baseRelativePath .= "../";

		//create base URL
//TODO - need to validate HTTP_HOST, can be spoofed by client
//TODO - need to correctly handle HTTP/HTTPS/PORT VARIANTS
//TODO - check for multiple consecutive slashes in path (should be fixed for end of path)??
		$path = parse_url( $_SERVER['SCRIPT_NAME'], PHP_URL_PATH );
		$path = substr( $path, 0, strrpos( $path, "/" )+1 );
		self::$baseURL = "http://{$_SERVER['HTTP_HOST']}{$path}";
	}

	private static function loadResources()
	{
		self::$resourceQueue = new \SPLQueue();
		self::$session['lastPage'] = "";
		while( ! self::$URIQueue->isEmpty() )
		{
			//determine class name from url
			//url name format is "word-word-word-word"
			//converts to class name format of "WordWordWordWord"
			$rawName = self::$URIQueue->dequeue();
			$name = str_replace( " ", "", ucwords( str_replace( "-", " ", $rawName ) ) );

			//404 error if invalid class
			if( ! is_readable( "res/{$name}.php" ) )
			{
				header( "HTTP/1.1 404 Not Found" );
				$message = "<h1>HTTP/1.1 404 Not Found</h1>";
				$message .= print_r( self::$URIQueue, true );
				$message .= $name;
				exit( $message );
			}

			//instantiate resource
			$class = "\\Phen\\{$name}";
			$res = new $class( self::$URIQueue );
	
			if( $res instanceof Page )
			{
				//keep this / last page history
				self::$session['lastPage'] .= "{$rawName}/";
			}
	
			if( $res instanceof Action )
			{
				$res->execute();
				if( ( $dest = $res->getRedirect() ) !== NULL )
				{
					header( "Location: {$dest}" );
					exit();
				}
			}

			self::$resourceQueue->enqueue( $res );

			//save the root resource
			if( self::$rootResource === NULL )
			{
				self::$rootResource = $res;
				self::$rootResourceName = $name;
			}
		}
	}

	public static function getLastPage()
	{
		self::init();
		return self::$session['lastPage'];
	}

	public static function getBaseURL()
	{
		self::init();
		return self::$baseURL;
	}

	public static function getBaseRelativePath()
	{
		self::init();
		return self::$baseRelativePath;
	}

	public static function getResourceQueue()
	{
		self::init();
		return self::$resourceQueue;
	}

	public static function getRootResource()
	{
		self::init();
		return self::$rootResource;
	}

	public static function getRootResourceName()
	{
		self::init();
		return self::$rootResourceName;
	}
}
?>
