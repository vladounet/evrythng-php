<!DOCTYPE html>
<html lang="en">

<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
	<title>EVRYTHNG</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="files/custom.css" media="all" type="text/css">
    <link rel="stylesheet" href="theme/default/style.css" type="text/css">
    <link rel="stylesheet" href="theme/style.css" type="text/css">
	<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
	<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
   
	<script src="lib/OpenLayers.js"></script>
    <script type="text/javascript">
        var map, layer;

		// This is the layer of Markers
		var vectorLayer = new OpenLayers.Layer.Vector("Overlay");

		//Add a selector control to the vectorLayer with popup functions
		var controls = {selector: new OpenLayers.Control.SelectFeature(vectorLayer, { onSelect: createPopup, onUnselect: destroyPopup })};

        function init(){

	        OpenLayers.ProxyHost = "/proxy/?url=";
		
            map = new OpenLayers.Map('map', {maxResolution:'auto'});
            layer = new OpenLayers.Layer.WMS( "OpenLayers WMS","http://vmap0.tiles.osgeo.org/wms/vmap0", {layers: 'basic'} );
            map.addLayer(layer);
            map.setCenter(new OpenLayers.LonLat(0, 0), 0);
            map.addControl(new OpenLayers.Control.LayerSwitcher());
		 	map.addControl(controls['selector']);
		    controls['selector'].activate();		
        }

		/*
			This is a much cleaner solution, but the engine doesn't support GeoRSS at this point. 
		*/
        function addUrl() {
            var urlObj = OpenLayers.Util.getElement('url');
            var value = urlObj.value;
            var parts = value.split("/");
                var newl = new OpenLayers.Layer.GeoRSS( parts[parts.length-1], value);
                map.addLayer(newl);
            urlObj.value = "";
        }


		/* 
			Obviously, this is not a clean solution. One could call the locations to display directly 
			from JS on the fly. The point is to illustrate how to use both JS and PHP apps to create more
			interactive displays and experiences around real-time data. 
			
		*/	
		function plotLocations(){
			
			// For each location update
			for (var i = 0; i < locations.length; i++) {
			    var location = locations[i];
				var lon=location.longitude;
				var lat=location.latitude;
				var desc = "<h3>Marker #" + i +"</h3><br><b>Timestamp: </b>" + new Date(location.timestamp)+"<br>"; 
				desc = desc + "<b>Long: </b>"+ lon +", <b>Lat: </b>"+ lat;"<br><br><h3>Custom Fields</h3><br>";
				
				for (var key in location.customFields) {
				  if (location.customFields.hasOwnProperty(key)) {
					desc = desc + "<br><b>"+key+": </b><em>"+location.customFields[key]+"</em>";
				  }
				}
							
				// Add a marker to the map (& a popup with it)
				var feature = new OpenLayers.Feature.Vector(
			            new OpenLayers.Geometry.Point( lon, lat ),
			            {description: desc},
			            {externalGraphic: 'img/marker.png', graphicHeight: 25, graphicWidth: 21, graphicXOffset:-12, graphicYOffset:-25  }
			        );    
			    vectorLayer.addFeatures(feature);

			}
						
			// Now add the layer to the map
		    map.addLayer(vectorLayer);
			
		}
		
		function createPopup(feature) {
	      feature.popup = new OpenLayers.Popup.FramedCloud("pop",
	          feature.geometry.getBounds().getCenterLonLat(),
	          null,
	          '<div class="markerContent">'+feature.attributes.description+'</div>',
	          null,
	          true,
	          function() { controls['selector'].unselectAll(); }
	      );
	      //feature.popup.closeOnMove = true;
	      map.addPopup(feature.popup);
	    }

	    function destroyPopup(feature) {
	      feature.popup.destroy();
	      feature.popup = null;
	    }


    </script> 
