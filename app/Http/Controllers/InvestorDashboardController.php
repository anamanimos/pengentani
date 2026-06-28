<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PertanianInvestor;

class InvestorDashboardController extends Controller
{
    /**
     * Helper: hitung laba bersih setelah zakat untuk satu pertanian
     */
    private function hitungLabaSetelahZakat($pertanian)
    {
        $totalIncome = $pertanian->incomes->sum('amount');
        $totalPurchase = $pertanian->purchases->sum('total_amount');
        $totalWorker = $pertanian->workerJobs->sum('wage');
        $labaBersih = $totalIncome - $totalPurchase - $totalWorker;

        $zakatPersen = $pertanian->persentase_zakat ?? 5;
        $zakat = $labaBersih > 0 ? $labaBersih * ($zakatPersen / 100) : 0;
        return $labaBersih - $zakat;
    }

    public function home()
    {
        $user = Auth::user();
        $entityIds = $user->entities()->pluck('entities.id')->toArray();

        // === DATA INVESTOR ===
        $investorData = null;
        $investments = PertanianInvestor::whereIn('entity_id', $entityIds)
            ->with(['pertanian.incomes', 'pertanian.purchases', 'pertanian.workerJobs'])
            ->get();

        if ($investments->count() > 0) {
            $totalInvestment = 0;
            $totalReturnInvestor = 0;

            foreach ($investments as $inv) {
                $pertanian = $inv->pertanian;
                if (!$pertanian) continue;

                $labaSetelahZakat = $this->hitungLabaSetelahZakat($pertanian);

                $persentaseInvestorTotal = $pertanian->persentase_investor ?? 0;
                $batasanInvestasi = $pertanian->batasan_investasi > 0 ? $pertanian->batasan_investasi : 1;
                $proportion = $inv->besaran_investasi / $batasanInvestasi;
                $userProfit = $labaSetelahZakat * ($persentaseInvestorTotal / 100) * $proportion;

                $totalInvestment += $inv->besaran_investasi;
                $totalReturnInvestor += $userProfit;
            }

            // Consider withdrawals for any of the user's entities in role investor
            $totalDitarikInvestor = 0;
            foreach ($investments as $inv) {
                if ($inv->pertanian) {
                    $entityUserIds = $inv->entity ? $inv->entity->users->pluck('id')->toArray() : [];
                    $totalDitarikInvestor += \App\Models\Withdrawal::where('pertanian_id', $inv->pertanian->id)
                        ->whereIn('user_id', $entityUserIds)
                        ->where('role', 'investor')
                        ->sum('amount');
                }
            }

            $investorData = (object)[
                'totalInvestment' => $totalInvestment,
                'totalReturn' => $totalReturnInvestor,
                'totalDitarik' => $totalDitarikInvestor,
                'sisa' => $totalReturnInvestor - $totalDitarikInvestor,
                'projectCount' => $investments->count(),
            ];
        }

        // === DATA ADMIN ===
        $adminData = null;
        $adminProjects = \App\Models\Pertanian::where('admin_id', $user->id)
            ->with(['kebun', 'incomes', 'purchases', 'workerJobs'])
            ->get();

        if ($adminProjects->count() > 0) {
            $totalReturnAdmin = 0;
            foreach ($adminProjects as $p) {
                $labaSetelahZakat = $this->hitungLabaSetelahZakat($p);
                $totalReturnAdmin += $labaSetelahZakat > 0 ? $labaSetelahZakat * (($p->persentase_admin ?? 0) / 100) : 0;
            }

            $totalDitarikAdmin = \App\Models\Withdrawal::where('user_id', $user->id)
                ->where('role', 'admin')->sum('amount');

            $adminData = (object)[
                'totalReturn' => $totalReturnAdmin,
                'totalDitarik' => $totalDitarikAdmin,
                'sisa' => $totalReturnAdmin - $totalDitarikAdmin,
                'projects' => $adminProjects,
            ];
        }

        // === DATA PENGELOLA ===
        $pengelolaData = null;
        $pengelolaProjects = \App\Models\Pertanian::whereIn('pengelola_entity_id', $entityIds)
            ->with(['kebun', 'incomes', 'purchases', 'workerJobs'])
            ->get();

        if ($pengelolaProjects->count() > 0) {
            $totalReturnPengelola = 0;
            foreach ($pengelolaProjects as $p) {
                $labaSetelahZakat = $this->hitungLabaSetelahZakat($p);
                $totalReturnPengelola += $labaSetelahZakat > 0 ? $labaSetelahZakat * (($p->persentase_pengelola ?? 0) / 100) : 0;
            }

            $totalDitarikPengelola = 0;
            foreach ($pengelolaProjects as $p) {
                $entityUserIds = $p->pengelolaEntity ? $p->pengelolaEntity->users->pluck('id')->toArray() : [];
                $totalDitarikPengelola += \App\Models\Withdrawal::where('pertanian_id', $p->id)
                    ->whereIn('user_id', $entityUserIds)
                    ->where('role', 'pengelola')
                    ->sum('amount');
            }

            $pengelolaData = (object)[
                'totalReturn' => $totalReturnPengelola,
                'totalDitarik' => $totalDitarikPengelola,
                'sisa' => $totalReturnPengelola - $totalDitarikPengelola,
                'projects' => $pengelolaProjects,
            ];
        }

        return view('investor.home', compact('user', 'investorData', 'adminData', 'pengelolaData'));
    }

