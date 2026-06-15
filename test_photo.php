<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$u = App\Models\PertanianUpdate::whereNotNull('photo')->get();
echo json_encode($u->toArray(), JSON_PRETTY_PRINT);
