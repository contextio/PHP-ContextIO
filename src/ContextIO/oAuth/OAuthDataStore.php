<?php

namespace ContextIO\oAuth;

class OAuthDataStore
{
    
    public function lookupConsumer($consumer_key)
    {
        // implement me
    }
    
    public function lookupToken($consumer, $token_type, $token)
    {
        // implement me
    }
    
    public function lookupNonce($consumer, $token, $nonce, $timestamp)
    {
        // implement me
    }
    
    public function newRequestToken($consumer, $callback = null)
    {
        // return a new token attached to this consumer
    }
    
    public function newAccessToken($token, $consumer, $verifier = null)
    {
        // return a new access token attached to this consumer
        // for the user associated with this token if the request token
        // is authorized
        // should also invalidate the request token
    }
    
}
