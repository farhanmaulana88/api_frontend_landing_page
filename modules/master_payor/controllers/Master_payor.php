<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;
use FontLib\Table\Type\name;

/**
 * Class Tipe_industri
 *
 * @property Tipe_industri_model tipe_industri_model
 * @property Authentication authentication
 * @property Validation_lib validation_lib
 */
class Master_payor extends RestController
{
  function __construct()
  {
    parent::__construct();
    $this->authentication->init();

    $this->load->model(Master_payor_model::class, 'model');
  }

  public function index_get($id_payor = null)
  {
    $proses = $this->model->find($id_payor);
    $this->validation_lib->respondSuccess($proses);
  }

  public function get_data_post()
  {
    $post = $this->post();
    $proses = $this->model->get_payor($post);
    $this->validation_lib->respondSuccess($proses);
  }

  public function create_post()
  {
    $data = $this->post();

    $this->form_validation->set_data($data['data_payor']);
    $this->form_validation->set_rules('payor_name', 'Nama payor', 'trim|required');
    $this->form_validation->set_rules('payor_initial_name', 'Nama inisial', 'trim|required');
    $this->form_validation->set_rules('payor_business_entity', 'Bentuk Bidang Usaha', 'trim|required');
    $this->form_validation->set_rules('payor_type_of_business', 'Jenis usaha', 'trim|required');
    $this->form_validation->set_rules('payor_type', 'Type usaha', 'trim|required');
    // $this->form_validation->set_rules('payor_since', 'Lama Kerjasama', 'trim|required');
    $this->form_validation->set_rules('payor_long_business', 'Lama Berusaha', 'trim|required');

    $this->form_validation->set_rules('payor_province', 'Provinsi Domisili Payor', 'trim|required');
    $this->form_validation->set_rules('payor_city', 'Kota / Kabupaten Domisili Payor', 'trim|required');
    $this->form_validation->set_rules('payor_district', 'Kecamatan Domisili Payor', 'trim|required');
    $this->form_validation->set_rules('payor_village', 'Desa / Kelurahan Domisili Payor', 'trim|required');
    $this->form_validation->set_rules('payor_rw_address', 'RW Domisili Payor', 'trim|required');
    $this->form_validation->set_rules('payor_rt_address', 'RT Domisili Payor', 'trim|required');
    $this->form_validation->set_rules('payor_postal_code', 'Kode Pos Domisili Payor	', 'trim|required');
    $this->form_validation->set_rules('payor_address', 'Alamat Domisili Payor', 'trim|required');

    $this->form_validation->set_rules('pic_name', 'Nama PIC', 'trim|required');
    $this->form_validation->set_rules('pic_position', 'Jabatan PIC', 'trim|required');
    $this->form_validation->set_rules('pic_phone', 'No Telepon PIC', 'numeric|required');
    $this->form_validation->set_rules('pic_email', 'E-mail PIC', 'valid_email|trim|required');
    $this->form_validation->set_rules('pic_length_of_work', 'Lama Bekerja PIC', 'trim|required');
    $this->form_validation->set_rules('created_by', 'Created by', 'trim|required');

    $this->form_validation->set_message('required', '%s tidak boleh kosong.');
    $this->form_validation->set_message('numeric', '%s harus berisi angka.');
    $this->form_validation->set_message('valid_email', '%s harus sesuai.');
    $this->form_validation->set_message('valid_url', '%s harus sesuai.');

    if (!$this->form_validation->run()) {
      $errors = $this->form_validation->get_array_errors();
      $this->validation_lib->respondError($errors);
    }

    if (count($data['shareholder']) > 0) {
      if (count($data['shareholder']["name"]) > 0) {
        $new_arr = [];
        $keys = [];
        foreach ($data['shareholder'] as $key => $val) {
          $keys[] = $key;
        }

        for ($i = 1; $i < count($data['shareholder'][$keys[0]]) + 1; $i++) {
          $arr = [];
          foreach ($keys as $value) {
            $arr[$value] = $data['shareholder'][$value][$i];
          }
          $new_arr[] = $arr;
        }
        $data["shareholder"] = $new_arr;
      }
    }

    $proses = $this->model->create_payor($data);
    if ($proses) {
      $this->validation_lib->respondSuccess($proses);
    } else {
      $this->validation_lib->respondError('Gagal menyimpan data.');
    }
  }

