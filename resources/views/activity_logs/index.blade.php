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
                        <td>{{ $log->created_at->format('d M Y, H:i') }}</td>
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
                    <tr>
                        <td colspan="7" class="text-center">Belum ada aktivitas yang dicatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex flex-wrap py-2 mr-3">
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Search DataTables
    const searchInput = document.querySelector('[data-kt-log-table-filter="search"]');
    if (searchInput) {
        searchInput.addEventListener('keyup', function (e) {
            const val = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#kt_table_logs tbody tr');
            
            rows.forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(val) ? '' : 'none';
            });
        });
    }
</script>
@endpush
