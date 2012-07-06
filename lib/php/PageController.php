<?php
namespace PhenLib;

class PageController
{
//TODO - finish implementing singleton here and elsewhere
//	use Singleton;

//TODO - constructed fixes chicken and egg problem with instance, but is not nice
	private static $constructed = FALSE;
	private static $instance = NULL;
	private static $session = NULL;
	private static $baseURL = "";
	private static $relativeRootPath = "";
	private static $rootResource = NULL;

	private function __construct( URIQueue $uq )
	{
		if( self::$instance !== NULL )
			throw new \Exception( "PageController should never instantiate more than once" );

		self::$constructed = TRUE;

		Session::start();

		//link static vars to session storage
		if( ! isset( $_SESSION[__CLASS__] ) )
			$_SESSION[__CLASS__] = array(
				'lastPage' => NULL
				);
		self::$session =& $_SESSION[__CLASS__];


//TODO - needs to recurse template structure, down to bottom-most page, url - do based on page is templatable, url available

		//determine class name from url and load root resource
		//url name format is "word-word-word-word"
		//converts to class name format of "WordWordWordWord"
		$name = str_replace( " ", "", ucwords( str_replace( "-", " ", $uq->dequeue() ) ) );
		$class = "\\Phen\\{$name}";
		//TODO - detect when this should thow a 404 error (between autoload/controller)
		$res = new $class( $uq );

		if( $res instanceof Page )
		{
			//keep this / last page history
			self::$session['lastPage'] = $name;
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

		//get the raw URI path depth and create relative path to root
		$path_depth = $uq->count();
		for( $x=0; $x<$path_depth; $x++ )
			self::$relativeRootPath .= "../";

		//create base URL
//TODO - need to validate HTTP_HOST, can be spoofed by client
//TODO - need to correctly handle HTTP/HTTPS/PORT VARIANTS
//TODO - write code to use redirect uri instead of url
//TODO - check for multiple consecutive slashes in path (should be fixed for end of path)
//TODO - should probably do all this in URIQueue:
//		instead of using $_GET['url'], parse our of $_SERVER vars instead - more accurate
//TODO - FIX THIS GARBAGE
		$rurl = "";
		if( ! isset( $_SERVER['REDIRECT_URL'] ) )
			list( $rurl ) = explode( "?", $_SERVER['REQUEST_URI'] );
		else if( $_SERVER['REDIRECT_URL'][strlen($_SERVER['REDIRECT_URL'])-1] === "/" )
			$rurl = rtrim( $_SERVER['REDIRECT_URL'], "/" ) . "/" . self::$relativeRootPath;
		else
			$rurl = $_SERVER['REDIRECT_URL'] . "/../" . self::$relativeRootPath;
		self::$baseURL = "http://{$_SERVER['HTTP_HOST']}{$rurl}";

		//save the root resource
		self::$rootResource = $res;
	}

	public static function init( URIQueue $uq = NULL )
	{
		if( self::$constructed === FALSE )
		{
			if( $uq !== NULL )
			{
                        	self::$instance = new PageController( $uq );
				self::$constructed = TRUE;
			}
			else
				throw new \Exception( "PageController must be initialized with a URIQueue before use" );
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

	public static function getRelativeRootPath()
	{
		self::init();

		return self::$relativeRootPath;
	}

	public static function getRootResource()
	{
		self::init();

		return self::$rootResource;
	}
}
?>
