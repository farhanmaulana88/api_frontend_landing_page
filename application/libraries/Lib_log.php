<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Lib_log
{
    protected $_ci;

    function __construct()
    {
        $this->_ci = &get_instance();
    }

    public function create_log($log_name, $log_data, $log_status, $table_info = [])
    {
        $log_id = 'LG' . date('Ymd') . '-';

        $context_db = $this->_ci->load->database('default', TRUE);

        $cekID = $context_db->query("SELECT max(log_id) as maxID
        FROM main_log WHERE log_id LIKE '" . $log_id . "%'")->result();

        $noUrut = (int)substr(@$cekID[0]->maxID, 12, 8);
        $noUrut++;
        $newID = $log_id . sprintf("%08s", $noUrut);

        $auth_user = $this->_ci->authentication->getUser();
        if ($auth_user == '') {
          $id_auth_user = '';
        }else{
          $id_auth_user = $auth_user->id;
        }

        $insert_log = [
            'log_id' => $newID,
            'log_name' => $log_name,
            'log_data' => !empty($log_data) ? json_encode($log_data, true) : null,
            'log_status' => $log_status,
            'table_info' => json_encode($table_info),
            'created_by' => $id_auth_user,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $context_db->insert('main_log', $insert_log);
    }

    public function create_first_log($log_name, $log_data, $log_status, $created_by)
    {
        $log_id = 'LG' . date('Ymd') . '-';

        $cekID = $this->_ci->db->query("SELECT max(log_id) as maxID
        FROM main_log WHERE log_id LIKE '" . $log_id . "%'")->result();

        $noUrut = (int)substr(@$cekID[0]->maxID, 12, 8);
        $noUrut++;
        $newID = $log_id . sprintf("%08s", $noUrut);

        $insert_log = [
            'log_id' => $newID,
            'log_name' => $log_name,
            'log_data' => json_encode($log_data, true),
            'log_status' => $log_status,
            'created_by' => $created_by,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->_ci->db->insert('main_log', $insert_log);
    }
}
