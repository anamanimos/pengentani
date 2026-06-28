@extends('layouts.metronic')

@section('title', 'Detail Rencana Pertanian - ' . $pertanian->name)
@section('page_title', 'Detail Rencana Pertanian')

@section('page_actions')
    <a href="{{ route('pertanians.index') }}" class="btn btn-sm btn-light">
        <i class="ki-duotone ki-arrow-left fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Kembali
    </a>
    <a href="{{ route('pertanians.edit', $pertanian) }}" class="btn btn-sm btn-light-warning">
        <i class="ki-duotone ki-pencil fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Edit Rencana
    </a>
    <a href="{{ route('pertanians.investors.index', $pertanian) }}" class="btn btn-sm btn-primary">
        <i class="ki-duotone ki-profile-user fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i> Kelola Investor
    </a>
@endsection

@section('content')
{{-- Header Rencana Pertanian --}}
<div class="row g-5 g-xl-8 mb-5">
    <div class="col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header border-0 pt-6">
                <div class="card-title d-flex flex-column">
                    <div class="d-flex align-items-center mb-1">
                        <h2 class="fw-bold fs-2 text-gray-900 mb-0 me-3">{{ $pertanian->name }}</h2>
                        <span class="badge badge-light-{{ $pertanian->status == 'Selesai' ? 'success' : ($pertanian->status == 'Sedang Berjalan' ? 'primary' : ($pertanian->status == 'Pencarian Investor' ? 'warning' : 'secondary')) }} fs-7">
                            {{ $pertanian->status }}
                        </span>
                    </div>
                    <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">
                        <div class="d-flex align-items-center text-gray-600 me-5 mb-2">
                            <i class="ki-duotone ki-geolocation text-danger fs-5 me-2"><span class="path1"></span><span class="path2"></span></i>
                            Kebun: <strong class="ms-1">{{ $pertanian->kebun->name ?? '-' }}</strong>
                        </div>
                        <div class="d-flex align-items-center text-gray-600 me-5 mb-2">
                            <i class="ki-duotone ki-user text-success fs-5 me-2"><span class="path1"></span><span class="path2"></span></i>
                            Admin: <strong class="ms-1">{{ $pertanian->admin->name ?? '-' }}</strong>
                        </div>
                        <div class="d-flex align-items-center text-gray-600 me-5 mb-2">
                            <i class="ki-duotone ki-user-edit text-info fs-5 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            Pengelola: <strong class="ms-1">{{ $pertanian->pengelola->name ?? '-' }}</strong>
                        </div>
                        <div class="d-flex align-items-center text-gray-600 me-5 mb-2">
                            <i class="ki-duotone ki-calendar-8 text-primary fs-5 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i>
                            Periode: 
                            <strong class="ms-1">
                                @if($pertanian->start_date && $pertanian->end_date)
                                    {{ \Carbon\Carbon::parse($pertanian->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($pertanian->end_date)->format('d M Y') }}
                                @else
                                    -
                                @endif
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Tabs Navigation --}}
            <div class="card-footer py-0 border-0">
                <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary py-5 active" data-bs-toggle="tab" href="#kt_tab_pane_financial" role="tab">
                            <i class="ki-duotone ki-graph-up fs-4 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i>Ringkasan & Bagi Hasil
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary py-5" data-bs-toggle="tab" href="#kt_tab_pane_kebun" role="tab">
                            <i class="ki-duotone ki-map fs-4 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>Kebun & Peta Lokasi
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary py-5" data-bs-toggle="tab" href="#kt_tab_pane_tanaman" role="tab">
                            <i class="ki-duotone ki-design-2 fs-4 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i>Rencana Tanaman
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary py-5" data-bs-toggle="tab" href="#kt_tab_pane_biaya" role="tab">
                            <i class="ki-duotone ki-coin fs-4 me-2"><span class="path1"></span><span class="path2"></span></i>Estimasi Biaya
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary py-5" data-bs-toggle="tab" href="#kt_tab_pane_realisasi" role="tab">
                            <i class="ki-duotone ki-bill fs-4 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i>Realisasi
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary py-5" data-bs-toggle="tab" href="#kt_tab_pane_investors" role="tab">
                            <i class="ki-duotone ki-user-edit fs-4 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>Daftar Investor
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary py-5" data-bs-toggle="tab" href="#kt_tab_pane_withdrawals" role="tab">
                            <i class="ki-duotone ki-wallet fs-4 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>Penarikan Dana
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- Tabs Content --}}
<div class="tab-content" id="myTabContent">
    {{-- Tab 1: Ringkasan & Bagi Hasil --}}
    <div class="tab-pane fade show active" id="kt_tab_pane_financial" role="tabpanel">
        {{-- Kartu Ringkasan Finansial --}}
        {{-- Kartu Finansial (Estimasi vs Realisasi) --}}
        <div class="row g-5 g-xl-8 mb-5">
            {{-- Biaya Operasional --}}
            <div class="col-xl-3 col-md-6">
                <div class="card bg-light-danger mb-5 mb-xl-10 h-100">
                    <div class="card-body py-5">
                        <div class="d-flex align-items-center mb-4">
                            <i class="ki-duotone ki-finance-calculator fs-1 text-danger me-3">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span><span class="path7"></span>
                            </i>
                            <h4 class="text-danger fw-bold m-0">Biaya Operasional</h4>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-gray-600 fw-semibold">Estimasi:</span>
                            <span class="text-gray-900 fw-bold">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-gray-600 fw-semibold">Realisasi:</span>
                            <span class="text-danger fw-bold">Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pendapatan --}}
            <div class="col-xl-3 col-md-6">
                <div class="card bg-light-info mb-5 mb-xl-10 h-100">
                    <div class="card-body py-5">
                        <div class="d-flex align-items-center mb-4">
                            <i class="ki-duotone ki-graph-up fs-1 text-info me-3">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span>
                            </i>
                            <h4 class="text-info fw-bold m-0">Pendapatan</h4>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-gray-600 fw-semibold">Estimasi:</span>
                            <span class="text-gray-900 fw-bold">Rp {{ number_format($estimasiPendapatan, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-gray-600 fw-semibold">Realisasi:</span>
                            <span class="text-info fw-bold">Rp {{ number_format($totalRealisasiPendapatan, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Laba Bersih --}}
            <div class="col-xl-3 col-md-6">
                <div class="card bg-light-{{ $estimasiLaba >= 0 ? 'success' : 'danger' }} mb-5 mb-xl-10 h-100">
                    <div class="card-body py-5">
                        <div class="d-flex align-items-center mb-4">
                            <i class="ki-duotone ki-badge fs-1 text-{{ $estimasiLaba >= 0 ? 'success' : 'danger' }} me-3">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span>
                            </i>
                            <h4 class="text-{{ $estimasiLaba >= 0 ? 'success' : 'danger' }} fw-bold m-0">Laba Bersih</h4>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-gray-600 fw-semibold">Estimasi:</span>
                            <span class="text-gray-900 fw-bold">Rp {{ number_format($estimasiLaba, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-gray-600 fw-semibold">Realisasi:</span>
                            <span class="text-{{ $realisasiLabaBersih >= 0 ? 'success' : 'danger' }} fw-bold">Rp {{ number_format($realisasiLabaBersih, 0, ',', '.') }}</span>
                        </div>
                        @if($estimasiLaba > 0)
                            <div class="separator separator-dashed my-2"></div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-gray-600 fs-8">Est. Sisa Laba (Stlh Zkt):</span>
                                <span class="text-success fw-bold fs-8">Rp {{ number_format($labaSetelahZakat, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Investasi Terkumpul --}}
            <div class="col-xl-3 col-md-6">
                <div class="card bg-light-primary mb-5 mb-xl-10 h-100 position-relative">
                    <div class="card-body py-5">
                        <div class="d-flex align-items-center mb-4">
                            <i class="ki-duotone ki-wallet fs-1 text-primary me-3">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span>
                            </i>
                            <h4 class="text-primary fw-bold m-0">Investasi Terkumpul</h4>
                        </div>
                        
                        <div class="position-absolute" style="top: 15px; right: 15px;">
                            <div class="form-check form-switch form-check-custom form-check-solid form-check-sm">
                                <input class="form-check-input" type="checkbox" id="investor-deal-toggle" />
                                <label class="form-check-label fs-8 text-muted" for="investor-deal-toggle" title="Hanya hitung investor dengan status Deal"></label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mb-2 mt-4">
                            <span class="text-gray-600 fw-semibold">Total:</span>
                            <span class="text-gray-900 fw-bold fs-5" id="inv-total-text">Rp {{ number_format($totalInvestasiAll, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-gray-600 fw-semibold">Sisa Cash:</span>
                            <span class="text-primary fw-bold fs-5" id="inv-cash-text">Rp {{ number_format($sisaCashAll, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Skema Bagi Hasil --}}
        <div class="card">
            <div class="card-body">
                <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed mb-9 p-6">
                    <i class="ki-duotone ki-information-5 fs-1 text-warning me-4">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                    </i>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">Skema Zakat & Bagi Hasil</h4>
                            <div class="fs-6 text-gray-700">
                                @if($estimasiLaba > 0)
                                    Persentase Zakat (<strong>{{ number_format($zakatPersen, 2, ',', '.') }}%</strong>) dipotong terlebih dahulu dari Estimasi Laba Bersih (<strong>Rp {{ number_format($estimasiLaba, 0, ',', '.') }}</strong>).
                                    Sisa laba setelah zakat sebesar <strong>Rp {{ number_format($labaSetelahZakat, 0, ',', '.') }}</strong> kemudian dibagi sesuai dengan porsi masing-masing pihak (Investor, Pengelola Lahan, dan Admin).
                                @else
                                    Persentase Zakat (<strong>{{ number_format($zakatPersen, 2, ',', '.') }}%</strong>) akan dipotong terlebih dahulu dari Estimasi Laba Bersih sebelum dibagikan ke masing-masing pihak. Saat ini, estimasi laba bersih belum menghasilkan nilai positif.
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row text-center">
                    <div class="col-md-3 col-6">
                        <div class="border border-dashed border-gray-300 rounded min-w-125px py-3 px-4 mb-3 bg-light-warning">
                            <div class="fs-3 fw-bold text-warning">{{ number_format($zakatPersen, 2, ',', '.') }}%</div>
                            <div class="fw-semibold text-muted fs-7">Zakat</div>
                            <div class="fs-6 fw-bold text-gray-800 mt-2">Rp {{ number_format($zakat, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="border border-dashed border-gray-300 rounded min-w-125px py-3 px-4 mb-3">
                            <div class="fs-3 fw-bold text-primary">{{ $pertanian->persentase_investor }}%</div>
                            <div class="fw-semibold text-muted fs-7">Investor</div>
                            <div class="fs-6 fw-bold text-gray-800 mt-2">Rp {{ number_format($labaInvestor, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="border border-dashed border-gray-300 rounded min-w-125px py-3 px-4 mb-3">
                            <div class="fs-3 fw-bold text-success">{{ $pertanian->persentase_pengelola }}%</div>
                            <div class="fw-semibold text-muted fs-7">Pengelola Lahan</div>
                            <div class="fs-6 fw-bold text-gray-800 mt-2">Rp {{ number_format($labaPengelola, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="border border-dashed border-gray-300 rounded min-w-125px py-3 px-4 mb-3">
                            <div class="fs-3 fw-bold text-info">{{ $pertanian->persentase_admin }}%</div>
                            <div class="fw-semibold text-muted fs-7">Admin</div>
                            <div class="fs-6 fw-bold text-gray-800 mt-2">Rp {{ number_format($labaAdmin, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab 2: Kebun & Peta Lokasi --}}
    <div class="tab-pane fade" id="kt_tab_pane_kebun" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900 fs-3">Peta Wilayah Kebun</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Visualisasi koordinat batas wilayah area kebun</span>
                </h3>
                <div class="card-toolbar gap-2">
                    <!-- Luas Badge -->
                    <span class="badge badge-light-success fs-6 fw-bold py-3 px-4 me-2">
                        <i class="ki-duotone ki-design-2 text-success fs-4 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i>
                        Luas Kebun: <strong>{{ number_format($pertanian->kebun->area ?? 0, 2, ',', '.') }} m²</strong>
                    </span>
                    
                    <!-- Map Layers Switcher -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light-primary btn-icon shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Pilih Layer Peta">
                            <i class="ki-duotone ki-map fs-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item map-layer-btn active" href="#" data-layer="streetmap">StreetMap (OpenStreetMap)</a></li>
                            <li><a class="dropdown-item map-layer-btn" href="#" data-layer="google_map">Google Map</a></li>
                            <li><a class="dropdown-item map-layer-btn" href="#" data-layer="hybrid">Hybrid</a></li>
                            <li><a class="dropdown-item map-layer-btn" href="#" data-layer="satelit">Satelit</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <input type="hidden" id="kebun_polygon" value="{{ $pertanian->kebun->polygon ?? '' }}" />
                <div id="detail_map" style="height: 480px; z-index: 1;" class="rounded border shadow-xs"></div>
            </div>
        </div>
    </div>

    {{-- Tab 3: Rencana Tanaman --}}
    <div class="tab-pane fade" id="kt_tab_pane_tanaman" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900 fs-3">Rencana Tanaman</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Tanaman yang dibudidayakan dalam rencana ini</span>
                </h3>
            </div>
            <div class="card-body py-3">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                            <tr class="fw-bold text-muted bg-light text-start">
                                <th class="ps-4 min-w-150px rounded-start">Jenis Tanaman</th>
                                <th class="min-w-100px text-start">Jumlah Pohon</th>
                                <th class="min-w-120px text-start">Est. Panen/Pohon</th>
                                <th class="min-w-120px text-start">Est. Harga/Kg</th>
                                <th class="min-w-150px text-start rounded-end pe-4">Est. Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pertanian->tanamans as $pt)
                            @php
                                $totalTanaman = $pt->qty_pohon * $pt->estimasi_berat_per_pohon * $pt->estimasi_harga_per_kg;
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center ps-2">
                                        <div class="symbol symbol-35px me-3">
                                            <span class="symbol-label bg-light-success text-success fw-bold">
                                                <i class="ki-duotone ki-design-2 text-success fs-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i>
                                            </span>
                                        </div>
                                        <span class="text-gray-900 fw-bold fs-6">{{ $pt->tanaman->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="text-start text-gray-800 fw-semibold">{{ number_format($pt->qty_pohon, 0, ',', '.') }}</td>
                                <td class="text-start text-gray-800 fw-semibold">{{ number_format($pt->estimasi_berat_per_pohon, 2, ',', '.') }} Kg</td>
                                <td class="text-start text-gray-800 fw-semibold">Rp {{ number_format($pt->estimasi_harga_per_kg, 0, ',', '.') }}</td>
                                <td class="text-start text-gray-900 fw-bold pe-4">Rp {{ number_format($totalTanaman, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-start text-muted py-5">Belum ada rencana tanaman.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab 4: Estimasi Biaya --}}
    <div class="tab-pane fade" id="kt_tab_pane_biaya" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900 fs-3">Rincian Estimasi Biaya</span>
                    <span class="text-muted mt-1 fw-semibold fs-7">Rincian kebutuhan modal kerja proyek pertanian</span>
                </h3>
            </div>
            <div class="card-body py-3">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                            <tr class="fw-bold text-muted bg-light text-start">
                                <th class="ps-4 min-w-150px rounded-start">Nama Biaya</th>
                                <th class="min-w-80px text-start">Quantity</th>
                                <th class="min-w-120px text-start">Harga Satuan</th>
                                <th class="min-w-150px text-start rounded-end pe-4">Total Biaya</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pertanian->biayas as $biaya)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center ps-2">
                                        <div class="symbol symbol-35px me-3">
                                            <span class="symbol-label bg-light-danger text-danger fw-bold">
                                                <i class="ki-duotone ki-coin text-danger fs-4"><span class="path1"></span><span class="path2"></span></i>
                                            </span>
                                        </div>
                                        <span class="text-gray-900 fw-bold fs-6">{{ $biaya->name }}</span>
                                    </div>
                                </td>
                                <td class="text-start text-gray-800 fw-semibold">{{ number_format($biaya->qty, 0, ',', '.') }}</td>
                                <td class="text-start text-gray-800 fw-semibold">Rp {{ number_format($biaya->harga_satuan, 0, ',', '.') }}</td>
                                <td class="text-start text-danger fw-bold pe-4">Rp {{ number_format($biaya->total, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-start text-muted py-5">Belum ada rincian biaya.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold bg-light">
                                <td colspan="3" class="text-start pe-4">Total Estimasi Biaya</td>
                                <td class="text-start text-danger pe-4">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab 5: Realisasi --}}
    <div class="tab-pane fade" id="kt_tab_pane_realisasi" role="tabpanel">
        <div class="row g-5 g-xl-8">
            <div class="col-xl-12">
                <div class="card shadow-sm h-100">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-900 fs-3">Riwayat Realisasi</span>
                            <span class="text-muted mt-1 fw-semibold fs-7">Gabungan pengeluaran pembelian material dan upah pekerja</span>
                        </h3>
                    </div>
                    <div class="card-body py-3">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5">
                                <thead>
                                    <tr class="fw-bold text-muted bg-light text-start">
                                        <th class="ps-4 min-w-100px rounded-start">Tanggal</th>
                                        <th class="min-w-150px">Kategori</th>
                                        <th class="min-w-200px">Deskripsi/Keterangan</th>
                                        <th class="min-w-150px text-start rounded-end pe-4">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($realisasiList as $realisasi)
                                    <tr>
                                        <td class="ps-4">{{ \Carbon\Carbon::parse($realisasi->date)->format('d M Y') }}</td>
                                        <td>
                                            <span class="badge badge-light-{{ $realisasi->color }} fs-7">
                                                <i class="ki-duotone {{ $realisasi->icon }} text-{{ $realisasi->color }} fs-5 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                {{ $realisasi->kategori }}
                                            </span>
                                        </td>
                                        <td>{{ $realisasi->deskripsi }}</td>
                                        <td class="text-start fw-bold text-gray-800 pe-4">Rp {{ number_format($realisasi->nominal, 0, ',', '.') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-start text-muted py-5">Belum ada data realisasi.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5 mb-5">
            <div class="col-12">
                <div class="card bg-light-danger shadow-sm border border-danger border-dashed">
                    <div class="card-body d-flex justify-content-between align-items-center py-5">
                        <div class="d-flex align-items-center">
                            <i class="ki-duotone ki-finance-calculator fs-3x text-danger me-5">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span><span class="path7"></span>
                            </i>
                            <div class="d-flex flex-column">
                                <span class="text-danger fw-bold fs-5">Total Seluruh Realisasi Biaya</span>
                                <span class="text-muted fw-semibold fs-7">Gabungan dari pembelian material dan upah pekerja</span>
                            </div>
                        </div>
                        <span class="text-gray-900 fw-bolder fs-1 text-nowrap">Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab 6: Daftar Investor --}}
    <div class="tab-pane fade" id="kt_tab_pane_investors" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-header border-0 pt-5">
                <h3 class="card-title">
                    <span class="card-label fw-bold text-gray-900 fs-3">Daftar Investor Terdaftar</span>
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-50px">No</th>
                                <th class="min-w-200px">Nama Investor</th>
                                <th class="min-w-150px">Besaran Investasi</th>
                                <th class="min-w-100px">Porsi (%)</th>
                                <th class="min-w-150px">Estimasi Bagi Hasil</th>
                                <th class="min-w-200px">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            @forelse($pertanian->investors as $index => $investor)
                            @php
                                $porsi = $totalInvestasi > 0 ? ($investor->besaran_investasi / $totalInvestasi) * 100 : 0;
                                $bagiHasilIndividu = $totalInvestasi > 0 ? ($investor->besaran_investasi / $totalInvestasi) * $labaInvestor : 0;
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-circle symbol-35px me-3">
                                            <span class="symbol-label bg-light-primary text-primary fw-bold">{{ strtoupper(substr($investor->user->name ?? 'U', 0, 1)) }}</span>
                                        </div>
                                        <div>
                                            <span class="fw-bold">{{ $investor->user->name ?? '-' }}</span>
                                            <span class="text-muted d-block fs-7">{{ $investor->user->email ?? '-' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="fw-bold">Rp {{ number_format($investor->besaran_investasi, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge badge-light-primary fs-7">{{ number_format($porsi, 1) }}%</span>
                                </td>
                                <td class="fw-bold text-success">Rp {{ number_format($bagiHasilIndividu, 0, ',', '.') }}</td>
                                <td>
                                    <span class="text-muted fs-7">{{ $investor->keterangan ?? '-' }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">Belum ada investor terdaftar.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Penarikan Dana Tab --}}
    <div class="tab-pane fade" id="kt_tab_pane_withdrawals" role="tabpanel">
        {{-- Info Saldo --}}
        <div class="row g-5 mb-8">
            <div class="col-md-4">
                <div class="bg-light-primary rounded border-primary border border-dashed p-6">
                    <div class="d-flex align-items-center mb-3">
                        <i class="ki-duotone ki-user text-primary fs-1 me-2"><span class="path1"></span><span class="path2"></span></i>
                        <span class="fs-5 fw-bold text-gray-800">Saldo Admin</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-gray-600">Alokasi</span>
                        <span class="fw-bold">Rp {{ number_format($alokasiAdmin, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-gray-600">Ditarik</span>
                        <span class="text-danger fw-bold">- Rp {{ number_format($ditarikAdmin, 0, ',', '.') }}</span>
                    </div>
                    <div class="separator separator-dashed my-2"></div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-gray-800 fw-bold">Sisa</span>
                        <span class="text-success fs-3 fw-bold">Rp {{ number_format($sisaAdmin, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="bg-light-info rounded border-info border border-dashed p-6">
                    <div class="d-flex align-items-center mb-3">
                        <i class="ki-duotone ki-user-edit text-info fs-1 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        <span class="fs-5 fw-bold text-gray-800">Saldo Pengelola</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-gray-600">Alokasi</span>
                        <span class="fw-bold">Rp {{ number_format($alokasiPengelola, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-gray-600">Ditarik</span>
                        <span class="text-danger fw-bold">- Rp {{ number_format($ditarikPengelola, 0, ',', '.') }}</span>
                    </div>
                    <div class="separator separator-dashed my-2"></div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-gray-800 fw-bold">Sisa</span>
                        <span class="text-success fs-3 fw-bold">Rp {{ number_format($sisaPengelola, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="bg-light-success rounded border-success border border-dashed p-6">
                    <div class="d-flex align-items-center mb-3">
                        <i class="ki-duotone ki-profile-user text-success fs-1 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                        <span class="fs-5 fw-bold text-gray-800">Saldo Total Investor</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-gray-600">Alokasi Total</span>
                        <span class="fw-bold">Rp {{ number_format($alokasiInvestorTotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-gray-600">Ditarik Total</span>
                        <span class="text-danger fw-bold">- Rp {{ number_format($ditarikInvestorTotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="separator separator-dashed my-2"></div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-gray-800 fw-bold">Sisa Total</span>
                        <span class="text-success fs-3 fw-bold">Rp {{ number_format($sisaInvestorTotal, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-5 mb-xl-10">
            <div class="card-header border-0 pt-6">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1">Riwayat Penarikan Dana</span>
                    <span class="text-muted fw-semibold fs-7">Seluruh penarikan bagi hasil oleh Admin, Pengelola, maupun Investor</span>
                </h3>
                <div class="card-toolbar">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_withdrawal">
                        <i class="ki-duotone ki-plus fs-2"></i>Catat Penarikan
                    </button>
                </div>
            </div>
            <div class="card-body py-3">


                {{-- Filter & Pencarian (Notion-style) --}}
                <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                    <div class="position-relative my-1">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4 top-50 translate-middle-y"><span class="path1"></span><span class="path2"></span></i>
                        <input type="text" id="withdrawal_search" class="form-control form-control-solid form-control-sm w-200px ps-12" placeholder="Cari penarikan..." />
                    </div>
                    
                    {{-- Filter Dropdown --}}
                    <div class="my-1">
                        <button type="button" class="btn btn-sm btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-start">
                            <i class="ki-duotone ki-filter fs-2"><span class="path1"></span><span class="path2"></span></i> Filter
                        </button>
                        <div class="menu menu-sub menu-sub-dropdown w-250px w-md-300px" data-kt-menu="true" id="kt_menu_filter_withdrawal">
                            <div class="px-7 py-5">
                                <div class="fs-5 text-dark fw-bold">Filter Data</div>
                            </div>
                            <div class="separator border-gray-200"></div>
                            <div class="px-7 py-5">
                                <div class="mb-5">
                                    <label class="form-label fw-semibold">Tanggal:</label>
                                    <input type="text" id="withdrawal_daterange" class="form-control form-control-solid form-control-sm" placeholder="Pilih rentang" />
                                </div>
                                <div class="mb-5">
                                    <label class="form-label fw-semibold">Peran:</label>
                                    <select id="withdrawal_role_filter" class="form-select form-select-solid form-select-sm" data-control="select2" data-hide-search="true" data-placeholder="Semua Peran" multiple="multiple">
                                        <option value="admin">Admin</option>
                                        <option value="pengelola">Pengelola</option>
                                        <option value="investor">Investor</option>
                                    </select>
                                </div>
                                <div class="mb-5">
                                    <label class="form-label fw-semibold">Penerima:</label>
                                    <select id="withdrawal_user_filter" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="Semua Penerima" multiple="multiple">
                                        @foreach($withdrawals->unique('user_id') as $wd)
                                            @if($wd->user)
                                                <option value="{{ $wd->user->name }}">{{ $wd->user->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-sm btn-light btn-active-light-primary me-2" data-kt-menu-dismiss="true" id="withdrawal_filter_reset">Reset</button>
                                    <button type="button" class="btn btn-sm btn-primary" data-kt-menu-dismiss="true">Terapkan</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Active Filters Chips Container --}}
                <div id="withdrawal_active_filters" class="d-flex flex-wrap gap-2 mb-4 empty-hidden"></div>

                {{-- Tabel Penarikan --}}
                <div class="table-responsive">
                    <table id="kt_table_withdrawals" class="table table-sm align-middle table-row-dashed fs-7 gy-2">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="text-start">Tanggal</th>
                                <th class="text-start">Tanggal Input</th>
                                <th class="text-start">Penerima</th>
                                <th class="text-start">Peran</th>
                                <th class="text-start">Nominal</th>
                                <th class="text-start">Bukti</th>
                                <th class="text-start">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            @forelse($withdrawals as $withdrawal)
                            <tr data-date="{{ $withdrawal->date }}">
                                <td class="text-start" data-order="{{ $withdrawal->date }}">{{ \Carbon\Carbon::parse($withdrawal->date)->format('d M Y') }}</td>
                                <td class="text-start" data-order="{{ $withdrawal->created_at }}">{{ $withdrawal->created_at->format('d M Y H:i') }}</td>
                                <td class="text-start">{{ $withdrawal->user->name ?? '-' }}</td>
                                <td class="text-start">
                                    <span class="badge badge-light-{{ $withdrawal->role == 'admin' ? 'primary' : ($withdrawal->role == 'pengelola' ? 'info' : 'success') }} fs-7 text-capitalize">
                                        {{ $withdrawal->role }}
                                    </span>
                                </td>
                                <td class="text-start fw-bold text-danger">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</td>
                                <td class="text-start">
                                    @if($withdrawal->proof_image)
                                        <a href="{{ Storage::url($withdrawal->proof_image) }}" target="_blank" class="btn btn-sm btn-icon btn-light-primary"><i class="ki-duotone ki-picture fs-2"><span class="path1"></span><span class="path2"></span></i></a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-start">
                                    <form action="{{ route('withdrawals.destroy', $withdrawal) }}" method="POST" class="d-inline form-delete-withdrawal">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-light-danger btn-sm"><i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-start text-muted py-5">Belum ada data penarikan dana.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Catat Penarikan --}}
<div class="modal fade" tabindex="-1" id="kt_modal_add_withdrawal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('withdrawals.store', $pertanian) }}" method="POST" enctype="multipart/form-data" id="form_add_withdrawal">
                @csrf
                <div class="modal-header">
                    <h3 class="modal-title">Catat Penarikan Dana</h3>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="mb-5">
                        <label class="required form-label">Peran (Kapasitas)</label>
                        <select class="form-select form-select-solid" name="role" required>
                            <option value="">Pilih Peran...</option>
                            <option value="admin">Admin</option>
                            <option value="pengelola">Pengelola</option>
                            <option value="investor">Investor</option>
                        </select>
                    </div>
                    <div class="mb-5">
                        <label class="required form-label">Pilih User Penerima</label>
                        <select class="form-select form-select-solid" name="user_id" data-control="select2" data-dropdown-parent="#kt_modal_add_withdrawal" required>
                            <option value="">Pilih...</option>
                            @if($pertanian->admin)
                                <option value="{{ $pertanian->admin->id }}">[Admin] {{ $pertanian->admin->name }}</option>
                            @endif
                            @if($pertanian->pengelola)
                                <option value="{{ $pertanian->pengelola->id }}">[Pengelola] {{ $pertanian->pengelola->name }}</option>
                            @endif
                            @foreach($pertanian->investors as $inv)
                                <option value="{{ $inv->user->id }}">[Investor] {{ $inv->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-5">
                        <label class="required form-label">Nominal Ditarik (Rp)</label>
                        <input type="number" class="form-control form-control-solid" name="amount" required min="1" />
                    </div>
                    <div class="mb-5">
                        <label class="required form-label">Tanggal Penarikan</label>
                        <input type="text" class="form-control form-control-solid" id="withdrawal_date" name="date" required value="{{ date('Y-m-d') }}" />
                    </div>
                    <div class="mb-5">
                        <label class="form-label">Bukti Transfer (Opsional)</label>
                        <input type="file" class="form-control form-control-solid" name="proof_image" accept="image/*" />
                    </div>
                    <div class="mb-5">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control form-control-solid" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .leaflet-container {
            font-family: inherit;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        $(document).ready(function () {
            // Check if map container exists
            const mapContainer = document.getElementById("detail_map");
            if (!mapContainer) return;

            // Load Polygon GeoJSON
            const polygonStr = document.getElementById("kebun_polygon").value;
            let kebunPolygon = null;
            if (polygonStr) {
                try {
                    kebunPolygon = JSON.parse(polygonStr);
                } catch(e) {
                    console.error("Invalid GeoJSON for kebun polygon");
                }
            }

            // Default center if no polygon
            let initialCenter = [-0.7893, 113.9213];
            let initialZoom = 5;

            // Base Layers
            const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            });

            const googleStreets = L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',{
                maxZoom: 20,
                subdomains:['mt0','mt1','mt2','mt3'],
                attribution: '&copy; Google Maps'
            });

            const googleHybrid = L.tileLayer('http://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}',{
                maxZoom: 20,
                subdomains:['mt0','mt1','mt2','mt3'],
                attribution: '&copy; Google Maps'
            });

            const googleSatellite = L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}',{
                maxZoom: 20,
                subdomains:['mt0','mt1','mt2','mt3'],
                attribution: '&copy; Google Maps'
            });

            // Initialize Map
            const detailMap = L.map('detail_map', {
                zoomControl: true,
                layers: [osmLayer] // Default layer
            }).setView(initialCenter, initialZoom);

            // Base maps collection
            const baseMaps = {
                "streetmap": osmLayer,
                "google_map": googleStreets,
                "hybrid": googleHybrid,
                "satelit": googleSatellite
            };

            // Custom switcher action
            $('.map-layer-btn').on('click', function (e) {
                e.preventDefault();
                $('.map-layer-btn').removeClass('active');
                $(this).addClass('active');

                const layerKey = $(this).data('layer');

                // Remove current layers
                for (let key in baseMaps) {
                    if (detailMap.hasLayer(baseMaps[key])) {
                        detailMap.removeLayer(baseMaps[key]);
                    }
                }

                // Add chosen layer
                if (baseMaps[layerKey]) {
                    detailMap.addLayer(baseMaps[layerKey]);
                }
            });

            // Add Polygon and adjust view
            if (kebunPolygon) {
                const geoJsonLayer = L.geoJSON(kebunPolygon, {
                    style: {
                        color: "#0052e2",
                        fillColor: "#0072ff",
                        fillOpacity: 0.3,
                        weight: 3
                    }
                }).addTo(detailMap);

                // Add popup showing name and area
                const area = "{{ number_format($pertanian->kebun->area ?? 0, 2, ',', '.') }}";
                geoJsonLayer.bindPopup(`<strong>Kebun:</strong> {{ $pertanian->kebun->name ?? '-' }}<br><strong>Estimasi Luas:</strong> ${area} m²`);

                // Fit bounds
                detailMap.fitBounds(geoJsonLayer.getBounds());
            }

            // Invalidate Leaflet size when Tab is shown
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                if ($(e.target).attr('href') === '#kt_tab_pane_kebun') {
                    setTimeout(function() {
                        detailMap.invalidateSize();
                        if (kebunPolygon) {
                            // Re-fit bounds just in case
                            const bounds = L.geoJSON(kebunPolygon).getBounds();
                            detailMap.fitBounds(bounds);
                        }
                    }, 150);
                }
            });
            // Investor Toggle Logic
            const toggle = document.getElementById('investor-deal-toggle');
            if (toggle) {
                const totalText = document.getElementById('inv-total-text');
                const cashText = document.getElementById('inv-cash-text');
                
                const dataAll = {
                    total: 'Rp {{ number_format($totalInvestasiAll, 0, ",", ".") }}',
                    cash: 'Rp {{ number_format($sisaCashAll, 0, ",", ".") }}'
                };
                const dataDeal = {
                    total: 'Rp {{ number_format($totalInvestasiDeal, 0, ",", ".") }}',
                    cash: 'Rp {{ number_format($sisaCashDeal, 0, ",", ".") }}'
                };
                
                toggle.addEventListener('change', function() {
                    if (this.checked) {
                        totalText.textContent = dataDeal.total;
                        cashText.textContent = dataDeal.cash;
                    } else {
                        totalText.textContent = dataAll.total;
                        cashText.textContent = dataAll.cash;
                    }
                });
            }
            // Withdrawal Flatpickr
            $("#withdrawal_date").flatpickr({
                dateFormat: "Y-m-d"
            });

            // Activate Tab from Hash
            if (window.location.hash) {
                var tabEl = document.querySelector('a[href="' + window.location.hash + '"]');
                if (tabEl) {
                    var tab = new bootstrap.Tab(tabEl);
                    tab.show();
                }
            }

            // Init DataTable for Withdrawals
            var withdrawalTable = $('#kt_table_withdrawals').DataTable({
                "info": false,
                "order": [],
                "pageLength": 10,
                "columnDefs": [
                    { orderable: false, targets: [5, 6] } // Disable sorting on Bukti and Aksi columns
                ]
            });

            // Filter logic
            $("#withdrawal_daterange").flatpickr({
                mode: "range",
                dateFormat: "Y-m-d",
                onChange: function() {
                    withdrawalTable.draw();
                }
            });

            $('#withdrawal_role_filter, #withdrawal_user_filter').on('change', function() {
                withdrawalTable.draw();
            });

            $('#withdrawal_search').on('keyup', function () {
                withdrawalTable.search(this.value).draw();
            });
            
            function updateWithdrawalActiveFilters() {
                var container = $('#withdrawal_active_filters');
                container.empty();
                
                var date = $('#withdrawal_daterange').val();
                if (date) {
                    container.append(`<span class="badge badge-light-primary">Tanggal: ${date} <i class="ki-duotone ki-cross ms-1 fs-6 cursor-pointer" onclick="clearWithdrawalFilter('date')"><span class="path1"></span><span class="path2"></span></i></span>`);
                }
                
                var roles = $('#withdrawal_role_filter').val();
                if (roles && roles.length > 0) {
                    var roleTexts = [];
                    $('#withdrawal_role_filter option:selected').each(function() { roleTexts.push($(this).text()); });
                    container.append(`<span class="badge badge-light-primary">Peran: ${roleTexts.join(', ')} <i class="ki-duotone ki-cross ms-1 fs-6 cursor-pointer" onclick="clearWithdrawalFilter('role')"><span class="path1"></span><span class="path2"></span></i></span>`);
                }
                
                var users = $('#withdrawal_user_filter').val();
                if (users && users.length > 0) {
                    var userTexts = [];
                    $('#withdrawal_user_filter option:selected').each(function() { userTexts.push($(this).text()); });
                    container.append(`<span class="badge badge-light-primary">Penerima: ${userTexts.join(', ')} <i class="ki-duotone ki-cross ms-1 fs-6 cursor-pointer" onclick="clearWithdrawalFilter('user')"><span class="path1"></span><span class="path2"></span></i></span>`);
                }
                
                if (container.children().length > 0) {
                    container.removeClass('d-none');
                } else {
                    container.addClass('d-none');
                }
            }

            window.clearWithdrawalFilter = function(type) {
                if (type === 'date') {
                    document.getElementById('withdrawal_daterange')._flatpickr.clear();
                } else if (type === 'role') {
                    $('#withdrawal_role_filter').val(null).trigger('change.select2');
                } else if (type === 'user') {
                    $('#withdrawal_user_filter').val(null).trigger('change.select2');
                }
                withdrawalTable.draw();
            };
            
            $('#withdrawal_filter_reset').on('click', function() {
                document.getElementById('withdrawal_daterange')._flatpickr.clear();
                $('#withdrawal_role_filter').val(null).trigger('change.select2');
                $('#withdrawal_user_filter').val(null).trigger('change.select2');
                withdrawalTable.draw();
            });
            
            withdrawalTable.on('draw', function() {
                updateWithdrawalActiveFilters();
            });

            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    if (settings.nTable.id !== 'kt_table_withdrawals') {
                        return true;
                    }

                    var searchDate = $('#withdrawal_daterange').val();
                    var searchRoles = $('#withdrawal_role_filter').val();
                    var searchUsers = $('#withdrawal_user_filter').val();

                    var rawDate = $(settings.aoData[dataIndex].nTr).data('date');
                    var rowUser = data[2];
                    var rowRole = data[3].toLowerCase();

                    // Role filter
                    if (searchRoles && searchRoles.length > 0) {
                        var roleMatch = false;
                        for (var i = 0; i < searchRoles.length; i++) {
                            if (rowRole.includes(searchRoles[i].toLowerCase())) {
                                roleMatch = true;
                                break;
                            }
                        }
                        if (!roleMatch) return false;
                    }

                    // User filter
                    if (searchUsers && searchUsers.length > 0) {
                        if (!searchUsers.includes(rowUser)) {
                            return false;
                        }
                    }

                    // Date range filter
                    if (searchDate && rawDate) {
                        var rowD = new Date(rawDate);
                        rowD.setHours(0,0,0,0);

                        if (searchDate.includes(' to ')) {
                            var dates = searchDate.split(' to ');
                            var minDate = new Date(dates[0]); minDate.setHours(0,0,0,0);
                            var maxDate = new Date(dates[1]); maxDate.setHours(0,0,0,0);
                            
                            if (rowD < minDate || rowD > maxDate) {
                                return false;
                            }
                        } else {
                            var filterDate = new Date(searchDate); filterDate.setHours(0,0,0,0);
                            if (filterDate.getTime() !== rowD.getTime()) {
                                return false;
                            }
                        }
                    }

                    return true;
                }
            );

            // Handle Add Withdrawal
            $('#form_add_withdrawal').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var submitBtn = form.find('button[type="submit"]');
                var originalText = submitBtn.html();
                
                submitBtn.html('<span class="spinner-border spinner-border-sm align-middle ms-2"></span> Menyimpan...').prop('disabled', true);
                
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#kt_modal_add_withdrawal').modal('hide');
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message || 'Penarikan berhasil dicatat',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.hash = '#kt_tab_pane_withdrawals';
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        submitBtn.html(originalText).prop('disabled', false);
                        Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan, silakan coba lagi', 'error');
                    }
                });
            });

            // Handle Delete Withdrawal
            $('.form-delete-withdrawal').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data penarikan ini akan dihapus secara permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-light'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: form.attr('action'),
                            type: 'POST',
                            data: form.serialize(),
                            success: function(response) {
                                Swal.fire({
                                    title: 'Terhapus!',
                                    text: response.message || 'Data berhasil dihapus',
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    window.location.hash = '#kt_tab_pane_withdrawals';
                                    window.location.reload();
                                });
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON?.message || 'Gagal menghapus data', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
