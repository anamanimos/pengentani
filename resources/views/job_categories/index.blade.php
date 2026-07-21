@extends('layouts.metronic')

@section('title', 'Kategori Pekerjaan')
@section('page_title', 'Kategori Pekerjaan')

@section('page_actions')
    <a href="{{ route('worker-jobs.index') }}" class="btn btn-light btn-sm me-3">
        <i class="ki-duotone ki-arrow-left fs-2"><span class="path1"></span><span class="path2"></span></i>Kembali
    </a>
    <a href="{{ route('job-categories.create') }}" class="btn btn-primary btn-sm">
        <i class="ki-duotone ki-plus fs-2"></i>Tambah Kategori
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

        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_job_categories">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-50px">No</th>
                        <th class="min-w-200px">Nama Kategori Pekerjaan</th>
                        <th class="min-w-300px">Deskripsi</th>
                        <th class="min-w-150px">Jumlah Penggunaan</th>
                        <th class="text-end min-w-100px">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    @foreach($categories as $index => $category)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->description ?? '-' }}</td>
                        <td>
                            @if($category->worker_jobs_count > 0)
                                <span class="badge badge-light-success fs-7 fw-bold">{{ $category->worker_jobs_count }} Kali</span>
                            @else
                                <span class="badge badge-light-secondary fs-7 fw-bold">Belum Digunakan</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('job-categories.edit', $category) }}" class="btn btn-icon btn-light-warning btn-sm me-1" title="Edit">
                                <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
                            </a>
                            <form action="{{ route('job-categories.destroy', $category) }}" method="POST" class="d-inline delete-form" data-usage="{{ $category->worker_jobs_count }}">
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
        $('#kt_table_job_categories').DataTable({
            "language": {
                "lengthMenu": "Tampilkan _MENU_",
                "zeroRecords": "Tidak ada data yang ditemukan",
                "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                "infoEmpty": "Tidak ada record yang tersedia",
                "infoFiltered": "(difilter dari _MAX_ total record)",
                "search": "Cari:"
            }
        });

        // SweetAlert for delete with AJAX
        $('.delete-form').on('submit', function(e) {
            e.preventDefault();
            let form = this;
            let url = $(form).attr('action');
            let row = $(form).closest('tr');
            let usage = parseInt($(form).data('usage')) || 0;
            
            let alertText = "Ingin menghapus kategori pekerjaan ini?";
            if (usage > 0) {
                alertText = "Kategori ini telah digunakan sebanyak " + usage + " kali. Karena Soft Delete diaktifkan, data pekerjaan pekerja lama Anda akan tetap aman.";
            }

            Swal.fire({
                title: "Apakah Anda yakin?",
                text: alertText,
                icon: usage > 0 ? "warning" : "question",
                showCancelButton: true,
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal",
                customClass: {
                    confirmButton: "btn btn-danger",
                    cancelButton: "btn btn-light"
                }
            }).then(function(result) {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Mohon tunggu...',
                        text: 'Sedang menghapus kategori...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: $(form).serialize(),
                        dataType: 'json',
                        success: function(response) {
                            Swal.close();
                            if (response.success) {
                                Swal.fire({
                                    title: "Berhasil!",
                                    text: response.message,
                                    icon: "success",
                                    confirmButtonText: "OK",
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                }).then(function() {
                                    // Remove the row from DataTable
                                    let table = $('#kt_table_job_categories').DataTable();
                                    table.row(row).remove().draw();
                                });
                            } else {
                                Swal.fire({
                                    title: "Gagal!",
                                    text: response.message || "Terjadi kesalahan.",
                                    icon: "error",
                                    confirmButtonText: "OK",
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.close();
                            let msg = "Terjadi kesalahan saat menghapus data.";
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                title: "Gagal!",
                                text: msg,
                                icon: "error",
                                confirmButtonText: "OK",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
