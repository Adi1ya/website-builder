<?php
defined('BASEPATH') or exit('No direct script access allowed');

class UploadController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Access verificaion.
        $token = $this->jwt->verifyToken();
        if(isset($token) && !empty($token)) {
            $this->userId = $token->data->userId;
        } else {
            $response = array(
                'status' => false,
                'message' => 'Unauthorized Access'
            );
            $this->toolbox->response($response);
            die();
        }

    }

    public function uploadFile()
    {
        echo $this->userId;
        echo "Authorized";
        die;
    }



}