<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Key Auth
|--------------------------------------------------------------------------
|
| Value ini digunakan pada encryption untuk keperluan access_token
| Yang dimana access_token ini untuk autentikasi ke API Borrower atau Lender
*/
$config['key_auth'] = 'auth-pt-satustop-finansial-solusi-@-2023';

/*
|--------------------------------------------------------------------------
| Key Token
|--------------------------------------------------------------------------
|
| Value ini digunakan pada JWT untuk men-generate token
*/
$config['key_token'] = 'token-pt-satustop-finansial-solusi-@-2023';