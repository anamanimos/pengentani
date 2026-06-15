@extends('layouts.metronic')

@section('title', 'Master Data Toko')
@section('page_title', 'Toko / Vendor')

@section('page_actions')
    <a href="{{ route('stores.create') }}" class="btn btn-primary btn-sm">
        <i class="ki-duotone ki-plus fs-2"></i>Tambah Toko
    </a>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-body py-4">
        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center p-5 mb-10">
                <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-success">Sukses</h4>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center p-5 mb-10">
                <i class="ki-duotone ki-information fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-danger">Gagal</h4>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_stores">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-50px">No</th>
                        <th class="min-w-200px">Nama Toko</th>
                        <th class="min-w-250px">Alamat</th>
                        <th class="min-w-150px">Telepon</th>
                        <th class="text-end min-w-100px">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    @foreach($stores as $index => $store)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $store->name }}</td>
                        <td>{{ $store->address ?? '-' }}</td>
                        <td>{{ $store->phone ?? '-' }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('stores.edit', $store) }}" class="btn btn-icon btn-light-warning btn-sm me-1" title="Edit">
                                <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
                            </a>
                            <form action="{{ route('stores.destroy', $store) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-light-danger btn-sm" title="Hapus">
                                    <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
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
@endsection

@push('styles')
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endpush

@push('scripts')
    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script>
    $(document).ready(function () {
        $('#kt_table_stores').DataTable({
            "language": {
                "lengthMenu": "Tampilkan _MENU_",
                "zeroRecords": "Tidak ada data yang ditemukan",
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
                text: "Data toko ini akan dihapus permanen.",
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
