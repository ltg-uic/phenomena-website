//static vars
HelioRoomSolarSystem.svgns = "http://www.w3.org/2000/svg";
HelioRoomSolarSystem.xlns = "http://www.w3.org/1999/xlink";
HelioRoomSolarSystem.errorBox = document.createElementNS( HelioRoomSolarSystem.svgns, "text" );

function HelioRoomSolarSystem( model, container, id_prefix )
{
	//save reference to data model
	this.model = model;
	this.idPrefix = id_prefix;

	//init
	this.error = false;
	this.windows = new Array( HelioRoomSimulation.MAX_WINDOWS );
	this.orbits = new Array( HelioRoomSimulation.MAX_PLANETS );
	this.planets = new Array( HelioRoomSimulation.MAX_PLANETS );

	//create svg canvas
	this.svg = document.createElementNS( HelioRoomSolarSystem.svgns, "svg" );
	this.svg.setAttributeNS( null, "version", "1.1" );
	this.svg.setAttributeNS( null, "width", "500" );
	this.svg.setAttributeNS( null, "height", "500" );
	this.svg.setAttributeNS( null, "viewbox", "0 0 500 500" );
	this.svg.setAttributeNS( null, "preserveAspectRatio", "xMidYMid meet" );

	//background circle
	var bg = document.createElementNS( HelioRoomSolarSystem.svgns, "circle" );
	bg.setAttributeNS( null, "cx", 250 );
	bg.setAttributeNS( null, "cy", 250 );
	bg.setAttributeNS( null, "r", 250 );
	bg.setAttributeNS( null, "stroke-width", "0" );
	bg.setAttributeNS( null, "fill", "#000000" );
	this.svg.appendChild( bg );

	//init windows, orbits, planets
	this.initWindows();
	this.initOrbits();
	this.initPlanets();

	//create error message box
	HelioRoomSolarSystem.errorBox.setAttributeNS( null, "x", 250 );
	HelioRoomSolarSystem.errorBox.setAttributeNS( null, "y", 250 );
	HelioRoomSolarSystem.errorBox.setAttributeNS( null, "text-anchor", "middle" );
	HelioRoomSolarSystem.errorBox.setAttributeNS( null, "stroke", "red" );

	//add to dom
	container.get(0).appendChild( this.svg );
}

HelioRoomSolarSystem.prototype.redraw = function()
{
	//remove / hide elements
	if( this.error == true )
		this.svg.removeChild( HelioRoomSolarSystem.errorBox );

	//validate
	if( this.model.windowCount * this.model.windowDegrees > 360 )
	{
		this.error = true;
		HelioRoomSolarSystem.errorBox.textContent = "ERROR: windows X degrees cannot exceed 360";
		this.svg.appendChild( HelioRoomSolarSystem.errorBox );
		return null;
	}

	//draw things
	this.drawWindows();
	this.drawOrbits();
	this.drawPlanets();
}

HelioRoomSolarSystem.prototype.initWindows = function()
{
	//create svg group and save references for windows
	var group = document.createElementNS( HelioRoomSolarSystem.svgns, "g" );
	this.svg.appendChild( group );
	for( var x = 0; x < HelioRoomSimulation.MAX_WINDOWS; x++ )
	{
		var path = this.windows[x] = document.createElementNS( HelioRoomSolarSystem.svgns, "path" );
		group.appendChild( path );
		path.setAttributeNS( null, "fill", "#292729" );
	}
}

HelioRoomSolarSystem.prototype.drawWindows = function()
{
	//*** adapted from:
	//    http://jmvidal.cse.sc.edu/talks/canvassvg/javascriptandsvg.xml ***//

	//cx, cy, r: the center and radius of the pie
	var cx = 250;
	var cy = 250;
	var r = 250;

	//angle of sectors
	var hidden_degrees = (360 - this.model.windowCount * this.model.windowDegrees) / this.model.windowCount;
	var angles = [ this.model.windowDegrees / 360 * Math.PI * 2, hidden_degrees / 360 * Math.PI * 2 ];
	
	// Loop through each slice of pie.
	startangle = 0;
	for( var x = 0; x < HelioRoomSimulation.MAX_WINDOWS; x++ )
	{
		//get reference to path
		var path = this.windows[x];

		//hide windows elements greater than model count
		if( x >= this.model.windowCount )
		{
			path.setAttributeNS( null, "fill-opacity", "0" );
			continue;
		}
		path.setAttributeNS( null, "fill-opacity", "1" );

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
		path.setAttributeNS( null, "d", "M " + cx + "," + cy +  // Start at circle center
						" L " + x1 + "," + y1 +     // Draw line to (x1,y1)
						" A " + r + "," + r +       // Draw an arc of radius r
						" 0 " + big + " 1 " +       // Arc details...
						x2 + "," + y2 +             // Arc goes to to (x2,y2)
						" Z" );                    // Close path back to (cx,cy)

		// The next wedge begins where this one ends
		startangle = endangle + angles[1];
	}	
};

