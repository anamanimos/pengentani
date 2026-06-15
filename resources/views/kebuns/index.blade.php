@extends('layouts.metronic')

@section('title', 'Data Kebun')
@section('page_title', 'Data Kebun')

@section('content')
<!--begin::Card-->
<div class="card">
    <!--begin::Card header-->
    <div class="card-header border-0 pt-6">
        <!--begin::Card title-->
        <div class="card-title">
            <!--begin::Search-->
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                <input type="text" data-kt-kebun-table-filter="search" class="form-control form-control-solid w-250px ps-13" placeholder="Cari Kebun..." />
            </div>
            <!--end::Search-->
        </div>
        <!--begin::Card title-->
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            <!--begin::Toolbar-->
            <div class="d-flex justify-content-end" data-kt-kebun-table-toolbar="base">
                <a href="{{ route('kebuns.create') }}" class="btn btn-primary">
                    <i class="ki-duotone ki-plus fs-2"></i>Tambah Kebun
                </a>
            </div>
            <!--end::Toolbar-->
        </div>
        <!--end::Card toolbar-->
    </div>
    <!--end::Card header-->
    
    <!--begin::Card body-->
    <div class="card-body py-4">
        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center p-5">
                <i class="ki-duotone ki-check-circle fs-2hx text-success me-4"></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-success">Berhasil</h4>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <!--begin::Table-->
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_kebuns">
            <thead>
                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                    <th class="min-w-125px">Nama Kebun</th>
                    <th class="min-w-125px">Luas (m²)</th>
                    <th class="min-w-125px">Status</th>
                    <th class="min-w-125px">Dibuat Pada</th>
                    <th class="text-end min-w-100px">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 fw-semibold">
                @foreach($kebuns as $kebun)
                <tr>
                    <td>{{ $kebun->name }}</td>
                    <td>{{ number_format($kebun->area, 2, ',', '.') }}</td>
                    <td>
                        @if($kebun->status === 'draft')
                            <span class="badge badge-light-warning fw-bold">Draft</span>
                        @else
                            <span class="badge badge-light-success fw-bold">Aktif</span>
                        @endif
                    </td>
                    <td>{{ $kebun->created_at->format('d M Y H:i') }}</td>
                    <td class="text-end">
                        <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Aksi
                        <i class="ki-duotone ki-down fs-5 ms-1"></i></a>
                        <!--begin::Menu-->
                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                            <div class="menu-item px-3">
                                <a href="{{ route('kebuns.edit', $kebun->id) }}" class="menu-link px-3">Edit</a>
                            </div>
                            <div class="menu-item px-3">
                                <form action="{{ route('kebuns.destroy', $kebun->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="menu-link px-3 w-100 border-0 bg-transparent text-start text-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus kebun ini?');">Hapus</button>
                                </form>
                            </div>
                        </div>
                        <!--end::Menu-->
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <!--end::Table-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Card-->
@endsection

@push('styles')
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endpush

@push('scripts')
    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('js/custom/kebun/table.js') }}"></script>
@endpush
