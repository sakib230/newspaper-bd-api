<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class My_Controller extends CI_Controller {

    function __construct() {
        parent::__construct();
        error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
        if ($this->input->post('access_token', true) !== ACCESS_TOKEN) {
            echo "Unauthorized User...!";
            exit();
        }
    }

    function errorMsg($msg) {
        return json_encode(array("is_success" => "N","warning" => $msg));
    }

    function newsPaperPageSource($url = null) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
        ));
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        if ($error) {
            return false;
        }
        return $response;
    }

}