HelioRoomSolarSystem.prototype.initOrbits = function()
{
	//create svg group and save references for windows
	var group = document.createElementNS( HelioRoomSolarSystem.svgns, "g" );
	this.svg.appendChild( group );
	for( var x = 0; x < HelioRoomSimulation.MAX_PLANETS; x++ )
	{
		var path = this.orbits[x] = document.createElementNS( HelioRoomSolarSystem.svgns, "path");
		group.appendChild( path );
		path.setAttributeNS( null, "stroke", "#5B5B5B" );
		path.setAttributeNS( null, "stroke-width", "1" );
		if( x == 0 )
		{
			path.setAttributeNS( null, "fill", "yellow" );
			path.setAttributeNS( null, "fill-opacity", "1" );
		}
		else
		{
			path.setAttributeNS( null, "fill-opacity", "0" );
			path.setAttributeNS( null, "id", this.idPrefix + "ss-orbit-" + x );
		}
	}
}

HelioRoomSolarSystem.prototype.drawOrbits = function()
{
	for( var x = 0; x < HelioRoomSimulation.MAX_PLANETS; x++ )
	{
		var path = this.orbits[x];

		//hide orbit elements greater than model count
		if( x-1 >= this.model.planetCount )
		{
			path.setAttributeNS( null, "color", "transparent" );
			continue;
		}

		var r = (x+1) / (this.model.planetCount+2) * 250;
		
		var angle1 = 0;
		if( x > 0 )
			angle1 = this.model.planets[x-1].angleStart;
		var angle2 = angle1 + 180;
		angle1 = angle1 / 360 * Math.PI * 2;
		angle2 = angle2 / 360 * Math.PI * 2;
		var x1 = 250 + r * Math.sin(angle1);
		var y1 = 250 - r * Math.cos(angle1);
		var x2 = 250 + r * Math.sin(angle2);
		var y2 = 250 - r * Math.cos(angle2);
		path.setAttributeNS( null, "d", "M " + x1 + " " + y1 +
						" A " + r + " " + r + " 0 0 0 " + x2 + " " + y2 +
						" A " + r + " " + r + " 0 0 0 " + x1 + " " + y1
						);
	}
};

HelioRoomSolarSystem.prototype.initPlanets = function()
{
	//create svg group and save references for windows
	var group = document.createElementNS( HelioRoomSolarSystem.svgns, "g" );
	this.svg.appendChild( group );
	for( var x = 0; x < HelioRoomSimulation.MAX_PLANETS; x++ )
	{
		var circle = this.planets[x] = document.createElementNS( HelioRoomSolarSystem.svgns, "circle" );
		group.appendChild( circle );

		var animMotion = document.createElementNS( HelioRoomSolarSystem.svgns, "animateMotion" );
		animMotion.setAttributeNS( null, "begin", "0s" );
		animMotion.setAttributeNS( null, "dur", "0s" );
		animMotion.setAttributeNS( null, "repeatCount", "indefinite" );

		var mpath = document.createElementNS( HelioRoomSolarSystem.svgns, "mpath" );
		mpath.setAttributeNS( HelioRoomSolarSystem.xlns, "xlink:href", "#" + this.idPrefix + "ss-orbit-" + (x+1) );

		animMotion.appendChild( mpath );
		circle.appendChild( animMotion );
		circle.animMotion = animMotion;
	}
};

HelioRoomSolarSystem.prototype.drawPlanets = function()
{
	for( var x = 0; x < HelioRoomSimulation.MAX_PLANETS; x++ )
	{
		var circle = this.planets[x];

		//hide circle elements greater than model count
		if( x >= this.model.planetCount )
		{
			circle.setAttributeNS( null, "color", "transparent" );
			continue;
		}

		var planet = this.model.planets[x];
		circle.setAttributeNS( null, "r", 250 / (this.model.planetCount+2) / 2 );
		circle.setAttributeNS( null, "fill", planet.color );
	
		//set orbital time
		circle.animMotion.setAttributeNS( null, "dur", planet.orbitTime / this.model.speedFactor + "min" );
	}
};

HelioRoomSolarSystem.prototype.updateOrbitSpeeds = function()
{
//TODO - this function doesn't quite work correctly, fix
//TODO - check how they want this to adjust speed
	for( var x=0; x<this.model.planetCount; x++ )
		this.planets[x].animMotion.setAttributeNS( null, "dur", this.model.planets[x].orbitTime / this.model.speedFactor + "min" );
};
