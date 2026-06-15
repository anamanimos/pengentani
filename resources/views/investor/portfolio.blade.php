@extends('layouts.investor')

@section('content')
<div class="hero-card">
    <div class="user-greeting">
        <div>
            <div class="name">Hai, {{ explode(' ', $user->name)[0] }}</div>
            <div class="subtitle">Selamat datang di Portofoliomu</div>
        </div>
        <div class="avatar">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
    </div>

    <div class="portfolio-balance">
        <div class="label">Total Nilai Investasi</div>
        <div class="amount">Rp {{ number_format($totalInvestment + $totalReturn, 0, ',', '.') }}</div>
        
        @if($totalInvestment > 0)
            @php $totalRoi = ($totalReturn / $totalInvestment) * 100; @endphp
            <div class="roi">
                <span style="font-weight: 600; color: {{ $totalRoi >= 0 ? '#d1fae5' : '#fecaca' }}">
                    {{ $totalRoi >= 0 ? '+' : '' }}{{ number_format($totalRoi, 2, ',', '.') }}% (Rp {{ number_format($totalReturn, 0, ',', '.') }})
                </span>
            </div>
            <div style="margin-top: 15px; font-size: 0.85rem; opacity: 0.9;">
                Estimasi Akhir: <strong>Rp {{ number_format($totalInvestment + $totalEstimatedFinalReturn, 0, ',', '.') }}</strong>
            </div>
        @else
            <div class="roi">
                <span style="font-weight: 600;">0.00% (Rp 0)</span>
            </div>
        @endif
    </div>
</div>

<div class="content-area">
    <h2 class="section-title">Portofolio Aktif Anda</h2>

    @forelse($investments as $inv)
    <a href="{{ route('investor.pertanian.show', $inv->pertanian->uuid) }}" class="investment-card" style="display: block; text-decoration: none; color: inherit;">
        <div class="card-header">
            <div class="card-title">{{ $inv->pertanian->name ?? 'Proyek Tidak Diketahui' }}</div>
            <div class="card-status">{{ ucfirst($inv->pertanian->status ?? 'pending') }}</div>
        </div>
        
        <div class="card-body">
            <div class="data-group">
                <div class="label">Modal Disetor</div>
                <div class="value">Rp {{ number_format($inv->besaran_investasi, 0, ',', '.') }}</div>
            </div>
            <div class="data-group">
                <div class="label">Bagi Hasil Saat Ini</div>
                <div class="value {{ $inv->user_profit >= 0 ? 'success' : 'danger' }}">
                    {{ $inv->user_profit >= 0 ? '+' : '' }}Rp {{ number_format($inv->user_profit, 0, ',', '.') }}
                </div>
            </div>
            <div class="data-group">
                <div class="label">Estimasi Keuntungan Akhir</div>
                <div class="value {{ $inv->estimasi_user_profit >= 0 ? 'success' : 'danger' }}">
                    {{ $inv->estimasi_user_profit >= 0 ? '+' : '' }}Rp {{ number_format($inv->estimasi_user_profit, 0, ',', '.') }}
                </div>
            </div>
            <div class="data-group">
                <div class="label">Estimasi ROI Akhir</div>
                <div class="value {{ $inv->estimasi_roi >= 0 ? 'success' : 'danger' }}">
                    {{ $inv->estimasi_roi >= 0 ? '+' : '' }}{{ number_format($inv->estimasi_roi, 2, ',', '.') }}%
                </div>
            </div>
        </div>
    </a>
    @empty
    <div style="text-align: center; padding: 40px 20px; background: var(--surface); border-radius: 16px; margin-top: 20px;">
        <svg viewBox="0 0 24 24" style="width: 48px; height: 48px; fill: var(--text-muted); opacity: 0.5; margin-bottom: 10px;">
            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 9h-2V7h-2v5H6v2h2v5h2v-5h2v-2z"/>
        </svg>
        <div style="font-weight: 500; color: var(--text-muted);">Belum Ada Portofolio</div>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 5px;">Anda belum melakukan investasi pada proyek pertanian apapun.</p>
    </div>
    @endforelse

</div>
@endsection
