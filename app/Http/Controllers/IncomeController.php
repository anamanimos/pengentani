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
        $proofs = \App\Models\TransactionProof::where('user_id', \Illuminate\Support\Facades\Auth::id())->orderBy('name')->get();

        return view('incomes.index', compact('incomes', 'pertanians', 'tengkulaks', 'proofs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'data' => 'required|array',
            'data.*.id' => 'nullable|exists:incomes,id',
            'data.*.pertanian_id' => 'nullable|exists:pertanians,id',
            'data.*.date' => 'nullable|date',
            'data.*.type' => 'nullable|in:Panen,Lain-lain',
            'data.*.description' => 'nullable|string|max:255',
            'data.*.amount' => 'nullable|numeric',
            'data.*.tengkulak_id' => 'nullable|exists:tengkulaks,id',
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

                if (!empty($row['id'])) {
                    $income = \App\Models\Income::find($row['id']);
                    if ($income) {
                        $income->update([
                            'pertanian_id' => $row['pertanian_id'],
                            'date' => $row['date'],
                            'type' => $row['type'],
                            'description' => $row['description'] ?? null,
                            'amount' => $row['amount'],
                            'tengkulak_id' => $row['tengkulak_id'] ?? null,
                            'transaction_proof_id' => $row['transaction_proof_id'] ?? null,
                        ]);
                        $savedData[] = ['index' => $index, 'id' => $income->id];
                    }
                } else {
                    $income = \App\Models\Income::create([
                        'pertanian_id' => $row['pertanian_id'],
                        'date' => $row['date'],
                        'type' => $row['type'],
                        'description' => $row['description'] ?? null,
                        'amount' => $row['amount'] ?? 0,
                        'tengkulak_id' => $row['tengkulak_id'] ?? null,
                        'transaction_proof_id' => $row['transaction_proof_id'] ?? null,
                    ]);
                    $savedData[] = ['index' => $index, 'id' => $income->id];
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
}
