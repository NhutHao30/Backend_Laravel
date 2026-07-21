<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('cuahang');
    if (in_array('DIACHI', $columns) || in_array('Diachi', $columns) || in_array('diachi', $columns)) {
        // Find exact column name
        $diachiCol = 'DIACHI';
        foreach ($columns as $c) {
            if (strtolower($c) == 'diachi') $diachiCol = $c;
        }
        DB::statement("ALTER TABLE cuahang MODIFY {$diachiCol} TEXT");
        echo "CuaHang table DIACHI altered successfully!";
    } else {
        echo "No DIACHI column in CuaHang.";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