  public function update_put($payor_code)
  {

    $data = $this->put();
    // dd($data);
    $this->form_validation->set_data($data['data_payor']);
    $this->form_validation->set_rules('payor_name', 'Nama payor', 'trim|required');
    $this->form_validation->set_rules('payor_initial_name', 'Nama inisial', 'trim|required');
    $this->form_validation->set_rules('payor_business_entity', 'Bentuk Bidang Usaha', 'trim|required');
    $this->form_validation->set_rules('payor_type_of_business', 'Jenis usaha', 'trim|required');
    $this->form_validation->set_rules('payor_type', 'Type usaha', 'trim|required');
    // $this->form_validation->set_rules('payor_since', 'Lama Kerjasama', 'trim|required');
    $this->form_validation->set_rules('payor_long_business', 'Lama Berusaha', 'trim|required');

    $this->form_validation->set_rules('payor_province', 'Provinsi Domisili Payor', 'trim|required');
    $this->form_validation->set_rules('payor_city', 'Kota / Kabupaten Domisili Payor', 'trim|required');
    $this->form_validation->set_rules('payor_district', 'Kecamatan Domisili Payor', 'trim|required');
    $this->form_validation->set_rules('payor_village', 'Desa / Kelurahan Domisili Payor', 'trim|required');
    $this->form_validation->set_rules('payor_rw_address', 'RW Domisili Payor', 'trim|required');
    $this->form_validation->set_rules('payor_rt_address', 'RT Domisili Payor', 'trim|required');
    $this->form_validation->set_rules('payor_postal_code', 'Kode Pos Domisili Payor	', 'trim|required');
    $this->form_validation->set_rules('payor_address', 'Alamat Domisili Payor', 'trim|required');

    $this->form_validation->set_rules('pic_name', 'Nama PIC', 'trim|required');
    $this->form_validation->set_rules('pic_position', 'Jabatan PIC', 'trim|required');
    $this->form_validation->set_rules('pic_phone', 'No Telepon PIC', 'numeric|required');
    $this->form_validation->set_rules('pic_email', 'E-mail PIC', 'valid_email|trim|required');
    $this->form_validation->set_rules('pic_length_of_work', 'Lama Bekerja PIC', 'trim|required');
    $this->form_validation->set_rules('updated_by', 'Updated by', 'trim|required');

    $this->form_validation->set_message('required', '%s tidak boleh kosong.');
    $this->form_validation->set_message('numeric', '%s harus berisi angka.');
    $this->form_validation->set_message(' ', '%s harus sesuai.');
    $this->form_validation->set_message('valid_url', '%s harus sesuai.');
    // $this->validation_lib->respondError('test');
    if (!$this->form_validation->run()) {
      $errors = $this->form_validation->get_array_errors();
      $this->validation_lib->respondError($errors);
    }

    if (count($data['shareholder']) > 0) {
      if (count($data['shareholder']["name"]) > 0) {
        $new_arr = [];
        $keys = [];
        foreach ($data['shareholder'] as $key => $val) {
          $keys[] = $key;
        }

        for ($i = 1; $i < count($data['shareholder'][$keys[0]]) + 1; $i++) {
          $arr = [];
          foreach ($keys as $value) {
            $arr[$value] = $data['shareholder'][$value][$i];
          }
          $new_arr[] = $arr;
        }
        $data["shareholder"] = $new_arr;
      }
    }
    // dd($data);
    $proses = $this->model->update_payor($payor_code, $data);


    if ($proses) {
      $this->validation_lib->respondSuccess($proses);
    } else {
      $this->validation_lib->respondError('Gagal memperbaharui data.');
    }
  }


  public function checkDateFormat($date)
  {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if (($d && $d->format('Y-m-d') === $date) === FALSE) {
      $this->form_validation->set_message('checkDateFormat', '' . $date . ' tidak valid, format harus(yyyy-mm-dd)');
      return FALSE;
    } else {
      return TRUE;
    }
  }

  public function delete_data_post($payor_code)
  {
    $data = $this->post();
    $proses = $this->model->delete_payor($payor_code, $data);
    if ($proses) {
      $this->validation_lib->respondSuccess($proses);
    } else {
      $this->validation_lib->respondError('Gagal menghapus data.');
    }
  }
  public function detail_payor_get($payor_code)
  {
    $data = $this->model->get_detail_payor($payor_code);

    $this->validation_lib->respondSuccess($data);
  }
}
