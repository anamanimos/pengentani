<?php

namespace App\Http\Controllers;

use App\Models\Kebun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KebunController extends Controller
{
    public function index()
    {
        $kebuns = Kebun::where('user_id', Auth::id())->latest()->get();
        return view('kebuns.index', compact('kebuns'));
    }

    public function create()
    {
        return view('kebuns.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'polygon' => 'nullable|string',
            'area' => 'nullable|numeric',
            'status' => 'nullable|string|in:draft,published'
        ]);

        Kebun::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'polygon' => $request->polygon,
            'area' => $request->area,
            'status' => $request->status ?? 'published',
        ]);

        if ($request->ajax()) {
            return response()->json(['message' => 'Kebun berhasil ditambahkan.', 'redirect' => route('kebuns.index')]);
        }
        return redirect()->route('kebuns.index')->with('success', 'Kebun berhasil ditambahkan.');
    }

    public function edit(Kebun $kebun)
    {
        if ($kebun->user_id !== Auth::id()) abort(403);
        return view('kebuns.edit', compact('kebun'));
    }

    public function update(Request $request, Kebun $kebun)
    {
        if ($kebun->user_id !== Auth::id()) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'polygon' => 'nullable|string',
            'area' => 'nullable|numeric',
            'status' => 'nullable|string|in:draft,published'
        ]);

        $kebun->update([
            'name' => $request->name,
            'polygon' => $request->polygon,
            'area' => $request->area,
            'status' => $request->status ?? 'published',
        ]);

        if ($request->ajax()) {
            return response()->json(['message' => 'Kebun berhasil diperbarui.', 'redirect' => route('kebuns.index')]);
        }
        return redirect()->route('kebuns.index')->with('success', 'Kebun berhasil diperbarui.');
    }

    public function destroy(Kebun $kebun)
    {
        if ($kebun->user_id !== Auth::id()) abort(403);
        
        $kebun->delete();
        
        return redirect()->route('kebuns.index')->with('success', 'Kebun berhasil dihapus.');
    }
}
