<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::statement("ALTER TABLE khachhang MODIFY DIACHI TEXT");
    
    // Kiểm tra xem bảng khách hàng có các cột avatar/hình ảnh hay không, nếu có thì alter luôn
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('khachhang');
    if (in_array('AVATAR', $columns)) {
        DB::statement("ALTER TABLE khachhang MODIFY AVATAR TEXT");
    }
    
    echo "KhachHang table columns altered successfully!";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
