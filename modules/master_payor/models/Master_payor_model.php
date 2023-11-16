<?php

use LDAP\Result;

defined('BASEPATH') or exit('No direct script access allowed');

class Master_payor_model extends CI_Model
{
  /**
   * Tipe_industri_model constructor.
   */
  public function __construct()
  {
    parent::__construct();
    $this->db1 = $this->load->database('v1', true);
  }

  public function find($id_payor = null)
  {
    $this->db->select('id_payor,payor_name');
    if ($id_payor) {
      $this->db->where('id_payor', $id_payor);
    }
    return $this->db->get('tb_master_payor')->result();
  }

  public function get_payor($post)
  {
    $data = $this->db->select('
      a.*,
      floor(DATEDIFF(now(), a.payor_since)/365) AS payor_operating_year,
      floor(DATEDIFF(now(), a.payor_long_business)/365) AS payor_long_business_year,
      b.industry_name as payor_type_of_business_name,
      e.entity_business_name as payor_bussines_entity_name,
      c.payor_type_code,
      c.payor_type_name,
      d.tier_name as payor_tier_name,
      CASE
        WHEN (select count(sub_a.id_mpb) from tb_meet_potencial_borrower sub_a where a.payor_code = sub_a.id_payor) > 0 THEN 0
        WHEN (select count(sub_a.id_mpb) from tb_meet_potencial_borrower sub_a where a.payor_code = sub_a.mpb_payor_id_payor) > 0 THEN 0
        WHEN (select count(sub_a.id_payor) from tb_fintech_payor_borrower sub_a where a.id_payor = sub_a.id_payor) > 0 THEN 0
        WHEN (select count(sub_a.payor_id) from tb_potential_borrower sub_a where a.id_payor = sub_a.payor_id) > 0 THEN 0
        ELSE 1
      END as deletable,
      ')
      ->from('tb_master_payor a')
      ->join($this->db1->database . '.tb_param_industry b', 'a.payor_type_of_business = b.id_param_industry', 'LEFT')
      ->join('tb_master_payor_type c', 'a.payor_type = c.payor_type_code', 'LEFT')
      ->join('tb_master_tier d', 'a.payor_tier = d.id_tier', 'LEFT')
      ->join($this->db1->database . '.tb_param_entity_business e', 'a.payor_business_entity = e.id_param_entity_business', 'LEFT')
      ->where('a.is_delete', 'No');
    if (isset($post['payor_code']) && $post['payor_code'] != '') $data = $data->like('a.payor_code', $post['payor_code']);
    if (isset($post['payor_name']) && $post['payor_name'] != '') $data = $data->like('a.payor_name', $post['payor_name']);
    if (isset($post['payor_initial_name']) && $post['payor_initial_name'] != '') $data = $data->like('a.payor_initial_name', $post['payor_initial_name']);
    if (isset($post['payor_brand']) && $post['payor_brand'] != '') $data = $data->like('a.payor_brand', $post['payor_brand']);
    if (isset($post['payor_type_of_business']) && $post['payor_type_of_business'] != '') $data = $data->where('a.payor_type_of_business', $post['payor_type_of_business']);
    if (isset($post['payor_operating_year']) && $post['payor_operating_year'] != '') $data = $data->where('round(DATEDIFF(now(), a.payor_since)/365) = ' . $post['payor_operating_year'], NULL);
    if (isset($post['payor_tier']) && $post['payor_tier'] != '') $data = $data->where('a.payor_tier', $post['payor_tier']);
    if ((isset($post['page']) && $post['page'] != '') && (isset($post['row_per_page']) && $post['row_per_page'] != '')) $data = $data->limit($post['row_per_page'], $post['page']);
    $data = $data->order_by('a.created_at', 'desc')->get()->result();

    return $data;
  }

  public function generate_payor_code()
  {
    $setting = $this->db1->from('tb_setting')
      ->order_by('id_setting', 'ASC')
      ->get()->row();
    $max_code = $this->db->select('MAX(payor_code) as max_code')
      ->from('tb_master_payor')
      ->like('payor_code', $setting->reg_ID_pay)
      ->get()->row()->max_code;
    $noUrut = (int) substr($max_code, 4, 8);
    $noUrut++;
    $payor_code = $setting->reg_ID_pay . sprintf("%08s", $noUrut);
    return $payor_code;
  }

  public function create_payor($data)
  {
    $this->db->trans_start();
    $payor_code['payor_code'] = $this->generate_payor_code();
    $check = [];
    foreach ($data["shareholder"] as $field => $key) {
      $field = $key;
      $check = !empty($field["name"]);
    }

    $param_array = [
      "payor_code" => $payor_code['payor_code'],
      "created_by" =>  $data['data_payor']["created_by"],
    ];

    $data['data_payor']["payor_code"] =  $payor_code['payor_code'];
    $this->db->insert('tb_master_payor', $data["data_payor"]);

    // Pre kondisi sebelum insert share holder========
    if ($check == true) {
      $param_array = [
        "payor_code" => $payor_code['payor_code'],
        "created_by" =>  $data['data_payor']["created_by"],
      ];
      $shareholder = [];
      foreach ($data["shareholder"] as $key => $val) {
        $arr = array_merge($data["shareholder"][$key], $param_array);
        $new_arr[] = $arr;
        $shareholder = $new_arr;
      }
      foreach ($shareholder as $key) {
        $this->create_shareholder($key);
      }
    }
    //========== end
    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
      return false;
    }
    $table = [
      "tb_master_payor" => null,
      "tb_master_payor_shareholder" => null,
    ];
    $this->lib_log->create_log('Menambahkan Data Payor', $data, 'Create', $table);

    return true;
  }

