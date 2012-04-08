<?php
namespace Phen;

class Home extends \PhenLib\Page
{
	public function __construct()
	{
		parent::__construct();

		$rootDoc = \PhenLib\Template::getDOC();
		$root = $this->root;

		$h1 = $rootDoc->createElement( "h1" );
		$h1->appendChild( $rootDoc->createTextNode( $this->getTitle() ) );
		$root->appendChild( $h1 );

		$root->appendChild( (new Login)->getDOC() );
		$root->appendChild( (new Register)->getDOC() );
		$root->appendChild( (new Login)->getDOC() );
	}

	public function getTitle()
	{
		return "Home";
	}
}
?>
