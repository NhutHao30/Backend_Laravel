<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TaiKhoan;

$user = TaiKhoan::where('USERNAME', 'khachhang_01')->first();
if($user) {
    $user->MAROLE = 0;
    $user->save();
    echo "Thanh cong! Tai khoan 'khachhang_01' da duoc nang cap len Admin (MAROLE = 0).\n";
} else {
    echo "Khong tim thay tai khoan 'khachhang_01'. Hay chac chan ban da dang ky truoc do.\n";
}
