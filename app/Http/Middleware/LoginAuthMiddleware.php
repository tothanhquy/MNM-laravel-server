<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\ControllerResponse;
use App\Http\Controllers\JwtAuth;
use App\MongoService\UserService;
use App\Http\Middleware\RedisManager;

class LoginAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try{
            // Thực hiện bất kỳ xử lý nào bạn muốn trước khi chuyển yêu cầu đến bên trong ứng dụng
            $jwt = $request->header('Authorization');
            if(empty($jwt)){
                return response()->json(ControllerResponse::Error(403,"not authenticated"));
            }
            $jwtUser = JwtAuth::decode($jwt);
            if($jwtUser==null){
                return response()->json(ControllerResponse::Error(403,"not authenticated"));
            }
            $jwtAccessToken = $jwtUser["accessToken"];
            $jwtRefreshToken = $jwtUser["refreshToken"];
            $user_id = $jwtUser["id"];
            $request->headers->set('user_id', $user_id);
            $request->headers->set('user_access_token', $jwtAccessToken);

            if(RedisManager::inWhiteList($user_id.$jwtAccessToken)){
                return $next($request);
            }

            if(RedisManager::inBlackList($user_id.$jwtAccessToken)){
                return response()->json(ControllerResponse::Error(403,"not authenticated"));
            }

            $userService = new UserService();
            $queryResult = $userService->getById($user_id);
            if($queryResult->isCompleted===false||$queryResult->data===null){
                throw new Exception("Server error");
            }

            $userQuery = $queryResult->data;

            if($userQuery->refreshToken!=$jwtRefreshToken){
                //invalid user
                RedisManager::addBlackList($user_id.$jwtAccessToken);
                return response()->json(ControllerResponse::Error(403,"not authenticated"));
            }else{
                $newRefreshToken = JwtAuth::generateRandomString(20);
                $newAccessToken = JwtAuth::generateRandomString(20);
                $userQuery->refreshToken = $newRefreshToken;
                $updateFields = ["refreshToken"=>$newAccessToken];
                $queryResult = $userService->updateFields($user_id,$updateFields);
                if($queryResult->isCompleted===false){
                    throw new Exception("Server error");
                }
                RedisManager::addWhiteList($user_id.$newAccessToken);
                $newJwt = JwtAuth::encode($user_id, $newAccessToken, $newRefreshToken);
                $request->headers->set('newAuthorization', $newJwt);
                return $next($request);
            }
            
        }catch(Exception $e){
            return response()->json(ControllerResponse::Error(500,"Server error: " . $e->getMessage));
        }
        
    }
}
