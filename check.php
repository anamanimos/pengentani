<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$stores = \App\Models\Store::all();
echo "Total Stores: " . $stores->count() . "\n";
foreach ($stores as $s) {
    echo "ID: {$s->id} | Name: {$s->name}\n";
}
