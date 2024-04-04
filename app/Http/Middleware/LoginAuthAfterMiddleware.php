<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use App\Http\Controllers\ControllerResponse;
use App\Http\Controllers\JwtAuth;
use App\MongoService\UserService;
use App\Http\Middleware\RedisManager;

class LoginAuthAfterMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Thực hiện các tác vụ sau khi response được trả về từ route
        // Ví dụ: Ghi log, thêm header vào response, ...
        if(!empty($request->header('newAuthorization'))){
            $response->headers->set('newAuthorization', $request->header('newAuthorization'));
        }
        return $response;
    }
}
