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
        $targetPertanianIds = $userPertanianIds;
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
                    'qty' => (float) ($income->qty ?? 1),
                    'unit' => 'Kg',
                    'unit_price' => (float) ($income->unit_price ?? $income->amount),
                    'total' => (float) $income->amount,
                    'proof_url' => $income->transactionProof ? $income->transactionProof->url : '',
                    'notes' => $income->description ?? '-',
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
                    'qty' => 1.0,
                    'unit' => 'Pekerjaan',
                    'unit_price' => (float) $job->wage,
                    'total' => (float) $job->wage,
                    'proof_url' => $job->transactionProof ? $job->transactionProof->url : '',
                    'notes' => $job->description ?? '-',
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
                    'qty' => (float) ($item->qty ?? 1),
                    'unit' => 'Unit',
                    'unit_price' => (float) ($item->unit_price ?? $item->total_price),
                    'total' => (float) $item->total_price,
                    'proof_url' => $item->transactionProof ? $item->transactionProof->url : '',
                    'notes' => $item->description ?? '-',
                ]);
            }
        }

        // Sort Data by Date Descending
        $reportData = $reportData->sortByDesc('date')->values();

        // Calculate Totals
        $totalIncome = $reportData->where('type_code', 'income')->sum('total');
        $totalWorker = $reportData->where('type_code', 'worker_job')->sum('total');
        $totalPurchase = $reportData->where('type_code', 'purchase')->sum('total');
        $totalExpense = $totalWorker + $totalPurchase;
        $netCashflow = $totalIncome - $totalExpense;
        $totalRows = $reportData->count();

        $pertanians = $userPertanians;

        return view('report.index', compact(
            'reportData',
            'pertanians',
            'selectedPertanianId',
            'selectedType',
            'startDate',
            'endDate',
            'totalIncome',
            'totalWorker',
            'totalPurchase',
            'totalExpense',
            'netCashflow',
            'totalRows'
        ));
    }

    public function export(Request $request)
    {
        // Reuse logic to fetch data
        $indexRequest = $request;
        $userPertanians = Pertanian::where('user_id', Auth::id())->get();
        $userPertanianIds = $userPertanians->pluck('id')->toArray();

        $selectedPertanianId = $request->get('pertanian_id');
        $selectedType = $request->get('type', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $targetPertanianIds = $userPertanianIds;
        if (!empty($selectedPertanianId) && $selectedPertanianId !== 'all') {
            $targetPertanianIds = array_intersect([$selectedPertanianId], $userPertanianIds);
        }

        $reportData = collect();

        // Fetch Incomes
        if (in_array($selectedType, ['all', 'income'])) {
            $incomeQuery = Income::with(['pertanian', 'category', 'tengkulak'])
                ->whereIn('pertanian_id', $targetPertanianIds);

            if (!empty($startDate)) $incomeQuery->whereDate('date', '>=', $startDate);
            if (!empty($endDate)) $incomeQuery->whereDate('date', '<=', $endDate);

            foreach ($incomeQuery->get() as $income) {
                $reportData->push([
                    'type_label' => 'Pendapatan',
                    'date' => $income->date ? $income->date->format('Y-m-d') : '',
                    'pertanian_name' => $income->pertanian->name ?? '-',
                    'item_name' => $income->category->name ?? $income->description ?? 'Pendapatan',
                    'party_name' => $income->tengkulak->name ?? '-',
                    'qty' => (float) ($income->qty ?? 1),
                    'unit' => 'Kg',
                    'unit_price' => (float) ($income->unit_price ?? $income->amount),
                    'total' => (float) $income->amount,
                    'notes' => $income->description ?? '-',
                ]);
            }
        }

        // Fetch Upah Pekerja
        if (in_array($selectedType, ['all', 'worker_job'])) {
            $workerQuery = WorkerJob::with(['pertanian', 'worker', 'category'])
                ->whereIn('pertanian_id', $targetPertanianIds);

            if (!empty($startDate)) $workerQuery->whereDate('date', '>=', $startDate);
            if (!empty($endDate)) $workerQuery->whereDate('date', '<=', $endDate);

            foreach ($workerQuery->get() as $job) {
                $reportData->push([
                    'type_label' => 'Upah Pekerja',
                    'date' => $job->date ? \Carbon\Carbon::parse($job->date)->format('Y-m-d') : '',
                    'pertanian_name' => $job->pertanian->name ?? '-',
                    'item_name' => $job->category->name ?? $job->description ?? 'Upah Pekerja',
                    'party_name' => $job->worker->name ?? 'Pekerja',
                    'qty' => 1.0,
                    'unit' => 'Pekerjaan',
                    'unit_price' => (float) $job->wage,
                    'total' => (float) $job->wage,
                    'notes' => $job->description ?? '-',
                ]);
            }
        }

        // Fetch Pembelian
        if (in_array($selectedType, ['all', 'purchase'])) {
            $purchaseItemQuery = PurchaseItem::with(['purchase.pertanian', 'purchase.store', 'purchaseCategory'])
                ->whereHas('purchase', function ($q) use ($targetPertanianIds, $startDate, $endDate) {
                    $q->whereIn('pertanian_id', $targetPertanianIds);
                    if (!empty($startDate)) $q->whereDate('date', '>=', $startDate);
                    if (!empty($endDate)) $q->whereDate('date', '<=', $endDate);
                });

            foreach ($purchaseItemQuery->get() as $item) {
                $reportData->push([
                    'type_label' => 'Pembelian Material',
                    'date' => ($item->purchase && $item->purchase->date) ? $item->purchase->date->format('Y-m-d') : '',
                    'pertanian_name' => $item->purchase->pertanian->name ?? '-',
                    'item_name' => $item->purchaseCategory->name ?? $item->category ?? $item->description ?? 'Material',
                    'party_name' => $item->purchase->store->name ?? 'Toko',
                    'qty' => (float) ($item->qty ?? 1),
                    'unit' => 'Unit',
                    'unit_price' => (float) ($item->unit_price ?? $item->total_price),
                    'total' => (float) $item->total_price,
                    'notes' => $item->description ?? '-',
                ]);
            }
        }

        $reportData = $reportData->sortByDesc('date')->values();

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

        // Column Headers
        $headers = ['No', 'Jenis Transaksi', 'Tanggal', 'Proyek Pertanian', 'Kategori / Deskripsi', 'Pihak Terkait', 'Qty', 'Satuan', 'Harga Satuan (Rp)', 'Total Nominal (Rp)', 'Catatan'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];

        foreach ($headers as $index => $headerText) {
            $colLetter = $cols[$index];
            $sheet->setCellValue($colLetter . '1', $headerText);
        }
        $sheet->getStyle('A1:K1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Data Rows
        $rowNum = 2;
        foreach ($reportData as $idx => $row) {
            $sheet->setCellValue('A' . $rowNum, $idx + 1);
            $sheet->setCellValue('B' . $rowNum, $row['type_label']);
            $sheet->setCellValue('C' . $rowNum, $row['date']);
            $sheet->setCellValue('D' . $rowNum, $row['pertanian_name']);
            $sheet->setCellValue('E' . $rowNum, $row['item_name']);
            $sheet->setCellValue('F' . $rowNum, $row['party_name']);
            $sheet->setCellValue('G' . $rowNum, $row['qty']);
            $sheet->setCellValue('H' . $rowNum, $row['unit']);
            $sheet->setCellValue('I' . $rowNum, $row['unit_price']);
            $sheet->setCellValue('J' . $rowNum, $row['total']);
            $sheet->setCellValue('K' . $rowNum, $row['notes']);

            // Format numbers
            $sheet->getStyle('G' . $rowNum)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('I' . $rowNum)->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle('J' . $rowNum)->getNumberFormat()->setFormatCode('"Rp "#,##0');

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
}
