<?php
/*
Copyright (C) 2011 DokDok Inc.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

/**
 * Context.IO API client library
 * @copyright Copyright (C) 2011 DokDok Inc.
 * @licence http://opensource.org/licenses/mit-license MIT Licence
 */

require_once dirname(__FILE__) . '/class.contextioresponse.php';
require_once dirname(__FILE__) . '/OAuth.php';

/**
 * Class to manage Context.IO API access
 */
class ContextIO {

	protected $responseHeaders;
	protected $requestHeaders;
	protected $oauthKey;
	protected $oauthSecret;
	protected $saveHeaders;
	protected $ssl;
	protected $endPoint;
	protected $apiVersion;
	protected $lastResponse;
	protected $authHeaders;

	/**
	 * Instantiate a new ContextIO object. Your OAuth consumer key and secret can be
	 * found under the "settings" tab of the developer console (https://console.context.io/#settings)
	 * @param $key Your Context.IO OAuth consumer key
	 * @param $secret Your Context.IO OAuth consumer secret
	 */
	function __construct($key, $secret) {
		$this->oauthKey = $key;
		$this->oauthSecret = $secret;
		$this->saveHeaders = false;
		$this->ssl = true;
		$this->endPoint = 'api.context.io';
		$this->apiVersion = '1.0';
		$this->lastResponse = null;
		$this->authHeaders = false;
	}

	/**
	 * Returns the 25 most recent attachments found in a mailbox. Use limit to change that number.
	 * @link http://context.io/docs/1.0/allfiles
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: since, limit
	 * @return ContextIOResponse
	 */
	public function allFiles($account, $params) {
		$params = $this->_filterParams($params, array('since','limit'));
		return $this->get($account, 'allfiles.json', $params);
	}

	/**
	 * Returns the 25 most recent attachments found in a mailbox. Use limit to change that number.
	 * This is useful if you're polling a mailbox for new messages and want all new messages
	 * indexed since a given timestamp.
	 * @link http://context.io/docs/1.0/allmessages
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: since, limit
	 * @return ContextIOResponse
	 */
	public function allMessages($account, $params) {
		$params = $this->_filterParams($params, array('since','limit'));
		return $this->get($account, 'allmessages.json', $params);
	}


	/**
	 * This call returns the latest attachments exchanged with one
	 * or more email addresses
	 * @link http://context.io/docs/1.0/contactfiles
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: 'email', 'to', 'from', 'cc', 'bcc', 'limit'
	 * @return ContextIOResponse
	 */
	public function contactFiles($account, $params) {
		$params = $this->_filterParams($params, array('email','to','from','cc','bcc','limit'));
		return $this->get($account, 'contactfiles.json', $params);
	}

	/**
	 * This call returns list of email messages for one or more contacts. Use the email
	 * parameter to get emails where a contact appears in the recipients or is the sender.
	 * Use to, from and cc parameters for more precise control.
	 * @link http://context.io/docs/1.0/contactmessages
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: 'email', 'to', 'from', 'cc', 'bcc', 'limit'
	 * @return ContextIOResponse
	 */
	public function contactMessages($account, $params) {
		$params = $this->_filterParams($params, array('email','to','from','cc','bcc','limit'));
		return $this->get($account, 'contactmessages.json', $params);
	}

	/**
	 * Given two files, this will return the list of insertions and deletions made
	 * from the oldest of the two files to the newest one.
	 * @link http://context.io/docs/1.0/diffsummary
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]string $params Query parameters for the API call: 'fileId1', 'fileId2'
	 * @return ContextIOResponse
	 */
	public function diffSummary($account, $params) {
		$params = $this->_filterParams($params, array('fileid1', 'fileid2'));
		$params['generate'] = 1;
		return $this->get($account, 'diffsummary.json', $params);
	}

	/**
	 * Returns the content a given attachment. If you want to save the attachment to
	 * a file, set $saveAs to the destination file name. If $saveAs is left to null,
	 * the function will return the file data.
	 * on the 
	 * @link http://context.io/docs/1.0/downloadfile
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]string $params Query parameters for the API call: 'fileId'
	 * @param string $saveAs Path to local file where the attachment should be saved to.
	 * @return mixed
	 */
	public function downloadFile($account, $params, $saveAs=null) {
		$params = $this->_filterParams($params, array('fileid'));

		$consumer = new OAuthConsumer($this->oauthKey, $this->oauthSecret);
		$params['account'] = $account;
		$baseUrl = $this->build_url('downloadfile');
		$req = OAuthRequest::from_consumer_and_token($consumer, null, "GET", $baseUrl, $params);
		$sig_method = new OAuthSignatureMethod_HMAC_SHA1();
		$req->sign_request($sig_method, $consumer, null);

		//get data using signed url
		if ($this->authHeaders) {
			$curl = curl_init($baseUrl . '?' . OAuthUtil::build_http_query($params));
			curl_setopt($curl, CURLOPT_HTTPHEADER, array($req->to_header()));
		}
		else {
			$curl = curl_init($req->to_url());
		}
		
		if ($this->ssl) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		}
		
