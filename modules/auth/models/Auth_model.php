<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Auth_model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
  }

  private function create_client_google()
  {
    $client = new Google\Client();
    $client->setApplicationName($this->config->item('app_name'));
    $client->setClientId($this->config->item('google_client_id'));
    $client->setClientSecret($this->config->item('google_secret_key'));
    $client->setRedirectUri($this->config->item('google_redirect_uri'));
    $client->addScope("https://www.googleapis.com/auth/userinfo.email");
    $client->addScope("https://www.googleapis.com/auth/userinfo.profile");
    $client->addScope("https://www.googleapis.com/auth/user.phonenumbers.read");
    $client->addScope("openid");

    return $client;
  }

  public function get_google_oauth_url()
  {
    $client = $this->create_client_google();

    return $client->createAuthUrl();
  }

  public function get_google_oauth_account($code)
  {
    $client = $this->create_client_google();
    $client->setIncludeGrantedScopes(true);
    $client->setAccessType('offline'); 

    $token = $client->fetchAccessTokenWithAuthCode($code);
    if (!empty($token["error"]))
      return [
        'status' => false,
        'message' => $token["error"],
      ];

    $client->setAccessToken($token['access_token']);
    $google_service = new Google_Service_Oauth2($client);
    $data = $google_service->userinfo->get();

    return [
      'status' => true,
      'payload' => [
        'id' => $data->id,
        'picture' => $data->picture,
        'name' => $data->name,
        'given_name' => $data->givenName,
        'family_name' => $data->familyName,
        'email' => $data->email,
        'gender' => $data->gender,
      ]
    ];
  }
}