  public function update_payor($payor_code, $data)
  {
    $payor = $this->get_detail_payor($payor_code);
    // dd($payor);
    //check shareholder
    $check = [];
    foreach ($data["shareholder"] as $field => $key) {
      $field = $key;
      $check = !empty($field["name"]);
    }

    if (empty($payor)) {
      $this->validation_lib->respondError('Payor code tidak ditemukan!');
    }
    $this->db->trans_start();
    $this->db->where('payor_code', $payor_code)->update('tb_master_payor', $data['data_payor']);

    // Pre kondisi sebelum Update share holder========
    if ($check == true) {
      $param_array = [
        "payor_code" => $payor_code,
        "updated_by" =>  $data['data_payor']["updated_by"],
      ];
      $shareholder = [];
      foreach ($data["shareholder"] as $key => $val) {
        $arr = array_merge($data["shareholder"][$key], $param_array);
        $new_arr[] = $arr;
        $shareholder = $new_arr;
      }

      $this->db->delete('tb_master_payor_shareholder', ["payor_code" => $payor_code]);
      foreach ($shareholder as $key) {
        $this->update_shareholder($key);
      }
    } else {
      if (count($payor["shareholder"]) > 0) {
        $this->db->delete('tb_master_payor_shareholder', ['payor_code' => $payor_code]);
      }
    }
    $this->db->trans_complete();
    if ($this->db->trans_status() === FALSE) {
      return false;
    }
    $table = [
      "tb_master_payor" => ['payor_code' => $payor_code],
      "tb_master_payor_shareholder" => ['payor_code' => $payor_code],
    ];
    $this->lib_log->create_log('Mengubah Data Payor', $data, 'Update', $table);

    return true;
  }

  public function delete_payor($payor_code, $userid)
  {

    $payor = $this->get_payor(['payor_code' => $payor_code]);
    if (empty($payor)) {
      $this->validation_lib->respondError('Payor code tidak ditemukan!');
    }
    $data = [
      'deleted_at' => date("Y-m-d H:i:s"),
      'is_delete' => "Yes",
      'deleted_by' => $userid['deleted_by'],
    ];
    $this->db->trans_start();
    $this->db->where('payor_code', $payor_code)->update('tb_master_payor', $data);
    $this->db->trans_complete();
    if ($this->db->trans_status() === FALSE) {
      return false;
    }
    $table = [
      "tb_master_payor" => ['payor_code' => $payor_code],
    ];
    $this->lib_log->create_log('Menghapus Data Payor', $data, 'Delete', $table);

    return true;
  }


  public function get_detail_payor($payor_code)
  {
    $check_code = substr($payor_code, 0, 3);

    if ($check_code == 'PAY') {
      $params = ['payor_code' => $payor_code];
    } else {
      $params = ['id_payor' => $payor_code];
    }
    $data = $this->db->select('
      a.*,
      floor(DATEDIFF(now(), a.payor_since)/365) AS payor_operating_year,
      floor(DATEDIFF(now(), a.payor_long_business)/365) AS payor_long_business_year,
      floor(DATEDIFF(now(), a.pic_length_of_work)/365) AS pic_length_of_work_year,
      b.industry_name as payor_type_of_business_name,
      c.payor_type_code,
      c.payor_type_name,
      d.tier_name as payor_tier_name,
      e.entity_business_name as payor_bussines_entity_name,
      a.scoring_jenis_payor,
      a.scoring_distance_office_payor
      ')
      ->from('tb_master_payor a')
      ->join($this->db1->database . '.tb_param_industry b', 'a.payor_type_of_business = b.id_param_industry', 'LEFT')
      ->join('tb_master_payor_type c', 'a.payor_type = c.payor_type_code', 'LEFT')
      ->join('tb_master_tier d', 'a.payor_tier = d.id_tier', 'LEFT')
      ->join($this->db1->database . '.tb_param_entity_business e', 'a.payor_business_entity = e.id_param_entity_business', 'LEFT')
      ->where('a.is_delete', 'No')
      ->where($params)
      ->get()->row();

    $result = [
      "data_payor" => $data,
      "shareholder" => $this->get_shareholder($data->payor_code)
    ];
    return $result;
  }

  public function get_shareholder($payor_code)
  {
    $data = $this->db->select("*")->from("tb_master_payor_shareholder")->where('payor_code', $payor_code)->get()->result();
    return $data;
  }

  function create_shareholder($params)
  {
    $this->db->trans_start();;
    $this->db->insert('tb_master_payor_shareholder', $params);
    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
      $data =  false;
    } else {
      $data =  true;
    }
    return $data;
  }
  function update_shareholder($params)
  {

    $data = [
      "name" => $params["name"],
      "payor_code" => $params["payor_code"],
      "tanggal" => $params["tanggal"],
      "lembar" => $params["lembar"],
      "persentase" => $params["persentase"],
      "created_by" => $params["updated_by"],
      "created_at" => date('Y-m-d H:i:s'),
      "updated_by" => $params["updated_by"],
      "shareholder_status" => $params["shareholder_status"]
    ];
    return $this->db->insert('tb_master_payor_shareholder', $data);
  }
}
