# Context.IO

* [API Documentation](http://context.io/docs/2.0/)
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
define('CONSUMER_KEY', 'YOUR API CONSUMER KEY');
define('CONSUMER_SECRET', 'YOUR API CONSUMER SECRET');

// many calls are based for an Account - you can define a ACCOUNT_ID to make these calls
// the ACCOUNT_ID is returned in either the listAccounts call or the getAccount call
// you can also get this from the interactive console
define('ACCOUNT_ID', 'A CONTEXTIO ACCOUNT ID');

// instantiate the contextio object
$contextio = new ContextIO\ContextIO(CONSUMER_KEY, CONSUMER_SECRET);

// get a list of accounts and print the response data out
$r = $contextio->listAccounts();
print_r($r->getData());

// many calls are based on an account - you can define an ACCOUNT_ID to make these calls
// the ACCOUNT_ID is returned in either the listaccounts call or the getaccount call
// you can also get this from the interactive developer console
define('ACCOUNT_ID', 'A CONTEXTIO ACCOUNT ID');

// You also need to know the SOURCE_LABEL and FOLDER to list messages.
$r = $contextio->listSources(ACCOUNT_ID);
print_r($r->getData());

// You can see all the folders in an email account using the listEmailAccountFolders method
define('LABEL', 'A SOURCE LABEL');
$params = array('label'=>LABEL);
$r = $contextio->listSourceFolders(ACCOUNT_ID, $params);
print_r($r);

// Now that you know the ACCOUNT_ID, LABEL, and FOLDER you can list messages
define('FOLDER', 'A FOLDER NAME');
$params = array('label'=>LABEL, 'folder'=>FOLDER);
$r = $contextio->listMessagesBySourceAndFolder(ACCOUNT_ID, $params);
print_r($r);

// It's a good idea to do error handling on your api calls. You can get the last error response 
// from the client, and then retry the call
$x = 0;
while($x < 10) { //retry the call up to 10 times if it fails
	$r = $contextio->listAccounts();
	if($r != false) {
		print_r($r->getData());
		break;
	} else {
		print_r($contextio->getLastResponse());
		$x++;
		sleep(5); //don't retry immediately
	}
}

```

All methods are listed in src/ContextIO/ContextIO.php