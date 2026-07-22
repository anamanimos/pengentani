<?php

namespace App\Http\Controllers;

use App\Models\Pertanian;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $this->cleanUpCorruptRecords();

        $pertanians = Pertanian::where('user_id', Auth::id())->with('kebun')->orderBy('name')->get();
        $categories = \App\Models\PurchaseCategory::orderBy('name')->get();
        $stores = \App\Models\Store::orderBy('name')->get();

        $query = Purchase::whereHas('pertanian', function ($q) {
            $q->where('user_id', Auth::id());
        })->with(['pertanian', 'items', 'store']);

        if ($request->filled('pertanian_id')) {
            $query->where('pertanian_id', $request->pertanian_id);
        }

        // Removed backend month filter to allow frontend date filtering
        // Add take(500) limit to prevent browser crash
        $purchases = $query->orderBy('id', 'asc')->take(500)->get();
        
        $initialData = [];
        $totalPengeluaran = 0;

        foreach ($purchases as $p) {
            foreach ($p->items as $item) {
                $initialData[] = [
                    $item->id,                  // 0: Item ID
                    $p->date ? \Carbon\Carbon::parse($p->date)->format('Y-m-d') : null,                   // 1: Tanggal
                    $p->pertanian_id,           // 2: Pertanian
                    $p->store_id,               // 3: Toko / Vendor
                    $item->purchase_category_id,// 4: Kategori Barang
                    $item->description,         // 5: Deskripsi
                    (float) $item->qty,                 // 6: Qty
                    (float) $item->unit_price,          // 7: Harga Satuan
                    (float) $item->total_price,         // 8: Total (Read-only view)
                    $item->transaction_proof_id         // 9: Bukti Transaksi
                ];
                $totalPengeluaran += $item->total_price;
            }
        }

        $proofs = \App\Models\TransactionProof::where('user_id', \Illuminate\Support\Facades\Auth::id())->orderBy('name')->get();

        return view('purchases.index', compact('initialData', 'pertanians', 'categories', 'stores', 'totalPengeluaran', 'proofs'));
    }

    public function store(Request $request)
    {
        $data = $request->input('data');
        \Illuminate\Support\Facades\Log::info('Purchase store received data: ', ['data' => $data]);
        if (!$data || !is_array($data)) return response()->json(['message' => 'No data'], 400);

        $savedData = [];
        $validPurchaseIds = [];

        foreach ($data as $index => $row) {
            \Illuminate\Support\Facades\Log::info("Processing row $index", ['row' => $row]);
            // Skip invalid data
            if (empty($row['pertanian_id']) || empty($row['date'])) continue;

            $pertanianId = $row['pertanian_id'];
            if (!is_numeric($pertanianId)) {
                $searchName = trim($pertanianId);
                if (preg_match('/\]\s*-\s*(.*)/', $searchName, $matches)) {
                    $searchName = trim($matches[1]);
                }
                $pertanian = Pertanian::where('user_id', Auth::id())
                    ->where('name', 'like', '%' . $searchName . '%')
                    ->first();
                if (!$pertanian) {
                    return response()->json(['error' => 'Pertanian tidak ditemukan: ' . htmlspecialchars(substr($row['pertanian_id'], 0, 50))], 422);
                }
                $pertanianId = $pertanian->id;
            } else {
                $pertanian = Pertanian::find($pertanianId);
                if (!$pertanian || $pertanian->user_id !== Auth::id()) {
                    return response()->json(['message' => 'Pertanian tidak valid.'], 422);
                }
                $pertanianId = $pertanian->id;
            }

            $date = $row['date'];
            $invoiceNumber = $row['invoice_number'] ?? '-';
            $storeId = $row['store_id'] ?? null;
            
            // Create or resolve store with strict sanitization
            if (!empty($storeId)) {
                $storeStr = trim((string)$storeId);
                if (
                    $storeStr === 'NEW_STORE' ||
                    str_contains($storeStr, '+ Tambah') ||
                    str_contains($storeStr, 'NEW_STORE') ||
                    str_contains($storeStr, '...') ||
                    strlen($storeStr) > 60
                ) {
                    $storeId = null;
                } elseif (!is_numeric($storeStr)) {
                    $existingStore = \App\Models\Store::where('name', $storeStr)->first();
                    if ($existingStore) {
                        $storeId = $existingStore->id;
                    } else {
                        $newStore = \App\Models\Store::create(['name' => $storeStr]);
                        $storeId = $newStore->id;
                    }
                } else {
                    $storeId = (int)$storeStr;
                }
            } else {
                $storeId = null;
            }
            
            // Find or create Purchase (Nota)
            $purchase = Purchase::firstOrCreate(
                [
                    'pertanian_id' => $pertanian->id,
                    'store_id' => $storeId,
                    'invoice_number' => $invoiceNumber,
                    'date' => $date,
                ],
                ['total_amount' => 0]
            );

            $validPurchaseIds[$purchase->id] = $purchase;

            $qtyStr = str_replace(',', '', $row['qty']);
            $qty = (float) $qtyStr;
            $unitPriceStr = str_replace(',', '', $row['unit_price']);
            $unitPrice = (float) $unitPriceStr;
            $totalPrice = $qty * $unitPrice;

            $catId = $row['category_id'] ?? null;
            
            if (!empty($catId)) {
                $catStr = trim((string)$catId);
                if (
                    $catStr === 'NEW_CATEGORY' ||
                    str_contains($catStr, '+ Tambah') ||
                    str_contains($catStr, 'NEW_CATEGORY') ||
                    str_contains($catStr, '...') ||
                    strlen($catStr) > 60
                ) {
                    $catId = null;
                    $categoryName = 'Lain-lain';
                } elseif (!is_numeric($catStr)) {
                    $existingCat = \App\Models\PurchaseCategory::where('name', $catStr)->first();
                    if ($existingCat) {
                        $catId = $existingCat->id;
                        $categoryName = $existingCat->name;
                    } else {
                        $newCat = \App\Models\PurchaseCategory::create(['name' => $catStr]);
                        $catId = $newCat->id;
                        $categoryName = $newCat->name;
                    }
                } else {
                    $category = \App\Models\PurchaseCategory::find((int)$catStr);
                    $categoryName = $category ? $category->name : 'Lain-lain';
                    $catId = $category ? $category->id : null;
                }
            } else {
                $catId = null;
                $categoryName = 'Lain-lain';
            }

            if (!empty($row['id'])) {
                $item = PurchaseItem::find($row['id']);
                if ($item && $item->purchase->pertanian->user_id === Auth::id()) {
                    $oldPurchaseId = $item->purchase_id;
                    $item->update([
                        'purchase_id' => $purchase->id,
                        'purchase_category_id' => $catId,
                        'category' => $categoryName,
                        'description' => $row['description'] ?? '-',
                        'qty' => $qty,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                        'transaction_proof_id' => current(array_filter([$row['transaction_proof_id'] ?? null, $item->transaction_proof_id])) ?: null,
                    ]);
                    if ($oldPurchaseId != $purchase->id) {
                        $oldPurchase = \App\Models\Purchase::find($oldPurchaseId);
                        if ($oldPurchase) {
                            $validPurchaseIds[$oldPurchase->id] = $oldPurchase;
                        }
                    }
                    $savedData[] = ['index' => $row['index'], 'id' => $item->id];
                    \Illuminate\Support\Facades\Log::info("Updated item: ", ['id' => $item->id, 'qty' => $qty, 'price' => $unitPrice]);
                } else {
                    \Illuminate\Support\Facades\Log::warning("Item not found or unauthorized", ['id' => $row['id']]);
                }
            } else {
                $item = $purchase->items()->create([
                    'purchase_category_id' => $catId,
                    'category' => $categoryName,
                    'description' => $row['description'] ?? '-',
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'transaction_proof_id' => $row['transaction_proof_id'] ?? null,
                ]);
                $savedData[] = ['index' => $row['index'], 'id' => $item->id];
                \Illuminate\Support\Facades\Log::info("Created new item: ", ['id' => $item->id, 'purchase_id' => $purchase->id, 'qty' => $qty, 'price' => $unitPrice]);
            }
        }

        \Illuminate\Support\Facades\Log::info("Finished processing rows. Valid purchases count: " . count($validPurchaseIds));

        foreach ($validPurchaseIds as $p) {
            $itemCount = $p->items()->count();
            \Illuminate\Support\Facades\Log::info("Checking purchase ID " . $p->id . " - Item count: " . $itemCount);
            if ($itemCount === 0) {
                \Illuminate\Support\Facades\Log::info("Deleting purchase ID " . $p->id . " because it has 0 items.");
                $p->delete();
            } else {
                $sum = $p->items()->sum('total_price');
                \Illuminate\Support\Facades\Log::info("Updating purchase ID " . $p->id . " total_amount to " . $sum);
                $p->update(['total_amount' => $sum]);
            }
        }

        return response()->json(['message' => 'Tersimpan otomatis', 'savedData' => $savedData]);
    }

    public function destroy($id)
    {
        $item = PurchaseItem::find($id);
        if ($item) {
            $purchase = $item->purchase;
            if ($purchase->pertanian->user_id !== Auth::id()) abort(403);
            $item->delete();
            
            // If purchase has no items left, delete purchase
            if ($purchase->items()->count() === 0) {
                $purchase->delete();
            } else {
                $purchase->update(['total_amount' => $purchase->items()->sum('total_price')]);
            }
        }
        return response()->json(['message' => 'Deleted']);
    }

    private function cleanUpCorruptRecords()
    {
        try {
            \App\Models\Store::where('name', 'like', '%+ Tambah%')
                ->orWhere('name', 'like', '%NEW_STORE%')
                ->orWhere('name', 'like', '%...%')
                ->orWhereRaw('LENGTH(name) > 60')
                ->delete();

            \App\Models\PurchaseCategory::where('name', 'like', '%+ Tambah%')
                ->orWhere('name', 'like', '%NEW_CATEGORY%')
                ->orWhere('name', 'like', '%...%')
                ->orWhereRaw('LENGTH(name) > 60')
                ->delete();
        } catch (\Exception $e) {
            // Silence exceptions during cleanup
        }
    }

    public function getDropdownsAjax()
    {
        $this->cleanUpCorruptRecords();

        $stores = \App\Models\Store::orderBy('name')->get(['id', 'name']);
        $categories = \App\Models\PurchaseCategory::orderBy('name')->get(['id', 'name']);
        
        $proofs = \App\Models\TransactionProof::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->orderBy('name')
            ->get()
            ->map(function($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'url' => \Illuminate\Support\Facades\Storage::url($p->file_path)
                ];
            });

        return response()->json([
            'stores' => $stores,
            'categories' => $categories,
            'proofs' => $proofs
        ]);
    }

    public function storeStoreAjax(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $name = trim($request->name);
        if ($name === 'NEW_STORE' || str_contains($name, '+ Tambah') || str_contains($name, '...') || strlen($name) > 60) {
            return response()->json(['error' => 'Nama toko tidak valid.'], 422);
        }
        $store = \App\Models\Store::firstOrCreate(['name' => $name]);
        return response()->json(['id' => $store->id, 'name' => $store->name]);
    }

    public function storeCategoryAjax(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $name = trim($request->name);
        if ($name === 'NEW_CATEGORY' || str_contains($name, '+ Tambah') || str_contains($name, '...') || strlen($name) > 60) {
            return response()->json(['error' => 'Nama kategori tidak valid.'], 422);
        }
        $cat = \App\Models\PurchaseCategory::firstOrCreate(['name' => $name]);
        return response()->json(['id' => $cat->id, 'name' => $cat->name]);
    }

    public function export(Request $request)
    {
        $query = Purchase::whereHas('pertanian', function ($q) {
            $q->where('user_id', Auth::id());
        })->with(['pertanian', 'items.category', 'store']);

        if ($request->filled('pertanian_id')) {
            $query->where('pertanian_id', $request->pertanian_id);
        }

        $purchases = $query->orderBy('id', 'asc')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Tanggal');
        $sheet->setCellValue('C1', 'Pertanian');
        $sheet->setCellValue('D1', 'Toko / Vendor');
        $sheet->setCellValue('E1', 'Kategori Barang');
        $sheet->setCellValue('F1', 'Nama Barang / Deskripsi');
        $sheet->setCellValue('G1', 'Qty');
        $sheet->setCellValue('H1', 'Harga Satuan (Rp)');
        $sheet->setCellValue('I1', 'Total (Rp)');

        $rowNum = 2;
        $index = 1;
        foreach ($purchases as $p) {
            foreach ($p->items as $item) {
                $sheet->setCellValue('A' . $rowNum, $index);
                $sheet->setCellValue('B' . $rowNum, $p->date ? \Carbon\Carbon::parse($p->date)->format('Y-m-d') : '-');
                $sheet->setCellValue('C' . $rowNum, $p->pertanian->name ?? '-');
                $sheet->setCellValue('D' . $rowNum, $p->store->name ?? '-');
                $sheet->setCellValue('E' . $rowNum, $item->category->name ?? '-');
                $sheet->setCellValue('F' . $rowNum, $item->description ?? '-');
                $sheet->setCellValue('G' . $rowNum, (float) $item->qty);
                $sheet->setCellValue('H' . $rowNum, (float) $item->unit_price);
                $sheet->setCellValue('I' . $rowNum, (float) $item->total_price);
                $rowNum++;
                $index++;
            }
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Laporan_Pembelian_' . date('Ymd_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        $writer->save('php://output');
        exit;
    }
}
