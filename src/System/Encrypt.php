<?php


namespace enflares\System;


class Encrypt
{
    public static function hmacSign($message, $key)
    {
        return hash_hmac('sha256', $message, $key) . $message;
    }

    public static function hmacVerify($bundle, $key)
    {
        $msgMAC = mb_substr($bundle, 0, 64, '8bit');
        $message = mb_substr($bundle, 64, null, '8bit');

        return hash_equals(
            hash_hmac('sha256', $message, $key),
            $msgMAC
        );
    }

    public static function createIV($size, $option=NULL)
    {
        if( is_null($option) && defined('MCRYPT_DEV_URANDOM') )
            $option = constant('MCRYPT_DEV_URANDOM');

        if( function_exists('mcrypt_create_iv') )
            return call_user_func('mcrypt_create_iv', $size, $option);

        if( function_exists('openssl_random_pseudo_bytes') )
            return openssl_random_pseudo_bytes($size);

        $s = md5(microtime(FALSE) . mt_rand(10000000, 99999999));
        return substr($s.$s, mt_rand(0, 32), intval($size) % 32);
    }

    public static function encode($data, $eKey=NULL, $aKey=NULL)
    {
        $iv = static::createIV(16);
        
        if( function_exists('mcrypt_encrypt') )
        {
            $cipherText = call_user_func('mcrypt_encrypt',
                constant('MCRYPT_RIJNDAEL_128'),
                $eKey,
                json_encode($data),
                'ctr',
                $iv
            );
        }else{
            $cipherText = json_encode([                
                $eKey,
                json_encode($data),
                'ctr',
                $iv
            ]);
        }        

        // Note: We cover the IV in our HMAC
        $hmac = hash_hmac('sha256', $iv.$cipherText, $aKey, TRUE);
        return base64_encode($hmac.$iv.$cipherText);
    }
    
    public static function decode($str, $eKey, $aKey)
    {
        $decoded = base64_decode($str);
        $hmac = mb_substr($decoded, 0, 32, '8bit');
        $iv = mb_substr($decoded, 32, 16, '8bit');
        $cipherText = mb_substr($decoded, 48, null, '8bit');
        
        $calculated = hash_hmac('sha256', $iv.$cipherText, $aKey, true);

        if (hash_equals($hmac, $calculated)) {
            
            if( function_exists('mcrypt_encrypt') )
            {
                $decrypted = rtrim(
                    call_user_func('mcrypt_decrypt', 
                        constant('MCRYPT_RIJNDAEL_128'),
                        $eKey,
                        $cipherText,
                        'ctr',
                        $iv
                    ),
                    "\0"
                );
            }else{
                $decrypted = json_decode($cipherText, TRUE);
                if( ($eKey===$decoded[0]) 
                    && ('ctr'===$decoded[2])
                    && ($iv===$decoded[3]) )
                    $decrypted = $decrypted[1];
                else
                    return;
            }
            
            return json_decode($decrypted, true);
        }
    }
}