<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $username = '089204017978';
    
    // Xóa trong bảng NhanVien trước (vì có khóa ngoại trỏ tới TaiKhoan)
    $deletedNhanVien = DB::table('nhanvien')->where('USERNAME', $username)->delete();
    
    // Sau đó xóa trong bảng TaiKhoan
    $deletedTaiKhoan = DB::table('taikhoan')->where('USERNAME', $username)->delete();
    
    echo "Deleted NhanVien: $deletedNhanVien row(s)\n";
    echo "Deleted TaiKhoan: $deletedTaiKhoan row(s)\n";
    echo "Xóa nhân viên $username thành công!";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
