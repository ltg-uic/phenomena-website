<?php
namespace Phen;

class HelioRoom extends \PhenLib\Page implements \PhenLib\Action
{
	public function generateOutput()
	{
		$burl = \PhenLib\PageController::getBaseURL();
		\PhenLib\Template::scriptExternal( "{$burl}lib/js/HelioRoomSimulation.js" );
		\PhenLib\Template::scriptExternal( "{$burl}lib/js/HelioRoomSolarSystem.js" );
		\PhenLib\Template::scriptExternal( "{$burl}lib/js/HelioRoomPlanetTable.js" );

		//have config loaded and running on first load, or else animation start bug
		$html = <<<EOHTML
<script type="text/javascript">
<!--
//once page is ready
$(document).one( "pageinit", function()
	{
		//references to controls for windows and view angle
		var container_planet_table = $("#{$this->id}_ss-container-planet-table");
		var container_solar_system = $("#{$this->id}_ss-container-solar-system");
		var control_windows = $("#{$this->id}_ss-control-windows");
		var control_view_angle = $("#{$this->id}_ss-control-view-angle");
		var control_system_speed = $("#{$this->id}_ss-control-system-speed");

		var HRS = new HelioRoomSimulation( container_planet_table, container_solar_system, control_windows, control_view_angle, control_system_speed, '{$this->id}' );

		//triggered by event eventually
		HRS.getConfig()
	} );
-->
</script>
<div class="ui-grid-a">
	<div class="ui-block-a">
		<h2>Your Planetary System</h2>
		<div class="ui-grid-d" id="{$this->id}_ss-container-planet-table">
			<div class="ui-block-a"><b>Name</b></div>
			<div class="ui-block-b"><b>Color</b></div>
			<div class="ui-block-c"><b>Orbital period<br />(Earth days)</b></div>
			<div class="ui-block-d"><b>In the room</b></div>
			<div class="ui-block-e"><b>Status</b></div>
		</div>
		<h2>Your Display Setup</h2>
		<div data-role="fieldcontain">
			<label for="{$this->id}_ss-control-windows">Number of displays</label>
			<input type="range" name="{$this->id}_ss-control-windows" id="{$this->id}_ss-control-windows" value="4" min="4" max="8" />
		</div>
		<div data-role="fieldcontain">
			<label for="{$this->id}_ss-control-view-angle">Viewing angle (degrees)</label>
			<input type="range" name="{$this->id}_ss-control-view-angle" id="{$this->id}_ss-control-view-angle" value="45" min="1" max="90" />
		</div>

		<div data-role="fieldcontain">
			<fieldset data-role="controlgroup" data-type="horizontal">
				<legend>Display text</legend>
			     	<input type="radio" name="{$this->id}_ss-control-display-text" id="{$this->id}_ss-control-display-text-none" value="none" checked="checked" />
			     	<label for="{$this->id}_ss-control-display-text-none">None</label>
	
			     	<input type="radio" name="{$this->id}_ss-control-display-text" id="{$this->id}_ss-control-display-text-planet-name" value="planet-name" />
			     	<label for="{$this->id}_ss-control-display-text-planet-name">Planet name</label>
	
			     	<input type="radio" name="{$this->id}_ss-control-display-text" id="{$this->id}_ss-control-display-text-color-name" value="color-name" />
			     	<label for="{$this->id}_ss-control-display-text-color-name">Color name</label>
			</fieldset>
		</div>

		<label for="{$this->id}_ss-control-system-speed">System speed percent</label>
		<input type="range" name="{$this->id}_ss-control-system-speed" id="{$this->id}_ss-control-system-speed" value="100" min="10" max="500" step="10" />
	</div>
	<div class="ui-block-b" id="{$this->id}_ss-container-solar-system">
	</div>
</div>
EOHTML;
		$this->root->appendChild( \PhenLib\Template::HTMLtoDOM( $html ) );
	}

	public function getTitle()
	{
		return "HelioRoom";
	}
	
	public function execute()
	{
		if( isset( $_POST['action'] ) && $_POST['action'] === "get_config" )
		{
			\PhenLib\XML::send( $this->getInstanceConfig() );
			exit();
		}
		throw new \Exception( "HelioRoom: Missing or invalid action" );
	}

	public function getRedirect()
	{
		//All actions handled by JSON
		return NULL;
	}

	private function initInstance()
	{
		//read xml flat file
		//use admin channel to create:
		//- instance --- GUGO HELP
		//- master user (included in instance config message) - is main id of message, don't send password attribute
			//- ACTUALLY - JUST SEND ID, PHEN SERVER MANAGES THIS USER
		//- cp user
		//- other users (IF DEFINED INTHE CONFIG)
		//- window users (DONE ONLY ON THE FLY AS NEEDED - CAN NEVER KNOW MAX, HAVE TO CREATE ON FLY)
		// ALL USERS MUST BE ADDED TO INSTANCE (PER USER WORK FOR EACH ADDITION)
			// - ALL USERS (EXCEPT MASTER) ADDED TO MASTER's ROSTER
			// - MASTER IS ADDED TO ALL USER'S ROSTERS (EXCEPT MASTER)
			// - SUBSCRIPTION = BOTH
			// XEP-0144: Roster Item Exchange
	}

	private function getInstanceConfig()
	{
		//reset admin window password, don't cache and reuse
		//connect to xmpp with cp user,
		// establish presence, --- GUGO HELP
		//get config -- GUGO HELP
		//write back to ajax request

		return file_get_contents( "lib/xml/HelioRoomSimulationExampleData.xml" );
	}

	private function updatetInstanceConfig()
	{
		//connect to xmpp, sent update --- GUGO HELP
	}
}
?>
