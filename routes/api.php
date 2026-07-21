<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:api']]);
// ==========================================
// 🔓 API CÔNG KHAI
// ==========================================
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Giao Hàng Nhanh (GHN) API Proxy
Route::get('/ghn/provinces', [\App\Http\Controllers\Api\GhnController::class, 'getProvinces']);
Route::get('/ghn/districts', [\App\Http\Controllers\Api\GhnController::class, 'getDistricts']);
Route::get('/ghn/wards', [\App\Http\Controllers\Api\GhnController::class, 'getWards']);
Route::post('/ghn/fee', [\App\Http\Controllers\Api\GhnController::class, 'calculateFee']);
Route::post('/ghn/webhook', [\App\Http\Controllers\Api\GhnController::class, 'webhook']);

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
    // 💬 API CHAT REALTIME (Dành cho CẢ NHÂN VIÊN và KHÁCH HÀNG)
    // ==========================================
    Route::middleware(['auth:api'])->group(function () {
        Route::get('/chat/conversations', [\App\Http\Controllers\Api\ChatController::class, 'getConversations']);
        Route::post('/chat/conversations', [\App\Http\Controllers\Api\ChatController::class, 'startConversation']);
        Route::get('/chat/conversations/{id}/messages', [\App\Http\Controllers\Api\ChatController::class, 'getMessages']);
        Route::post('/chat/conversations/{id}/messages', [\App\Http\Controllers\Api\ChatController::class, 'sendMessage']);
        Route::delete('/chat/conversations/{id}/messages/{msgId}', [\App\Http\Controllers\Api\ChatController::class, 'recallMessage']);
    });

    // ==========================================
    // 👑 API DÀNH CHO ADMIN (Quyền = 0)
    // ==========================================
    Route::middleware(['role:0'])->group(function () {
        // Quản lý Cửa hàng (Multi-store) (Chỉ Admin)
        Route::get('/admin/stores', [\App\Http\Controllers\Api\StoreController::class, 'index']);
        Route::post('/admin/stores', [\App\Http\Controllers\Api\StoreController::class, 'store']);
        Route::put('/admin/stores/{id}/status', [\App\Http\Controllers\Api\StoreController::class, 'updateStatus']);
    });

    // ==========================================
    // 👥 API DÀNH CHO ADMIN & QUẢN LÝ CHI NHÁNH (Quyền = 0, 1)
    // ==========================================
    Route::middleware(['role:0,1'])->group(function () {
        // Thống kê - Báo cáo
        Route::get('/admin/reports/revenue', [AdminController::class, 'revenueReport']);
        Route::get('/admin/reports/top-products', [AdminController::class, 'topProducts']);

        // Quản lý Nhân sự
        Route::get('/admin/users', [AdminController::class, 'listUsers']);
        Route::post('/admin/staff', [AdminController::class, 'createStaff']);
        Route::post('/admin/staff/import', [AdminController::class, 'importStaff']);
        Route::put('/admin/staff/{username}', [AdminController::class, 'updateStaff']);
        Route::delete('/admin/staff/{username}', [AdminController::class, 'deleteStaff']);
        Route::get('/admin/promote-customer/{makh}', [AdminController::class, 'promoteCustomer']);
        Route::get('/admin/cham-cong', [AdminController::class, 'getChamCong']);
        Route::post('/admin/cham-cong', [AdminController::class, 'chamCong']);
        Route::post('/admin/staff/scan-cccd', [\App\Http\Controllers\Api\CccdOcrController::class, 'scan']);

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
        
        // Quản lý Hóa đơn (Sửa, cập nhật trạng thái)
        Route::put('/hoa-don/{id}', [\App\Http\Controllers\Api\OrderController::class, 'updateStatus']);

        // Quản lý Khách hàng (Thêm, Sửa, Xóa)
        Route::post('/customers', [\App\Http\Controllers\Api\CustomerController::class, 'store']);
        Route::put('/customers/{id}', [\App\Http\Controllers\Api\CustomerController::class, 'update']);
        Route::delete('/customers/{id}', [\App\Http\Controllers\Api\CustomerController::class, 'destroy']);
    });

    // ==========================================
    // 🎧 API DÀNH CHO ADMIN, QUẢN LÝ VÀ CSKH (Quyền = 0, 1, 4)
    // ==========================================
    Route::middleware(['role:0,1,4'])->group(function () {
        // Xem danh sách hóa đơn
        Route::get('/hoa-don', [\App\Http\Controllers\Api\OrderController::class, 'index']);
        Route::get('/hoa-don/{id}', [\App\Http\Controllers\Api\OrderController::class, 'show']);
        Route::get('/hoa-don/{id}/chi-tiet', [\App\Http\Controllers\Api\OrderController::class, 'getDetails']);
        
        // Xem danh sách khách hàng
        Route::get('/customers', [\App\Http\Controllers\Api\CustomerController::class, 'index']);

        // Tra cứu CSKH
        Route::get('/cskh/customer-info/{makh}', [\App\Http\Controllers\Api\CskhController::class, 'getCustomerInfo']);
        Route::get('/cskh/product-stock', [\App\Http\Controllers\Api\CskhController::class, 'checkProductStock']);
        Route::get('/cskh/nearby-stores', [\App\Http\Controllers\Api\CskhController::class, 'findNearbyStores']);
        Route::post('/cskh/place-order/{makh}', [\App\Http\Controllers\Api\CskhController::class, 'placeOrderForCustomer']);
    });

    // ==========================================
    // 🛒 API DÀNH CHO KHÁCH HÀNG (Quyền = 3)
    // ==========================================
    Route::middleware(['role:3'])->group(function () {
        // Mua hàng, xem giỏ hàng...
        Route::get('/cart', [\App\Http\Controllers\Api\CartController::class, 'index']);
        Route::post('/cart/add', [\App\Http\Controllers\Api\CartController::class, 'add']);
        Route::put('/cart/update', [\App\Http\Controllers\Api\CartController::class, 'updateQuantity']);
        Route::delete('/cart/remove/{masp}', [\App\Http\Controllers\Api\CartController::class, 'remove']);
        Route::post('/cart/checkout', [\App\Http\Controllers\Api\CartController::class, 'checkoutOnline']);

        // Xem đơn hàng của tôi
        Route::get('/my-orders', [\App\Http\Controllers\Api\OrderController::class, 'myOrders']);
        Route::get('/my-orders/{id}/chi-tiet', [\App\Http\Controllers\Api\OrderController::class, 'getMyOrderDetails']);
    });
});