</head>
<body onload="init(); plotLocations()">
	<div id="masthead">
		<a href="/" class="brand">Basic App Example</a>
	</div>

	<div class="container">

			<?
			// include the EVRYTHNG API wrapper
			include_once "evrythng.php";
		
			// Used for time conversion, by default all data in our 
			date_default_timezone_set('Europe/London');


			// Set app or user
			if($appId=$_GET["appId"]) { // If an app is selected, set the App as context
				$results = getApplication($appId);
				$currentApp=json_decode($results);
				setContext($results->{'appApiKey'});
			} else {
				// If no app is selected, then let's switch to Account API Key
				setContext($accountApiKey);
			}


			// Create an action
			if($_GET["doAction"]) {

				$action = array();

				// Set the action type
				$action["type"] = $_GET["actionType"];

				// If a thngId is given, set it 
				if (strcmp($_GET["thngId"], "none")) {
					$action["thng"] = $_GET["thngId"];
				}
				
				// If a productId is given
				if (strcmp($_GET["productId"], "none")) {
					$action["product"] = $_GET["productId"];
				}
				
				// We should check the location (as GET vars)
				$action["location"]["position"]=array('type' => 'Point', 'coordinates' => array("10","-11"));
				$action["locationSource"] = "sensor";

				// If you want to acc custom fields:
				//$action["customFields"]["boom"]="YESS";
				//$action["customFields"]["trying?"]="NOPES";
				
				// If you want to add tags:
				//$action["tags"] = array("test","phpApp");
				$data = json_encode($action);

				// Ideally send the action

				// Send the action, ideally validate the response
				$results = createAction($_GET["actionType"],$data);
				$newAction=json_decode($results);

			}

			?>
			
			<h1>Simple Application</h1>
			<p>This simple page shows you the basics of how to build a php application. It allows you to pick an application, login/logout as an user and then do actions in that application</p>

			<br><br>
			
			<h3>Context</h3>

			<div>Choose an application:</div>
			<form accept-charset="UTF-8" action="app.php" class="simple_form form-inline" id="apps" method="get" novalidate="novalidate">
				<div style="margin:0;padding:0;display:inline"></div>
				<div class="input select required">
					<select class="select required" id="bottle_destination" name="appId">
				
						<?
						// We get the list of all Applications in that account & display them
						$results = getApplications();
						$apps=json_decode($results);

						for ($i=0; $i<sizeof($apps); $i++) {
							echo "<option value=\"".$apps[$i]->{'id'}."\">".$apps[$i]->{'name'}." (".$apps[$i]->{'id'}.")"."</option>\n";
						}
						?>
					</select>
					<input class="btn btn-primary" name="commit" value="Set this application" type="submit">
				</div>	
			</form>
			<br>
			<div>Current API Key</div>
			<input type="text" id="api-key" size="100" value=<? echo "\"".$apiKey."\""; ?> />

			<br><br>

			<h3>User login/signup</h3>
			<div>Here you can create, login, logout, with users. </div>

			<input class="btn btn-primary" name="createAnonUser" value="Create EVT User" type="submit">
			<input class="btn btn-primary" name="loginEvtUser" value="Login EVT User" type="submit">

			<input class="btn btn-primary" name="createAnonUser" value="Create Anon User" type="submit">
			<input class="btn btn-primary" name="loginAnonUser" value="Login Anon User" type="submit">

			<br><br>

			<h3>Action Creator</h3>
			<form accept-charset="UTF-8" action="app.php" class="simple_form form-inline" id="new_bottle" method="get" novalidate="novalidate">

				<div style="margin:0;padding:0;display:inline"></div>
				<div class="input select required">
					<div>Choose thng</div>
					<select class="select required" id="thng-select" name="thngId">
						<?
						// This gets the list of all the thngs created with this API key and displays it in a drop-down box
						$results = getThngs();
						$thngs=json_decode($results);

						for ($i=0; $i<sizeof($thngs); $i++) {
							echo "<option value=\"".$thngs[$i]->{'id'}."\">".$thngs[$i]->{'name'}." (".$thngs[$i]->{'id'}.")"."</option>\n";
						}
						?>
					</select>
				</div>
				<br><br><br>	
				<div class="input select required">
					<div>Choose Product</div>
					<select class="select required" id="thng-select" name="productId">
						<option value="none">No Product</option>
						<?
						// Gets the list of all products in the account
						$results = getProducts();
						$products=json_decode($results);

						for ($i=0; $i<sizeof($products); $i++) {
							echo "<option value=\"".$products[$i]->{'id'}."\">".$products[$i]->{'fn'}." (".$products[$i]->{'id'}.")"."</option>\n";
						}
						?>
					</select>
				</div>
				<br><br><br>	
				<div class="input select required">
					<div>Action type</div>
					<select class="select required" id="actionSelector" name="actionType">
				
						<?
						// Get the list of all action types available for the app
						$results = getActionTypes();
						$types=json_decode($results);

						for ($i=0; $i<sizeof($types); $i++) {
							echo "<option value=\"".$types[$i]->{'name'}."\">".$types[$i]->{'name'}."</option>\n";
						}
						?>
					</select>

					<br><br>
					<input class="btn btn-primary" name="doAction" value="Send this action" type="submit">
				</div>	
			</form>

			<br><br><br>

</div>
</body>
</html>