@extends('layouts.investor')

@section('content')
<div class="hero-card" style="padding-bottom: 30px;">
    <div style="margin-bottom: 20px;">
        <h2 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 5px;">Peluang Investasi</h2>
        <p style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 0;">Temukan dan biayai proyek pertanian potensial.</p>
    </div>
</div>

<div class="content-area" style="padding-top: 15px;">

    @forelse($opportunities as $opp)
        @php
            // Hitung Kebutuhan Modal
            $totalModal = $opp->biayas->sum('total_price');
            
            // Hitung Estimasi Laba/Rugi (Sederhana)
            $totalEstimasiPendapatan = 0;
            foreach($opp->tanamans as $tanaman) {
                $qty = $tanaman->qty; // Jumlah pohon
                $estBerat = $tanaman->tanaman->estimasi_berat_panen_kg;
                $estHarga = $tanaman->tanaman->estimasi_harga_jual_kg;
                $totalEstimasiPendapatan += ($qty * $estBerat * $estHarga);
            }
            
            $estLaba = $totalEstimasiPendapatan - $totalModal;
            $estROI = $totalModal > 0 ? ($estLaba / $totalModal) * 100 : 0;
        @endphp

        <div class="investment-card" style="position: relative;">
            <div style="position: absolute; top: 15px; right: 15px; background: rgba(16, 185, 129, 0.1); color: var(--primary-dark); padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">
                <i class="ki-duotone ki-verify fs-6 me-1 text-success"><span class="path1"></span><span class="path2"></span></i> Terverifikasi
            </div>
            
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text); margin: 0 0 5px 0; padding-right: 80px;">{{ $opp->name }}</h3>
            <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 15px;">
                <i class="ki-duotone ki-geolocation fs-6 me-1"></i> {{ $opp->kebun->name ?? 'Lokasi Belum Ditentukan' }}
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px; background: rgba(0,0,0,0.02); padding: 10px; border-radius: 12px;">
                <div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Kebutuhan Modal</div>
                    <div style="font-weight: 700; color: var(--text); font-size: 0.95rem;">Rp {{ number_format($totalModal, 0, ',', '.') }}</div>
                </div>
                <div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Estimasi ROI</div>
                    <div style="font-weight: 700; color: var(--primary-dark); font-size: 0.95rem;">+{{ number_format($estROI, 1, ',', '.') }}%</div>
                </div>
            </div>

            <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 15px;">
                Periode Tanam: {{ \Carbon\Carbon::parse($opp->start_date)->format('M Y') }} - {{ \Carbon\Carbon::parse($opp->end_date)->format('M Y') }}
            </p>

            <a href="{{ route('investor.pertanian.show', $opp->uuid) }}" style="display: block; text-align: center; background: var(--primary); color: white; padding: 12px; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 0.9rem; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);">
                Lihat Detail Proyek
            </a>
        </div>
    @empty
        <div style="text-align: center; padding: 40px 20px; background: rgba(255,255,255,0.4); border-radius: 16px; backdrop-filter: blur(10px);">
            <i class="ki-duotone ki-information-5 fs-3x text-muted mb-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
            <div style="font-weight: 600; color: var(--text);">Belum Ada Peluang</div>
            <div style="font-size: 0.85rem; color: var(--text-muted);">Saat ini belum ada proyek pertanian yang membuka pendanaan.</div>
        </div>
    @endforelse

</div>
@endsection
