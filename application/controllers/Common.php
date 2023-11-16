<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;

use function PHPSTORM_META\map;

/**
 * Class Example
 *
 * @property Common_model model
 * @property Authentication authentication
 * @property Validation_lib validation_lib
 */
class Common extends RestController
{
    function __construct()
    {
        parent::__construct();
        $this->load->library('Bni_investor');
        $this->load->model("Common_model", 'model');
        $this->load->model("transfer_rdl/Transfer_rdl_model", 'transfer_rdl_model');
        $this->load->model("deposit_rdl_lender/Deposit_rdl_lender_model", 'deposit_rdl_lender_model');
    }

    // Penarikan RDL pada Daftar Pendanaan Loan
    public function withdrawing_funds_loan_get()
    {
        // Load model
        $this->load->model('Daftar_pendanaan_loan/Daftar_pendanaan_loan_model', 'daftar_pendanaan_loan_model');

        // Get current date
        $date = date('Y-m-d');

        // Get data from database
        $data = $this->daftar_pendanaan_loan_model->get_data_not_yet_withdrawn($date);

        // Withdrawing funds each item
        foreach ($data as $item) {
            // Get info loan
            $lender_code = $item->register_code;

            // Post withdrawal RDL
            $result = $this->daftar_pendanaan_loan_model->post_withdrawal_rdl($date, $lender_code);

            // Check if process is error
            $this->db->insert('tb_history_funding_rdl_cron', [
                'lender_code' => $lender_code,
                'loan_date' => $date,
                'response' => json_encode($result),
                'status' => (!$result['status'] ? 'FAILED' : 'SUCCESS')
            ]);
        }

        $this->validation_lib->respondSuccess([
            'message' => 'Berhasil menjalankan fungsi'
        ]);
    }

    // Penarikan RDL pada Daftar Pengembalian Dana
    public function withdrawing_refund_loan_get()
    {
        // Load model
        $this->load->model('Daftar_pengembalian_dana/Daftar_pengembalian_dana_model', 'daftar_pengembalian_dana_model');
        $this->load->model("transfer_rdl/Transfer_rdl_model", 'transfer_rdl_model');

        // Get current date
        $date = date('Y-m-d');

        // Get data from database
        $data = $this->daftar_pengembalian_dana_model->get_data_not_yet_withdrawn($date);

        // Withdrawing funds each item
        foreach ($data as $item) {
            // Get info loan
            $lender_code = $item->register_code;

            // Post withdrawal RDL
            $result = $this->daftar_pengembalian_dana_model->refund_rdl($date, $lender_code);

            // Insert history
            $this->db->insert('tb_history_funding_refund_dana_cron', [
                'lender_code' => $lender_code,
                'loan_date' => $date,
                'response' => json_encode($result),
                'status' => (!empty($result['status']) && ($result['status'] || $result['status'] == true || $result['status'] == 1) ? 'SUCCESS' : 'FAILED')
            ]);
        }

        $this->validation_lib->respondSuccess([
            'message' => 'Berhasil menjalankan fungsi'
        ]);
    }

    // Process Transfer ke RDL atau Escrow pada Akun RDL Borrower
    public function account_rdl_borrower_process_transfer_get()
    {
        // Load model
        $this->load->model('Account_rdl_borrower/Account_rdl_borrower_model', 'acc_borrower_model');

        $data = $this->acc_borrower_model->get_data_for_transfer();

        foreach ($data as $item) {
            $register_code = $item->register_code;
            $type_transfer = $item->type_transfer;

            if ($type_transfer == 'TO_ESCROW') {
                $response = $this->acc_borrower_model->transfer_to_escrow($register_code);
            } else if ($type_transfer == 'TO_RDL') {
                $response = $this->acc_borrower_model->transfer_to_rdl($register_code);
            }

            $this->db->insert('tb_history_process_transfer_acc_rdl_borrower_cron', [
                'register_code' => $register_code,
                'response' => json_encode($response),
                'status' => $response['status'] == true ? 'SUCCESS' : 'FAILED',
                'type_transfer' => $type_transfer
            ]);
        }

        $this->validation_lib->respondSuccess([
            'message' => 'Berhasil menjalankan fungsi'
        ]);
    }

    public function insert_log_post()
    {
        $post = $this->post();

        $log_name = $post['log_name'];
        $log_data = $post['log_data'];
        $log_status = $post['log_status'];

        $this->lib_log->create_log($log_name, $log_data, $log_status);

        return true;
    }

    public function bni_register_investor_post()
    {
        $post = $this->post();

        $result = $this->bni_investor->register_investor($post);

        $this->validation_lib->respondSuccess($result);
    }