    public function index()
    {
        $user = Auth::user();
        $entityIds = $user->entities()->pluck('entities.id')->toArray();

        $investments = PertanianInvestor::whereIn('entity_id', $entityIds)
            ->with(['pertanian.kebun', 'pertanian.incomes', 'pertanian.purchases.items', 'pertanian.workerJobs', 'pertanian.tanamans', 'pertanian.biayas'])
            ->latest()
            ->get();

        $totalInvestment = 0;
        $totalReturn = 0;
        $totalEstimatedFinalReturn = 0;

        foreach ($investments as $inv) {
            $pertanian = $inv->pertanian;
            if (!$pertanian) continue;

            $totalIncome = $pertanian->incomes->sum('nominal');
            $totalPurchase = $pertanian->purchases->sum(function($p) { return $p->items->sum('total_price'); });
            $totalWorker = $pertanian->workerJobs->sum('wage');
            
            $laba_sementara = $totalIncome - $totalPurchase - $totalWorker;
            
            // Calculate Estimated Final Profit
            $estimasiPendapatan = $pertanian->tanamans->sum(function($t) {
                return $t->qty_pohon * $t->estimasi_berat_per_pohon * $t->estimasi_harga_per_kg;
            });
            $estimasiBiaya = $pertanian->biayas->sum('total');
            $estimasiLaba = $estimasiPendapatan - $estimasiBiaya;

            // Investor's share of the profit
            $persentaseInvestorTotal = $pertanian->persentase_investor ?? 0;
            $batasanInvestasi = $pertanian->batasan_investasi > 0 ? $pertanian->batasan_investasi : 1; 
            
            // Proportion of this user's investment compared to max allowed investment
            $proportion = $inv->besaran_investasi / $batasanInvestasi;
            
            // Expected profit = Total Profit * (Total Investor %) * Proportion
            $userProfit = $laba_sementara * ($persentaseInvestorTotal / 100) * $proportion;
            $estimasiUserProfit = $estimasiLaba * ($persentaseInvestorTotal / 100) * $proportion;

            $inv->laba_sementara = $laba_sementara;
            $inv->user_profit = $userProfit;
            $inv->estimasi_user_profit = $estimasiUserProfit;
            $inv->roi = $inv->besaran_investasi > 0 ? ($userProfit / $inv->besaran_investasi) * 100 : 0;
            $inv->estimasi_roi = $inv->besaran_investasi > 0 ? ($estimasiUserProfit / $inv->besaran_investasi) * 100 : 0;
            
            $totalInvestment += $inv->besaran_investasi;
            $totalReturn += $userProfit;
            $totalEstimatedFinalReturn += $estimasiUserProfit;
        }

        return view('investor.portfolio', compact('user', 'investments', 'totalInvestment', 'totalReturn', 'totalEstimatedFinalReturn'));
    }

