@extends('layouts.metronic')

@section('title', 'Kategori Pemasukan')
@section('page_title', 'Kategori Pemasukan')

@section('page_actions')
    <a href="{{ route('incomes.index') }}" class="btn btn-light-primary btn-sm me-3">
        <i class="ki-duotone ki-arrow-left fs-2"><span class="path1"></span><span class="path2"></span></i>Kembali ke Pemasukan
    </a>
    <button type="button" class="btn btn-primary btn-sm btn-create">
        <i class="ki-duotone ki-plus fs-2"></i>Tambah Kategori
    </button>
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
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_income_categories">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-50px">No</th>
                        <th class="min-w-200px">Nama Kategori Pemasukan</th>
                        <th class="min-w-300px">Deskripsi</th>
                        <th class="text-end min-w-100px">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    @foreach($categories as $index => $category)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->description ?? '-' }}</td>
                        <td class="text-end text-nowrap">
                            <button type="button" class="btn btn-icon btn-light-warning btn-sm me-1 btn-edit" 
                                data-id="{{ $category->id }}" 
                                data-name="{{ $category->name }}" 
                                data-description="{{ $category->description ?? '' }}" 
                                title="Edit">
                                <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
                            </button>
                            <button type="button" class="btn btn-icon btn-light-danger btn-sm btn-delete" 
                                data-id="{{ $category->id }}" 
                                title="Hapus">
                                <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                            </button>
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
        var table = $('#kt_table_income_categories').DataTable({
            "language": {
                "lengthMenu": "Tampilkan _MENU_",
                "zeroRecords": "Tidak ada data yang ditemukan",
                "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                "infoEmpty": "Tidak ada record yang tersedia",
                "infoFiltered": "(difilter dari _MAX_ total record)",
                "search": "Cari:"
            }
        });

        // Form HTML for SweetAlert
        const formHtml = `
            <div class="mb-5 text-start">
                <label class="required form-label">Nama Kategori</label>
                <input type="text" id="swal-input-name" class="form-control form-control-solid" placeholder="Contoh: Panen Sayur">
            </div>
            <div class="mb-0 text-start">
                <label class="form-label">Deskripsi (Opsional)</label>
                <textarea id="swal-input-desc" class="form-control form-control-solid" rows="3" placeholder="Keterangan kategori"></textarea>
            </div>
        `;

        // Create
        $('.btn-create').click(function() {
            Swal.fire({
                title: 'Tambah Kategori Pemasukan',
                html: formHtml,
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-light"
                },
                preConfirm: () => {
                    const name = document.getElementById('swal-input-name').value;
                    const desc = document.getElementById('swal-input-desc').value;
                    if (!name) {
                        Swal.showValidationMessage('Nama kategori wajib diisi');
                        return false;
                    }
                    return $.ajax({
                        url: '{{ route("income-categories.store") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            name: name,
                            description: desc
                        }
                    }).catch(error => {
                        Swal.showValidationMessage(`Error: ${error.responseJSON?.message || error.statusText}`);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: result.value.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }
            });
        });

        // Edit
        $(document).on('click', '.btn-edit', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            let desc = $(this).data('description');

            Swal.fire({
                title: 'Edit Kategori Pemasukan',
                html: formHtml,
                didOpen: () => {
                    document.getElementById('swal-input-name').value = name;
                    document.getElementById('swal-input-desc').value = desc;
                },
                showCancelButton: true,
                confirmButtonText: 'Simpan Perubahan',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-light"
                },
                preConfirm: () => {
                    const newName = document.getElementById('swal-input-name').value;
                    const newDesc = document.getElementById('swal-input-desc').value;
                    if (!newName) {
                        Swal.showValidationMessage('Nama kategori wajib diisi');
                        return false;
                    }
                    return $.ajax({
                        url: `/console/incomes/categories/${id}`,
                        type: 'PUT',
                        data: {
                            _token: '{{ csrf_token() }}',
                            name: newName,
                            description: newDesc
                        }
                    }).catch(error => {
                        Swal.showValidationMessage(`Error: ${error.responseJSON?.message || error.statusText}`);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: result.value.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }
            });
        });

        // Delete
        $(document).on('click', '.btn-delete', function() {
            let id = $(this).data('id');
            Swal.fire({
                title: "Apakah Anda yakin?",
                text: "Ingin menghapus Kategori Pemasukan ini?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal",
                customClass: {
                    confirmButton: "btn btn-danger",
                    cancelButton: "btn btn-light"
                },
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: `/console/incomes/categories/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        }
                    }).catch(error => {
                        Swal.showValidationMessage(`Error: ${error.responseJSON?.message || error.statusText}`);
                    });
                }
            }).then(function(result) {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Terhapus!',
                        text: result.value.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }
            });
        });
    });
</script>
@endpush
