<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Common_model extends CI_Model
{
    /**
     * Account_rdl_lender_model constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->db1 = $this->load->database('v1', true);
    }

    public function deposit_rdl($post)
    {
        $register_code = $post['register_code'];
        $amount = $post['amount'];
        $created_by = $post['created_by'];
        $app = $post['app_source'];

        $lender_code = substr($register_code, 0, 3);
        if ($lender_code != 'LEN') {
            $this->validation_lib->respondError('lender dan penerima harus sesuai dengan kategori yang dipilih.');
        }

        // Cek Lender
        $Check_lender = $this->get_data_bank($register_code);
        if (empty($Check_lender)) {
            $this->validation_lib->respondError('Lender tidak ditemukan.');
        }
        // Get Body request
        $get_request = $this->request_do_payment($register_code, $amount);

        $this->db1->trans_start();

        //Hit RDL BNI api
        $response_bni = $this->bni_investor->do_payment($get_request);
        if ($response_bni->doPaymentResponse->parameters->responseCode != "001") {
            $this->validation_lib->respondError($response_bni->doPaymentResponse->parameters->responseMessage);
        }
        // dd($response_bni);

        // $lender_deposit = [
        //     "register_code" => $register_code,
        //     "amount" => $amount,
        //     "status_trf" => "Has Transferred",
        //     "created_at" => date("Y-m-d h:i:s"),
        //     "created_by" => $app == "panel" ? $created_by : $register_code
        // ];
        //
        // $this->db1->insert('bni_rdl_lender_deposit', $lender_deposit);
        //
        // $log_deposit = [
        //     "register_code" => $register_code,
        //     "response" => json_encode($response_bni->doPaymentResponse),
        //     "created_at" => date("Y-m-d h:i:s"),
        //     "api_function" => null,
        //     "status" => null,
        //     "request_uuid" => null,
        //     "response_uuid" => null,
        // ];
        // $this->db1->insert('bni_rdl_status_deposit_lender_log', $log_deposit);

        $this->db1->trans_complete();
        if ($this->db1->trans_status() === FALSE) {
            return false;
        }
        if ($app == "panel") {
            $this->lib_log->create_log('Transaksi Deposit ke RDL', $post, 'Create', ['bni_rdl_lender_deposit' => null, 'bni_rdl_status_deposit_lender_log' => null]);
        }

        return true;
    }

    function request_do_payment($register_code, $amount)
    {
        $options = [];
        $data_bank = $this->get_data_bank($register_code);
        if (!empty($data_bank)) {
            $data = isset($data_bank) ? $data_bank : '';

            $options = array(
                'creditAccountNo' => $data->account_number_rdl,
                'valueAmount' => $amount,
                'beneficiaryEmailAddress' => $data->register_email,
                'beneficiaryName' => '',
                'beneficiaryAddress1' => '',
                'beneficiaryAddress2' => '',
                'destinationBankCode' => '',
            );
        }
        return $options;
    }

    public function get_data_bank($register_code)
    {
        $data_bank = $this->db1->select(
            "
      a.register_code,
      a.register_email,
      a.account_number_rdl,
      a.cif_number_rdl
      "
        )
            ->from("tb_fintech_register a")
            ->where("a.register_code", $register_code)
            ->get()->row();
        return $data_bank;
    }

    public function cron_topup_rdl()
    {
        $antrian = $this->db1->get_where('tb_fintech_rdl_topup_queue', ['status' => 0])->result();
        if (empty($antrian)) {
            $return = [
                'success' => false,
                'message' => 'Antrian topup RDL tidak ditemukan.'
            ];
            return $return;
        }

        $url = "common/bni_do_payment/";
        foreach ($antrian as $key => $value) {
            $data_do_payment = [
                "register_code" => $value->register_code,
                "amount" => $value->clean_with_fee,
                "created_by" => $value->register_code,
                "app_source" => 'Web Sanders'
            ];

            $request_rdl_account = $this->deposit_rdl($data_do_payment);
            // $this->lib_log->create_log('BNI Do Payment', $data_do_payment, 'Create', ['result' => json_decode($request_rdl_account)]);
            $this->db1->where('id', $value->id)->update('tb_fintech_rdl_topup_queue', ['status' => 1]);
        }

        $return = [
            'success' => true,
            'message' => 'Done top up RDL VA'
        ];
        return $return;
    }




    public function proses_withdrawal($post)
    {
        $data_with = (array) $post;

        $register_code = $post->register_code;
        $amount = $post->lender_fund - $post->amount_rdl;

        $data_with['amount_transfer'] = $amount;


        $get_account_bank = $this->request_do_payment($register_code, $amount);
        $do_payment = $this->bni_investor->do_payment($get_account_bank);
        $log = [
            'register_code' => $register_code,
            'amount' => $amount,
            'account_number_rdl' => $post->account_number_rdl,
            'created_at' => date('Y-m-d h:i:s'),
        ];

        if (@$do_payment->doPaymentResponse->parameters->responseCode = "0001") {
            $log['status'] = "success";
            $log['response'] = json_encode($do_payment);
            $this->db1->insert('history_cron_withdrawal_lender', $log);

            // buat return
            $data_with['status'] = 'success';
            $data_with['response'] = 'Transfer dari Escrow ke RDL (LENDER) Success';
        } else {
            $log['status'] = "failed";
            $log['response'] = json_encode($do_payment);

            $this->db1->insert('history_cron_withdrawal_lender', $log);
            $data_with['status'] = 'failed';
            $data_with['response'] = $do_payment->doPaymentResponse->parameters->responseMessage;
        }
        return $data_with;

    }



    public function proses_deposit($post)
    {
        $data_depo = (array) $post;

        $register_code = $post->register_code;
        $account_number_rdl = $post->account_number_rdl;
        $amount_wallet = $post->lender_fund;
        $amount_rdl = $post->amount_rdl;
        $amount = $amount_rdl - $amount_wallet;

        $data_depo['amount_transfer'] = $amount;

        $postBody = [
            "accountNumber" => $account_number_rdl,
            // RDl sanders
            "beneficiaryAccountNumber" => $this->config->item("bni_p2p_account_debet"),
            // lender tujuan
            "currency" => "IDR",
            // Default curency
            "amount" => $amount,
            // ammount input
            "remark" => "Transfer dari RDL ke Escrow (LENDER)"
        ];

        $return_api_bni = $this->bni_investor->payment_transfer($postBody);

        $result_api = $return_api_bni['response'];

        $log = [
            'register_code' => $register_code,
            'amount' => $amount,
            'account_number_rdl' => $post->account_number_rdl,
            'created_at' => date('Y-m-d h:i:s'),
        ];

        if (!empty($result_api->Response->parameters)) {
            if (@$result_api->Response->parameters->responseCode != '0001') {
                //simpan ke log
                $log['status'] = "failed";
                $log['response'] = json_encode($result_api);

                // buat return
                $data_depo['status'] = 'failed';
                $data_depo['response'] = $result_api->Response->parameters->responseMessage;

            } else {
                //simpan ke log
                $log['status'] = "success";
                $log['response'] = json_encode($result_api);

                // buat return
                $data_depo['status'] = 'success';
                $data_depo['response'] = 'Transfer dari RDL ke Escrow (LENDER) Success';

            }
        } else {
            if (@$result_api->response->responseCode != '0001') {
                //simpan ke log
                $log['status'] = "failed";
                $log['response'] = json_encode($result_api);

                // buat return
                $data_depo['status'] = 'failed';
                $data_depo['response'] = $result_api->Response->parameters->responseMessage;

            } else {
                //simpan ke log
                $log['status'] = "success";
                $log['response'] = json_encode($result_api);

                // buat return
                $data_depo['status'] = 'success';
                $data_depo['response'] = 'Transfer dari RDL ke Escrow (LENDER) Success';
            }
        }
        $this->db1->insert('history_cron_deposit_lender', $log);
        return $data_depo;

    }

    public function ret_false($msg)
    {
        $ret = [
            'status' => false,
            'message' => $msg
        ];

        return $ret;
    }

    public function ret_true($msg)
    {
        $ret = [
            'status' => true,
            'message' => 'Transfer dari RDL ke Escrow (LENDER) Success'
        ];

        return $ret;
    }

    public function pembulatanAngka($angka)
    {
        // Membagi angka menjadi bagian bulat dan desimal
        $bagianBulat = floor($angka);
        $desimal = $angka - $bagianBulat;

        // Membulatkan ke atas jika desimal lebih dari 0.5
        if ($desimal > 0.5) {
            return $bagianBulat + 1;
        } else {
            return $bagianBulat;
        }
    }

}