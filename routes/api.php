<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;

// ==========================================
// PUBLIC API (NO LOGIN REQUIRED)
// ==========================================
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/products', [ProductController::class, 'index']); // See list of products
Route::get('/products/{id}', [ProductController::class, 'show']); // See product details

// ==========================================
// SECURE API (JWT TOKEN MUST BE ATTACHED)
// ==========================================
Route::group(['middleware' => 'auth:api'], function () {
    
    // Member 1: Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']); // Retrieve current user information
    
    // Member 3: Cart
    Route::get('/cart', [CartController::class, 'getCart']);
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    
    // Các bạn tự thêm Route của mình vào đây, Viết code và comment bằng tiếng Anh, làm xong đẩy lên github với branch của mình,
    // mỗi branch nên là 1 tính năng sau khi xong tính năng nào thì xóa branch luôn cũng được
    // Gitflow:
    // Branch main: Code sạch, dùng để deploy.
    // Branch develop: Code đang phát triển chung của nhóm.
    // đặt tên branch theo quy tắt sau: feature/author/feature-name: Nhánh riêng của từng bạn (ví dụ: feature/NhutHao/login-api).
    // Commit Message: [Type] Message (Ví dụ: Fix - resolve login bug, Add - Minio upload service, Feat - ....).
});
