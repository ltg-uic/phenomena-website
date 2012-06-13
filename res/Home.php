<?php
namespace Phen;

class Home extends \PhenLib\Page
{
	public function __construct()
	{
		parent::__construct();

		$rootDoc = \PhenLib\Template::getDOC();
		$root = $this->root;

//TODO - temp jquery stuff, need to refactor this into a better place, probably page class
$root->setAttribute( "data-role", "page" );
$root->setAttribute( "data-theme", "a" );

$contentDiv = $rootDoc->createElement( "div" );
$contentDiv->setAttribute( "data-role", "content" );

$root->appendChild( $contentDiv );
$root = $contentDiv;
//END TODO

		$img = $rootDoc->createElement( "img" );
		$img->setAttribute( "src", "lib/media/waitress.png" );
		$img->setAttribute( "style", "float: left;" );
		$root->appendChild( $img );

		$h1 = $rootDoc->createElement( "h1" );
		$h1->appendChild( $rootDoc->createTextNode( "The Phenomenon Server" ) );
		$root->appendChild( $h1 );

		$div = $rootDoc->createElement( "div" );
		$div->appendChild( $rootDoc->createTextNode( "Conduct classroom investigations of simulated science phenomena using the Phenomenon Server. A free service of the Learning Technologies Group at the University of Illinois at Chicago." ) );
		$root->appendChild( $div );

		$div = $rootDoc->createElement( "div" );
		$div->setAttribute( "style", "clear: both;" );
		$root->appendChild( $div );

		$root->appendChild( (new LoginRecoverRegister)->getDOC() );
	}

	public function getTitle()
	{
		return "Home";
	}
}
?>
