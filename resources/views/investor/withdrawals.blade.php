@extends('layouts.investor')

@section('content')
<div class="hero-card" style="padding-bottom: 20px;">
    <div style="margin-bottom: 20px;">
        <a href="{{ route('dashboard') }}" style="color: white; text-decoration: none; display: flex; align-items: center; font-size: 0.9rem; font-weight: 500;">
            <i class="ki-duotone ki-arrow-left fs-3 me-2 text-white"><span class="path1"></span><span class="path2"></span></i>
            Kembali
        </a>
    </div>
    <div style="text-align: center; position: relative; z-index: 2;">
        <div style="font-size: 0.85rem; opacity: 0.9; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">Riwayat</div>
        <h1 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 10px; line-height: 1.2;">Penarikan Dana</h1>
        <div style="font-size: 1.4rem; font-weight: 700; margin-top: 10px;">Rp {{ number_format($totalDitarik, 0, ',', '.') }}</div>
        <div style="font-size: 0.8rem; opacity: 0.8; margin-top: 3px;">Total sudah ditarik</div>
    </div>
</div>

<div class="content-area">
    @forelse($withdrawals as $withdrawal)
    <div style="background: white; border-radius: 12px; padding: 15px; margin-bottom: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
            <div>
                <div style="font-weight: 600; font-size: 0.95rem;">{{ $withdrawal->pertanian->name ?? '-' }}</div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $withdrawal->pertanian->kebun->name ?? '-' }}</div>
            </div>
            <span style="font-weight: 700; color: var(--danger); font-size: 1rem;">
                - Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}
            </span>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 0.8rem; color: var(--text-muted);">
                <i class="ki-duotone ki-calendar me-1"></i> {{ \Carbon\Carbon::parse($withdrawal->date)->format('d M Y') }}
            </span>
            <span style="font-size: 0.75rem; padding: 3px 8px; border-radius: 8px; font-weight: 600; text-transform: capitalize;
                background: {{ $withdrawal->role == 'admin' ? 'rgba(59,130,246,0.1)' : ($withdrawal->role == 'pengelola' ? 'rgba(99,102,241,0.1)' : 'rgba(16,185,129,0.1)') }};
                color: {{ $withdrawal->role == 'admin' ? '#3b82f6' : ($withdrawal->role == 'pengelola' ? '#6366f1' : '#10b981') }};">
                {{ $withdrawal->role }}
            </span>
        </div>
        @if($withdrawal->notes)
        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 8px; padding-top: 8px; border-top: 1px solid rgba(0,0,0,0.05);">
            {{ $withdrawal->notes }}
        </div>
        @endif
        @if($withdrawal->proof_image)
        <div style="margin-top: 8px;">
            <a href="{{ Storage::url($withdrawal->proof_image) }}" target="_blank" style="font-size: 0.8rem; text-decoration: none; background: var(--light); padding: 4px 10px; border-radius: 10px; color: var(--primary); display: inline-flex; align-items: center;">
                <i class="ki-duotone ki-picture me-1"></i> Lihat Bukti
            </a>
        </div>
        @endif
    </div>
    @empty
    <div style="text-align: center; padding: 40px 20px; background: white; border-radius: 16px;">
        <i class="ki-duotone ki-wallet fs-3x text-muted mb-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
        <div style="color: var(--text-muted); font-size: 0.95rem;">Belum ada riwayat penarikan dana.</div>
    </div>
    @endforelse
</div>
@endsection
