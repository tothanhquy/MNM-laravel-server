<?php
namespace App\MongoService;

use MongoDB\Client;
use MongoDB\BSON\ObjectID;

class Database{
    protected $db=null;
    public function __construct(){
        try{
            $client = new Client(env('MONGODB_URI'));
            $this->db = $client->selectDatabase("test");
        }catch(Exception $err){
            echo $err;
        };
    }
    public static function isObjectId($id){
        return (bool)preg_match('/^[0-9a-f]{24}$/i', $id);
    }
}
