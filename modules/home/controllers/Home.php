<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;
use FontLib\Table\Type\name;

class Home extends RestController
{
  function __construct()
  {
    parent::__construct();
    // $this->authentication->init();
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    $method = $_SERVER['REQUEST_METHOD'];
    if($method == "OPTIONS") {
    die();
    }

    $this->load->model(Home_model::class, 'model');
  }

  public function carousell_get()
  {
    $proses = $this->model->carousell();
    $this->validation_lib->respondSuccess($proses);
  }

  public function statistic_get($group)
  {
    $proses = $this->model->statistic($group);
    $this->validation_lib->respondSuccess($proses);
  }

}
