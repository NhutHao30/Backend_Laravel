<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$provId = 267; // Assuming 267 is An Giang based on standard province list, wait, let's query it

$res = \Illuminate\Support\Facades\Http::withoutVerifying()->withHeaders(['Token' => env('GHN_API_TOKEN')])->get('https://online-gateway.ghn.vn/shiip/public-api/master-data/province')->json();
$ag = null;
foreach($res['data'] as $p) {
    if (strpos($p['ProvinceName'], 'An Giang') !== false) {
        $ag = $p['ProvinceID'];
        break;
    }
}
$dist = \Illuminate\Support\Facades\Http::withoutVerifying()->withHeaders(['Token' => env('GHN_API_TOKEN')])->get('https://online-gateway.ghn.vn/shiip/public-api/master-data/district', ['province_id' => $ag])->json();
$ts = null;
foreach($dist['data'] as $d) {
    if (strpos($d['DistrictName'], 'Thoại Sơn') !== false) {
        $ts = $d['DistrictID'];
        break;
    }
}
$ward = \Illuminate\Support\Facades\Http::withoutVerifying()->withHeaders(['Token' => env('GHN_API_TOKEN')])->get('https://online-gateway.ghn.vn/shiip/public-api/master-data/ward', ['district_id' => $ts])->json();
$ns = null;
foreach($ward['data'] as $w) {
    if (strpos($w['WardName'], 'Núi Sập') !== false) {
        $ns = $w['WardCode'];
        break;
    }
}

echo "An Giang: $ag, Thoai Son: $ts, Nui Sap: $ns\n";

$res1 = \Illuminate\Support\Facades\Http::withoutVerifying()->withHeaders([
    'Token' => env('GHN_API_TOKEN'),
    'ShopId' => 6556036
])->post('https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/fee', [
    'service_type_id' => 2,
    'to_ward_code' => $ns,
    'to_district_id' => $ts,
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
    'to_ward_code' => $ns,
    'to_district_id' => $ts,
    'weight' => 1000,
    'length' => 20,
    'width' => 20,
    'height' => 10
])->json();

echo "Shop 1: " . json_encode($res1) . "\n";
echo "Shop 2: " . json_encode($res2) . "\n";
