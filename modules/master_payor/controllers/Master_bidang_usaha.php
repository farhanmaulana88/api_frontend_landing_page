<?php
defined('BASEPATH') or exit('No direct script access allowed');
use chriskacerguis\RestServer\RestController;

/**
 * Class Tipe_industri
 *
 * @property Master_bidang_usaha_model model
 * @property Authentication authentication
 * @property Validation_lib validation_lib
 */
class Master_bidang_usaha extends RestController
{
    function __construct()
    {
        parent::__construct();
        $this->authentication->init();

        $this->load->model(Master_bidang_usaha_model::class, 'model');
    }

    public function index_get($id = 0)
    {
        if(isset($_GET['filters'])){
            $filters = $_GET['filters'];
        }else{
            $filters = array();
        }
        if (!empty($id)) {
            $filters['id_param_business_field'] = $id;
            $data = $this->model->find($filters);
        } else {
            $data = $this->model->all($filters);
        }
        $this->validation_lib->respondSuccess($data);
    }
}
