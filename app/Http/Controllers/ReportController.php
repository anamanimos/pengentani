<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Pertanian;
use App\Models\PurchaseItem;
use App\Models\WorkerJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $userPertanians = Pertanian::where('user_id', Auth::id())->get();
        $userPertanianIds = $userPertanians->pluck('id')->toArray();

        $selectedPertanianId = $request->get('pertanian_id');
        $selectedType = $request->get('type', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Target Pertanian IDs filter
        $targetPertanianIds = $userPertanians->pluck('id')->toArray();
        if (!empty($selectedPertanianId) && $selectedPertanianId !== 'all') {
            $targetPertanianIds = array_intersect([$selectedPertanianId], $userPertanianIds);
        }

        $reportData = collect();

        // 1. Fetch Incomes
        if (in_array($selectedType, ['all', 'income'])) {
            $incomeQuery = Income::with(['pertanian', 'category', 'tengkulak', 'transactionProof'])
                ->whereIn('pertanian_id', $targetPertanianIds);

            if (!empty($startDate)) {
                $incomeQuery->whereDate('date', '>=', $startDate);
            }
            if (!empty($endDate)) {
                $incomeQuery->whereDate('date', '<=', $endDate);
            }

            foreach ($incomeQuery->get() as $income) {
                $reportData->push([
                    'id' => 'INC-' . $income->id,
                    'raw_id' => $income->id,
                    'type_code' => 'income',
                    'type_label' => 'Pendapatan',
                    'date' => $income->date ? $income->date->format('Y-m-d') : '',
                    'pertanian_id' => $income->pertanian_id,
                    'pertanian_name' => $income->pertanian->name ?? '-',
                    'item_name' => $income->category->name ?? $income->description ?? 'Pendapatan',
                    'party_name' => $income->tengkulak->name ?? '-',
                    'notes' => $income->description ?? '-',
                    'qty' => (float) ($income->qty ?? 1),
                    'unit_price' => (float) ($income->unit_price ?? $income->amount),
                    'konsumsi' => 0.0,
                    'total' => (float) $income->amount,
                    'proof_id' => $income->transaction_proof_id,
                    'proof_url' => $income->transactionProof ? $income->transactionProof->url : '',
                ]);
            }
        }

        // 2. Fetch Upah Pekerja
        if (in_array($selectedType, ['all', 'worker_job'])) {
            $workerQuery = WorkerJob::with(['pertanian', 'worker', 'category', 'transactionProof'])
                ->whereIn('pertanian_id', $targetPertanianIds);

            if (!empty($startDate)) {
                $workerQuery->whereDate('date', '>=', $startDate);
            }
            if (!empty($endDate)) {
                $workerQuery->whereDate('date', '<=', $endDate);
            }

            foreach ($workerQuery->get() as $job) {
                $reportData->push([
                    'id' => 'JOB-' . $job->id,
                    'raw_id' => $job->id,
                    'type_code' => 'worker_job',
                    'type_label' => 'Upah Pekerja',
                    'date' => $job->date ? \Carbon\Carbon::parse($job->date)->format('Y-m-d') : '',
                    'pertanian_id' => $job->pertanian_id,
                    'pertanian_name' => $job->pertanian->name ?? '-',
                    'item_name' => $job->category->name ?? $job->description ?? 'Upah Pekerja',
                    'party_name' => $job->worker->name ?? 'Pekerja',
                    'notes' => $job->description ?? '-',
                    'qty' => 1.0,
                    'unit_price' => (float) $job->wage,
                    'konsumsi' => (float) ($job->konsumsi ?? 0),
                    'total' => (float) ($job->wage + ($job->konsumsi ?? 0)),
                    'proof_id' => $job->transaction_proof_id,
                    'proof_url' => $job->transactionProof ? $job->transactionProof->url : '',
                ]);
            }
        }

        // 3. Fetch Pembelian
        if (in_array($selectedType, ['all', 'purchase'])) {
            $purchaseItemQuery = PurchaseItem::with(['purchase.pertanian', 'purchase.store', 'purchaseCategory', 'transactionProof'])
                ->whereHas('purchase', function ($q) use ($targetPertanianIds, $startDate, $endDate) {
                    $q->whereIn('pertanian_id', $targetPertanianIds);
                    if (!empty($startDate)) {
                        $q->whereDate('date', '>=', $startDate);
                    }
                    if (!empty($endDate)) {
                        $q->whereDate('date', '<=', $endDate);
                    }
                });

            foreach ($purchaseItemQuery->get() as $item) {
                $reportData->push([
                    'id' => 'PUR-' . $item->id,
                    'raw_id' => $item->id,
                    'type_code' => 'purchase',
                    'type_label' => 'Pembelian Material',
                    'date' => ($item->purchase && $item->purchase->date) ? $item->purchase->date->format('Y-m-d') : '',
                    'pertanian_id' => $item->purchase->pertanian_id ?? null,
                    'pertanian_name' => $item->purchase->pertanian->name ?? '-',
                    'item_name' => $item->purchaseCategory->name ?? $item->category ?? $item->description ?? 'Material',
                    'party_name' => $item->purchase->store->name ?? 'Toko',
                    'notes' => $item->description ?? '-',
                    'qty' => (float) ($item->qty ?? 1),
                    'unit_price' => (float) ($item->unit_price ?? $item->total_price),
                    'konsumsi' => 0.0,
                    'total' => (float) $item->total_price,
                    'proof_id' => $item->transaction_proof_id,
                    'proof_url' => $item->transactionProof ? $item->transactionProof->url : '',
                ]);
            }
        }

        // Sort Data by Date Descending
        $reportData = $reportData->sortByDesc('date')->values();

        // Default current month start & end dates for frontend filtering
        $defaultStartDate = now()->startOfMonth()->format('Y-m-d');
        $defaultEndDate = now()->endOfMonth()->format('Y-m-d');

        // Calculate Totals
        $totalIncome = $reportData->where('type_code', 'income')->sum('total');
        $totalWorker = $reportData->where('type_code', 'worker_job')->sum('total');
        $totalKonsumsi = $reportData->sum('konsumsi');
        $totalPurchase = $reportData->where('type_code', 'purchase')->sum('total');
        $totalExpense = $totalWorker + $totalPurchase;
        $netCashflow = $totalIncome - $totalExpense;
        $totalRows = $reportData->count();

        $pertanians = $userPertanians;
        $proofs = \App\Models\TransactionProof::where('user_id', Auth::id())->latest()->get();

        return view('report.index', compact(
            'reportData',
            'pertanians',
            'proofs',
            'selectedPertanianId',
            'selectedType',
            'startDate',
            'endDate',
            'defaultStartDate',
            'defaultEndDate',
            'totalIncome',
            'totalWorker',
            'totalKonsumsi',
            'totalPurchase',
            'totalExpense',
            'netCashflow',
            'totalRows'
        ));
    }

    public function export(Request $request)
    {
        $reportData = collect();

        if ($request->filled('filtered_data')) {
            $clientData = json_decode($request->get('filtered_data'), true);
            if (is_array($clientData)) {
                foreach ($clientData as $item) {
                    $reportData->push([
                        'date' => $item['date'] ?? '',
                        'type_label' => $item['type_label'] ?? 'Transaksi',
                        'pertanian_name' => $item['pertanian_name'] ?? '-',
                        'item_name' => $item['item_name'] ?? '-',
                        'party_name' => $item['party_name'] ?? '-',
                        'notes' => $item['notes'] ?? '-',
                        'qty' => (float) ($item['qty'] ?? 1),
                        'unit_price' => (float) ($item['unit_price'] ?? 0),
                        'konsumsi' => (float) ($item['konsumsi'] ?? 0),
                        'total' => (float) ($item['total'] ?? 0),
                        'proof_name' => !empty($item['proof_url']) ? 'Ada Bukti' : '-',
                    ]);
                }
            }
        }

        if ($reportData->isEmpty()) {
            $userPertanians = Pertanian::where('user_id', Auth::id())->get();
            $userPertanianIds = $userPertanians->pluck('id')->toArray();

            $selectedPertanianId = $request->get('pertanian_id');
            $selectedType = $request->get('type', 'all');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $targetPertanianIds = $userPertanians->pluck('id')->toArray();
            if (!empty($selectedPertanianId) && $selectedPertanianId !== 'all') {
                $targetPertanianIds = array_intersect([$selectedPertanianId], $userPertanianIds);
            }

            // Fetch Incomes
            if (in_array($selectedType, ['all', 'income'])) {
                $incomeQuery = Income::with(['pertanian', 'category', 'tengkulak'])
                    ->whereIn('pertanian_id', $targetPertanianIds);

                if (!empty($startDate)) $incomeQuery->whereDate('date', '>=', $startDate);
                if (!empty($endDate)) $incomeQuery->whereDate('date', '<=', $endDate);

                foreach ($incomeQuery->get() as $income) {
                    $reportData->push([
                        'date' => $income->date ? $income->date->format('Y-m-d') : '',
                        'type_label' => 'Pendapatan',
                        'pertanian_name' => $income->pertanian->name ?? '-',
                        'item_name' => $income->category->name ?? $income->description ?? 'Pendapatan',
                        'party_name' => $income->tengkulak->name ?? '-',
                        'notes' => $income->description ?? '-',
                        'qty' => (float) ($income->qty ?? 1),
                        'unit_price' => (float) ($income->unit_price ?? $income->amount),
                        'konsumsi' => 0.0,
                        'total' => (float) $income->amount,
                        'proof_name' => $income->transactionProof ? $income->transactionProof->name : '-',
                    ]);
                }
            }

            // Fetch Upah Pekerja
            if (in_array($selectedType, ['all', 'worker_job'])) {
                $workerQuery = WorkerJob::with(['pertanian', 'worker', 'category', 'transactionProof'])
                    ->whereIn('pertanian_id', $targetPertanianIds);

                if (!empty($startDate)) $workerQuery->whereDate('date', '>=', $startDate);
                if (!empty($endDate)) $workerQuery->whereDate('date', '<=', $endDate);

                foreach ($workerQuery->get() as $job) {
                    $reportData->push([
                        'date' => $job->date ? \Carbon\Carbon::parse($job->date)->format('Y-m-d') : '',
                        'type_label' => 'Upah Pekerja',
                        'pertanian_name' => $job->pertanian->name ?? '-',
                        'item_name' => $job->category->name ?? $job->description ?? 'Upah Pekerja',
                        'party_name' => $job->worker->name ?? 'Pekerja',
                        'notes' => $job->description ?? '-',
                        'qty' => 1.0,
                        'unit_price' => (float) $job->wage,
                        'konsumsi' => (float) ($job->konsumsi ?? 0),
                        'total' => (float) ($job->wage + ($job->konsumsi ?? 0)),
                        'proof_name' => $job->transactionProof ? $job->transactionProof->name : '-',
                    ]);
                }
            }

            // Fetch Pembelian
            if (in_array($selectedType, ['all', 'purchase'])) {
                $purchaseItemQuery = PurchaseItem::with(['purchase.pertanian', 'purchase.store', 'purchaseCategory', 'transactionProof'])
                    ->whereHas('purchase', function ($q) use ($targetPertanianIds, $startDate, $endDate) {
                        $q->whereIn('pertanian_id', $targetPertanianIds);
                        if (!empty($startDate)) $q->whereDate('date', '>=', $startDate);
                        if (!empty($endDate)) $q->whereDate('date', '<=', $endDate);
                    });

                foreach ($purchaseItemQuery->get() as $item) {
                    $reportData->push([
                        'date' => ($item->purchase && $item->purchase->date) ? $item->purchase->date->format('Y-m-d') : '',
                        'type_label' => 'Pembelian Material',
                        'pertanian_name' => $item->purchase->pertanian->name ?? '-',
                        'item_name' => $item->purchaseCategory->name ?? $item->category ?? $item->description ?? 'Material',
                        'party_name' => $item->purchase->store->name ?? 'Toko',
                        'notes' => $item->description ?? '-',
                        'qty' => (float) ($item->qty ?? 1),
                        'unit_price' => (float) ($item->unit_price ?? $item->total_price),
                        'konsumsi' => 0.0,
                        'total' => (float) $item->total_price,
                        'proof_name' => $item->transactionProof ? $item->transactionProof->name : '-',
                    ]);
                }
            }

            $reportData = $reportData->sortByDesc('date')->values();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Gabungan');

        // Header Styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E1E2D'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];

        $headers = ['No', 'Tanggal', 'Jenis Transaksi', 'Proyek Pertanian', 'Kategori', 'Pihak Terkait', 'Catatan', 'Qty', 'Satuan / Upah (Rp)', 'Konsumsi (Rp)', 'Total (Rp)', 'Bukti Transaksi'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];

        foreach ($headers as $index => $headerText) {
            $colLetter = $cols[$index];
            $sheet->setCellValue($colLetter . '1', $headerText);
        }
        $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Data Rows
        $rowNum = 2;
        foreach ($reportData as $idx => $row) {
            $sheet->setCellValue('A' . $rowNum, $idx + 1);
            $sheet->setCellValue('B' . $rowNum, $row['date']);
            $sheet->setCellValue('C' . $rowNum, $row['type_label']);
            $sheet->setCellValue('D' . $rowNum, $row['pertanian_name']);
            $sheet->setCellValue('E' . $rowNum, $row['item_name']);
            $sheet->setCellValue('F' . $rowNum, $row['party_name']);
            $sheet->setCellValue('G' . $rowNum, $row['notes']);
            $sheet->setCellValue('H' . $rowNum, $row['qty']);
            $sheet->setCellValue('I' . $rowNum, $row['unit_price']);
            $sheet->setCellValue('J' . $rowNum, $row['konsumsi']);
            $sheet->setCellValue('K' . $rowNum, $row['total']);
            $sheet->setCellValue('L' . $rowNum, $row['proof_name']);

            // Format numbers
            $sheet->getStyle('H' . $rowNum)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('I' . $rowNum)->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle('J' . $rowNum)->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle('K' . $rowNum)->getNumberFormat()->setFormatCode('"Rp "#,##0');

            $rowNum++;
        }

        // Auto column widths
        foreach ($cols as $colLetter) {
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Laporan_Gabungan_PengenTani_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function exportPdf(Request $request)
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '300');

        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $reportData = collect();

        // 1. Check if client sent visible filtered table rows directly
        if ($request->filled('filtered_data')) {
            $clientData = json_decode($request->get('filtered_data'), true);
            if (is_array($clientData)) {
                foreach ($clientData as $item) {
                    $typeLabel = $item['type_label'] ?? 'Transaksi';
                    $typeCode = 'purchase';
                    if (str_contains(strtolower($typeLabel), 'pendapatan')) {
                        $typeCode = 'income';
                    } else if (str_contains(strtolower($typeLabel), 'upah')) {
                        $typeCode = 'worker_job';
                    }

                    $reportData->push([
                        'type_code' => $typeCode,
                        'type_label' => $typeLabel,
                        'date' => $item['date'] ?? '',
                        'pertanian_name' => $item['pertanian_name'] ?? '-',
                        'item_name' => $item['item_name'] ?? '-',
                        'party_name' => $item['party_name'] ?? '-',
                        'notes' => $item['notes'] ?? '-',
                        'qty' => (float) ($item['qty'] ?? 1),
                        'unit_price' => (float) ($item['unit_price'] ?? 0),
                        'konsumsi' => (float) ($item['konsumsi'] ?? 0),
                        'total' => (float) ($item['total'] ?? 0),
                        'proof_url' => $item['proof_url'] ?? '',
                    ]);
                }
            }
        }

        // Fallback: If no client filtered data sent, query database
        if ($reportData->isEmpty()) {
            $userPertanians = Pertanian::where('user_id', Auth::id())->get();
            $userPertanianIds = $userPertanians->pluck('id')->toArray();

            $selectedPertanianId = $request->get('pertanian_id');
            $selectedType = $request->get('type', 'all');
            $startDate = $request->filled('start_date') ? $request->get('start_date') : now()->startOfMonth()->format('Y-m-d');
            $endDate = $request->filled('end_date') ? $request->get('end_date') : now()->endOfMonth()->format('Y-m-d');

            $targetPertanianIds = $userPertanians->pluck('id')->toArray();
            if (!empty($selectedPertanianId) && $selectedPertanianId !== 'all') {
                $targetPertanianIds = array_intersect([$selectedPertanianId], $userPertanianIds);
            }

            $formatProofUrl = function($proof) {
                if (!$proof || empty($proof->url)) return '';
                $url = $proof->url;
                if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
                    return $url;
                }
                return url($url);
            };

            // Fetch Incomes
            if (in_array($selectedType, ['all', 'income'])) {
                $incomeQuery = Income::with(['pertanian', 'category', 'tengkulak', 'transactionProof'])
                    ->whereIn('pertanian_id', $targetPertanianIds);

                if (!empty($startDate)) $incomeQuery->whereDate('date', '>=', $startDate);
                if (!empty($endDate)) $incomeQuery->whereDate('date', '<=', $endDate);

                foreach ($incomeQuery->get() as $income) {
                    $reportData->push([
                        'type_code' => 'income',
                        'type_label' => 'Pendapatan',
                        'date' => $income->date ? $income->date->format('Y-m-d') : '',
                        'pertanian_name' => $income->pertanian->name ?? '-',
                        'item_name' => $income->category->name ?? $income->description ?? 'Pendapatan',
                        'party_name' => $income->tengkulak->name ?? '-',
                        'notes' => $income->description ?? '-',
                        'qty' => (float) ($income->qty ?? 1),
                        'unit_price' => (float) ($income->unit_price ?? $income->amount),
                        'konsumsi' => 0.0,
                        'total' => (float) $income->amount,
                        'proof_url' => $formatProofUrl($income->transactionProof),
                    ]);
                }
            }

            // Fetch Upah Pekerja
            if (in_array($selectedType, ['all', 'worker_job'])) {
                $workerQuery = WorkerJob::with(['pertanian', 'worker', 'category', 'transactionProof'])
                    ->whereIn('pertanian_id', $targetPertanianIds);

                if (!empty($startDate)) $workerQuery->whereDate('date', '>=', $startDate);
                if (!empty($endDate)) $workerQuery->whereDate('date', '<=', $endDate);

                foreach ($workerQuery->get() as $job) {
                    $reportData->push([
                        'type_code' => 'worker_job',
                        'type_label' => 'Upah Pekerja',
                        'date' => $job->date ? \Carbon\Carbon::parse($job->date)->format('Y-m-d') : '',
                        'pertanian_name' => $job->pertanian->name ?? '-',
                        'item_name' => $job->category->name ?? $job->description ?? 'Upah Pekerja',
                        'party_name' => $job->worker->name ?? 'Pekerja',
                        'notes' => $job->description ?? '-',
                        'qty' => 1.0,
                        'unit_price' => (float) $job->wage,
                        'konsumsi' => (float) ($job->konsumsi ?? 0),
                        'total' => (float) ($job->wage + ($job->konsumsi ?? 0)),
                        'proof_url' => $formatProofUrl($job->transactionProof),
                    ]);
                }
            }

            // Fetch Pembelian
            if (in_array($selectedType, ['all', 'purchase'])) {
                $purchaseItemQuery = PurchaseItem::with(['purchase.pertanian', 'purchase.store', 'purchaseCategory', 'transactionProof'])
                    ->whereHas('purchase', function ($q) use ($targetPertanianIds, $startDate, $endDate) {
                        $q->whereIn('pertanian_id', $targetPertanianIds);
                        if (!empty($startDate)) $q->whereDate('date', '>=', $startDate);
                        if (!empty($endDate)) $q->whereDate('date', '<=', $endDate);
                    });

                foreach ($purchaseItemQuery->get() as $item) {
                    $reportData->push([
                        'type_code' => 'purchase',
                        'type_label' => 'Pembelian Material',
                        'date' => ($item->purchase && $item->purchase->date) ? $item->purchase->date->format('Y-m-d') : '',
                        'pertanian_name' => $item->purchase->pertanian->name ?? '-',
                        'item_name' => $item->purchaseCategory->name ?? $item->category ?? $item->description ?? 'Material',
                        'party_name' => $item->purchase->store->name ?? 'Toko',
                        'notes' => $item->description ?? '-',
                        'qty' => (float) ($item->qty ?? 1),
                        'unit_price' => (float) ($item->unit_price ?? $item->total_price),
                        'konsumsi' => 0.0,
                        'total' => (float) $item->total_price,
                        'proof_url' => $formatProofUrl($item->transactionProof),
                    ]);
                }
            }
        }

        $reportData = $reportData->sortByDesc('date')->values();

        if ($reportData->isNotEmpty()) {
            $dates = $reportData->pluck('date')->filter()->sort()->values();
            if ($dates->isNotEmpty()) {
                if (empty($startDate)) $startDate = $dates->first();
                if (empty($endDate)) $endDate = $dates->last();
            }
        }

        $totalIncome = $reportData->where('type_code', 'income')->sum('total');
        $totalWorker = $reportData->where('type_code', 'worker_job')->sum('total');
        $totalKonsumsi = $reportData->sum('konsumsi');
        $totalPurchase = $reportData->where('type_code', 'purchase')->sum('total');
        $totalExpense = $totalWorker + $totalPurchase;
        $netCashflow = $totalIncome - $totalExpense;

        try {
            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('report.pdf', compact(
                    'reportData',
                    'startDate',
                    'endDate',
                    'totalIncome',
                    'totalPurchase',
                    'totalWorker',
                    'totalKonsumsi',
                    'totalExpense',
                    'netCashflow'
                ))->setPaper('a4', 'landscape')
                  ->setOption('isRemoteEnabled', false)
                  ->setOption('isHtml5ParserEnabled', true);

                $filename = 'Laporan_Gabungan_PengenTani_' . date('Ymd_His') . '.pdf';
                return $pdf->stream($filename, ['Attachment' => false]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('PDF Export Error: ' . $e->getMessage());
        }

        // Fallback: render printable HTML view
        return view('report.pdf', compact(
            'reportData',
            'startDate',
            'endDate',
            'totalIncome',
            'totalPurchase',
            'totalWorker',
            'totalKonsumsi',
            'totalExpense',
            'netCashflow'
        ));
    }
}
