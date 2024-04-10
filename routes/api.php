<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });



Route::prefix('user')->middleware(['LoginAuth:admin','LoginAuthAfter'])->group(function () {
    Route::delete('/{id}', [UserController::class, 'delete']);
});

Route::prefix('auth')->middleware(['LoginAuth','LoginAuthAfter'])->group(function () {
    Route::post('/register', [UserController::class, 'register'])->withoutMiddleware('LoginAuth');
    Route::put('/{id}', [UserController::class, 'update']);
    Route::get('/get{id}', [UserController::class, 'get']);
    Route::post('/login', [UserController::class, 'login'])->withoutMiddleware('LoginAuth');
    Route::post('/logout', [UserController::class, 'logout']);
    Route::post('/logoutAll', [UserController::class, 'logoutAll']);
});

