<?php

use LDAP\Result;

defined('BASEPATH') or exit('No direct script access allowed');

class Registration_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        // $this->db1 = $this->load->database('v1', true);
    }

    public function register($data)
    {
        $ret = [];

        $text = 'Development';
        $digest = hash('sha256', $text);

        $ret = [
            'id' => $digest
        ];

        return $ret;
    }

    public function generateOTP()
    {
        // Panjang OTP
        $otpLength = 6;

        // Karakter yang mungkin dalam OTP
        $otpChars = '0123456789';

        // Menghitung panjang karakter yang mungkin
        $numChars = strlen($otpChars);

        // Inisialisasi OTP
        $otp = '';

        // Menghasilkan OTP dengan panjang yang diinginkan
        for ($i = 0; $i < $otpLength; $i++) {
            // Memilih karakter acak dari string $otpChars
            $otp .= $otpChars[rand(0, $numChars - 1)];
        }

        // Mengembalikan OTP yang dihasilkan
        return ['otp' => $otp];
    }
}