<?php

namespace App\Http\Controllers;

use App\Models\Pertanian;
use App\Models\PertanianInvestor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PertanianInvestorController extends Controller
{
    public function index(Pertanian $pertanian)
    {
        if ($pertanian->user_id !== Auth::id()) abort(403);

        $pertanian->load('investors.user', 'biayas', 'tanamans.tanaman');

        $totalBiaya = $pertanian->biayas->sum('total');
        $totalInvestasi = $pertanian->investors->whereIn('status', ['Deal', 'Standby'])->sum('besaran_investasi');

        // Hitung estimasi pendapatan dari tanaman
        $estimasiPendapatan = 0;
        foreach ($pertanian->tanamans as $pt) {
            $estimasiPendapatan += ($pt->qty_pohon * $pt->estimasi_berat_per_pohon * $pt->estimasi_harga_per_kg);
        }

        $investasiDeal = $pertanian->investors->where('status', 'Deal')->sum('besaran_investasi');
        $investasiStandby = $pertanian->investors->where('status', 'Standby')->sum('besaran_investasi');
        $investasiNego = $pertanian->investors->where('status', 'Nego')->sum('besaran_investasi');
        $investasiBatal = $pertanian->investors->where('status', 'Batal')->sum('besaran_investasi');

        return view('pertanians.investors.index', compact(
            'pertanian', 'totalBiaya', 'totalInvestasi', 'estimasiPendapatan',
            'investasiDeal', 'investasiStandby', 'investasiNego', 'investasiBatal'
        ));
    }

    public function create(Pertanian $pertanian)
    {
        if ($pertanian->user_id !== Auth::id()) abort(403);

        $users = User::all();
        return view('pertanians.investors.create', compact('pertanian', 'users'));
    }

    public function store(Request $request, Pertanian $pertanian)
    {
        if ($pertanian->user_id !== Auth::id()) abort(403);

        $request->merge([
            'besaran_investasi' => str_replace(',', '', $request->besaran_investasi)
        ]);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'besaran_investasi' => 'required|numeric|min:1',
            'porsi_bagi_hasil' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        $pertanian->investors()->create([
            'user_id' => $request->user_id,
            'besaran_investasi' => $request->besaran_investasi,
            'porsi_bagi_hasil' => $request->porsi_bagi_hasil,
            'status' => $request->status,
            'keterangan' => $request->keterangan,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Investor berhasil ditambahkan.',
                'redirect' => route('pertanians.investors.index', $pertanian)
            ]);
        }
        return redirect()->route('pertanians.investors.index', $pertanian)->with('success', 'Investor berhasil ditambahkan.');
    }

    public function edit(Pertanian $pertanian, PertanianInvestor $investor)
    {
        if ($pertanian->user_id !== Auth::id()) abort(403);
        if ($investor->pertanian_id !== $pertanian->id) abort(404);

        $users = User::all();
        return view('pertanians.investors.edit', compact('pertanian', 'investor', 'users'));
    }

    public function update(Request $request, Pertanian $pertanian, PertanianInvestor $investor)
    {
        if ($pertanian->user_id !== Auth::id()) abort(403);
        if ($investor->pertanian_id !== $pertanian->id) abort(404);

        $request->merge([
            'besaran_investasi' => str_replace(',', '', $request->besaran_investasi)
        ]);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'besaran_investasi' => 'required|numeric|min:1',
            'porsi_bagi_hasil' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        $investor->update([
            'user_id' => $request->user_id,
            'besaran_investasi' => $request->besaran_investasi,
            'porsi_bagi_hasil' => $request->porsi_bagi_hasil,
            'status' => $request->status,
            'keterangan' => $request->keterangan,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Investor berhasil diperbarui.',
                'redirect' => route('pertanians.investors.index', $pertanian)
            ]);
        }
        return redirect()->route('pertanians.investors.index', $pertanian)->with('success', 'Investor berhasil diperbarui.');
    }

    public function destroy(Pertanian $pertanian, PertanianInvestor $investor)
    {
        if ($pertanian->user_id !== Auth::id()) abort(403);

        $investor->delete();

        return redirect()->route('pertanians.investors.index', $pertanian)->with('success', 'Investor berhasil dihapus.');
    }
}
