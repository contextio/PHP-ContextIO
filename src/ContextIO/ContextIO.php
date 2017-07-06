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

/**
 * Class to manage Context.IO API access
 */

namespace ContextIO;

class ContextIO
{
    
    /** @var RequestInterface */
    protected $requestClass;
    
    /**
     * Instantiate a new ContextIO object. Your OAuth consumer key and secret can be
     * found under the "settings" tab of the developer console (https://console.context.io/#settings)
     *
     * @param string $key                    Your Context.IO OAuth consumer key
     * @param string $secret                 Your Context.IO OAuth consumer secret
     * @param null|string $access_token      Acces token
     * @param string|null $access_token_secret
     * @param RequestInterface $requestClass Request class you want to use. Default to ContextIORequest
     */
    public function __construct(
        $key,
        $secret,
        $access_token = null,
        $access_token_secret = null,
        RequestInterface $requestClass = null
    ) {
        if (!$requestClass) {
            $requestClass = new ContextIORequest($key, $secret, $access_token, $access_token_secret);
        }
        $this->requestClass = $requestClass;
    }
    
    /**
     * Returns the request class.
     * @return RequestInterface
     */
    public function getRequestClass()
    {
        return $this->requestClass;
    }

    /**
     * Attempts to discover IMAP settings for a given email address
     * @link http://context.io/docs/2.0/discovery
     *
     * @param mixed $params either a string or assoc array
     *                      with email as its key
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function discovery($params)
    {
        if (is_string($params)) {
            $params = array('email' => $params);
        } else {
            $params = $this->checkFilterParams($params, array('email'), array('email'));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        
        return $this->requestClass->get(null, 'discovery?source_type=imap&email=' . rawurlencode($params[ 'email' ]));
    }
    
    /**
     * @link http://context.io/docs/2.0/connecttokens
     *
     * @param string $account accountId of the mailbox you want to query
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function listConnectTokens($account = null)
    {
        return $this->requestClass->get($account, 'connect_tokens');
    }
    
    /**
     * @link http://context.io/docs/2.0/connecttokens
     *
     * @param string $account  accountId of the mailbox you want to query
     * @param string[] $params Query parameters for the API call
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function getConnectToken($account = null, $params)
    {
        if (is_string($params)) {
            $params = array('token' => $params);
        } else {
            $params = $this->checkFilterParams($params, array('token'));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        
        return $this->requestClass->get($account, 'connect_tokens/' . $params[ 'token' ]);
    }
    
    /**xt.io/docs/2.0/connecttokens
     *
     * @param string $account  accountId of the mailbox you want to query
     * @param string[] $params Query parameters for the API call
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function addConnectToken($account = null, $params = array())
    {
        $params = $this->checkFilterParams($params, array(
            'service_level',
            'email',
            'callback_url',
            'first_name',
            'last_name',
            'source_expunge_on_deleted_flag',
            'source_sync_all_folders',
            'source_callback_url',
            'source_sync_flags',
            'source_raw_file_list',
        ), array('callback_url'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        
        return $this->requestClass->post(
            $account,
            'connect_tokens',
            $params,
            null,
            array('Content-Type: application/x-www-form-urlencoded')
        );
    }
    
    /**
     * @link http://context.io/docs/2.0/connecttokens
     *
     * @param string $account  accountId of the mailbox you want to query
     * @param string[] $params Query parameters for the API call
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function deleteConnectToken($account = null, $params)
    {
        if (is_string($params)) {
            $params = array('token' => $params);
        } else {
            $params = $this->checkFilterParams($params, array('token'), array('token'));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        
        return $this->requestClass->delete($account, 'connect_tokens/' . $params[ 'token' ]);
    }
    
    /**
     * @link http://context.io/docs/2.0/oauthproviders
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function listOAuthProviders()
    {
        return $this->requestClass->get(null, 'oauth_providers');
    }
    
    /**
     * @link http://context.io/docs/2.0/oauthproviders
     *
     * @param string[] $params Query parameters for the API call
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function getOAuthProvider($params)
    {
        if (is_string($params)) {
            $params = array('provider_consumer_key' => $params);
        } else {
            $params = $this->checkFilterParams($params, array('provider_consumer_key'), array('provider_consumer_key'));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        
        return $this->requestClass->get(null, 'oauth_providers/' . $params[ 'provider_consumer_key' ]);
    }
    
    /**
     * @link http://context.io/docs/2.0/oauthproviders
     *
     * @param string[] $params Query parameters for the API call
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function addOAuthProvider($params = array())
    {
        $params = $this->checkFilterParams($params, array('type', 'provider_consumer_key', 'provider_consumer_secret'),
            array('type', 'provider_consumer_key', 'provider_consumer_secret'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        
        return $this->requestClass->post(null, 'oauth_providers', $params);
    }
    
    /**
     * @link http://context.io/docs/2.0/oauthproviders
     *
     * @param string[] $params Query parameters for the API call
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function deleteOAuthProvider($params)
    {
        if (is_string($params)) {
            $params = array('provider_consumer_key' => $params);
        } else {
            $params = $this->checkFilterParams($params, array('provider_consumer_key'), array('provider_consumer_key'));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        
        return $this->requestClass->delete(null, 'oauth_providers/' . $params[ 'provider_consumer_key' ]);
    }
    
    /**
     * Returns the 20 contacts with whom the most emails were exchanged.
     * @link http://context.io/docs/2.0/accounts/contacts
     *
     * @param string $account  accountId of the mailbox you want to query
     * @param string[] $params Query parameters for the API call
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function listContacts($account, $params = null)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_array($params)) {
            $params = $this->checkFilterParams($params,
                array('active_after', 'active_before', 'limit', 'offset', 'search', 'sort_by', 'sort_order'));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        
        return $this->requestClass->get($account, 'contacts', $params);
    }
    
    public function getContact($account, $params = array())
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_string($params)) {
            $params = array('email' => $params);
        } else {
            $params = $this->checkFilterParams($params, array('email'), array('email'));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        
        return $this->requestClass->get($account, 'contacts/' . $params[ 'email' ]);
    }
    
    /**
     * @link http://context.io/docs/2.0/accounts/contacts/files
     *
     * @param string $account  accountId of the mailbox you want to query
     * @param string[] $params Query parameters for the API call
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function listContactFiles($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        $params = $this->checkFilterParams($params,
            array('email', 'limit', 'offset', 'scope', 'group_by_revisions', 'include_person_info'), array('email'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        
        return $this->requestClass->get($account, 'contacts/' . $params[ 'email' ] . '/files', $params);
    }
    
    /**
     * @link http://context.io/docs/2.0/accounts/contacts/messages
     *
     * @param string $account  accountId of the mailbox you want to query
     * @param string[] $params Query parameters for the API call
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function listContactMessages($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        $params = $this->checkFilterParams($params,
            array('email', 'limit', 'offset', 'scope', 'folder', 'include_person_info'), array('email'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        
        return $this->requestClass->get($account, 'contacts/' . $params[ 'email' ] . '/messages', $params);
    }
    
    /**
     * @link http://context.io/docs/2.0/accounts/contacts/threads
     *
     * @param string $account  accountId of the mailbox you want to query
     * @param string[] $params Query parameters for the API call
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function listContactThreads($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        $params = $this->checkFilterParams($params, array('email', 'limit', 'offset', 'scope', 'folder'),
            array('email'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        
        return $this->requestClass->get($account, 'contacts/' . $params[ 'email' ] . '/threads', $params);
    }
    
    /**
     * @link http://context.io/docs/2.0/accounts/files
     *
     * @param string $account  accountId of the mailbox you want to query
     * @param string[] $params Query parameters for the API call: indexed_after, limit
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function listFiles($account, $params = null)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_array($params)) {
            $params = $this->checkFilterParams($params, array(
                'indexed_before',
                'indexed_after',
                'date_before',
                'date_after',
                'file_name',
                'limit',
                'offset',
                'email',
                'to',
                'from',
                'cc',
                'bcc',
                'group_by_revisions',
                'include_person_info',
                'source',
            ));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        
        return $this->requestClass->get($account, 'files', $params);
    }
    
    public function getFile($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_string($params)) {
            $params = array('file_id' => $params);
        } else {
            $params = $this->checkFilterParams($params, array('file_id'), array('file_id'));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        
        return $this->requestClass->get($account, 'files/' . $params[ 'file_id' ]);
    }
    
    public function getFileURL($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_string($params)) {
            $params = array('file_id' => $params);
        } else {
            $params = $this->checkFilterParams($params, array('file_id'), array('file_id'));
        }
        
        return $this->requestClass->get($account, 'files/' . $params[ 'file_id' ] . '/content', array('as_link' => 1),
            array('text/uri-list'));
    }
    
    /**
     * Returns the content a given attachment. If you want to save the attachment to
     * a file, set $saveAs to the destination file name. If $saveAs is left to null,
     * the function will return the file data.
     * on the
     * @link http://context.io/docs/2.0/accounts/files/content
     *
     * @param string $account accountId of the mailbox you want to query
     * @param array           [string]string $params Query parameters for the API call: 'fileId'
     *
     * @return mixed
     */
    public function getFileContent($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_string($params)) {
            $params = array('file_id' => $params);
        } else {
            $params = $this->checkFilterParams($params, array('file_id', 'as_link'), array('file_id'));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        
        return $this->requestClass->get($account, 'files/' . $params[ 'file_id' ] . '/content');
    }
    
