@extends('layouts.metronic')

@section('title', 'Log Aktivitas Sistem')

@section('content')
<div class="card">
    <div class="card-header border-0 pt-6">
        <div class="card-title">
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span class="path2"></span></i>
                <input type="text" data-kt-log-table-filter="search" class="form-control form-control-solid w-250px ps-13" placeholder="Cari Log..." />
            </div>
        </div>
        
        <div class="card-toolbar">
            <div class="d-flex justify-content-end" data-kt-log-table-toolbar="base">
                <!--begin::Filter-->
                <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                <i class="ki-duotone ki-filter fs-2"><span class="path1"></span><span class="path2"></span></i> Filter
                </button>
                <!--begin::Menu 1-->
                <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true" id="kt-toolbar-filter">
                    <div class="px-7 py-5">
                        <div class="fs-4 text-gray-900 fw-bold">Opsi Filter</div>
                    </div>
                    <div class="separator border-gray-200"></div>
                    <div class="px-7 py-5">
                        <form action="{{ route('activity-logs.index') }}" method="GET" id="kt_filter_form">
                            <!--begin::Input group-->
                            <div class="mb-10">
                                <label class="form-label fs-5 fw-semibold mb-3">Rentang Tanggal:</label>
                                <input class="form-control form-control-solid" placeholder="Pilih tanggal" id="kt_daterangepicker" name="date_range" value="{{ request('date_range') }}"/>
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="mb-10">
                                <label class="form-label fs-5 fw-semibold mb-3">Pengguna:</label>
                                <select class="form-select form-select-solid" data-control="select2" data-placeholder="Pilih Pengguna" data-allow-clear="true" multiple="multiple" name="users[]">
                                    @foreach($filterUsers as $user)
                                        <option value="{{ $user->id }}" {{ is_array(request('users')) && in_array($user->id, request('users')) ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="mb-10">
                                <label class="form-label fs-5 fw-semibold mb-3">Aksi:</label>
                                <select class="form-select form-select-solid" data-control="select2" data-placeholder="Pilih Aksi" data-allow-clear="true" multiple="multiple" name="actions[]">
                                    @foreach($filterActions as $action)
                                        <option value="{{ $action }}" {{ is_array(request('actions')) && in_array($action, request('actions')) ? 'selected' : '' }}>
                                            {{ ucfirst($action) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!--end::Input group-->

                            <div class="d-flex justify-content-end">
                                <a href="{{ route('activity-logs.index') }}" class="btn btn-light btn-active-light-primary me-2">Reset</a>
                                <button type="submit" class="btn btn-primary" data-kt-menu-dismiss="true">Terapkan</button>
                            </div>
                        </form>
                    </div>
                </div>
                <!--end::Menu 1-->
                <!--end::Filter-->
            </div>
        </div>
    </div>
    
    <div class="card-body py-4">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_logs">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-10px pe-2">#</th>
                        <th class="min-w-125px">Waktu</th>
                        <th class="min-w-125px">Pelaku (Pengguna)</th>
                        <th class="min-w-100px">Kategori</th>
                        <th class="min-w-100px">Aksi</th>
                        <th class="min-w-200px">Deskripsi</th>
                        <th class="min-w-125px">Alamat IP</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    @forelse($logs as $index => $log)
                    <tr>
                        <td>{{ $logs->firstItem() + $index }}</td>
                        <td data-order="{{ $log->created_at->timestamp }}">{{ $log->created_at->format('d M Y, H:i') }}</td>
                        <td>
                            @if($log->user)
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                        <div class="symbol-label">
                                            <img src="{{ $log->user->avatar ?? asset('assets/media/avatars/blank.png') }}" alt="{{ $log->user->name }}" class="w-100" />
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <a href="#" class="text-gray-800 text-hover-primary mb-1">{{ $log->user->name }}</a>
                                        <span>{{ $log->user->email }}</span>
                                    </div>
                                </div>
                            @else
                                <span class="badge badge-light-secondary">Sistem / Guest</span>
                            @endif
                        </td>
                        <td>
                            @if($log->type == 'auth')
                                <span class="badge badge-light-primary">Autentikasi</span>
                            @else
                                <span class="badge badge-light-info">{{ ucfirst($log->type) }}</span>
                            @endif
                        </td>
                        <td>
                            @if($log->action == 'login')
                                <span class="badge badge-light-success">Login</span>
                            @elseif($log->action == 'logout')
                                <span class="badge badge-light-warning">Logout</span>
                            @else
                                <span class="badge badge-light-dark">{{ ucfirst($log->action) }}</span>
                            @endif
                        </td>
                        <td>
                            {{ $log->description }}
                            @if($log->payload)
                                <div class="text-muted fs-7 mt-1">
                                    @foreach($log->payload as $key => $val)
                                        <span class="badge badge-outline badge-primary me-1">{{ $key }}: {{ is_array($val) ? json_encode($val) : $val }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td>{{ $log->ip_address }}</td>
                    </tr>
                    @empty
                    <!-- Handled by DataTable empty state but kept for server side empty render -->
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Server-side pagination is handled by DataTables visually or we can keep it as fallback -->
        @if(!$logs->isEmpty())
        <div class="d-flex justify-content-between align-items-center flex-wrap mt-5" id="server_pagination">
            <div class="d-flex flex-wrap py-2 mr-3">
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endpush

@push('scripts')
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize Flatpickr for Date Range
        $("#kt_daterangepicker").flatpickr({
            altInput: true,
            altFormat: "F j, Y",
            dateFormat: "Y-m-d",
            mode: "range"
        });

        // Initialize DataTable
        var table = $('#kt_table_logs').DataTable({
            "info": false,
            'order': [],
            'pageLength': 50,
            "bPaginate": false, // Disable DataTables pagination since we use Laravel's server-side pagination for performance
            'columnDefs': [
                { orderable: false, targets: 0 }, // Disable ordering on #
                { orderable: false, targets: 6 }, // Disable ordering on IP
            ]
        });

        // Client-side search within the current page
        document.querySelector('[data-kt-log-table-filter="search"]').addEventListener('keyup', function (e) {
            table.search(e.target.value).draw();
        });
    });
</script>
@endpush
