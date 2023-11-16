<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Bni_investor
{

	protected $CI;

	public function __construct()
	{
		$this->CI = &get_instance();
		// $this->CI->load->library('PHPRequests');
		$this->CI->load->config('bni');
		$this->db = $this->CI->load->database();
		$this->db1 = $this->CI->load->database('v1', true);


		$this->set = [
			'companyId' => $this->CI->config->item('bni_p2p_client_id'),
			//nama aplikasi
			'url' => $this->CI->config->item('bni_p2p_url'),
			'sandbox' => $this->CI->config->item('bni_p2p_sandbox'),
			'development' => $this->CI->config->item('bni_p2p_development'),
			'testing' => $this->CI->config->item('bni_p2p_testing'),
			'username' => $this->CI->config->item('bni_p2p_username'),
			'password' => $this->CI->config->item('bni_p2p_password'),
			'api_key' => $this->CI->config->item('bni_p2p_x_api_key'),
			'api_secret' => $this->CI->config->item('bni_p2p_secret_key'),
			'account_debet' => $this->CI->config->item('bni_p2p_account_debet'),
		];
	}

	private function init_curl($url, $method, $body, $headers = [])
	{
		$auth = base64_encode($this->set['username'] . ":" . $this->set['password']);
		$headers[] = 'Authorization: Basic ' . $auth;

		$curl = curl_init();
		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => $method,
				CURLOPT_POSTFIELDS => $body,
				CURLOPT_HTTPHEADER => $headers
			)
		);

		$response = curl_exec($curl);
		$error = curl_error($curl);

		$data = json_decode($response);
		curl_close($curl);
		return $data;
	}



	public function register_investor($data)
	{
		// Operasi ini harus dipanggil untuk membuat dan / atau mendaftarkan investor baru. File identifikasi pelanggan baru (cifNumber) akan dibuat di BNI.
		$token = $this->get_token();

		$options['request'] = [
			'header' => [
				'companyId' => $this->set['companyId'],
				//Yes
				'parentCompanyId' => '',
				//no
				'requestUuid' => $this->gen_uuid(),
				//Yes --
			],
		];

		foreach ($data as $key => $value) {
			switch ($key) {
				case "addressStreet":
					$value = substr($value, 0, 40);
					break;
				case "mobilePhone1":
					$value = substr($value, 0, 4);
					break;
				case "mobilePhone2":
					$value = substr($value, 0, 8);
					break;
			}

			$options['request'][$key] = $value;
		}

		$options['request']['header']['signature'] = $this->signature($options);

		$data = $this->init_curl(
			$this->set['url'] . '/p2pl/register/investor?access_token=' . $token,
			'POST',
			json_encode($options),
			array(
				'Content-Type: application/json',
				'X-API-KEY: ' . $this->set['api_key'],
			)
		);

		$this->history_hit_bni('register_investor', $options, $data);

		return $data;
		// “cifNumber” from responnse should be kept as a key of an investor/lender. It will be used to create RDL account.
	}

	public function register_investor_account($data)
	{
		// Operasi ini harus dipanggil untuk membuat dan / atau mendaftarkan akun investor baru. Nomor akun baru (nomor rekening) akan dibuat di BNI, akun ini akan disebut RDL (Rekening Dana Lender).
		$token = $this->get_token();
		$options['request'] = [
			'header' => [
				'companyId' => $this->set['companyId'],
				//Yes
				'parentCompanyId' => '',
				//no
				'requestUuid' => $this->gen_uuid(),
				//Yes --
			],
		];

		foreach ($data as $key => $d)
			$options['request'][$key] = $d;

		$options['request']['header']['signature'] = $this->signature($options);

		$data = $this->init_curl(
			$this->set['url'] . '/p2pl/register/investor/account?access_token=' . $token,
			'POST',
			json_encode($options),
			array(
				'Content-Type: application/json',
				'X-API-Key: ' . $this->set['api_key'],
			)
		);

		$this->history_hit_bni('register_investor_account', $options, $data);

		return $data;

		// $token = $this->get_token();
		// $url = $this->set['url'] . '/p2pl/register/investor/account?access_token=' . $token;

		// $headers = array(
		// 	'Content-Type: application/json',
		// 	'X-API-Key: ' . $this->set['api_key'],
		// );

		// $options['request'] = [
		// 	'header' => [
		// 		'companyId'			=> $this->set['companyId'], //Yes
		// 		'parentCompanyId'	=> '', //no
		// 		'requestUuid'		=> $this->gen_uuid(), //Yes --
		// 	],
		// ];

		// foreach ($data as $key => $d) {
		// 	$options['request'][$key] = $d;
		// }

		// $options['request']['header']['signature'] = $this->signature($options);
		// $response = Requests::post($url, $headers, json_encode($options));
		// $result = json_decode($response->body);
		// $result->request = json_decode(json_encode($options), FALSE);
		// return $result;
	}

	public function inquiry_account_info($data)
	{
		// Operasi ini digunakan untuk mendapatkan informasi dasar tentang nomor akun perusahaan P2P atau investor. Nomor akun tidak perlu didaftarkan di BNI API, karena operasi ini tidak akan mengembalikan info saldo.
		$token = $this->get_token();
		$url = $this->set['url'] . '/p2pl/inquiry/account/info?access_token=' . $token;

		$options['request'] = [
			'header' => [
				'companyId' => $this->set['companyId'],
				//Yes
				'parentCompanyId' => '',
				//no
				'requestUuid' => $this->gen_uuid(),
				//Yes --
			],
		];

		foreach ($data as $key => $d) {
			$options['request'][$key] = $d;
		}

		$options['request']['header']['signature'] = $this->signature($options);

		$data = $this->init_curl(
			$url,
			'POST',
			json_encode($options),
			array(
				'Content-Type: application/json',
				'X-API-KEY: ' . $this->set['api_key'],
			)
		);

		$this->history_hit_bni('inquiry_account_info', $options, $data);

		return $data;
	}

	public function inquiry_account_balance($accountNumber)
	{
		$data = [];
		if (!empty($accountNumber)) {
			// Operasi ini digunakan untuk mendapatkan informasi saldo dari perusahaan P2P atau nomor akun investor. Nomor akun harus terdaftar di BNI API.
			$token = $this->get_token();

			$options['request'] = [
				'header' => [
					'companyId' => $this->set['companyId'],
					//Yes
					'parentCompanyId' => '',
					//no
					'requestUuid' => $this->gen_uuid(),
					//Yes --
				],
			];


			$options['request']['accountNumber'] = $accountNumber;

			$options['request']['header']['signature'] = $this->signature($options);

			$data = $this->init_curl(
				$this->set['url'] . '/p2pl/inquiry/account/balance?access_token=' . $token,
				'POST',
				json_encode($options),
				array(
					'Content-Type: application/json',
					'X-API-KEY: ' . $this->set['api_key'],
				)
			);

			$this->history_hit_bni('inquiry_account_balance', $options, $data);

			if (isset($data->response->responseCode) && $data->response->responseCode == 0001) {
				return $data->response;
			}
		}
		return $data;
	}

	public function inquiry_account_history($account_number)
	{
		$data = [];

		if (!empty($account_number)) {

			$token = $this->get_token();

			$options['request'] = [
				'header' => [
					'companyId' => $this->set['companyId'],
					//Yes
					'parentCompanyId' => '',
					//no
					'requestUuid' => $this->gen_uuid(),
					//Yes --
				],
			];

			$options['request']['accountNumber'] = $account_number;
			$options['request']['header']['signature'] = $this->signature($options);

			$data = $this->init_curl(
				$this->set['url'] . '/p2pl/inquiry/account/history?access_token=' . $token,
				'POST',
				json_encode($options),
				array(
					'Content-Type: application/json',
					'X-API-KEY: ' . $this->set['api_key'],
				)
			);

			$this->history_hit_bni('inquiry_account_history', $options, $data);

			if (isset($data->response->responseCode) && $data->response->responseCode == 0001) {
				return $data->response;
			}
		}
		return $data;
	}

	public function payment_transfer($data)
	{
		//Operasi ini digunakan untuk pembayaran atau transfer dari nomor rekening perusahaan P2P atau investor ke nomor rekening BNI lainnya. Nomor akun harus terdaftar di BNI API.
		$token = $this->get_token();

		$options['request'] = [
			'header' => [
				'companyId' => $this->set['companyId'],
				//Yes
				'parentCompanyId' => '',
				//no
				'requestUuid' => $this->gen_uuid(),
				//Yes --
			],
		];

		foreach ($data as $key => $d) {
			$options['request'][$key] = $d;
		}

		$options['request']['header']['signature'] = $this->signature($options);

		// echo '<pre>';
		// echo json_encode($options);
		// echo '<br>';
		// die;
		$data = $this->init_curl(
			$this->set['url'] . '/p2pl/payment/transfer?access_token=' . $token,
			'POST',
			json_encode($options),
			array(
				'Content-Type: application/json',
				'X-API-Key: ' . $this->set['api_key'],
			)
		);

		$this->history_hit_bni('payment_transfer', $options, $data);

		$response = [
			"response" => $data,
			"requestUuid" => $options['request']['header']["requestUuid"],
		];

		$result_api = $response['response'];
		if (!empty($result_api->Response->parameters)) {
			if (@$result_api->Response->parameters->responseCode != '0001') {

			} else {
				$this->ledger_transfer_deposit($options);
				$this->update_amount_rdl($options['request']['accountNumber']);
				$this->transfer_log($options, 'Return', $data);
				$this->transaction_account_history('payment_transfer', $options['request']['accountNumber'], $options['request']['amount']);
			}
		} else {
			if (@$result_api->response->responseCode != '0001') {

			} else {
				$this->ledger_transfer_deposit($options);
				$this->update_amount_rdl($options['request']['accountNumber']);
				$this->transfer_log($options, 'Return', $data);
				$this->transaction_account_history('payment_transfer', $options['request']['accountNumber'], $options['request']['amount']);
			}
		}

		return $response;
	}

	public function payment_status($data)
	{
		//Operasi ini digunakan untuk mendapatkan status pembayaran dari setiap operasi pembayaran / transaksi.

		$token = $this->get_token();
		$url = $this->set['url'] . '/p2pl/inquiry/payment/status?access_token=' . $token;

		$headers = array(
			'Content-Type' => 'application/json',
			'X-API-Key' => $this->set['api_key'],
		);

		$options['request'] = [
			'header' => [
				'companyId' => $this->set['companyId'],
				//Yes
				'parentCompanyId' => '',
				//no
				'requestUuid' => $this->gen_uuid(),
				//Yes --
			],
		];

		foreach ($data as $key => $d) {
			$options['request'][$key] = $d;
		}

		$options['request']['header']['signature'] = $this->signature($options);
		$response = Requests::post($url, $headers, json_encode($options));
		$result = json_decode($response->body);
		$result->request = json_decode(json_encode($options), FALSE);
		return $result;
	}

	public function payment_clearing($data)
	{
		//Operasi ini digunakan untuk pembayaran atau transfer dari perusahaan P2P atau nomor rekening investor ke nomor rekening lain (BNI atau bank lain) dengan menggunakan transaksi kliring (SKN-BI). Transfer kredit diizinkan diproses hanya dalam jumlah yang kurang atau sama dengan Rp1 miliar. Nomor akun harus terdaftar di BNI API. Peningkatan pada P2PL / RDL untuk mengakomodasi cut off time transaksi kliring pada pukul 15.00 WIB

		$token = $this->get_token();
		$url = $this->set['url'] . '/p2pl/payment/clearing?access_token=' . $token;

		$headers = array(
			'Content-Type' => 'application/json',
			'X-API-Key' => $this->set['api_key'],
		);

		$options['request'] = [
			'header' => [
				'companyId' => $this->set['companyId'],
				//Yes
				'parentCompanyId' => '',
				//no
				'requestUuid' => $this->gen_uuid(),
				//Yes --
			],
		];

		foreach ($data as $key => $d) {
			$options['request'][$key] = $d;
		}

		$options['request']['header']['signature'] = $this->signature($options);
		$response = Requests::post($url, $headers, json_encode($options));
		$result = json_decode($response->body);
		$result->request = json_decode(json_encode($options), FALSE);
		return $result;
	}

	public function payment_rtgs($data)
	{
		//Operasi ini digunakan untuk pembayaran atau transfer dari perusahaan P2P atau nomor rekening investor ke nomor rekening lain (BNI atau bank lain) dengan menggunakan transaksi RTGS (Sistem BI-RTGS). Transfer kredit diizinkan diproses dalam RTGS hanya dalam jumlah lebih dari Rp1 miliar. Nomor akun harus terdaftar di BNI API.

		$token = $this->get_token();
		$url = $this->set['url'] . '/p2pl/payment/rtgs?access_token=' . $token;

		$headers = array(
			'Content-Type' => 'application/json',
			'X-API-Key' => $this->set['api_key'],
		);

		$options['request'] = [
			'header' => [
				'companyId' => $this->set['companyId'],
				//Yes
				'parentCompanyId' => '',
				//no
				'requestUuid' => $this->gen_uuid(),
				//Yes --
			],
		];

		foreach ($data as $key => $d) {
			$options['request'][$key] = $d;
		}

		$options['request']['header']['signature'] = $this->signature($options);
		$response = Requests::post($url, $headers, json_encode($options));
		$result = json_decode($response->body);
		$result->request = json_decode(json_encode($options), FALSE);
		return $result;
	}

	public function inquiry_interbank($data)
	{
		//Operasi ini digunakan untuk pembayaran atau transfer dari nomor rekening perusahaan P2P atau investor ke nomor rekening penerima (bank lain) dengan menggunakan transaksi online. Batas transaksi harian maksimum adalah Rp25 juta per akun. Nomor akun harus terdaftar di BNI API.

		$token = $this->get_token();
		$url = $this->set['url'] . '/p2pl/inquiry/interbank/account?access_token=' . $token;

		$headers = array(
			'Content-Type' => 'application/json',
			'X-API-Key' => $this->set['api_key'],
		);

		$options['request'] = [
			'header' => [
				'companyId' => $this->set['companyId'],
				//Yes
				'parentCompanyId' => '',
				//no
				'requestUuid' => $this->gen_uuid(),
				//Yes --
			],
		];

		foreach ($data as $key => $d) {
			$options['request'][$key] = $d;
		}

		$options['request']['header']['signature'] = $this->signature($options);
		$response = Requests::post($url, $headers, json_encode($options));
		$result = json_decode($response->body);
		$result->request = json_decode(json_encode($options), FALSE);
		return $result;
	}

	public function payment_interbank($data)
	{
		//Operasi ini digunakan untuk mendapatkan informasi dasar tentang nomor rekening penerima (bank lain) sebelum menekan operasi “Pembayaran Menggunakan Antar Bank”. Nomor akun harus terdaftar di BNI API..
		$token = $this->get_token();
		$url = $this->set['url'] . '/p2pl/payment/interbank?access_token=' . $token;

		$headers = array(
			'Content-Type' => 'application/json',
			'X-API-Key' => $this->set['api_key'],
		);

		$options['request'] = [
			'header' => [
				'companyId' => $this->set['companyId'],
				//Yes
				'parentCompanyId' => '',
				//no
				'requestUuid' => $this->gen_uuid(),
				//Yes --
			],
		];

		foreach ($data as $key => $d) {
			$options['request'][$key] = $d;
		}

		$options['request']['header']['signature'] = $this->signature($options);
		$response = Requests::post($url, $headers, json_encode($options));
		$result = json_decode($response->body);
		$result->request = json_decode(json_encode($options), FALSE);
		return $result;
	}

	public function do_payment($post)
	{
		$data = [];
		$token = $this->get_token();
		if (!empty($post)) {
			$options = [
				"clientId" => "IDBNI" . base64_encode($this->set['companyId']),
				'customerReferenceNumber' => date('Ymdhis') . rand(100, 999),
				'valueDate' => date('Ymdhis'),
				'valueCurrency' => "IDR",
				'paymentMethod' => (isset($post['paymentMethod']) ? "" : "0"),
				'remark' => "?",
				"debitAccountNo" => $this->set['account_debet'],
				"chargingModelId" => "OUR",
			];

			if (isset($post['paymentMethod'])) {
				unset($options['paymentMethod']);
			}

			foreach ($post as $key => $d) {
				$options[$key] = $d;
			}

			$options['signature'] = $this->signature($options);

			$data = $this->init_curl(
				$this->set['url'] . '/H2H/v2/dopayment?access_token=' . $token,
				'POST',
				json_encode($options),
				array(
					'Content-Type: application/json',
					'X-API-KEY: ' . $this->set['api_key'],
				)
			);

			$this->history_hit_bni('do_payment', $options, $data);

			if (isset($data->doPaymentResponse->parameters->responseCode) && $data->doPaymentResponse->parameters->responseCode != "0001") {

			} else {
				$this->ledger_transfer_withdrawal($options);
				$this->update_amount_rdl($options['creditAccountNo']);
				$this->transfer_log($options, 'Deposit', $data);
				$this->transaction_account_history('do_payment', $options['creditAccountNo'], $options['valueAmount']);
			}

		}

		return $data;
	}

	public function transfer_log($options, $type, $data)
	{
		if ($type == 'Deposit') {
			$check = $this->db1->get_where('tb_fintech_register', ['account_number_rdl' => $options['creditAccountNo']])->row();
			if (!empty($check)) {
				$lender_deposit = [
					"register_code" => $check->register_code,
					"amount" => $options['valueAmount'],
					"status_trf" => "Has Transferred",
					"created_at" => date("Y-m-d h:i:s"),
					"created_by" => $check->register_code
				];

				$this->db1->insert('bni_rdl_lender_deposit', $lender_deposit);

				$log_deposit = [
					"register_code" => $check->register_code,
					"response" => json_encode($data),
					"created_at" => date("Y-m-d h:i:s"),
					"api_function" => null,
					"status" => null,
					"request_uuid" => null,
					"response_uuid" => null,
				];
				$this->db1->insert('bni_rdl_status_deposit_lender_log', $log_deposit);
			}
		} else {
			$check = $this->db1->get_where('tb_fintech_register', ['account_number_rdl' => $options['request']['accountNumber']])->row();
			if (!empty($check)) {
				$data = [
					"register_code" => $check->register_code,
					"amount" => $options['request']['amount'],
					"status_trf" => "Has Transferred",
					"request_uuid" => $options['request']['header']["requestUuid"],
					"created_at" => date("Y-m-d h:i:s"),
					"created_by" => $check->register_code
				];

				$this->db1->insert('bni_rdl_lender_return', $data);

				$arrLog = [
					"register_code" => $check->register_code,
					// "status" => "",
					"response" => json_encode($data),
					"request_uuid" => $options['request']['header']["requestUuid"],
					// "response_uuid" => !empty($result_api->Response->parameters->responseUuid) ? $result_api->Response->parameters->responseUuid : $result_api->response->responseUuid,
				];
				$this->db1->insert('bni_rdl_status_return_lender_log', $arrLog);
			}
		}
	}

	public function get_balance_payment($account_number)
	{
		$data = [];
		$token = $this->get_token();

		$options = [
			"clientId" => "IDBNI" . base64_encode($this->set['companyId']),
			"accountNo" => $account_number,
		];
		$options['signature'] = $this->signature($options);

		$data = $this->init_curl(
			$this->set['url'] . '/H2H/v2/getbalance?access_token=' . $token,
			'POST',
			json_encode($options),
			array(
				'Content-Type: application/json',
				'X-API-KEY: ' . $this->set['api_key'],
			)
		);

		$this->history_hit_bni('get_balance_payment', $options, $data);

		return $data;
	}

	public function get_in_house_inquiry_payment($account_number)
	{
		$data = [];
		$token = $this->get_token();

		$options = [
			"clientId" => "IDBNI" . base64_encode($this->set['companyId']),
			"accountNo" => $account_number,
		];
		$options['signature'] = $this->signature($options);

		$data = $this->init_curl(
			$this->set['url'] . '/H2H/v2/getinhouseinquiry?access_token=' . $token,
			'POST',
			json_encode($options),
			array(
				'Content-Type: application/json',
				'X-API-KEY: ' . $this->set['api_key'],
			)
		);

		$this->history_hit_bni('get_in_house_inquiry_payment', $options, $data);

		return $data;
	}

	public function inter_bank_inquiry($post)
	{

		$data = [];
		$token = $this->get_token();
		if (!empty($post)) {
			$options = [
				"clientId" => "IDBNI" . base64_encode($this->set['companyId']),
				'customerReferenceNumber' => date('Ymdhis') . rand(100, 999),
				'accountNum' => $this->set['account_debet']
			];

			foreach ($post as $key => $d) {
				$options[$key] = $d;
			}

			$options['signature'] = $this->signature($options);

			$data = $this->init_curl(
				$this->set['url'] . '/H2H/v2/getinterbankinquiry?access_token=' . $token,
				'POST',
				json_encode($options),
				array(
					'Content-Type: application/json',
					'X-API-KEY: ' . $this->set['api_key'],
				)
			);

			$this->history_hit_bni('inter_bank_inquiry', $options, $data);

		}

		return $data;

	}

	public function inter_bank_payment($post)
	{

		$data = [];
		$token = $this->get_token();
		if (!empty($post)) {
			$options = [
				"clientId" => "IDBNI" . base64_encode($this->set['companyId']),
				'customerReferenceNumber' => date('Ymdhis') . rand(100, 999),
				'accountNum' => $this->set['account_debet']
			];

			foreach ($post as $key => $d) {
				$options[$key] = $d;
			}

			$options['signature'] = $this->signature($options);

			$data = $this->init_curl(
				$this->set['url'] . '/H2H/v2/getinterbankpayment?access_token=' . $token,
				'POST',
				json_encode($options),
				array(
					'Content-Type: application/json',
					'X-API-KEY: ' . $this->set['api_key'],
				)
			);

			$this->history_hit_bni('inter_bank_payment', $options, $data);

		}

		return $data;

	}

	public function in_house_inquiry($post)
	{

		$data = [];
		$token = $this->get_token();
		if (!empty($post)) {
			$options = [
				"clientId" => "IDBNI" . base64_encode($this->set['companyId']),
			];

			foreach ($post as $key => $d) {
				$options[$key] = $d;
			}

			$options['signature'] = $this->signature($options);

			$data = $this->init_curl(
				$this->set['url'] . '/H2H/v2/getinhouseinquiry?access_token=' . $token,
				'POST',
				json_encode($options),
				array(
					'Content-Type: application/json',
					'X-API-KEY: ' . $this->set['api_key'],
				)
			);

			$this->history_hit_bni('in_house_inquiry', $options, $data);

		}

		return $data;

	}

	public function get_token()
	{
		$data = $this->init_curl(
			$this->set['url'] . '/api/oauth/token',
			'POST',
			'grant_type=client_credentials',
			array(
				'Content-Type: application/x-www-form-urlencoded',
			)
		);

		return $data->access_token;
	}

	public function ledger_transfer_withdrawal($options)
	{

		$check = $this->db1->get_where('tb_fintech_register', ['account_number_rdl' => $options['creditAccountNo']])->row();
		if (!empty($check)) {
			$lender_code = $check->register_code;
			$amount = $options['valueAmount'];

			$jurnal_rules = [
				[
					'id_param_coa_e' => 182,
					'description' => 'Withdrawal ke RDL - ' . $lender_code,
				],
				[
					'id_param_coa_e' => 162,
					'description' => 'Withdrawal ke RDL - ' . $lender_code,
				]
			];
			// INIT GL
			$idMaxgl = $this->journal_coder();
			$noUrutgl = (int) substr(@$idMaxgl->maxID, 0, 5);
			$noBulan = (string) substr(@$idMaxgl->maxID, 6, 11);
			$noUrutgl++;
			$date_now = DATE("Y-m-d");
			$date_codegl = date('m/y');
			$first_date = date('Y-m-01', strtotime($date_now));

			if ($date_now == $first_date && $noBulan != $date_codegl) {
				$newIDgl = sprintf("00001", $noUrutgl) . '/' . $date_codegl;
			} else {
				$newIDgl = sprintf("%05s", $noUrutgl) . '/' . $date_codegl;
			}

			foreach ($jurnal_rules as $key => $value) {
				$data_journal = array(
					'journal_no' => $newIDgl,
					'id_param_coa_e' => $value['id_param_coa_e'],
					'journal_entry' => date('Y-m-d H:i:s'),
					'journal_date' => date('Y-m-d H:i:s'),
					'cashflow_code_status' => '0',
					'journal_status' => '0',
					'journal_description' => $value['description']
				);

				if ($value['id_param_coa_e'] == 182) {
					$data_journal['journal_debit'] = $amount;
					$data_journal['journal_kredit'] = 0;
				} elseif ($value['id_param_coa_e'] == 162) {
					$data_journal['journal_debit'] = 0;
					$data_journal['journal_kredit'] = $amount;
				}
				$this->db1->insert('tb_general_ledger_journal', $data_journal);
			}
		}
	}

	public function ledger_transfer_deposit($options)
	{
		$check = $this->db1->get_where('tb_fintech_register', ['account_number_rdl' => $options['request']['accountNumber']])->row();
		if (!empty($check)) {
			$amount = $options['request']['amount'];
			$lender_code = $check->register_code;

			$jurnal_rules = [
				[
					'id_param_coa_e' => 162,
					'description' => 'Deposit dari RDL - ' . $lender_code,
				],
				[
					'id_param_coa_e' => 182,
					'description' => 'Deposit dari RDL - ' . $lender_code,
				]
			];
			// INIT GL
			$idMaxgl = $this->journal_coder();
			$noUrutgl = (int) substr(@$idMaxgl->maxID, 0, 5);
			$noBulan = (string) substr(@$idMaxgl->maxID, 6, 11);
			$noUrutgl++;
			$date_now = DATE("Y-m-d");
			$date_codegl = date('m/y');
			$first_date = date('Y-m-01', strtotime($date_now));

			if ($date_now == $first_date && $noBulan != $date_codegl) {
				$newIDgl = sprintf("00001", $noUrutgl) . '/' . $date_codegl;
			} else {
				$newIDgl = sprintf("%05s", $noUrutgl) . '/' . $date_codegl;
			}

			foreach ($jurnal_rules as $key => $value) {
				$data_journal = array(
					'journal_no' => $newIDgl,
					'id_param_coa_e' => $value['id_param_coa_e'],
					'journal_entry' => date('Y-m-d H:i:s'),
					'journal_date' => date('Y-m-d H:i:s'),
					'cashflow_code_status' => '0',
					'journal_status' => '0',
					'journal_description' => $value['description']
				);

				if ($value['id_param_coa_e'] == 162) {
					$data_journal['journal_debit'] = $amount;
					$data_journal['journal_kredit'] = 0;
				} elseif ($value['id_param_coa_e'] == 182) {
					$data_journal['journal_debit'] = 0;
					$data_journal['journal_kredit'] = $amount;
				}
				$this->db1->insert('tb_general_ledger_journal', $data_journal);
			}
		}
	}

	public function update_amount_rdl($account_number)
	{
		$check = $this->db1->get_where('tb_fintech_register', ['account_number_rdl' => $account_number])->row();
		if (!empty($check)) {
			$balance = $this->inquiry_account_balance($account_number);
			if ($balance->responseCode == "0001") {
				if ($check->register_status == "Lender") {
					$this->db1->set(['amount_rdl' => $balance->accountBalance]);
					$this->db1->where('register_code', $check->register_code);
					$this->db1->update('tb_fintech_lender_fund');
				} else {
					$this->db1->set(['amount_rdl' => $balance->accountBalance]);
					$this->db1->where('register_code', $check->register_code);
					$this->db1->update('tb_fintech_borrower_fund');
				}
			}
		}
	}

	private function journal_coder()
	{
		return $this->db1->query("SELECT journal_no as maxID
        FROM tb_general_ledger_journal WHERE journal_no ORDER BY id_journal DESC LIMIT 1")->row();
	}



	public function history_hit_bni($function, $request, $response)
	{
		$data = [
			'function' => $function,
			'request' => json_encode($request),
			'response' => json_encode($response)
		];
		$this->db1->insert('tb_history_hit_proccess_rdl', $data);
	}

	public function signature($payload)
	{
		// Create token header as a JSON string
		$header = JSON_encode(['typ' => 'JWT', 'alg' => 'HS256']);

		// Create token payload as a JSON string
		// $payload = JSON_encode(array('clientId' => "IDBNI".base64_encode($this->client_id), 'accountNo' => $this->account_debet));
		$payload = JSON_encode($payload);

		// Encode Header to Base64Url String
		$base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

		// Encode Payload to Base64Url String
		$base64UrlPayload = str_replace(
			['+', '/', '='],
			['-', '_', ''],
			base64_encode($payload)
		);

		// Create Signature Hash
		$signature = hash_hmac(
			'sha256',
			$base64UrlHeader . "." . $base64UrlPayload,
			$this->set['api_secret'],
			true
		);

		// Encode Signature to Base64Url String
		$base64UrlSignature = str_replace(
			['+', '/', '='],
			['-', '_', ''],
			base64_encode($signature)
		);

		// Create JWT
		$jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
		return $jwt;
	}

	function gen_uuid()
	{
		$uuid = strtoupper(bin2hex(openssl_random_pseudo_bytes(16)));
		return substr($uuid, 0, 16);
	}

	public function transaction_account_history($from, $account_number, $amount)
	{
		$check = $this->db1->get_where('tb_fintech_register', ['account_number_rdl' => $account_number])->row();

		if ($check->register_status == "Lender") {
			$check_last_balance = $this->db1->get_where('tb_fintech_lender_fund', ['register_code' => $check->register_code])->row();
		} else {
			$check_last_balance = $this->db1->get_where('tb_fintech_borrower_fund', ['register_code' => $check->register_code])->row();
		}
		if (!empty($check)) {
			$history = [
				"date" => date("Y-m-d H:i:s"),
				"register_code" => $check->register_code,
				"account_number" => $account_number,
				"description" => $from == "payment_transfer" ? "Transfer dari RDL ".$check->register_status." ke Escrow" : "Transfer dari Escrow ke RDL ".$check->register_status,
				"type" => $from == "payment_transfer" ? "D" : "K",
				"amount" => $amount,
				"balance" => $check_last_balance->amount_rdl
			];
			$this->db1->insert('transaction_account_history', $history);
		}


	}
}