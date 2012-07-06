<?php
namespace Phen;

class HelioRoom extends \PhenLib\Page
{
	public function __construct()
	{
		parent::__construct();

		$rootDoc = \PhenLib\Template::getDOC();
		$root = $this->root;

		$html = <<<EOHTML
<script type="text/javascript">
<!--
function HelioRoomSolarSystem( container_id, windows, degrees )
{
	//static var
	HelioRoomSolarSystem.svgns = "http://www.w3.org/2000/svg";

	//init values
//TODO - make this error output into the svg itself
	if( windows * degrees > 360 )
	{
		alert( "invalid solar system arguments" );
		return null;
	}
	this.windows = windows;
	this.degrees = degrees;

	//create svg canvas
	this.svg = document.createElementNS( HelioRoomSolarSystem.svgns, "svg" );
	this.svg.setAttribute( "width", "500" );
	this.svg.setAttribute( "height", "500" );
	this.svg.setAttribute( "viewbox", "0 0 500 500" );
	this.svg.setAttribute( "preserveAspectRatio", "xMidYMid meet" );

	//create svg group for sectors, reference array for sectors
	var sector_group = document.createElementNS( HelioRoomSolarSystem.svgns, "g");
	this.sectors = new Array(16);
	for( var x = 0; x < 16; x++ )
	{
		this.sectors[x] = document.createElementNS( HelioRoomSolarSystem.svgns, "path");
		sector_group.appendChild( this.sectors[x] );
	}
		
	//create svg group for rings
	this.rings = document.createElementNS( HelioRoomSolarSystem.svgns, "g");

	//link dom elements together
	this.svg.appendChild( sector_group );
	this.svg.appendChild( this.rings );
	document.getElementById( container_id ).appendChild( this.svg );

	//draw things
	this.drawSectors();
	this.drawRings();
}

HelioRoomSolarSystem.prototype.setWindows = function( windows )
{
	//out of bounds from ui specification
	if( windows < 4 || windows > 8 )
		return false;

	//update values
	this.windows = windows;
	this.degrees = 180 / windows;
	return true;
};

HelioRoomSolarSystem.prototype.getWindows = function()
{
	return this.windows;
};

HelioRoomSolarSystem.prototype.setViewAngle = function( degrees )
{
	//windows * degrees bigger than the circle
	if( this.windows * degrees > 360 )
		return false;

	//update value
	this.degrees = degrees;
	return true;
};

HelioRoomSolarSystem.prototype.getViewAngle = function()
{
	return this.degrees;
};

HelioRoomSolarSystem.prototype.drawSectors = function()
{
	//*** adapted from:
	//    http://jmvidal.cse.sc.edu/talks/canvassvg/javascriptandsvg.xml ***//

	//cx, cy, r: the center and radius of the pie
	var cx = 250;
	var cy = 250;
	var r = 250;
	//angle of sectors
	var hidden_degrees = (360 - this.windows * this.degrees) / this.windows;
	var angles = [ this.degrees / 360 * Math.PI * 2, hidden_degrees / 360 * Math.PI * 2 ];
	//colors to alternate
	var colors = [ "#292729", "#000000" ];
	
	// Loop through each slice of pie.
	startangle = 0;
	for(var x = 0; x < 16; x++)
	{
		//get reference to path
		var path = this.sectors[x];

		//hide sectors not needed for this many windows
		if( x >= this.windows * 2 )
		{
			path.setAttribute( "fill-opacity", "0" );
			continue;
		}

		// This is where the wedge ends
		var endangle = startangle + angles[x%2];
	
		// Compute the two points where our wedge intersects the circle
		// These formulas are chosen so that an angle of 0 is at 12 o'clock
	
		// and positive angles increase clockwise.
		var x1 = cx + r * Math.sin(startangle);
		var y1 = cy - r * Math.cos(startangle);
		var x2 = cx + r * Math.sin(endangle);
		var y2 = cy - r * Math.cos(endangle);
	        
		// This is a flag for angles larger than than a half circle
		var big = 0;
		if (endangle - startangle > Math.PI) big = 1;
        
		// This string holds the path details
		var d = "M " + cx + "," + cy +  // Start at circle center
			" L " + x1 + "," + y1 +     // Draw line to (x1,y1)
			" A " + r + "," + r +       // Draw an arc of radius r
			" 0 " + big + " 1 " +       // Arc details...
			x2 + "," + y2 +             // Arc goes to to (x2,y2)
			" Z";                       // Close path back to (cx,cy)

	        // This is an XML element, so all attributes must be set
	        // with setAttribute().  We can't just use JavaScript properties
		path.setAttribute( "d", d );              // Set this path 
		path.setAttribute( "fill", colors[x%2] );   // Set wedge color
		path.setAttribute( "fill-opacity", "1" );

		// The next wedge begins where this one ends
		startangle = endangle;
	}	
};

HelioRoomSolarSystem.prototype.drawRings = function()
{
	//remove rings
	while( this.rings.lastChild )
		this.rings.removeChild( this.rings.lastChild );

	for( var x = 0; x < 10; x++ )
	{
		var circle = document.createElementNS( HelioRoomSolarSystem.svgns, "circle" );
		circle.setAttribute( "cx", "250" );
		circle.setAttribute( "cy", "250" );
		circle.setAttribute( "r", ( (x+1) / 11 ) * 250 );
		circle.setAttribute( "stroke", "#5B5B5B" );
		circle.setAttribute( "stroke-width", "1" );
		circle.setAttribute( "fill", "#FFFFFF" );
		if( x == 0 )
			circle.setAttribute( "fill-opacity", "1" );
		else
			circle.setAttribute( "fill-opacity", "0" );
		this.rings.appendChild(circle);
	}
}

//once page is ready
$(document).on( "pageinit", function()
	{
		//references to controls for windows and view angle
		var control_windows = $("#{$this->id}_ss-control-windows");
		var control_view_angle = $("#{$this->id}_ss-control-view-angle");

		//init helioroom solar system
		var ss = new HelioRoomSolarSystem( "{$this->id}_ss", control_windows.prop( "value" ), control_view_angle.prop( "value" ) );

		//bind event when windows changes
		control_windows.on( "change", function( event, ui )
		{
			//fast fail if windows is not changing by a integer number
			if( control_windows.prop( "value" ) == ss.getWindows() )
				return;

			//if change fails, constrain slider to proper bound
			if( ! ss.setWindows( control_windows.prop( "value" ) ) )
				control_windows.slider( 'refresh', ss.getWindows(), true );
			//if change succeeds, update angle slider to new value and redraw ss
			else
			{
				control_view_angle.slider( 'refresh', ss.getViewAngle(), true );
				ss.drawSectors();
			}
		});

		//bind event when view angle changes
		control_view_angle.on( "change", function( event, ui )
		{
			//fast fail if angle is not changing by a integer number
			if( control_view_angle.prop( "value" ) == ss.getViewAngle() )
				return;

			//if change fails, constrain slider to proper bound
			if( ! ss.setViewAngle( control_view_angle.prop( "value" ) ) )
				control_view_angle.slider( 'refresh', ss.getViewAngle(), true );
			//if change succeeds, redraw ss
			else
				ss.drawSectors();
		});
	} );
-->
</script>
<div class="ui-grid-a">
	<div class="ui-block-a">
		<h2>Your Planetary System</h2>
		<div class="ui-grid-d">
			<div class="ui-block-a"><div class="ui-bar ui-bar-e" ><b>Name</b></div></div>
			<div class="ui-block-b"><div class="ui-bar ui-bar-e" ><b>Color</b></div></div>
			<div class="ui-block-c"><div class="ui-bar ui-bar-e" ><b>Orbital period<br />(Earth days)</b></div></div>
			<div class="ui-block-d"><div class="ui-bar ui-bar-e" ><b>In the room</b></div></div>
			<div class="ui-block-e"><div class="ui-bar ui-bar-e" ><b>Status</b></div></div>
			<div class="ui-block-a"><div class="ui-bar ui-bar-e" >Mercury</div></div>
			<div class="ui-block-b"><div class="ui-bar ui-bar-e" >blue</div></div>
			<div class="ui-block-c"><div class="ui-bar ui-bar-e" >88</div></div>
			<div class="ui-block-d"><div class="ui-bar ui-bar-e" >5 minutes</div></div>
			<div class="ui-block-e"><div class="ui-bar ui-bar-e" >Leaves W2 in 3:13</div></div>
			<div class="ui-block-a"><div class="ui-bar ui-bar-e" >A</div></div>
			<div class="ui-block-b"><div class="ui-bar ui-bar-e" >B</div></div>
			<div class="ui-block-c"><div class="ui-bar ui-bar-e" >C</div></div>
			<div class="ui-block-d"><div class="ui-bar ui-bar-e" >D</div></div>
			<div class="ui-block-e"><div class="ui-bar ui-bar-e" >E</div></div>
			<div class="ui-block-a"><div class="ui-bar ui-bar-e" >A</div></div>
			<div class="ui-block-b"><div class="ui-bar ui-bar-e" >B</div></div>
			<div class="ui-block-c"><div class="ui-bar ui-bar-e" >C</div></div>
			<div class="ui-block-d"><div class="ui-bar ui-bar-e" >D</div></div>
			<div class="ui-block-e"><div class="ui-bar ui-bar-e" >E</div></div>
			<div class="ui-block-a"><div class="ui-bar ui-bar-e" >A</div></div>
			<div class="ui-block-b"><div class="ui-bar ui-bar-e" >B</div></div>
			<div class="ui-block-c"><div class="ui-bar ui-bar-e" >C</div></div>
			<div class="ui-block-d"><div class="ui-bar ui-bar-e" >D</div></div>
			<div class="ui-block-e"><div class="ui-bar ui-bar-e" >E</div></div>
			<div class="ui-block-a"><div class="ui-bar ui-bar-e" >A</div></div>
			<div class="ui-block-b"><div class="ui-bar ui-bar-e" >B</div></div>
			<div class="ui-block-c"><div class="ui-bar ui-bar-e" >C</div></div>
			<div class="ui-block-d"><div class="ui-bar ui-bar-e" >D</div></div>
			<div class="ui-block-e"><div class="ui-bar ui-bar-e" >E</div></div>
			<div class="ui-block-a"><div class="ui-bar ui-bar-e" >A</div></div>
			<div class="ui-block-b"><div class="ui-bar ui-bar-e" >B</div></div>
			<div class="ui-block-c"><div class="ui-bar ui-bar-e" >C</div></div>
			<div class="ui-block-d"><div class="ui-bar ui-bar-e" >D</div></div>
			<div class="ui-block-e"><div class="ui-bar ui-bar-e" >E</div></div>
			<div class="ui-block-a"><div class="ui-bar ui-bar-e" >A</div></div>
			<div class="ui-block-b"><div class="ui-bar ui-bar-e" >B</div></div>
			<div class="ui-block-c"><div class="ui-bar ui-bar-e" >C</div></div>
			<div class="ui-block-d"><div class="ui-bar ui-bar-e" >D</div></div>
			<div class="ui-block-e"><div class="ui-bar ui-bar-e" >E</div></div>
			<div class="ui-block-a"><div class="ui-bar ui-bar-e" >A</div></div>
			<div class="ui-block-b"><div class="ui-bar ui-bar-e" >B</div></div>
			<div class="ui-block-c"><div class="ui-bar ui-bar-e" >C</div></div>
			<div class="ui-block-d"><div class="ui-bar ui-bar-e" >D</div></div>
			<div class="ui-block-e"><div class="ui-bar ui-bar-e" >E</div></div>
			<div class="ui-block-a"><div class="ui-bar ui-bar-e" >A</div></div>
			<div class="ui-block-b"><div class="ui-bar ui-bar-e" >B</div></div>
			<div class="ui-block-c"><div class="ui-bar ui-bar-e" >C</div></div>
			<div class="ui-block-d"><div class="ui-bar ui-bar-e" >D</div></div>
			<div class="ui-block-e"><div class="ui-bar ui-bar-e" >E</div></div>
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

		<label for="{$this->id}_ss-control-system-speed">System speed</label>
		<input type="range" name="{$this->id}_ss-control-system-speed" id="{$this->id}_ss-control-system-speed" value="3" min="1" max="30" />
	</div>
	<div class="ui-block-b"><div id="{$this->id}_ss"></div></div>
</div>

EOHTML;

		$this->root->appendChild( \PhenLib\Template::HTMLtoDOM( $html ) );
	}

	public function getTitle()
	{
		return "HelioRoom";
	}
}
?>

