<?php
namespace App\Http\Controllers;

class ControllerResponse{
    public $message = '';
    public $data = [];
    public $statusCode = 0;
    public $status="success";

    public function __construct($status="success", $statusCode = 0, $message = '', $data = []){
        $this->message = $message;
        $this->data = $data;
        $this->statusCode = $statusCode;
        $this->status = $status;
    }
    public static function Success($data = [], $message = '') {
        $instance = new ControllerResponse("success",200,$message,$data);
        return $instance;
    }
    public static function Error( $message = '',$statusCode = 0, $data = []) {
        $instance = new ControllerResponse("error",$statusCode,$message,$data);
        return $instance;
    }

}