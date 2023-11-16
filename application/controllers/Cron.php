<?php
defined('BASEPATH') or exit('No direct script access allowed');
use chriskacerguis\RestServer\RestController;
use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Class Example
 *
 * @property Cron_model cron_model
 * @property Authentication authentication
 * @property Validation_lib validation_lib
 */
class Cron extends RestController
{
    function __construct()
    {
        parent::__construct();
        $this->load->model(Cron_model::class, 'cron_model');
    }


    public function reminder_facility_fee_get($days, $kelipatan)
    {
        $entities = $this->cron_model->reminderFacilityFee($days, $kelipatan);
        $this->validation_lib->respondSuccess($entities);
    }
}
