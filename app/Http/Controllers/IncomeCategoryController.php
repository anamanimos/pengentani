<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IncomeCategory;

class IncomeCategoryController extends Controller
{
    public function index()
    {
        $categories = IncomeCategory::latest()->get();
        return view('income_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('income_categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:income_categories,name',
            'description' => 'nullable|string'
        ]);

        $category = IncomeCategory::create($request->all());

        if ($request->ajax()) {
            return response()->json(['message' => 'Kategori pendapatan berhasil ditambahkan.', 'category' => $category]);
        }

        return redirect()->route('income-categories.index')->with('success', 'Kategori pendapatan berhasil ditambahkan.');
    }

    public function edit(IncomeCategory $category)
    {
        return view('income_categories.edit', compact('category'));
    }

    public function update(Request $request, IncomeCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:income_categories,name,' . $category->id,
            'description' => 'nullable|string'
        ]);

        $category->update($request->all());

        if ($request->ajax()) {
            return response()->json(['message' => 'Kategori pendapatan berhasil diperbarui.', 'category' => $category]);
        }

        return redirect()->route('income-categories.index')->with('success', 'Kategori pendapatan berhasil diperbarui.');
    }

    public function destroy(Request $request, IncomeCategory $category)
    {
        $category->delete();
        
        if ($request->ajax()) {
            return response()->json(['message' => 'Kategori pendapatan berhasil dihapus.']);
        }
        
        return redirect()->route('income-categories.index')->with('success', 'Kategori pendapatan berhasil dihapus.');
    }
}
