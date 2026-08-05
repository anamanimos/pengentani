<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('name', 'like', '%Choi%')->first();
$entityIds = $user->entities()->pluck('entities.id')->toArray();

$investments = \App\Models\PertanianInvestor::whereIn('entity_id', $entityIds)
    ->with(['pertanian.incomes', 'pertanian.purchases', 'pertanian.workerJobs'])
    ->get();

foreach ($investments as $inv) {
    $pertanian = $inv->pertanian;
    $totalIncome = $pertanian->incomes->sum('amount');
    $totalPurchase = $pertanian->purchases->sum('total_amount');
    $totalWorker = $pertanian->workerJobs->sum('wage');
    
    $laba_sementara = $totalIncome - $totalPurchase - $totalWorker;
    
    echo "Pertanian: " . $pertanian->name . "\n";
    echo "Total Income: " . $totalIncome . "\n";
    echo "Total Purchase: " . $totalPurchase . "\n";
    echo "Total Worker: " . $totalWorker . "\n";
    echo "Laba Sementara: " . $laba_sementara . "\n";
    
    // hitungLabaSetelahZakat
    $labaBersih = $laba_sementara;
    $zakatPersen = $pertanian->persentase_zakat ?? 5;
    $zakat = $labaBersih > 0 ? $labaBersih * ($zakatPersen / 100) : 0;
    $labaSetelahZakat = $labaBersih - $zakat;
    
    echo "Laba Setelah Zakat: " . $labaSetelahZakat . "\n";
    
    if (!is_null($inv->porsi_bagi_hasil)) {
        $userProfitHome = $labaSetelahZakat * ($inv->porsi_bagi_hasil / 100);
        $userProfitIndex = $laba_sementara * ($inv->porsi_bagi_hasil / 100);
    } else {
        $persentaseInvestorTotal = $pertanian->persentase_investor ?? 0;
        $batasanInvestasi = $pertanian->batasan_investasi;
        if (empty($batasanInvestasi) || $batasanInvestasi <= 0) {
            $batasanInvestasi = \App\Models\PertanianInvestor::where('pertanian_id', $pertanian->id)->sum('besaran_investasi');
        }
        $proportion = $batasanInvestasi > 0 ? ($inv->besaran_investasi / $batasanInvestasi) : 0;
        $userProfitHome = $labaSetelahZakat * ($persentaseInvestorTotal / 100) * $proportion;
        $userProfitIndex = $laba_sementara * ($persentaseInvestorTotal / 100) * $proportion;
    }
    
    echo "User Profit (Home): " . $userProfitHome . "\n";
    echo "User Profit (Index): " . $userProfitIndex . "\n";
    echo "Porsi Bagi Hasil: " . $inv->porsi_bagi_hasil . "\n\n";
}
