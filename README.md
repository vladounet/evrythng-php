evrythng-php
============

A very basic PHP wrapper for the evrythng.com API. Comes with a few sample applications. 


# Prerequisites

## Your system
You need a Web server with PHP 5 enabled. Also make sure you install cURL & json modules in PHP (php5-curl & php5-json).  

## 
You need to add a *config.php* file in your basic folder which must contain your account API Key. You can initialize it with the following content. 

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