//static config vars
HelioRoomSimulation.MAX_WINDOWS = 8;
HelioRoomSimulation.MAX_PLANETS = 10;

//constructor
function HelioRoomSimulation( container_planet_table, container_solar_system, control_windows, control_view_angle, control_system_speed, id_prefix )
{
	//references
	this.containerPlanetTable = container_planet_table;
	this.containerSolarSystem = container_solar_system;
	this.controlWindows = control_windows;
	this.controlViewAngle = control_view_angle;
	this.controlSystemSpeed = control_system_speed;
	this.idPrefix = id_prefix;

	//simulation shared model
	this.model = 
	{
		"windowCount": 0,
		"windowDegrees": null,
		"windows": null,
		"planetCount": 0,
		"planets": null,
		"speedFactor": 1
	};

	//simulation objects
	this.ss = new HelioRoomSolarSystem( this.model, this.containerSolarSystem, this.idPrefix );
	this.pt = new HelioRoomPlanetTable( this.model, this.containerPlanetTable );

	this.registerHandlers();
};

//refresh config via ajax
HelioRoomSimulation.prototype.getConfig = function()
{
	//send request
	$.ajax(
	{
		url: "getConfig",
		type: "GET",
		datatype: "xml",
		context: this,
		complete: function( jqXHR, status )
			{
				if( jqXHR.status === 200 ) 
					this.loadConfig( jqXHR.responseText );
			}
	} ).fail( function()
		{
			//TODO - add fail case
		});

};

//load config
HelioRoomSimulation.prototype.loadConfig = function( xmlConfig )
{
	var xml = $( $.parseXML( xmlConfig ) );

	var windowsRef = this.model.windows = new Array();
	$(xml).find( 'win' ).each( function()
	{
		windowsRef.push(
		{
			"id": $(this).attr("id"),
			"angleStart": parseInt($(this).find("viewAngleBegin").text()),
			"angleEnd": parseInt($(this).find("viewAngleEnd").text())
		});
	});
	this.model.windowCount = windowsRef.length;
	this.model.windowDegrees = ( windowsRef.length >= 1 ) ? ( windowsRef[0].angleStart - windowsRef[0].angleEnd ) : null;

	var planetsRef = this.model.planets = new Array();
	$(xml).find( 'planet' ).each( function()
	{
		planetsRef.push(
		{
			"name": $(this).find("name").text(),
			"color": "#" + $(this).find("color").text().substring( 4 ).toLowerCase(),
			"colorName": $(this).find("colorName").text(),
			"orbitTime": parseInt($(this).find("classOrbitalTime").text()),
			"angleStart": parseInt($(this).find("startPosition").text())
		});
	});
	this.model.planetCount = planetsRef.length;

	console.log( "xml config model:" );
	console.log( this.model );

	//init / update helioroom solar system
	this.ss.redraw();
	this.pt.redraw();
};

HelioRoomSimulation.prototype.setWindows = function( windows )
{
	//out of bounds from ui specification
	if( windows < 4 || windows > 8 )
		return false;

	//update values
	this.model.windowCount = windows;
	this.model.windowDegrees = 180 / windows;
	return true;
};

HelioRoomSimulation.prototype.setViewAngle = function( degrees )
{
	//windows * degrees bigger than the circle
	if( this.model.windowCount * degrees > 360 )
		return false;

	//update value
	this.model.windowDegrees = degrees;
	return true;
};

HelioRoomSimulation.prototype.registerHandlers = function()
{
	//bind event when windows changes
	this.controlWindows.on( "change", $.proxy( function( event, ui )
	{
		//fast fail if windows is not changing by a integer number
		if( this.controlWindows.prop( "value" ) == this.model.windowCount )
			return;

		//if change fails, constrain slider to proper bound
		if( ! this.setWindows( this.controlWindows.prop( "value" ) ) )
			this.controlWindows.slider( 'refresh', this.model.windowCount, true );
		//if change succeeds, update angle slider to new value+max and redraw ss
		else
		{
			this.controlViewAngle.attr( "max", Math.floor( 360 / this.model.windowCount ) );
			this.controlViewAngle.slider( 'refresh', this.model.windowDegrees, true );
			this.ss.drawWindows();
		}
	}, this ) );


	//bind event when view angle changes
	this.controlViewAngle.on( "change", $.proxy( function( event, ui )
	{
		//fast fail if angle is not changing by a integer number
		if( this.controlViewAngle.prop( "value" ) == this.model.windowDegrees )
			return;

		//do change and redraw (bounds enforced automatically
		this.setViewAngle( this.controlViewAngle.prop( "value" ) );
		this.ss.drawWindows();
	}, this ) );

	//bind event when system speed changes
	this.controlSystemSpeed.on( "change", $.proxy( function( event, ui )
	{
		if( this.controlSystemSpeed.prop( "value" ) == ( this.model.speedFactor * 100 ) )
			return;

		this.model.speedFactor = this.controlSystemSpeed.prop( "value" ) / 100;
		this.ss.updateOrbitSpeeds();
	}, this ) );
};
