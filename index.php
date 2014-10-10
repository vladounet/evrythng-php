<!DOCTYPE html>
<html lang="en">

<head>
	<title>EVRYTHNG</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
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
		<a href="/" class="brand">EVRYTHNG Thng Viewer</a>
	</div>

	<div class="container">

			<?
			// include the EVRYTHNG API wrapper
			include_once "evrythng.php";
			setContext($accountApiKey);
		
			// Used for time conversion, by default all data in our 
			date_default_timezone_set('Europe/London');

			?>
			
			<h1>API General</h1>

			<h3>API Key</h3>
			<input type="text" id="api-key" size="100" value=<? echo "\"".$apiKey."\""; ?> />

			<h3>List of Thngs owned</h3>
			<form accept-charset="UTF-8" action="index.php" class="simple_form form-inline" method="get" novalidate="novalidate">
				<div style="margin:0;padding:0;display:inline"></div>
				<div class="input select required">
					<select class="select required" name="thng_id">
				
					<?

					// This gets the list of all the thngs created with this API key and displays it in a drop-down box
					$results = getThngs();
					$thngs=json_decode($results);

					for ($i=0; $i<sizeof($thngs); $i++) {
						echo "<option value=\"".$thngs[$i]->{'id'}."\">".$thngs[$i]->{'name'}." (".$thngs[$i]->{'id'}.")"."</option>\n";
					}
					?>
					</select>
					<input class="btn btn-primary" name="commit" value="Display this thng" type="submit">
				</div>	
			</form>

			<br><br><br>

	<h1>Thng Details</h1>

	<?

	if($thng_id=$_GET["thng_id"]) { // If a thng_id has been specified using the parameter "thng_id", then get & display the data of the thng
		$results = getThng($thng_id);
		$thng=json_decode($results);		
		?>
		
		<table class="table table-striped table-bordered table-condensed">
			<tbody>

				<tr>
					<th>Full JSON</th>
					<td><? echo $results; ?></td>
				</tr>

				<tr>
					<th>Thng ID</th>
					<td><? echo $thng_id; ?></td>
				</tr>

				<tr>
					<th>Name</th>
					<td><? echo $thng->{'name'}; ?> </td>
				</tr>
				<tr>
					<th>Description</th>
					<td><? echo $thng->{'description'}; ?></td>
				</tr>
				<tr>
					<th>Created At</th>
					<td><?
					// Time stamps in the engine are stored in milliseconds, so need to convert into seconds to get epochs
					echo date('r', $thng->{'createdAt'}/1000)." (UNIX Timestamp: ".$thng->{'createdAt'}.")"; 
					?></td>
				</tr>
				<tr>
					<th>Updated At</th>
					<td><? echo date('r', $thng->{'updatedAt'}/1000)." (UNIX Timestamp: ".$thng->{'updatedAt'}.")"; ?></td>
				</tr>
				<tr>
					<th>Product ID</th>
					<td><? 
					if ($thng->{'product'}){ // some thngs don't have a product
						$product_id=$thng->{'product'};
						echo "<a href=\"index.php?product_id=".$product_id."\">".$product_id."</a>";
					} 
					?></td>
				</tr>
				<tr>
					<th>Tags</th>
					<td><? echo "[\"".implode('","', $thng->{'tags'})."\"]"; ?></td>
				</tr>
				<tr>
					<th>Last Seen: </th>
					<td> 
						<table class="table table-bordered">
							<tr>
								<th>Longitude: </th>
								<td><? echo $thng->{'location'}->{'longitude'}; ?></td>
							</tr>
							<tr>
								<th>Latitude: </th>
								<td> <? echo $thng->{'location'}->{'latitude'}; ?></td>
							</tr>
							<tr>
								<th>Time: </th>
								<td><? echo $thng->{'location'}->{'timestamp'}; ?></td>
							</tr>

						</table> 
					</td>
				</tr>

				<tr>
					<th>Properties</th>
					<td> 
						<table class="table table-bordered">
						
						<?
						// now we get all the properties of that thng
						$results = getThngProperties($thng_id);
						$thng_props=json_decode($results);

						for ($i=0; $i<sizeof($thng_props); $i++) {
							//$res = isset($arr[$i]);
							echo "<tr><th>".$i."</th><td> ".$thng_props[$i]->{'key'}."</td><td>".$thng_props[$i]->{'value'}."</td></tr>";
						}
						?>							
					</table> 
					
					<h4>Property Browser</h4> 
					
					<p>
					 <label for="unittype">Select unit type:</label>
  				   <select class="select required" id="thng_property_display" name="thng_property_display" onChange="updateGraph(this.value)">
 						   <?
 							   // This gets the list of all the thngs created with this API key and displays it in a drop-down box
 							   // Ideally get only the thngs in the right collection
				
 							   for ($i=0; $i<sizeof($thng_props); $i++) {
 								   if ($_GET['property']==$thng_props[$i]->{'key'}){
 									   echo "<option value=\"".$thng_props[$i]->{'key'}."\" selected=\"selected\">".$thng_props[$i]->{'key'}."</option>\n";							
 								   } else {							
 									   echo "<option value=\"".$thng_props[$i]->{'key'}."\">".$thng_props[$i]->{'key'}."</option>\n";
 								   }
 							   }
 							?>
 					</select>
					</p>
					
					
					<div id="container" style="height: 500px; min-width: 500px"></div>
					
				</td>
			</tr>
			
		</tbody>
	</table>
			
		
		
		<? 				
//		$property="distance";
//		$results = getPropertyPlotData($thng_id,$property);
//			$rawData=json_decode($results);
//			var_dump($rawData);		
		?>
		
	
		
		<script src="files/highstock.js"></script>
		<script src="http://code.highcharts.com/stock/modules/exporting.js"></script>

		<script>
				
		function updateGraph(property){
			
			var thng_id = '<?php echo $thng_id; ?>';
			
			var json = (function() {
			        var json = null;
			        $.ajax({
			            'async': false,
			            'global': false,
			            'url': "evrythng.php?proxy="+property+"&thng_id="+thng_id+"&serverTime=true",
			            'dataType': "json",
			            'success': function (data) {
			                json = data;
			            }
			        });
			        return json;
			    })();
					
			//alert(json);
				
			plotProperty(json,property);		
		}
		
		function plotPropertyStock(data,label) {
						// Create the chart
						$('#container').highcharts('StockChart', {
			

							rangeSelector : {
								selected : 1
							},
							

							title : {
								text : label
							},
			
							series : [{
								name : label,
								data : data,
								tooltip: {
									valueDecimals: 2
								}
							}]
						});
					}
		
		
					function plotProperty(data,label) {
									// Create the chart
									$('#container').highcharts({
			

										rangeSelector : {
											selected : 1
										},
										
										xAxis: {
  		                type: 'datetime',
			                dateTimeLabelFormats: { // don't display the dummy year
		                    month: '%e. %b',
		                    year: '%b'
			                }
				            },
							

										title : {
											text : label
										},
			
										series : [{
											name : label,
											data : data,
											tooltip: {
												valueDecimals: 2
											}
										}]
									});
								}
		
	
		$(function() {
			/* 
				Get this data remotely, not tested - just playing around
			 	Obiously we should use more jquery here, not optimal to call it from
			 	php. Ideally, graphs can be easily refreshed only using jQuery, either 
			 	directly from the server, or from the local php (hence the wrapper proxy mode)

			*/
			var json = (function() {
			        var json = null;
			        $.ajax({
			            'async': false,
			            'global': false,
			            'url': "rawData.json",
			            'dataType': "json",
			            'success': function (data) {
			                json = data;
			            }
			        });
			        return json;
			    })();


			plotProperty(json,'Temperature')
			
			
		});
		
		</script>

		
		
	

	<h2>Redirection</h2>

	<div>
		<p> 
			<?
				
				//echo $results;
				
				// Check here if there is a delete redirection request
				if ($_POST['delete_redirection']){
					echo "Redirection deleted: /thngs/".$thng_id."/redirection".deleteRedirection($thng_id);
				} elseif ($_POST['create_redirection']){
					$data = array("defaultRedirectUrl" => "http://localhost/~trifa/quickstart/index.php?thng_id={thngId}&redirected=1");                                                                    
					$data_string = json_encode($data);
					echo "<br><b>Data sent:</b>".$data_string;
					echo "<br><b>Redirection created:</b>".createRedirection($thng_id,$data_string)."<br>";
				} 

				$results = getRedirection($thng_id);
				$redirection = json_decode($results);
				
				if ($redirection->{'status'}==404){
					echo "<br><b>No redirection set yet.</b> Click the button below to create one!";
					?>
					<form method="post" action=""> 
						<input type="submit" name="create_redirection" value="Create one!"> 
					</form>
										
					<?
					
				} else {
					
					?>
					<table class="table table-striped table-bordered table-condensed">
						<tbody>
							<tr>
								<th>Created At</th>
								<td><?
								// Time stamps in the engine are stored in milliseconds, so need to convert into seconds to get epochs
								echo date('r', $redirection->{'createdAt'}/1000)." (".$redirection->{'createdAt'}.")"; 
								?></td>
							</tr>
							<tr>
								<th>Last Updated At</th>
								<td><? echo date('r', $redirection->{'updatedAt'}/1000)." (".$redirection->{'updatedAt'}.")"; ?></td>
							</tr>
							<tr>
								<th>Short URL</th>
								<td><? 
									$shortURL="http://".$redirection->{'shortDomain'}."/".$redirection->{'shortId'};
									echo "<a href=\"$shortURL\">".$shortURL."</a>"; 
								?></td>
							</tr>	
							<tr>
								<th>Target URL</th>
								<td><? 
									$targetURL=$redirection->{'defaultRedirectUrl'};
									echo "<a href=\"$targetURL\">".$targetURL."</a>"; 
								?></td>
							</tr>	
							<tr>
								<th>Hits on Short URL</th>
								<td><? echo $redirection->{'hits'}; ?></td>
							</tr>	
							<tr>
								<th>QR code</th>
								<td><?
								 	// Let's display the QR code image (display directly instead of downloading the file)	
									echo "<img src=\"http://".$redirection->{'shortDomain'}."/".$redirection->{'shortId'}.".png?w=150&h=150&caption=none\">"; 
								?></td>
							</tr>	
						</tbody>
					</table>
					
					<form method="post" action=""> 
						<input type="submit" name="delete_redirection" value="Delete the redirection"> 
					</form>
							
						
					<?
					
				}
				
			?>
		</p>
	</div>

	
	<h2>Thng Location</h2>

	<div id="map" class="smallmap"></div>

	<form onsubmit="return false;">
	       <input type="submit" onclick="plotLocations(); return false;" value="Plot all locations on map" onsubmit="plotLocations(); return false;">
	</form>

	<h3>All Locations</h3>
	<table class="table table-striped table-bordered table-condensed">
		<tbody>
			<?
		// Now we get all the locations of that thng
		$results = getThngLocations($thng_id);
		$locations=json_decode($results);
		
		?>
		
		<script>
			// Create a js object of all the locations that is used by plotLocations() for putting them on the map
			// Unlike other requests we're using JS to parse the locations (so the data is available in JS and can be plotted, etc.)			
			var locations = JSON.parse('<?php echo $results; ?>');
		</script>

		<?
		echo "<tr><th></th><td>Timestamp</td><td>Long</td><td>Lat</td><td>customFields</td></tr>";
		
		for ($i=0; $i<sizeof($locations); $i++) {
			echo "<tr><th>".$i."</th><td> ".date('r', $locations[$i]->{'timestamp'}/1000)."</td><td>".$locations[$i]->{'longitude'}."</td><td>".$locations[$i]->{'latitude'}."</td><td>".json_encode($locations[$i]->{'customFields'})."</td></tr>";
		}
		?>
		

		</tbody>
	</table>


	<? 
}  else  { // if no thng_id as param ?>
	
	<div>The thng you select does not belong to a product.</div>

<? 
} // End of the if(thng_id)
?>


