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
		$this->endPoint = 'api-preview.context.io';
		$this->apiVersion = '2.0';
		$this->lastResponse = null;
		$this->authHeaders = true;
	}

	/**
	 * Attempts to discover IMAP settings for a given email address
	 * @link http://context.io/docs/2.0/discovery
	 * @param mixed $params either a string or assoc array
	 *    with email as its key
	 * @return ContextIOResponse
	 */
	public function discovery($params) {
		if (is_string($params)) {
			$params = array('email' => $params);
		}
		else {
			$params = $this->_filterParams($params, array('email'));
		}
		return $this->get(null, 'discovery?source=imap&email=' . $params['email']);
	}

	/**
	 *
	 * @link http://context.io/docs/2.0/oauthproviders
	 */
	public function listOAuthProviders() {
		return $this->get(null, 'oauth_providers');
	}

	/**
	 *
	 * @link http://context.io/docs/2.0/oauthproviders
	 */
	public function getOAuthProvider($params) {
		if (is_string($params)) {
			$params = array('consumer_key' => $params);
		}
		else {
			$params = $this->_filterParams($params, array('consumer_key'));
			if (! array_key_exists('consumer_key', $params)) {
				throw new InvalidArgumentException('consumer_key is a required hash key');
			}
		}
		return $this->get(null, 'oauth_providers/' . $params['consumer_key']);
	}

	/**
	 *
	 * @link http://context.io/docs/2.0/oauthproviders
	 */
	public function addOAuthProvider($params=array()) {
		$params = $this->_filterParams($params, array('type','consumer_key','consumer_secret'));
		return $this->post(null, 'oauth_providers', $params);
	}

	/**
	 *
	 * @link http://context.io/docs/2.0/oauthproviders
	 */
	public function deleteOAuthProvider($params) {
		if (is_string($params)) {
			$params = array('consumer_key' => $params);
		}
		else {
			$params = $this->_filterParams($params, array('consumer_key'));
			if (! array_key_exists('consumer_key', $params)) {
				throw new InvalidArgumentException('consumer_key is a required hash key');
			}
		}
		return $this->delete(null, 'oauth_providers/' . $params['consumer_key']);
	}

	/**
	 * Returns the 20 contacts with whom the most emails were exchanged.
	 * @link http://context.io/docs/2.0/accounts/contacts
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @return ContextIOResponse
	 */
	public function listContacts($account, $params=null) {
		if (is_array($params)) $params = $this->_filterParams($params, array('active_after','active_before','limit','offset','search'));
		return $this->get($account, 'contacts', $params);
	}

	public function getContact($account, $params=array()) {
		if (is_string($params)) {
			$params = array('email' => $params);
		}
		else {
			$params = $this->_filterParams($params, array('email'));
			if (! array_key_exists('email', $params)) {
				throw new InvalidArgumentException('email is a required hash key');
			}
		}
		return $this->get($account, 'contacts/' . $params['email']);
	}

	/**
	 * @link http://context.io/docs/2.0/accounts/contacts/files
	 * @return ContextIOResponse
	 */
	public function listContactFiles($account, $params) {
		$params = $this->_filterParams($params, array('email','limit','offset','scope','group_by_revisions'));
		if (! array_key_exists('email', $params)) {
			throw new InvalidArgumentException('email is a required hash key');
		}
		return $this->get($account, 'contacts/' . $params['email'] . '/files', $params);
	}

	/**
	 * @link http://context.io/docs/2.0/accounts/contacts/messages
	 * @return ContextIOResponse
	 */
	public function listContactMessages($account, $params) {
		$params = $this->_filterParams($params, array('email','limit','offset','scope'));
		if (! array_key_exists('email', $params)) {
			throw new InvalidArgumentException('email is a required hash key');
		}
		return $this->get($account, 'contacts/' . $params['email'] . '/messages', $params);
	}

	/*
	public function listContactThreads($account, $params) {
		$params = $this->_filterParams($params, array('email','limit','offset','scope'));
		if (! array_key_exists('email', $params)) {
			throw new InvalidArgumentException('email is a required hash key');
		}
		return $this->get($account, 'contacts/' . $params['email'] . '/threads', $params);
	}
	 */

	/**
	 * @link http://context.io/docs/2.0/accounts/files
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: indexed_after, limit
	 * @return ContextIOResponse
	 */
	public function listFiles($account, $params=null) {
		if (is_array($params)) $params = $this->_filterParams($params, array('indexed_after','date_before','date_after','name','limit', 'offset', 'email', 'to','from','cc','bcc','group_by_revisions'));
		return $this->get($account, 'files', $params);
	}

	public function getFile($account, $params) {
		if (is_string($params)) {
			$params = array('file_id' =>$params);
		}
		else {
			$params = $this->_filterParams($params, array('file_id'));
			if (! array_key_exists('file_id', $params)) {
				throw new InvalidArgumentException('file_id is a required hash key');
			}
		}
		return $this->get($account, 'files/' . $params['file_id']);
	}

	/**
	 * Returns the content a given attachment. If you want to save the attachment to
	 * a file, set $saveAs to the destination file name. If $saveAs is left to null,
	 * the function will return the file data.
	 * on the 
	 * @link http://context.io/docs/2.0/accounts/files/content
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]string $params Query parameters for the API call: 'fileId'
	 * @param string $saveAs Path to local file where the attachment should be saved to.
	 * @return mixed
	 */
	public function getFileContent($account, $params, $saveAs=null) {
		if (is_string($params)) {
			$params = array('file_id' =>$params);
		}
		else {
			$params = $this->_filterParams($params, array('file_id'));
			if (! array_key_exists('file_id', $params)) {
				throw new InvalidArgumentException('file_id is a required hash key');
			}
		}

		$consumer = new OAuthConsumer($this->oauthKey, $this->oauthSecret);
		$baseUrl = $this->build_url('accounts/' . $account . '/files/' . $params['file_id'] . '/content');
		$req = OAuthRequest::from_consumer_and_token($consumer, null, "GET", $baseUrl);
		$sig_method = new OAuthSignatureMethod_HMAC_SHA1();
		$req->sign_request($sig_method, $consumer, null);

		//get data using signed url
		if ($this->authHeaders) {
			$curl = curl_init($baseUrl);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array($req->to_header()));
		}
		else {
			$curl = curl_init($req->to_url());
		}
		
		if ($this->ssl) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		}
		
		curl_setopt($curl, CURLOPT_USERAGENT, 'ContextIOLibrary/2.0 (PHP)');

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
		$result = curl_exec($curl);
		if (curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200) {
			$response = new ContextIOResponse(
				curl_getinfo($curl, CURLINFO_HTTP_CODE),
				null,
				null,
				curl_getinfo($curl, CURLINFO_CONTENT_TYPE),
				$result);
			$this->lastResponse = $response;
			curl_close($curl);
			return false;
		}
		curl_close($curl);
		return $result;
	}

	/**
	 * Given two files, this will return the list of insertions and deletions made
	 * from the oldest of the two files to the newest one.
	 * @link http://context.io/docs/2.0/accounts/files/changes
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]string $params Query parameters for the API call: 'fileId1', 'fileId2'
	 * @return ContextIOResponse
	 */
	public function getFileChanges($account, $params) {
		$params = $this->_filterParams($params, array('fileid1', 'fileid2'));
		$params['generate'] = 1;
		return $this->get($account, 'diffsummary.json', $params);
	}

	/**
	 * Returns a list of revisions attached to other emails in the 
	 * mailbox for one or more given files (see fileid parameter below).
	 * @link http://context.io/docs/2.0/accounts/files/revisions
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]string $params Query parameters for the API call: 'fileId', 'fileName'
	 * @return ContextIOResponse
	 */
	public function listFileRevisions($account, $params) {
		if (is_string($params)) {
			$params = array('file_id' =>$params);
		}
		else {
			$params = $this->_filterParams($params, array('file_id'));
			if (! array_key_exists('file_id', $params)) {
				throw new InvalidArgumentException('file_id is a required hash key');
			}
		}
		return $this->get($account, 'files/' . $params['file_id'] . '/revisions');
	}

	/**
	 * Returns a list of files that are related to the given file. 
	 * Currently, relation between files is based on how similar their names are.
	 * You must specify either the fileId of fileName parameter
	 * @link http://context.io/docs/2.0/accounts/files/related
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]string $params Query parameters for the API call: 'fileId', 'fileName'
	 * @return ContextIOResponse
	 */
	public function listFileRelated($account, $params) {
		if (is_string($params)) {
			$params = array('file_id' =>$params);
		}
		else {
			$params = $this->_filterParams($params, array('file_id'));
			if (! array_key_exists('file_id', $params)) {
				throw new InvalidArgumentException('file_id is a required hash key');
			}
		}
		return $this->get($account, 'files/' . $params['file_id'] . '/related');
	}

	/**
	 * Returns message information
	 * @link http://context.io/docs/2.0/accounts/messages
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: 'subject', 'limit'
	 * @return ContextIOResponse
	 */
	public function listMessages($account, $params=null) {
		if (is_array($params)) $params = $this->_filterParams($params, array('subject', 'date_before', 'date_after', 'indexed_after', 'limit', 'offset','email', 'to','from','cc','bcc','email_message_id','type','include_body'));
		return $this->get($account, 'messages', $params);
	}

	public function addMessageToFolder($account, $params=array()) {
		$params = $this->_filterParams($params, array('label','dst_folder','src_folder','src_uid'));
		return $this->post($account, 'messages', $params);
	}

	/**
	 * Returns document and contact information about a message.
	 * A message can be identified by the value of its Message-ID header
	 * @link http://context.io/docs/2.0/accounts/messages#id-get
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: 'emailMessageId'
	 * @return ContextIOResponse
	 */
	public function getMessage($account, $params) {
		if (is_string($params)) {
			$params = array('email_message_id' =>$params);
		}
		else {
			$params = $this->_filterParams($params, array('email_message_id'));
			if (! array_key_exists('email_message_id', $params)) {
				throw new InvalidArgumentException('email_message_id is a required hash key');
			}
		}
		return $this->get($account, 'messages/' . $params['email_message_id']);
	}

	/**
	 * Returns the message headers of a message.
	 * A message can be identified by the value of its Message-ID header
	 * @link http://context.io/docs/2.0/accounts/messages/headers
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: 'emailMessageId'
	 * @return ContextIOResponse
	 */
	public function getMessageHeaders($account, $params) {
		if (is_string($params)) {
			$params = array('email_message_id' =>$params);
		}
		else {
			$params = $this->_filterParams($params, array('email_message_id'));
			if (! array_key_exists('email_message_id', $params)) {
				throw new InvalidArgumentException('email_message_id is a required hash key');
			}
		}
		return $this->get($account, 'messages/' . $params['email_message_id'] . '/headers');
	}

	/**
	 * Returns the message flags of a message.
	 * A message can be identified by the value of its Message-ID header
	 * @link http://context.io/docs/2.0/accounts/messages/flags
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: 'emailMessageId'
	 * @return ContextIOResponse
	 */
	public function getMessageFlags($account, $params) {
		if (is_string($params)) {
			$params = array('email_message_id' =>$params);
		}
		else {
			$params = $this->_filterParams($params, array('email_message_id'));
			if (! array_key_exists('email_message_id', $params)) {
				throw new InvalidArgumentException('email_message_id is a required hash key');
			}
		}
		return $this->get($account, 'messages/' . $params['email_message_id'] . '/flags');
	}

	/**
	 * Returns the message flags of a message.
	 * A message can be identified by the value of its Message-ID header
	 * @link http://context.io/docs/2.0/accounts/messages/flags
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: 'emailMessageId'
	 * @return ContextIOResponse
	 */
	public function setMessageFlags($account, $params) {
		$params = $this->_filterParams($params, array('email_message_id', 'flags'));
		if (! array_key_exists('email_message_id', $params)) {
			throw new InvalidArgumentException('email_message_id is a required hash key');
		}
		return $this->put($account, 'messages/' . $params['email_message_id'] . '/flags', array('flags' => serialize($params['flags'])));
	}

	/**
	 * Returns the message body (excluding attachments) of a message.
	 * A message can be identified by the value of its Message-ID header
	 * or by the combination of the date sent timestamp and email address
	 * of the sender.
	 * @link http://context.io/docs/2.0/accounts/messages/body
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: 'emailMessageId', 'from', 'dateSent','type
	 * @return ContextIOResponse
	 */
	public function getMessageBody($account, $params) {
		$params = $this->_filterParams($params, array('email_message_id', 'type'));
		if (! array_key_exists('email_message_id', $params)) {
			throw new InvalidArgumentException('email_message_id is a required hash key');
		}
		return $this->get($account, 'messages/' . $params['email_message_id'] . '/body', $params);
	}

	/**
	 * Returns message and contact information about a given email thread.
	 * @link http://context.io/docs/2.0/accounts/messages/thread
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]string $params Query parameters for the API call: 'email_message_id'
	 * @return ContextIOResponse
	 */
	public function getMessageThread($account, $params) {
		$params = $this->_filterParams($params, array('email_message_id'));
		if (! array_key_exists('email_message_id', $params)) {
			throw new InvalidArgumentException('email_message_id is required hash keys');
		}
		return $this->get($account, 'messages/' . $params['email_message_id'] . '/thread');
	}

	/**
	 * Returns list of threads
	 * @link http://context.io/docs/2.0/accounts/threads
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]string $params Query parameters for the API call: 'gmailthreadid'
	 * @return ContextIOResponse
	 */
	/*
	public function listThreads($account, $params=null) {
		if (is_array($params)) $params = $this->_filterParams($params, array('subject', 'indexed_after', 'active_after', 'active_before', 'limit', 'started_before', 'started_after', 'offset','email', 'to','from','cc','bcc'));
		return $this->get($account, 'threads', $params);
	}
	 */

	/**
	 * Returns message and contact information about a given email thread.
	 * @link http://context.io/docs/2.0/accounts/threads
	 * @param string $account accountId or email address of the mailbox you want to query
	 * @param array[string]string $params Query parameters for the API call: 'gmailthreadid'
	 * @return ContextIOResponse
	 */
	public function getThread($account, $params) {
		$params = $this->_filterParams($params, array('gmail_thread_id','email_message_id'));
		if (! array_key_exists('email_message_id', $params) && ! array_key_exists('gmail_thread_id', $params)) {
			throw new InvalidArgumentException('gmail_thread_id or email_message_id are required hash keys');
		}
		if (array_key_exists('email_message_id', $params)) {
			return $this->get($account, 'messages/' . $params['email_message_id'] . '/thread');
		}
		else {
			return $this->get($account, 'threads/gm-' . $params['gmail_thread_id']);
		}
	}


	public function apiKeyInfo() {
		return $this->get(null, 'apikeyinfo.json');
	}

	public function addAccount($params) {
		$params = $this->_filterParams($params, array('email','first_name','last_name'));
		return $this->post(null, 'accounts', $params);
	}

	public function modifyAccount($account, $params) {
		$params = $this->_filterParams($params, array('first_name','last_name'));
		return $this->put($account, '', $params);
	}

	public function getAccount($account) {
		return $this->get($account);
	}

	public function listAccountEmailAddresses($account) {
		return $this->get($account, 'email_addresses');
	}

	public function addEmailAddressToAccount($account, $params) {
		$params = $this->_filterParams($params, array('email_address'));
		return $this->post($account, 'email_addresses', $params);
	}

	public function listAccounts($params=null) {
		if (is_array($params)) $params = $this->_filterParams($params, array('limit','offset'));
		return $this->get(null, 'accounts', $params);
	}

	/**
	 * Modify the IMAP server settings of an already indexed account
	 * @link http://context.io/docs/2.0/accounts/sources
	 * @param array[string]string $params Query parameters for the API call: 'credentials', 'mailboxes'
	 * @return ContextIOResponse
	 */
	public function modifySource($account, $params) {
		$params = $this->_filterParams($params, array('credentials', 'label', 'mailboxes', 'service_level'));
		if (! array_key_exists('label', $params)) {
			throw new InvalidArgumentException('label is a required hash key');
		}
		return $this->put($account, 'sources/' . $params['label'], $params);
	}

	public function resetSourceStatus($account, $params) {
		if (is_string($params)) {
			$params = array('label' => $params);
		}
		else {
			$params = $this->_filterParams($params, array('label'));
			if (! array_key_exists('label', $params)) {
				throw new InvalidArgumentException('label is a required hash key');
			}
		}
		return $this->put($account, 'sources/' . $params['label'], array('status' => 1));
	}

	public function listSources($account) {
		return $this->get($account, 'sources');
	}

	public function getSource($account, $params) {
		if (is_string($params)) {
			$params = array('label' => $params);
		}
		else {
			$params = $this->_filterParams($params, array('label'));
			if (! array_key_exists('label', $params)) {
				throw new InvalidArgumentException('label is a required hash key');
			}
		}
		return $this->get($account, 'sources/' . $params['label']);
	}

	/**
	 * @link http://context.io/docs/2.0/accounts/sources
	 * @param array[string]string $params Query parameters for the API call: 'email', 'server', 'username', 'password', 'oauthconsumername', 'oauthtoken', 'oauthtokensecret', 'usessl', 'port'
	 * @return ContextIOResponse
	 */
	public function addSource($account, $params) {
		$params = $this->_filterParams($params, array('type','email','server','username','oauth_consumer_key','oauth_token','oauth_token_secret','service_level','password','use_ssl','port'));
		if (! array_key_exists('type', $params)) {
			$params['type'] = 'imap';
		}
		return $this->post($account, 'sources/', $params);
	}

	/**
	 * Remove the connection to an IMAP account
	 * @link http://context.io/docs/2.0/accounts/sources
	 * @return ContextIOResponse
	 */
	public function deleteSource($account, $params=array()) {
		if (is_string($params)) {
			$params = array('label' => $params);
		}
		else {
			$params = $this->_filterParams($params, array('label'));
			if (! array_key_exists('label', $params)) {
				throw new InvalidArgumentException('label is a required hash key');
			}
		}
		return $this->delete($account, 'sources/' . $params['label']);
	}

	public function syncSource($account, $params=array()) {
		$params = $this->_filterParams($params, array('label'));
		if ($params == array()) {
			return $this->post($account, 'sync');
		}
		return $this->post($account, 'sources/' . $params['label'] . '/sync');
	}

	public function getSync($account, $params=array()) {
		$params = $this->_filterParams($params, array('label'));
		if ($params == array()) {
			return $this->get($account, 'sync');
		}
		return $this->get($account, 'sources/' . $params['label'] . '/sync');
	}

	public function addFolderToSource($account, $params=array()) {
		$params = $this->_filterParams($params, array('label','folder'));
		if (! array_key_exists('label', $params) || ! array_key_exists('folder', $params)) {
			throw new InvalidArgumentException('label and folder are required hash keys');
		}
		return $this->put($account, 'sources/' . $params['label'] . '/folders/' . $params['folder']);
	}

	public function listSourceFolders($account, $params=array()) {
		if (is_string($params)) {
			$params = array('label' => $params);
		}
		else {
			$params = $this->_filterParams($params, array('label'));
			if (! array_key_exists('label', $params)) {
				throw new InvalidArgumentException('label is a required hash key');
			}
		}
		return $this->get($account, 'sources/' . $params['label'] . '/folders');
	}

	public function listWebhooks($account) {
		return $this->get($account, 'webhooks');
	}

	public function getWebhook($account, $params) {
		if (is_string($params)) {
			$params = array('webhook_id' => $params);
		}
		else {
			$params = $this->_filterParams($params, array('webhook_id'));
			if (! array_key_exists('webhook_id', $params)) {
				throw new InvalidArgumentException('webhook_id is a required hash key');
			}
		}
		return $this->get($account, 'webhooks/' . $params['webhook_id']);
	}

	public function addWebhook($account, $params) {
		$params = $this->_filterParams($params, array('filter_to', 'filter_from', 'filter_cc', 'filter_subject', 'filter_thread', 'filter_new_important', 'filter_file', 'filter_file_revisions', 'delay', 'callback_url', 'failure_notif_url'));
		return $this->post($account, 'webhooks/', $params);
	}

	public function deleteWebhook($account, $params) {
		if (is_string($params)) {
			$params = array('webhook_id' => $params);
		}
		else {
			$params = $this->_filterParams($params, array('webhook_id'));
			if (! array_key_exists('webhook_id', $params)) {
				throw new InvalidArgumentException('webhook_id is a required hash key');
			}
		}
		return $this->delete($account, 'webhooks/' . $params['webhook_id']);
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
	 * @return boolean success
	 */
	public function setApiVersion($apiVersion) {
		if ($apiVersion != '2.0') {
			return false;
		}
		$this->apiVersion = $apiVersion;
		return true;
	}

	/**
	 * Specify whether OAuth parameters should be included as URL query parameters
	 * or sent as HTTP Authorization headers. The default is URL query parameters.
	 * @param bool $authHeadersOn Set to true to use HTTP Authorization headers, false to use URL query params
	 */
	public function useAuthorizationHeaders($authHeadersOn = true) {
		$this->authHeaders = (is_bool($authHeadersOn)) ? $authHeadersOn : true;
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
	
	protected function get($account, $action='', $parameters=null) {
		if (is_array($account)) {
			$tmp_results = array();
			foreach ($account as $accnt) {
				$result = $this->_doCall('GET', $accnt, $action, $parameters);
				if ($result === false) {
					return false;
				}
				$tmp_results[$accnt] = $result;
			}
			return $tmp_results;
		}
		else {
			return $this->_doCall('GET', $account, $action, $parameters);
		}
	}

	protected function put($account, $action, $parameters=null) {
		$this->authHeaders = false;
		return $this->_doCall('PUT', $account, $action, $parameters);
	}

	protected function post($account, $action, $parameters=null) {
		$this->authHeaders = false;
		return $this->_doCall('POST', $account, $action, $parameters);
	}

	protected function delete($account, $action, $parameters=null) {
		$this->authHeaders = false;
		return $this->_doCall('DELETE', $account, $action, $parameters);
	}

	protected function _doCall($httpMethod, $account, $action, $parameters=null) {
		$consumer = new OAuthConsumer($this->oauthKey, $this->oauthSecret);
		if (! is_null($account)) {
			$action = 'accounts/' . $account . '/' . $action;
		}
		$baseUrl = $this->build_url($action);
		$req = OAuthRequest::from_consumer_and_token($consumer, null, $httpMethod, $baseUrl, $parameters);
		$sig_method = new OAuthSignatureMethod_HMAC_SHA1();
		$req->sign_request($sig_method, $consumer, null);

		//get data using signed url
		if ($this->authHeaders) {
			if ($httpMethod == 'GET') {
				$curl = curl_init((is_null($parameters) || count($parameters) == 0) ? $baseUrl : $baseUrl. '?' . OAuthUtil::build_http_query($parameters));
			}
			else {
				$curl = curl_init($baseUrl);
			}
			curl_setopt($curl, CURLOPT_HTTPHEADER, array($req->to_header()));
		}
		else {
			$curl = curl_init($req->to_url());
		}
		
		if ($this->ssl) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		}
		
		curl_setopt($curl, CURLOPT_USERAGENT, 'ContextIOLibrary/2.0 (PHP)');

		if ($httpMethod != 'GET') {
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $httpMethod);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
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
