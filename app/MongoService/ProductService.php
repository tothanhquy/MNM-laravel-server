<?php
namespace App\MongoService;
use App\MongoService\Database;
use App\MongoService\ModelResponse;
use MongoDB\BSON\ObjectID;
use App\MongoModel\ProductModel;

class ProductService extends Database{
    private $collection;
    public function __construct(){
        parent::__construct();
        $this->collection = $this->db->selectCollection("product");
    }
    private function isDuplicateName($name){
        $cursor = $this->collection->find(['name' => $name]);
        if(iterator_count($cursor) > 0){
            return true;
        }
        return false;
    }
    public function insert(ProductModel $product){
        try{
            if(!$product->checkValidations()){
                return new ModelResponse(false,"invalid");
            }else if(self::isDuplicateName($product->name)){
                return new ModelResponse(false,"duplicate username");
            }else {
                $result = $this->collection->insertOne($product->getMongoObject());
                return new ModelResponse(true,"success",["id"=>$result->getInsertedId()]);
            }
        }catch(Exception $e){
            return new ModelResponse(false, $e->getMessage());
        }
    }
    public function update($id, ProductModel $product){
        try{
            if(!$this->isObjectId($id)||!$product->checkValidations()){
                return new ModelResponse(false,"invalid");
            }else{
                $result = $this->collection->updateOne(
                    ['_id' => new ObjectId($id)],
                    ['$set' => $product->getMongoObject()]
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
                $product = iterator_to_array($cursor);
                if(count($product) > 0){
                    return new ModelResponse(true,"success",$product[0]);
                }else{
                    return new ModelResponse(false,"not found");
                }
            }
        }catch(Exception $e){
            return new ModelResponse(false, $e->getMessage());
        }
    }
    public function getAll($isDelete=false) {
        try {
            $cursor;
            if($isDelete){
                $cursor = $this->collection->find(["deletedAt"=>['$gt' => 0]]);
            }else{
                $cursor = $this->collection->find(["deletedAt"=>0]);
            }
            $products = iterator_to_array($cursor);
            return new ModelResponse(true,"success",$products);
        } catch (Exception $e) {
            return new ModelResponse(false, $e->getMessage());
        }
    }
}