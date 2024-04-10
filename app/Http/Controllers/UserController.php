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
            if($res->isCompleted==false){
                return response()->json(ControllerResponse::Error($res->message));
            }else{
                return response()->json(ControllerResponse::Success($res->data));
            }
        } catch (Exception $e) {
            // Nếu có ngoại lệ xảy ra, in ra thông báo lỗi
            
            return response()->json(ControllerResponse::Error("Server Error",500));
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
            if($res->isCompleted==false){
                return response()->json(ControllerResponse::Error($res->message));
            }else{
                return response()->json(ControllerResponse::Success($res->data));
            }
        } catch (Exception $e) {
            // Nếu có ngoại lệ xảy ra, in ra thông báo lỗi
            
            return response()->json(ControllerResponse::Error("Server Error",500));
        }
    }

    public function get (Request  $request, $id) {    
        try {
            $userService = new UserService();
            $res = $userService->getById($id);

            if($res->isCompleted==false){
                return response()->json(ControllerResponse::Error($res->message));
            }else{
                $id = $res->data["_id"]->toString();
                unset($res->data["_id"]);
                $res->data["id"] = $id;
                return response()->json(ControllerResponse::Success($res->data));
            }
        } catch (Exception $e) {
            // Nếu có ngoại lệ xảy ra, in ra thông báo lỗi
            
            return response()->json(ControllerResponse::Error("Server Error",500));
        }
    }
    public function delete (Request  $request, $id) {    
        try {
            $userService = new UserService();
            $res = $userService->delete($id);
            if($res->isCompleted===false){
                return response()->json(ControllerResponse::Error($res->message));
            }else{
                return response()->json(ControllerResponse::Success(""));
            }
        } catch (Exception $e) {
            
            return response()->json(ControllerResponse::Error("Server Error",500));
        }
    }
    public function login(Request  $request) {    
        try {
            $username = $request->input('username');
            $password = $request->input('password');
    
            $userService = new UserService();
            $queryResult = $userService->getByUsername($username);
            if($queryResult->isCompleted===false){
                return response()->json(ControllerResponse::Error("username or password wrong"));
            }
            $userQuery = $queryResult->data;
            $user_id = $userQuery->_id->__toString();
            $role = $userQuery["role"];
            if(!password_verify($password, $userQuery->password)){
                return response()->json(ControllerResponse::Error("username or password wrong"));
            }else{
                $newRefreshToken = JwtAuth::generateRandomString(20);
                $newAccessToken = JwtAuth::generateRandomString(20);

                $newRefreshToken = JwtAuth::generateRandomString(20);
                $refreshTokens = explode(",",$userQuery["refreshTokens"]);
                array_push($refreshTokens,$newRefreshToken);
                $updateFields = ["refreshTokens"=>implode(",",$refreshTokens)];
                
                $queryResult = $userService->updateFields($user_id,$updateFields);
                if($queryResult->isCompleted===false){
                    throw new Exception("Server error");
                }
                RedisManager::addWhiteList($user_id.$newAccessToken);
                $newJwt = JwtAuth::encode($user_id, $newAccessToken, $newRefreshToken,$role);

                return response()->json(ControllerResponse::Success(["jwt"=>$newJwt]));
            }
        } catch (Exception $e) {
            // Nếu có ngoại lệ xảy ra, in ra thông báo lỗi
            
            return response()->json(ControllerResponse::Error("Server Error",500));
        }
    }
    public function logout(Request  $request) {    
        try {
            $user_id = $request->header('user_id');
            $accessToken = $request->header('user_access_token');
            $refreshToken = $request->header('user_refresh_token');
    
            $userService = new UserService();
            $queryResult = $userService->getById($user_id);
            if($queryResult->isCompleted===false){
                return response()->json(ControllerResponse::Error("System Error",500));
            }
            $userQuery = $queryResult->data;

            $refreshTokens = explode(",",$userQuery["refreshTokens"]);
            if (($key = array_search($refreshToken, $refreshTokens)) !== false) {
                unset($refreshTokens[$key]);
            }
            $updateFields = ["refreshTokens"=>implode(",",$refreshTokens)];

            $queryResult = $userService->updateFields($user_id,$updateFields);
            if($queryResult->isCompleted===false){
                throw new Exception("Server error");
            }
            RedisManager::delWhiteList($user_id.$accessToken);
            RedisManager::addBlackList($user_id.$accessToken);

            return response()->json(ControllerResponse::Success(["isCompleted"=>true]));
            
        } catch (Exception $e) {
            // Nếu có ngoại lệ xảy ra, in ra thông báo lỗi
            
            return response()->json(ControllerResponse::Error("Server Error",500));
        }
    }
    public function logoutAll(Request  $request) {    
        try {
            $user_id = $request->header('user_id');
    
            $userService = new UserService();
            $queryResult = $userService->getById($user_id);
            if($queryResult->isCompleted===false){
                return response()->json(ControllerResponse::Error("System Error",500));
            }
            $userQuery = $queryResult->data;

            $updateFields = ["refreshTokens"=>""];

            $queryResult = $userService->updateFields($user_id,$updateFields);
            if($queryResult->isCompleted===false){
                throw new Exception("Server error");
            }
            RedisManager::delWhiteListByUser($user_id);

            return response()->json(ControllerResponse::Success(["isCompleted"=>true]));
            
        } catch (Exception $e) {
            // Nếu có ngoại lệ xảy ra, in ra thông báo lỗi
            
            return response()->json(ControllerResponse::Error("Server Error",500));
        }
    }
}