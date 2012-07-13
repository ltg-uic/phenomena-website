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
//TODO - build svg as html with php due to anim bug?
	//static var
	HelioRoomSolarSystem.svgns = "http://www.w3.org/2000/svg";
	HelioRoomSolarSystem.xlns = "http://www.w3.org/1999/xlink";

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
	this.svg.setAttributeNS( null, "version", "1.1" );
	this.svg.setAttributeNS( null, "width", "500" );
	this.svg.setAttributeNS( null, "height", "500" );
	this.svg.setAttributeNS( null, "viewbox", "0 0 500 500" );
	this.svg.setAttributeNS( null, "preserveAspectRatio", "xMidYMid meet" );
	document.getElementById( container_id ).appendChild( this.svg );

	//background circle
	var bg = document.createElementNS( HelioRoomSolarSystem.svgns, "circle" );
	bg.setAttributeNS( null, "cx", 250 );
	bg.setAttributeNS( null, "cy", 250 );
	bg.setAttributeNS( null, "r", 250 );
	bg.setAttributeNS( null, "stroke-width", "0" );
	bg.setAttributeNS( null, "fill", "#000000" );
	this.svg.appendChild( bg );

	//create svg group for sectors, reference array for sectors
	var sector_group = document.createElementNS( HelioRoomSolarSystem.svgns, "g");
	this.svg.appendChild( sector_group );
	this.sectors = new Array(8);
	for( var x = 0; x < 8; x++ )
	{
		this.sectors[x] = document.createElementNS( HelioRoomSolarSystem.svgns, "path");
		sector_group.appendChild( this.sectors[x] );
	}
		
	//create svg group for rings
	this.rings = document.createElementNS( HelioRoomSolarSystem.svgns, "g");
	this.svg.appendChild( this.rings );

	//draw things
	this.drawSectors();
	this.drawRings();

}

HelioRoomSolarSystem.prototype.test = function()
{
	//animation stuff
	var circle = document.createElementNS( HelioRoomSolarSystem.svgns, "circle" );
	circle.setAttributeNS( null, "r", 5 );
	circle.setAttributeNS( null, "fill", "#FF0000" );
	
	var animMotion = document.createElementNS( HelioRoomSolarSystem.svgns, "animateMotion" );
	animMotion.setAttributeNS( null, "dur", "6s" );
//	animMotion.setAttributeNS( null, "fill", "freeze" );
	animMotion.setAttributeNS( null, "repeatCount", "indefinite" );

	var mpath = document.createElementNS( HelioRoomSolarSystem.svgns, "mpath" );
	mpath.setAttributeNS( HelioRoomSolarSystem.xlns, "xlink:href", "#sector1");

	animMotion.appendChild( mpath );
	circle.appendChild( animMotion );
	this.svg.appendChild( circle );	
//	animMotion.beginElement();
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
	for(var x = 0; x < 8; x++)
	{
		//get reference to path
		var path = this.sectors[x];

		//hide sectors not needed for this many windows
		if( x >= this.windows )
		{
			path.setAttributeNS( null, "fill-opacity", "0" );
			continue;
		}

		// This is where the wedge ends
		var endangle = startangle + angles[0];
	
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
		path.setAttributeNS( null, "d", d );              // Set this path 
		path.setAttributeNS( null, "fill", "#292729" );   // Set wedge color
		path.setAttributeNS( null, "fill-opacity", "1" );
		path.setAttributeNS( null, "id", "sector" + x );

		// The next wedge begins where this one ends
		startangle = endangle + angles[1];
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
		circle.setAttributeNS( null, "cx", "250" );
		circle.setAttributeNS( null, "cy", "250" );
		circle.setAttributeNS( null, "r", ( (x+1) / 11 ) * 250 );
		circle.setAttributeNS( null, "stroke", "#5B5B5B" );
		circle.setAttributeNS( null, "stroke-width", "1" );
		circle.setAttributeNS( null, "fill", "#FFFFFF" );
		circle.setAttributeNS( null, "id", "orbit" + x );
		if( x == 0 )
			circle.setAttributeNS( null, "fill-opacity", "1" );
		else
			circle.setAttributeNS( null, "fill-opacity", "0" );
		this.rings.appendChild(circle);
	}
}

//TODO - make not global
var ss;
		//init helioroom solar system
//once page is ready
$(document).on( "pageinit", function()
	{
		//references to controls for windows and view angle
		var control_windows = $("#{$this->id}_ss-control-windows");
		var control_view_angle = $("#{$this->id}_ss-control-view-angle");

		//init helioroom solar system
		ss = new HelioRoomSolarSystem( "{$this->id}_ss", control_windows.prop( "value" ), control_view_angle.prop( "value" ) );
			ss.test();

		//bind event when windows changes
		control_windows.on( "change", function( event, ui )
		{
//TODO - auto update max angle bound
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
	<div class="ui-block-b" id="{$this->id}_ss">
	</div>
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

