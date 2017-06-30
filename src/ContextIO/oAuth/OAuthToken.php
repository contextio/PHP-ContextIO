<?php

namespace ContextIO\oAuth;

class OAuthToken
{
    
    /**
     * @var string Access tokens and request tokens
     */
    public $key;
    public $secret;
    
    /**
     * OAuthToken constructor.
     *
     * @param string $key    The Token
     * @param string $secret The token secret.
     */
    public function __construct($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
    }
    
    /**
     * generates the basic string serialization of a token that a server
     * would respond to request_token and access_token calls with
     */
    public function toString()
    {
        return "oauth_token=" . OAuthUtil::urlencodeRfc3986($this->key) . "&oauth_token_secret=" . OAuthUtil::urlencodeRfc3986($this->secret);
    }
    
    public function __toString()
    {
        return $this->toString();
    }
    
}
