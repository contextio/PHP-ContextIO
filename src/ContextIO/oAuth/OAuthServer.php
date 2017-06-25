<?php

namespace ContextIO\oAuth;

use ContextIO\oAuth\SignatureMethods\AbstractSignatureMethod;

class OAuthServer
{
    
    protected $timestamp_threshold = 300; // in seconds, five minutes
    protected $version = '1.0';             // hi blaine
    protected $signature_methods = array();
    
    /** @var OAuthDataStore */
    protected $data_store;
    
    public function __construct($data_store)
    {
        $this->data_store = $data_store;
    }
    
    /**
     * @param AbstractSignatureMethod $signature_method
     */
    public function add_signature_method($signature_method)
    {
        $this->signature_methods[ $signature_method->get_name() ] = $signature_method;
    }
    
    /**
     * Process a request_token request
     *
     * @param OAuthRequest $request
     *
     * @return mixed Returns the request token on success.
     */
    public function fetch_request_token(&$request)
    {
        $this->get_version($request);
        
        $consumer = $this->get_consumer($request);
        
        // no token required for the initial token request
        $token = null;
        
        $this->check_signature($request, $consumer, $token);
        
        // Rev A change
        $callback = $request->get_parameter('oauth_callback');
        $new_token = $this->data_store->new_request_token($consumer, $callback);
        
        return $new_token;
    }
    
    /**
     */
    
    /**
     * Process an access_token request.
     *
     * @param OAuthRequest $request
     *
     * @return mixed The access token on success
     */
    public function fetch_access_token(&$request)
    {
        $this->get_version($request);
        
        $consumer = $this->get_consumer($request);
        
        // requires authorized request token
        $token = $this->get_token($request, $consumer, "request");
        
        $this->check_signature($request, $consumer, $token);
        
        // Rev A change
        $verifier = $request->get_parameter('oauth_verifier');
        $new_token = $this->data_store->new_access_token($token, $consumer, $verifier);
        
        return $new_token;
    }
    
    /**
     * verify an api call, checks all the parameters
     *
     * @param OAuthRequest $request
     *
     * @return array
     */
    public function verify_request(&$request)
    {
        $this->get_version($request);
        $consumer = $this->get_consumer($request);
        $token = $this->get_token($request, $consumer, "access");
        $this->check_signature($request, $consumer, $token);
        
        return array($consumer, $token);
    }
    
    /**
     * @param OAuthRequest $request
     *
     * @return string
     * @throws OAuthException
     *
     * @return mixed
     */
    protected function get_version(&$request)
    {
        $version = $request->get_parameter("oauth_version");
        if (!$version) {
            // Service Providers MUST assume the protocol version to be 1.0 if this parameter is not present.
            // Chapter 7.0 ("Accessing Protected Ressources")
            $version = '1.0';
        }
        if ($version !== $this->version) {
            throw new OAuthException("OAuth version '$version' not supported");
        }
        
        return $version;
    }
    
    /**
     * Figure out the signature with some defaults.
     *
     * @param OAuthRequest $request
     *
     * @return mixed
     * @throws OAuthException
     */
    protected function get_signature_method(&$request)
    {
        $signature_method =
            @$request->get_parameter("oauth_signature_method");
        
        if (!$signature_method) {
            // According to chapter 7 ("Accessing Protected Ressources") the signature-method
            // parameter is required, and we can't just fallback to PLAINTEXT
            throw new OAuthException('No signature method parameter. This parameter is required');
        }
        
        if (!in_array($signature_method,
            array_keys($this->signature_methods))
        ) {
            throw new OAuthException(
                "Signature method '$signature_method' not supported " .
                "try one of the following: " .
                implode(", ", array_keys($this->signature_methods))
            );
        }
        
        return $this->signature_methods[ $signature_method ];
    }
    
    /**
     * Try to find the consumer for the provided request's consumer key
     *
     * @param OAuthRequest $request
     *
     * @throws OAuthException
     *
     * @return mixed
     */
    protected function get_consumer(&$request)
    {
        $consumer_key = @$request->get_parameter("oauth_consumer_key");
        if (!$consumer_key) {
            throw new OAuthException("Invalid consumer key");
        }
        
        $consumer = $this->data_store->lookup_consumer($consumer_key);
        if (!$consumer) {
            throw new OAuthException("Invalid consumer");
        }
        
        return $consumer;
    }
    
    /**
     * Try to find the token for the provided request's token key.
     *
     * @param OAuthRequest $request
     * @param $consumer
     * @param string $token_type
     *
     * @throws OAuthException
     *
     * @return mixed
     */
    protected function get_token(&$request, $consumer, $token_type = "access")
    {
        $token_field = @$request->get_parameter('oauth_token');
        $token = $this->data_store->lookup_token(
            $consumer, $token_type, $token_field
        );
        if (!$token) {
            throw new OAuthException("Invalid $token_type token: $token_field");
        }
        
        return $token;
    }
    
    /**
     * all-in-one function to check the signature on a request
     * should guess the signature method appropriately
     *
     * @param OAuthRequest $request
     * @param $consumer
     * @param $token
     *
     * @throws OAuthException
     */
    protected function check_signature(&$request, $consumer, $token)
    {
        // this should probably be in a different method
        $timestamp = @$request->get_parameter('oauth_timestamp');
        $nonce = @$request->get_parameter('oauth_nonce');
        
        $this->check_timestamp($timestamp);
        $this->check_nonce($consumer, $token, $nonce, $timestamp);
        
        $signature_method = $this->get_signature_method($request);
        
        $signature = $request->get_parameter('oauth_signature');
        $valid_sig = $signature_method->check_signature(
            $request,
            $consumer,
            $token,
            $signature
        );
        
        if (!$valid_sig) {
            throw new OAuthException("Invalid signature");
        }
    }
    
    /**
     * check that the timestamp is new enough
     *
     * @param $timestamp
     *
     * @throws OAuthException
     */
    protected function check_timestamp($timestamp)
    {
        if (!$timestamp) {
            throw new OAuthException(
                'Missing timestamp parameter. The parameter is required'
            );
        }
        
        // verify that timestamp is recentish
        $now = time();
        if (abs($now - $timestamp) > $this->timestamp_threshold) {
            throw new OAuthException(
                "Expired timestamp, yours $timestamp, ours $now"
            );
        }
    }
    
    /**
     * check that the nonce is not repeated
     */
    protected function check_nonce($consumer, $token, $nonce, $timestamp)
    {
        if (!$nonce) {
            throw new OAuthException(
                'Missing nonce parameter. The parameter is required'
            );
        }
        
        // verify that the nonce is uniqueish
        $found = $this->data_store->lookup_nonce(
            $consumer,
            $token,
            $nonce,
            $timestamp
        );
        if ($found) {
            throw new OAuthException("Nonce already used: $nonce");
        }
    }
    
}
