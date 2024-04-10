<?php
namespace App\MongoModel;
use App\MongoModel\Database;
use App\MongoModel\ModelResponse;
use MongoDB\BSON\ObjectID;

// const PriceSchema = new Schema({
//     size: { type: String, required: true },
//     price: { type: Number, required: true },
// });

// name: { type: String, required: true },
// prices: { type: [PriceSchema], required: true },
// image: { type: String, required: true },
// rating: { type: Number, required: true },
// description: { type: String, required: true },
// included: { type: String, required: true },
// createdAt: { type: Date, default: Date.now },
// updatedAt: { type: Date, default: Date.now },
// deletedAt: { type: Date, default: null },
// categoryId: { type: Number, required: true },

class ProductModel{
    public $name = "";
    public $prices = [];
    public $image = "";
    public $rating = "";
    public $description = "";
    public $included = "";
    public $createdAt = "";
    public $updatedAt = "";
    public $deletedAt = 0;
    public $categoryId = null;
    // public $comments = [];

    public static function getPrice($size, $price){
        return [
            "size" => $size,
            "price" => $price
        ];
    }
    // public static function getComment($author, $content){
    //     return [
    //         "author" => $author,
    //         "content" => $content
    //     ];
    // }

    public function __construct($name, $prices, $image, $rating, $description, $included, $categoryId){
        $this->name = $name;
        $this->prices = $prices;
        $this->image = $image;
        $this->rating = $rating;
        $this->description = $description;
        $this->included = $included;
        $this->categoryId = $categoryId;
        $this->createdAt = time();
        $this->updatedAt = time();
    }
    public function getMongoObject(){
        return [
            "name" => $this->name,
            "prices" => $this->prices,
            "image" => $this->image,
            "rating" => $this->rating,
            "description" => $this->description,
            "included" => $this->included,
            "createdAt" => $this->createdAt,
            "updatedAt" => $this->updatedAt,
            "deletedAt" => $this->deletedAt,
            "categoryId" => $this->categoryId
        ];
    }
    public function checkValidations(){
        if($this->name == null 
        || $this->image == null 
        || $this->rating == null 
        || $this->prices == null 
        || $this->description == null 
        || $this->categoryId == null
        || $this->included){
            return false;
        }
        return true;
    }
}