<?php
namespace App\MongoService;

class ModelResponse{
    public $isCompleted;
    public $message;
    public $data;
    public function __construct($isCompleted=true, $message="",$data=null){
        $this->isCompleted = $isCompleted;
        $this->message = $message;
        $this->data = $data;
    }
}
