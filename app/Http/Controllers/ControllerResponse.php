<?php
namespace App\Http\Controllers;

class ControllerResponse{
    public $message = '';
    public $data = [];
    public $statusCode = 0;

    public function __construct($statusCode = 0, $message = '', $data = []){
        $this->message = $message;
        $this->data = $data;
        $this->statusCode = $statusCode;
    }
    public static function Success($message = '', $data = []) {
        $instance = new ControllerResponse(0,$message,$data);
        return $instance;
    }
    public static function Error($statusCode = 0, $message = '', $data = []) {
        $instance = new ControllerResponse($statusCode,$message,$data);
        return $instance;
    }

}