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
        $this->load->database();

        if ($this->db->table_exists('api_user')) {
            $this->validation_lib->respondError('Table sudah tersedia!');
        } else {
            //        bikin query untuk create table - TO DO dan alter table
            $queryCreateTable = "CREATE TABLE `api_user` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `username` varchar(100) NOT NULL,
                  `password` varchar(255) DEFAULT NULL,
                  `token` varchar(255) DEFAULT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1";
            $tableTransaction = $this->db->query($queryCreateTable);
            if ($tableTransaction) {

                $data['username'] = 'administrator';
                $data['password'] = 'd033e22ae348aeb5660fc2140aec35850c4da997';
                $insert = $this->db->insert('api_user', $data);
                if ($insert) {
                    $this->validation_lib->respondSuccess('Table berhasil ditambahkan!');
                } else {
                    $this->validation_lib->respondError('Gagal menambahkan data user');
                }
            }else{
                $this->validation_lib->respondError('Gagal menambahkan table user');

            }
        }
    }
}
