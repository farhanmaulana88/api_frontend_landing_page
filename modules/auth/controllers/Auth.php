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
