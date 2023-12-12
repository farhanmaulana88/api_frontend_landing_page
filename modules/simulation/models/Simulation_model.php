<?php

use LDAP\Result;

defined('BASEPATH') or exit('No direct script access allowed');

class Simulation_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        // $this->db1 = $this->load->database('v1', true);
    }

    public function calculate_loan($data)
    {
        $kom_bor = 4.75;
        $fee_pg = 6500;
        $kom_len = 3;
        $insurance = 0.75;
        $admin_fee = 50000;
        $adm_cost = 0;
        $vat_ppn = 11;

        $input_rate = $this->rate_loan($data['grade']);

        $rate = $input_rate / 100;
        $bunga = ($data['amount'] * $rate) * ($data['tenor'] / 12);

        $t_pokok = $data['amount'] / $data['tenor'];
        $t_bunga = $bunga / $data['tenor'];

        $pokok_bunga = (float) $t_pokok + (float) $t_bunga;
        $montly = $pokok_bunga;

        $t_pokok2 = ceil(($data['amount'] / $data['tenor']) / 100) * 100;
        $t_bunga2 = round($t_bunga);

        $montly2 = (float) $t_pokok2 + (float) $t_bunga2 + (float) $fee_pg;

        $t_kom = ceil(($data['amount'] * (float) $kom_bor) / 100);

        $t_insurance = ceil(($data['amount'] * (float) $insurance) / 100);

        $ppn_fee_pg = ceil((float) $fee_pg / (((float) $vat_ppn + 100) / 100));
        $fee_pg_and_ppn = round($ppn_fee_pg * (float) $vat_ppn / 100);
        $ppn_insurance = (float) $t_insurance * (float) $vat_ppn / 100;
        $ppn_kom = (float) $t_kom * (float) $vat_ppn / 100;
        $ppn_admin_fee = (float) $admin_fee * (float) $vat_ppn / 100;
        $ppn_adm_cost = (float) $adm_cost * (float) $vat_ppn / 100;

        $tax = $fee_pg_and_ppn + $ppn_insurance + $ppn_kom + $ppn_admin_fee + $ppn_adm_cost;
        $loan_receive_view = round($data['amount'] - ((float) $t_insurance + (float) $t_kom + (float) $ppn_fee_pg + (float) $admin_fee + (float) $adm_cost + $tax));

        return [
            'amount' => $data['amount'],
            'tenor' => $data['tenor'],
            'grade' => $data['grade'],
            'rate' => $input_rate,
            'insurance' => $t_insurance,
            'tax' => $tax,
            'fee_pg' => $ppn_fee_pg + $fee_pg_and_ppn,
            'admin_fee' => $admin_fee,
            'admin' => $adm_cost,
            'principal' => $t_pokok2,
            'interest' => $t_bunga2,
            'monthly_payment' => $montly2,
            'receive' => $loan_receive_view,
        ];
    }

    public function calculate_lending($data)
    {
        $kom_bor = 4.75;
        $fee_pg = 6500;
        $kom_len = 3;
        $insurance = 0.75;
        $admin_fee = 50000;
        $adm_cost = 0;
        $vat_ppn = 11;

        $input_rate = $this->rate_loan($data['grade']);

        $rate = $input_rate / 100;
        $bunga = ($data['amount'] * $rate) / 12;

        $t_pokok = $data['amount'] / $data['tenor'];
        $t_bunga = $bunga / $data['tenor'];

        $pokok_bunga = (float) $t_pokok + (float) $t_bunga;
        $amount_payment = round($pokok_bunga);

        $t_kom = round(($t_pokok + $bunga) * (3 / 100));

        $payment = $amount_payment - $t_kom;
        $return_receive = round($payment);
        $all_return_receive = $return_receive * $data['tenor'];

        $ppn = $t_kom * (float) $vat_ppn / 100;
        $bunga_lender = $bunga - $t_kom;
        $bunga_lender_ppn = $bunga_lender - $ppn;
        $pph = round($bunga_lender_ppn * (15 / 100));
        $bunga_diterima = round($bunga_lender_ppn - $pph);
        $pokok_bunga2 = round((float) $t_pokok + (float) $bunga_diterima);
        $return_receive2 = round($pokok_bunga2);
        $all_return_receive2 = round($return_receive2 * $data['tenor']);

        return [
            'amount' => $data['amount'],
            'tenor' => $data['tenor'],
            'grade' => $data['grade'],
            'rate' => $input_rate,
            'amount_payment' => $pokok_bunga2 + $t_kom,
            'monthly_fee' => $t_kom,
            'monthly_return' => $return_receive2,
            'total_return' => $all_return_receive2,
        ];
    }

    
    private function rate_loan($grade) {
        $rate = [
            'A+' => 15.00,
            'A' => 18.00,
            'B+' => 21.00,
            'B' => 24.00,
            'C+' => 27.00,
            'C' => 30.00,
            'D+' => 33.00,
            'D' => 36.00,
        ];

        return $rate[$grade];
    }
}


        