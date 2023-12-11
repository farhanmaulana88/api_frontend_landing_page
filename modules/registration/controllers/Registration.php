<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;
use FontLib\Table\Type\name;

class Registration extends RestController
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

        $this->load->model(Registration_model::class, 'model');
    }

    public function index_post()
    {
        $data = $this->post();
        $used_email = 'test@mail.com';

        $this->form_validation->set_data($data);

        $this->form_validation->set_rules('email', 'Email', 'valid_email|trim|required');

        $this->form_validation->set_message('required', '%s tidak boleh kosong.');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->get_array_errors();
            $this->validation_lib->respondError($errors);
            die;
        } 
        
        if($data['email'] === $used_email) {
            $this->validation_lib->respondError(['message' => 'Email sudah terdaftar']);
            die;
        }
        
        $this->validation_lib->respondSuccess('Email dapat digunakan.');
    }

    public function email_post()
    {
        $data = $this->post();

        $this->form_validation->set_data($data);

        $this->form_validation->set_rules('email', 'Email', 'valid_email|trim|required');
        $this->form_validation->set_rules('fullname', 'Full Name', 'trim|required');
        $this->form_validation->set_rules('nik', 'NIK', 'numeric|trim|required');
        $this->form_validation->set_rules('birthdate', 'Birth Date', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[8]|max_length[20]');


        $this->form_validation->set_message('required', '%s tidak boleh kosong.');
        $this->form_validation->set_message('numeric', '%s harus berisi angka.');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->get_array_errors();
            $this->validation_lib->respondError($errors);
        } else {

            $check_birthdate = $this->isValidDate($data['birthdate']);

            if (!$check_birthdate) {
                $this->validation_lib->respondError(['birthdate' => "Masukan Format tanggal yang sesuai (Y-m-d)"]);
            } else {
                $proses = $this->model->register($data);
                $this->validation_lib->respondSuccess($proses);
            }
        }
    }

    public function resend_verification_post()
    {
        $data = $this->post();
        $used_email = 'test@mail.com';

        $this->form_validation->set_data($data);

        $this->form_validation->set_rules('email', 'Email', 'valid_email|trim|required');

        $this->form_validation->set_message('required', '%s tidak boleh kosong.');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->get_array_errors();
            $this->validation_lib->respondError($errors);
            die;
        } 
        
        if($data['email'] === $used_email) {
            $this->validation_lib->respondError(['message' => 'Email sudah terdaftar']);
            die;
        }
        
        $this->validation_lib->respondSuccess('Email Verifikasi berhasil dikirim.');
    }

    public function verification_post()
    {
        $post = $this->post();
        $id_unique = '21b6a798321a49f75b9c3827fa3cfdb7efe2c4e05c3d13aefe1c825b9774a158';

        if (empty($post['id'])) {
            $this->validation_lib->respondError('ID tidak boleh kosong!');
        }

        if ($post['id'] == $id_unique) {
            $this->validation_lib->respondSuccess([]);
        } else {
            $this->validation_lib->respondError('ID salah / sudah kadaluarsa!');
        }
    }

    public function check_date($birthday)
    {
        // Check string is valid date
        $birthday = date("YYY-MM-DD", strtotime($birthday));

        if ($birthday) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function isValidDate($date, $format = 'Y-m-d')
    {
        $dateTimeObject = DateTime::createFromFormat($format, $date);

        return $dateTimeObject && $dateTimeObject->format($format) === $date;
    }

}