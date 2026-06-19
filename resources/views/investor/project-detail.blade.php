@extends('layouts.investor')

<style>
    .tab-nav {
        display: flex;
        justify-content: space-between;
        background: rgba(255, 255, 255, 0.4);
        backdrop-filter: blur(10px);
        border-radius: 30px;
        padding: 5px;
        margin: 20px;
        position: relative;
        z-index: 2;
    }
    .tab-btn {
        flex: 1;
        text-align: center;
        padding: 10px 0;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-muted);
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .tab-btn.active {
        background: var(--primary);
        color: white;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }
    .tab-content {
        display: none;
        animation: fadeIn 0.3s ease-in-out;
    }
    .tab-content.active {
        display: block;
    }
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Missing CSS added from pertanian-show */
    .content-area {
        background: white;
        border-radius: 20px 20px 0 0;
        padding: 20px;
        position: relative;
        z-index: 2;
        margin-top: -20px;
        min-height: calc(100vh - 250px);
    }
    .investment-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.02);
    }
    .data-group {
        margin-bottom: 15px;
    }
    .label {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }
    .value {
        font-weight: 600;
        font-size: 1.1rem;
        color: var(--text);
    }
    .detail-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px dashed rgba(0,0,0,0.1);
    }
    .detail-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    /* Timeline CSS */
    .timeline {
        position: relative;
        padding-left: 20px;
        margin-top: 15px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 0;
        top: 5px;
        bottom: 0;
        width: 2px;
        background: rgba(16, 185, 129, 0.3);
    }
    .timeline-item {
        position: relative;
        padding-bottom: 25px;
    }
    .timeline-dot {
        position: absolute;
        left: -24px;
        top: 0;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--primary);
        border: 2px solid white;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
    }
    .timeline-date {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-bottom: 5px;
        font-weight: 600;
    }
    .timeline-card {
        background: white;
        border-radius: 12px;
        padding: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        border: 1px solid rgba(0,0,0,0.02);
    }
</style>

@section('content')
<div class="hero-card" style="padding-bottom: 20px;">
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('dashboard') }}" style="color: white; text-decoration: none; display: flex; align-items: center; font-size: 0.9rem; font-weight: 500;">
            <i class="ki-duotone ki-arrow-left fs-3 me-2 text-white"><span class="path1"></span><span class="path2"></span></i>
            Kembali
        </a>
    </div>
    <div style="text-align: center; position: relative; z-index: 2;">
        <div style="font-size: 0.85rem; opacity: 0.9; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">
            Detail Proyek — {{ ucfirst($userRole) }}
        </div>
        <h1 style="font-size: 1.6rem; font-weight: 700; margin-bottom: 10px; line-height: 1.2;">{{ $pertanian->name }}</h1>
        <div style="display: inline-block; padding: 4px 12px; border-radius: 20px; background: rgba(255,255,255,0.2); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; backdrop-filter: blur(5px);">
            {{ $pertanian->status }}
        </div>
    </div>
</div>

<!-- Tabs -->
<div class="tab-nav">
    <div class="tab-btn active" onclick="switchTab('overview')">Ringkasan</div>
    <div class="tab-btn" onclick="switchTab('keuntungan')">Keuntungan</div>
    <div class="tab-btn" onclick="switchTab('penarikan')">Penarikan</div>
    <div class="tab-btn" onclick="switchTab('info')">Info</div>
    <div class="tab-btn" onclick="switchTab('updates')">Update</div>
</div>

