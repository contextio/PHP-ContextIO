#!/usr/bin/php
<?php
// remove first line above if you're not running these examples through PHP CLI


include_once("class.contextio.php");

// see https://console.context.io/#settings to get your consumer key and consumer secret.

$contextIO = new ContextIO('consumerKeyHere','consumerSecretHere');

// This is optional
$contextIO->useAuthorizationHeaders(true);

// Specify the mailbox we'll get data from. That mailbox must be added to your
// API key through https://console.context.io or ContextIO->imap_addAccount()
$mailboxToQuery = 'jim@acme.com';


// EXAMPLE 1
// Print the subject line of the last 20 emails sent to with bill@widgets.com
$args = array('to'=>'bill@widgets.com', 'limit'=>20);
echo "\nGetting last 20 messages exchanged with {$args['to']}\n";
$r = $contextIO->contactMessages($mailboxToQuery, $args);
foreach ($r->getData() as $message) {
	echo "Subject: ".$message['subject']."\n";
}

// EXAMPLE 2
// Download all versions of the last 2 attachments exchanged with bill@widgets.com

$saveToDir = dirname(__FILE__)."/".mt_rand(100,999);
mkdir($saveToDir);

$args = array('email'=>'bill@widgets.com', 'limit'=>2);
$r = $contextIO->contactFiles($mailboxToQuery, $args);
foreach ($r->getData() as $document) {
	echo "\nDownloading all versions of document \"".$document['fileName']."\"\n";
	foreach ($document['occurrences'] as $attachment) {
		echo "Downloading attachment '".$attachment['fileName']."' to $saveToDir ... ";
		$contextIO->downloadFile($mailboxToQuery, array('fileId'=>$attachment['fileId']), $saveToDir."/".$attachment['fileName']);
		echo "done\n";
	}
}

// EXAMPLE 3
// Download all attachments with a file name that contains the word 'proposal'

$saveToDir = dirname(__FILE__)."/".mt_rand(100,999);
mkdir($saveToDir);

echo "\nDownloading all attachments matching 'proposal'\n";
$args = array('filename'=>'proposal');
$r = $contextIO->fileSearch($mailboxToQuery, $args);
foreach ($r->getData() as $attachment) {
	echo "Downloading attachment '".$attachment['fileName']."' to $saveToDir ... ";
	$contextIO->downloadFile($mailboxToQuery, array('fileId'=>$attachment['fileId']), $saveToDir."/".$attachment['fileName']);
	echo "done\n";
}

?>
