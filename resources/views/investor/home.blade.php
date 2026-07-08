@extends('layouts.investor')

@section('content')
<div class="hero-card">
    <div class="user-greeting">
        <div>
            <div class="name">Hai, {{ explode(' ', $user->name)[0] }}</div>
            <div class="subtitle">Selamat datang di Beranda Pengen Tani</div>
        </div>
        <div class="avatar">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
    </div>

    {{-- Gabungan Total Keuntungan --}}
    @php
        $grandTotal = 0;
        $grandDitarik = 0;
        if ($investorData) { $grandTotal += $investorData->totalReturn + $investorData->totalInvestment; $grandDitarik += $investorData->totalDitarik; }
        if ($adminData) { $grandTotal += $adminData->totalReturn; $grandDitarik += $adminData->totalDitarik; }
        if ($pengelolaData) { $grandTotal += $pengelolaData->totalReturn; $grandDitarik += $pengelolaData->totalDitarik; }
    @endphp

    <div class="portfolio-balance" style="margin-top: 20px;">
        <h3 style="color: var(--text-main); font-weight: 600; font-size: 1.1rem; margin-bottom: 10px;">Total Saldo Anda</h3>
        <div class="label" style="color: var(--text-muted);">Dari Semua Peran (Termasuk Modal)</div>
        <div class="amount" style="color: var(--text-main);">Rp {{ number_format($grandTotal, 0, ',', '.') }}</div>
        <div style="display: flex; justify-content: space-between; margin-top: 15px; font-size: 0.85rem; color: var(--text-main);">
            <div>
                <div style="color: var(--text-muted);">Sudah Ditarik</div>
                <div style="font-weight: 700; font-size: 1rem; color: var(--danger);">Rp {{ number_format($grandDitarik, 0, ',', '.') }}</div>
            </div>
            <div style="text-align: right;">
                <div style="color: var(--text-muted);">Sisa Saldo</div>
                <div style="font-weight: 700; font-size: 1rem; color: var(--primary);">Rp {{ number_format($grandTotal - $grandDitarik, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="content-area">

    {{-- SECTION: INVESTOR --}}
    @if($investorData)
    <div class="investment-card" style="margin-bottom: 20px;">
        <h3 class="section-title" style="margin-bottom: 12px; font-size: 1.05rem;">
            <i class="ki-duotone ki-chart-line text-success me-2"><span class="path1"></span><span class="path2"></span></i>
            Sebagai Investor
        </h3>
        <div class="card-body" style="grid-template-columns: 1fr;">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 10px; margin-bottom: 10px;">
                <span style="color: var(--text-muted); font-size: 0.9rem;">Total Modal Disetor</span>
                <span style="font-weight: 600;">Rp {{ number_format($investorData->totalInvestment, 0, ',', '.') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 10px; margin-bottom: 10px;">
                <span style="color: var(--text-muted); font-size: 0.9rem;">Keuntungan Bagi Hasil</span>
                <span style="font-weight: 700; color: {{ $investorData->totalReturn >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                    {{ $investorData->totalReturn >= 0 ? '+' : '' }}Rp {{ number_format($investorData->totalReturn, 0, ',', '.') }}
                </span>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 10px; margin-bottom: 10px; background: rgba(0,0,0,0.02); padding: 10px; border-radius: 8px;">
                <span style="color: var(--text-muted); font-size: 0.9rem;">Total Saldo (Modal + Hasil)</span>
                <span style="font-weight: 700;">Rp {{ number_format($investorData->totalInvestment + $investorData->totalReturn, 0, ',', '.') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 10px; margin-bottom: 10px;">
                <span style="color: var(--text-muted); font-size: 0.9rem;">Sudah Ditarik</span>
                <span style="font-weight: 600; color: var(--danger);">- Rp {{ number_format($investorData->totalDitarik, 0, ',', '.') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted); font-size: 0.9rem; font-weight: 600;">Sisa Bisa Ditarik</span>
                <span style="font-weight: 700; color: var(--success); font-size: 1.05rem;">Rp {{ number_format($investorData->sisa, 0, ',', '.') }}</span>
            </div>
        </div>
        <a href="{{ route('investor.portfolio') }}" style="display: block; text-align: center; margin-top: 15px; padding: 10px; background: var(--light); border-radius: 10px; text-decoration: none; color: var(--primary); font-weight: 600; font-size: 0.9rem;">
            Lihat {{ $investorData->projectCount }} Proyek Investasi →
        </a>
    </div>
    @endif

    {{-- SECTION: ADMIN --}}
    @if($adminData)
    <div class="investment-card" style="margin-bottom: 20px;">
        <h3 class="section-title" style="margin-bottom: 12px; font-size: 1.05rem;">
            <i class="ki-duotone ki-user text-primary me-2"><span class="path1"></span><span class="path2"></span></i>
            Sebagai Admin
        </h3>
        <div class="card-body" style="grid-template-columns: 1fr;">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 10px; margin-bottom: 10px;">
                <span style="color: var(--text-muted); font-size: 0.9rem;">Total Keuntungan</span>
                <span style="font-weight: 700; color: {{ $adminData->totalReturn >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                    Rp {{ number_format($adminData->totalReturn, 0, ',', '.') }}
                </span>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 10px; margin-bottom: 10px;">
                <span style="color: var(--text-muted); font-size: 0.9rem;">Sudah Ditarik</span>
                <span style="font-weight: 600; color: var(--danger);">- Rp {{ number_format($adminData->totalDitarik, 0, ',', '.') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted); font-size: 0.9rem; font-weight: 600;">Sisa Bisa Ditarik</span>
                <span style="font-weight: 700; color: var(--success); font-size: 1.05rem;">Rp {{ number_format($adminData->sisa, 0, ',', '.') }}</span>
            </div>
        </div>
        <div style="margin-top: 15px;">
            <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px; font-weight: 600;">Proyek yang Dikelola ({{ $adminData->projects->count() }})</div>
            @foreach($adminData->projects as $p)
            <a href="{{ route('investor.project.detail', $p->uuid) }}" style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--light); border-radius: 10px; margin-bottom: 8px; text-decoration: none; color: inherit;">
                <div>
                    <div style="font-weight: 600; font-size: 0.95rem;">{{ $p->name }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $p->kebun->name ?? '-' }}</div>
                </div>
                <i class="ki-duotone ki-arrow-right text-muted"><span class="path1"></span><span class="path2"></span></i>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- SECTION: PENGELOLA --}}
    @if($pengelolaData)
    <div class="investment-card" style="margin-bottom: 20px;">
        <h3 class="section-title" style="margin-bottom: 12px; font-size: 1.05rem;">
            <i class="ki-duotone ki-user-edit text-info me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
            Sebagai Pengelola Lahan
        </h3>
        <div class="card-body" style="grid-template-columns: 1fr;">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 10px; margin-bottom: 10px;">
                <span style="color: var(--text-muted); font-size: 0.9rem;">Total Keuntungan</span>
                <span style="font-weight: 700; color: {{ $pengelolaData->totalReturn >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                    Rp {{ number_format($pengelolaData->totalReturn, 0, ',', '.') }}
                </span>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 10px; margin-bottom: 10px;">
                <span style="color: var(--text-muted); font-size: 0.9rem;">Sudah Ditarik</span>
                <span style="font-weight: 600; color: var(--danger);">- Rp {{ number_format($pengelolaData->totalDitarik, 0, ',', '.') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted); font-size: 0.9rem; font-weight: 600;">Sisa Bisa Ditarik</span>
                <span style="font-weight: 700; color: var(--success); font-size: 1.05rem;">Rp {{ number_format($pengelolaData->sisa, 0, ',', '.') }}</span>
            </div>
        </div>
        <div style="margin-top: 15px;">
            <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px; font-weight: 600;">Proyek yang Dikelola ({{ $pengelolaData->projects->count() }})</div>
            @foreach($pengelolaData->projects as $p)
            <a href="{{ route('investor.project.detail', $p->uuid) }}" style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--light); border-radius: 10px; margin-bottom: 8px; text-decoration: none; color: inherit;">
                <div>
                    <div style="font-weight: 600; font-size: 0.95rem;">{{ $p->name }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $p->kebun->name ?? '-' }}</div>
                </div>
                <i class="ki-duotone ki-arrow-right text-muted"><span class="path1"></span><span class="path2"></span></i>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- AKSES CEPAT --}}
    <h2 class="section-title">Akses Cepat</h2>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
        <a href="{{ route('investor.portfolio') }}" style="background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-decoration: none; color: #1e293b; display: flex; flex-direction: column; align-items: center;">
            <i class="ki-duotone ki-briefcase fs-3x text-primary mb-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
            <span style="font-weight: 600;">Portofolio</span>
        </a>
        <a href="{{ route('investor.withdrawals') }}" style="background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-decoration: none; color: #1e293b; display: flex; flex-direction: column; align-items: center;">
            <i class="ki-duotone ki-wallet fs-3x text-success mb-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
            <span style="font-weight: 600;">Penarikan</span>
        </a>
        <a href="{{ route('investor.opportunities') }}" style="background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-decoration: none; color: #1e293b; display: flex; flex-direction: column; align-items: center;">
            <i class="ki-duotone ki-star fs-3x text-warning mb-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
            <span style="font-weight: 600;">Cari Peluang</span>
        </a>
        <a href="{{ route('investor.profile') }}" style="background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-decoration: none; color: #1e293b; display: flex; flex-direction: column; align-items: center;">
            <i class="ki-duotone ki-user fs-3x text-info mb-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
            <span style="font-weight: 600;">Profil</span>
        </a>
    </div>
</div>
@endsection