<div class="content-area" style="padding-top: 0;">

    <!-- TAB INFO: Informasi Proyek -->
    <div id="tab-info" class="tab-content">
        <div class="investment-card">
            <h3 class="section-title" style="margin-bottom: 15px; font-size: 1.05rem;">Informasi Proyek</h3>
            <div class="card-body">
                <div class="data-group">
                    <div class="label">Kebun</div>
                    <div class="value" style="font-size: 0.95rem;">{{ $pertanian->kebun->name ?? '-' }}</div>
                </div>
                <div class="data-group">
                    <div class="label">Periode</div>
                    <div class="value" style="font-size: 0.95rem;">
                        {{ \Carbon\Carbon::parse($pertanian->start_date)->format('d M y') }} -
                        {{ $pertanian->end_date ? \Carbon\Carbon::parse($pertanian->end_date)->format('d M y') : 'Selesai' }}
                    </div>
                </div>
                <div class="data-group">
                    <div class="label">Admin</div>
                    <div class="value" style="font-size: 0.95rem;">{{ $pertanian->admin->name ?? '-' }}</div>
                </div>
                <div class="data-group">
                    <div class="label">Pengelola</div>
                    <div class="value" style="font-size: 0.95rem;">{{ $pertanian->pengelola->name ?? '-' }}</div>
                </div>

                @if($pertanian->kebun && $pertanian->kebun->polygon)
                <div class="data-group" style="grid-column: 1 / -1; margin-top: 15px; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 15px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <div class="label" style="margin:0;">Peta Lokasi & Area</div>
                        <select class="form-select form-select-sm map-layer-select" style="font-size: 0.75rem; padding: 4px 20px 4px 8px; border-radius: 6px; border: 1px solid #ddd; background-color: #fff; max-width: 150px;">
                            <option value="hybrid" selected>Satelit + Label (Hybrid)</option>
                            <option value="satelit">Satelit Saja</option>
                            <option value="google_map">Google Maps (Jalan)</option>
                            <option value="streetmap">OpenStreetMap</option>
                        </select>
                    </div>
                    <input type="hidden" id="kebun_polygon" value="{{ $pertanian->kebun->polygon }}">
                    <div id="detail_map" style="height: 250px; width: 100%; border-radius: 12px; z-index: 1;"></div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 5px; font-style: italic;">
                        <i class="ki-duotone ki-information fs-7 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        Lebar area diperkirakan dari pemetaan geolocation (estimasi: {{ number_format($pertanian->kebun->area ?? 0, 2, ',', '.') }} m²)
                    </div>
                </div>
                @endif

                <div class="data-group" style="grid-column: 1 / -1; margin-top: 15px; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 15px;">
                    <div class="label" style="margin-bottom: 10px;">Tanaman yang Ditanam</div>
                    @if($pertanian->tanamans->count() > 0)
                        @foreach($pertanian->tanamans as $tanaman)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px dashed rgba(0,0,0,0.05);">
                            <div>
                                <span style="font-weight: 600; font-size: 0.9rem;">{{ $tanaman->tanaman->name ?? 'Unknown' }}</span>
                                <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">{{ $tanaman->tanaman->variety ?? '-' }}</span>
                            </div>
                            <div style="text-align: right;">
                                <span style="font-weight: 700; color: var(--primary);">{{ number_format($tanaman->qty_pohon, 0, ',', '.') }}</span>
                                <span style="font-size: 0.75rem; color: var(--text-muted);">Pohon/Bibit</span>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div style="font-size: 0.85rem; color: var(--text-muted); font-style: italic;">Belum ada data tanaman.</div>
                    @endif
                </div>
        </div>
    </div> <!-- End Tab Info -->

    <!-- TAB 1: Ringkasan Proyek (Realisasi & Estimasi) -->
    <div id="tab-overview" class="tab-content active">
        <div class="investment-card">
            <h3 class="section-title" style="margin-bottom: 15px; font-size: 1.05rem;">Realisasi Keuangan Proyek</h3>
            <div class="card-body" style="grid-template-columns: 1fr;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 10px; margin-bottom: 10px;">
                    <span style="color: var(--text-muted); font-size: 0.9rem;">Total Pendapatan</span>
                    <span style="font-weight: 600; color: var(--success);">Rp {{ number_format($totalIncome, 0, ',', '.') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 10px; margin-bottom: 10px;">
                    <span style="color: var(--text-muted); font-size: 0.9rem;">Total Pembelian</span>
                    <span style="font-weight: 600; color: var(--danger);">- Rp {{ number_format($totalPurchase, 0, ',', '.') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 10px; margin-bottom: 10px;">
                    <span style="color: var(--text-muted); font-size: 0.9rem;">Total Upah Pekerja</span>
                    <span style="font-weight: 600; color: var(--danger);">- Rp {{ number_format($totalWorker, 0, ',', '.') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 10px; background: rgba(0,0,0,0.02); border-radius: 8px;">
                    <span style="font-weight: 700; font-size: 0.95rem;">Laba Bersih</span>
                    <span style="font-weight: 700; font-size: 1.1rem; color: {{ $labaBersih >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                        Rp {{ number_format($labaBersih, 0, ',', '.') }}
                    </span>
                </div>
                <div style="margin-top: 20px; text-align: center;">
                    <a href="{{ route('investor.pertanian.laporan', $pertanian->uuid) }}" target="_blank" style="display: inline-block; background: var(--primary); color: white; padding: 10px 20px; border-radius: 20px; text-decoration: none; font-weight: 600; font-size: 0.9rem; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3); width: 100%;">
                        <i class="ki-duotone ki-document fs-4 me-2 text-white"><span class="path1"></span><span class="path2"></span></i> Unduh Detail Laporan Keuangan (PDF)
                    </a>
                </div>
            </div>
        </div>

        <div class="investment-card">
            <h3 class="section-title" style="margin-bottom: 15px; font-size: 1.05rem;">Estimasi Proyek</h3>
            <div class="card-body" style="grid-template-columns: 1fr;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 10px; margin-bottom: 10px;">
                    <span style="color: var(--text-muted); font-size: 0.9rem;">Estimasi Pendapatan</span>
                    <span style="font-weight: 600;">Rp {{ number_format($estimasiPendapatan, 0, ',', '.') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 10px; margin-bottom: 10px;">
                    <span style="color: var(--text-muted); font-size: 0.9rem;">Estimasi Biaya</span>
                    <span style="font-weight: 600; color: var(--danger);">- Rp {{ number_format($estimasiBiaya, 0, ',', '.') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="font-weight: 700;">Estimasi Laba</span>
                    <span style="font-weight: 700; color: {{ $estimasiLaba >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                        Rp {{ number_format($estimasiLaba, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: Keuntungan -->
    <div id="tab-keuntungan" class="tab-content">
        <div class="investment-card">
            <h3 class="section-title" style="margin-bottom: 15px; font-size: 1.05rem;">Alokasi Keuntungan Anda ({{ ucfirst($userRole) }})</h3>
            <div class="card-body" style="grid-template-columns: 1fr;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 10px; margin-bottom: 10px;">
                    <span style="color: var(--text-muted); font-size: 0.9rem;">Laba Bersih Proyek</span>
                    <span style="font-weight: 600;">Rp {{ number_format($labaBersih, 0, ',', '.') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 10px; margin-bottom: 10px;">
                    <span style="color: var(--text-muted); font-size: 0.9rem;">Zakat ({{ $pertanian->persentase_zakat ?? 5 }}%)</span>
                    <span style="font-weight: 600; color: var(--danger);">- Rp {{ number_format($zakat, 0, ',', '.') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 10px; margin-bottom: 10px;">
                    <span style="color: var(--text-muted); font-size: 0.9rem;">Laba Setelah Zakat</span>
                    <span style="font-weight: 600;">Rp {{ number_format($labaSetelahZakat, 0, ',', '.') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 10px; margin-bottom: 10px;">
                    <span style="color: var(--text-muted); font-size: 0.9rem;">Porsi Anda ({{ $persentase }}%)</span>
                    <span style="font-weight: 700; color: var(--success); font-size: 1.1rem;">Rp {{ number_format($alokasiUser, 0, ',', '.') }}</span>
                </div>

                <div style="background: rgba(16,185,129,0.05); border-radius: 10px; padding: 15px; margin-top: 5px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="color: var(--text-muted); font-size: 0.9rem;">Sudah Ditarik</span>
                        <span style="font-weight: 600; color: var(--danger);">- Rp {{ number_format($ditarikTotal, 0, ',', '.') }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="font-weight: 700;">Sisa Bisa Ditarik</span>
                        <span style="font-weight: 700; color: var(--success); font-size: 1.15rem;">Rp {{ number_format($sisaBisaDitarik, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="investment-card">
            <h3 class="section-title" style="margin-bottom: 15px; font-size: 1.05rem;">Estimasi Keuntungan Akhir</h3>
            <div class="card-body" style="grid-template-columns: 1fr;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted); font-size: 0.9rem;">Estimasi Porsi Anda ({{ $persentase }}%)</span>
                    <span style="font-weight: 700; color: var(--primary-dark);">Rp {{ number_format($estimasiAlokasiUser, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 3: Penarikan -->
    <div id="tab-penarikan" class="tab-content">
        <div class="investment-card">
            <h3 class="section-title" style="margin-bottom: 15px; font-size: 1.05rem;">Riwayat Penarikan Anda</h3>
            @forelse($withdrawals as $withdrawal)
            <div style="border: 1px solid rgba(0,0,0,0.05); border-radius: 12px; padding: 15px; margin-bottom: 10px; background: white;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 0.85rem; color: var(--text-muted);">
                        <i class="ki-duotone ki-calendar me-1"></i> {{ \Carbon\Carbon::parse($withdrawal->date)->format('d M Y') }}
                    </span>
                    <span style="font-weight: 700; color: var(--danger); font-size: 1rem;">
                        Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}
                    </span>
                </div>
                @if($withdrawal->notes)
                <div style="font-size: 0.85rem; color: var(--text-muted);">
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
            <div style="text-align: center; padding: 20px; color: var(--text-muted); font-style: italic; font-size: 0.9rem;">
                Belum ada riwayat penarikan dana.
            </div>
            @endforelse
        </div>
    </div>

    <!-- TAB UPDATE: Info / Timeline -->
    <div id="tab-updates" class="tab-content">
        @if($pertanian->updates->isEmpty())
            <div style="text-align: center; padding: 40px 20px; background: rgba(255,255,255,0.4); border-radius: 16px; margin-top: 10px; backdrop-filter: blur(10px);">
                <i class="ki-duotone ki-information-5 fs-3x text-muted mb-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                <div style="font-weight: 600; color: var(--text);">Belum ada update</div>
                <div style="font-size: 0.85rem; color: var(--text-muted);">Informasi terbaru tentang proyek ini akan muncul di sini.</div>
            </div>
        @else
            <div class="timeline">
                @foreach($pertanian->updates->sortByDesc('date') as $update)
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-date">{{ \Carbon\Carbon::parse($update->date)->format('d M Y, H:i') }} WIB</div>
                    <div class="timeline-card">
                        <h4 style="margin: 0 0 5px 0; font-size: 1.05rem;">{{ $update->title }}</h4>
                        <div style="font-size: 0.75rem; color: var(--primary); margin-bottom: 10px; font-weight: 600;">
                            Oleh: {{ $update->user->name ?? 'Pengelola' }}
                        </div>
                        @if(is_array($update->photo) && count($update->photo) > 0)
                            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 10px;">
                            @foreach($update->photo as $img)
                                <div style="flex: 1 1 calc(50% - 4px); min-width: 120px;">
                                    <img src="{{ Storage::disk('r2')->url($img) }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); aspect-ratio: 4/3;">
                                </div>
                            @endforeach
                            </div>
                        @elseif(is_string($update->photo) && !empty($update->photo))
                            <img src="{{ Storage::disk('r2')->url($update->photo) }}" style="width: 100%; border-radius: 8px; margin-bottom: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                        @endif
                        <p style="font-size: 0.9rem; margin: 0; opacity: 0.9; line-height: 1.5; white-space: pre-wrap;">{{ $update->description }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

<script>
    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-' + tabName).classList.add('active');
        event.target.classList.add('active');

        // Fix Leaflet map sizing issue when tab becomes visible
        if(tabName === 'info' && typeof detailMap !== 'undefined') {
            setTimeout(function() {
                detailMap.invalidateSize();
                if (typeof kebunPolygonData !== 'undefined' && kebunPolygonData) {
                    const bounds = L.geoJSON(kebunPolygonData).getBounds();
                    detailMap.fitBounds(bounds);
                }
            }, 150);
        }
    }
</script>
@endsection

@push('styles')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .leaflet-container {
            font-family: inherit;
        }
    </style>
@endpush

@push('scripts')
    <!-- JQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let detailMap;
        let kebunPolygonData = null;

        $(document).ready(function () {
            const mapContainer = document.getElementById("detail_map");
            if (!mapContainer) return;

            const polygonStr = document.getElementById("kebun_polygon").value;
            if (polygonStr) {
                try {
                    kebunPolygonData = JSON.parse(polygonStr);
                } catch(e) {
                    console.error("Invalid GeoJSON for kebun polygon");
                }
            }

            let initialCenter = [-0.7893, 113.9213];
            let initialZoom = 5;

            const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            });

            const googleStreets = L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',{
                maxZoom: 20,
                subdomains:['mt0','mt1','mt2','mt3']
            });

            const googleHybrid = L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}',{
                maxZoom: 20,
                subdomains:['mt0','mt1','mt2','mt3']
            });

            const googleSatellite = L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}',{
                maxZoom: 20,
                subdomains:['mt0','mt1','mt2','mt3']
            });

            // Initialize Map with Hybrid as default
            detailMap = L.map('detail_map', {
                zoomControl: true,
                layers: [googleHybrid] 
            }).setView(initialCenter, initialZoom);

            const baseMaps = {
                "streetmap": osmLayer,
                "google_map": googleStreets,
                "hybrid": googleHybrid,
                "satelit": googleSatellite
            };

            $('.map-layer-select').on('change', function () {
                const layerKey = $(this).val();

                for (let key in baseMaps) {
                    if (detailMap.hasLayer(baseMaps[key])) {
                        detailMap.removeLayer(baseMaps[key]);
                    }
                }

                if (baseMaps[layerKey]) {
                    detailMap.addLayer(baseMaps[layerKey]);
                }
            });

            if (kebunPolygonData) {
                const geoJsonLayer = L.geoJSON(kebunPolygonData, {
                    style: {
                        color: "#0052e2",
                        fillColor: "#0072ff",
                        fillOpacity: 0.3,
                        weight: 3
                    }
                }).addTo(detailMap);

                const area = "{{ number_format($pertanian->kebun->area ?? 0, 2, ',', '.') }}";
                geoJsonLayer.bindPopup(`<strong>Kebun:</strong> {{ $pertanian->kebun->name ?? '-' }}<br><strong>Estimasi Luas:</strong> ${area} m²`);

                detailMap.fitBounds(geoJsonLayer.getBounds());
            }

            // Fix sizing issue initially
            setTimeout(function(){ detailMap.invalidateSize(); }, 500);
        });
    </script>
@endpush
