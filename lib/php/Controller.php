<?php
namespace PhenLib;

class Controller
{
//TODO - finish implementing singleton here and elsewhere
//	use Singleton;

	private static $instance = FALSE;
	private static $lastPage = NULL;
	private $resource;

	public function __construct( URIQueue $uq )
	{
		if( self::$instance === TRUE )
			throw new \Exception( "Controller should never instantiate more than once" );
		self::$instance = TRUE;

		Session::start();

		//link static vars to session storage
		if( ! isset( $_SESSION[__CLASS__]['lastPage'] ) )
			$_SESSION[__CLASS__]['lastPage'] = NULL;
		self::$lastPage =& $_SESSION[__CLASS__]['lastPage'];
		
		//load resource
		$name = $uq->dequeue();
//TODO - should be better logic here, not just assume uppercase name
		$class = "\\Phen\\".ucfirst($name);
		//TODO - detect when this should thow a 404 error (between autoload/controller)
		$res = new $class( $uq );

		if( $res instanceof Page )
		{
			//keep this / last page history
			self::$lastPage = $name;
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

		$this->resource = $res;
	}

	public static function getLastPage()
	{
		return self::$lastPage;
	}

	public function getResource()
	{
		return $this->resource;
	}
}
?>
