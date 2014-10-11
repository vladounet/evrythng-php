<?php

/* 
	EVRYTHNG Engine PHP wrapper v0.25 written Vlad Trifa - 10 Oct 2014
	WARNING - to use this you'll need to install php5-json & php5-curl, and enable short-codes for php (<? ?>) in apache. 
*/

#######
	## Basic Environment Setup
#######

// The base API endpoint of the EVRYTHNG Engine
$apiUrl = "https://api.evrythng.com";

// Contains the API Key for your own environment
include_once "config.php";

// set to true to see details in the console
define('DEBUG', true);


#######
	## PROXY MODE - FOR TESTING Purpose ONLY!!!! Risky to leave this accessible!
	## You should only expose some specific methods server-side that are tested
	## Is used to enable proxy mode (for testing/prototyping) - obviously commented out & not used in production 
	## Also this allows to use local jQuery requests that interact with a different API domain (so bypassing the same domain limitation)
#######

if ($_POST["operation"]){
	echo "Using proxy mode"; 
}


if ($_GET["proxy"]){
	if (!$_GET["debug"]){
		header('Content-Type: application/json');		
	}
		
	$property=$_GET["proxy"];
	$thngId=$_GET["thngId"];


	if ($_GET["serverTime"]=="true"){
		echo getPropertyPlotData($thngId,$property,true);
	} else {
		echo getPropertyPlotData($thngId,$property,false);
	}	
}

// Just a testing function, not to be used in prod
function getPropertyPlotData($thngId,$property,$servertime=true){
		$results = getThngProperty($thngId,$property);
		$rawData=json_decode($results,true);

		$data=array();
		
		if ($servertime==true){
			$time="createdAt";
		} else {
			$time="timestamp";
		}
		
		for ($i=0; $i < sizeof($rawData); $i++) {
			$value=$rawData[$i]["value"];
			if (is_numeric($value)){
				$data[$i]=array($rawData[$i][$time],(float)$rawData[$i]["value"]);		
			} else {
				$data[$i]=array($rawData[$i][$time],0);						
			}
		}	
		
		return json_encode($data);
}


#######
	## API calls (using curl library)
#######

# Sets the current context - the API key to use (Operator, App, or User)
function setContext($key) {
	global $apiKey;
	$apiKey = $key;
}


// This simply executes a request to the EVRYTHNG Endpoint
function sendRequest($url,$verb='GET',$data=NULL,$appId=NULL) {
	global $apiUrl,$apiKey;
	
	// Initializing cURL
	$ch = curl_init();

	// Setting curl options

	if ($appId==NULL) {
		curl_setopt($ch, CURLOPT_URL,$apiUrl.$url);
	} else {
		curl_setopt($ch, CURLOPT_URL,$apiUrl.$url."?app=".$appId);
	}

	curl_setopt($ch, CURLOPT_URL,$apiUrl.$url);
  	curl_setopt($ch, CURLOPT_USERAGENT, "PHP Wrapper v0.25");

  	//TODO: The problem when using cURL lib is that headers are empty, so we lose the actual headers of each individual browser, we should get them and fill them in

	// Set the verb
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $verb);

	DEBUG ? debug_to_console("TX --> payload (".$verb." ".$url.") ".$data) : null;


	// If we post data as well, we should add it here
	if (isset($data)){
		curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	}
  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
  	curl_setopt($ch, CURLOPT_HTTPHEADER,array(
		'Content-type: application/json',
		'Authorization: '.$apiKey,
		'Accept: application/json')
	);


/*	
	// Extra stuff for SSL - not really tested yet. 
	curl_setopt($ch, CURLOPT_URL,'https://graph.facebook.com/me/og.likes');
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
*/

	// Execute the request
	if(($result = curl_exec($ch)) === false){
	    echo 'Curl error: ' . curl_error($ch);
	} else {
	    //echo 'Operation completed without any errors';
	}

	if(curl_errno($ch)){
    	echo 'Request Error:' . curl_error($ch);
	}

	DEBUG ? debug_to_console("RX <-- payload (".$verb." ".$url.") ".$result) : null;
	
	// Close the connection
	curl_close($ch);

	//TODO:  Request success/fail check is not handled much

	return $result;
}




#######
	## THNGS
#######


# GET /thngs - retrieves the list of all Thngs
function getThngs() {
	$url = '/thngs';
	$result = sendRequest($url); 	
	return $result;
}

# POST /thngs - creates a new Thng
function createThng($data) {
	$url = '/thngs';
	$result =  sendRequest($url,'POST',$data); 	
	return $result;
}