		if (! is_null($saveAs)) {
			$fp = fopen($saveAs, "w");
			curl_setopt($curl, CURLOPT_FILE, $fp);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_exec($curl);
			curl_close($curl);
			fclose($fp);
			return true;
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		return curl_exec($curl);
	}

	/**
	 * Returns a list of revisions attached to other emails in the 
	 * mailbox for one or more given files (see fileid parameter below).
	 * @link http://context.io/docs/1.0/filerevisions
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]string $params Query parameters for the API call: 'fileId', 'fileName'
	 * @return ContextIOResponse
	 */
	public function fileRevisions($account, $params) {
		$params = $this->_filterParams($params, array('fileid', 'filename'));
		return $this->get($account, 'filerevisions.json', $params);
	}

	/**
	 * Returns a list of files that are related to the given file. 
	 * Currently, relation between files is based on how similar their names are.
	 * You must specify either the fileId of fileName parameter
	 * @link http://context.io/docs/1.0/relatedfiles
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]string $params Query parameters for the API call: 'fileId', 'fileName'
	 * @return ContextIOResponse
	 */
	public function relatedFiles($account, $params) {
		$params = $this->_filterParams($params, array('fileid', 'filename'));
		return $this->get($account, 'relatedfiles.json', $params);
	}

	/**
	 * 
	 * @link http://context.io/docs/1.0/filesearch
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]string $params Query parameters for the API call: 'fileName'
	 * @return ContextIOResponse
	 */
	public function fileSearch($account, $params) {
		$params = $this->_filterParams($params, array('filename'));
		return $this->get($account, 'filesearch.json', $params);
	}

	/**
	 * @link http://context.io/docs/1.0/imap/addaccount
	 * @param array[string]string $params Query parameters for the API call: 'email', 'server', 'username', 'password', 'usessl', 'port'
	 * @return ContextIOResponse
	 */
	public function imap_addAccount($params) {
		$params = $this->_filterParams($params, array('email','server','username','password','usessl','port','firstname','lastname'));
		return $this->get(null, 'imap/addaccount.json', $params);
	}

	/**
	 * Attempts to discover IMAP settings for a given email address
	 * @link http://context.io/docs/1.0/imap/discover
	 * @param mixed $params either a string or assoc array
	 *    with email as its key
	 * @return ContextIOResponse
	 */
	public function imap_discover($params) {
		if (is_string($params)) {
			$params = array('email' => $params);
		}
		else {
			$params = $this->_filterParams($params, array('email'));
		}
		return $this->get(null, 'imap/discover.json', $params);
	}

	/**
	 * Modify the IMAP server settings of an already indexed account
	 * @link http://context.io/docs/1.0/imap/modifyaccount
	 * @param array[string]string $params Query parameters for the API call: 'credentials', 'mailboxes'
	 * @return ContextIOResponse
	 */
	public function imap_modifyAccount($account, $params) {
		$params = $this->_filterParams($params, array('credentials', 'mailboxes'));
		return $this->get($account, 'imap/modifyaccount.json', $params);
	}

	/**
	 * Remove the connection to an IMAP account
	 * @link http://context.io/docs/1.0/imap/removeaccount
	 * @return ContextIOResponse
	 */
	public function imap_removeAccount($account, $params=array()) {
		$params = $this->_filterParams($params, array('label'));
		return $this->get($account, 'imap/removeaccount.json', $params);
	}

	/**
	 * When Context.IO can't connect to your IMAP server, 
	 * the IMAP server gets flagged as unavailable in our database. 
	 * Use this call to re-enable the syncing.
	 * @link http://context.io/docs/1.0/imap/resetstatus
	 * @return ContextIOResponse
	 */
	public function imap_resetStatus($account, $params=array()) {
		$params = $this->_filterParams($params, array('label'));
		return $this->get($account, 'imap/resetstatus.json', $params);
	}

	/**
	 * Returns document and contact information about a message.
	 * A message can be identified by the value of its Message-ID header
	 * or by the combination of the date sent timestamp and email address
	 * of the sender.
	 * @link http://context.io/docs/1.0/messageinfo
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: 'emailMessageId', 'from', 'dateSent', 'server', 'mbox', 'uid'
	 * @return ContextIOResponse
	 */
	public function messageInfo($account, $params) {
		$params = $this->_filterParams($params, array('emailmessageid', 'from', 'datesent','server','mbox','uid'));
		return $this->get($account, 'messageinfo.json', $params);
	}

	/**
	 * Returns the message body (excluding attachments) of a message.
	 * A message can be identified by the value of its Message-ID header
	 * or by the combination of the date sent timestamp and email address
	 * of the sender.
	 * @link http://context.io/docs/1.0/messagetext
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: 'emailMessageId', 'from', 'dateSent','type
	 * @return ContextIOResponse
	 */
	public function messageText($account, $params) {
		$params = $this->_filterParams($params, array('emailmessageid', 'from', 'datesent','type'));
		return $this->get($account, 'messagetext.json', $params);
	}

