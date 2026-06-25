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
        $pertanians = Pertanian::where('user_id', Auth::id())->with('kebun')->orderBy('name')->get();
        $categories = \App\Models\PurchaseCategory::orderBy('name')->get();
        $stores = \App\Models\Store::orderBy('name')->get();

        $query = Purchase::whereHas('pertanian', function ($q) {
            $q->where('user_id', Auth::id());
        })->with(['pertanian', 'items', 'store']);

        if ($request->filled('pertanian_id')) {
            $query->where('pertanian_id', $request->pertanian_id);
        }

        $purchases = $query->orderBy('id', 'asc')->get();
        
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
                $pertanian = Pertanian::where('user_id', Auth::id())
                    ->where('name', 'like', '%' . trim($pertanianId) . '%')
                    ->first();
                if (!$pertanian) {
                    return response()->json(['message' => 'Pertanian "' . $pertanianId . '" tidak ditemukan. Pastikan namanya sama dengan yang ada di sistem.'], 422);
                }
            } else {
                $pertanian = Pertanian::find($pertanianId);
                if (!$pertanian || $pertanian->user_id !== Auth::id()) {
                    return response()->json(['message' => 'Pertanian tidak valid.'], 422);
                }
            }

            $date = $row['date'];
            $invoiceNumber = $row['invoice_number'] ?? '-';
            $storeId = $row['store_id'] ?? null;
            
            // Create new store if user typed a string instead of selecting an existing ID
            if (!empty($storeId) && !is_numeric($storeId)) {
                $newStore = \App\Models\Store::firstOrCreate([
                    'name' => trim($storeId)
                ]);
                $storeId = $newStore->id;
            } else {
                $storeId = $storeId ?: null;
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
            
            if (!empty($catId) && !is_numeric($catId)) {
                $newCat = \App\Models\PurchaseCategory::firstOrCreate([
                    'name' => trim($catId)
                ]);
                $catId = $newCat->id;
                $categoryName = $newCat->name;
            } else {
                $category = \App\Models\PurchaseCategory::find($catId);
                $categoryName = $category ? $category->name : 'Lain-lain';
                $catId = $catId ?: null;
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

    public function getDropdownsAjax()
    {
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
        $store = \App\Models\Store::firstOrCreate(['name' => trim($request->name)]);
        return response()->json(['id' => $store->id, 'name' => $store->name]);
    }

    public function storeCategoryAjax(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $cat = \App\Models\PurchaseCategory::firstOrCreate(['name' => trim($request->name)]);
        return response()->json(['id' => $cat->id, 'name' => $cat->name]);
    }
}
