<?php

namespace App\Http\Controllers;

use App\Models\TransactionProof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TransactionProofController extends Controller
{
    public function index(Request $request)
    {
        $query = TransactionProof::withCount(['purchaseItems', 'incomes', 'workerJobs'])
            ->where('user_id', Auth::id());
            
        if ($request->has('status') && $request->status !== 'all' && $request->status !== '') {
            if ($request->status === 'used') {
                $query->where(function($q) {
                    $q->has('purchaseItems')
                      ->orHas('incomes')
                      ->orHas('workerJobs');
                });
            } elseif ($request->status === 'unused') {
                $query->whereDoesntHave('purchaseItems')
                      ->whereDoesntHave('incomes')
                      ->whereDoesntHave('workerJobs');
            }
        }

        $proofs = $query->latest()->get();

        $proofs->each(function($proof) {
            $proof->is_used = ($proof->purchase_items_count + $proof->incomes_count + $proof->worker_jobs_count) > 0;
        });

        return view('transaction_proofs.index', compact('proofs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120', // Max 5MB
        ]);

        $path = $request->file('file')->store('transaction_proofs', 'public');

        $proof = TransactionProof::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'file_path' => $path,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'proof' => $proof,
                'message' => 'Bukti transaksi berhasil diunggah'
            ]);
        }

        return redirect()->back()->with('success', 'Bukti transaksi berhasil diunggah');
    }

    public function destroy(TransactionProof $transactionProof)
    {
        if ($transactionProof->user_id !== Auth::id()) {
            abort(403);
        }

        if (Storage::disk('public')->exists($transactionProof->file_path)) {
            Storage::disk('public')->delete($transactionProof->file_path);
        }

        $transactionProof->delete();

        return redirect()->back()->with('success', 'Bukti transaksi berhasil dihapus');
    }

    public function rename(Request $request, TransactionProof $transactionProof)
    {
        if ($transactionProof->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $oldName = $transactionProof->name;
        $newName = $request->name;

        if ($oldName !== $newName) {
            $history = $transactionProof->rename_history ?? [];
            $history[] = [
                'old_name' => $oldName,
                'new_name' => $newName,
                'changed_by' => Auth::user()->name,
                'changed_at' => now()->format('d M Y, H:i')
            ];

            $transactionProof->update([
                'name' => $newName,
                'rename_history' => $history
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Nama bukti transaksi berhasil diubah',
            'name' => $newName,
            'rename_history' => $transactionProof->rename_history
        ]);
    }

    public function show(Request $request, TransactionProof $transactionProof)
    {
        if ($transactionProof->user_id !== Auth::id()) {
            abort(403);
        }

        $transactionProof->load([
            'purchaseItems.purchase.pertanian.kebun',
            'purchaseItems.purchaseCategory',
            'incomes.pertanian',
            'incomes.category',
            'workerJobs.pertanian',
            'workerJobs.worker',
            'workerJobs.category'
        ]);

        // Sort relations by date
        $transactionProof->setRelation('purchaseItems', $transactionProof->purchaseItems->sortBy(function($item) {
            return $item->purchase->date ?? '';
        })->values());

        $transactionProof->setRelation('incomes', $transactionProof->incomes->sortBy('date')->values());

        $transactionProof->setRelation('workerJobs', $transactionProof->workerJobs->sortBy('date')->values());

        $totalPurchases = $transactionProof->purchaseItems->sum('total_price');
        $totalIncomes = $transactionProof->incomes->sum('amount');
        $totalWages = $transactionProof->workerJobs->sum('wage');
        $totalKonsumsi = $transactionProof->workerJobs->sum('konsumsi');
        $totalWorkerJobs = $totalWages + $totalKonsumsi;

        $compactData = compact(
            'transactionProof',
            'totalPurchases',
            'totalIncomes',
            'totalWages',
            'totalKonsumsi',
            'totalWorkerJobs'
        );

        if ($request->ajax()) {
            return view('transaction_proofs.modal_content', $compactData);
        }

        return view('transaction_proofs.show', $compactData);
    }
}
