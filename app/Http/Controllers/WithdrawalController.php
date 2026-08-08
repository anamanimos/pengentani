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

        // Check if proof image picked from transaction_proofs gallery URL
        if ($request->filled('proof_url')) {
            $proofImagePath = $request->proof_url;
        }

        // If user uploaded a new image file
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

    public function update(Request $request, Withdrawal $withdrawal)
    {
        if ($withdrawal->pertanian->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'type' => 'required|in:bagi_hasil,pengembalian_modal,zakat',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'proof_image' => 'nullable|image|max:5120',
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
            $role = null;
            $userId = null;
        }

        $proofImagePath = $withdrawal->proof_image;

        // Check if proof image picked from transaction_proofs gallery URL
        if ($request->filled('proof_url')) {
            $proofImagePath = $request->proof_url;
        }

        // If user uploaded a new image file
        if ($request->hasFile('proof_image')) {
            if ($withdrawal->proof_image && !str_starts_with($withdrawal->proof_image, 'http') && Storage::disk('public')->exists($withdrawal->proof_image)) {
                Storage::disk('public')->delete($withdrawal->proof_image);
            }
            $proofImagePath = $request->file('proof_image')->store('withdrawals', 'public');
        }

        $withdrawal->update([
            'type' => $request->type,
            'user_id' => $userId,
            'role' => $role,
            'amount' => str_replace(',', '', $request->amount),
            'date' => $request->date,
            'proof_image' => $proofImagePath,
            'notes' => $request->notes
        ]);

        if ($request->ajax()) {
            return response()->json(['message' => 'Data penarikan berhasil diperbarui.']);
        }

        return back()->with('success', 'Data penarikan berhasil diperbarui.');
    }

    public function destroy(Withdrawal $withdrawal)
    {
        if ($withdrawal->pertanian->user_id !== Auth::id()) {
            abort(403);
        }

        if ($withdrawal->proof_image && !str_starts_with($withdrawal->proof_image, 'http')) {
            Storage::disk('public')->delete($withdrawal->proof_image);
        }

        $withdrawal->delete();

        if (request()->ajax()) {
            return response()->json(['message' => 'Data penarikan berhasil dihapus.']);
        }

        return back()->with('success', 'Data penarikan berhasil dihapus.');
    }
}