# GET /thngs/[thngId] - returns an individual thng
function getThng($thngId) {
	$url = '/thngs/'.$thngId;
	$result = sendRequest($url); 	
	return $result;
}

# PUT /thngs/[thngId] - updates an individual thngs
function updateThng($thngId,$data) {
	$url = '/thngs/'.$thngId;
	$result = sendRequest($url,'PUT',$data); 	
	return $result;
}

# DELETE /thngs/[thngId]
function deleteThng($thngId) {
	$url = '/thngs/'.$thngId;
	$result = sendRequest($url,'DELETE'); 	
	return $result;
}

# GET /thngs/[thngId]/properties
function getThngProperties($thngId) {
	$url = '/thngs/'.$thngId.'/properties';
	$result = sendRequest($url); 	
	return $result;
}

# GET /thngs/[thngId]/properties
function getThngProperty($thngId,$property) {
	$url = '/thngs/'.$thngId.'/properties/'.$property;
	$result = sendRequest($url); 	
	return $result;
}

# GET /thngs/[thngId]/properties
function setThngProperty($thngId,$property,$data) {
	$url = '/thngs/'.$thngId.'/properties/'.$property;
	$result = sendRequest($url,'PUT',$data); 	
	return $result;
}

# DELETE /thngs/[thngId]/properties
function deleteThngProperty($thngId,$property) {
	$url = '/thngs/'.$thngId.'/properties/'.$property;
	$result = sendRequest($url,'DELETE'); 	
	return $result;
}

# GET /thngs/[thngId]/location
function getThngLocations($thngId) {
	$url = '/thngs/'.$thngId.'/location';
	$result = sendRequest($url); 	
	return $result;
}

#######
	## PRODUCTS
#######

# GET /products - retrieves the list of all Products
function getProducts() {
	$url = '/products';
	$result = sendRequest($url); 	
	return $result;
}

# POST /products/ - creates a new product
function createProduct($data) {
	$url = '/products';
	$result = sendRequest($url,'POST',$data);
	return $result;
}

# GET /products/[productId] - retieves an individual product
function getProduct($productId) {
	$url = '/products/'.$productId;
	$result = sendRequest($url);
	return $result;
}

# PUT /products/[productId] - updates an existing product
function updateProduct($productId,$data) {
	$url = '/products/'.$productId;
	$result = sendRequest($url,'PUT',$data);
	return $result;
}

# DELETE /products/[productId] - retieves an individual product
function deleteProduct($productId) {
	$url = '/products/'.$productId;
	$result = sendRequest($url,'DELETE');
	return $result;
}

# GET /products/[productId]/properties - retrieves the list of all properties of a product
function getProdProperties($thngId) {
	$url = '/products/'.$thngId.'/properties';
	$result = sendRequest($url); 	
	return $result;
}


#######
	## REDIRECTIONS
#######

# POST /thngs/[thngId]/redirector -- Creates a redirection for a thng
function createRedirection($thngId,$data) {
	$url = '/thngs/'.$thngId.'/redirector';
	$result = sendRequest($url,'POST',$data); 	
	return $result;
}


# GET /thngs/[thngId]/redirector  --  Reads the redirection of a thng (if any)
function getRedirection($thngId) {
	$url = '/thngs/'.$thngId.'/redirector';
	$result = sendRequest($url); 	
	return $result;
}

# PUT /thngs/[thngId]/redirector  --  Updates the redirection of a thng
function updateRedirection($thngId,$data) {
	$url = '/thngs/'.$thngId.'/redirector';
	$result = sendRequest($url,'PUT',$data); 	
	return $result;
}

# DELETE /thngs/[thngId]/redirector  -- Deletes the redirection of a thng
function deleteRedirection($thngId) {
	$url = '/thngs/'.$thngId.'/redirector';
	$result = sendRequest($url,'DELETE'); 	
	return $result;
}


#######
	## LOCATIONS
#######

# GET /thngs/[thngId]/location
function getLocations($thngId) {
	$url = '/thngs/'.$thngId.'/location';
	$result = sendRequest($url); 	
	return $result;
}

# PUT /thngs/[thngId]/location
function updateLocation($thngId,$data) {
	$url = '/thngs/'.$thngId.'/location';
	$result = sendRequest($url,'PUT',$data); 	
	return $result;
}

# DELETE /thngs/[thngId]/location
function deleteLocations($thngId) {
	$url = '/thngs/'.$thngId.'/location';
	$result = sendRequest($url,'DELETE'); 	
	return $result;
}


