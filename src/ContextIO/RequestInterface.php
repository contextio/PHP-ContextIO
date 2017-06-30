<?php

namespace ContextIO;

interface RequestInterface
{
    
    /**
     * Instantiate a new Request object. Your OAuth consumer key and secret can be
     * found under the "settings" tab of the developer console (https://console.context.io/#settings)
     *
     * @param string $key                    Your Context.IO OAuth consumer key
     * @param string $secret                 Your Context.IO OAuth consumer secret
     * @param null|string $access_token      Acces token
     * @param string|null $access_token_secret
     */
    public function __construct($key, $secret, $access_token = null, $access_token_secret = null);
    
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
    public function get($account, $action = '', $parameters = null, $acceptableContentTypes = null);
    
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
    public function put($account, $action, $parameters = null, $httpHeadersToSet = array());
    
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
    public function post($account, $action = '', $parameters = null, $file = null, $httpHeadersToSet = array());
    
    /**
     * Sends a DELETE HTTP request.
     *
     * @param string $account
     * @param string $action
     * @param null $parameters
     *
     * @return bool|ContextIOResponse
     */
    public function delete($account, $action = '', $parameters = null);
    
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
    );
    
    /**
     * Returns the ContextIOResponse object for the last API call.
     * @return \ContextIO\ContextIOResponse
     */
    public function getLastResponse();
    
    /**
     * After how long the unused connection should be closed.
     *
     * @param $requestTimeout
     */
    public function setRequestTimeout($requestTimeout);
    
    /**
     * Add response headers to a property.
     *
     * @param $curl
     * @param string $headers
     *
     * @return int
     */
    public function addResponseHeaders($curl, $headers);
    
    /**
     * Whether reponse headers should be saved in class property.
     *
     * @param bool $saveHeaders
     */
    public function setSaveHeaders($saveHeaders = true);
    
    /**
     * Specify the API endpoint.
     *
     * @param string $endPoint
     *
     * @return boolean success
     */
    public function setEndPoint($endPoint);
    
    /**
     * Specify whether or not API calls should be made over a secure connection.
     * HTTPS is used on all calls by default.
     *
     * @param bool $sslOn Set to false to make calls over HTTP, true to use HTTPS
     */
    public function setSSL($sslOn = true);
    
    /**
     * Set the API version. By default, the latest official version will be used
     * for all calls.
     *
     * @param string $apiVersion Context.IO API version to use
     *
     * @return boolean success
     */
    public function setApiVersion($apiVersion);
    
    /**
     * Specify whether OAuth parameters should be included as URL query parameters
     * or sent as HTTP Authorization headers. The default is URL query parameters.
     *
     * @param bool $authHeadersOn Set to true to use HTTP Authorization headers, false to use URL query params
     */
    public function useAuthorizationHeaders($authHeadersOn = true);
    
    public function buildBaseUrl();
    
    public function buildUrl($action);
    
}
