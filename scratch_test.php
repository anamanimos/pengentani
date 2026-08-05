<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo 'Pertanians: ' . \App\Models\Pertanian::count() . "\n";
echo 'Stores: ' . \App\Models\Store::count() . "\n";
echo 'Purchase Cats: ' . \App\Models\PurchaseCategory::count() . "\n";
echo 'Transaction Proofs: ' . \App\Models\TransactionProof::count() . "\n";
echo 'Purchases This Month: ' . \App\Models\Purchase::whereMonth('date', date('m'))->whereYear('date', date('Y'))->count() . "\n";
echo 'Purchase Items This Month: ' . \App\Models\PurchaseItem::whereHas('purchase', function($q) { $q->whereMonth('date', date('m'))->whereYear('date', date('Y')); })->count() . "\n";
