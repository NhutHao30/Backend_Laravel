<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;

// ==========================================
// 🔓 API CÔNG KHAI
// ==========================================
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

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
        // Các logic khác của Admin...
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
