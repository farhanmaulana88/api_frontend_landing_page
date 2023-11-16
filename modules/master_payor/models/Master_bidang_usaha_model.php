<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Master_bidang_usaha_model extends CI_Model
{
    /**
     * Tipe_industri_model constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Find data.
     *
     * @param $id
     * @return mixed
     */
    public function find($filters)
    {
        return $this->db->get_where("tb_master_kbli_satu", $filters)->row(0);
    }

    /**
     * Read all data.
     *
     * @return mixed
     */
    public function all($filters = array())
    {
        if (empty($filters))
            return $this->db->get("tb_master_kbli_satu")->result();
        else
            return $this->db->get_where("tb_master_kbli_satu", $filters)->result();

    }
}
