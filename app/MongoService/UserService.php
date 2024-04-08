<?php
namespace App\MongoService;
use App\MongoService\Database;
use App\MongoService\ModelResponse;
use MongoDB\BSON\ObjectID;
use App\MongoModel\UserModel;


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

class UserService extends Database{
    private $collection;
    public function __construct(){
        parent::__construct();
        $this->collection = $this->db->selectCollection("user");
    }
    private function isDuplicateUserName($username){
        $cursor = $this->collection->find(['username' => $username]);
        if(iterator_count($cursor) > 0){
            return true;
        }
        return false;
    }
    private function isDuplicateSdt($sdt){
        $cursor = $this->collection->find(['sdt' => $sdt]);
        if(iterator_count($cursor) > 0){
            return true;
        }
        return false;
    }
    public function insert(UserModel $user){
        try{
            if(!$user->checkValidations()){
                return new ModelResponse(false,"invalid");
            }else if(self::isDuplicateUserName($user->username)){
                return new ModelResponse(false,"duplicate username");
            }else if(self::isDuplicateSdt($user->sdt)){
                return new ModelResponse(false,"duplicate sdt");
            }else{
                $result = $this->collection->insertOne($user->getMongoObject());
                return new ModelResponse(true,"success",["id"=>$result->getInsertedId()]);
            }
        }catch(Exception $e){
            return new ModelResponse(false, $e->getMessage());
        }
    }
    public function update($id, UserModel $user){
        try{
            if(!$this->isObjectId($id)||!$user->checkValidations()){
                return new ModelResponse(false,"invalid");
            }else{
                $result = $this->collection->updateOne(
                    ['_id' => new ObjectId($id)],
                    ['$set' => $user->getMongoObject()]
                );
                return new ModelResponse(true,"success");
            }
        }catch(Exception $e){
            return new ModelResponse(false, $e->getMessage());
        }
    }
    public function updateFields($id, $setFields){
        try{
            if(!$this->isObjectId($id)){
                return new ModelResponse(false,"id invalid");
            }else{
                $result = $this->collection->updateOne(
                    ['_id' => new ObjectId($id)],
                    ['$set' => $setFields]
                );
                return new ModelResponse(true,"success");
            }
        }catch(Exception $e){
            return new ModelResponse(false, $e->getMessage());
        }
    }
    public function delete($id){
        try{
            if(!$this->isObjectId($id)){
                return new ModelResponse(false,"id invalid");
            }else{
                $result = $this->collection->deleteOne(['_id' => new ObjectId($id)]);
                return new ModelResponse(true,"success");
            }
        }catch(Exception $e){
            return new ModelResponse(false, $e->getMessage());
        }
    }
    public function getById($id){
        try{
            if(!$this->isObjectId($id)){
                return new ModelResponse(false,"id invalid");
            }else{
                $cursor = $this->collection->find(['_id' => new ObjectID($id)]);
                $users = iterator_to_array($cursor);
                if(count($users) > 0){
                    return new ModelResponse(true,"success",$users[0]);
                }else{
                    return new ModelResponse(false,"not found");
                }
            }
        }catch(Exception $e){
            return new ModelResponse(false, $e->getMessage());
        }
    }
    public function getByUsername($username) {
        try {
            $cursor = $this->collection->find([
                'username' => $username
            ]);
            $users = iterator_to_array($cursor);
            if(count($users) > 0){
                return new ModelResponse(true,"success",$users[0]);
            }else{
                return new ModelResponse(false,"not found");
            }
        } catch (Exception $e) {
            return new ModelResponse(false, $e->getMessage());
        }
    }
    public function getByIsAdmin($isAdmin) {
        try {
            $cursor = $this->collection->find(["isAdmin" => $isAdmin]);
            $users = iterator_to_array($cursor);
            if(count($users) > 0){
                return new ModelResponse(true,"success",$users);
            }else{
                return new ModelResponse(false,"not found");
            }
        } catch (Exception $e) {
            return new ModelResponse(false, $e->getMessage());
        }
    }
}