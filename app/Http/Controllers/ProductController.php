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
    public function AddCMproduct (Request  $request) {    
        try {
            $id = $request->input('id');
            $content = $request->input('content');
            $author = $request->input('author');

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
            if($content==null||$content==""){
                return response()->json(
                    [
                        "success"=>false,
                        "message"=>'Content cannot be empty',
                    ], 404
                );
            }

            $dataQuery = $queryResult->data;
            $comments = $dataQuery["comments"];

            array_push($comments,ProductModel::getComment($author,$content));
            $updateFields = ["comments"=>$comments];

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
    public function getCMproduct (Request  $request) {    
        try {
            $id = $request->input('productId');
            $productService = new ProductService();
            $queryResult = $productService->getById($id);
            if($queryResult->isCompleted===false){
                throw new Exception("");
            }
            $dataQuery = $queryResult->data;
            $comments = $dataQuery["comments"];

            return response()->json(
                $comments, 200
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

}

// exports.destroy = async (req, res, next) => {
//     const { id } = req.params;
//     try {
//         // Remove any leading colon from the ID and trim whitespace
//         const formattedId = id.replace(':', '').trim();

//         // Use the restore method to mark the product as restored
//         const result = await Product.findByIdAndRemove({ _id: formattedId });

//         if (result.nModified === 0) {
//             return res.status(404).json({
//                 success: false,
//                 message: 'Product not found',
//             });
//         }
//         return res.status(200).json({
//             success: true,
//             message: 'Product restored successfully',
//         });
//     } catch (err) {
//         console.log(err);
//         return res.status(500).json({
//             success: false,
//             message: 'Error restoring product',
//         });
//     }
// };

// exports.GetProductdelete = async (req, res) => {
//     try {
//         const deletedProducts = await Product.findDeleted();
//         res.json(deletedProducts);
//     } catch (error) {
//         res.status(500).json({
//             success: false,
//             message: 'Error fetching deleted products',
//         });
//     }
// };
// exports.RestoreProduct = async (req, res, next) => {
//     const { id } = req.params;
//     try {
//         // Remove any leading colon from the ID and trim whitespace
//         const formattedId = id.replace(':', '').trim();

//         // Use the restore method to mark the product as restored
//         const result = await Product.restore({ _id: formattedId });

//         if (result.nModified === 0) {
//             return res.status(404).json({
//                 success: false,
//                 message: 'Product not found',
//             });
//         }
//         return res.status(200).json({
//             success: true,
//             message: 'Product restored successfully',
//         });
//     } catch (err) {
//         console.log(err);
//         return res.status(500).json({
//             success: false,
//             message: 'Error restoring product',
//         });
//     }
// };

// exports.DeleteProduct = async (req, res) => {
//     const { id } = req.params;
//     try {
//         // Remove any leading colon from the ID and trim whitespace
//         const formattedId = id.replace(':', '').trim();
//         const deletedAt = new Date();
//         // Use updateMany to mark the product as deleted
//         const result = await Product.updateMany(
//             { _id: formattedId },
//             { $set: { deleted: true, deletedAt: deletedAt } },
//         );
//         console.log(deletedAt);
//         if (result.nModified === 0) {
//             return res.status(404).json({
//                 success: false,
//                 message: 'Product not found',
//             });
//         }
//         return res.status(200).json({
//             success: true,
//             message: 'Product deleted successfully',
//         });
//     } catch (err) {
//         console.log(err);
//         return res.status(500).json({
//             success: false,
//             message: 'Error deleting product',
//         });
//     }
// };

// exports.LikeProduct = async (req, res) => {
//     await Product.findByIdAndUpdate(req.params.id, { $inc: { likes: 1 }, updateAt: Date.now() }, { new: true }).catch(
//         (err) => {
//             return res.status(400).json({
//                 success: false,
//                 message: 'error updating likes',
//             });
//         },
//     );
// };
// exports.getLike = async (req, res) => {
//     const data = await Product.find();
//     res.json({
//         success: true,
//         data: data.map((product) => ({
//             _id: product._id,
//             name: product.name,
//             img: product.img,
//             likes: product.likes,
//             updateAt: product.updateAt,
//             status: product.status,
//         })),
//     });
// };

// exports.PostProduct = async (req, res) => {
//     const { name, prices, image, rating, description, included, categoryId } = req.body;

//     // Check if prices is an array and each item has size and price properties
//     if (!Array.isArray(prices) || prices.some((item) => !item.size || !item.price)) {
//         return res.status(400).json({ error: 'Prices array is invalid' });
//     }

//     // Create a new product instance
//     const newProduct = new Product({
//         name,
//         prices,
//         image,
//         rating: 3, // You may want to use the provided rating value
//         description,
//         included,
//         categoryId,
//     });

//     try {
//         // Save the product to the database
//         const savedProduct = await newProduct.save();
//         // Respond with the saved product data
//         res.status(201).json(savedProduct);
//     } catch (error) {
//         console.error(error);
//         res.status(500).json({ error: 'Internal Server Error' });
//     }
// };

// exports.GetProduct = async (req, res) => {
//     const data = await Product.find();
//     res.json(data);
// };

// exports.Searchproduct = async (req, res) => {
//     try {
//         const searchQuery = req.query.q; // Get the search query from the URL parameters
//         if (!searchQuery) {
//           return res.status(400).json({ error: 'Search query is required.' });
//         }
    
//         // Use a case-insensitive regular expression to search for products by name
//         const regex = new RegExp(searchQuery, 'i');
//         const products = await Product.find({ name: regex });
    
//         res.json({ results: products });
//       } catch (error) {
//         console.error(error);
//         res.status(500).json({ error: 'Internal server error.' });
//       }
// };
// exports.GetProductid = async (req, res) => {
//     const product = await Product.findById(req.params.id);
//     res.json(product);
// };

// exports.EditProduct = async (req, res) => {
//     try {
//         const data = await Product.findByIdAndUpdate(req.params.id, req.body, { new: true });
//         res.json(data);
//     } catch (error) {
//         console.error(error);
//         res.status(500).json({ error: 'Something went wrong' });
//     }
// };

// exports.AddCMproduct = async (req, res) => {
//     try {
//         const product = await Product.findById(req.params.id).exec();
//         if (!req.body.content) {
//             res.status(400).send({ message: 'Content is required.' });
//         } else {
//             product.comments.push({
//                 author: req.body.author,
//                 content: req.body.content,
//             });

//             const savedProduct = await product.save();
//             res.send(savedProduct);
//         }
//     } catch (err) {
//         res.status(500).send(err);
//     }
// };
// exports.getCMproduct = async (req, res) => {
//     try {
//         const product = await Product.findById(req.params.productId).populate('comments');
//         res.json(product.comments);
//     } catch (err) {
//         console.error(err.message);
//         res.status(500).send('Server Error');
//     }
// };