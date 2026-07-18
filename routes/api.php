<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
// ==========================================
// 🔓 API CÔNG KHAI
// ==========================================
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
// ==========================================
// 🔒 API BẢO MẬT (ĐÃ ĐĂNG NHẬP)
// ==========================================
Route::middleware(['auth:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);

    // ==========================================
    // 👑 API DÀNH CHO ADMIN (Quyền = 0)
    // ==========================================
    Route::middleware(['role:0'])->group(function () {
        Route::get('/admin/users', [AdminController::class, 'listUsers']);
        Route::post('/admin/staff', [AdminController::class, 'createStaff']);
        Route::get('/admin/promote-customer/{makh}', [AdminController::class, 'promoteCustomer']);
        
        // Quản lý Danh mục (Catalog)
        Route::post('/admin/categories', [CategoryController::class, 'store']);
        Route::put('/admin/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/admin/categories/{id}', [CategoryController::class, 'destroy']);

        // Quản lý Sản phẩm (Catalog)
        Route::post('/admin/products', [ProductController::class, 'store']);
        Route::put('/admin/products/{id}', [ProductController::class, 'update']);
        Route::delete('/admin/products/{id}', [ProductController::class, 'destroy']);
        
        // POS Bán hàng
        Route::post('/admin/pos/checkout', [\App\Http\Controllers\Api\OrderController::class, 'storePOS']);
        
        // Quản lý Hóa đơn
        Route::get('/hoa-don', [\App\Http\Controllers\Api\OrderController::class, 'index']);
        Route::get('/hoa-don/{id}', [\App\Http\Controllers\Api\OrderController::class, 'show']);
        Route::get('/hoa-don/{id}/chi-tiet', [\App\Http\Controllers\Api\OrderController::class, 'getDetails']);
        Route::put('/hoa-don/{id}', [\App\Http\Controllers\Api\OrderController::class, 'updateStatus']);
    });

    // ==========================================
    // 👥 API DÀNH CHO NHÂN VIÊN (Quyền = 1)
    // ==========================================
    Route::middleware(['role:1,0'])->group(function () {
        // Chỉ Nhân viên hoặc Admin mới gọi được API ở đây
    });

    // ==========================================
    // 🛒 API DÀNH CHO KHÁCH HÀNG (Quyền = 2)
    // ==========================================
    Route::middleware(['role:2'])->group(function () {
        // Mua hàng, xem giỏ hàng...
    });
});
