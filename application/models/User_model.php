<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', true);
    }

    /**
     * Find data.
     *
     * @param $username
     * @return mixed
     */
    public function find($username)
    {
        return $this->db->get_where("api_user", array('username' => $username))->row(0);
    }

    public function getByToken($token)
    {
        return $this->db->get_where("api_user", array('token' => $token))->row(0);
    }

    public function getExistingToken()
    {
        return $this->db->get_where("tb_token_key")->row(0);
    }

    public function edit($username, $data)
    {
        if (!$this->find($username)) {
            $this->validation_lib->respondError('ID tidak ditemukan!');
        }
        $this->db->trans_start();
        $this->db->where('username', $username);
        $this->db->update('api_user', $data);
        $this->db->trans_complete();
        //anticipate if row doesn't have any data change, it will return num_rows 0. Let's check if query doesn't have error return true
        if ($this->db->trans_status() === FALSE) {
            // generate an error... or use the log_message() function to log your error
            return false;
        }
        return true;
    }

    public function update_sanders_token($token)
    {
      $this->db->empty_table('tb_token_key');
      return $this->db->insert('tb_token_key', ['token' => $token]);
    }

}
