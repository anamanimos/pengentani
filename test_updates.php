<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$p = App\Models\Pertanian::where('uuid', 'afc6d58d-aeba-4055-a7d7-cc8def02862d')->first();
echo json_encode($p->updates->toArray(), JSON_PRETTY_PRINT);
