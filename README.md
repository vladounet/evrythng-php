evrythng-php
============

A very basic PHP wrapper for the evrythng.com API. Comes with a few sample applications. 


## Prerequisites

### Your Web system
You need a Web server with PHP 5 enabled. Also make sure you install cURL and json modules in PHP (*php5-curl* & *php5-json*). Simply add this project to your base Web server URL, and you should be able to open [http://localhost/evrythng-php](http://localhost/evrythng-php).  

### Configuration file
You need to create a *config.php* file in the root folder which contains your account API Key (*evrythng.php* references it). You can initialize it with the following content: 

```
<?
	// This file defines the various API Keys

	// Your Account API Key (find it here: https://dashboard.evrythng.com/account)  
	$accountApiKey = '<YOUR ACCOUNT API KEY HERE>';

	// Your Application API Key (in your dashboard, "Projects" -> Select project -> "Setup")
	$appApiKey = '<YOUR APP API KEY HERE>';

	// You'll get this once you create a user via the API - you can specify one to make development easier
	$userApiKey = '';
?>
```