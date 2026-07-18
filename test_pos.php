<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $req = new Illuminate\Http\Request();
    $req->merge([
        'paymentMethod' => 'COD',
        'cart' => [
            ['id' => 'SP044955', 'quantity' => 1, 'price' => 15000]
        ]
    ]);
    
    $ctrl = new App\Http\Controllers\Api\OrderController();
    $res = $ctrl->storePOS($req);
    echo "STATUS: " . $res->getStatusCode() . "\n";
    echo "CONTENT: " . $res->getContent() . "\n";
} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
