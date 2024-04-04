<?php
namespace App\Http\Middleware;
use Illuminate\Support\Facades\Redis;

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
        Redis::set('black_list_'.$key, true);
    }
    public static function delWhiteList($key){
        return Redis::del('white_list_'.$key)>0;
    }
    public static function delBlackList($key){
        return Redis::del('black_list_'.$key)>0;
    }
}