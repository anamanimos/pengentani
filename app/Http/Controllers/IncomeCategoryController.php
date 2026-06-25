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

        IncomeCategory::create($request->all());

        return redirect()->route('income-categories.index')->with('success', 'Kategori pendapatan berhasil ditambahkan.');
    }

    public function edit(IncomeCategory $incomeCategory)
    {
        return view('income_categories.edit', compact('incomeCategory'));
    }

    public function update(Request $request, IncomeCategory $incomeCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:income_categories,name,' . $incomeCategory->id,
            'description' => 'nullable|string'
        ]);

        $incomeCategory->update($request->all());

        return redirect()->route('income-categories.index')->with('success', 'Kategori pendapatan berhasil diperbarui.');
    }

    public function destroy(IncomeCategory $incomeCategory)
    {
        $incomeCategory->delete();
        return redirect()->route('income-categories.index')->with('success', 'Kategori pendapatan berhasil dihapus.');
    }
}
