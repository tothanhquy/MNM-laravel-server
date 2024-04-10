<?php
namespace App\Http\Controllers;

use MiladRahimi\Jwt\Generator;
use MiladRahimi\Jwt\Parser;
use MiladRahimi\Jwt\Cryptography\Algorithms\Hmac\HS256;
use MiladRahimi\Jwt\Cryptography\Keys\HmacKey;
use MiladRahimi\Jwt\Exceptions\ValidationException;

class JwtAuth{
    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
    
        // Lấy độ dài của chuỗi ký tự
        $charLength = strlen($characters);
    
        // Lặp để tạo chuỗi ngẫu nhiên
        for ($i = 0; $i < $length; $i++) {
            // Chọn một ký tự ngẫu nhiên từ chuỗi ký tự đã cho
            $randomString .= $characters[random_int(0, $charLength - 1)];
        }
    
        return $randomString;
    }
    public static function createUser($id, $accessToken, $refreshToken, $role){
        $user = [
            'id' => $id,
            'accessToken' => $accessToken,
            'refreshToken' => $refreshToken,
            'role' => $role
        ];
        return $user;
    }
    public static function isValidUser($user){
        if(!isset($user)||!isset($user["id"])||!isset($user["accessToken"])||!isset($user["refreshToken"])||!isset($user["role"])){
            return false;
        }
        return true;
    }
    private static function getSigner() { 
        $key = new HmacKey('12345678901234567890123456789012');
        $signer = new HS256($key);
        return $signer;
    }
    public static function encode($id, $accessToken, $refreshToken, $role){
        $user = self::createUser($id, $accessToken, $refreshToken,$role);
        $generator = new Generator(self::getSigner());
        return $generator->generate($user);
    }
    public static function decode($token){
        try{
            $parser = new Parser(self::getSigner());
            $user = $parser->parse($token);
            //var_dump($user);
            if(!self::isValidUser($user)){
                //echo "!self::isValidUser($token)";
                return null;
            }
            return $user;
        }catch(\Exception  $e){
            return null;
        }
    }
}