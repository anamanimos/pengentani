@extends('layouts.metronic')

@section('title', 'Data Rencana Pertanian')
@section('page_title', 'Data Rencana Pertanian')

@section('page_actions')
    <a href="{{ route('pertanians.create') }}" class="btn btn-primary btn-sm shadow-sm">
        <i class="ki-duotone ki-plus fs-2"></i>Buat Rencana Baru
    </a>
@endsection

@section('content')

<div class="d-flex flex-wrap flex-stack mb-6">
    <div class="d-flex align-items-center my-2 gap-2 ms-auto">
        <!-- View Toggle -->
        <div class="nav-group nav-group-fluid bg-white rounded shadow-sm border" data-kt-buttons="true">
            <button class="btn btn-sm btn-color-muted btn-active btn-active-secondary px-4 active" id="btn_view_grid" title="Grid View">
                <i class="ki-duotone ki-element-11 fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
            </button>
            <button class="btn btn-sm btn-color-muted btn-active btn-active-secondary px-4" id="btn_view_list" title="List View">
                <i class="ki-duotone ki-row-horizontal fs-2"><span class="path1"></span><span class="path2"></span></i>
            </button>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success d-flex align-items-center p-5 mb-10">
        <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
        <div class="d-flex flex-column">
            <h4 class="mb-1 text-success">Sukses</h4>
            <span>{{ session('success') }}</span>
        </div>
    </div>
@endif

