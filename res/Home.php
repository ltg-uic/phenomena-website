<?php
namespace Phen;

class Home extends \PhenLib\Page
{
	public function generateOutput()
	{
		$rootDoc = \PhenLib\Template::getDOC();
		$root = $this->root;

		$img = $rootDoc->createElement( "img" );
		$img->setAttribute( "alt", "The Phenomena Server" );
		$img->setAttribute( "src", \PhenLib\PageController::getBaseURL() . "lib/media/server.svg" );
		$img->setAttribute( "style", "float: left; width: 286px; height: 214px; background-color: #555555; border: solid 1px #CCCCCC; margin: 5px;" );
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
		$root->lastChild->setAttribute( "style", "float: left;" );

		$div = $rootDoc->createElement( "div" );
		$div->appendChild( $rootDoc->createTextNode(
			"This site is based upon work supported by the National Science Foundation " .
			"underrant Nos. DRL -0735569, IIS-1065275, and IIS-1124495."
			) );
		$root->appendChild( $div );

		$div = $rootDoc->createElement( "div" );
		$div->setAttribute( "style", "clear: both;" );
		$root->appendChild( $div );
	}

	public function getTitle()
	{
		return "Home";
	}
}
?>
