<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

if (!function_exists('encrypt')) {
    function encrypt($text, $secretKey)
    {
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encrypted = openssl_encrypt($text, 'aes-256-cbc', $secretKey, 0, $iv);
        return bin2hex($iv . $encrypted);
    }
}

if (!function_exists('decrypt')) {
    function decrypt($encrypted, $secretKey)
    {
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = hex2bin(substr($encrypted, 0, $ivLength * 2));
        $encryptedText = hex2bin(substr($encrypted, $ivLength * 2));
        return openssl_decrypt($encryptedText, 'aes-256-cbc', $secretKey, 0, $iv);
    }
}