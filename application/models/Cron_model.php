<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('default', true);
        $this->db1 = $this->load->database('v1', true);
    }

    public function reminderFacilityFee($days, $kelipatan)
    {
        //$kelipatan = 0 jika pengulangannya hanya mau 1 kali
        $whereOr = array();
        if ($kelipatan > 0) {
            for ($i = $days; $i > 0; $i = $i - $kelipatan) {
                $whereOr[] = "DATE_FORMAT((`expired_date` - INTERVAL $i DAY), '%Y-%m-%d') = DATE_FORMAT(NOW(), '%Y-%m-%d')";
            }
        } else {
            $whereOr[] = "DATE_FORMAT((`expired_date` - INTERVAL $days DAY), '%Y-%m-%d') = DATE_FORMAT(NOW(), '%Y-%m-%d')";
        }
        $queryOr = "(" . implode(' OR ', $whereOr) . ")";
        $query = "SELECT * FROM `tb_giro_borrower` WHERE is_ttd = '1' 
        AND " . $queryOr;
        $entities = $this->db->query($query)->result();
        $borrowerArr = array();
        foreach ($entities as $entity) {
            $register_code = $entity->register_code;
            $query = "SELECT a.register_email,b.* FROM tb_fintech_register a LEFT JOIN tb_fintech_borrower_detail_commercial_sme b on a.register_code = b.register_code where a.register_code='$register_code'";
            $borrower = $this->db1->query($query)->row();
            if ($borrower) {
                $borrowerArr[] = $borrower;
            } else {
                $this->validation_lib->respondNotFound("Borrower tidak ditemukan!");
            }
        }
        if ($borrowerArr) {
            $this->sendEmail($borrowerArr);
        }
        return $entities;
    }


    public function sendEmail($borrowerArr)
    {
        $this->config->load('email');
        $config['charset'] = 'iso-8859-1';
        $config['useragent'] = 'Codeigniter';
        $config['protocol'] = 'smtp';
        $config['mailtype'] = 'html';

        $config['smtp_host'] = $this->config->item('smtp_host'); //pengaturan smtp
        $config['smtp_port'] = $this->config->item('smtp_port');
        $config['smtp_timeout'] = $this->config->item('smtp_timeout');
        $config['smtp_user'] = $this->config->item('smtp_user'); // isi dengan email kamu
        $config['smtp_pass'] = $this->config->item('smtp_pass'); // isi dengan password kamu

        $config['crlf'] = "\r\n";
        $config['newline'] = "\r\n";
        $config['wordwrap'] = TRUE;

        //memanggil library email dan set konfigurasi untuk pengiriman email
        $this->load->library('email', $config);
        $this->email->initialize($config);
        foreach ($borrowerArr as $borrower) {
            $register_email = $borrower->register_email;
//        $register_email = 'henry.sanders625@gmail.com';
            //konfigurasi pengiriman
            $this->email->from($config['smtp_user']);
            $this->email->to($register_email);
            $this->email->subject("Notifikasi Expired Limit Borrower");
            $body = $this->templateEmailExpired($borrower);
            $this->email->message($body);
            $isKirim = $this->email->send();
            if ($isKirim) {
                return $isKirim;
            } else {
                $this->validation_lib->respondError('E-mail gagal dikirim!');
            }
        }


    }

    private function templateEmailExpired($data)
    {
        $template = "<p>Kepada Yth. " . $data->borrower_business_name . "</p><br/>";
        $template .= "<p>Pengajuan limit Anda akan segera berakhir!<br/>Silakan mengajukan facility fee kembali untuk menambah limit Anda<br/></p>";
        return $template;
    }



}
