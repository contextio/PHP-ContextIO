<?php

namespace ContextIO\oAuth\SignatureMethods;

use ContextIO\oAuth\OAuthUtil;

/**
 * The HMAC-SHA1 signature method uses the HMAC-SHA1 signature algorithm as defined in [RFC2104]
 * where the Signature Base String is the text and the key is the concatenated values (each first
 * encoded per Parameter Encoding) of the Consumer Secret and Token Secret, separated by an '&'
 * character (ASCII code 38) even if empty.
 *   - Chapter 9.2 ("HMAC-SHA1")
 */
class HMAC_SHA1 extends AbstractSignatureMethod
{
    
    public function getName()
    {
        return 'HMAC-SHA1';
    }
    
    public function buildSignature($request, $consumer, $token)
    {
        $base_string = $request->getSignatureBaseString();
        $request->base_string = $base_string;
        
        $key_parts = array(
            $consumer->secret,
            ($token) ? $token->secret : "",
        );
        
        $key_parts = OAuthUtil::urlencodeRfc3986($key_parts);
        $key = implode('&', $key_parts);
        
        return base64_encode(hash_hmac('sha1', $base_string, $key, true));
    }
    
}
