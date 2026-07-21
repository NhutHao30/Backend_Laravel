<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$res1 = \Illuminate\Support\Facades\Http::withoutVerifying()->withHeaders([
    'Token' => env('GHN_API_TOKEN'),
    'ShopId' => 6556036
])->post('https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/fee', [
    'service_type_id' => 2,
    'to_ward_code' => '20314', // Default to a ward
    'to_district_id' => 1444,
    'weight' => 1000,
    'length' => 20,
    'width' => 20,
    'height' => 10
])->json();

$res2 = \Illuminate\Support\Facades\Http::withoutVerifying()->withHeaders([
    'Token' => env('GHN_API_TOKEN'),
    'ShopId' => 6556043
])->post('https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/fee', [
    'service_type_id' => 2,
    'to_ward_code' => '20314',
    'to_district_id' => 1444,
    'weight' => 1000,
    'length' => 20,
    'width' => 20,
    'height' => 10
])->json();

echo "Shop 1: " . json_encode($res1) . "\n";
echo "Shop 2: " . json_encode($res2) . "\n";
