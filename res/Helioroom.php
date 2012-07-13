<?php
namespace Phen;

class HelioRoom extends \PhenLib\Page
{
	public function generateOutput()
	{
		$colorPickerButtonSize = "50px;";

		$rootDoc = \PhenLib\Template::getDOC();
		$root = $this->root;

		$html = <<<EOHTML
<script type="text/javascript">
<!--
function HelioRoomSolarSystem( container_id, control_windows, control_view_angle, control_system_speed )
{
	//static var
	HelioRoomSolarSystem.svgns = "http://www.w3.org/2000/svg";
	HelioRoomSolarSystem.xlns = "http://www.w3.org/1999/xlink";

	//init values
	this.control_windows = control_windows;
	this.control_view_angle = control_view_angle;
	this.control_system_speed = control_system_speed;
	this.windows = control_windows.prop( 'value' );
	this.degrees = control_view_angle.prop( 'value' );
	this.speed = control_system_speed.prop( 'value' );

	//create svg canvas
	this.svg = document.createElementNS( HelioRoomSolarSystem.svgns, "svg" );
	this.svg.setAttributeNS( null, "version", "1.1" );
	this.svg.setAttributeNS( null, "width", "500" );
	this.svg.setAttributeNS( null, "height", "500" );
	this.svg.setAttributeNS( null, "viewbox", "0 0 500 500" );
	this.svg.setAttributeNS( null, "preserveAspectRatio", "xMidYMid meet" );
	document.getElementById( container_id ).appendChild( this.svg );

	//validate
	if( this.windows * this.degrees > 360 )
	{
		var errorText = document.createElementNS( HelioRoomSolarSystem.svgns, "text" );
		errorText.setAttributeNS( null, "x", 250 );
		errorText.setAttributeNS( null, "y", 250 );
		errorText.setAttributeNS( null, "text-anchor", "middle" );
		errorText.setAttributeNS( null, "stroke", "red" );
		errorText.textContent = "ERROR: windows X degrees cannot exceed 360";
		this.svg.appendChild( errorText );
		return null;
	}

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

	//array for planets
	this.planets = new Array(9);
		
	//create svg group for rings
	this.rings = document.createElementNS( HelioRoomSolarSystem.svgns, "g");
	this.svg.appendChild( this.rings );

	//draw things
	this.drawWindows();
	this.drawOrbits();
	this.drawPlanets();

	this.registerHandlers();
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

HelioRoomSolarSystem.prototype.drawWindows = function()
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

HelioRoomSolarSystem.prototype.drawOrbits = function()
{
	//remove rings
	while( this.rings.lastChild )
		this.rings.removeChild( this.rings.lastChild );

	for( var x = 0; x < 10; x++ )
	{
		var path = document.createElementNS( HelioRoomSolarSystem.svgns, "path" );
		var r = (x+1) / 11 * 250;
		var angle1 = Math.floor( Math.random() * 360 );
		var angle2 = angle1 + 180;
		angle1 = angle1 / 360 * Math.PI * 2;
		angle2 = angle2 / 360 * Math.PI * 2;
		var x1 = 250 + r * Math.sin(angle1);
		var y1 = 250 - r * Math.cos(angle1);
		var x2 = 250 + r * Math.sin(angle2);
		var y2 = 250 - r * Math.cos(angle2);
		path.setAttributeNS( null, "d", "M " + x1 + " " + y1 + " " + 
						"A " + r + " " + r + " 0 0 1 " + x2 + " " + y2 + " " +
						"A " + r + " " + r + " 0 0 1 " + x1 + " " + y1
						);
		path.setAttributeNS( null, "stroke", "#5B5B5B" );
		path.setAttributeNS( null, "stroke-width", "1" );
		path.setAttributeNS( null, "fill", "#FFFFFF" );

		if( x == 0 )
			path.setAttributeNS( null, "fill-opacity", "1" );
		else
		{
			path.setAttributeNS( null, "fill-opacity", "0" );
			path.setAttributeNS( null, "id", "orbit" + x );
		}
		this.rings.appendChild(path);
	}
};

HelioRoomSolarSystem.prototype.drawPlanets = function()
{
	var colors = new Array( "brown", "pink", "red", "gray", "green", "yellow", "orange", "blue", "purple" );

	for( var x = 1; x<10; x++ )
	{
		var circle = document.createElementNS( HelioRoomSolarSystem.svgns, "circle" );
		circle.setAttributeNS( null, "r", 8 );
		circle.setAttributeNS( null, "fill", colors[x-1] );
	
		var animMotion = document.createElementNS( HelioRoomSolarSystem.svgns, "animateMotion" );
		var baseDuration = Math.floor( Math.random() * 120 ) + 15;
		animMotion.setAttributeNS( null, "begin", "0s" );
		animMotion.setAttributeNS( null, "dur", baseDuration / this.speed + "s" );
		animMotion.setAttributeNS( null, "repeatCount", "indefinite" );

		var mpath = document.createElementNS( HelioRoomSolarSystem.svgns, "mpath" );
		mpath.setAttributeNS( HelioRoomSolarSystem.xlns, "xlink:href", "#orbit" + x );

		animMotion.appendChild( mpath );
		circle.appendChild( animMotion );
		this.svg.appendChild( circle );	
		this.planets[x-1] = new Array( animMotion, baseDuration );
	}
};

HelioRoomSolarSystem.prototype.updateOrbitSpeeds = function()
{
	for( var x=0; x<9; x++ )
		this.planets[x][0].setAttributeNS( null, "dur", this.planets[x][1] / this.speed + "s" );
};

HelioRoomSolarSystem.prototype.registerHandlers = function()
{
		//for access inside closures
		//TODO - use bind() to scope these properly?
		var ss = this;
		var cw = this.control_windows;
		var cva = this.control_view_angle;
		var css = this.control_system_speed;

		//bind event when windows changes
		cw.on( "change", function( event, ui )
		{
			//fast fail if windows is not changing by a integer number
			if( cw.prop( "value" ) == ss.getWindows() )
				return;

			//if change fails, constrain slider to proper bound
			if( ! ss.setWindows( cw.prop( "value" ) ) )
				cw.slider( 'refresh', ss.getWindows(), true );
			//if change succeeds, update angle slider to new value+max and redraw ss
			else
			{
				cva.prop( "max", Math.floor( 360 / ss.getWindows() ) );
				cva.slider( 'refresh', ss.getViewAngle(), true );
				ss.drawWindows();
			}
		});

		//bind event when view angle changes
		cva.on( "change", function( event, ui )
		{
			//fast fail if angle is not changing by a integer number
			if( cva.prop( "value" ) == ss.getViewAngle() )
				return;

			//do change and redraw (bounds enforced automatically
			ss.setViewAngle( cva.prop( "value" ) )
			ss.drawWindows();
		});

		//bind event when system speed changes
		css.on( "change", function( event, ui )
		{
			if( css.prop( "value" ) == ss.speed )
				return;

			ss.speed = css.prop( "value" );
			ss.updateOrbitSpeeds();
		});
}


function ColorPicker( )
{
        ColorPicker.picker = $('#colorPicker');
        ColorPicker.pickerCells = $('#colorPicker td');
        ColorPicker.pickerCells.on('click', this.run);
}

ColorPicker.prototype.attach = function(selector) {
        $(selector).on( 'click', this.open );
        $(selector).on( 'keypress', function(e) { e.preventDefault(); } );
}

ColorPicker.prototype.open = function(e)
{
        ColorPicker.picker.popup("open");
        ColorPicker.input = $(e.currentTarget);
}

ColorPicker.prototype.run = function()
{
        //console.log(this);
        var color = $(this).css('background-color');
        //console.log(color);
        ColorPicker.input.css('background-color',color);
        ColorPicker.input.css('color', "#000");
        ColorPicker.input.css('font-weight', "bold");
        var colorName = ($(this).attr('id')).split("_")[1]
        ColorPicker.input.val(colorName);
        ColorPicker.picker.popup("close");
}


//once page is ready
$(document).one( "pageinit", function()
	{
		//references to controls for windows and view angle
		var control_windows = $("#{$this->id}_ss-control-windows");
		var control_view_angle = $("#{$this->id}_ss-control-view-angle");
		var control_system_speed = $("#{$this->id}_ss-control-system-speed");

		//init helioroom solar system
		var ss = new HelioRoomSolarSystem( "{$this->id}_ss", control_windows, control_view_angle, control_system_speed );

		var cp = new ColorPicker();
		cp.attach(".colorPickerInput");
	} );
-->
</script>
<div class="ui-grid-a">
	<div class="ui-block-a">
		<h2>Your Planetary System</h2>
		<div class="ui-grid-d">
			<div class="ui-block-a"><b>Name</b></div>
			<div class="ui-block-b"><b>Color</b></div>
			<div class="ui-block-c"><b>Orbital period<br />(Earth days)</b></div>
			<div class="ui-block-d"><b>In the room</b></div>
			<div class="ui-block-e"><b>Status</b></div>
			<div class="ui-block-a">Mercury</div>
			<div class="ui-block-b"><input type="text" class="colorPickerInput" value="color" data-mini="true" /></div>
			<div class="ui-block-c">88</div>
			<div class="ui-block-d">5 minutes</div>
			<div class="ui-block-e">Leaves W2 in 3:13</div>
			<div class="ui-block-a">A</div>
			<div class="ui-block-b"><input type="text" class="colorPickerInput" value="color" data-mini="true" /></div>
			<div class="ui-block-c">C</div>
			<div class="ui-block-d">D</div>
			<div class="ui-block-e">E</div>
			<div class="ui-block-a">A</div>
			<div class="ui-block-b"><input type="text" class="colorPickerInput" value="color" data-mini="true" /></div>
			<div class="ui-block-c">C</div>
			<div class="ui-block-d">D</div>
			<div class="ui-block-e">E</div>
			<div class="ui-block-a">A</div>
			<div class="ui-block-b"><input type="text" class="colorPickerInput" value="color" data-mini="true" /></div>
			<div class="ui-block-c">C</div>
			<div class="ui-block-d">D</div>
			<div class="ui-block-e">E</div>
			<div class="ui-block-a">A</div>
			<div class="ui-block-b"><input type="text" class="colorPickerInput" value="color" data-mini="true" /></div>
			<div class="ui-block-c">C</div>
			<div class="ui-block-d">D</div>
			<div class="ui-block-e">E</div>
			<div class="ui-block-a">A</div>
			<div class="ui-block-b"><input type="text" class="colorPickerInput" value="color" data-mini="true" /></div>
			<div class="ui-block-c">C</div>
			<div class="ui-block-d">D</div>
			<div class="ui-block-e">E</div>
			<div class="ui-block-a">A</div>
			<div class="ui-block-b"><input type="text" class="colorPickerInput" value="color" data-mini="true" /></div>
			<div class="ui-block-c">C</div>
			<div class="ui-block-d">D</div>
			<div class="ui-block-e">E</div>
			<div class="ui-block-a">A</div>
			<div class="ui-block-b"><input type="text" class="colorPickerInput" value="color" data-mini="true" /></div>
			<div class="ui-block-c">C</div>
			<div class="ui-block-d">D</div>
			<div class="ui-block-e">E</div>
			<div class="ui-block-a">A</div>
			<div class="ui-block-b"><input type="text" class="colorPickerInput" value="color" data-mini="true" /></div>
			<div class="ui-block-c">C</div>
			<div class="ui-block-d">D</div>
			<div class="ui-block-e">E</div>
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
<div data-role="popup" id="colorPicker">
        <h2>Choose a Color</h2>
        <table>
                <tr>
                        <td id="colorPicker_blue" style="width: {$colorPickerButtonSize} height: {$colorPickerButtonSize} background: #1E67E6;" />
                        <td id="colorPicker_orange" style="width: {$colorPickerButtonSize} height: {$colorPickerButtonSize} background: #FF7F0E;" />
                        <td id="colorPicker_green" style="width: {$colorPickerButtonSize} height: {$colorPickerButtonSize} background: #30B330;" />
                        <td id="colorPicker_red" style="width: {$colorPickerButtonSize} height: {$colorPickerButtonSize} background: #ED0E10;" />
                        <td id="colorPicker_purple" style="width: {$colorPickerButtonSize} height: {$colorPickerButtonSize} background: #A873D9;" />
                </tr>
                <tr>
                        <td id="colorPicker_brown" style="width: {$colorPickerButtonSize} height: {$colorPickerButtonSize} background: #96655A;" />
                        <td id="colorPicker_pink" style="width: {$colorPickerButtonSize} height: {$colorPickerButtonSize} background: #F582D2;" />
                        <td id="colorPicker_gray" style="width: {$colorPickerButtonSize} height: {$colorPickerButtonSize} background: #A4A4A4;" />
                        <td id="colorPicker_yellow" style="width: {$colorPickerButtonSize} height: {$colorPickerButtonSize} background: #FCF921;" />
                        <td id="colorPicker_white" style="width: {$colorPickerButtonSize} height: {$colorPickerButtonSize} background: #FFFFFF;" />
                </tr>
        </table>
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

