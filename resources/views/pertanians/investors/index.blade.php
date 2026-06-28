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
                    <a href="{{ route('pertanians.show', $pertanian) }}" class="btn btn-sm btn-light me-2">
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
                                // Hanya Deal dan Standby yang porsinya dihitung
                                $porsi = (in_array($investor->status, ['Deal', 'Standby']) && $basisHitung > 0) ? ($investor->besaran_investasi / $basisHitung) * 100 : 0;
                                
                                // Override jika ada porsi_bagi_hasil khusus
                                if ($investor->porsi_bagi_hasil !== null) {
                                    $porsi = $investor->porsi_bagi_hasil;
                                }

                                $bagiHasilIndividu = (in_array($investor->status, ['Deal', 'Standby'])) ? ($porsi / 100) * $labaInvestor : 0;
                                
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