    /**
     * Given two files, this will return the list of insertions and deletions made
     * from the oldest of the two files to the newest one.
     * @link http://context.io/docs/2.0/accounts/files/changes
     *
     * @param string $account accountId of the mailbox you want to query
     * @param array           [string]string $params Query parameters for the API call: 'fileId1', 'fileId2'
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function getFileChanges($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        $params = $this->checkFilterParams($params, array('file_id1', 'file_id2', 'generate'),
            array('file_id1', 'file_id2'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        $newParams = array(
            'file_id' => $params[ 'file_id2' ],
        );
        if (!array_key_exists('generate', $params)) {
            $newParams[ 'generate' ] = 1;
        } else {
            $newParams[ 'generate' ] = $params[ 'generate' ];
        }
        
        return $this->requestClass->get($account, 'files/' . $params[ 'file_id1' ] . '/changes', $newParams);
    }
    
    /**
     * Returns a list of revisions attached to other emails in the
     * mailbox for one or more given files (see fileid parameter below).
     * @link http://context.io/docs/2.0/accounts/files/revisions
     *
     * @param string $account accountId of the mailbox you want to query
     * @param array           [string]string $params Query parameters for the API call: 'fileId', 'fileName'
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function listFileRevisions($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_string($params)) {
            $params = array('file_id' => $params);
        } else {
            $params = $this->checkFilterParams($params, array('file_id', 'include_person_info'), array('file_id'));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        
        return $this->requestClass->get($account, 'files/' . $params[ 'file_id' ] . '/revisions', $params);
    }
    
    /**
     * Returns a list of files that are related to the given file.
     * Currently, relation between files is based on how similar their names are.
     * You must specify either the fileId of fileName parameter
     * @link http://context.io/docs/2.0/accounts/files/related
     *
     * @param string $account accountId of the mailbox you want to query
     * @param array           [string]string $params Query parameters for the API call: 'fileId', 'fileName'
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function listFileRelated($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_string($params)) {
            $params = array('file_id' => $params);
        } else {
            $params = $this->checkFilterParams($params, array('file_id', 'include_person_info'), array('file_id'));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        
        return $this->requestClass->get($account, 'files/' . $params[ 'file_id' ] . '/related', $params);
    }
    
    /**
     * Returns message information
     *
     * @param string $account accountId of the mailbox you want to query
     * @param array           [string]mixed $params Query parameters for the API call: 'subject', 'limit'
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function listMessagesBySourceAndFolder($account, $params = null)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_array($params)) {
            $params = $this->checkFilterParams($params, array(
                'label',
                'folder',
                'limit',
                'offset',
                'type',
                'include_body',
                'include_headers',
                'include_flags',
                'flag_seen',
                'async',
                'async_job_id',
            ), array('label', 'folder'));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        $source = $params[ 'label' ];
        $folder = $params[ 'folder' ];
        unset($params[ 'label' ]);
        unset($params[ 'folder' ]);
        if (array_key_exists('async_job_id', $params)) {
            return $this->requestClass->get($account, "sources/$source/folders/$folder/messages/" . $params[ 'async_job_id' ]);
        }
        
        return $this->requestClass->get($account, "sources/$source/folders/$folder/messages", $params);
    }
    
    /**
     * Returns message information
     * @link http://context.io/docs/2.0/accounts/messages
     *
     * @param string $account accountId of the mailbox you want to query
     * @param array           [string]mixed $params Query parameters for the API call: 'subject', 'limit'
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function listMessages($account, $params = null)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_array($params)) {
            $params = $this->checkFilterParams($params, array(
                'subject',
                'date_before',
                'date_after',
                'indexed_after',
                'indexed_before',
                'limit',
                'offset',
                'email',
                'to',
                'from',
                'cc',
                'bcc',
                'email_message_id',
                'type',
                'body_type',
                'include_body',
                'include_headers',
                'include_flags',
                'folder',
                'gm_search',
                'include_person_info',
                'file_name',
                'file_size_min',
                'file_size_max',
                'source',
                'include_thread_size',
                'include_source',
                'sort_order',
            ));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        
        return $this->requestClass->get($account, 'messages', $params);
    }
    
    public function addMessageToFolder($account, $params = array())
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        $requiredParams = array('dst_folder');
        if (array_key_exists('src_file', $params)) {
            // $requiredParams[] = 'dst_label';
        }
        $params = $this->checkFilterParams($params, array(
            'dst_source',
            'dst_label',
            'dst_folder',
            'src_file',
            'message_id',
            'email_message_id',
            'gmail_message_id',
            'flag_seen',
            'flag_answered',
            'flag_flagged',
            'flag_deleted',
            'flag_draft',
            'move',
        ), $requiredParams);
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        if (array_key_exists('move', $params)) {
            if (($params[ 'move' ] === true) || ($params[ 'move' ] == 1)) {
                $params[ 'move' ] = 1;
            } elseif (($params[ 'move' ] === false) || ($params[ 'move' ] == 0)) {
                unset($params[ 'move' ]);
            } else {
                throw new \InvalidArgumentException("move parameter must be boolean or 0/1");
            }
        }
        if (array_key_exists('src_file', $params)) {
            $params[ 'src_file' ] = realpath($params[ 'src_file' ]);
            if (($params[ 'src_file' ] === false) || !is_readable($params[ 'src_file' ])) {
                throw new \InvalidArgumentException("invalid source file");
            }
            $src_file = '@' . $params[ 'src_file' ];
            unset($params[ 'src_file' ]);
            
            return $this->requestClass->post($account, 'messages', $params, array('field' => 'message', 'filename' => $src_file));
        } elseif (array_key_exists('message_id', $params)) {
            return $this->requestClass->post($account, 'messages/' . $params[ 'message_id' ], $params);
        } elseif (array_key_exists('email_message_id', $params)) {
            return $this->requestClass->post($account, 'messages/' . rawurlencode($params[ 'email_message_id' ]), $params);
        } elseif (array_key_exists('gmail_message_id', $params)) {
            if (substr($params[ 'gmail_message_id' ], 0, 3) == 'gm-') {
                return $this->requestClass->post($account, 'messages/' . $params[ 'gmail_message_id' ], $params);
            }
            
            return $this->requestClass->post($account, 'messages/gm-' . $params[ 'gmail_message_id' ], $params);
        } else {
            throw new \InvalidArgumentException('src_file, message_id, email_message_id or gmail_message_id is a required hash key');
        }
    }
    
    /**
     * Returns document and contact information about a message.
     * A message can be identified by the value of its Message-ID header
     * @link http://context.io/docs/2.0/accounts/messages#id-get
     *
     * @param string $account accountId of the mailbox you want to query
     * @param array           [string]mixed $params Query parameters for the API call: 'emailMessageId'
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function getMessage($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_string($params)) {
            return $this->requestClass->get($account, 'messages/' . rawurlencode($params));
        } else {
            $params = $this->checkFilterParams($params, array(
                'message_id',
                'email_message_id',
                'gmail_message_id',
                'include_person_info',
                'type',
                'include_thread_size',
                'include_body',
                'include_headers',
                'include_flags',
                'body_type',
                'include_source',
            ));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
            if (array_key_exists('message_id', $params)) {
                return $this->requestClass->get($account, 'messages/' . $params[ 'message_id' ], $params);
            } elseif (array_key_exists('email_message_id', $params)) {
                return $this->requestClass->get($account, 'messages/' . rawurlencode($params[ 'email_message_id' ]), $params);
            } elseif (array_key_exists('gmail_message_id', $params)) {
                if (substr($params[ 'gmail_message_id' ], 0, 3) == 'gm-') {
                    return $this->requestClass->get($account, 'messages/' . $params[ 'gmail_message_id' ], $params);
                }
                
                return $this->requestClass->get($account, 'messages/gm-' . $params[ 'gmail_message_id' ], $params);
            } else {
                throw new \InvalidArgumentException('message_id, email_message_id or gmail_message_id is a required hash key');
            }
        }
    }
    
    public function deleteMessage($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_string($params)) {
            return $this->requestClass->delete($account, 'messages/' . rawurlencode($params));
        } else {
            $params = $this->checkFilterParams($params, array('message_id', 'email_message_id', 'gmail_message_id'));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
            if (array_key_exists('message_id', $params)) {
                return $this->requestClass->delete($account, 'messages/' . $params[ 'message_id' ]);
            } elseif (array_key_exists('email_message_id', $params)) {
                return $this->requestClass->delete($account, 'messages/' . rawurlencode($params[ 'email_message_id' ]));
            } elseif (array_key_exists('gmail_message_id', $params)) {
                if (substr($params[ 'gmail_message_id' ], 0, 3) == 'gm-') {
                    return $this->requestClass->delete($account, 'messages/' . $params[ 'gmail_message_id' ]);
                }
                
                return $this->requestClass->delete($account, 'messages/gm-' . $params[ 'gmail_message_id' ]);
            } else {
                throw new \InvalidArgumentException('message_id, email_message_id or gmail_message_id is a required hash key');
            }
        }
    }
    
    /**
     * Returns the message headers of a message.
     * A message can be identified by the value of its Message-ID header
     * @link http://context.io/docs/2.0/accounts/messages/headers
     *
     * @param string $account accountId of the mailbox you want to query
     * @param array           [string]mixed $params Query parameters for the API call: 'emailMessageId'
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function getMessageHeaders($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_string($params)) {
            return $this->requestClass->get($account, 'messages/' . rawurlencode($params) . '/headers');
        } else {
            $params = $this->checkFilterParams($params,
                array('message_id', 'email_message_id', 'gmail_message_id', 'raw'),
                array());
            $additionalParams = null;
            if (array_key_exists('raw', $params)) {
                $additionalParams = array('raw' => $params[ 'raw' ]);
            }
            if (array_key_exists('message_id', $params)) {
                return $this->requestClass->get($account, 'messages/' . $params[ 'message_id' ] . '/headers', $additionalParams);
            } elseif (array_key_exists('email_message_id', $params)) {
                return $this->requestClass->get($account, 'messages/' . rawurlencode($params[ 'email_message_id' ]) . '/headers',
                    $additionalParams);
            } elseif (array_key_exists('gmail_message_id', $params)) {
                if (substr($params[ 'gmail_message_id' ], 0, 3) == 'gm-') {
                    return $this->requestClass->get($account, 'messages/' . $params[ 'gmail_message_id' ] . '/headers',
                        $additionalParams);
                }
                
                return $this->requestClass->get($account, 'messages/gm-' . $params[ 'gmail_message_id' ] . '/headers',
                    $additionalParams);
            } else {
                throw new \InvalidArgumentException('message_id, email_message_id or gmail_message_id is a required hash key');
            }
        }
    }
    
    /**
     * Returns the message source of a message.
     * A message can be identified by the value of its Message-ID header
     * @link http://context.io/docs/2.0/accounts/messages/source
     *
     * @param string $account     accountId of the mailbox you want to query
     * @param string[] $params    Query parameters for the API call: 'emailMessageId'
     *
     * @return bool|\ContextIO\ContextIOResponse
     */
    public function getMessageSource($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_string($params)) {
            $url = 'messages/' . rawurlencode($params) . '/source';
        } elseif (array_key_exists('message_id', $params)) {
            $url = 'messages/' . $params[ 'message_id' ] . '/source';
        } elseif (array_key_exists('email_message_id', $params)) {
            $url = 'messages/' . rawurlencode($params[ 'email_message_id' ]) . '/source';
        } elseif (array_key_exists('gmail_message_id', $params)) {
            if (substr($params[ 'gmail_message_id' ], 0, 3) == 'gm-') {
                $url = 'messages/' . $params[ 'gmail_message_id' ] . '/source';
            } else {
                $url = 'messages/gm-' . $params[ 'gmail_message_id' ] . '/source';
            }
        } else {
            throw new \InvalidArgumentException('message_id, email_message_id or gmail_message_id is a required hash key');
        }
        
