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
            'type' => 'required|in:bagi_hasil,pengembalian_modal,zakat',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'proof_image' => 'nullable|image|max:5120', // Max 5MB
            'notes' => 'nullable|string'
        ]);

        $role = $request->role;
        $userId = $request->user_id;

        if ($request->type === 'bagi_hasil') {
            $request->validate([
                'role' => 'required|in:admin,pengelola,investor',
                'user_id' => 'required|exists:users,id',
            ]);
        } elseif ($request->type === 'pengembalian_modal') {
            $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);
            $role = 'investor';
        } else {
            // Zakat
            $role = null;
            $userId = null;
        }

        $proofImagePath = null;
        if ($request->hasFile('proof_image')) {
            $proofImagePath = $request->file('proof_image')->store('withdrawals', 'public');
        }

        $pertanian->withdrawals()->create([
            'type' => $request->type,
            'user_id' => $userId,
            'role' => $role,
            'amount' => str_replace(',', '', $request->amount),
            'date' => $request->date,
            'proof_image' => $proofImagePath,
            'notes' => $request->notes
        ]);

        if ($request->ajax()) {
            return response()->json(['message' => 'Penarikan dana berhasil dicatat.']);
        }

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

        if (request()->ajax()) {
            return response()->json(['message' => 'Data penarikan berhasil dihapus.']);
        }

        return back()->with('success', 'Data penarikan berhasil dihapus.');
    }
}
