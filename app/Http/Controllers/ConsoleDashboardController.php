<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Income;
use App\Models\Purchase;
use App\Models\PertanianInvestor;
use Carbon\Carbon;

class ConsoleDashboardController extends Controller
{
    public function index()
    {
        // 1. Top KPI Metrics
        $totalIncome = Income::sum('amount') ?? 0;
        $totalExpense = Purchase::sum('total_amount') ?? 0;
        $totalInvestment = PertanianInvestor::where('status', 'paid')->sum('besaran_investasi') ?? 0;
        $netProfit = $totalIncome - $totalExpense;

        // 2. Monthly Trend (last 6 months)
        $monthlyIncome = [];
        $monthlyExpense = [];
        $months = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth()->format('Y-m-d');
            $monthEnd = $date->copy()->endOfMonth()->format('Y-m-d');
            $months[] = $date->translatedFormat('M Y');

            $monthlyIncome[] = Income::whereBetween('date', [$monthStart, $monthEnd])->sum('amount') ?? 0;
            $monthlyExpense[] = Purchase::whereBetween('date', [$monthStart, $monthEnd])->sum('total_amount') ?? 0;
        }

        // 3. Recent Activity (Keuangan)
        $recentIncomes = Income::with('pertanian')->latest('date')->take(5)->get();
        $recentPurchases = Purchase::with(['pertanian', 'store'])->latest('date')->take(5)->get();

        // 4. Aktivitas Pertanian
        $totalActiveProjects = \App\Models\Pertanian::where('status', 'aktif')->count() ?? 0;
        $totalProjects = \App\Models\Pertanian::count() ?? 0;
        $recentProjects = \App\Models\Pertanian::latest()->take(5)->get();
        $recentUpdates = \App\Models\PertanianUpdate::with('pertanian')->latest()->take(5)->get();

        return view('dashboard', compact(
            'totalIncome', 
            'totalExpense', 
            'totalInvestment', 
            'netProfit',
            'months',
            'monthlyIncome',
            'monthlyExpense',
            'recentIncomes',
            'recentPurchases',
            'totalActiveProjects',
            'totalProjects',
            'recentProjects',
            'recentUpdates'
        ));
    }
}
