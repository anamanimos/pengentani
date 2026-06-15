<?php

namespace App\Http\Controllers;

use App\Models\Tanaman;
use Illuminate\Http\Request;

class TanamanController extends Controller
{
    public function index()
    {
        $tanamans = Tanaman::latest()->get();
        return view('tanamans.index', compact('tanamans'));
    }

    public function create()
    {
        return view('tanamans.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tanamans,name',
            'description' => 'nullable|string'
        ]);

        Tanaman::create($request->all());

        if ($request->ajax()) {
            return response()->json(['message' => 'Master Tanaman berhasil ditambahkan.', 'redirect' => route('tanamans.index')]);
        }
        return redirect()->route('tanamans.index')->with('success', 'Master Tanaman berhasil ditambahkan.');
    }

    // For Select2 AJAX Creation
    public function storeAjax(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $tanaman = Tanaman::firstOrCreate(['name' => $request->name]);

        return response()->json([
            'id' => $tanaman->id,
            'text' => $tanaman->name
        ]);
    }

    public function edit(Tanaman $tanaman)
    {
        return view('tanamans.edit', compact('tanaman'));
    }

    public function update(Request $request, Tanaman $tanaman)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tanamans,name,' . $tanaman->id,
            'description' => 'nullable|string'
        ]);

        $tanaman->update($request->all());

        if ($request->ajax()) {
            return response()->json(['message' => 'Master Tanaman berhasil diperbarui.', 'redirect' => route('tanamans.index')]);
        }
        return redirect()->route('tanamans.index')->with('success', 'Master Tanaman berhasil diperbarui.');
    }

    public function destroy(Tanaman $tanaman)
    {
        $tanaman->delete();
        return redirect()->route('tanamans.index')->with('success', 'Master Tanaman berhasil dihapus.');
    }
}
