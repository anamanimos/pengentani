<?php

namespace App\Http\Controllers;

use App\Models\Pertanian;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class WithdrawalController extends Controller
{
    public function store(Request $request, Pertanian $pertanian)
    {
        if ($pertanian->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'role' => 'required|in:admin,pengelola,investor',
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'proof_image' => 'nullable|image|max:5120', // Max 5MB
            'notes' => 'nullable|string'
        ]);

        $proofImagePath = null;
        if ($request->hasFile('proof_image')) {
            $proofImagePath = $request->file('proof_image')->store('withdrawals', 'public');
        }

        $pertanian->withdrawals()->create([
            'user_id' => $request->user_id,
            'role' => $request->role,
            'amount' => str_replace(',', '', $request->amount),
            'date' => $request->date,
            'proof_image' => $proofImagePath,
            'notes' => $request->notes
        ]);

        return back()->with('success', 'Penarikan dana berhasil dicatat.');
    }

    public function destroy(Withdrawal $withdrawal)
    {
        if ($withdrawal->pertanian->user_id !== Auth::id()) {
            abort(403);
        }

        if ($withdrawal->proof_image) {
            Storage::disk('public')->delete($withdrawal->proof_image);
        }

        $withdrawal->delete();

        return back()->with('success', 'Data penarikan berhasil dihapus.');
    }
}
