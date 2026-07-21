<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::statement("ALTER TABLE nhanvien MODIFY DIACHI TEXT");
    DB::statement("ALTER TABLE nhanvien MODIFY CCCD_TRUOC TEXT");
    DB::statement("ALTER TABLE nhanvien MODIFY CCCD_SAU TEXT");
    echo "Columns altered successfully!";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
