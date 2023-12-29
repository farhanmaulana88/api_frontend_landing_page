<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once 'vendor/autoload.php';

use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Lender extends RestController
{
  function __construct()
  {
    parent::__construct();
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "OPTIONS") {
      die();
    }

    // Load auth sanders config
    $this->config->load('auth_sanders');
  }

  public function login_post()
  {
    $data = $this->post();

    $this->form_validation->set_data($data);
    $this->form_validation->set_rules('access_token', 'Akses token', 'required');
    $this->form_validation->set_message('required', '%s tidak boleh kosong.');

    if (!$this->form_validation->run()) {
      $errors = $this->form_validation->get_array_errors();
      $this->validation_lib->respondError($errors);
      die;
    }

    // Get access token
    $access_token = $data['access_token'];

    // Retrieve information of access token
    $secret_key = $this->config->item('key_auth');
    $decryptedText = decrypt($access_token, $secret_key);

    // Check whether access token is valid ?
    if (empty($decryptedText)) {
      $this->validation_lib->respondError('Akses token tidak valid');
      return;
    }

    // Get payload of access token
    $payload = json_decode($decryptedText);

    // Get id user
    $user_id = $payload->id;

    // JWT payload
    $payload = [
      'id' => $user_id,
      'email' => 'test@mail.com',
      'name' => 'Test',
      'generate_token_at' => date('Y-m-d H:i:s'),
      'permissions' => []
    ];

    // Generate jwt token
    $key = $this->config->item('key_token') . '-LENDER';
    $token = JWT::encode($payload, $key, 'HS256');

    $this->validation_lib->respondSuccess([
      'token' => $token,
      'message' => 'Berhasil login'
    ]);
  }

  public function user_get()
  {
    $authHeader = $this->input->request_headers()['Authorization'];
    $arr = explode(" ", $authHeader);
    $token = $arr[1];

    if ($token) {
      try {
        $secret_key = $this->config->item('key_token') . '-LENDER';
        $data = JWT::decode($token, new Key($secret_key, 'HS256'));

        $this->validation_lib->respondSuccess($data);
      } catch (\Exception $e) {
        $this->validation_lib->respondError('Token tidak valid');
      }
    }
  }
}
