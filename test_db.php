<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $items = App\Models\PurchaseItem::orderBy('id', 'desc')->take(5)->get();
    echo "ITEMS:\n";
    foreach($items as $i) {
        echo $i->id . " - " . $i->description . " - " . $i->total_price . "\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
