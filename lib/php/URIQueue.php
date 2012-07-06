<?php
namespace PhenLib;

class URIQueue extends \SplQueue
{
	public function __construct( $uri )
	{
		//can't call even though doc says it exists...
		//parent::__construct();

		$uri = explode( "/", $uri );
		
		for( $x=0; $x<sizeof($uri); $x++ )
			$this->enqueue( $uri[$x] );
	}
}
?>
