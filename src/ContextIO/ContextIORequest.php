<?php
/**
 * Created by PhpStorm.
 * User: Krystian
 * Date: 2017-06-29
 * Time: 10:54
 */

namespace ContextIO;

class ContextIORequest implements RequestInterface
{
    
    protected $endPoint = 'api.context.io';
    protected $apiVersion = '2.0';
    protected $oauthKey;
    protected $oauthSecret;
    protected $accessToken;
    protected $accessTokenSecret;
    protected $authHeaders = true;
    protected $ssl = true;
    protected $saveHeaders = false;
    protected $requestTimeout;
    protected $requestHeaders;
    protected $responseHeaders;
    protected $lastResponse = null;
    
    public function __construct($key, $secret, $access_token = null, $access_token_secret = null)
    {
        $this->oauthKey = $key;
        $this->oauthSecret = $secret;
        $this->accessToken = $access_token;
        $this->accessTokenSecret = $access_token_secret;
    }
    
    /**
     * Sends a GET HTTP request.
     *
     * @param string $account
     * @param string $action
     * @param null $parameters
     * @param null $acceptableContentTypes
     *
     * @return array|bool|\ContextIO\ContextIOResponse
     */
    public function get($account, $action = '', $parameters = null, $acceptableContentTypes = null)
    {
        if (is_array($account)) {
            $tmp_results = array();
            foreach ($account as $accnt) {
                $result = $this->sendRequest('GET', $accnt, $action, $parameters, null, $acceptableContentTypes);
                if ($result === false) {
                    return false;
                }
                $tmp_results[ $accnt ] = $result;
            }
            
            return $tmp_results;
        }
        
        return $this->sendRequest('GET', $account, $action, $parameters, null, $acceptableContentTypes);
    }
    
    /**
     * Sends a PUT HTTP request.
     *
     * @param string $account
     * @param string $action
     * @param null $parameters
     * @param array $httpHeadersToSet
     *
     * @return bool|ContextIOResponse
     */
    public function put($account, $action, $parameters = null, $httpHeadersToSet = array())
    {
        return $this->sendRequest('PUT', $account, $action, $parameters, null, null, $httpHeadersToSet);
    }
    
    /**
     * Sends a POST HTTP request.
     *
     * @param string $account
     * @param string $action
     * @param null $parameters
     * @param null $file
     * @param array $httpHeadersToSet
     *
     * @return bool|ContextIOResponse
     */
    public function post($account, $action = '', $parameters = null, $file = null, $httpHeadersToSet = array())
    {
        return $this->sendRequest('POST', $account, $action, $parameters, $file, null, $httpHeadersToSet);
    }
    
    /**
     * Sends a DELETE HTTP request.
     *
     * @param string $account
     * @param string $action
     * @param null $parameters
     *
     * @return bool|ContextIOResponse
     */
    public function delete($account, $action = '', $parameters = null)
    {
        return $this->sendRequest('DELETE', $account, $action, $parameters);
    }
    
