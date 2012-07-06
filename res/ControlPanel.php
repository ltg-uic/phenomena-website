<?php
namespace Phen;

class ControlPanel extends \PhenLib\Page implements \PhenLib\Templatable
{
	private $hooks = array();

	public function __construct()
	{
		parent::__construct();

		$rootDoc = \PhenLib\Template::getDOC();
		$root = $this->root;

		$this->root->appendChild( \PhenLib\Template::HTMLtoDOM( 
			<<<EOHTML
			<p><img src="lib/media/wallcology.png" /></p>
			<p><img src="lib/media/roomquake.png" /></p>
			<p><a href="../helioroom/"><img src="lib/media/helioroom.png" /></a></p>
EOHTML
			) );
		$div = $rootDoc->createElement( "div" );
		$div->setAttribute( "style", "clear: both;" );
		$this->root->appendChild( $div );

		$this->hooks['body'] = $this->root;
	}

	public function getTitle()
	{
		return "Control Panel";
	}

	public function getHooks()
	{
		return $this->hooks;
	}
}
?>