<!-- GRID VIEW -->
<div id="view_grid" class="row g-6 g-xl-9">
    @forelse($pertanians as $pertanian)
    <div class="col-md-6 col-xxl-4">
        <div class="card shadow-sm h-100">
            <div class="card-body p-6">
                <!-- Status & Action -->
                <div class="d-flex flex-stack mb-4">
                    @if($pertanian->status == 'Draft')
                        <span class="badge badge-light-secondary">Draft</span>
                    @elseif($pertanian->status == 'Pencarian Investor')
                        <span class="badge badge-light-warning">Pencarian Investor</span>
                    @elseif($pertanian->status == 'Sedang Berjalan')
                        <span class="badge badge-light-primary">Sedang Berjalan</span>
                    @elseif($pertanian->status == 'Selesai')
                        <span class="badge badge-light-success">Selesai</span>
                    @else
                        <span class="badge badge-light-dark">{{ $pertanian->status }}</span>
                    @endif

                    <!-- Dropdown Action -->
                    <button type="button" class="btn btn-sm btn-icon btn-color-light-dark btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                        <i class="ki-duotone ki-dots-square fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                    </button>
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px py-3" data-kt-menu="true">
                        <div class="menu-item px-3">
                            <a href="{{ route('pertanians.show', $pertanian) }}" class="menu-link px-3"><i class="ki-duotone ki-eye fs-4 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Detail Rencana</a>
                        </div>
                        <div class="menu-item px-3">
                            <a href="{{ route('pertanians.investors.index', $pertanian) }}" class="menu-link px-3"><i class="ki-duotone ki-profile-user fs-4 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i> Kelola Investor</a>
                        </div>
                        <div class="menu-item px-3">
                            <a href="{{ route('pertanians.updates.index', $pertanian) }}" class="menu-link px-3"><i class="ki-duotone ki-information fs-4 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Kelola Informasi</a>
                        </div>
                        <div class="menu-item px-3">
                            <a href="{{ route('pertanians.edit', $pertanian) }}" class="menu-link px-3"><i class="ki-duotone ki-pencil fs-4 me-2"><span class="path1"></span><span class="path2"></span></i> Edit</a>
                        </div>
                        <div class="separator mt-3 opacity-75"></div>
                        <div class="menu-item px-3">
                            <form action="{{ route('pertanians.destroy', $pertanian) }}" method="POST" class="delete-form-grid m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="menu-link px-3 w-100 border-0 bg-transparent text-danger"><i class="ki-duotone ki-trash text-danger fs-4 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i> Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Title & Location -->
                <div class="mb-4">
                    <a href="{{ route('pertanians.show', $pertanian) }}" class="fs-4 text-gray-900 fw-bold text-hover-primary mb-1 d-block">{{ $pertanian->name }}</a>
                    <div class="fw-semibold text-muted">
                        <i class="ki-duotone ki-geolocation text-danger fs-6 me-1"><span class="path1"></span><span class="path2"></span></i>
                        {{ $pertanian->kebun->name ?? '-' }}
                    </div>
                </div>

                <!-- Period -->
                <div class="d-flex flex-wrap mb-4">
                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-4 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="ki-duotone ki-calendar-8 text-primary fs-3 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i>
                            <div class="fs-6 fw-bold text-gray-800">
                                @if($pertanian->start_date && $pertanian->end_date)
                                    {{ \Carbon\Carbon::parse($pertanian->start_date)->format('d M y') }} - {{ \Carbon\Carbon::parse($pertanian->end_date)->format('d M y') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <div class="fw-semibold text-muted fs-7 mt-1">Periode Pelaksanaan</div>
                    </div>
                </div>

                <!-- Laba Sementara -->
                <div class="bg-light-{{ $pertanian->laba_sementara >= 0 ? 'success' : 'danger' }} rounded p-4 border border-{{ $pertanian->laba_sementara >= 0 ? 'success' : 'danger' }} border-dashed">
                    <div class="d-flex flex-stack">
                        <div class="d-flex flex-column">
                            <span class="text-{{ $pertanian->laba_sementara >= 0 ? 'success' : 'danger' }} fw-bold fs-7">Laba/Rugi Sementara</span>
                            <span class="text-gray-900 fw-bold fs-3 text-nowrap">Rp {{ number_format($pertanian->laba_sementara, 0, ',', '.') }}</span>
                        </div>
                        <i class="ki-duotone ki-{{ $pertanian->laba_sementara >= 0 ? 'arrow-up-refraction text-success' : 'arrow-down-refraction text-danger' }} fs-2x">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span>
                        </i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-10">
                <div class="text-muted fs-5">Belum ada data rencana pertanian.</div>
            </div>
        </div>
    </div>
    @endforelse
</div>

<!-- LIST VIEW -->
<div id="view_list" class="card d-none">
    <div class="card-body py-4">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5 text-nowrap" id="kt_table_pertanians">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-50px">No</th>
                        <th class="min-w-150px">Nama Rencana</th>
                        <th class="min-w-150px">Lokasi Kebun</th>
                        <th class="min-w-150px">Periode</th>
                        <th class="min-w-150px">Laba Sementara</th>
                        <th class="min-w-100px">Status</th>
                        <th class="text-end min-w-150px">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    @foreach($pertanians as $index => $pertanian)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <a href="{{ route('pertanians.show', $pertanian) }}" class="text-gray-800 text-hover-primary fw-bold">{{ $pertanian->name }}</a>
                        </td>
                        <td>{{ $pertanian->kebun->name ?? '-' }}</td>
                        <td>
                            @if($pertanian->start_date && $pertanian->end_date)
                                {{ \Carbon\Carbon::parse($pertanian->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($pertanian->end_date)->format('d M Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-light-{{ $pertanian->laba_sementara >= 0 ? 'success' : 'danger' }} fs-7">
                                Rp {{ number_format($pertanian->laba_sementara, 0, ',', '.') }}
                            </span>
                        </td>
                        <td>
                            @if($pertanian->status == 'Draft')
                                <span class="badge badge-light-secondary">Draft</span>
                            @elseif($pertanian->status == 'Pencarian Investor')
                                <span class="badge badge-light-warning">Pencarian Investor</span>
                            @elseif($pertanian->status == 'Sedang Berjalan')
                                <span class="badge badge-light-primary">Sedang Berjalan</span>
                            @elseif($pertanian->status == 'Selesai')
                                <span class="badge badge-light-success">Selesai</span>
                            @else
                                <span class="badge badge-light-dark">{{ $pertanian->status }}</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <button type="button" class="btn btn-sm btn-icon btn-light btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                <i class="ki-duotone ki-dots-square fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                            </button>
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px py-3" data-kt-menu="true">
                                <div class="menu-item px-3">
                                    <a href="{{ route('pertanians.show', $pertanian) }}" class="menu-link px-3"><i class="ki-duotone ki-eye fs-4 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Detail Rencana</a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="{{ route('pertanians.investors.index', $pertanian) }}" class="menu-link px-3"><i class="ki-duotone ki-profile-user fs-4 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i> Kelola Investor</a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="{{ route('pertanians.edit', $pertanian) }}" class="menu-link px-3"><i class="ki-duotone ki-pencil fs-4 me-2"><span class="path1"></span><span class="path2"></span></i> Edit</a>
                                </div>
                                <div class="separator mt-3 opacity-75"></div>
                                <div class="menu-item px-3">
                                    <form action="{{ route('pertanians.destroy', $pertanian) }}" method="POST" class="delete-form-list m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="menu-link px-3 w-100 border-0 bg-transparent text-danger"><i class="ki-duotone ki-trash text-danger fs-4 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i> Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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
        // Initialize DataTable for List View
        $('#kt_table_pertanians').DataTable({
            "language": {
                "lengthMenu": "Tampilkan _MENU_",
                "zeroRecords": "Tidak ada data yang ditemukan",
                "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                "infoEmpty": "Tidak ada record yang tersedia",
                "infoFiltered": "(difilter dari _MAX_ total record)",
                "search": "Cari:"
            }
        });

        // Delete confirmation
        $(document).on('submit', '.delete-form-grid, .delete-form-list', function(e) {
            e.preventDefault();
            let form = this;
            Swal.fire({
                title: "Apakah Anda yakin?",
                text: "Rencana pertanian ini dan rinciannya akan dihapus permanen!",
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

        // View Toggler
        const btnGrid = $('#btn_view_grid');
        const btnList = $('#btn_view_list');
        const viewGrid = $('#view_grid');
        const viewList = $('#view_list');

        // Load preference
        const viewPref = localStorage.getItem('pertanian_view_pref') || 'grid';
        if (viewPref === 'list') {
            showList();
        } else {
            showGrid();
        }

        btnGrid.on('click', function(e) {
            e.preventDefault();
            showGrid();
            localStorage.setItem('pertanian_view_pref', 'grid');
        });

        btnList.on('click', function(e) {
            e.preventDefault();
            showList();
            localStorage.setItem('pertanian_view_pref', 'list');
        });

        function showGrid() {
            btnGrid.addClass('active');
            btnList.removeClass('active');
            viewGrid.removeClass('d-none');
            viewList.addClass('d-none');
        }

        function showList() {
            btnList.addClass('active');
            btnGrid.removeClass('active');
            viewList.removeClass('d-none');
            viewGrid.addClass('d-none');
        }
    });
</script>
@endpush