    /**
     * Makes an HTTP request using cURL.
     *
     * @param string $httpMethod
     * @param string $account
     * @param string $action
     * @param null|array $parameters
     * @param null|array $file
     * @param null|array $acceptableContentTypes
     * @param array $httpHeadersToSet
     *
     * @return bool|\ContextIO\ContextIOResponse
     *
     * @throws cURLException
     */
    public function sendRequest(
        $httpMethod,
        $account,
        $action,
        $parameters = null,
        $file = null,
        $acceptableContentTypes = null,
        $httpHeadersToSet = array()
    ) {
        $consumer = new oAuth\OAuthConsumer($this->oauthKey, $this->oauthSecret);
        $accessToken = null;
        if (!is_null($account)) {
            $action = 'accounts/' . $account . '/' . $action;
            if (substr($action, -1) == '/') {
                $action = substr($action, 0, -1);
            }
            if (!is_null($this->accessToken) && !is_null($this->accessTokenSecret)) {
                $accessToken = new oAuth\OAuthToken($this->accessToken, $this->accessTokenSecret);
            }
        }
        $baseUrl = $this->buildUrl($action);
        $isMultiPartPost = (!is_null($file) && array_key_exists('field', $file) && array_key_exists('filename', $file));
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
            if (!in_array('Content-Type: application/x-www-form-urlencoded', $httpHeadersToSet)) {
                $signatureParams = array();
            } else {
                $newParams = '';
                foreach ($parameters as $key => $value) {
                    if (!is_array($value)) {
                        if ($newParams != '') {
                            $newParams .= '&';
                        }
                        $newParams .= "$key=" . urlencode($value);
                    } else {
                        unset($signatureParams[ $key ]);
                        $signatureParams[ $key . '[]' ] = $value;
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
        
        $req = oAuth\OAuthRequest::fromConsumerAndToken(
            $consumer, $accessToken, $httpMethod, $baseUrl, $signatureParams
        );
        $sig_method = new oAuth\SignatureMethods\HMAC_SHA1();
        $req->signRequest($sig_method, $consumer, $accessToken);
        
        //get data using signed url
        if ($this->authHeaders) {
            if ($httpMethod != 'POST') {
                $curl = curl_init((is_null($parameters) || is_string($parameters) || (count($parameters) == 0)) ? $baseUrl : $baseUrl . '?' . oAuth\OAuthUtil::buildHttpQuery($parameters));
            } else {
                $curl = curl_init($baseUrl);
            }
            $httpHeadersToSet[] = $req->toHeader();
        } else {
            $curl = curl_init($req->toUrl());
        }
        
        if ($this->ssl) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        }
        
        curl_setopt($curl, CURLOPT_USERAGENT, 'ContextIOLibrary/2.0 (PHP)');
        
        if ($httpMethod != 'GET') {
            if ($httpMethod == 'POST') {
                curl_setopt($curl, CURLOPT_POST, true);
                if (!is_null($parameters)) {
                    if (is_null($file)) {
                        if (is_string($parameters)) {
                            $httpHeadersToSet[] = 'Content-Length: ' . strlen($parameters);
                        }
                    } else {
                        $parameters[ $file[ 'field' ] ] = $file[ 'filename' ];
                    }
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
                } elseif (!is_null($file)) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, array($file[ 'field' ] => $file[ 'filename' ]));
                } else {
                    $httpHeadersToSet[] = 'Content-Length: 0';
                }
            } else {
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
        
        if ($this->requestTimeout !== null) {
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->requestTimeout);
        }
        
        if ($this->saveHeaders) {
            $this->responseHeaders = array();
            $this->requestHeaders = array();
            curl_setopt($curl, CURLOPT_HEADERFUNCTION, array($this, 'addResponseHeaders'));
            curl_setopt($curl, CURLINFO_HEADER_OUT, 1);
        }
        $result = curl_exec($curl);
        
        $errno = curl_errno($curl);
        if (!empty($errno)) {
            throw new cURLException($curl);
        }
        
        if ($this->saveHeaders) {
            $httpHeadersIn = $this->responseHeaders;
            $httpHeadersOut = preg_split('/(\\n|\\r){1,2}/', curl_getinfo($curl, CURLINFO_HEADER_OUT));
        } else {
            $httpHeadersIn = null;
            $httpHeadersOut = null;
        }
        
        if (is_null($acceptableContentTypes)) {
            $response = new ContextIOResponse(
                curl_getinfo($curl, CURLINFO_HTTP_CODE),
                $httpHeadersOut,
                $httpHeadersIn,
                curl_getinfo($curl, CURLINFO_CONTENT_TYPE),
                $result
            );
        } else {
            $response = new ContextIOResponse(
                curl_getinfo($curl, CURLINFO_HTTP_CODE),
                $httpHeadersOut,
                $httpHeadersIn,
                curl_getinfo($curl, CURLINFO_CONTENT_TYPE),
                $result,
                $acceptableContentTypes
            );
        }
        curl_close($curl);
        if ($response->hasError()) {
            $this->lastResponse = $response;
            
            return false;
        }
        
        return $response;
    }
    
    /**
     * Returns the ContextIOResponse object for the last API call.
     * @return \ContextIO\ContextIOResponse
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }
    
    /**
     * After how long the unused connection should be closed.
     *
     * @param $requestTimeout
     */
    public function setRequestTimeout($requestTimeout)
    {
        $this->requestTimeout = $requestTimeout;
    }
    
    /**
     * Add response headers to a property.
     *
     * @param $curl
     * @param string $headers
     *
     * @return int
     */
    public function addResponseHeaders($curl, $headers)
    {
        $this->responseHeaders[] = trim($headers, "\n\r");
        
        return strlen($headers);
    }
    
    /**
     * Whether reponse headers should be saved in class property.
     *
     * @param bool $saveHeaders
     */
    public function setSaveHeaders($saveHeaders = true)
    {
        $this->saveHeaders = $saveHeaders;
    }
    
    /**
     * Specify the API endpoint.
     *
     * @param string $endPoint
     *
     * @return boolean success
     */
    public function setEndPoint($endPoint)
    {
        $this->endPoint = $endPoint;
        
        return true;
    }
    
    /**
     * Specify whether or not API calls should be made over a secure connection.
     * HTTPS is used on all calls by default.
     *
     * @param bool $sslOn Set to false to make calls over HTTP, true to use HTTPS
     */
    public function setSSL($sslOn = true)
    {
        $this->ssl = (is_bool($sslOn)) ? $sslOn : true;
    }
    
    /**
     * Set the API version. By default, the latest official version will be used
     * for all calls.
     *
     * @param string $apiVersion Context.IO API version to use
     *
     * @return boolean success
     */
    public function setApiVersion($apiVersion)
    {
        if ($apiVersion != '2.0') {
            return false;
        }
        $this->apiVersion = $apiVersion;
        
        return true;
    }
    
    /**
     * Specify whether OAuth parameters should be included as URL query parameters
     * or sent as HTTP Authorization headers. The default is URL query parameters.
     *
     * @param bool $authHeadersOn Set to true to use HTTP Authorization headers, false to use URL query params
     */
    public function useAuthorizationHeaders($authHeadersOn = true)
    {
        $this->authHeaders = (is_bool($authHeadersOn)) ? $authHeadersOn : true;
    }
    
    public function buildBaseUrl()
    {
        $url = 'http';
        if ($this->ssl) {
            $url = 'https';
        }
        
        return "$url://" . $this->endPoint . "/" . $this->apiVersion . '/';
    }
    
    public function buildUrl($action)
    {
        return $this->buildBaseUrl() . $action;
    }
    
}
