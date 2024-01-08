<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once 'vendor/autoload.php';

use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Borrower extends RestController
{
  function __construct()
  {
    parent::__construct();
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization");
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
    $id_unique = '21b6a798321a49f75b9c3827fa3cfdb7efe2c4e05c3d13aefe1c825b9774a158';
    
    $data = $this->post();

    $this->form_validation->set_data($data);
    $this->form_validation->set_rules('auth_token', 'Auth Token', 'required');
    $this->form_validation->set_message('required', '%s tidak boleh kosong.');

    if (!$this->form_validation->run()) {
      $errors = $this->form_validation->get_array_errors();
      $this->validation_lib->respondError($errors);
      die;
    }

    // Get auth token
    $auth_token = $data['auth_token'];

    // Retrieve information of access token
    $secret_key = $this->config->item('key_auth');
    $decryptedText = decrypt($auth_token, $secret_key);

    // Check whether access token is valid ?
    if (empty($decryptedText)) {
      $this->validation_lib->respondError('Akses token tidak valid');
      return;
    }

    // Get payload of access token
    $payload = json_decode($decryptedText);

    // Get id user
    $user_id = $payload->id;

    $payload = [];
    $permission = [];

    if($user_id === $id_unique) {

      // JWT payload
      $payload = [
        'id' => $user_id,
        'fullName' => 'John Doe',
        'username' => 'johndoe',
        'avatar' => 'avatar-1.png',
        'type' => 'Borrower',
        'borrowerId' => 'BOR00000001',
        'generate_token_at' => date('Y-m-d H:i:s'),
      ];

      // Generate Permission
      $permission = [
        [
          'action' => 'manage',
          'subject' => 'all'
        ]
      ];

    } else {

      $payload = [
        'id' => $user_id,
        'fullName' => '',
        'username' => 'BOR00839212',
        'avatar' => 'avatar-1.png',
        'type' => 'Borrower',
        'borrowerId' => 'BOR00839212',
        'generate_token_at' => date('Y-m-d H:i:s'),
      ];

      // Generate Permission
      $permission = [
        [
          'action' => 'read',
          'subject' => 'Dashboards'
        ],
        [
          'action' => ['read', 'update'],
          'subject' => 'Account'
        ],
        [
          'action' => ['read', 'update'],
          'subject' => 'Account-Information'
        ],
        [
          'action' => ['read', 'update'],
          'subject' => 'Account-Bank-Information'
        ],
        [
          'action' => ['read', 'update'],
          'subject' => 'Account-Password'
        ],
      ];
    }

    // Generate jwt token
    $key = $this->config->item('key_token') . '-LENDER';
    $accessToken = JWT::encode($payload, $key, 'HS256');

    $this->validation_lib->respondSuccess([
      'access_token' => $accessToken,
      'permission' => $permission,
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
