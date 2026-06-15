<?php

namespace App\Http\Controllers;

use App\Models\Tengkulak;
use Illuminate\Http\Request;

class TengkulakController extends Controller
{
    public function index()
    {
        $tengkulaks = Tengkulak::latest()->get();
        return view('tengkulaks.index', compact('tengkulaks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        Tengkulak::create($request->all());

        return redirect()->route('tengkulaks.index')->with('success', 'Tengkulak berhasil ditambahkan.');
    }

    public function update(Request $request, Tengkulak $tengkulak)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $tengkulak->update($request->all());

        return redirect()->route('tengkulaks.index')->with('success', 'Data Tengkulak berhasil diperbarui.');
    }

    public function destroy(Tengkulak $tengkulak)
    {
        if ($tengkulak->incomes()->count() > 0) {
            return redirect()->route('tengkulaks.index')->with('error', 'Tengkulak tidak bisa dihapus karena sudah memiliki riwayat transaksi.');
        }

        $tengkulak->delete();

        return redirect()->route('tengkulaks.index')->with('success', 'Tengkulak berhasil dihapus.');
    }
}
