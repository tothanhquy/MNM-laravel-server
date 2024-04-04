<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MongoDB\Client;
use App\MongoModel\UserModel;
use App\Http\Controllers\JwtAuth;
use App\MongoService\UserService;
use App\Http\Middleware\RedisManager;

class UserController extends Controller{
    public function register (Request  $request) {    
        try {
            $username = $request->input('username');
            $password = $request->input('password');
            $sdt = $request->input('sdt');
            $age = $request->input('age');
            $gender = $request->input('gender');

            $passwordHash = password_hash($password,PASSWORD_DEFAULT);
    
            $newUser = new UserModel($username, $passwordHash,false, $sdt, $age, $gender);
            $userService = new UserService();
            $newUser->refreshToken = JwtAuth::generateRandomString(20);
            $res = $userService->insert($newUser);
            echo json_encode($res);
        } catch (Exception $e) {
            // Nếu có ngoại lệ xảy ra, in ra thông báo lỗi
            echo "Error: " . $e->getMessage();
        }
    }
    public function update (Request  $request, $id) {    
        try {
            $username = $request->input('username');
            $password = $request->input('password');
            $sdt = $request->input('sdt');
            $age = $request->input('age');
            $gender = $request->input('gender');
    
            $newUser = new UserModel($username, $password,false, $sdt, $age, $gender);
            $userService = new UserService();
            $res = $userService->update($id, $newUser);
            echo json_encode($res);
        } catch (Exception $e) {
            // Nếu có ngoại lệ xảy ra, in ra thông báo lỗi
            echo "Error: " . $e->getMessage();
        }
    }

    public function get (Request  $request, $id) {    
        try {
            $userService = new UserService();
            $res = $userService->getById($id);
            echo json_encode($res);
        } catch (Exception $e) {
            // Nếu có ngoại lệ xảy ra, in ra thông báo lỗi
            echo "Error: " . $e->getMessage();
        }
    }
    public function delete (Request  $request, $id) {    
        try {
            $userService = new UserService();
            $res = $userService->delete($id);
            echo json_encode($res);
        } catch (Exception $e) {
            // Nếu có ngoại lệ xảy ra, in ra thông báo lỗi
            echo "Error: " . $e->getMessage();
        }
    }
    public function login(Request  $request) {    
        try {
            $username = $request->input('username');
            $password = $request->input('password');
    
            $userService = new UserService();
            $queryResult = $userService->getByUsername($username);
            if($queryResult->isCompleted===false){
                return response()->json(ControllerResponse::Success(["isCompleted"=>false,"message"=>"username or password wrong"]));
            }
            $userQuery = $queryResult->data;
            $user_id = $userQuery->_id->__toString();
            if(!password_verify($password, $userQuery->password)){
                return response()->json(ControllerResponse::Success(["isCompleted"=>false,"message"=>"username or password wrong"]));
            }else{
                $newRefreshToken = JwtAuth::generateRandomString(20);
                $newAccessToken = JwtAuth::generateRandomString(20);
                $updateFields = ["refreshToken"=>$newRefreshToken];
                $queryResult = $userService->updateFields($user_id,$updateFields);
                if($queryResult->isCompleted===false){
                    throw new Exception("Server error");
                }
                RedisManager::addWhiteList($user_id.$newAccessToken);
                $newJwt = JwtAuth::encode($user_id, $newAccessToken, $newRefreshToken);

                return response()->json(ControllerResponse::Success(["isCompleted"=>true,"jwt"=>$newJwt]));
            }
        } catch (Exception $e) {
            // Nếu có ngoại lệ xảy ra, in ra thông báo lỗi
            echo "Error: " . $e->getMessage();
            return response()->json(ControllerResponse::Error(500,"Server Error"));
        }
    }
    public function logout(Request  $request) {    
        try {
            $user_id = $request->header('user_id');
            $accessToken = $request->header('user_access_token');
    
            $userService = new UserService();
            $queryResult = $userService->getById($user_id);
            if($queryResult->isCompleted===false){
                return response()->json(ControllerResponse::Error(500,"System Error"));
            }
            $userQuery = $queryResult->data;

            $newRefreshToken = JwtAuth::generateRandomString(20);
            $updateFields = ["refreshToken"=>$newRefreshToken];
            $queryResult = $userService->updateFields($user_id,$updateFields);
            if($queryResult->isCompleted===false){
                throw new Exception("Server error");
            }
            RedisManager::delWhiteList($user_id.$accessToken);
            RedisManager::addBlackList($user_id.$accessToken);

            return response()->json(ControllerResponse::Success(["isCompleted"=>true]));
            
        } catch (Exception $e) {
            // Nếu có ngoại lệ xảy ra, in ra thông báo lỗi
            echo "Error: " . $e->getMessage();
            return response()->json(ControllerResponse::Error(500,"Server Error"));
        }
    }
}