    public function bni_register_investor_account_post()
    {
        $post = $this->post();

        $result = $this->bni_investor->register_investor_account($post);

        $this->validation_lib->respondSuccess($result);
    }

    public function bni_token_get()
    {
        $result = $this->bni_investor->get_token();

        $this->validation_lib->respondSuccess($result);
    }

    public function bni_payment_using_transfer_post()
    {
        $post = $this->post();

        $result = $this->bni_investor->payment_transfer($post);

        $this->validation_lib->respondSuccess($result);
    }

    public function bni_inquiry_account_info_post()
    {
        $post = $this->post();

        $result = $this->bni_investor->inquiry_account_info($post);

        $this->validation_lib->respondSuccess($result);
    }

    public function bni_inquiry_account_balance_post()
    {
        $post = $this->post();
        $account_number = $post['accountNumber'];

        $result = $this->bni_investor->inquiry_account_balance($account_number);

        $this->validation_lib->respondSuccess($result);
    }

    public function bni_inquiry_account_history_post()
    {
        $post = $this->post();
        $account_number = $post['accountNumber'];

        $result = $this->bni_investor->inquiry_account_history($account_number);

        $this->validation_lib->respondSuccess($result);
    }

    public function bni_do_payment_post()
    {
        $post = $this->post();
        $result = $this->model->deposit_rdl($post);
        $this->transfer_rdl_model->save_amountRdl($post['register_code']);
        $this->validation_lib->respondSuccess($result);
    }

    public function bni_do_payment_disbursement_post()
    {
        $post = $this->post();

        $result = $this->bni_investor->do_payment($post);
    }

    public function bni_get_balance_payment_post()
    {
        $post = $this->post();
        $account_number = $post['account_number'];

        $result = $this->bni_investor->get_balance_payment($account_number);

        $this->validation_lib->respondSuccess($result);
    }

    public function bni_get_in_house_inquiry_payment_post()
    {
        $post = $this->post();
        $account_number = $post['account_number'];

        $result = $this->bni_investor->get_in_house_inquiry_payment($account_number);

        $this->validation_lib->respondSuccess($result);
    }

    public function cron_topup_rdl_get()
    {
        $result = $this->model->cron_topup_rdl();
        if ($result['success']) {
            $this->validation_lib->respondSuccess($result);
        } else {
            $this->validation_lib->respondError($result);
        }
    }

    public function sendEmail_get()
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
        $this->email->from($config['smtp_user']);
        $this->email->to('rinaaprll76@gmail.com');
        $this->email->subject("TEST EMAIL SUPPORT 2");
        $this->email->message('CEK');
        $isKirim = $this->email->send();
        if ($isKirim) {
            $this->validation_lib->respondSuccess('Email berhasil dikirim');
        } else {
            $this->validation_lib->respondError('E-mail gagal dikirim!');
        }
    }

    public function proses_withdrawal_post()
    {
        $post = $this->post();

        $return = ['message' => "tidak ada data yang di proses"];

        if (isset($post['confirm']) && $post['confirm'] == '1') {
            $get_data = $this->deposit_rdl_lender_model->data_withdrawal();
            $tmp_result = [];
            $total_success = 0;
            $total_failed = 0;
            foreach ($get_data as $key => $value) {
                $data_withdrawal = $this->model->proses_withdrawal($value);
                array_push($tmp_result, $data_withdrawal);

                if ($data_withdrawal['status'] == "failed") {
                    $total_failed++;
                } else {
                    $total_success++;
                }
            }
            $return = [
                'message' => count($get_data) . " Sudah diproses",
                'status' => 'success',
                'data' => $tmp_result,
                'total_success' => $total_success,
                'total_failed' => $total_failed
            ];
        }

        $this->validation_lib->respondSuccess($return);
    }

    public function proses_deposit_post()
    {
        $post = $this->post();

        $return = ['message' => "tidak ada data yang di proses"];

        if (isset($post['confirm']) && $post['confirm'] == '1') {
            $get_data = $this->deposit_rdl_lender_model->data_deposit();
            $tmp_result = [];
            $total_success = 0;
            $total_failed = 0;
            foreach ($get_data as $key => $value) {
                $data_deposit = $this->model->proses_deposit($value);
                array_push($tmp_result, $data_deposit);

                if ($data_deposit['status'] == "failed") {
                    $total_failed++;
                } else {
                    $total_success++;
                }
            }

            $return = [
                'message' => count($get_data) . " Sudah diproses",
                'status' => 'success',
                'data' => $tmp_result,
                'total_success' => $total_success,
                'total_failed' => $total_failed
            ];
        }

        $this->validation_lib->respondSuccess($return);
    }
}
