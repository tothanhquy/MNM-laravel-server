<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MongoDB\Client;
use App\MongoModel\ProductModel;
use App\Http\Controllers\JwtAuth;
use App\MongoService\ProductService;
use App\Http\Middleware\RedisManager;

class ProductController extends Controller{
    public function PostProduct (Request  $request) {    
        try {
            $name = $request->input('name');
            $prices = $request->input('prices');
            $image = $request->input('image');
            $rating = $request->input('rating');
            $description = $request->input('description');
            $included = $request->input('included');
            $categoryId = $request->input('categoryId');

            if (!is_array($prices) || array_reduce($prices, function($carry, $item) {
                return $carry || empty($item['size']) || empty($item['price']);
            }, false)) {
                return response()->json(['error' => 'Prices array is invalid'], 400);
            }
    
            $newProduct = new ProductModel($name, $prices, $image, $rating, $description, $included, $categoryId);
            $productService = new ProductService();

            $queryResult = $productService->insert($newProduct);

            if($queryResult->isCompleted===false){
                throw new Exception($queryResult->message);
            }

            $dataQuery = $queryResult->data;

            return response()->json($dataQuery , 201);

        } catch (\Exception $e) {
            return response()->json(
                [
                    "success"=>false,
                    "message"=>$e->getMessage(),
                ], 500
            );
        }
    }
    public function destroy (Request  $request) {    
        try {
            $id = $request->input('id');
            $formattedId = trim(str_replace(':', '', $id));

            $productService = new ProductService();
            $queryResult = $productService->delete($id);
            if($queryResult->isCompleted===true){
                return response()->json(
                    [
                        "success"=>true,
                        "message"=>'Product restored successfully',
                    ], 200
                );
            }else throw new Exception("");

        } catch (\Exception $e) {
            return response()->json(
                [
                    "success"=>false,
                    "message"=>'Error restoring product',
                ], 500
            );
        }
    }
    public function GetProductdelete (Request  $request) {    
        try {
            $productService = new ProductService();
            $queryResult = $productService->getAll(true);
            if($queryResult->isCompleted===false){
                throw new Exception("");
            }
            $dataQuery = $queryResult->data;

            echo json_encode($dataQuery);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success"=>false,
                    "message"=>'Error fetching deleted products',
                ], 500
            );
        }
    }
    public function RestoreProduct (Request  $request) {    
        try {
            $id = $request->input('id');
            $formattedId = trim(str_replace(':', '', $id));

            $productService = new ProductService();
            $queryResult = $productService->getById($id);
            if($queryResult->isCompleted===false){
                return response()->json(
                    [
                        "success"=>false,
                        "message"=>'Product not found',
                    ], 404
                );
            }

            $dataQuery = $queryResult->data;

            $updateFields = ["deletedAt"=>0];

            $queryResult = $productService->updateFields($id,$updateFields);
            if($queryResult->isCompleted===true){
                return response()->json(
                    [
                        "success"=>true,
                        "message"=>'Product restored successfully',
                    ], 200
                );
            }else{
                throw new Exception("");
            }

        } catch (\Exception $e) {
            return response()->json(
                [
                    "success"=>false,
                    "message"=>'Error restoring product',
                ], 500
            );
        }
    }
    public function DeleteProduct (Request  $request) {    
        try {
            $id = $request->input('id');
            $formattedId = trim(str_replace(':', '', $id));

            $productService = new ProductService();
            $queryResult = $productService->getById($id);
            if($queryResult->isCompleted===false){
                return response()->json(
                    [
                        "success"=>false,
                        "message"=>'Product not found',
                    ], 404
                );
            }

            $dataQuery = $queryResult->data;

            $updateFields = ["deletedAt"=>time()];

            $queryResult = $productService->updateFields($id,$updateFields);
            if($queryResult->isCompleted===true){
                return response()->json(
                    [
                        "success"=>true,
                        "message"=>'Product restored successfully',
                    ], 200
                );
            }else{
                throw new Exception("");
            }

        } catch (\Exception $e) {
            return response()->json(
                [
                    "success"=>false,
                    "message"=>'Error restoring product',
                ], 500
            );
        }
    }
    public function GetProduct (Request  $request) {    
        try {
            $productService = new ProductService();
            $queryResult = $productService->getAll(false);
            if($queryResult->isCompleted===false){
                throw new Exception("");
            }
            $dataQuery = $queryResult->data;

            echo json_encode($dataQuery);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success"=>false,
                    "message"=>'Internal server error.',
                ], 500
            );
        }
    }
    public function Searchproduct (Request  $request) {    
        try {
            $search = strtolower($request->input('q',""));

            $productService = new ProductService();
            $queryResult = $productService->getAll(false);
            if($queryResult->isCompleted===false){
                throw new Exception("");
            }
            $dataQuery = $queryResult->data;
            $dataQuery = array_filter($dataQuery, function($value) {
                return strpos($search,strtolower($value["name"]))!==false;
            });

            return response()->json(
                $dataQuery, 200
            );
            echo json_encode($dataQuery);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success"=>false,
                    "message"=>'Internal server error.',
                ], 500
            );
        }
    }
    public function GetProductById (Request  $request) {    
        try {
            $id = $request->input('id');
            $productService = new ProductService();
            $queryResult = $productService->getById($id);
            if($queryResult->isCompleted===false){
                throw new Exception("");
            }
            $dataQuery = $queryResult->data;
            return response()->json(
                $dataQuery, 200
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "success"=>false,
                    "message"=>'Internal server error.',
                ], 500
            );
        }
    }
    public function EditProduct (Request  $request) {    
        try {
            $id = $request->input('id');

            $productService = new ProductService();
            $queryResult = $productService->getById($id);
            if($queryResult->isCompleted===false){
                return response()->json(
                    [
                        "success"=>false,
                        "message"=>'Product not found',
                    ], 404
                );
            }

            $dataQuery = $queryResult->data;

            $updateFields = ["updatedAt"=>time()];

            $name = $request->input('name');
            $prices = $request->input('prices');
            $image = $request->input('image');
            $rating = $request->input('rating');
            $description = $request->input('description');
            $included = $request->input('included');
            $categoryId = $request->input('categoryId');

            if($name!=null)$updateFields["name"] = $name;
            if($prices!=null)$updateFields["prices"] = $prices;
            if($image!=null)$updateFields["image"] = $image;
            if($rating!=null)$updateFields["rating"] = $rating;
            if($description!=null)$updateFields["description"] = $description;
            if($included!=null)$updateFields["included"] = $included;
            if($categoryId!=null)$updateFields["categoryId"] = $categoryId;

            $queryResult = $productService->updateFields($id,$updateFields);
            if($queryResult->isCompleted===true){
                return response()->json(
                    [
                        "success"=>true,
                        "message"=>'Product updated successfully',
                    ], 200
                );
            }else{
                throw new Exception("");
            }

        } catch (\Exception $e) {
            return response()->json(
                [
                    "success"=>false,
                    "message"=>'System Error',
                ], 500
            );
        }
    }


}