#######
	## COLLECTIONS
#######

# GET /collection
function getAllCollections() {
	$url = '/collections';
	$result = sendRequest($url); 	
	return $result;
}

# POST /collections
function createCollection($data) {
	$url = '/collections';
	$result = sendRequest($url,'POST',$data); 	
	return $result;
}

# GET /collections/{id}
function getCollection($collId) {
	$url = '/collections/'.$collId;
	$result = sendRequest($url); 	
	return $result;
}

# PUT /collections/{id}
function updateCollection($collId,$data) {
	$url = '/collections/'.$collId;
	$result =  sendRequest($url,'PUT',$data); 	
	return $result;
}

# DELETE /collections/{id}
function deleteCollection($collId) {
	$url = '/collections/'.$collId;
	$result = sendRequest($url,'DELETE'); 	
	return $result;
}

# GET /collections/ID/thngs
function getCollectionThngs($collId) {
	$url = '/collections/'.$collId.'/thngs';
	$result = sendRequest($url); 	
	return $result;
}



#######
	## APPLICATIONS
#######

# GET /applications
function getApplications() {
	$url = '/applications';
	$result = sendRequest($url); 	
	return $result;
}

# POST /collections
function createApplication($data) {
	$url = '/applications';
	$result = sendRequest($url,'POST',$data); 	
	return $result;
}

# GET /applications/{id}
function getApplication($appId) {
	$url = '/applications/'.$appId;
	$result = sendRequest($url); 	
	return $result;
}

# PUT /applications/{id}
function updateApplication($appId,$data) {
	$url = '/applications/'.$appId;
	$result = sendRequest($url,'PUT',$data); 	
	return $result;
}

# DELETE /applications/{id}
function deleteApplication($appId) {
	$url = '/applications/'.$appId;
	$result = sendRequest($url,'DELETE'); 	
	return $result;
}



#######
	## USERS - You MUST use an APP API Key for these calls
#######


# POST /auth/evrythng/users   --- Create a new EVRYTHNG User in an APP
function createEvtUser($data) {
	$url = '/auth/evrythng/users';
	$result = sendRequest($url,'POST',$data); 	
	return $result;
}


# POST /users/X/validate   --- Validates a new EVRYTHNG user in an APP
function validateEvtUser($userId,$data) {
	$url = '/auth/evrythng/users/'.$userId.'/validate';
	$result = sendRequest($url,'POST',$data); 	
	return $result;
}


# POST /auth/evrythng/users   --- Create a new application
# loginDocument={"email":"XXX","password":"YYY"}
function loginEvtUser($data) {
	$url = '/auth/evrythng/';
	$result = sendRequest($url,'POST',$data); 	
	return $result;
}

# POST /auth/facebook FB user 
# {"access": {"expires" : <Timestamp>,"token"": &lt;Facebook-Token&gt;}}
function createFbUser($data) {
	$url = '/auth/facebook';
	$result = sendRequest($url,'POST',$data); 	
	return $result;
}


# POST /logout -- Logs out the current user - MUST be done using the User API Key
function logoutUser() {
	$url = '/auth/all/logout';
	$result = sendRequest($url,'POST'); 	
	return $result;
}


# GET /users/X -- reads data about 1 user
function getUser($userId) {
	$url = '/users/'.$userId;
	$result = sendRequest($url); 	
	return $result;
}

# GET /users/ -- reads all users in a given app (or all apps)
function getAllUsers($appId=NULL) {
	$url = '/users';	
	$result = sendRequest($url); 	
	return $result;
}


#######
	## ACTIONS
#######

# GET /actions  -- Reads the list of all action types
function getActionTypes() {
	$url = '/actions';
	$result = sendRequest($url); 	
	return $result;
}


# POST /actions -- Creates a new action Type
function createActionType($data) {
	$url = '/actions';
	$result = sendRequest($url,'POST',$data); 	
	return $result;
}


# GET /actions/{type} -- Get all actions of a given type
function getActions($actionType) {
	$url = '/actions/'.$actionType;
	$result = sendRequest($url); 	
	return $result;
}


# POST /actions/{type}  -- Creates a new action 
function createAction($actionType,$data) {
	$url = '/actions/'.$actionType;
	$result = sendRequest($url,'POST',$data); 	
	return $result;
}


#######
	## HELPERS
#######

function debug_to_console($data) {
    if (is_array($data))
        $output = "<script>console.log('evrythng.php: ".implode(',', $data)."');</script>";
    else
        $output = "<script>console.log('evrythng.php: ".$data."');</script>";
    echo $output;
}


?>