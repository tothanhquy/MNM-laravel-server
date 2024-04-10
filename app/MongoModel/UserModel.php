<?php
namespace App\MongoModel;
use App\MongoModel\Database;
use App\MongoModel\ModelResponse;
use MongoDB\BSON\ObjectID;


// $collection = $database->selectCollection('mycollection');

// // Insert a document
// $insertResult = $collection->insertOne([
//     'name' => 'John Doe',
//     'age' => 30,
//     'email' => 'john@example.com'
// ]);

// // Find documents
// $cursor = $collection->find(['age' => ['$gt' => 25]]);
// foreach ($cursor as $document) {
//     var_dump($document);
// }

// // Update a document
// $updateResult = $collection->updateOne(
//     ['name' => 'John Doe'],
//     ['$set' => ['age' => 31]]
// );

// // Delete a document
// $deleteResult = $collection->deleteOne(['name' => 'John Doe']);


// username: { type: String, required: true, unique: true },
// password: { type: String, required: true },
// isAdmin: {
//     type: Boolean,
//     default: false,
// },
// sdt: { type: String, required: true, unique: true },
// date: { type: Date, default: Date.now },
// deletedAt: { type: Date, default: null },
// age: { type: Number, required: true },
// gender: { type: String, required: true },

class UserModel{
    public $username =null;
    public $password=null;
    public $isAdmin=false;
    public $sdt=null;
    public $date = 0;
    public $deletedAt = null;
    public $age = null;
    public $gender = null;
    public $id=null;
    public $refreshTokens="";
    public $role="user";
  
    public function __construct($username, $password, $isAdmin, $sdt, $age, $gender){
        $this->username = $username;
        $this->password = $password;
        $this->isAdmin = $isAdmin;
        $this->date = time();
        $this->sdt = $sdt;
        $this->age = $age;
        $this->gender = $gender;
    }
    public function getMongoObject(){
        return [
            'username' => $this->username,
            'password' => $this->password,
            'isAdmin' => $this->isAdmin,
            'sdt' => $this->sdt,
            'date' => $this->date,
            'deletedAt' => $this->deletedAt,
            'age' => $this->age,
            'gender' => $this->gender,
            'id' => $this->id,
           'refreshTokens' => $this->refreshTokens,
            'role' => $this->role
        ];
    }
    public function checkValidations(){
        if($this->username == null || $this->password == null || $this->sdt == null || $this->age == null || $this->gender == null){
            return false;
        }
        return true;
    }
}