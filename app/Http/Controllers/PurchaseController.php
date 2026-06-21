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
                    $p->invoice_number,         // 4: No Nota
                    $item->purchase_category_id,// 5: Kategori Barang
                    $item->description,         // 6: Deskripsi
                    (float) $item->qty,                 // 7: Qty
                    (float) $item->unit_price,          // 8: Harga Satuan
                    (float) $item->total_price,         // 9: Total (Read-only view)
                ];
                $totalPengeluaran += $item->total_price;
            }
        }

        return view('purchases.index', compact('initialData', 'pertanians', 'categories', 'stores', 'totalPengeluaran'));
    }

    public function store(Request $request)
    {
        $data = $request->input('data');
        if (!$data || !is_array($data)) return response()->json(['message' => 'No data'], 400);

        $savedData = [];
        $validPurchaseIds = [];

        foreach ($data as $index => $row) {
            // Skip invalid data
            if (empty($row['pertanian_id']) || empty($row['date'])) continue;

            $pertanian = Pertanian::find($row['pertanian_id']);
            if (!$pertanian || $pertanian->user_id !== Auth::id()) continue;

            $date = $row['date'];
            $storeId = $row['store_id'];
            $invoiceNumber = $row['invoice_number'];
            
            // Create or update store if needed
            // If storeId is a string and not empty, but not numeric, it might be a new store name
            // but Jspreadsheet passes ID if source is defined
            // If not found in DB, maybe they typed a new store? We handle that in AJAX dropdown now.
            // Let's assume storeId is valid or null
            
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

            $qtyStr = str_replace(['.', ','], ['', '.'], $row['qty']);
            $qty = (float) $qtyStr;
            $unitPriceStr = str_replace(['.', ','], ['', '.'], $row['unit_price']);
            $unitPrice = (float) $unitPriceStr;
            $totalPrice = $qty * $unitPrice;

            $catId = $row['category_id'];
            $category = \App\Models\PurchaseCategory::find($catId);
            $categoryName = $category ? $category->name : 'Lain-lain';

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
                    ]);
                    if ($oldPurchaseId != $purchase->id) {
                        $oldPurchase = \App\Models\Purchase::find($oldPurchaseId);
                        if ($oldPurchase) {
                            $validPurchaseIds[$oldPurchase->id] = $oldPurchase;
                        }
                    }
                    $savedData[] = ['index' => $index, 'id' => $item->id];
                }
            } else {
                $item = $purchase->items()->create([
                    'purchase_category_id' => $catId,
                    'category' => $categoryName,
                    'description' => $row['description'] ?? '-',
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ]);
                $savedData[] = ['index' => $index, 'id' => $item->id];
            }
        }

        // Update total_amount for affected purchases or delete if empty
        foreach ($validPurchaseIds as $p) {
            if ($p->items()->count() === 0) {
                $p->delete();
            } else {
                $p->update(['total_amount' => $p->items()->sum('total_price')]);
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
        return response()->json([
            'stores' => $stores,
            'categories' => $categories
        ]);
    }

    public function storeStoreAjax(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $store = \App\Models\Store::create(['name' => $request->name]);
        return response()->json(['id' => $store->id, 'name' => $store->name]);
    }

    public function storeCategoryAjax(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $cat = \App\Models\PurchaseCategory::create(['name' => $request->name]);
        return response()->json(['id' => $cat->id, 'name' => $cat->name]);
    }
}