    public function show($uuid)
    {
        $user = Auth::user();
        $entityIds = $user->entities()->pluck('entities.id')->toArray();

        $pertanian = \App\Models\Pertanian::where('uuid', $uuid)
            ->with(['kebun', 'tanamans.tanaman', 'biayas', 'incomes', 'purchases.items', 'workerJobs', 'updates.user'])
            ->firstOrFail();

        // Ensure user is an investor in this farm (through any entity)
        $inv = PertanianInvestor::where('pertanian_id', $pertanian->id)
            ->whereIn('entity_id', $entityIds)
            ->firstOrFail();

        $totalIncome = $pertanian->incomes->sum('nominal');
        $totalPurchase = $pertanian->purchases->sum(function($p) { return $p->items->sum('total_price'); });
        $totalWorker = $pertanian->workerJobs->sum('wage');
        
        $laba_sementara = $totalIncome - $totalPurchase - $totalWorker;
        
        // Calculate Estimated Final Profit
        $estimasiPendapatan = $pertanian->tanamans->sum(function($t) {
            return $t->qty_pohon * $t->estimasi_berat_per_pohon * $t->estimasi_harga_per_kg;
        });
        $estimasiBiaya = $pertanian->biayas->sum('total');
        $estimasiLaba = $estimasiPendapatan - $estimasiBiaya;

        // Investor's share of the profit
        $persentaseInvestorTotal = $pertanian->persentase_investor ?? 0;
        $batasanInvestasi = $pertanian->batasan_investasi > 0 ? $pertanian->batasan_investasi : 1; 
        $proportion = $inv->besaran_investasi / $batasanInvestasi;
        $invPercentage = $proportion * 100;
        
        $userProfit = $laba_sementara * ($persentaseInvestorTotal / 100) * $proportion;
        $estimasiUserProfit = $estimasiLaba * ($persentaseInvestorTotal / 100) * $proportion;

        $inv->laba_sementara = $laba_sementara;
        $inv->user_profit = $userProfit;
        $inv->estimasi_user_profit = $estimasiUserProfit;
        $inv->roi = $inv->besaran_investasi > 0 ? ($userProfit / $inv->besaran_investasi) * 100 : 0;
        $inv->estimasi_roi = $inv->besaran_investasi > 0 ? ($estimasiUserProfit / $inv->besaran_investasi) * 100 : 0;

        // Withdrawals specifically for this investor entity
        $entityUserIds = $inv->entity ? $inv->entity->users->pluck('id')->toArray() : [];
        $withdrawals = \App\Models\Withdrawal::where('pertanian_id', $pertanian->id)
            ->whereIn('user_id', $entityUserIds)
            ->where('role', 'investor')
            ->latest('date')
            ->get();
            
        $ditarikTotal = $withdrawals->sum('amount');
        $sisaBisaDitarik = $userProfit - $ditarikTotal;

        return view('investor.pertanian-show', compact(
            'user', 'pertanian', 'inv', 'totalIncome', 'totalPurchase', 'totalWorker', 
            'estimasiPendapatan', 'estimasiBiaya', 'estimasiLaba', 'batasanInvestasi', 'invPercentage',
            'withdrawals', 'ditarikTotal', 'sisaBisaDitarik'
        ));
    }

    public function opportunities()
    {
        $user = Auth::user();
        $opportunities = \App\Models\Pertanian::where('status', 'Pencarian Investor')
            ->with(['kebun', 'tanamans.tanaman', 'biayas'])
            ->latest()
            ->get();

        return view('investor.opportunities', compact('user', 'opportunities'));
    }

    public function profile()
    {
        $user = Auth::user();
        $entityIds = $user->entities()->pluck('entities.id')->toArray();

        // Calculate simple stats
        $investments = PertanianInvestor::whereIn('entity_id', $entityIds)->get();
        $totalInvestment = $investments->sum('besaran_investasi');
        $activeProjects = $investments->count();

        return view('investor.profile', compact('user', 'totalInvestment', 'activeProjects'));
    }

    public function editProfile()
    {
        $user = Auth::user();
        return view('investor.profile-edit', compact('user'));
    }

    public function updateProfile(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users')->ignore($user->id)],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->whatsapp = $request->whatsapp;

        if ($request->filled('password')) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('investor.profile')->with('success', 'Profil berhasil diperbarui!');
    }

    public function withdrawalHistory()
    {
        $user = Auth::user();
        $entityIds = $user->entities()->pluck('entities.id')->toArray();
        
        // Find all member user IDs for all entities this user belongs to
        $memberUserIds = \Illuminate\Support\Facades\DB::table('entity_user')
            ->whereIn('entity_id', $entityIds)
            ->pluck('user_id')
            ->toArray();
            
        // Include the user themselves
        $memberUserIds[] = $user->id;
        $memberUserIds = array_unique($memberUserIds);

        $withdrawals = \App\Models\Withdrawal::whereIn('user_id', $memberUserIds)
            ->with(['pertanian.kebun'])
            ->latest('date')
            ->get();

        $totalDitarik = $withdrawals->sum('amount');

        return view('investor.withdrawals', compact('user', 'withdrawals', 'totalDitarik'));
    }