        return $this->requestClass->get($account, $url);
    }
    
    /**
     * Returns the message flags of a message.
     * A message can be identified by the value of its Message-ID header
     * @link http://context.io/docs/2.0/accounts/messages/flags
     *
     * @param string $account accountId of the mailbox you want to query
     * @param array           [string]mixed $params Query parameters for the API call: 'emailMessageId'
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function getMessageFlags($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_string($params)) {
            return $this->requestClass->get($account, 'messages/' . rawurlencode($params) . '/flags');
        } elseif (array_key_exists('message_id', $params)) {
            return $this->requestClass->get($account, 'messages/' . $params[ 'message_id' ] . '/flags');
        } elseif (array_key_exists('email_message_id', $params)) {
            return $this->requestClass->get($account, 'messages/' . rawurlencode($params[ 'email_message_id' ]) . '/flags');
        } elseif (array_key_exists('gmail_message_id', $params)) {
            if (substr($params[ 'gmail_message_id' ], 0, 3) == 'gm-') {
                return $this->requestClass->get($account, 'messages/' . $params[ 'gmail_message_id' ] . '/flags');
            }
            
            return $this->requestClass->get($account, 'messages/gm-' . $params[ 'gmail_message_id' ] . '/flags');
        } else {
            throw new \InvalidArgumentException('message_id, email_message_id or gmail_message_id is a required hash key');
        }
    }
    
    /**
     * Returns the folders the message is part of.
     * A message can be identified by the value of its Message-ID header
     * @link http://context.io/docs/2.0/accounts/messages/folders
     *
     * @param string $account accountId
     * @param array           [string]mixed $params Query parameters for the API call: 'emailMessageId'
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function getMessageFolders($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_string($params)) {
            return $this->requestClass->get($account, 'messages/' . rawurlencode($params) . '/folders');
        } elseif (array_key_exists('message_id', $params)) {
            return $this->requestClass->get($account, 'messages/' . $params[ 'message_id' ] . '/folders');
        } elseif (array_key_exists('email_message_id', $params)) {
            return $this->requestClass->get($account, 'messages/' . rawurlencode($params[ 'email_message_id' ]) . '/folders');
        } elseif (array_key_exists('gmail_message_id', $params)) {
            if (substr($params[ 'gmail_message_id' ], 0, 3) == 'gm-') {
                return $this->requestClass->get($account, 'messages/' . $params[ 'gmail_message_id' ] . '/folders');
            }
            
            return $this->requestClass->get($account, 'messages/gm-' . $params[ 'gmail_message_id' ] . '/folders');
        } else {
            throw new \InvalidArgumentException('message_id, email_message_id or gmail_message_id is a required hash key');
        }
    }
    
    /**
     * Sets the message folders of a message.
     * A message can be identified by the value of its Message-ID header
     *
     * @param string $account accountId of the mailbox you want to query
     * @param array           [string]mixed $params Query parameters for the API call: 'emailMessageId'
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function setMessageFolders($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        $params = $this->checkFilterParams($params,
            array('message_id', 'email_message_id', 'gmail_message_id', 'add', 'remove', 'folders'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        if (array_key_exists('folders', $params)) {
            if (!is_array($params[ 'folders' ])) {
                throw new \InvalidArgumentException("folders must be array");
            }
            $folderStr = json_encode($params[ 'folders' ]);
            if (array_key_exists('email_message_id', $params)) {
                return $this->requestClass->put($account, 'messages/' . rawurlencode($params[ 'email_message_id' ]) . '/folders',
                    $folderStr, array('Content-Type: application/json'));
            } elseif (array_key_exists('message_id', $params)) {
                return $this->requestClass->put($account, 'messages/' . $params[ 'message_id' ] . '/folders', $folderStr,
                    array('Content-Type: application/json'));
            } elseif (array_key_exists('gmail_message_id', $params)) {
                if (substr($params[ 'gmail_message_id' ], 0, 3) == 'gm-') {
                    return $this->requestClass->put($account, 'messages/' . $params[ 'gmail_message_id' ] . '/folders', $folderStr,
                        array('Content-Type: application/json'));
                }
                
                return $this->requestClass->put($account, 'messages/gm-' . $params[ 'gmail_message_id' ] . '/folders', $folderStr,
                    array('Content-Type: application/json'));
            } else {
                throw new \InvalidArgumentException('message_id, email_message_id or gmail_message_id is a required hash key');
            }
        } else {
            $addRemoveParams = array();
            $convertToString = false;
            foreach (array('add', 'remove') as $currentName) {
                if (array_key_exists($currentName, $params)) {
                    if (is_array($params[ $currentName ])) {
                        $convertToString = true;
                    }
                    $addRemoveParams[ $currentName ] = $params[ $currentName ];
                }
            }
            if (count(array_keys($addRemoveParams)) == 0) {
                throw new \InvalidArgumentException("must specify at least one of add,remove");
            }
            
            $httpHeadersToSet = array();
            if ($convertToString) {
                $httpHeadersToSet[] = 'Content-Type: application/x-www-form-urlencoded';
            }
            
            if (array_key_exists('email_message_id', $params)) {
                return $this->requestClass->post($account, 'messages/' . rawurlencode($params[ 'email_message_id' ]) . '/folders',
                    $addRemoveParams, null, $httpHeadersToSet);
            } elseif (array_key_exists('message_id', $params)) {
                return $this->requestClass->post($account, 'messages/' . $params[ 'message_id' ] . '/folders', $addRemoveParams, null,
                    $httpHeadersToSet);
            } elseif (array_key_exists('gmail_message_id', $params)) {
                if (substr($params[ 'gmail_message_id' ], 0, 3) == 'gm-') {
                    return $this->requestClass->post($account, 'messages/' . $params[ 'gmail_message_id' ] . '/folders',
                        $addRemoveParams, null, $httpHeadersToSet);
                }
                
                return $this->requestClass->post($account, 'messages/gm-' . $params[ 'gmail_message_id' ] . '/folders',
                    $addRemoveParams, null, $httpHeadersToSet);
            } else {
                throw new \InvalidArgumentException('message_id, email_message_id or gmail_message_id is a required hash key');
            }
        }
    }
    
    /**
     * Returns the message flags of a message.
     * A message can be identified by the value of its Message-ID header
     * @link http://context.io/docs/2.0/accounts/messages/flags
     *
     * @param string $account accountId of the mailbox you want to query
     * @param array           [string]mixed $params Query parameters for the API call: 'emailMessageId'
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function setMessageFlags($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        $params = $this->checkFilterParams($params, array(
            'message_id',
            'email_message_id',
            'gmail_message_id',
            'seen',
            'answered',
            'flagged',
            'deleted',
            'draft',
        ));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        $flagParams = array();
        foreach (array('seen', 'answered', 'flagged', 'deleted', 'draft') as $currentFlagName) {
            if (array_key_exists($currentFlagName, $params)) {
                if (!is_bool($params[ $currentFlagName ])) {
                    throw new \InvalidArgumentException("$currentFlagName must be boolean");
                }
                $flagParams[ $currentFlagName ] = ($params[ $currentFlagName ] === true) ? 1 : 0;
            }
        }
        if (count(array_keys($flagParams)) == 0) {
            throw new \InvalidArgumentException("must specify at least one of seen,answered,flagged,deleted,draft");
        }
        
        if (array_key_exists('email_message_id', $params)) {
            return $this->requestClass->post($account, 'messages/' . rawurlencode($params[ 'email_message_id' ]) . '/flags',
                $flagParams);
        } elseif (array_key_exists('message_id', $params)) {
            return $this->requestClass->post($account, 'messages/' . $params[ 'message_id' ] . '/flags', $flagParams);
        } elseif (array_key_exists('gmail_message_id', $params)) {
            if (substr($params[ 'gmail_message_id' ], 0, 3) == 'gm-') {
                return $this->requestClass->post($account, 'messages/' . $params[ 'gmail_message_id' ] . '/flags', $flagParams);
            }
            
            return $this->requestClass->post($account, 'messages/gm-' . $params[ 'gmail_message_id' ] . '/flags', $flagParams);
        } else {
            throw new \InvalidArgumentException('message_id, email_message_id or gmail_message_id is a required hash key');
        }
    }
    
    /**
     * Returns the message body (excluding attachments) of a message.
     * A message can be identified by the value of its Message-ID header
     * or by the combination of the date sent timestamp and email address
     * of the sender.
     * @link http://context.io/docs/2.0/accounts/messages/body
     *
     * @param string $account accountId of the mailbox you want to query
     * @param array $params   Query  parameters for the  API  call: 'emailMessageId', 'from', 'dateSent','type
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function getMessageBody($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_string($params)) {
            return $this->requestClass->get($account, 'messages/' . rawurlencode($params) . '/body');
        }
        $params = $this->checkFilterParams($params,
            array('message_id', 'email_message_id', 'gmail_message_id', 'type'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        $additionalParams = null;
        if (array_key_exists('type', $params)) {
            $additionalParams = array('type' => $params[ 'type' ]);
        }
        if (array_key_exists('email_message_id', $params)) {
            return $this->requestClass->get($account, 'messages/' . rawurlencode($params[ 'email_message_id' ]) . '/body',
                $additionalParams);
        } elseif (array_key_exists('message_id', $params)) {
            return $this->requestClass->get($account, 'messages/' . $params[ 'message_id' ] . '/body', $additionalParams);
        } elseif (array_key_exists('gmail_message_id', $params)) {
            if (substr($params[ 'gmail_message_id' ], 0, 3) == 'gm-') {
                return $this->requestClass->get($account, 'messages/' . $params[ 'gmail_message_id' ] . '/body', $additionalParams);
            }
            
            return $this->requestClass->get($account, 'messages/gm-' . $params[ 'gmail_message_id' ] . '/body', $additionalParams);
        } else {
            throw new \InvalidArgumentException('message_id, email_message_id or gmail_message_id is a required hash key');
        }
    }
    
    /**
     * Returns message and contact information about a given email thread.
     * @link http://context.io/docs/2.0/accounts/messages/thread
     *
     * @param string $account accountId of the mailbox you want to query
     * @param array           [string]string $params Query parameters for the API call: 'email_message_id'
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function getMessageThread($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_string($params)) {
            return $this->requestClass->get($account, 'messages/' . rawurlencode($params) . '/thread');
        }
        $params = $this->checkFilterParams($params, array(
            'message_id',
            'email_message_id',
            'gmail_message_id',
            'include_body',
            'include_headers',
            'include_flags',
            'type',
            'include_person_info',
        ));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        if (array_key_exists('email_message_id', $params)) {
            return $this->requestClass->get($account, 'messages/' . rawurlencode($params[ 'email_message_id' ]) . '/thread', $params);
        } elseif (array_key_exists('message_id', $params)) {
            return $this->requestClass->get($account, 'messages/' . $params[ 'message_id' ] . '/thread', $params);
        } elseif (array_key_exists('gmail_message_id', $params)) {
            if (substr($params[ 'gmail_message_id' ], 0, 3) == 'gm-') {
                return $this->requestClass->get($account, 'messages/' . $params[ 'gmail_message_id' ] . '/thread', $params);
            }
            
            return $this->requestClass->get($account, 'messages/gm-' . $params[ 'gmail_message_id' ] . '/thread', $params);
        } else {
            throw new \InvalidArgumentException('message_id, email_message_id or gmail_message_id is a required hash key');
        }
    }
    
    /**
     * Returns list of threads
     * @link http://context.io/docs/2.0/accounts/threads
     *
     * @param string $account accountId of the mailbox you want to query
     * @param array           [string]string $params Query parameters for the API call: 'gmailthreadid'
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function listThreads($account, $params = null)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_array($params)) {
            $params = $this->checkFilterParams($params, array(
                'subject',
                'indexed_after',
                'indexed_before',
                'active_after',
                'active_before',
                'started_after',
                'started_before',
                'limit',
                'offset',
                'email',
                'to',
                'from',
                'cc',
                'bcc',
                'folder',
            ));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        
        return $this->requestClass->get($account, 'threads', $params);
    }
    
    /**
     * Returns message and contact information about a given email thread.
     * @link http://context.io/docs/2.0/accounts/threads
     *
     * @param string $account accountId of the mailbox you want to query
     * @param array           [string]string $params Query parameters for the API call: 'gmailthreadid'
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function getThread($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        $params = $this->checkFilterParams($params, array(
            'message_id',
            'gmail_thread_id',
            'gmail_message_id',
            'email_message_id',
            'include_body',
            'include_headers',
            'include_flags',
            'type',
            'include_person_info',
            'limit',
            'offset',
        ));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        if (array_key_exists('email_message_id', $params)) {
            return $this->requestClass->get($account, 'messages/' . rawurlencode($params[ 'email_message_id' ]) . '/thread', $params);
        } elseif (array_key_exists('message_id', $params)) {
            return $this->requestClass->get($account, 'messages/' . $params[ 'message_id' ] . '/thread', $params);
        } elseif (array_key_exists('gmail_message_id', $params)) {
            if (substr($params[ 'gmail_message_id' ], 0, 3) == 'gm-') {
                return $this->requestClass->get($account, 'messages/' . $params[ 'gmail_message_id' ] . '/thread', $params);
            }
            
            return $this->requestClass->get($account, 'messages/gm-' . $params[ 'gmail_message_id' ] . '/thread', $params);
        } elseif (array_key_exists('gmail_thread_id', $params)) {
            if (substr($params[ 'gmail_thread_id' ], 0, 3) == 'gm-') {
                return $this->requestClass->get($account, 'threads/' . $params[ 'gmail_thread_id' ], $params);
            }
            
            return $this->requestClass->get($account, 'threads/gm-' . $params[ 'gmail_thread_id' ], $params);
        } else {
            throw new \InvalidArgumentException('gmail_thread_id, messageId, email_message_id or gmail_message_id are required hash keys');
        }
    }
    
    /**
     * delete thread
     * @link http://context.io/docs/2.0/accounts/threads
     *
     * @param string $account accountId of the mailbox you want to query
     * @param array           [string]string $params Query parameters for the API call: 'gmailthreadid'
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function deleteThread($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        $params = $this->checkFilterParams($params, array('gmail_thread_id'), array('gmail_thread_id'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        if (substr($params[ 'gmail_thread_id' ], 0, 3) == 'gm-') {
            return $this->requestClass->delete($account, 'threads/' . $params[ 'gmail_thread_id' ]);
        }
        
        return $this->requestClass->delete($account, 'threads/gm-' . $params[ 'gmail_thread_id' ]);
    }
    
    /**
     * Sets the message folders of a thread.
     * A thread can be identified by the value of its Gmail-ThreadId
     *
     * @param string $account accountId of the mailbox you want to query
     * @param array           [string]mixed $params Query parameters for the API call: 'gmail_thread_id'
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function setThreadFolders($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        $params = $this->checkFilterParams($params, array('gmail_thread_id', 'add', 'remove', 'folders'),
            array('gmail_thread_id'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        $gmailThreadId = $params[ 'gmail_thread_id' ];
        if (!substr($params[ 'gmail_thread_id' ], 0, 3) == 'gm-') {
            $gmailThreadId = 'gm-' . $gmailThreadId;
        }
        if (array_key_exists('folders', $params)) {
            if (!is_array($params[ 'folders' ])) {
                throw new \InvalidArgumentException("folders must be array");
            }
            $folderStr = json_encode($params[ 'folders' ]);
            
            return $this->requestClass->put($account, 'threads/' . $gmailThreadId . '/folders', $folderStr,
                array('Content-Type: application/json'));
        } else {
            $addRemoveParams = array();
            $convertToString = false;
            foreach (array('add', 'remove') as $currentName) {
                if (array_key_exists($currentName, $params)) {
                    if (is_array($params[ $currentName ])) {
                        $convertToString = true;
                    }
                    $addRemoveParams[ $currentName ] = $params[ $currentName ];
                }
            }
            if (count(array_keys($addRemoveParams)) == 0) {
                throw new \InvalidArgumentException("must specify at least one of add,remove");
            }
            
            $httpHeadersToSet = array();
            if ($convertToString) {
                $httpHeadersToSet[] = 'Content-Type: application/x-www-form-urlencoded';
            }
            
            return $this->requestClass->post($account, 'threads/' . $gmailThreadId . '/folders', $addRemoveParams, null,
                $httpHeadersToSet);
        }
    }
    
    public function addAccount($params)
    {
        $params = $this->checkFilterParams($params, array(
            'email',
            'first_name',
            'last_name',
            'type',
            'server',
            'username',
            'provider_consumer_key',
            'provider_token',
            'provider_token_secret',
            'provider_refresh_token',
            'service_level',
            'sync_period',
            'password',
            'use_ssl',
            'port',
            'callback_url',
            'sync_flags',
            'raw_file_list',
            'expunge_on_deleted_flag',
            'migrate_account_id',
        ), array('email'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        
        return $this->requestClass->post(null, 'accounts', $params);
    }
    
    public function modifyAccount($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        $params = $this->checkFilterParams($params, array('first_name', 'last_name'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        
        return $this->requestClass->post($account, '', $params);
    }
    
    public function getAccount($account)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        
        return $this->requestClass->get($account);
    }
    
    public function deleteAccount($account)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        
        return $this->requestClass->delete($account);
    }
    
    public function listAccountEmailAddresses($account)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        
        return $this->requestClass->get($account, 'email_addresses');
    }
    
    public function addEmailAddressToAccount($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        $params = $this->checkFilterParams($params, array('email_address'), array('email_address'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        
        return $this->requestClass->post($account, 'email_addresses', $params);
    }
    
    public function deleteEmailAddressFromAccount($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_string($params)) {
            return $this->requestClass->delete($account, 'email_addresses/' . $params);
        }
        $params = $this->checkFilterParams($params, array('email_address'), array('email_address'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        
        return $this->requestClass->delete($account, 'email_addresses/' . $params[ 'email_address' ]);
    }
    
    public function setPrimaryEmailAddressForAccount($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_string($params)) {
            return $this->requestClass->post($account, 'email_addresses/' . $params, array('primary' => 1));
        }
        $params = $this->checkFilterParams($params, array('email_address'), array('email_address'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        
        return $this->requestClass->post($account, 'email_addresses/' . $params[ 'email_address' ], array('primary' => 1));
    }
    
    public function listAccounts($params = null)
    {
        if (is_array($params)) {
            $params = $this->checkFilterParams($params, array('limit', 'offset', 'email', 'status_ok', 'status'));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        
        return $this->requestClass->get(null, 'accounts', $params);
    }
    
    /**
     * Modify the IMAP server settings of an already indexed account
     * @link http://context.io/docs/2.0/accounts/sources
     *
     * @param string $account  accountId of the mailbox you want to query
     * @param string[] $params Query parameters for the API call: 'credentials', 'mailboxes'
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function modifySource($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        $params = $this->checkFilterParams($params, array(
            'provider_token',
            'provider_token_secret',
            'provider_refresh_token',
            'password',
            'provider_consumer_key',
            'label',
            'mailboxes',
            'expunge_on_deleted_flag',
            'sync_all_folders',
            'service_level',
            'sync_period',
        ), array('label'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        
        return $this->requestClass->post($account, 'sources/' . $params[ 'label' ], $params);
    }
    
    public function resetSourceStatus($account, $params, $force = false)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_string($params)) {
            $params = array('label' => $params);
        } else {
            $params = $this->checkFilterParams($params, array('label'), array('label'));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        if ($force) {
            return $this->requestClass->post($account, 'sources/' . $params[ 'label' ], array('force_status_check' => 1));
        }
        
        return $this->requestClass->post($account, 'sources/' . $params[ 'label' ], array('status' => 1));
    }
    
    public function listSources($account, $params = null)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_array($params)) {
            $params = $this->checkFilterParams($params, array('status_ok', 'status'));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        
        return $this->requestClass->get($account, 'sources', $params);
    }
    
    public function getSource($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_string($params)) {
            $params = array('label' => $params);
        } else {
            $params = $this->checkFilterParams($params, array('label'), array('label'));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        
        return $this->requestClass->get($account, 'sources/' . $params[ 'label' ]);
    }
    
    /**
     * @link http://context.io/docs/2.0/accounts/sources
     *
     * @param string $account  accountId of the mailbox you want to query
     * @param string[] $params Query parameters for the API call:
     *                         'email',  'server', 'username', 'password', 'oauthconsumername', 'oauthtoken',
     *                         'oauthtokensecret', 'usessl', 'port'
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function addSource($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        $params = $this->checkFilterParams($params, array(
            'type',
            'email',
            'server',
            'username',
            'provider_consumer_key',
            'provider_token',
            'provider_token_secret',
            'provider_refresh_token',
            'service_level',
            'sync_period',
            'sync_all_folders',
            'origin_ip',
            'sync_folders',
            'password',
            'use_ssl',
            'port',
            'callback_url',
            'expunge_on_deleted_flag',
        ), array('server', 'username'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        if (!array_key_exists('type', $params)) {
            $params[ 'type' ] = 'imap';
        }
        
        return $this->requestClass->post($account, 'sources/', $params);
    }
    
    /**
     * Remove the connection to an IMAP account
     * @link http://context.io/docs/2.0/accounts/sources
     *
     * @param string $account  accountId of the mailbox you want to query
     * @param string[] $params Query parameters for the API call
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function deleteSource($account, $params = array())
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_string($params)) {
            $params = array('label' => $params);
        } else {
            $params = $this->checkFilterParams($params, array('label'), array('label'));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        
        return $this->requestClass->delete($account, 'sources/' . $params[ 'label' ]);
    }
    
    public function syncSource($account, $params = array())
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        $params = $this->checkFilterParams($params, array('label'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        if ($params == array()) {
            return $this->requestClass->post($account, 'sync');
        }
        
        return $this->requestClass->post($account, 'sources/' . $params[ 'label' ] . '/sync');
    }
    
    public function getSync($account, $params = array())
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        $params = $this->checkFilterParams($params, array('label'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        if ($params == array()) {
            return $this->requestClass->get($account, 'sync');
        }
        
        return $this->requestClass->get($account, 'sources/' . $params[ 'label' ] . '/sync');
    }
    
    public function addFolderToSource($account, $params = array())
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        $params = $this->checkFilterParams($params, array('label', 'folder', 'delim'), array('label', 'folder'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        $path = 'sources/' . rawurlencode($params[ 'label' ]) . '/folders/' . rawurlencode($params[ 'folder' ]);
        if (array_key_exists('delim', $params)) {
            $path .= '?' . urlencode($params[ 'delim' ]);
        }
        
        return $this->requestClass->put($account, $path);
    }
    
    public function deleteFolderFromSource($account, $params = array())
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        $params = $this->checkFilterParams($params, array('label', 'folder', 'delim'), array('label', 'folder'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        if (array_key_exists('delim', $params)) {
            return $this->requestClass->delete($account,
                'sources/' . $params[ 'label' ] . '/folders/' . rawurlencode($params[ 'folder' ]),
                array('delim' => $params[ 'delim' ]));
        }
        
        return $this->requestClass->delete($account,
            'sources/' . $params[ 'label' ] . '/folders/' . rawurlencode($params[ 'folder' ]));
    }
    
    public function sendMessage($account, $params = array())
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        $params = $this->checkFilterParams($params, array('label', 'rcpt', 'message', 'message_id', 'gmail_thread_id'),
            array('label'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        if (!array_key_exists('message_id', $params) && !array_key_exists('message',
                $params) && !array_key_exists('gmail_thread_id', $params)
        ) {
            throw new \InvalidArgumentException('gmail_thread_id, message_id or message is a required hash key');
        }
        
        return $this->requestClass->post($account, 'exits/' . $params[ 'label' ], $params);
    }
    
    public function listSourceFolders($account, $params = array())
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_string($params)) {
            $params = array('label' => $params);
        } else {
            $params = $this->checkFilterParams($params, array('label', 'include_extended_counts', 'no_cache'),
                array('label'));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        $source = $params[ 'label' ];
        unset($params[ 'label' ]);
        
        return $this->requestClass->get($account, 'sources/' . $source . '/folders', $params);
    }
    
    public function getSourceFolder($account, $params = array())
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        $params = $this->checkFilterParams($params, array('label', 'folder'), array('label', 'folder'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        
        return $this->requestClass->get($account,
            'sources/' . rawurlencode($params[ 'label' ]) . '/folders/' . rawurlencode($params[ 'folder' ]));
    }
    
    public function listWebhooks($account)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        
        return $this->requestClass->get($account, 'webhooks');
    }
    
    public function getWebhook($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_string($params)) {
            $params = array('webhook_id' => $params);
        } else {
            $params = $this->checkFilterParams($params, array('webhook_id'), array('webhook_id'));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        
        return $this->requestClass->get($account, 'webhooks/' . $params[ 'webhook_id' ]);
    }
    
    public function addWebhook($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        $params = $this->checkFilterParams($params, array(
            'filter_to',
            'filter_from',
            'filter_cc',
            'filter_subject',
            'filter_thread',
            'filter_new_important',
            'filter_file_name',
            'filter_file_revisions',
            'sync_period',
            'callback_url',
            'failure_notif_url',
            'filter_folder_added',
            'filter_folder_removed',
            'filter_to_domain',
            'filter_from_domain',
            'filter_parsed_receipts',
            'include_body',
            'body_type',
        ), array('callback_url', 'failure_notif_url'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        
        return $this->requestClass->post($account, 'webhooks/', $params);
    }
    
    public function deleteWebhook($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        if (is_string($params)) {
            $params = array('webhook_id' => $params);
        } else {
            $params = $this->checkFilterParams($params, array('webhook_id'), array('webhook_id'));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        
        return $this->requestClass->delete($account, 'webhooks/' . $params[ 'webhook_id' ]);
    }
    
    public function modifyWebhook($account, $params)
    {
        if (is_null($account) || !is_string($account) || (!strpos($account, '@') === false)) {
            throw new \InvalidArgumentException('account must be string representing accountId');
        }
        $params = $this->checkFilterParams($params, array('webhook_id', 'active'), array('webhook_id', 'active'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        
        return $this->requestClass->post($account, 'webhooks/' . $params[ 'webhook_id' ], $params);
    }
    
    /**
     * Gets application-level webhook list.
     *
     * @link https://context.io/docs/2.0/webhooks#get
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function listApplicationWebhook()
    {
        return $this->requestClass->get(null, 'webhooks/');
    }
    
    /**
     * Gets application-level webhook.
     *
     * @link https://context.io/docs/2.0/webhooks#id-get
     *
     * @param string|array $params
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function getApplicationWebhook($params)
    {
        if (is_string($params)) {
            $params = array('webhook_id' => $params);
        } else {
            $params = $this->checkFilterParams($params, array('webhook_id'), array('webhook_id'));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        
        return $this->requestClass->get(null, 'webhooks/' . $params[ 'webhook_id' ]);
    }
    
    /**
     * Creates application-level webhook. If you want to create user-level webhook use $this->addWebook() instead.
     *
     * @link https://context.io/docs/2.0/webhooks#post
     *
     * @param array [string] $params Query params.
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function addApplicationWebhook(array $params)
    {
        $params = $this->checkFilterParams($params, array(
            'filter_to',
            'filter_from',
            'filter_cc',
            'filter_subject',
            'filter_thread',
            'filter_new_important',
            'filter_file_name',
            'filter_file_revisions',
            'sync_period',
            'callback_url',
            'failure_notif_url',
            'filter_folder_added',
            'filter_folder_removed',
            'filter_to_domain',
            'filter_from_domain',
            'filter_parsed_receipts',
            'include_body',
            'body_type',
            'include_header',
            'receive_all_changes',
            'receive_historical',
        ), array('callback_url'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        
        return $this->requestClass->post(null, 'webhooks/', $params);
    }
    
    /**
     * Deletes application-level webhook.
     *
     * @link https://context.io/docs/2.0/webhooks#id-delete
     *
     * @param string|array $params
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function deleteApplicationWebhook($params)
    {
        if (is_string($params)) {
            $params = array('webhook_id' => $params);
        } else {
            $params = $this->checkFilterParams($params, array('webhook_id'), array('webhook_id'));
            if ($params === false) {
                throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
            }
        }
        
        return $this->requestClass->delete(null, 'webhooks/' . $params[ 'webhook_id' ]);
    }
    
    /**
     * Modifies application-level webhook.
     *
     * @Link https://context.io/docs/2.0/webhooks#id-post
     *
     * @param string|array $params
     *
     * @return \ContextIO\ContextIOResponse
     */
    public function modifyApplicationWebhook($params)
    {
        $params = $this->checkFilterParams($params, array(
            'webhook_id',
            'filter_to',
            'filter_from',
            'filter_cc',
            'filter_subject',
            'filter_thread',
            'filter_new_important',
            'filter_file_name',
            'filter_file_revisions',
            'sync_period',
            'callback_url',
            'failure_notif_url',
            'filter_folder_added',
            'filter_folder_removed',
            'filter_to_domain',
            'filter_from_domain',
            'filter_parsed_receipts',
            'include_body',
            'body_type',
            'include_header',
            'receive_all_changes',
            'receive_historical',
        ), array('webhook_id'));
        if ($params === false) {
            throw new \InvalidArgumentException("params array contains invalid parameters or misses required parameters");
        }
        
        return $this->requestClass->post(null, 'webhooks/' . $params[ 'webhook_id' ], $params);
    }
    
    /**
     * Checks whether all set params are valid and all required params are set.
     *
     * @param array $givenParams
     * @param array $validParams
     * @param array $requiredParams
     *
     * @return array|bool
     */
    protected function checkFilterParams($givenParams, $validParams, $requiredParams = array())
    {
        $filteredParams = array();
        foreach ($givenParams as $name => $value) {
            if (in_array(strtolower($name), $validParams)) {
                $filteredParams[ strtolower($name) ] = $value;
            } else {
                return false;
            }
        }
        foreach ($requiredParams as $name) {
            if (!array_key_exists(strtolower($name), $filteredParams)) {
                return false;
            }
        }
        
        return $filteredParams;
    }
    
}
