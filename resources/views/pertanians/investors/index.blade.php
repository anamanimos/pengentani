@extends('layouts.metronic')

@section('title', 'Manajemen Investor - ' . $pertanian->name)
@section('page_title', 'Manajemen Investor')

@section('content')
@php
    $estimasiLaba = $estimasiPendapatan - $totalBiaya;
    $zakatPersen = $pertanian->persentase_zakat ?? 5.00;
    $zakat = $estimasiLaba > 0 ? $estimasiLaba * ($zakatPersen / 100) : 0;
    $labaSetelahZakat = $estimasiLaba - $zakat;

    $labaInvestor = $labaSetelahZakat * ($pertanian->persentase_investor / 100);
    $labaPengelola = $labaSetelahZakat * ($pertanian->persentase_pengelola / 100);
    $labaAdmin = $labaSetelahZakat * ($pertanian->persentase_admin / 100);
    $labaColorClass = $estimasiLaba >= 0 ? 'success' : 'danger';
@endphp

{{-- Ringkasan Rencana Pertanian --}}
<div class="row g-5 g-xl-8 mb-5">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ $pertanian->name }}</h3>
                <div class="card-toolbar">
                    <a href="{{ route('pertanians.index') }}" class="btn btn-sm btn-light me-2">
                        <i class="fas fa-arrow-left fs-5 me-1"></i> Kembali
                    </a>
                    <a href="{{ route('pertanians.investors.create', $pertanian) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus fs-5 me-1"></i> Tambah Investor
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Kartu Ringkasan Finansial --}}
<div class="row g-5 g-xl-8 mb-5">
    <div class="col-xl-3 col-md-6">
        <div class="card bg-light-danger mb-5 mb-xl-10 h-100">
            <div class="card-body d-flex align-items-center py-5">
                <i class="ki-duotone ki-finance-calculator fs-3x text-danger me-5">
                    <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span><span class="path7"></span>
                </i>
                <div class="d-flex flex-column">
                    <span class="text-danger fw-bold fs-7">Total Biaya Operasional</span>
                    <span class="text-gray-900 fw-bold fs-2 text-nowrap">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-light-primary mb-5 mb-xl-10 h-100">
            <div class="card-body d-flex align-items-center py-5">
                <i class="ki-duotone ki-wallet fs-3x text-primary me-5">
                    <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span>
                </i>
                <div class="d-flex flex-column">
                    @php
                        $targetInvestasi = $pertanian->batasan_investasi > 0 ? $pertanian->batasan_investasi : $totalBiaya;
                        $persentaseTerkumpul = $targetInvestasi > 0 ? ($totalInvestasi / $targetInvestasi) * 100 : 0;
                        $statusInvestasi = $persentaseTerkumpul >= 100 ? 'Terpenuhi' : 'Belum Terpenuhi';
                        $statusColor = $persentaseTerkumpul >= 100 ? 'success' : 'warning';
                    @endphp
                    <div class="d-flex align-items-center mb-1">
                        <span class="text-primary fw-bold fs-7">Total Investasi Terkumpul</span>
                        <span class="badge badge-light-{{ $statusColor }} ms-2 fs-9">{{ $statusInvestasi }}</span>
                    </div>
                    <span class="text-gray-900 fw-bold fs-2 text-nowrap">Rp {{ number_format($totalInvestasi, 0, ',', '.') }}</span>
                    <div class="mt-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            @if($pertanian->batasan_investasi > 0)
                                <span class="text-muted fs-8 fw-semibold">Batas: Rp {{ number_format($pertanian->batasan_investasi, 0, ',', '.') }}</span>
                            @else
                                <span class="text-muted fs-8 fw-semibold">Target: Rp {{ number_format($totalBiaya, 0, ',', '.') }}</span>
                            @endif
                            <span class="badge badge-light-primary fs-9 ms-2">{{ number_format($persentaseTerkumpul, 1, ',', '.') }}%</span>
                        </div>
                        <div class="progress h-6px w-100 bg-light-primary">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $persentaseTerkumpul > 100 ? 100 : $persentaseTerkumpul }}%" aria-valuenow="{{ $persentaseTerkumpul }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-light-info mb-5 mb-xl-10 h-100">
            <div class="card-body d-flex align-items-center py-5">
                <i class="ki-duotone ki-graph-up fs-3x text-info me-5">
                    <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span>
                </i>
                <div class="d-flex flex-column">
                    <span class="text-info fw-bold fs-7">Estimasi Pendapatan</span>
                    <span class="text-gray-900 fw-bold fs-2 text-nowrap">Rp {{ number_format($estimasiPendapatan, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-light-{{ $labaColorClass }} mb-5 mb-xl-10 h-100">
            <div class="card-body d-flex align-items-center py-5">
                <i class="ki-duotone ki-badge fs-3x text-{{ $labaColorClass }} me-5">
                    <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span>
                </i>
                <div class="d-flex flex-column">
                    <span class="text-{{ $labaColorClass }} fw-bold fs-7">Estimasi Laba Bersih</span>
                    <span class="text-gray-900 fw-bold fs-2 text-nowrap">Rp {{ number_format($estimasiLaba, 0, ',', '.') }}</span>
                    @if($estimasiLaba > 0)
                        <div class="mt-2 text-muted fs-8">
                            <div class="d-flex justify-content-between gap-3">
                                <span>Zakat ({{ number_format($zakatPersen, 2, ',', '.') }}%):</span>
                                <span class="fw-semibold text-danger">-Rp {{ number_format($zakat, 0, ',', '.') }}</span>
                            </div>
                            <div class="separator separator-dashed my-1"></div>
                            <div class="d-flex justify-content-between gap-3 text-{{ $labaColorClass }} fw-bold">
                                <span>Sisa Laba:</span>
                                <span>Rp {{ number_format($labaSetelahZakat, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Skema Bagi Hasil --}}
<div class="row g-5 g-xl-8 mb-5">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Skema Bagi Hasil</h3>
            </div>
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
                    <div class="col-md-3">
                        <div class="border border-dashed border-gray-300 rounded min-w-125px py-3 px-4 mb-3 bg-light-warning">
                            <div class="fs-3 fw-bold text-warning">{{ number_format($zakatPersen, 2, ',', '.') }}%</div>
                            <div class="fw-semibold text-muted">Zakat</div>
                            <div class="fs-6 fw-bold text-gray-800 mt-2">Rp {{ number_format($zakat, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border border-dashed border-gray-300 rounded min-w-125px py-3 px-4 mb-3">
                            <div class="fs-3 fw-bold text-primary">{{ $pertanian->persentase_investor }}%</div>
                            <div class="fw-semibold text-muted">Investor</div>
                            <div class="fs-6 fw-bold text-gray-800 mt-2">Rp {{ number_format($labaInvestor, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border border-dashed border-gray-300 rounded min-w-125px py-3 px-4 mb-3">
                            <div class="fs-3 fw-bold text-success">{{ $pertanian->persentase_pengelola }}%</div>
                            <div class="fw-semibold text-muted">Pengelola Lahan</div>
                            <div class="fs-6 fw-bold text-gray-800 mt-2">Rp {{ number_format($labaPengelola, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border border-dashed border-gray-300 rounded min-w-125px py-3 px-4 mb-3">
                            <div class="fs-3 fw-bold text-info">{{ $pertanian->persentase_admin }}%</div>
                            <div class="fw-semibold text-muted">Admin</div>
                            <div class="fs-6 fw-bold text-gray-800 mt-2">Rp {{ number_format($labaAdmin, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Ringkasan Status Investasi --}}
<div class="row g-5 g-xl-8 mb-5">
    <div class="col-12">
        <div class="card">
            <div class="card-body py-5">
                <h4 class="card-title text-gray-800 mb-5">Rincian Nominal Berdasarkan Status</h4>
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="border border-dashed border-success rounded min-w-125px py-3 px-4 mb-3 bg-light-success">
                            <div class="fs-4 fw-bold text-success">Rp {{ number_format($investasiDeal, 0, ',', '.') }}</div>
                            <div class="fw-semibold text-success">Deal</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border border-dashed border-primary rounded min-w-125px py-3 px-4 mb-3 bg-light-primary">
                            <div class="fs-4 fw-bold text-primary">Rp {{ number_format($investasiStandby, 0, ',', '.') }}</div>
                            <div class="fw-semibold text-primary">Standby</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border border-dashed border-warning rounded min-w-125px py-3 px-4 mb-3 bg-light-warning">
                            <div class="fs-4 fw-bold text-warning">Rp {{ number_format($investasiNego, 0, ',', '.') }}</div>
                            <div class="fw-semibold text-warning">Nego</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border border-dashed border-danger rounded min-w-125px py-3 px-4 mb-3 bg-light-danger">
                            <div class="fs-4 fw-bold text-danger">Rp {{ number_format($investasiBatal, 0, ',', '.') }}</div>
                            <div class="fw-semibold text-danger">Batal</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tabel Daftar Investor --}}
<div class="row g-5 g-xl-8">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <h3 class="card-title">Daftar Investor</h3>
                </div>
            </div>

            <div class="card-body py-4">
                @if(session('success'))
                    <div class="alert alert-success d-flex align-items-center p-5 mb-10">
                        <i class="fas fa-check-circle fs-2hx text-success me-4"></i>
                        <div class="d-flex flex-column">
                            <h4 class="mb-1 text-success">Sukses</h4>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_investors">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-50px">No</th>
                                <th class="min-w-200px">Nama Investor</th>
                                <th class="min-w-150px">Besaran Investasi</th>
                                <th class="min-w-100px">Porsi (%)</th>
                                <th class="min-w-150px">Estimasi Bagi Hasil</th>
                                <th class="min-w-100px">Status</th>
                                <th class="min-w-150px">Keterangan</th>
                                <th class="text-end min-w-100px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            @foreach($pertanian->investors as $index => $investor)
                            @php
                                $basisHitung = $pertanian->batasan_investasi > 0 ? $pertanian->batasan_investasi : $totalInvestasi;
                                // Hanya Deal dan Standby yang porsinya dihitung (opsional, tapi karena di controller sum() untuk Deal dan Standby, porsinya juga untuk Deal dan Standby)
                                $porsi = (in_array($investor->status, ['Deal', 'Standby']) && $basisHitung > 0) ? ($investor->besaran_investasi / $basisHitung) * 100 : 0;
                                $bagiHasilIndividu = (in_array($investor->status, ['Deal', 'Standby']) && $basisHitung > 0) ? ($investor->besaran_investasi / $basisHitung) * $labaInvestor : 0;
                                
                                $statusColor = 'dark';
                                if($investor->status == 'Deal') $statusColor = 'success';
                                elseif($investor->status == 'Standby') $statusColor = 'primary';
                                elseif($investor->status == 'Nego') $statusColor = 'warning';
                                elseif($investor->status == 'Batal') $statusColor = 'danger';
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
                                    <span class="badge badge-light-{{ $statusColor }}">{{ $investor->status }}</span>
                                </td>
                                <td>
                                    <span class="text-muted fs-7">{{ $investor->keterangan ?? '-' }}</span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('pertanians.investors.edit', [$pertanian, $investor]) }}" class="btn btn-icon btn-light-warning btn-sm me-1" title="Edit">
                                        <i class="fas fa-edit fs-4"></i>
                                    </a>
                                    <form action="{{ route('pertanians.investors.destroy', [$pertanian, $investor]) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-light-danger btn-sm" title="Hapus">
                                            <i class="fas fa-trash fs-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endpush

@push('scripts')
    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script>
    $(document).ready(function () {
        $('#kt_table_investors').DataTable({
            "language": {
                "lengthMenu": "Tampilkan _MENU_",
                "zeroRecords": "Belum ada investor yang terdaftar.",
                "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                "infoEmpty": "Tidak ada record yang tersedia",
                "infoFiltered": "(difilter dari _MAX_ total record)",
                "search": "Cari:"
            }
        });

        // SweetAlert for delete
        $('.delete-form').on('submit', function(e) {
            e.preventDefault();
            let form = this;
            Swal.fire({
                title: "Apakah Anda yakin?",
                text: "Investor ini akan dihapus dari rencana pertanian!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal",
                customClass: {
                    confirmButton: "btn btn-danger",
                    cancelButton: "btn btn-light"
                }
            }).then(function(result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
