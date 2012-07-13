<?php
namespace Phen;

class ControlPanel extends \PhenLib\Page implements \PhenLib\Templatable
{
	private $hooks = array();

	public function __construct( \SPLQueue $uq )
	{
		parent::__construct();

		$rootDoc = \PhenLib\Template::getDOC();
		$root = $this->root;

		//display if no subpath
		if( ! ( $uq !== NULL && ! $uq->isEmpty() ) )
		{
			$burl = \PhenLib\PageController::getBaseURL();
			$this->root->appendChild( \PhenLib\Template::HTMLtoDOM( 
				<<<EOHTML
				<p><img src="{$burl}lib/media/wallcology.png" /></p>
				<p><img src="{$burl}lib/media/roomquake.png" /></p>
				<p><a href="helioroom/"><img src="{$burl}lib/media/helioroom.png" /></a></p>
EOHTML
				) );
		}

		$this->hooks['content'] = $this->root;
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
