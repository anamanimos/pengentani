<?php

namespace App\Http\Controllers;

use App\Models\PurchaseCategory;
use Illuminate\Http\Request;

class PurchaseCategoryController extends Controller
{
    public function index()
    {
        $categories = PurchaseCategory::latest()->get();
        return view('purchase_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('purchase_categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:purchase_categories,name',
            'description' => 'nullable|string'
        ]);

        PurchaseCategory::create($request->all());

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Kategori pembelian berhasil ditambahkan.',
                'redirect' => route('purchase-categories.index')
            ]);
        }
        return redirect()->route('purchase-categories.index')->with('success', 'Kategori pembelian berhasil ditambahkan.');
    }

    public function edit(PurchaseCategory $purchaseCategory)
    {
        return view('purchase_categories.edit', compact('purchaseCategory'));
    }

    public function update(Request $request, PurchaseCategory $purchaseCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:purchase_categories,name,' . $purchaseCategory->id,
            'description' => 'nullable|string'
        ]);

        $purchaseCategory->update($request->all());

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Kategori pembelian berhasil diperbarui.',
                'redirect' => route('purchase-categories.index')
            ]);
        }
        return redirect()->route('purchase-categories.index')->with('success', 'Kategori pembelian berhasil diperbarui.');
    }

    public function destroy(PurchaseCategory $purchaseCategory)
    {
        $purchaseCategory->delete();
        return redirect()->route('purchase-categories.index')->with('success', 'Kategori pembelian berhasil dihapus.');
    }
}
