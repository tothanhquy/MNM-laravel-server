<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use MongoDB\Client;
use App\MongoModel\UserModel;
use App\MongoService\UserService;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/info', function () {
    phpinfo();
});
Route::get('/ping', function (Request  $request) {    
    try {
        // Khởi tạo client MongoDB từ URI trong biến môi trường
        $client = new Client(env('MONGODB_URI'));
    
        // Thực hiện một hoạt động đơn giản như lấy danh sách cơ sở dữ liệu
        $databases = $client->listDatabases();
    
        // Nếu không có ngoại lệ nào được ném, kết nối thành công
        echo "Kết nối MongoDB thành công!";
        var_dump($databases);
    } catch (\Exception $e) {
        // Nếu có ngoại lệ xảy ra, in ra thông báo lỗi
        echo "Không thể kết nối MongoDB: " . $e->getMessage();
    }
});

// Route::post('/user', [UserController::class, 'create']);
// Route::put('/user/{id}', [UserController::class, 'update']);
