<?php
namespace Phen;

class JQueryMobileTemplate extends \PhenLib\Displayable implements \PhenLib\Templatable
{
	private $hooks = array();

	public function generateOutput()
	{
		$rootDoc = \PhenLib\Template::getDOC();
		$root = $this->root;

		$pageDiv = $rootDoc->createElement( "div" );
		$pageDiv->setAttribute( "data-role", "page" );
		$pageDiv->setAttribute( "data-theme", "a" );
		$root->appendChild( $pageDiv );

		$headerDiv = $rootDoc->createElement( "div" );
		$headerDiv->setAttribute( "data-role", "header" );
		$pageDiv->appendChild( $headerDiv );

		$contentDiv = $rootDoc->createElement( "div" );
		$contentDiv->setAttribute( "data-role", "content" );
		$pageDiv->appendChild( $contentDiv );

		$footerDiv = $rootDoc->createElement( "div" );
		$footerDiv->setAttribute( "data-role", "footer" );
		$footerDiv->setAttribute( "class", "ui-bar" );
		$pageDiv->appendChild( $footerDiv );

		$this->hooks['header'] = $headerDiv;
		$this->hooks['content'] = $contentDiv;
		$this->hooks['footer'] = $footerDiv;
	}

	public function getHooks()
	{
		return $this->hooks;
	}
}
?>
