# This version of the API will be partially deprecated June 15th, 2018 and completely deprecated December 15th, 2018

[https://blog.context.io/important-announcement-deprecation-of-2-0-api-8f73488a8c0e](READ MORE HERE)

# Context.IO

* [API Documentation](https://docs.context.io)
* [API Explorer](https://console.context.io/#explore)
* [Sign Up](http://context.io)

## Description

A PHP Client Library for [Context.IO](http://context.io). 

## Requirements

PHP Curl (http://php.net/curl)

## Install using Composer

You can install the library by adding it as a dependency to your composer.json.

```
"require": {
  "contextio/php-contextio": ">=2.*"
}
```

## Examples

```php
// include Composer's autoloader
include_once("vendor/autoload.php");

// define your API key and secret - find this https://console.context.io/#settings
$apiKey = 'YOUR API KEY';
$apiSecret = 'YOUR API SECRET';

// many calls are based for an Account - you can define a ACCOUNT_ID to make these calls
// the ACCOUNT_ID is returned in either the listAccounts call or the getAccount call
// you can also get this from the interactive console
$accountId = 'A CONTEXTIO ACCOUNT ID';

// instantiate the contextio object
$contextio = new ContextIO\ContextIO($apiKey, $apiSecret);

// get a list of accounts and print the response data out
$response = $contextio->listAccounts();
print_r($response->getData());

// You also need to know the SOURCE_LABEL and FOLDER to list messages.
$response = $contextio->listSources($accountId);
print_r($response->getData());

// You can see all the folders in an email account using the listEmailAccountFolders method
$label = 'A SOURCE LABEL';
$params = array('label'=> $label);
$response = $contextio->listSourceFolders($accountId, $params);
print_r($response);

// Now that you know the ACCOUNT_ID, LABEL, and FOLDER you can list messages
$folder = 'A FOLDER NAME';
$params = array('label'=> $label, 'folder'=> $folder);
$response = $contextio->listMessagesBySourceAndFolder($accountId, $params);
print_r($response);

// It's a good idea to do error handling on your api calls. You can get the last error response 
// from the client, and then retry the call
$x = 0;
while($x < 10) { //retry the call up to 10 times if it fails
	$response = $contextio->listAccounts();
	if($reponse != false) {
		print_r($response->getData());
		break;
	} else {
		print_r($contextio->getLastRequest()->getLastResponse());
		$x++;
		sleep(5); //don't retry immediately
	}
}

// You can set a request timeout, after which a ContextIO\cURLException will be thrown if 
// no response has been received from the API
$contextio->getLastRequest()->setRequestTimeout(10);

```

All methods are listed in src/ContextIO/ContextIO.php
