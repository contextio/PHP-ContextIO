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
			$params = $this->_filterParams($params, array('email'), array('email'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
			}
		}
		return $this->get(null, 'discovery?source_type=imap&email=' . $params['email']);
	}

	/**
	 *
	 * @link http://context.io/docs/2.0/connecttokens
	 */
	public function listConnectTokens($account=null) {
		return $this->get($account, 'connect_tokens');
	}

	/**
	 *
	 * @link http://context.io/docs/2.0/connecttokens
	 */
	public function getConnectToken($account=null,$params) {
		if (is_string($params)) {
			$params = array('token' => $params);
		}
		else {
			$params = $this->_filterParams($params, array('token'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
			}
		}
		return $this->get($account, 'connect_tokens/' . $params['token']);
	}

	/**
	 *
	 * @link http://context.io/docs/2.0/connecttokens
	 */
	public function addConnectToken($account=null,$params=array()) {
		$params = $this->_filterParams($params, array('service_level','email','callback_url','first_name','last_name','source_expunge_on_deleted_flag','source_sync_all_folders','source_callback_url','source_sync_flags','source_raw_file_list'), array('callback_url'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		return $this->post($account, 'connect_tokens', $params, null, array('Content-Type: application/x-www-form-urlencoded'));
	}

	/**
	 *
	 * @link http://context.io/docs/2.0/connecttokens
	 */
	public function deleteConnectToken($account=null, $params) {
		if (is_string($params)) {
			$params = array('token' => $params);
		}
		else {
			$params = $this->_filterParams($params, array('token'), array('token'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
			}
		}
		return $this->delete($account, 'connect_tokens/' . $params['token']);
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
			$params = array('provider_consumer_key' => $params);
		}
		else {
			$params = $this->_filterParams($params, array('provider_consumer_key'), array('provider_consumer_key'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
			}
		}
		return $this->get(null, 'oauth_providers/' . $params['provider_consumer_key']);
	}

	/**
	 *
	 * @link http://context.io/docs/2.0/oauthproviders
	 */
	public function addOAuthProvider($params=array()) {
		$params = $this->_filterParams($params, array('type','provider_consumer_key','provider_consumer_secret'), array('type','provider_consumer_key','provider_consumer_secret'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		return $this->post(null, 'oauth_providers', $params);
	}

	/**
	 *
	 * @link http://context.io/docs/2.0/oauthproviders
	 */
	public function deleteOAuthProvider($params) {
		if (is_string($params)) {
			$params = array('provider_consumer_key' => $params);
		}
		else {
			$params = $this->_filterParams($params, array('provider_consumer_key'), array('provider_consumer_key'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
			}
		}
		return $this->delete(null, 'oauth_providers/' . $params['provider_consumer_key']);
	}

	/**
	 * Returns the 20 contacts with whom the most emails were exchanged.
	 * @link http://context.io/docs/2.0/accounts/contacts
	 * @param string $account accountId of the mailbox you want to query
	 * @return ContextIOResponse
	 */
	public function listContacts($account, $params=null) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_array($params)) {
			$params = $this->_filterParams($params, array('active_after','active_before','limit','offset','search','sort_by','sort_order'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
			}
		}
		return $this->get($account, 'contacts', $params);
	}

	public function getContact($account, $params=array()) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_string($params)) {
			$params = array('email' => $params);
		}
		else {
			$params = $this->_filterParams($params, array('email'), array('email'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
			}
		}
		return $this->get($account, 'contacts/' . $params['email']);
	}

	/**
	 * @link http://context.io/docs/2.0/accounts/contacts/files
	 * @return ContextIOResponse
	 */
	public function listContactFiles($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		$params = $this->_filterParams($params, array('email','limit','offset','scope','group_by_revisions','include_person_info'), array('email'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		return $this->get($account, 'contacts/' . $params['email'] . '/files', $params);
	}

	/**
	 * @link http://context.io/docs/2.0/accounts/contacts/messages
	 * @return ContextIOResponse
	 */
	public function listContactMessages($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		$params = $this->_filterParams($params, array('email','limit','offset','scope','folder','include_person_info'), array('email'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		return $this->get($account, 'contacts/' . $params['email'] . '/messages', $params);
	}

	/**
	 * @link http://context.io/docs/2.0/accounts/contacts/threads
	 * @return ContextIOResponse
	 */
	public function listContactThreads($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		$params = $this->_filterParams($params, array('email','limit','offset','scope','folder'), array('email'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		return $this->get($account, 'contacts/' . $params['email'] . '/threads', $params);
	}

	/**
	 * @link http://context.io/docs/2.0/accounts/files
	 * @param string $account accountId of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: indexed_after, limit
	 * @return ContextIOResponse
	 */
	public function listFiles($account, $params=null) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_array($params)) {
			$params = $this->_filterParams($params, array('indexed_before', 'indexed_after','date_before','date_after','file_name','limit', 'offset', 'email', 'to','from','cc','bcc','group_by_revisions','include_person_info','source'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
			}
		}
		return $this->get($account, 'files', $params);
	}

	public function getFile($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_string($params)) {
			$params = array('file_id' =>$params);
		}
		else {
			$params = $this->_filterParams($params, array('file_id'), array('file_id'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
			}
		}
		return $this->get($account, 'files/' . $params['file_id']);
	}

	public function getFileURL($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_string($params)) {
			$params = array('file_id' =>$params);
		}
		else {
			$params = $this->_filterParams($params, array('file_id'), array('file_id'));
		}
		return $this->get($account, 'files/' . $params['file_id'] . '/content', array('as_link' => 1), array('text/uri-list'));
	}

	/**
	 * Returns the content a given attachment. If you want to save the attachment to
	 * a file, set $saveAs to the destination file name. If $saveAs is left to null,
	 * the function will return the file data.
	 * on the
	 * @link http://context.io/docs/2.0/accounts/files/content
	 * @param string $account accountId of the mailbox you want to query
	 * @param array[string]string $params Query parameters for the API call: 'fileId'
	 * @param string $saveAs Path to local file where the attachment should be saved to.
	 * @return mixed
	 */
	public function getFileContent($account, $params, $saveAs=null) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_string($params)) {
			$params = array('file_id' =>$params);
		}
		else {
			$params = $this->_filterParams($params, array('file_id'), array('file_id'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
			}
		}

		$consumer = new ContextIOExtLib\OAuthConsumer($this->oauthKey, $this->oauthSecret);
		$baseUrl = $this->build_url('accounts/' . $account . '/files/' . $params['file_id'] . '/content');
		$req = ContextIOExtLib\OAuthRequest::from_consumer_and_token($consumer, null, "GET", $baseUrl);
		$sig_method = new ContextIOExtLib\OAuthSignatureMethod_HMAC_SHA1();
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
	 * @param string $account accountId of the mailbox you want to query
	 * @param array[string]string $params Query parameters for the API call: 'fileId1', 'fileId2'
	 * @return ContextIOResponse
	 */
	public function getFileChanges($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		$params = $this->_filterParams($params, array('file_id1', 'file_id2', 'generate'), array('file_id1','file_id2'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		$newParams = array(
			'file_id' => $params['file_id2']
		);
		if (! array_key_exists('generate', $params)) {
			$newParams['generate'] = 1;
		}
		else {
			$newParams['generate'] = $params['generate'];
		}
		return $this->get($account, 'files/' . $params['file_id1'] . '/changes', $newParams);
	}

	/**
	 * Returns a list of revisions attached to other emails in the
	 * mailbox for one or more given files (see fileid parameter below).
	 * @link http://context.io/docs/2.0/accounts/files/revisions
	 * @param string $account accountId of the mailbox you want to query
	 * @param array[string]string $params Query parameters for the API call: 'fileId', 'fileName'
	 * @return ContextIOResponse
	 */
	public function listFileRevisions($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_string($params)) {
			$params = array('file_id' =>$params);
		}
		else {
			$params = $this->_filterParams($params, array('file_id', 'include_person_info'), array('file_id'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
			}
		}
		return $this->get($account, 'files/' . $params['file_id'] . '/revisions', $params);
	}

	/**
	 * Returns a list of files that are related to the given file.
	 * Currently, relation between files is based on how similar their names are.
	 * You must specify either the fileId of fileName parameter
	 * @link http://context.io/docs/2.0/accounts/files/related
	 * @param string $account accountId of the mailbox you want to query
	 * @param array[string]string $params Query parameters for the API call: 'fileId', 'fileName'
	 * @return ContextIOResponse
	 */
	public function listFileRelated($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_string($params)) {
			$params = array('file_id' =>$params);
		}
		else {
			$params = $this->_filterParams($params, array('file_id','include_person_info'), array('file_id'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
			}
		}
		return $this->get($account, 'files/' . $params['file_id'] . '/related', $params);
	}

	/**
	 * Returns message information
	 * @param string $account accountId of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: 'subject', 'limit'
	 * @return ContextIOResponse
	 */
	public function listMessagesBySourceAndFolder($account, $params=null) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_array($params)) {
			$params = $this->_filterParams($params, array('label','folder','limit','offset','type','include_body','include_headers','include_flags','flag_seen','async','async_job_id'), array('label','folder'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
			}
		}
		$source = $params['label'];
		$folder = $params['folder'];
		unset($params['label']);
		unset($params['folder']);
		if (array_key_exists('async_job_id', $params)) {
			return $this->get($account, "sources/$source/folders/$folder/messages/" . $params['async_job_id']);
		}
		return $this->get($account, "sources/$source/folders/$folder/messages", $params);
	}

	/**
	 * Returns message information
	 * @link http://context.io/docs/2.0/accounts/messages
	 * @param string $account accountId of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: 'subject', 'limit'
	 * @return ContextIOResponse
	 */
	public function listMessages($account, $params=null) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_array($params)) {
			$params = $this->_filterParams($params, array('subject', 'date_before', 'date_after', 'indexed_after', 'indexed_before', 'limit', 'offset','email', 'to','from','cc','bcc','email_message_id','type','body_type','include_body','include_headers','include_flags','folder','gm_search','include_person_info','file_name', 'file_size_min', 'file_size_max', 'source','include_thread_size','include_source','sort_order'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
			}
		}
		return $this->get($account, 'messages', $params);
	}

	public function addMessageToFolder($account, $params=array()) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		$requiredParams = array('dst_folder');
		if (array_key_exists('src_file', $params)) {
			$requiredParams[] = 'dst_label';
		}
		$params = $this->_filterParams($params, array('dst_label','dst_folder','src_file','message_id','email_message_id','gmail_message_id','flag_seen','flag_answered','flag_flagged','flag_deleted','flag_draft','move'), $requiredParams);
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		if (array_key_exists('move', $params)) {
			if (($params['move'] === true) || ($params['move'] == 1)) {
				$params['move'] = 1;
			}
			elseif (($params['move'] === false) || ($params['move'] == 0)) {
				unset($params['move']);
			}
			else {
				throw new InvalidArgumentException("move parameter must be boolean or 0/1");
			}
		}
		if (array_key_exists('src_file', $params)) {
			$params['src_file'] = realpath($params['src_file']);
			if (($params['src_file'] === false) || !is_readable($params['src_file'])) {
				throw new InvalidArgumentException("invalid source file");
			}
			$src_file = '@' . $params['src_file'];
			unset($params['src_file']);
			return $this->post($account, 'messages', $params, array('field' => 'message', 'filename' => $src_file));
		}
		elseif (array_key_exists('message_id', $params)) {
			return $this->post($account, 'messages/' . $params['message_id'], $params);
		}
		elseif (array_key_exists('email_message_id', $params)) {
			return $this->post($account, 'messages/' . rawurlencode($params['email_message_id']), $params);
		}
		elseif (array_key_exists('gmail_message_id', $params)) {
			if (substr($params['gmail_message_id'],0,3) == 'gm-') {
				return $this->post($account, 'messages/' . $params['gmail_message_id'], $params);
			}
			return $this->post($account, 'messages/gm-' . $params['gmail_message_id'], $params);
		}
		else {
			throw new InvalidArgumentException('src_file, message_id, email_message_id or gmail_message_id is a required hash key');
		}
	}

	/**
	 * Returns document and contact information about a message.
	 * A message can be identified by the value of its Message-ID header
	 * @link http://context.io/docs/2.0/accounts/messages#id-get
	 * @param string $account accountId of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: 'emailMessageId'
	 * @return ContextIOResponse
	 */
	public function getMessage($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_string($params)) {
			return $this->get($account, 'messages/' . rawurlencode($params));
		}
		else {
			$params = $this->_filterParams($params, array('message_id', 'email_message_id', 'gmail_message_id', 'include_person_info', 'type','include_thread_size','include_body','include_headers','include_flags','body_type','include_source'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
			}
			if (array_key_exists('message_id', $params)) {
				return $this->get($account, 'messages/' . $params['message_id'], $params);
			}
			elseif (array_key_exists('email_message_id', $params)) {
				return $this->get($account, 'messages/' . rawurlencode($params['email_message_id']), $params);
			}
			elseif (array_key_exists('gmail_message_id', $params)) {
				if (substr($params['gmail_message_id'],0,3) == 'gm-') {
					return $this->get($account, 'messages/' . $params['gmail_message_id'], $params);
				}
				return $this->get($account, 'messages/gm-' . $params['gmail_message_id'], $params);
			}
			else {
				throw new InvalidArgumentException('message_id, email_message_id or gmail_message_id is a required hash key');
			}
		}
	}

	public function deleteMessage($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_string($params)) {
			return $this->delete($account, 'messages/' . rawurlencode($params));
		}
		else {
			$params = $this->_filterParams($params, array('message_id', 'email_message_id', 'gmail_message_id'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
			}
			if (array_key_exists('message_id', $params)) {
				return $this->delete($account, 'messages/' . $params['message_id']);
			}
			elseif (array_key_exists('email_message_id', $params)) {
				return $this->delete($account, 'messages/' . rawurlencode($params['email_message_id']));
			}
			elseif (array_key_exists('gmail_message_id', $params)) {
				if (substr($params['gmail_message_id'],0,3) == 'gm-') {
					return $this->delete($account, 'messages/' . $params['gmail_message_id']);
				}
				return $this->delete($account, 'messages/gm-' . $params['gmail_message_id']);
			}
			else {
				throw new InvalidArgumentException('message_id, email_message_id or gmail_message_id is a required hash key');
			}
		}
	}

	/**
	 * Returns the message headers of a message.
	 * A message can be identified by the value of its Message-ID header
	 * @link http://context.io/docs/2.0/accounts/messages/headers
	 * @param string $account accountId of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: 'emailMessageId'
	 * @return ContextIOResponse
	 */
	public function getMessageHeaders($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_string($params)) {
			return $this->get($account, 'messages/' . rawurlencode($params) . '/headers');
		}
		else {
			$params = $this->_filterParams($params, array('message_id','email_message_id', 'gmail_message_id', 'raw'), array());
			$additionalParams = null;
			if (array_key_exists('raw', $params)) {
				$additionalParams = array('raw' => $params['raw']);
			}
			if (array_key_exists('message_id', $params)) {
				return $this->get($account, 'messages/' . $params['message_id']. '/headers', $additionalParams);
			}
			elseif (array_key_exists('email_message_id', $params)) {
				return $this->get($account, 'messages/' . rawurlencode($params['email_message_id']) . '/headers', $additionalParams);
			}
			elseif (array_key_exists('gmail_message_id', $params)) {
				if (substr($params['gmail_message_id'],0,3) == 'gm-') {
					return $this->get($account, 'messages/' . $params['gmail_message_id'] . '/headers', $additionalParams);
				}
				return $this->get($account, 'messages/gm-' . $params['gmail_message_id'] . '/headers', $additionalParams);
			}
			else {
				throw new InvalidArgumentException('message_id, email_message_id or gmail_message_id is a required hash key');
			}
		}
	}

	/**
	 * Returns the message source of a message.
	 * A message can be identified by the value of its Message-ID header
	 * @link http://context.io/docs/2.0/accounts/messages/source
	 * @param string $account accountId of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: 'emailMessageId'
	 * @return ContextIOResponse
	 */
	public function getMessageSource($account, $params, $saveAs=null) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_string($params)) {
			$url = 'messages/' . rawurlencode($params) . '/source';
		}
		elseif (array_key_exists('message_id', $params)) {
			$url = 'messages/' . $params['message_id']. '/source';
		}
		elseif (array_key_exists('email_message_id', $params)) {
			$url = 'messages/' . rawurlencode($params['email_message_id']) . '/source';
		}
		elseif (array_key_exists('gmail_message_id', $params)) {
			if (substr($params['gmail_message_id'],0,3) == 'gm-') {
				$url = 'messages/' . $params['gmail_message_id'] . '/source';
			}
			else {
				$url = 'messages/gm-' . $params['gmail_message_id'] . '/source';
			}
		}
		else {
			throw new InvalidArgumentException('message_id, email_message_id or gmail_message_id is a required hash key');
		}

		$consumer = new ContextIOExtLib\OAuthConsumer($this->oauthKey, $this->oauthSecret);
		$baseUrl = $this->build_url('accounts/' . $account . '/' . $url);
		$req = ContextIOExtLib\OAuthRequest::from_consumer_and_token($consumer, null, "GET", $baseUrl);
		$sig_method = new ContextIOExtLib\OAuthSignatureMethod_HMAC_SHA1();
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
	 * Returns the message flags of a message.
	 * A message can be identified by the value of its Message-ID header
	 * @link http://context.io/docs/2.0/accounts/messages/flags
	 * @param string $account accountId of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: 'emailMessageId'
	 * @return ContextIOResponse
	 */
	public function getMessageFlags($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_string($params)) {
			return $this->get($account, 'messages/' . rawurlencode($params) . '/flags');
		}
		elseif (array_key_exists('message_id', $params)) {
			return $this->get($account, 'messages/' . $params['message_id']. '/flags');
		}
		elseif (array_key_exists('email_message_id', $params)) {
			return $this->get($account, 'messages/' . rawurlencode($params['email_message_id']) . '/flags');
		}
		elseif (array_key_exists('gmail_message_id', $params)) {
			if (substr($params['gmail_message_id'],0,3) == 'gm-') {
				return $this->get($account, 'messages/' . $params['gmail_message_id'] . '/flags');
			}
			return $this->get($account, 'messages/gm-' . $params['gmail_message_id'] . '/flags');
		}
		else {
			throw new InvalidArgumentException('message_id, email_message_id or gmail_message_id is a required hash key');
		}
	}

	/**
	 * Returns the folders the message is part of.
	 * A message can be identified by the value of its Message-ID header
	 * @link http://context.io/docs/2.0/accounts/messages/folders
	 * @param string $account accountId
	 * @param array[string]mixed $params Query parameters for the API call: 'emailMessageId'
	 * @return ContextIOResponse
	 */
	public function getMessageFolders($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_string($params)) {
			return $this->get($account, 'messages/' . rawurlencode($params) . '/folders');
		}
		elseif (array_key_exists('message_id', $params)) {
			return $this->get($account, 'messages/' . $params['message_id']. '/folders');
		}
		elseif (array_key_exists('email_message_id', $params)) {
			return $this->get($account, 'messages/' . rawurlencode($params['email_message_id']) . '/folders');
		}
		elseif (array_key_exists('gmail_message_id', $params)) {
			if (substr($params['gmail_message_id'],0,3) == 'gm-') {
				return $this->get($account, 'messages/' . $params['gmail_message_id'] . '/folders');
			}
			return $this->get($account, 'messages/gm-' . $params['gmail_message_id'] . '/folders');
		}
		else {
			throw new InvalidArgumentException('message_id, email_message_id or gmail_message_id is a required hash key');
		}
	}

	/**
	 * Sets the message folders of a message.
	 * A message can be identified by the value of its Message-ID header
	 * @param string $account accountId of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: 'emailMessageId'
	 * @return ContextIOResponse
	 */
	public function setMessageFolders($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		$params = $this->_filterParams($params, array('message_id', 'email_message_id', 'gmail_message_id', 'add','remove','folders'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		if (array_key_exists('folders', $params)) {
			if (! is_array($params['folders'])) {
				throw new InvalidArgumentException("folders must be array");
			}
			$folderStr = json_encode($params['folders']);
			if (array_key_exists('email_message_id', $params)) {
				return $this->put($account, 'messages/' . rawurlencode($params['email_message_id']) . '/folders', $folderStr, array('Content-Type: application/json'));
			}
			elseif (array_key_exists('message_id', $params)) {
				return $this->put($account, 'messages/' . $params['message_id'] . '/folders', $folderStr, array('Content-Type: application/json'));
			}
			elseif (array_key_exists('gmail_message_id', $params)) {
				if (substr($params['gmail_message_id'],0,3) == 'gm-') {
					return $this->put($account, 'messages/' . $params['gmail_message_id'] . '/folders', $folderStr, array('Content-Type: application/json'));
				}
				return $this->put($account, 'messages/gm-' . $params['gmail_message_id'] . '/folders', $folderStr, array('Content-Type: application/json'));
			}
			else {
				throw new InvalidArgumentException('message_id, email_message_id or gmail_message_id is a required hash key');
			}
		}
		else {
			$addRemoveParams = array();
			$convertToString = false;
			foreach (array('add','remove') as $currentName) {
				if (array_key_exists($currentName, $params)) {
					if (is_array($params[$currentName])) {
						$convertToString = true;
					}
					$addRemoveParams[$currentName] = $params[$currentName];
				}
			}
			if (count(array_keys($addRemoveParams)) == 0) {
				throw new InvalidArgumentException("must specify at least one of add,remove");
			}

			$httpHeadersToSet = array();
			if ($convertToString) {
				$httpHeadersToSet[] = 'Content-Type: application/x-www-form-urlencoded';
			}

			if (array_key_exists('email_message_id', $params)) {
				return $this->post($account, 'messages/' . rawurlencode($params['email_message_id']) . '/folders', $addRemoveParams, null, $httpHeadersToSet);
			}
			elseif (array_key_exists('message_id', $params)) {
				return $this->post($account, 'messages/' . $params['message_id'] . '/folders', $addRemoveParams, null, $httpHeadersToSet);
			}
			elseif (array_key_exists('gmail_message_id', $params)) {
				if (substr($params['gmail_message_id'],0,3) == 'gm-') {
					return $this->post($account, 'messages/' . $params['gmail_message_id'] . '/folders', $addRemoveParams, null, $httpHeadersToSet);
				}
				return $this->post($account, 'messages/gm-' . $params['gmail_message_id'] . '/folders', $addRemoveParams, null, $httpHeadersToSet);
			}
			else {
				throw new InvalidArgumentException('message_id, email_message_id or gmail_message_id is a required hash key');
			}
		}
	}

	/**
	 * Returns the message flags of a message.
	 * A message can be identified by the value of its Message-ID header
	 * @link http://context.io/docs/2.0/accounts/messages/flags
	 * @param string $account accountId of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: 'emailMessageId'
	 * @return ContextIOResponse
	 */
	public function setMessageFlags($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		$params = $this->_filterParams($params, array('message_id', 'email_message_id', 'gmail_message_id', 'seen','answered','flagged','deleted','draft'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		$flagParams = array();
		foreach (array('seen','answered','flagged','deleted','draft') as $currentFlagName) {
			if (array_key_exists($currentFlagName, $params)) {
				if (! is_bool($params[$currentFlagName])) {
					throw new InvalidArgumentException("$currentFlagName must be boolean");
				}
				$flagParams[$currentFlagName] = ($params[$currentFlagName] === true) ? 1 : 0;
			}
		}
		if (count(array_keys($flagParams)) == 0) {
			throw new InvalidArgumentException("must specify at least one of seen,answered,flagged,deleted,draft");
		}

		if (array_key_exists('email_message_id', $params)) {
			return $this->post($account, 'messages/' . rawurlencode($params['email_message_id']) . '/flags', $flagParams);
		}
		elseif (array_key_exists('message_id', $params)) {
			return $this->post($account, 'messages/' . $params['message_id'] . '/flags', $flagParams);
		}
		elseif (array_key_exists('gmail_message_id', $params)) {
			if (substr($params['gmail_message_id'],0,3) == 'gm-') {
				return $this->post($account, 'messages/' . $params['gmail_message_id'] . '/flags', $flagParams);
			}
			return $this->post($account, 'messages/gm-' . $params['gmail_message_id'] . '/flags', $flagParams);
		}
		else {
			throw new InvalidArgumentException('message_id, email_message_id or gmail_message_id is a required hash key');
		}
	}

	/**
	 * Returns the message body (excluding attachments) of a message.
	 * A message can be identified by the value of its Message-ID header
	 * or by the combination of the date sent timestamp and email address
	 * of the sender.
	 * @link http://context.io/docs/2.0/accounts/messages/body
	 * @param string $account accountId of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: 'emailMessageId', 'from', 'dateSent','type
	 * @return ContextIOResponse
	 */
	public function getMessageBody($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_string($params)) {
			return $this->get($account, 'messages/' . rawurlencode($params) . '/body');
		}
		$params = $this->_filterParams($params, array('message_id', 'email_message_id', 'gmail_message_id', 'type'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		$additionalParams = null;
		if (array_key_exists('type', $params)) {
			$additionalParams = array('type' => $params['type']);
		}
		if (array_key_exists('email_message_id', $params)) {
			return $this->get($account, 'messages/' . rawurlencode($params['email_message_id']) . '/body', $additionalParams);
		}
		elseif (array_key_exists('message_id', $params)) {
			return $this->get($account, 'messages/' . $params['message_id'] . '/body', $additionalParams);
		}
		elseif (array_key_exists('gmail_message_id', $params)) {
			if (substr($params['gmail_message_id'],0,3) == 'gm-') {
				return $this->get($account, 'messages/' . $params['gmail_message_id'] . '/body', $additionalParams);
			}
			return $this->get($account, 'messages/gm-' . $params['gmail_message_id'] . '/body', $additionalParams);
		}
		else {
			throw new InvalidArgumentException('message_id, email_message_id or gmail_message_id is a required hash key');
		}
	}

	/**
	 * Returns message and contact information about a given email thread.
	 * @link http://context.io/docs/2.0/accounts/messages/thread
	 * @param string $account accountId of the mailbox you want to query
	 * @param array[string]string $params Query parameters for the API call: 'email_message_id'
	 * @return ContextIOResponse
	 */
	public function getMessageThread($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_string($params)) {
			return $this->get($account, 'messages/' . rawurlencode($params) . '/thread');
		}
		$params = $this->_filterParams($params, array('message_id', 'email_message_id', 'gmail_message_id', 'include_body', 'include_headers', 'include_flags', 'type', 'include_person_info'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		if (array_key_exists('email_message_id', $params)) {
			return $this->get($account, 'messages/' . rawurlencode($params['email_message_id']) . '/thread', $params);
		}
		elseif (array_key_exists('message_id', $params)) {
			return $this->get($account, 'messages/' . $params['message_id'] . '/thread', $params);
		}
		elseif (array_key_exists('gmail_message_id', $params)) {
			if (substr($params['gmail_message_id'],0,3) == 'gm-') {
				return $this->get($account, 'messages/' . $params['gmail_message_id'] . '/thread', $params);
			}
			return $this->get($account, 'messages/gm-' . $params['gmail_message_id'] . '/thread', $params);
		}
		else {
			throw new InvalidArgumentException('message_id, email_message_id or gmail_message_id is a required hash key');
		}
	}

	/**
	 * Returns list of threads
	 * @link http://context.io/docs/2.0/accounts/threads
	 * @param string $account accountId of the mailbox you want to query
	 * @param array[string]string $params Query parameters for the API call: 'gmailthreadid'
	 * @return ContextIOResponse
	 */
	public function listThreads($account, $params=null) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_array($params)) {
			$params = $this->_filterParams($params, array('subject', 'indexed_after', 'indexed_before', 'active_after', 'active_before', 'started_after', 'started_before', 'limit', 'offset','email', 'to','from','cc','bcc','folder'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
			}
		}
		return $this->get($account, 'threads', $params);
	}

	/**
	 * Returns message and contact information about a given email thread.
	 * @link http://context.io/docs/2.0/accounts/threads
	 * @param string $account accountId of the mailbox you want to query
	 * @param array[string]string $params Query parameters for the API call: 'gmailthreadid'
	 * @return ContextIOResponse
	 */
	public function getThread($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		$params = $this->_filterParams($params, array('message_id', 'gmail_thread_id','gmail_message_id','email_message_id','include_body','include_headers','include_flags','type','include_person_info','limit','offset'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		if (array_key_exists('email_message_id', $params)) {
			return $this->get($account, 'messages/' . rawurlencode($params['email_message_id']) . '/thread', $params);
		}
		elseif (array_key_exists('message_id', $params)) {
			return $this->get($account, 'messages/' . $params['message_id'] . '/thread', $params);
		}
		elseif (array_key_exists('gmail_message_id', $params)) {
			if (substr($params['gmail_message_id'],0,3) == 'gm-') {
				return $this->get($account, 'messages/' . $params['gmail_message_id'] . '/thread', $params);
			}
			return $this->get($account, 'messages/gm-' . $params['gmail_message_id'] . '/thread', $params);
		}
		elseif (array_key_exists('gmail_thread_id', $params)) {
			if (substr($params['gmail_thread_id'],0,3) == 'gm-') {
				return $this->get($account, 'threads/' . $params['gmail_thread_id'], $params);
			}
			return $this->get($account, 'threads/gm-' . $params['gmail_thread_id'], $params);
		}
		else {
			throw new InvalidArgumentException('gmail_thread_id, messageId, email_message_id or gmail_message_id are required hash keys');
		}
	}

	/**
	 * delete thread
	 * @link http://context.io/docs/2.0/accounts/threads
	 * @param string $account accountId of the mailbox you want to query
	 * @param array[string]string $params Query parameters for the API call: 'gmailthreadid'
	 * @return ContextIOResponse
	 */
	public function deleteThread($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		$params = $this->_filterParams($params, array('gmail_thread_id'), array('gmail_thread_id'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		if (substr($params['gmail_thread_id'],0,3) == 'gm-') {
			return $this->delete($account, 'threads/' . $params['gmail_thread_id']);
		}
		return $this->delete($account, 'threads/gm-' . $params['gmail_thread_id']);
	}

	/**
	 * Sets the message folders of a thread.
	 * A thread can be identified by the value of its Gmail-ThreadId
	 * @param string $account accountId of the mailbox you want to query
	 * @param array[string]mixed $params Query parameters for the API call: 'gmail_thread_id'
	 * @return ContextIOResponse
	 */
	public function setThreadFolders($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		$params = $this->_filterParams($params, array('gmail_thread_id', 'add','remove','folders'), array('gmail_thread_id'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		$gmailThreadId = $params['gmail_thread_id'];
		if (! substr($params['gmail_thread_id'],0,3) == 'gm-') {
			$gmailThreadId = 'gm-' . $gmailThreadId;
		}
		if (array_key_exists('folders', $params)) {
			if (! is_array($params['folders'])) {
				throw new InvalidArgumentException("folders must be array");
			}
			$folderStr = json_encode($params['folders']);
			return $this->put($account, 'threads/' . $gmailThreadId . '/folders', $folderStr, array('Content-Type: application/json'));
		}
		else {
			$addRemoveParams = array();
			$convertToString = false;
			foreach (array('add','remove') as $currentName) {
				if (array_key_exists($currentName, $params)) {
					if (is_array($params[$currentName])) {
						$convertToString = true;
					}
					$addRemoveParams[$currentName] = $params[$currentName];
				}
			}
			if (count(array_keys($addRemoveParams)) == 0) {
				throw new InvalidArgumentException("must specify at least one of add,remove");
			}

			$httpHeadersToSet = array();
			if ($convertToString) {
				$httpHeadersToSet[] = 'Content-Type: application/x-www-form-urlencoded';
			}

			return $this->post($account, 'threads/' . $gmailThreadId . '/folders', $addRemoveParams, null, $httpHeadersToSet);
		}
	}


	public function addAccount($params) {
		$params = $this->_filterParams($params, array('email','first_name','last_name','type','server','username','provider_consumer_key','provider_token','provider_token_secret','service_level','sync_period','password','use_ssl','port','callback_url','sync_flags','raw_file_list','expunge_on_deleted_flag'), array('email'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		return $this->post(null, 'accounts', $params);
	}

	public function modifyAccount($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		$params = $this->_filterParams($params, array('first_name','last_name'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		return $this->post($account, '', $params);
	}

	public function getAccount($account) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		return $this->get($account);
	}

	public function deleteAccount($account) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		return $this->delete($account);
	}

	public function listAccountEmailAddresses($account) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		return $this->get($account, 'email_addresses');
	}

	public function addEmailAddressToAccount($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		$params = $this->_filterParams($params, array('email_address'), array('email_address'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		return $this->post($account, 'email_addresses', $params);
	}

	public function deleteEmailAddressFromAccount($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_string($params)) {
			return $this->delete($account, 'email_addresses/' . $params);
		}
		$params = $this->_filterParams($params, array('email_address'), array('email_address'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		return $this->delete($account, 'email_addresses/' . $params['email_address']);
	}

	public function setPrimaryEmailAddressForAccount($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_string($params)) {
			return $this->post($account, 'email_addresses/' . $params, array('primary' => 1));
		}
		$params = $this->_filterParams($params, array('email_address'), array('email_address'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		return $this->post($account, 'email_addresses/' . $params['email_address'], array('primary' => 1));
	}

	public function listAccounts($params=null) {
		if (is_array($params)) {
			$params = $this->_filterParams($params, array('limit','offset','email','status_ok','status'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
			}
		}
		return $this->get(null, 'accounts', $params);
	}

	/**
	 * Modify the IMAP server settings of an already indexed account
	 * @link http://context.io/docs/2.0/accounts/sources
	 * @param array[string]string $params Query parameters for the API call: 'credentials', 'mailboxes'
	 * @return ContextIOResponse
	 */
	public function modifySource($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		$params = $this->_filterParams($params, array('provider_token', 'provider_token_secret', 'provider_refresh_token', 'password', 'provider_consumer_key', 'label', 'mailboxes', 'expunge_on_deleted_flag', 'sync_all_folders', 'service_level','sync_period'), array('label'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		return $this->post($account, 'sources/' . $params['label'], $params);
	}

	public function resetSourceStatus($account, $params, $force=false) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_string($params)) {
			$params = array('label' => $params);
		}
		else {
			$params = $this->_filterParams($params, array('label'), array('label'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
			}
		}
		if ($force) {
			return $this->post($account, 'sources/' . $params['label'], array('force_status_check' => 1));
		}
		return $this->post($account, 'sources/' . $params['label'], array('status' => 1));
	}

	public function listSources($account, $params=null) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_array($params)) {
			$params = $this->_filterParams($params, array('status_ok','status'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
			}
		}
		return $this->get($account, 'sources', $params);
	}

	public function getSource($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_string($params)) {
			$params = array('label' => $params);
		}
		else {
			$params = $this->_filterParams($params, array('label'), array('label'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
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
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		$params = $this->_filterParams($params, array('type','email','server','username','provider_consumer_key','provider_token','provider_token_secret','provider_refresh_token','service_level','sync_period','sync_all_folders','origin_ip','sync_folders','password','use_ssl','port','callback_url','expunge_on_deleted_flag'), array('server','username'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
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
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_string($params)) {
			$params = array('label' => $params);
		}
		else {
			$params = $this->_filterParams($params, array('label'), array('label'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
			}
		}
		return $this->delete($account, 'sources/' . $params['label']);
	}

	public function syncSource($account, $params=array()) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		$params = $this->_filterParams($params, array('label'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		if ($params == array()) {
			return $this->post($account, 'sync');
		}
		return $this->post($account, 'sources/' . $params['label'] . '/sync');
	}

	public function getSync($account, $params=array()) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		$params = $this->_filterParams($params, array('label'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		if ($params == array()) {
			return $this->get($account, 'sync');
		}
		return $this->get($account, 'sources/' . $params['label'] . '/sync');
	}

	public function addFolderToSource($account, $params=array()) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		$params = $this->_filterParams($params, array('label','folder','delim'), array('label','folder'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		$path = 'sources/' . rawurlencode($params['label']) . '/folders/' . rawurlencode($params['folder']);
		if (array_key_exists('delim', $params)) {
			$path .= '?' . urlencode($params['delim']);
		}
		return $this->put($account, $path);
	}

	public function deleteFolderFromSource($account, $params=array()) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		$params = $this->_filterParams($params, array('label','folder','delim'), array('label','folder'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		if (array_key_exists('delim', $params)) {
			return $this->delete($account, 'sources/' . $params['label'] . '/folders/' . rawurlencode($params['folder']), array('delim' => $params['delim']));
		}
		return $this->delete($account, 'sources/' . $params['label'] . '/folders/' . rawurlencode($params['folder']));
	}

	public function sendMessage($account, $params=array()) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		$params = $this->_filterParams($params, array('label','rcpt','message','message_id', 'gmail_thread_id'), array('label'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		if (! array_key_exists('message_id', $params) && ! array_key_exists('message', $params) && ! array_key_exists('gmail_thread_id', $params)) {
			throw new InvalidArgumentException('gmail_thread_id, message_id or message is a required hash key');
		}

		return $this->post($account, 'exits/' . $params['label'], $params);
	}

	public function listSourceFolders($account, $params=array()) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_string($params)) {
			$params = array('label' => $params);
		}
		else {
			$params = $this->_filterParams($params, array('label','include_extended_counts','no_cache'), array('label'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
			}
		}
		$source = $params['label'];
		unset($params['label']);
		return $this->get($account, 'sources/' . $source . '/folders', $params);
	}

	public function getSourceFolder($account, $params=array()) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		$params = $this->_filterParams($params, array('label','folder'), array('label','folder'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		return $this->get($account, 'sources/' . rawurlencode($params['label']) . '/folders/' . rawurlencode($params['folder']));
	}

	public function listWebhooks($account) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		return $this->get($account, 'webhooks');
	}

	public function getWebhook($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_string($params)) {
			$params = array('webhook_id' => $params);
		}
		else {
			$params = $this->_filterParams($params, array('webhook_id'), array('webhook_id'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
			}
		}
		return $this->get($account, 'webhooks/' . $params['webhook_id']);
	}

	public function addWebhook($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		$params = $this->_filterParams($params, array('filter_to', 'filter_from', 'filter_cc', 'filter_subject', 'filter_thread', 'filter_new_important', 'filter_file_name', 'filter_file_revisions', 'sync_period', 'callback_url', 'failure_notif_url','filter_folder_added','filter_folder_removed','include_body','body_type'), array('callback_url','failure_notif_url'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		return $this->post($account, 'webhooks/', $params);
	}

	public function deleteWebhook($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		if (is_string($params)) {
			$params = array('webhook_id' => $params);
		}
		else {
			$params = $this->_filterParams($params, array('webhook_id'), array('webhook_id'));
			if ($params === false) {
				throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
			}
		}
		return $this->delete($account, 'webhooks/' . $params['webhook_id']);
	}

	public function modifyWebhook($account, $params) {
		if (is_null($account) || ! is_string($account) || (! strpos($account, '@') === false)) {
			throw new InvalidArgumentException('account must be string representing accountId');
		}
		$params = $this->_filterParams($params, array('webhook_id', 'active'), array('webhook_id','active'));
		if ($params === false) {
			throw new InvalidArgumentException("params array contains invalid parameters or misses required parameters");
		}
		return $this->post($account, 'webhooks/' . $params['webhook_id'], $params);
	}

	/**
	 * Specify the API endpoint.
	 * @param string $endPoint
	 * @return boolean success
	 */
	public function setEndPoint($endPoint) {
		$this->endPoint = $endPoint;
		return true;
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

	protected function get($account, $action='', $parameters=null, $acceptableContentTypes=null) {
		if (is_array($account)) {
			$tmp_results = array();
			foreach ($account as $accnt) {
				$result = $this->_doCall('GET', $accnt, $action, $parameters, null, $acceptableContentTypes);
				if ($result === false) {
					return false;
				}
				$tmp_results[$accnt] = $result;
			}
			return $tmp_results;
		}
		else {
			return $this->_doCall('GET', $account, $action, $parameters, null, $acceptableContentTypes);
		}
	}

	protected function put($account, $action, $parameters=null, $httpHeadersToSet=array()) {
		return $this->_doCall('PUT', $account, $action, $parameters, null, null, $httpHeadersToSet);
	}

	protected function post($account, $action='', $parameters=null, $file=null, $httpHeadersToSet=array()) {
		return $this->_doCall('POST', $account, $action, $parameters, $file, null, $httpHeadersToSet);
	}

	protected function delete($account, $action='', $parameters=null) {
		return $this->_doCall('DELETE', $account, $action, $parameters);
	}

	protected function _doCall($httpMethod, $account, $action, $parameters=null, $file=null, $acceptableContentTypes=null, $httpHeadersToSet=array()) {
		$consumer = new ContextIOExtLib\OAuthConsumer($this->oauthKey, $this->oauthSecret);
		if (! is_null($account)) {
			$action = 'accounts/' . $account . '/' . $action;
			if (substr($action,-1) == '/') {
				$action = substr($action,0,-1);
			}
		}
		$baseUrl = $this->build_url($action);
		$isMultiPartPost = (! is_null($file) && array_key_exists('field', $file) && array_key_exists('filename', $file));
		if ($isMultiPartPost || is_string($parameters)) {
			$this->authHeaders = true;
		}
		$signatureParams = $parameters;
		if ($isMultiPartPost) {
			$signatureParams = array();
		}
		if (is_string($parameters)) {
			$signatureParams = array();
		}
		if (($httpMethod != 'GET') && is_array($parameters)) {
			if (! in_array('Content-Type: application/x-www-form-urlencoded', $httpHeadersToSet)) {
				$signatureParams = array();
			}
			else {
				$newParams = '';
				foreach ($parameters as $key => $value) {
					if (!is_array($value)) {
						if ($newParams != '') {
							$newParams .= '&';
						}
						$newParams .= "$key=" . urlencode($value);
					}
					else {
						unset($signatureParams[$key]);
						$signatureParams[$key . '[]'] = $value;
						foreach ($value as $currentValue) {
							if ($newParams != '') {
								$newParams .= '&';
							}
							$newParams .= $key . '[]=' . urlencode($currentValue);
						}
					}
				}
				$parameters = $newParams;
			}

		}

		$req = ContextIOExtLib\OAuthRequest::from_consumer_and_token($consumer, null, $httpMethod, $baseUrl, $signatureParams);
		$sig_method = new ContextIOExtLib\OAuthSignatureMethod_HMAC_SHA1();
		$req->sign_request($sig_method, $consumer, null);

		//get data using signed url
		if ($this->authHeaders) {
			if ($httpMethod != 'POST') {
				$curl = curl_init((is_null($parameters) || is_string($parameters) || (count($parameters) == 0)) ? $baseUrl : $baseUrl. '?' . ContextIOExtLib\OAuthUtil::build_http_query($parameters));
			}
			else {
				$curl = curl_init($baseUrl);
			}
			$httpHeadersToSet[] = $req->to_header();
		}
		else {
			$curl = curl_init($req->to_url());
		}

		if ($this->ssl) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		}

		curl_setopt($curl, CURLOPT_USERAGENT, 'ContextIOLibrary/2.0 (PHP)');

		if ($httpMethod != 'GET') {
			if ($httpMethod == 'POST') {
				curl_setopt($curl, CURLOPT_POST, true);
				if (! is_null($parameters)) {
					if (is_null($file)) {
						if (is_string($parameters)) {
							$httpHeadersToSet[] = 'Content-Length: ' . strlen($parameters);
						}
					}
					else {
						$parameters[$file['field']] = $file['filename'];
					}
					curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
				}
				elseif (! is_null($file)) {
					curl_setopt($curl, CURLOPT_POSTFIELDS, array($file['field'] => $file['filename']));
				}
				else {
					$httpHeadersToSet[] = 'Content-Length: 0';
				}
			}
			else {
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $httpMethod);
				if ($httpMethod == 'PUT') {
					if (is_string($parameters)) {
						$httpHeadersToSet[] = 'Content-Length: ' . strlen($parameters);
					}
					curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters); 
				}
			}
		}
		if (count($httpHeadersToSet) > 0) {
			curl_setopt($curl, CURLOPT_HTTPHEADER, $httpHeadersToSet);
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
		$httpHeadersOut = ($this->saveHeaders) ? preg_split('/(\\n|\\r){1,2}/', curl_getinfo($curl, CURLINFO_HEADER_OUT)) : null;

		if (is_null($acceptableContentTypes)) {
			$response = new ContextIOResponse(
				curl_getinfo($curl, CURLINFO_HTTP_CODE),
				$httpHeadersOut,
				$httpHeadersIn,
				curl_getinfo($curl, CURLINFO_CONTENT_TYPE),
				$result);
		}
		else {
			$response = new ContextIOResponse(
				curl_getinfo($curl, CURLINFO_HTTP_CODE),
				$httpHeadersOut,
				$httpHeadersIn,
				curl_getinfo($curl, CURLINFO_CONTENT_TYPE),
				$result,
				$acceptableContentTypes);
		}
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

	protected function _filterParams($givenParams, $validParams, $requiredParams=array()) {
		$filteredParams = array();
		foreach ($givenParams as $name => $value) {
			if (in_array(strtolower($name), $validParams)) {
				$filteredParams[strtolower($name)] = $value;
			}
			else {
				return false;
			}
		}
		foreach ($requiredParams as $name) {
			if (! array_key_exists(strtolower($name), $filteredParams)) {
				return false;
			}
		}
		return $filteredParams;
	}

}


?>
