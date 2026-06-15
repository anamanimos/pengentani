<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// INVESTOR ROUTES (Root Level)
Route::get('/', [\App\Http\Controllers\InvestorDashboardController::class, 'home'])->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/dashboard', [\App\Http\Controllers\InvestorDashboardController::class, 'home'])->middleware(['auth', 'verified']);
Route::get('/portofolio', [\App\Http\Controllers\InvestorDashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('investor.portfolio');
Route::get('/investor/pertanian/{uuid}', [\App\Http\Controllers\InvestorDashboardController::class, 'show'])->middleware(['auth', 'verified'])->name('investor.pertanian.show');
Route::get('/peluang', [\App\Http\Controllers\InvestorDashboardController::class, 'opportunities'])->middleware(['auth', 'verified'])->name('investor.opportunities');
Route::get('/penarikan', [\App\Http\Controllers\InvestorDashboardController::class, 'withdrawalHistory'])->middleware(['auth', 'verified'])->name('investor.withdrawals');
Route::get('/project/{uuid}', [\App\Http\Controllers\InvestorDashboardController::class, 'projectDetail'])->middleware(['auth', 'verified'])->name('investor.project.detail');
Route::get('/profile', [\App\Http\Controllers\InvestorDashboardController::class, 'profile'])->middleware(['auth', 'verified'])->name('investor.profile');
Route::get('/profile/edit', [\App\Http\Controllers\InvestorDashboardController::class, 'editProfile'])->middleware(['auth', 'verified'])->name('investor.profile.edit');
Route::patch('/profile', [\App\Http\Controllers\InvestorDashboardController::class, 'updateProfile'])->middleware(['auth', 'verified'])->name('investor.profile.update');

// AUTO-LOGIN ROUTES
Route::get('/autologin/{user}', [\App\Http\Controllers\AutoLoginController::class, 'handle'])->name('autologin')->middleware('signed');
Route::post('/autologin/{user}/force', [\App\Http\Controllers\AutoLoginController::class, 'forceLogin'])->name('autologin.force')->middleware('signed');

// ADMIN / CONSOLE ROUTES
Route::prefix('console')->middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('console.dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::prefix('incomes')->group(function () {
        Route::resource('tengkulaks', \App\Http\Controllers\TengkulakController::class)->except(['create', 'show', 'edit']);
    });
    
    // Withdrawals
    Route::post('pertanians/{pertanian}/withdrawals', [\App\Http\Controllers\WithdrawalController::class, 'store'])->name('withdrawals.store');
    Route::delete('withdrawals/{withdrawal}', [\App\Http\Controllers\WithdrawalController::class, 'destroy'])->name('withdrawals.destroy');
    
    Route::resource('kebuns', \App\Http\Controllers\KebunController::class);
    
    // Auto Login Generation (Admin side) -> Actually we don't need a specific route for generation, just the endpoint
    
    // User Management
    Route::post('users/stop-impersonate', [\App\Http\Controllers\UserController::class, 'stopImpersonate'])->name('users.stop_impersonate');
    Route::post('users/{user}/impersonate', [\App\Http\Controllers\UserController::class, 'impersonate'])->name('users.impersonate');
    Route::resource('users', \App\Http\Controllers\UserController::class);
    
    // Master Tanaman
    Route::resource('tanamans', \App\Http\Controllers\TanamanController::class);
    Route::post('tanamans/ajax', [\App\Http\Controllers\TanamanController::class, 'storeAjax'])->name('tanamans.ajax');

    // Pertanian (Farm Plan)
    Route::resource('pertanians', \App\Http\Controllers\PertanianController::class);

    // Pencatatan Pembelian (Purchases)
    Route::resource('purchases/categories', \App\Http\Controllers\PurchaseCategoryController::class)
        ->names('purchase-categories')
        ->parameters(['categories' => 'purchase_category']);
    Route::get('purchases/ajax-dropdowns', [\App\Http\Controllers\PurchaseController::class, 'getDropdownsAjax'])->name('purchases.ajax-dropdowns');
    Route::post('purchases/ajax-store', [\App\Http\Controllers\PurchaseController::class, 'storeStoreAjax'])->name('purchases.ajax-store');
    Route::post('purchases/ajax-category', [\App\Http\Controllers\PurchaseController::class, 'storeCategoryAjax'])->name('purchases.ajax-category');
    Route::resource('purchases', \App\Http\Controllers\PurchaseController::class)->except(['create', 'show', 'edit', 'update']);
    Route::resource('purchases/vendor', \App\Http\Controllers\StoreController::class)
        ->names('stores')
        ->parameters(['vendor' => 'store']);
        
    // Pencatatan Pekerja
    Route::get('worker-jobs/ajax-dropdowns', [\App\Http\Controllers\WorkerJobController::class, 'getDropdownsAjax'])->name('worker-jobs.ajax-dropdowns');
    Route::post('worker-jobs/ajax-worker', [\App\Http\Controllers\WorkerJobController::class, 'storeWorkerAjax'])->name('worker-jobs.ajax-worker');
    Route::post('worker-jobs/ajax-category', [\App\Http\Controllers\WorkerJobController::class, 'storeCategoryAjax'])->name('worker-jobs.ajax-category');
    Route::get('worker-jobs/export', [\App\Http\Controllers\WorkerJobController::class, 'export'])->name('worker-jobs.export');
    Route::resource('worker-jobs/categories', \App\Http\Controllers\JobCategoryController::class)->names('job-categories');
    Route::resource('worker-jobs', \App\Http\Controllers\WorkerJobController::class)->except(['create', 'show', 'edit', 'update']);

    // Pencatatan Pendapatan (Incomes)
    Route::resource('incomes', \App\Http\Controllers\IncomeController::class)->except(['create', 'show', 'edit', 'update']);

    // Pertanian Investors
    Route::get('pertanians/{pertanian}/investors', [\App\Http\Controllers\PertanianInvestorController::class, 'index'])->name('pertanians.investors.index');
    Route::get('pertanians/{pertanian}/investors/create', [\App\Http\Controllers\PertanianInvestorController::class, 'create'])->name('pertanians.investors.create');
    Route::post('pertanians/{pertanian}/investors', [\App\Http\Controllers\PertanianInvestorController::class, 'store'])->name('pertanians.investors.store');
    Route::get('pertanians/{pertanian}/investors/{investor}/edit', [\App\Http\Controllers\PertanianInvestorController::class, 'edit'])->name('pertanians.investors.edit');
    Route::put('pertanians/{pertanian}/investors/{investor}', [\App\Http\Controllers\PertanianInvestorController::class, 'update'])->name('pertanians.investors.update');
    Route::delete('pertanians/{pertanian}/investors/{investor}', [\App\Http\Controllers\PertanianInvestorController::class, 'destroy'])->name('pertanians.investors.destroy');

    // Pertanian Updates (Informasi)
    Route::get('updates', [\App\Http\Controllers\PertanianUpdateController::class, 'globalIndex'])->name('updates.global_index');
    Route::get('updates/create', [\App\Http\Controllers\PertanianUpdateController::class, 'globalCreate'])->name('updates.global_create');
    Route::post('updates', [\App\Http\Controllers\PertanianUpdateController::class, 'globalStore'])->name('updates.global_store');
    Route::resource('pertanians.updates', \App\Http\Controllers\PertanianUpdateController::class)->except(['show']);
});

require __DIR__.'/auth.php';
