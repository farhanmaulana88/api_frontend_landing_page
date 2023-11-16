<?php
defined('BASEPATH') or exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;
use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Class Example
 *
 * @property User_model user_model
 * @property Authentication authentication
 * @property Validation_lib validation_lib
 */
class Auth extends RestController
{
    function __construct()
    {
        parent::__construct();
        $this->load->model(User_model::class, 'user_model');
    }

    public function login_post()
    {
        $post = $this->post();
        $user_id = $post['user_id'];

        $user = $this->db->get_where('main_users', ['id' => $user_id])->row();

        // JWT secret key
        $key = $this->authentication->getConfigToken()['secretkey'];

        // JWT Expired
        $exp = time() + 86400;

        // JWT payload
        $payload = [
            "iss" => 'apprestservice',
            "aud" => 'pengguna',
            'data' => $user,
            'iat' => time(),
            'exp' => $exp, // Token expiration time
        ];
        $token = JWT::encode($payload, $key, 'HS256');
        
        $output = [
            'status' => 200,
            'message' => 'Berhasil login',
            "token" => $token,
            "expireAt" => $exp
        ];
        $this->response($output, 200);
    }

    public function getToken_post()
    {
        $exp = time() + 86400;
        $grant_type = $this->post('grant_type');
        if (is_null($grant_type)) {
            $this->validation_lib->respondError('Anda tidak memiliki akses!');
        }
        if ($grant_type != 'client_credentials') {
            $this->validation_lib->respondError('Anda tidak memiliki akses!');
        }
        $username = $this->input->server('PHP_AUTH_USER');
        $password = $this->input->server('PHP_AUTH_PW');
        $user = $this->user_model->find($username);
        if (!$user || !$password) {
            $this->validation_lib->respondError('otentikasi login salah!');
        }
        $password_hash = sha1($password);

        if ($user->password !== $password_hash) {
            $this->validation_lib->respondError('otentikasi login salah!');
        }

        $token = array(
            "iss" => 'apprestservice',
            "aud" => 'pengguna',
            "iat" => time(),
            //            "nbf" => time() + 10,
            "exp" => $exp,
            //            "data" => array(
            //                "username" => $username,
            //                "password" => $password
            //            )
        );

        $jwt = JWT::encode($token, $this->authentication->getConfigToken()['secretkey'], 'HS256');
        $updateToken['token'] = $jwt;
        $edit = $this->user_model->edit($username, $updateToken);
        if (!$edit) {
            $this->validation_lib->respondError('Gagal update token!');
        }

        $this->user_model->update_sanders_token($jwt);
        $output = [
            'status' => 200,
            'message' => 'Berhasil login',
            "token" => $jwt,
            "expireAt" => $token['exp']
        ];
        $this->response($output, 200);
    }

    public function check_token_get()
    {
        $data['is_valid'] = $this->authentication->checktoken();
        $this->validation_lib->respondSuccess($data);
    }

    public function check_existing_token_get()
    {
        $data = $this->user_model->getExistingToken();
        $this->validation_lib->respondSuccess($data);
    }
}
