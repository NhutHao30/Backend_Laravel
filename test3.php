<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$res = \Illuminate\Support\Facades\DB::select("SELECT MAHD, MACUAHANG, TRANGTHAITHANHTOAN, GHICHU FROM hdban WHERE MAHD IN ('HD84566858', 'HD84566915')");
echo json_encode($res);
