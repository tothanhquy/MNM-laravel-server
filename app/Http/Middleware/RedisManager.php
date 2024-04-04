<?php
namespace App\Http\Middleware;
use Illuminate\Support\Facades\Redis;
use Predis\Client;

class RedisManager{
    public static function inWhiteList($key){
        $user = Redis::get('white_list_'.$key);
        return $user;
    }
    public static function inBlackList($key){
        $user = Redis::get('black_list_'.$key);
        return $user;
    }
    public static function addWhiteList($key){
        Redis::set('white_list_'.$key, true, 'EX', 1000);
    }
    public static function addBlackList($key){
        Redis::set('black_list_'.$key, true, 'EX', 60*60*24*30);
    }
    public static function delWhiteList($key){
        return Redis::del('white_list_'.$key)>0;
    }
    public static function delBlackList($key){
        return Redis::del('black_list_'.$key)>0;
    }
    public static function delWhiteListByUser($idUser){
        try{
            $redisConfig = [
                'host' => env('REDIS_HOST', 'localhost'),
                'password' => env('REDIS_PASSWORD', null),
                'port' => env('REDIS_PORT', 6379),
                //'database' => env('REDIS_DATABASE', 0),
            ];
            // Kết nối đến Redis server
            $client = new Client($redisConfig);

            //$response = $client->ping();

            // Kiểm tra kết quả của lệnh ping
            // if ($response === 'PONG') {
            //     echo "Kết nối thành công đến máy chủ Redis.";
            // } else {
            //     echo "Kết nối không thành công đến máy chủ Redis.";
            // }

            $pattern = 'white_list_'.$idUser;
            $list = $client->keys("*");

            foreach ($list as $key)
            {
                if(strpos($key,$pattern)!==false){
                    $client->del($key);
                }
            }

            return true;
        }catch(\Exception $e){
            echo "Error $e: " . $e->getMessage();
            return false;
        }
        
    }
}