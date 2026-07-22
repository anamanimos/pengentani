<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            Store::where('name', 'like', '%+ Tambah%')
                ->orWhere('name', 'like', '%NEW_STORE%')
                ->orWhere('name', 'like', '%...%')
                ->orWhereRaw('LENGTH(name) > 60')
                ->delete();
        } catch (\Exception $e) {}

        $stores = Store::orderBy('name')->get();
        return view('stores.index', compact('stores'));
    }

    public function create()
    {
        return view('stores.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
        ]);

        Store::create($validated);

        return redirect()->route('stores.index')->with('success', 'Toko berhasil ditambahkan.');
    }

    public function edit(Store $store)
    {
        return view('stores.edit', compact('store'));
    }

    public function update(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
        ]);

        $store->update($validated);

        return redirect()->route('stores.index')->with('success', 'Toko berhasil diperbarui.');
    }

    public function destroy(Store $store)
    {
        if ($store->purchases()->count() > 0) {
            return back()->with('error', 'Toko tidak bisa dihapus karena masih terkait dengan data pembelian.');
        }
        $store->delete();
        return redirect()->route('stores.index')->with('success', 'Toko berhasil dihapus.');
    }
}
