<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::statement("SET FOREIGN_KEY_CHECKS=0");
    
    DB::statement("UPDATE vaitro SET MAROLE = 3 WHERE MAROLE = 2 AND MOTA = 'khachhang'");
    DB::statement("UPDATE taikhoan SET MAROLE = 3 WHERE MAROLE = 2");
    
    DB::statement("UPDATE vaitro SET MAROLE = 2 WHERE MAROLE = 1 AND MOTA = 'nhanvien'");
    DB::statement("UPDATE taikhoan SET MAROLE = 2 WHERE MAROLE = 1");

    DB::statement("INSERT IGNORE INTO vaitro (MAROLE, MOTA) VALUES (1, 'quanly_chinhanh')");
    DB::statement("INSERT IGNORE INTO vaitro (MAROLE, MOTA) VALUES (2, 'nhanvien')");
    DB::statement("INSERT IGNORE INTO vaitro (MAROLE, MOTA) VALUES (3, 'khachhang')");
    
    DB::statement("UPDATE vaitro SET MOTA = 'quanly_chinhanh' WHERE MAROLE = 1");
    DB::statement("UPDATE vaitro SET MOTA = 'nhanvien' WHERE MAROLE = 2");
    DB::statement("UPDATE vaitro SET MOTA = 'khachhang' WHERE MAROLE = 3");
    
    DB::statement("SET FOREIGN_KEY_CHECKS=1");
    echo "Roles updated successfully!";
} catch (\Exception $e) {
    DB::statement("SET FOREIGN_KEY_CHECKS=1");
    echo "Error: " . $e->getMessage();
}
