HelioRoomPlanetTable.colors =
[
	{ colorName: "Blue",	color: "#0000ee" },
	{ colorName: "Orange",	color: "#ff6600" },
	{ colorName: "Green",	color: "#00ee00" },
	{ colorName: "Red",	color: "#cc0000" },
	{ colorName: "Purple",	color: "#9900cc" },
	{ colorName: "Brown",	color: "#8b4513" },
	{ colorName: "Pink",	color: "#ffb6c1" },
	{ colorName: "Gray",	color: "#3d3d3d" },
	{ colorName: "Yellow",	color: "#ffff00" },
	{ colorName: "White",	color: "#ffffff" }
];

function HelioRoomPlanetTable( model, container, idPrefix )
{
	//save reference to data model
	this.model = model;
	this.idPrefix = idPrefix;

	//create table
	this.rows = new Array( HelioRoomSimulation.MAX_PLANETS );
	for( var x = 0; x < this.rows.length; x++ )
	{
		var row = this.rows[x] = 
		{
			"name": $( '<input type="text" />' ), 
			"color": $( '<select />' ), 
			"orbitTime": $( '<input type="text" />' ), 
			"inRoom": $( '<input type="text" />' ), 
			"status": $( '<input type="text" />' ),
		};

		var idPrefixRow = this.idPrefix + "ss-container-planet-table_row-" + (x+1) + "_";

		//name
		var div = $( "<div />" ).attr( "class", "ui-block-a" );
		div.append( '<label for="' + idPrefixRow + 'name" class="ui-hidden-accessible">Name ' + (x+1) + '</label>' );
		div.append( row.name.attr( "id", idPrefixRow + "name" ).attr( "data-mini", "true" ) );
		container.append( div );

		//color
		var div = $( "<div />" ).attr( "class", "ui-block-b" );
		div.append( '<label for="' + idPrefixRow + 'color" class="ui-hidden-accessible">Color ' + (x+1) + '</label>' );
		div.append( row.color.attr( "id", idPrefixRow + "color" ).attr( "data-mini", "true" ).attr( "data-native-menu", "false" ) );
		for( var y = 0; y < HelioRoomPlanetTable.colors.length; y++ )
			row.color.append( $( '<option value="' + HelioRoomPlanetTable.colors[y].color.toLowerCase() + '">' +
						HelioRoomPlanetTable.colors[y].colorName + '</option>' ) );
		container.append( div );

		//orbit time
		var div = $( "<div />" ).attr( "class", "ui-block-c" );
		div.append( '<label for="' + idPrefixRow + 'orbitTime" class="ui-hidden-accessible">Orbital period ' + (x+1) + '</label>' );
		div.append( row.orbitTime.attr( "id", idPrefixRow + "orbitTime" ).attr( "data-mini", "true" ) );
		container.append( div );

		//in room
		var div = $( "<div />" ).attr( "class", "ui-block-d" );
		div.append( '<label for="' + idPrefixRow + 'inRoom" class="ui-hidden-accessible">In the room ' + (x+1) + '</label>' );
		div.append( row.inRoom.attr( "id", idPrefixRow + "inRoom" ).attr( "data-mini", "true" ) );
		container.append( div );

		//status
		var div = $( "<div />" ).attr( "class", "ui-block-e" );
		div.append( '<label for="' + idPrefixRow + 'status" class="ui-hidden-accessible">Status ' + (x+1) + '</label>' );
		div.append( row.status.attr( "id", idPrefixRow + "status" ).attr( "data-mini", "true" ) );
		container.append( div );
	}
	container.trigger( "create" );

	//hide table
	for( var x = 0; x < this.rows.length; x++ )
		for( col in this.rows[x] )
			this.rows[x][col].parent().hide();
};

HelioRoomPlanetTable.prototype.redraw = function()
{
	//called when config is updated
	for( var x = 0; x < this.rows.length; x++ )
	{
		var row = this.rows[x];

		//hide rows beyond current planets
		if( x >= this.model.planetCount )
		{
			for( col in row )
				row[col].parent().hide();
			continue;
		}

		//show row
		for( col in row )
			row[col].parent().show();

		//setup row
		row.name.val( this.model.planets[x].name );
		row.color.val( this.model.planets[x].color );
		row.color.selectmenu("refresh");
		row.orbitTime.val( this.model.planets[x].orbitTime );
		row.inRoom.val( "..." );
		row.status.val( "status" );
	}
};
