<?php

namespace ContextIO\oAuth\SignatureMethods;

use ContextIO\oAuth;

/**
 * A class for implementing a Signature Method
 * See section 9 ("Signing Requests") in the spec
 */
abstract class AbstractSignatureMethod
{
    
    /**
     * Needs to return the name of the Signature Method (ie HMAC-SHA1)
     * @return string
     */
    abstract public function getName();
    
    /**
     * Build up the signature
     * NOTE: The output of this function MUST NOT be urlencoded.
     * the encoding is handled in OAuthRequest when the final
     * request is serialized
     *
     * @param oAuth\OAuthRequest $request
     * @param oAuth\OAuthConsumer $consumer
     * @param oAuth\OAuthToken $token
     *
     * @return string
     */
    abstract public function buildSignature($request, $consumer, $token);
    
    /**
     * Verifies that a given signature is correct
     *
     * @param oAuth\OAuthRequest $request
     * @param oAuth\OAuthConsumer $consumer
     * @param oAuth\OAuthToken $token
     * @param string $signature
     *
     * @return bool
     */
    public function checkSignature($request, $consumer, $token, $signature)
    {
        $built = $this->buildSignature($request, $consumer, $token);
        
        return $built == $signature;
    }
    
}
