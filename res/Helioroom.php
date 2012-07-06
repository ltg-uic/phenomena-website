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
function HelioRoomSolarSystem( container_id )
{
	HelioRoomSolarSystem.svgns = "http://www.w3.org/2000/svg";

	//create svg canvas
	this.svg = document.createElementNS( HelioRoomSolarSystem.svgns, "svg" );
	this.svg.setAttribute( "width", "500" );
	this.svg.setAttribute( "height", "500" );
	this.svg.setAttribute( "viewbox", "0 0 500 500" );
	this.svg.setAttribute( "preserveAspectRatio", "xMidYMid meet" );

	this.sectors = document.createElementNS( HelioRoomSolarSystem.svgns, "g");
	this.rings = document.createElementNS( HelioRoomSolarSystem.svgns, "g");
	this.svg.appendChild( this.sectors );
	this.svg.appendChild( this.rings );
	document.getElementById( container_id ).appendChild( this.svg );

	this.windows = 4;
	this.degrees = 45;
}

HelioRoomSolarSystem.prototype.setWindows = function( windows )
{
	if( windows < 4 || windows > 8 )
		return false;
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
	if( this.windows * degrees > 360 )
		return false;
	this.degrees = degrees;
	return true;
};

HelioRoomSolarSystem.prototype.getViewAngle = function()
{
	return this.degrees;
};

HelioRoomSolarSystem.prototype.drawSectors = function()
{
	//remove sectors
	while( this.sectors.lastChild )
		this.sectors.removeChild( this.sectors.lastChild );

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
	for(var x = 0; x < this.windows * 2; x++)
	{
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
	        
		// We describe a wedge with an <svg:path> element
		// Notice that we create this with createElementNS()
		var path = document.createElementNS( HelioRoomSolarSystem.svgns, "path");
        
		// This string holds the path details
		var d = "M " + cx + "," + cy +  // Start at circle center
			" L " + x1 + "," + y1 +     // Draw line to (x1,y1)
			" A " + r + "," + r +       // Draw an arc of radius r
			" 0 " + big + " 1 " +       // Arc details...
			x2 + "," + y2 +             // Arc goes to to (x2,y2)
			" Z";                       // Close path back to (cx,cy)

	        // This is an XML element, so all attributes must be set
	        // with setAttribute().  We can't just use JavaScript properties
		path.setAttribute("d", d);              // Set this path 
		path.setAttribute("fill", colors[x%2]);   // Set wedge color
		path.setAttribute("class", "{$this->id}_ss-sector" );
		this.sectors.appendChild(path);               // Add wedge to canvas

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

$(document).on( "pageinit", function()
	{
		var ss = new HelioRoomSolarSystem( "{$this->id}_ss" );

		var control_windows = $("#{$this->id}_ss-control-windows");
		var control_view_angle = $("#{$this->id}_ss-control-view-angle");

		control_windows.on( "change", function( event, ui )
		{
			if( ! ss.setWindows( control_windows[0].value ) )
				control_windows.slider( 'refresh', ss.getWindows(), true );
			else
				control_view_angle.slider( 'refresh', ss.getViewAngle(), true );
				ss.drawSectors();
		});

		control_view_angle.on( "change", function( event, ui )
		{
			if( ! ss.setViewAngle( control_view_angle[0].value ) )
				control_view_angle.slider( 'refresh', ss.getViewAngle(), true );
			else
				ss.drawSectors();
		});

		ss.setWindows( control_windows[0].value );
		ss.setViewAngle( control_view_angle[0].value );
		ss.drawSectors();
		ss.drawRings();
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

