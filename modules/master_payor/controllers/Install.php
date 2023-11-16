<?php
defined('BASEPATH') or exit('No direct script access allowed');
use chriskacerguis\RestServer\RestController;
use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Class Example
 *
 * @property Authentication authentication
 * @property Validation_lib validation_lib
 */
class Install extends CI_Controller
{

    public function index()
    {
        if (!$this->db->table_exists('tb_master_payor')) {
            $tableQuery = "CREATE TABLE `tb_master_payor` (
              `id_payor` int(11) NOT NULL AUTO_INCREMENT,
              `payor_code` varchar(50) NOT NULL,
              `payor_name` varchar(255) DEFAULT NULL,
              `payor_initial_name` varchar(50) DEFAULT NULL,
              `payor_type_of_business` int(11) DEFAULT NULL,
              `payor_type` varchar(50) DEFAULT NULL,
              `payor_since` date DEFAULT NULL,
              `payor_brand` varchar(255) DEFAULT NULL,
              `payor_phone` varchar(50) DEFAULT NULL,
              `payor_email` varchar(50) DEFAULT NULL,
              `payor_tier` varchar(50) DEFAULT NULL,
              `payor_web_url` text DEFAULT NULL,
              `payor_address` text DEFAULT NULL,
              `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              `deleted_at` datetime DEFAULT NULL,
              `is_delete` enum('No','Yes') NOT NULL DEFAULT 'No',
              PRIMARY KEY (`id_payor`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
            $proses = $this->db->query($tableQuery);
            if (!$proses) {
              $this->validation_lib->respondError('Gagal menambahkan tabel tb_master_payor');
            }
        }

        if (!$this->db->field_exists('payor_brand', 'tb_master_payor')) {
          $tableQuery = "ALTER TABLE tb_master_payor ADD COLUMN payor_brand varchar(255)";
          $proses = $this->db->query($tableQuery);
          if (!$proses) {
            $this->validation_lib->respondError('Gagal menambahkan field payor_brand');
          }
        }

        if (!$this->db->field_exists('payor_email', 'tb_master_payor')) {
          $tableQuery = "ALTER TABLE tb_master_payor ADD COLUMN payor_email varchar(255)";
          $proses = $this->db->query($tableQuery);
          if (!$proses) {
            $this->validation_lib->respondError('Gagal menambahkan field payor_email');
          }
        }

        if (!$this->db->field_exists('payor_tier', 'tb_master_payor')) {
          $tableQuery = "ALTER TABLE tb_master_payor ADD COLUMN payor_tier varchar(50)";
          $proses = $this->db->query($tableQuery);
          if (!$proses) {
            $this->validation_lib->respondError('Gagal menambahkan field payor_tier');
          }
        }

        if (!$this->db->field_exists('payor_initial_name', 'tb_master_payor')) {
          $tableQuery = "ALTER TABLE tb_master_payor ADD COLUMN payor_initial_name varchar(50)";
          $proses = $this->db->query($tableQuery);
          if (!$proses) {
            $this->validation_lib->respondError('Gagal menambahkan field payor_initial_name');
          }
        }

        if (!$this->db->field_exists('payor_address', 'tb_master_payor')) {
          $tableQuery = "ALTER TABLE tb_master_payor ADD COLUMN payor_address text";
          $proses = $this->db->query($tableQuery);
          if (!$proses) {
            $this->validation_lib->respondError('Gagal menambahkan field payor_address');
          }
        }

        if (!$this->db->field_exists('payor_since', 'tb_master_payor')) {
          $tableQuery = "ALTER TABLE tb_master_payor ADD COLUMN payor_since date";
          $proses = $this->db->query($tableQuery);
          if (!$proses) {
            $this->validation_lib->respondError('Gagal menambahkan field payor_since');
          }
        }else{
          $tableQuery = "ALTER TABLE tb_master_payor MODIFY payor_since date DEFAULT NULL";
          $proses = $this->db->query($tableQuery);
          if (!$proses) {
            $this->validation_lib->respondError('Gagal mengubah type data payor_since');
          }
        }

        $this->validation_lib->respondSuccess('Tabel payor berhasil diperbaharui');
    }
}
