<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once 'vendor/autoload.php';

use chriskacerguis\RestServer\RestController;
use FontLib\Table\Type\name;

class Auth extends RestController
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

    $this->load->model(Auth_model::class, 'model');
  }

  public function login_post()
  {
    $data = $this->post();
    $this->form_validation->set_data($data);

    $this->form_validation->set_rules('email', 'Email', 'valid_email|trim|required');
    $this->form_validation->set_rules('password', 'Password', 'trim|required');

    $this->form_validation->set_message('required', '%s tidak boleh kosong.');

    if (!$this->form_validation->run()) {
      $errors = $this->form_validation->get_array_errors();
      $this->validation_lib->respondError($errors);
      die;
    }

    $valid_email = 'test@mail.com';
    $valid_password = 'test';

    if($data['email'] !== $valid_email || $data['password'] !== $valid_password) {
      $this->validation_lib->respondError(['message' => 'Invalid email or password']);
      die;
    }

    $this->validation_lib->respondSuccess(['id' => '21b6a798321a49f75b9c3827fa3cfdb7efe2c4e05c3d13aefe1c825b9774a158', 'message' => 'Kode OTP berhasil dikirim ke email anda']);
  }

  public function forgot_password_post() {
    $data = $this->post();
    $this->form_validation->set_data($data);
    $this->form_validation->set_rules('email', 'Email', 'valid_email|trim|required');

    $this->form_validation->set_message('required', '%s tidak boleh kosong.');

    if (!$this->form_validation->run()) {
      $errors = $this->form_validation->get_array_errors();
      $this->validation_lib->respondError($errors);
      die;
    }

    $valid_email = 'test@mail.com';

    if($data['email'] !== $valid_email) {
      $this->validation_lib->respondError(['message' => 'Invalid email or password']);
      die;
    }

    $this->validation_lib->respondSuccess('Berhasil mengirimkan URL Reset Password ke Email Anda.');
  }

  public function otp_get($id = "")
    {
        if (empty($id)) {
            $this->validation_lib->respondError('ID tidak boleh kosong');
            die;
        }
        // $generate_otp = $this->model->generateOTP();
        $this->validation_lib->respondSuccess('Kode OTP berhasil dikirim ke email anda');
    }

    public function otp_post()
    {
        $post = $this->post();
        $id_unique = '21b6a798321a49f75b9c3827fa3cfdb7efe2c4e05c3d13aefe1c825b9774a158';
        $otp_static = '111111';

        if (empty($post['id']) || empty($post['otp'])) {
            $this->validation_lib->respondError('ID Atau OTP tidak boleh kosong!');
        }

        if ($post['id'] !== $id_unique) {
            $this->validation_lib->respondError('Error');
            die;
        }

        if ($post['otp'] !== $otp_static) {
            $this->validation_lib->respondError('Error');
            die;
        }

        $result = $this->model->mock_login();
        $this->validation_lib->respondSuccess($result);
        
    }

  public function google_post()
  {
    $result = $this->model->get_google_oauth_url();

    $this->validation_lib->respondSuccess($result);
  }

  public function google_account_post()
  {
    $code = $this->input->post('code');

    $result = $this->model->get_google_oauth_account($code);

    if (!empty($result['status']) && $result['status'] == true)
      $this->validation_lib->respondSuccess($result['payload']);
    else
      $this->validation_lib->respondError($result['message']);
  }
}