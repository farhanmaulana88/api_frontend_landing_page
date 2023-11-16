<?php

use LDAP\Result;

defined('BASEPATH') or exit('No direct script access allowed');

class Home_model extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
    // $this->db1 = $this->load->database('v1', true);
  }

  public function carousell()
  {
    $data = [
      [
        'title' => 'Carousel 1 PT Satustop Finansial Solusi',
        'text' => 'Carousel 1 - Nikmati kemudahan pinjaman di Sanders',
        'url' => './assets/slider/image1.jpg',
        'button' => [
          'url' => 'https://sanders.co.id',
          'text' => 'Carousell 1'
        ],
      ],
      [
        'title' => 'Carousel 2 PT Satustop Finansial Solusi',
        'text' => 'Carousel 2 - Nikmati kemudahan pinjaman di Sanders',
        'url' => './assets/slider/image2.jpg',
        'button' => [
          'url' => 'https://sanders.co.id',
          'text' => 'Carousell 2'
        ],
      ],
      [
        'title' => 'Carousel 3 PT Satustop Finansial Solusi',
        'text' => 'Carousel 3 - Nikmati kemudahan pinjaman di Sanders',
        'url' => './assets/slider/image3.jpg',
        'button' => [
          'url' => 'https://sanders.co.id',
          'text' => 'Carousell 3'
        ],
      ],
    ];
    return $data;
  }

  public function statistic($group)
  {
    if ($group == 1) {
      return $this->statistic_group1();
    }elseif ($group == 2) {
      return $this->statistic_group2();
    }else{
      return 'Data tidak ditemukan';
    }
  }

  public function statistic_group1()
  {
    $data = [
      [
        'name' => 'Data Group 1 - Statistik 1',
        'value' => '19.60%',
        'desc' => 'Data Group 1 - Deskripsi statistik 1',
        'icon' => ' ./assets/slider/icon1.svg',
      ],
      [
        'name' => 'Data Group 1 - Statistik 2',
        'value' => '640.44M',
        'desc' => 'Data Group 1 - Deskripsi statistik 2',
        'icon' => ' ./assets/slider/icon2.svg',
      ],
      [
        'name' => 'Data Group 1 - Statistik 3',
        'value' => '28.340',
        'desc' => 'Data Group 1 - Deskripsi statistik 3',
        'icon' => ' ./assets/slider/icon3.svg',
      ],
      [
        'name' => 'Data Group 1 - Statistik 4',
        'value' => '10',
        'desc' => 'Data Group 1 - Deskripsi statistik 4',
        'icon' => ' ./assets/slider/icon4.svg',
      ],
      [
        'name' => 'Data Group 1 - Statistik 5',
        'value' => '3 Hari',
        'desc' => 'Data Group 1 - Deskripsi statistik 5',
        'icon' => ' ./assets/slider/icon5.svg',
      ],
      [
        'name' => 'Data Group 1 - Statistik 6',
        'value' => '214.31M',
        'desc' => 'Data Group 1 - Deskripsi statistik 6',
        'icon' => ' ./assets/slider/icon6.svg',
      ],
      [
        'name' => 'Data Group 1 - Statistik 7',
        'value' => '163',
        'desc' => 'Data Group 1 - Deskripsi statistik 7',
        'icon' => ' ./assets/slider/icon7.svg',
      ],
      [
        'name' => 'Data Group 1 - Statistik 8',
        'value' => '5.445',
        'desc' => 'Data Group 1 - Deskripsi statistik 8',
        'icon' => ' ./assets/slider/icon8.svg',
      ],
      [
        'name' => 'Data Group 1 - Statistik 9',
        'value' => '1.395',
        'desc' => 'Data Group 1 - Deskripsi statistik 9',
        'icon' => ' ./assets/slider/icon9.svg',
      ],
      [
        'name' => 'Data Group 1 - Statistik 10',
        'value' => '1.29 Triliun',
        'desc' => 'Data Group 1 - Deskripsi statistik 10',
        'icon' => ' ./assets/slider/icon10.svg',
      ],
    ];
    return $data;
  }

  public function statistic_group2()
  {
    $data = [
      [
        'name' => 'Data Group 2 - Statistik 1',
        'value' => '10.60%',
        'desc' => 'Data Group 2 - Deskripsi statistik 1',
        'icon' => ' ./assets/slider/icon11.svg',
      ],
      [
        'name' => 'Data Group 2 - Statistik 2',
        'value' => '240.44M',
        'desc' => 'Data Group 2 - Deskripsi statistik 2',
        'icon' => ' ./assets/slider/icon12.svg',
      ],
      [
        'name' => 'Data Group 2 - Statistik 3',
        'value' => '10.340',
        'desc' => 'Data Group 2 - Deskripsi statistik 3',
        'icon' => ' ./assets/slider/icon13.svg',
      ],
      [
        'name' => 'Data Group 2 - Statistik 4',
        'value' => '20',
        'desc' => 'Data Group 2 - Deskripsi statistik 4',
        'icon' => ' ./assets/slider/icon14.svg',
      ],
      [
        'name' => 'Data Group 2 - Statistik 5',
        'value' => '10 Hari',
        'desc' => 'Data Group 2 - Deskripsi statistik 5',
        'icon' => ' ./assets/slider/icon15.svg',
      ],
      [
        'name' => 'Data Group 2 - Statistik 6',
        'value' => '100.31M',
        'desc' => 'Data Group 2 - Deskripsi statistik 6',
        'icon' => ' ./assets/slider/icon16.svg',
      ],
      [
        'name' => 'Data Group 2 - Statistik 7',
        'value' => '200',
        'desc' => 'Data Group 2 - Deskripsi statistik 7',
        'icon' => ' ./assets/slider/icon17.svg',
      ],
      [
        'name' => 'Data Group 2 - Statistik 8',
        'value' => '3.000',
        'desc' => 'Data Group 2 - Deskripsi statistik 8',
        'icon' => ' ./assets/slider/icon18.svg',
      ],
      [
        'name' => 'Data Group 2 - Statistik 9',
        'value' => '2.000',
        'desc' => 'Data Group 2 - Deskripsi statistik 9',
        'icon' => ' ./assets/slider/icon19.svg',
      ],
      [
        'name' => 'Data Group 2 - Statistik 10',
        'value' => '5 Triliun',
        'desc' => 'Data Group 2 - Deskripsi statistik 10',
        'icon' => ' ./assets/slider/icon20.svg',
      ],
    ];
    return $data;
  }
}
