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

        // === DATA INVESTOR ===
        $investorData = null;
        $investments = PertanianInvestor::where('user_id', $user->id)
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

            $totalDitarikInvestor = \App\Models\Withdrawal::where('user_id', $user->id)
                ->where('role', 'investor')->sum('amount');

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
        $pengelolaProjects = \App\Models\Pertanian::where('pengelola_id', $user->id)
            ->with(['kebun', 'incomes', 'purchases', 'workerJobs'])
            ->get();

        if ($pengelolaProjects->count() > 0) {
            $totalReturnPengelola = 0;
            foreach ($pengelolaProjects as $p) {
                $labaSetelahZakat = $this->hitungLabaSetelahZakat($p);
                $totalReturnPengelola += $labaSetelahZakat > 0 ? $labaSetelahZakat * (($p->persentase_pengelola ?? 0) / 100) : 0;
            }

            $totalDitarikPengelola = \App\Models\Withdrawal::where('user_id', $user->id)
                ->where('role', 'pengelola')->sum('amount');

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

        $investments = PertanianInvestor::where('user_id', $user->id)
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

        $pertanian = \App\Models\Pertanian::where('uuid', $uuid)
            ->with(['kebun', 'tanamans.tanaman', 'biayas', 'incomes', 'purchases.items', 'workerJobs', 'updates.user'])
            ->firstOrFail();

        // Ensure user is an investor in this farm
        $inv = PertanianInvestor::where('pertanian_id', $pertanian->id)
            ->where('user_id', $user->id)
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

        // Withdrawals specifically for this investor
        $withdrawals = \App\Models\Withdrawal::where('pertanian_id', $pertanian->id)
            ->where('user_id', $user->id)
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

        // Calculate simple stats
        $investments = PertanianInvestor::where('user_id', $user->id)->get();
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

        $withdrawals = \App\Models\Withdrawal::where('user_id', $user->id)
            ->with(['pertanian.kebun'])
            ->latest('date')
            ->get();

        $totalDitarik = $withdrawals->sum('amount');

        return view('investor.withdrawals', compact('user', 'withdrawals', 'totalDitarik'));
    }

    public function projectDetail($uuid)
    {
        $user = Auth::user();

        $pertanian = \App\Models\Pertanian::where('uuid', $uuid)
            ->with(['kebun', 'admin', 'pengelola', 'tanamans.tanaman', 'biayas', 'incomes', 'purchases', 'workerJobs', 'updates.user'])
            ->firstOrFail();

        // Determine user's role in this project
        $userRole = null;
        $persentase = 0;

        if ($pertanian->admin_id == $user->id) {
            $userRole = 'admin';
            $persentase = $pertanian->persentase_admin ?? 0;
        } elseif ($pertanian->pengelola_id == $user->id) {
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
        $withdrawals = \App\Models\Withdrawal::where('pertanian_id', $pertanian->id)
            ->where('user_id', $user->id)
            ->where('role', $userRole)
            ->latest('date')
            ->get();

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
}