    public function projectDetail($uuid)
    {
        $user = Auth::user();
        $entityIds = $user->entities()->pluck('entities.id')->toArray();

        $pertanian = \App\Models\Pertanian::where('uuid', $uuid)
            ->with(['kebun', 'admin', 'pengelolaEntity', 'tanamans.tanaman', 'biayas', 'incomes', 'purchases', 'workerJobs', 'updates.user'])
            ->firstOrFail();

        // Determine user's role in this project
        $userRole = null;
        $persentase = 0;

        if ($pertanian->admin_id == $user->id) {
            $userRole = 'admin';
            $persentase = $pertanian->persentase_admin ?? 0;
        } elseif (in_array($pertanian->pengelola_entity_id, $entityIds)) {
            $userRole = 'pengelola';
            $persentase = $pertanian->persentase_pengelola ?? 0;
        }

        if (!$userRole) {
            abort(403, 'Anda tidak memiliki akses ke proyek ini.');
        }

        // Calculate financials
        $totalIncome = $pertanian->incomes->sum('amount');
        $totalPurchase = $pertanian->purchases->sum('total_amount');
        $totalWorker = $pertanian->workerJobs->sum('wage');
        $labaBersih = $totalIncome - $totalPurchase - $totalWorker;

        $zakatPersen = $pertanian->persentase_zakat ?? 5;
        $zakat = $labaBersih > 0 ? $labaBersih * ($zakatPersen / 100) : 0;
        $labaSetelahZakat = $labaBersih - $zakat;

        $alokasiUser = $labaSetelahZakat > 0 ? $labaSetelahZakat * ($persentase / 100) : 0;

        // Estimasi
        $estimasiPendapatan = $pertanian->tanamans->sum(function($t) {
            return $t->qty_pohon * $t->estimasi_berat_per_pohon * $t->estimasi_harga_per_kg;
        });
        $estimasiBiaya = $pertanian->biayas->sum('total');
        $estimasiLaba = $estimasiPendapatan - $estimasiBiaya;
        $estimasiZakat = $estimasiLaba > 0 ? $estimasiLaba * ($zakatPersen / 100) : 0;
        $estimasiSetelahZakat = $estimasiLaba - $estimasiZakat;
        $estimasiAlokasiUser = $estimasiSetelahZakat > 0 ? $estimasiSetelahZakat * ($persentase / 100) : 0;

        // Withdrawals
        if ($userRole === 'admin') {
            $withdrawals = \App\Models\Withdrawal::where('pertanian_id', $pertanian->id)
                ->where('user_id', $user->id)
                ->where('role', 'admin')
                ->latest('date')
                ->get();
        } else {
            // For pengelola entity
            $entityUserIds = $pertanian->pengelolaEntity ? $pertanian->pengelolaEntity->users->pluck('id')->toArray() : [];
            $withdrawals = \App\Models\Withdrawal::where('pertanian_id', $pertanian->id)
                ->whereIn('user_id', $entityUserIds)
                ->where('role', 'pengelola')
                ->latest('date')
                ->get();
        }

        $ditarikTotal = $withdrawals->sum('amount');
        $sisaBisaDitarik = $alokasiUser - $ditarikTotal;

        return view('investor.project-detail', compact(
            'user', 'pertanian', 'userRole', 'persentase',
            'totalIncome', 'totalPurchase', 'totalWorker', 'labaBersih',
            'zakat', 'labaSetelahZakat', 'alokasiUser',
            'estimasiPendapatan', 'estimasiBiaya', 'estimasiLaba', 'estimasiAlokasiUser',
            'withdrawals', 'ditarikTotal', 'sisaBisaDitarik'
        ));
    }

    public function laporan($uuid)
    {
        $user = Auth::user();
        $entityIds = $user->entities()->pluck('entities.id')->toArray();

        // Ambil data pertanian dan relasinya
        $pertanian = \App\Models\Pertanian::where('uuid', $uuid)
            ->with(['kebun', 'admin', 'incomes', 'purchases.items', 'purchases.store', 'workerJobs.worker', 'workerJobs.category'])
            ->firstOrFail();

        // Pastikan user berhak melihat (sebagai admin, pengelola, atau investor)
        $isInvestor = PertanianInvestor::where('pertanian_id', $pertanian->id)->whereIn('entity_id', $entityIds)->exists();
        $isAdmin = $pertanian->admin_id == $user->id;
        $isPengelola = in_array($pertanian->pengelola_entity_id, $entityIds);

        if (!$isInvestor && !$isAdmin && !$isPengelola) {
            abort(403, 'Anda tidak memiliki akses ke laporan proyek ini.');
        }

        // Hitung total
        $totalIncome = $pertanian->incomes->sum('nominal');
        $totalPurchase = $pertanian->purchases->sum(function($p) { return $p->items->sum('total_price'); });
        $totalWorker = $pertanian->workerJobs->sum('wage');
        $laba_sementara = $totalIncome - $totalPurchase - $totalWorker;

        return view('investor.laporan-print', compact(
            'user', 'pertanian', 'totalIncome', 'totalPurchase', 'totalWorker', 'laba_sementara'
        ));
    }
}