	/**
	 * Returns message information
	 * @link http://context.io/docs/1.0/search
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: 'subject', 'limit'
	 * @return ContextIOResponse
	 */
	public function search($account, $params) {
		$params = $this->_filterParams($params, array('subject', 'limit'));
		return $this->get($account, 'search.json', $params);
	}

	/**
	 * Returns message and contact information about a given email thread.
	 * @link http://context.io/docs/1.0/threadinfo
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]string $params Query parameters for the API call: 'gmailthreadid'
	 * @return ContextIOResponse
	 */
	public function threadInfo($account, $params) {
		$params = $this->_filterParams($params, array('gmailthreadid'));
		return $this->get($account, 'threadinfo.json', $params);
	}

	/**
	 * Specify whether or not API calls should be made over a secure connection. 
	 * HTTPS is used on all calls by default.
	 * @param bool $sslOn Set to false to make calls over HTTP, true to use HTTPS
	 */
	public function setSSL($sslOn=true) {
		$this->ssl = (is_bool($sslOn)) ? $sslOn : true;
	}

	/**
	 * Set the API version. By default, the latest official version will be used
	 * for all calls.
	 * @param string $apiVersion Context.IO API version to use
	 */
	public function setApiVersion($apiVersion) {
		$this->apiVersion = $apiVersion;
	}

	/**
	 * Specify whether OAuth parameters should be included as URL query parameters
	 * or sent as HTTP Authorization headers. The default is URL query parameters.
	 * @param bool $authHeadersOn Set to true to use HTTP Authorization headers, false to use URL query params
	 */
	public function useAuthorizationHeaders($authHeadersOn = true) {
		$this->authHeaders = (is_bool($authHeadersOn)) ? $authHeadersOn : true;;
	}

	/**
	 * Returns the ContextIOResponse object for the last API call.
	 * @return ContextIOResponse
	 */
	public function getLastResponse() {
		return $this->lastResponse;
	}


	protected function build_baseurl() {
		$url = 'http';
		if ($this->ssl) {
			$url = 'https';
		}
		return "$url://" . $this->endPoint . "/" . $this->apiVersion . '/';
	}

	protected function build_url($action) {
		return $this->build_baseurl() . $action;
	}

	public function saveHeaders($yes=true) {
		$this->saveHeaders = $yes;
	}
	
	protected function get($account, $action, $parameters=null) {
		if (is_array($account)) {
			$tmp_results = array();
			foreach ($account as $accnt) {
				$result = $this->_doCall($accnt, $action, $parameters);
				if ($result === false) {
					return false;
				}
				$tmp_results[$accnt] = $result;
			}
			return $tmp_results;
		}
		else {
			return $this->_doCall($account, $action, $parameters);
		}
	}

	protected function _doCall($account, $action, $parameters=null) {
		$consumer = new OAuthConsumer($this->oauthKey, $this->oauthSecret);
		if (! is_null($account)) {
			if (is_null($parameters)) {
				$parameters = array('account' => $account);
			}
			else {
				$parameters['account'] = $account;
			}
		}
		$baseUrl = $this->build_url($action);
		$req = OAuthRequest::from_consumer_and_token($consumer, null, "GET", $baseUrl, $parameters);
		$sig_method = new OAuthSignatureMethod_HMAC_SHA1();
		$req->sign_request($sig_method, $consumer, null);

		//get data using signed url
		if ($this->authHeaders) {
			$curl = curl_init($baseUrl . '?' . OAuthUtil::build_http_query($parameters));
			curl_setopt($curl, CURLOPT_HTTPHEADER, array($req->to_header()));
		}
		else {
			$curl = curl_init($req->to_url());
		}
		
		if ($this->ssl) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		}
		
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		
		if ($this->saveHeaders) {
			$this->responseHeaders = array();
			$this->requestHeaders = array();
			curl_setopt($curl, CURLOPT_HEADERFUNCTION, array($this,'_setHeader'));
			curl_setopt($curl, CURLINFO_HEADER_OUT, 1);
		}
		$result = curl_exec($curl);
		
		$httpHeadersIn = ($this->saveHeaders) ? $this->responseHeaders : null;
		$httpHeadersOut = ($this->saveHeaders) ? preg_split('/\\n|\\r/', curl_getinfo($curl, CURLINFO_HEADER_OUT)) : null;
		
		$response = new ContextIOResponse(
			curl_getinfo($curl, CURLINFO_HTTP_CODE),
			$httpHeadersOut,
			$httpHeadersIn,
			curl_getinfo($curl, CURLINFO_CONTENT_TYPE),
			$result);
		curl_close($curl);
		if ($response->hasError()) {
			$this->lastResponse = $response;
			return false;
		}
		return $response;
	}

	public function _setHeader($curl,$headers) {
		$this->responseHeaders[] = trim($headers,"\n\r");
		return strlen($headers);
	}
	
	protected function _filterParams($givenParams, $validParams) {
		$filteredParams = array();
		foreach ($givenParams as $name => $value) {
			if (in_array(strtolower($name), $validParams)) {
				$filteredParams[strtolower($name)] = $value;
			}
		}
		return $filteredParams;
	}

}


?>