<h1>Product Details</h1>

<?
if ($_GET["product_id"]){  // If a product_id variable is given, then display that product	
	$product_id=$_GET["product_id"];
}

// The product view is displayed only if a product is available			
if ($product_id){ // get product info	
	$results = getProduct($product_id);
	$product=json_decode($results);		
	?>

	<table class="table table-striped table-bordered table-condensed">
		<tbody>
			<tr>
				<th>Full JSON</th>
				<td><? echo $results; ?></td>
			</tr>
			<tr>
				<th>Product ID</th>
				<td><? echo $product_id; ?></td>
			</tr>	
			<tr>
				<th>Name</th>
				<td><? echo $product->{'fn'}; ?> </td>
			</tr>
			<tr>
				<th>Description</th>
				<td><? echo $product->{'description'}; ?></td>
			</tr>
			<tr>
				<th>Created At</th>
				<td><?
				echo date('r', $product->{'createdAt'}/1000)." (".$product->{'createdAt'}.")"; 
				?></td>
			</tr>
			<tr>
				<th>Updated At</th>
				<td><? echo date('r', $product->{'updatedAt'}/1000)." (".$product->{'updatedAt'}.")"; ?></td>
			</tr>
			<tr>
				<th>Categories</th>
				<td><? echo "[\"".implode('","', $product->{'categories'})."\"]"; ?></td>
			</tr>
			<tr>
				<th>Tags</th>
				<td><?  echo "[\"".implode('","', $product->{'tags'})."\"]"; ?></td>
			</tr>
			<tr>
				<th>Photos</th>
				<td><? echo "[\"".implode('","', $product->{'photos'})."\"]"; ?></td>
			</tr>

			<tr>
				<th>Properties </th>
				<td> 
					<table class="table table-bordered">
						<?
					// now we get all the properties of that Product
					//$results = getProdProps($product_id);
					//$prod_props=json_decode($results);

					for ($i=0; $i<sizeof($prod_props); $i++) {
						//$res = isset($arr[$i]);
						echo "<tr><th>".$i."</th><td> ".$prod_props[$i]->{'key'}."</td><td>".$prod_props[$i]->{'value'}."</td></tr>";
					}
					?>							
				</table> 
			</td>
		</tr>

		<tr>
			<th>Identifiers</th>
			<td> 
				<table class="table table-bordered">
					<?

				$i=0;
				foreach($product->{'identifiers'} as $key => $value) {
					echo "<tr><th>".$i."</th><td> ".$key."</td><td>".$value."</td></tr>";
					$i=$i+1;
				}
				?>							
			</table> 
		</td>
	</tr>


</tbody>
</table>

<?} else { // If no product select ?>
	
	<div>The thng you select does not belong to a product.</div>

<?	} // End of the product display?>

</div>
</body>
</html>