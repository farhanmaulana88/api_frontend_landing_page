<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/userguide3/general/urls.html
	 */
	public function index()
	{
		$this->load->view('welcome_message');
	}

	public function content_type_base64()
	{
		$encode_base64 = base64_encode(file_get_contents('uploads/Fintech/ktp_borrower/Gusiarto_1650957209.png'));

		$decode = base64_decode($encode_base64);
		$f = finfo_open();
		$mime_type = finfo_buffer($f, $decode, FILEINFO_MIME_TYPE);
		var_dump($mime_type);exit;
	}
}
