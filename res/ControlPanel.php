<?php
namespace Phen;

class ControlPanel extends \PhenLib\Page implements \PhenLib\Templatable
{
	private $hooks = array();
	private $subpage = FALSE;

	public function __construct( \SPLQueue $uq )
	{
		parent::__construct();

		//if subpath don't display
		if( $uq !== NULL && ! $uq->isEmpty() )
			$this->subpage = TRUE;

		$this->hooks['content'] = $this->root;
	}

	public function generateOutput()
	{
		if( $this->subpage )
			return;

		$burl = \PhenLib\PageController::getBaseURL();
//data-ajax is false on helioroom to fix svg animation bug
		$this->root->appendChild( \PhenLib\Template::HTMLtoDOM( 
			<<<EOHTML
			<h2 style="line-height: 170px;"><img src="{$burl}lib/media/wallcology.png" alt="WallCology" style="float: left; margin-right: 50px;" />WallCology</h2>
			<h2 style="line-height: 170px;"><img src="{$burl}lib/media/roomquake.png" alt="RoomQuake" style="float: left; margin-right: 50px;" />RoomQuake</h2>
			<h2 style="line-height: 170px;"><a href="helioroom/" data-ajax="false"><img src="{$burl}lib/media/helioroom.png" alt="HelioRoom" style="float: left; margin-right: 50px;" />HelioRoom</a></h2>
EOHTML
			) );
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
