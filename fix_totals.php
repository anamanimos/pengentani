<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (\App\Models\Purchase::all() as $p) {
    $actual = $p->items()->sum('total_price');
    if ($p->items()->count() === 0) {
        echo "Deleting empty Purchase {$p->id}\n";
        $p->delete();
    } elseif ($p->total_amount != $actual) {
        echo "Updating Purchase {$p->id} from {$p->total_amount} to {$actual}\n";
        $p->update(['total_amount' => $actual]);
    }
}
echo "Done.\n";
