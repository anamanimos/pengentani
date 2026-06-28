<?php

namespace App\Http\Controllers;

use App\Models\Kebun;
use App\Models\Pertanian;
use App\Models\Tanaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PertanianController extends Controller
{
    public function index()
    {
        $pertanians = Pertanian::where('user_id', Auth::id())
            ->with(['kebun', 'incomes', 'purchases.items', 'workerJobs'])
            ->latest()
            ->get();

        foreach ($pertanians as $pertanian) {
            $totalIncome = $pertanian->incomes->sum('amount');
            
            $totalPurchase = $pertanian->purchases->sum(function($purchase) {
                return $purchase->items->sum('total_price');
            });
            
            $totalWorker = $pertanian->workerJobs->sum('wage');
            
            $pertanian->laba_sementara = $totalIncome - $totalPurchase - $totalWorker;
        }

        return view('pertanians.index', compact('pertanians'));
    }

    public function create()
    {
        $kebuns = Kebun::where('user_id', Auth::id())->where('status', 'published')->get();
        $tanamans = Tanaman::all();
        $admins = \App\Models\User::where('role', 'admin')->get();
        $pengelolas = \App\Models\User::where('role', 'pengelola')->get();
        return view('pertanians.create', compact('kebuns', 'tanamans', 'admins', 'pengelolas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kebun_id' => 'required|exists:kebuns,id',
            'name' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|string',
            'persentase_zakat' => 'nullable|numeric|min:0|max:100',
            'persentase_investor' => 'nullable|integer|min:0|max:100',
            'persentase_pengelola' => 'nullable|integer|min:0|max:100',
            'persentase_admin' => 'nullable|integer|min:0|max:100',
            'batasan_investasi' => 'nullable|numeric|min:0',
            'admin_id' => 'required|exists:users,id',
            'pengelola_id' => 'required|exists:users,id',
            'tanamans' => 'nullable|array',
            'biayas' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $pertanian = Pertanian::create([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'user_id' => Auth::id(),
                'kebun_id' => $request->kebun_id,
                'admin_id' => $request->admin_id,
                'pengelola_id' => $request->pengelola_id,
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                'persentase_zakat' => $request->persentase_zakat ?? 5.00,
                'persentase_investor' => $request->persentase_investor ?? 0,
                'persentase_pengelola' => $request->persentase_pengelola ?? 0,
                'persentase_admin' => $request->persentase_admin ?? 0,
                'batasan_investasi' => $request->batasan_investasi ? str_replace(',', '', $request->batasan_investasi) : null,
            ]);

            if ($request->has('tanamans')) {
                foreach ($request->tanamans as $tanamanData) {
                    if(!isset($tanamanData['tanaman_id'])) continue;
                    
                    // Allow creating new Tanaman on the fly if user entered text instead of ID
                    $tanamanId = $tanamanData['tanaman_id'];
                    if (!is_numeric($tanamanId)) {
                        $newTanaman = Tanaman::firstOrCreate(['name' => $tanamanId]);
                        $tanamanId = $newTanaman->id;
                    }

                    $pertanian->tanamans()->create([
                        'tanaman_id' => $tanamanId,
                        'qty_pohon' => $tanamanData['qty_pohon'] ?? 0,
                        'estimasi_berat_per_pohon' => str_replace(',', '', $tanamanData['estimasi_berat_per_pohon'] ?? 0),
                        'estimasi_harga_per_kg' => str_replace(',', '', $tanamanData['estimasi_harga_per_kg'] ?? 0),
                    ]);
                }
            }

            if ($request->has('biayas')) {
                foreach ($request->biayas as $biayaData) {
                    if(empty($biayaData['name'])) continue;
                    $qty = $biayaData['qty'] ?? 1;
                    $harga = str_replace(',', '', $biayaData['harga_satuan'] ?? 0);
                    
                    $pertanian->biayas()->create([
                        'name' => $biayaData['name'],
                        'qty' => $qty,
                        'harga_satuan' => $harga,
                        'total' => $qty * $harga,
                    ]);
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json(['message' => 'Rencana Pertanian berhasil dibuat.', 'redirect' => route('pertanians.index')]);
            }
            return redirect()->route('pertanians.index')->with('success', 'Rencana Pertanian berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 422);
            }
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Pertanian $pertanian)
    {
        if ($pertanian->user_id !== Auth::id()) abort(403);

        $pertanian->load(['kebun', 'admin', 'pengelola', 'tanamans.tanaman', 'biayas', 'investors.user', 'purchases.store', 'workerJobs.worker', 'workerJobs.category', 'incomes', 'withdrawals.user']);

        $totalBiaya = $pertanian->biayas->sum('total');
        
        $totalInvestasiAll = $pertanian->investors->sum('besaran_investasi');
        $totalInvestasiDeal = $pertanian->investors->where('status', 'Deal')->sum('besaran_investasi');
        // Gunakan all sebagai default totalInvestasi (bisa diganti di UI nantinya)
        $totalInvestasi = $totalInvestasiAll;

        $estimasiPendapatan = 0;
        foreach ($pertanian->tanamans as $pt) {
            $estimasiPendapatan += ($pt->qty_pohon * $pt->estimasi_berat_per_pohon * $pt->estimasi_harga_per_kg);
        }

        $estimasiLaba = $estimasiPendapatan - $totalBiaya;
        $zakatPersen = $pertanian->persentase_zakat ?? 5.00;
        $zakat = $estimasiLaba > 0 ? $estimasiLaba * ($zakatPersen / 100) : 0;
        $labaSetelahZakat = $estimasiLaba - $zakat;

        $labaInvestor = $labaSetelahZakat * ($pertanian->persentase_investor / 100);
        $labaPengelola = $labaSetelahZakat * ($pertanian->persentase_pengelola / 100);
        $labaAdmin = $labaSetelahZakat * ($pertanian->persentase_admin / 100);

        // Calculate Realisasi (Actual Expenditures & Incomes)
        $totalRealisasiPembelian = $pertanian->purchases->sum('total_amount');
        $totalRealisasiPekerjaan = $pertanian->workerJobs->sum('wage');
        $totalRealisasi = $totalRealisasiPembelian + $totalRealisasiPekerjaan;
        
        $sisaCashAll = $totalInvestasiAll - $totalRealisasi;
        $sisaCashDeal = $totalInvestasiDeal - $totalRealisasi;
        
        $totalRealisasiPendapatan = $pertanian->incomes->sum('amount');
        $realisasiLabaBersih = $totalRealisasiPendapatan - $totalRealisasi;

        // Withdrawal Logic
        $realisasiZakat = $realisasiLabaBersih > 0 ? $realisasiLabaBersih * ($zakatPersen / 100) : 0;
        $realisasiSetelahZakat = $realisasiLabaBersih - $realisasiZakat;
        
        $alokasiAdmin = $realisasiSetelahZakat > 0 ? $realisasiSetelahZakat * ($pertanian->persentase_admin / 100) : 0;
        $alokasiPengelola = $realisasiSetelahZakat > 0 ? $realisasiSetelahZakat * ($pertanian->persentase_pengelola / 100) : 0;
        $alokasiInvestorTotal = $realisasiSetelahZakat > 0 ? $realisasiSetelahZakat * ($pertanian->persentase_investor / 100) : 0;

        $ditarikAdmin = $pertanian->withdrawals->where('role', 'admin')->sum('amount');
        $ditarikPengelola = $pertanian->withdrawals->where('role', 'pengelola')->sum('amount');
        $ditarikInvestorTotal = $pertanian->withdrawals->where('role', 'investor')->sum('amount');

        $sisaAdmin = $alokasiAdmin - $ditarikAdmin;
        $sisaPengelola = $alokasiPengelola - $ditarikPengelola;
        $sisaInvestorTotal = $alokasiInvestorTotal - $ditarikInvestorTotal;

        $totalPenarikan = $ditarikAdmin + $ditarikPengelola + $ditarikInvestorTotal;
        $totalKasMasuk = $totalInvestasiDeal + $totalRealisasiPendapatan;
        $totalKasKeluar = $totalRealisasi + $totalPenarikan;
        $sisaUangCash = $totalKasMasuk - $totalKasKeluar;

        $withdrawals = $pertanian->withdrawals()->latest('date')->get();

        // Investor Statistics
        $investorStats = collect();
        $hasDealInvestors = $pertanian->investors->where('status', 'Deal')->count() > 0;
        if ($hasDealInvestors) {
            foreach ($pertanian->investors->where('status', 'Deal') as $inv) {
                if ($inv->porsi_bagi_hasil !== null) {
                    $porsi = $inv->porsi_bagi_hasil / 100;
                } else {
                    $porsi = $totalInvestasiDeal > 0 ? ($inv->besaran_investasi / $totalInvestasiDeal) : 0;
                }
                
                $alokasiInv = $alokasiInvestorTotal * $porsi;
                $ditarikInv = $pertanian->withdrawals->where('role', 'investor')->where('user_id', $inv->user_id)->sum('amount');
                $sisaInv = $alokasiInv - $ditarikInv;
                
                $investorStats->push((object)[
                    'name' => $inv->user->name ?? 'Investor',
                    'investasi' => $inv->besaran_investasi,
                    'porsi' => $porsi * 100,
                    'alokasi' => $alokasiInv,
                    'ditarik' => $ditarikInv,
                    'sisa' => $sisaInv,
                ]);
            }
        }

        // Combine for table
        $realisasiList = collect();

        foreach ($pertanian->purchases as $purchase) {
            $realisasiList->push((object)[
                'date' => $purchase->date,
                'kategori' => 'Pembelian Material',
                'deskripsi' => ($purchase->store->name ?? '-') . ' (Inv: ' . $purchase->invoice_number . ')',
                'nominal' => $purchase->total_amount,
                'icon' => 'ki-shop',
                'color' => 'primary'
            ]);
        }

        foreach ($pertanian->workerJobs as $job) {
            $realisasiList->push((object)[
                'date' => \Carbon\Carbon::parse($job->date),
                'kategori' => 'Upah Pekerja',
                'deskripsi' => ($job->worker->name ?? '-') . ' (' . ($job->category->name ?? '-') . ')',
                'nominal' => $job->wage,
                'icon' => 'ki-profile-user',
                'color' => 'success'
            ]);
        }

        $realisasiList = $realisasiList->sortByDesc('date');

        return view('pertanians.show', compact(
            'pertanian', 
            'totalBiaya', 
            'totalInvestasi', 
            'estimasiPendapatan',
            'estimasiLaba',
            'zakatPersen',
            'zakat',
            'labaSetelahZakat',
            'labaInvestor',
            'labaPengelola',
            'labaAdmin',
            'totalRealisasiPembelian',
            'totalRealisasiPekerjaan',
            'totalRealisasi',
            'totalRealisasiPendapatan',
            'realisasiLabaBersih',
            'realisasiZakat',
            'realisasiSetelahZakat',
            'alokasiAdmin',
            'alokasiPengelola',
            'alokasiInvestorTotal',
            'ditarikAdmin',
            'ditarikPengelola',
            'ditarikInvestorTotal',
            'sisaAdmin',
            'sisaPengelola',
            'sisaInvestorTotal',
            'totalInvestasiAll',
            'totalInvestasiDeal',
            'sisaCashAll',
            'sisaCashDeal',
            'realisasiList',
            'withdrawals',
            'investorStats',
            'totalPenarikan',
            'totalKasMasuk',
            'totalKasKeluar',
            'sisaUangCash'
        ));
    }

    public function edit(Pertanian $pertanian)
    {
        if ($pertanian->user_id !== Auth::id()) abort(403);
        
        $pertanian->load('tanamans.tanaman', 'biayas');
        $kebuns = Kebun::where('user_id', Auth::id())
            ->where(function($query) use ($pertanian) {
                $query->where('status', 'published')
                      ->orWhere('id', $pertanian->kebun_id);
            })->get();
        $tanamans = Tanaman::all();
        $admins = \App\Models\User::where('role', 'admin')->get();
        $pengelolas = \App\Models\User::where('role', 'pengelola')->get();
        
        return view('pertanians.edit', compact('pertanian', 'kebuns', 'tanamans', 'admins', 'pengelolas'));
    }

    public function update(Request $request, Pertanian $pertanian)
    {
        if ($pertanian->user_id !== Auth::id()) abort(403);

        $request->validate([
            'kebun_id' => 'required|exists:kebuns,id',
            'name' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|string',
            'persentase_zakat' => 'nullable|numeric|min:0|max:100',
            'persentase_investor' => 'nullable|integer|min:0|max:100',
            'persentase_pengelola' => 'nullable|integer|min:0|max:100',
            'persentase_admin' => 'nullable|integer|min:0|max:100',
            'batasan_investasi' => 'nullable|numeric|min:0',
            'admin_id' => 'required|exists:users,id',
            'pengelola_id' => 'required|exists:users,id',
            'tanamans' => 'nullable|array',
            'biayas' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $pertanian->update([
                'kebun_id' => $request->kebun_id,
                'admin_id' => $request->admin_id,
                'pengelola_id' => $request->pengelola_id,
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                'persentase_zakat' => $request->persentase_zakat ?? 5.00,
                'persentase_investor' => $request->persentase_investor ?? 0,
                'persentase_pengelola' => $request->persentase_pengelola ?? 0,
                'persentase_admin' => $request->persentase_admin ?? 0,
                'batasan_investasi' => $request->batasan_investasi ? str_replace(',', '', $request->batasan_investasi) : null,
            ]);

            // Sync Tanamans
            $pertanian->tanamans()->delete();
            if ($request->has('tanamans')) {
                foreach ($request->tanamans as $tanamanData) {
                    if(!isset($tanamanData['tanaman_id'])) continue;
                    
                    $tanamanId = $tanamanData['tanaman_id'];
                    if (!is_numeric($tanamanId)) {
                        $newTanaman = Tanaman::firstOrCreate(['name' => $tanamanId]);
                        $tanamanId = $newTanaman->id;
                    }

                    $pertanian->tanamans()->create([
                        'tanaman_id' => $tanamanId,
                        'qty_pohon' => $tanamanData['qty_pohon'] ?? 0,
                        'estimasi_berat_per_pohon' => str_replace(',', '', $tanamanData['estimasi_berat_per_pohon'] ?? 0),
                        'estimasi_harga_per_kg' => str_replace(',', '', $tanamanData['estimasi_harga_per_kg'] ?? 0),
                    ]);
                }
            }

            // Sync Biayas
            $pertanian->biayas()->delete();
            if ($request->has('biayas')) {
                foreach ($request->biayas as $biayaData) {
                    if(empty($biayaData['name'])) continue;
                    $qty = $biayaData['qty'] ?? 1;
                    $harga = str_replace(',', '', $biayaData['harga_satuan'] ?? 0);
                    
                    $pertanian->biayas()->create([
                        'name' => $biayaData['name'],
                        'qty' => $qty,
                        'harga_satuan' => $harga,
                        'total' => $qty * $harga,
                    ]);
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json(['message' => 'Rencana Pertanian berhasil diperbarui.', 'redirect' => route('pertanians.index')]);
            }
            return redirect()->route('pertanians.index')->with('success', 'Rencana Pertanian berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 422);
            }
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Pertanian $pertanian)
    {
        if ($pertanian->user_id !== Auth::id()) abort(403);
        $pertanian->delete();
        return redirect()->route('pertanians.index')->with('success', 'Rencana Pertanian berhasil dihapus.');
    }
}
