<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Income::with('pertanian.kebun');

        if ($request->filled('pertanian_id')) {
            $query->where('pertanian_id', $request->pertanian_id);
        }
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $incomes = $query->orderBy('id', 'asc')->take(500)->get();
        $pertanians = \App\Models\Pertanian::with('kebun')->where('user_id', \Illuminate\Support\Facades\Auth::id())->orderBy('name')->get();
        $tengkulaks = \App\Models\Tengkulak::orderBy('name')->get();
        $categories = \App\Models\IncomeCategory::orderBy('name')->get();

        return view('incomes.index', compact('incomes', 'pertanians', 'tengkulaks', 'proofs', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'data' => 'required|array',
            'data.*.id' => 'nullable|exists:incomes,id',
            'data.*.date' => 'nullable|date',
            'data.*.income_category_id' => 'nullable|exists:income_categories,id',
            'data.*.description' => 'nullable|string|max:255',
            'data.*.qty' => 'nullable',
            'data.*.unit_price' => 'nullable',
            'data.*.transaction_proof_id' => 'nullable|exists:transaction_proofs,id',
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $savedData = [];
            foreach ($request->data as $index => $row) {
                // Skip incomplete rows
                if (empty($row['pertanian_id']) || empty($row['date'])) {
                    continue;
                }

                $qtyStr = str_replace(',', '', $row['qty'] ?? '0');
                $qty = (float) $qtyStr;
                $unitPriceStr = str_replace(',', '', $row['unit_price'] ?? '0');
                $unitPrice = (float) $unitPriceStr;
                $amount = $qty * $unitPrice;

                $pertanianId = $row['pertanian_id'];
                if (!is_numeric($pertanianId)) {
                    $searchName = trim($pertanianId);
                    if (preg_match('/\]\s*-\s*(.*)/', $searchName, $matches)) {
                        $searchName = trim($matches[1]);
                    }
                    $pertanian = \App\Models\Pertanian::where('user_id', \Illuminate\Support\Facades\Auth::id())
                        ->where('name', 'like', '%' . $searchName . '%')
                        ->first();
                    if (!$pertanian) {
                        \Illuminate\Support\Facades\DB::rollBack();
                        return response()->json(['message' => 'Pertanian tidak ditemukan: ' . htmlspecialchars(substr($row['pertanian_id'], 0, 50))], 422);
                    }
                    $pertanianId = $pertanian->id;
                }

                $tengkulakId = $row['tengkulak_id'] ?? null;
                if (!empty($tengkulakId) && !is_numeric($tengkulakId)) {
                    $newTengkulak = \App\Models\Tengkulak::firstOrCreate([
                        'name' => trim($tengkulakId)
                    ]);
                    $tengkulakId = $newTengkulak->id;
                }

                $type = $row['type'] ?? null;

                if (!empty($row['id'])) {
                    $income = \App\Models\Income::find($row['id']);
                    if ($income) {
                        $income->update([
                            'pertanian_id' => $pertanianId,
                            'date' => $row['date'],
                            'income_category_id' => $row['income_category_id'] ?? null,
                            'description' => $row['description'] ?? null,
                            'qty' => $qty,
                            'unit_price' => $unitPrice,
                            'amount' => $amount,
                            'tengkulak_id' => $tengkulakId,
                            'transaction_proof_id' => $row['transaction_proof_id'] ?? null,
                        ]);
                        $savedData[] = ['index' => $row['index'], 'id' => $income->id];
                    }
                } else {
                    $income = \App\Models\Income::create([
                        'pertanian_id' => $pertanianId,
                        'date' => $row['date'],
                        'income_category_id' => $row['income_category_id'] ?? null,
                        'description' => $row['description'] ?? null,
                        'qty' => $qty,
                        'unit_price' => $unitPrice,
                        'amount' => $amount,
                        'tengkulak_id' => $tengkulakId,
                        'transaction_proof_id' => $row['transaction_proof_id'] ?? null,
                    ]);
                    $savedData[] = ['index' => $row['index'], 'id' => $income->id];
                }
            }
            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'message' => 'Data pendapatan berhasil disimpan secara massal.',
                'savedData' => $savedData,
                'redirect' => route('incomes.index')
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['message' => 'Gagal menyimpan data: ' . $e->getMessage()], 422);
        }
    }

    public function destroy(Request $request, \App\Models\Income $income)
    {
        $income->delete();
        
        if ($request->ajax()) {
            return response()->json(['message' => 'Data berhasil dihapus']);
        }
        
        return redirect()->route('incomes.index')->with('success', 'Data pendapatan berhasil dihapus.');
    }

    public function storeCategoryAjax(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $category = \App\Models\IncomeCategory::create([
            'name' => $request->name
        ]);
        return response()->json(['id' => $category->id, 'name' => $category->name]);
    }
}
