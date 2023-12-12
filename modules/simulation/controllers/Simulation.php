<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;
use FontLib\Table\Type\name;

class Simulation extends RestController
{

    function __construct()
    {
        parent::__construct();
        // $this->authentication->init();
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "OPTIONS") {
            die();
        }

        $this->load->model(Simulation_model::class, 'model');
    }

    public function index_get() {
      dd('here');
    }

    public function loan_post()
    {
        $data = $this->post();

        $this->form_validation->set_data($data);

        $this->form_validation->set_rules('amount', 'Amount', 'greater_than_equal_to[500000]|numeric|trim|required');
        $this->form_validation->set_rules('tenor', 'Tenor', 'numeric|trim|required');
        $this->form_validation->set_rules('grade', 'Grade', 'trim|required');

        $this->form_validation->set_message('required', '%s tidak boleh kosong.');
        $this->form_validation->set_message('numeric', '%s harus berupa angka.');
        $this->form_validation->set_message('greater_than_equal_to', '%s harus minimal 500000.');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->get_array_errors();
            $this->validation_lib->respondError($errors);
            die;
        }
        
        if((int) $data['amount'] % 100000 !== 0 ) {
            $this->validation_lib->respondError('Amount harus berupa kelipatan 100000');
            die;
        }
        
        $result = $this->model->calculate_loan($data);

        $this->validation_lib->respondSuccess($result);
    }

    public function lending_post()
    {
        $data = $this->post();

        $this->form_validation->set_data($data);

        $this->form_validation->set_rules('amount', 'Amount', 'greater_than_equal_to[100000]|numeric|trim|required');
        $this->form_validation->set_rules('tenor', 'Tenor', 'numeric|trim|required');
        $this->form_validation->set_rules('grade', 'Grade', 'trim|required');

        $this->form_validation->set_message('required', '%s tidak boleh kosong.');
        $this->form_validation->set_message('numeric', '%s harus berupa angka.');
        $this->form_validation->set_message('greater_than_equal_to', '%s harus minimal 100000.');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->get_array_errors();
            $this->validation_lib->respondError($errors);
            die;
        }
        
        if((int) $data['amount'] % 100000 !== 0 ) {
            $this->validation_lib->respondError('Amount harus berupa kelipatan 100000');
            die;
        }
        
        $result = $this->model->calculate_lending($data);

        $this->validation_lib->respondSuccess($result);
    }

}