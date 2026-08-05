<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('name', 'like', '%Choirul%')->first();
if (!$user) {
    echo "User Choirul not found.\n";
    $user = \App\Models\User::first();
}
echo "User: " . $user->name . "\n";
$entityIds = $user->entities()->pluck('entities.id')->toArray();

$investments = \App\Models\PertanianInvestor::whereIn('entity_id', $entityIds)
    ->with(['pertanian.incomes', 'pertanian.purchases', 'pertanian.workerJobs'])
    ->get();

$totalInvestment = 0;
$totalReturnInvestor = 0;

foreach ($investments as $inv) {
    $pertanian = $inv->pertanian;
    if (!$pertanian) continue;

    $totalIncome = $pertanian->incomes->sum('amount');
    $totalPurchase = $pertanian->purchases->sum('total_amount');
    $totalWorker = $pertanian->workerJobs->sum('wage');
    $labaBersih = $totalIncome - $totalPurchase - $totalWorker;

    $zakatPersen = $pertanian->persentase_zakat ?? 5;
    $zakat = $labaBersih > 0 ? $labaBersih * ($zakatPersen / 100) : 0;
    $labaSetelahZakat = $labaBersih - $zakat;

    $persentaseInvestorTotal = $pertanian->persentase_investor ?? 0;
    $batasanInvestasi = $pertanian->batasan_investasi > 0 ? $pertanian->batasan_investasi : 1;
    $proportion = $inv->besaran_investasi / $batasanInvestasi;
    $userProfit = $labaSetelahZakat * ($persentaseInvestorTotal / 100) * $proportion;

    echo "Pertanian ID: " . $pertanian->id . "\n";
    echo "  Total Income: " . $totalIncome . "\n";
    echo "  Total Purchase: " . $totalPurchase . "\n";
    echo "  Total Worker: " . $totalWorker . "\n";
    echo "  Laba Bersih: " . $labaBersih . "\n";
    echo "  Laba Setelah Zakat: " . $labaSetelahZakat . "\n";
    echo "  Persentase Investor Total: " . $persentaseInvestorTotal . "%\n";
    echo "  Batasan Investasi: " . $batasanInvestasi . "\n";
    echo "  Besaran Investasi: " . $inv->besaran_investasi . "\n";
    echo "  Proportion: " . $proportion . "\n";
    echo "  User Profit: " . $userProfit . "\n\n";
    
    $totalInvestment += $inv->besaran_investasi;
    $totalReturnInvestor += $userProfit;
}

echo "Total Investment: " . $totalInvestment . "\n";
echo "Total Return: " . $totalReturnInvestor . "\n";
