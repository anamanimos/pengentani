@extends('layouts.metronic')

@section('title', 'Data Tengkulak')
@section('page_title', 'Data Tengkulak')

@section('page_actions')
    <a href="{{ route('incomes.index') }}" class="btn btn-sm btn-light me-3">
        <i class="ki-duotone ki-arrow-left fs-2"><span class="path1"></span><span class="path2"></span></i> Kembali
    </a>
    <button type="button" class="btn btn-sm fw-bold btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_tengkulak">
        Tambah Tengkulak
    </button>
@endsection

@section('content')
<div class="d-flex flex-column flex-column-fluid">

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            @if(session('success'))
                <div class="alert alert-success d-flex align-items-center p-5 mb-10">
                    <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-success">Berhasil</h4>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger d-flex align-items-center p-5 mb-10">
                    <i class="ki-duotone ki-information-5 fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-danger">Gagal</h4>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-body py-4">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_tengkulaks">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="w-10px pe-2">No</th>
                                <th class="min-w-125px">Nama</th>
                                <th class="min-w-125px">No. Telepon</th>
                                <th class="text-end min-w-100px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            @foreach($tengkulaks as $index => $tengkulak)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $tengkulak->name }}</td>
                                <td>{{ $tengkulak->phone ?? '-' }}</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-icon btn-light-warning btn-sm me-1" data-bs-toggle="modal" data-bs-target="#kt_modal_edit_tengkulak_{{ $tengkulak->id }}" title="Edit">
                                        <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </button>

                                    <form action="{{ route('tengkulaks.destroy', $tengkulak) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-light-danger btn-sm" title="Hapus">
                                            <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="kt_modal_edit_tengkulak_{{ $tengkulak->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered mw-650px">
                                    <div class="modal-content">
                                        <div class="modal-header" id="kt_modal_add_user_header">
                                            <h2 class="fw-bold">Edit Tengkulak</h2>
                                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                                <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                                            </div>
                                        </div>
                                        <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                                            <form id="kt_modal_add_user_form" class="form" action="{{ route('tengkulaks.update', $tengkulak) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_add_user_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">
                                                    
                                                    <div class="fv-row mb-7">
                                                        <label class="required fw-semibold fs-6 mb-2">Nama</label>
                                                        <input type="text" name="name" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Nama Tengkulak" value="{{ $tengkulak->name }}" required />
                                                    </div>

                                                    <div class="fv-row mb-7">
                                                        <label class="fw-semibold fs-6 mb-2">No. Telepon (Opsional)</label>
                                                        <input type="text" name="phone" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="08xxxxxxxxxx" value="{{ $tengkulak->phone }}" />
                                                    </div>
                                                </div>
                                                <div class="text-center pt-15">
                                                    <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">
                                                        <span class="indicator-label">Simpan Perubahan</span>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="kt_modal_add_tengkulak" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header" id="kt_modal_add_user_header">
                <h2 class="fw-bold">Tambah Tengkulak</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="kt_modal_add_user_form" class="form" action="{{ route('tengkulaks.store') }}" method="POST">
                    @csrf
                    <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_add_user_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">
                        
                        <div class="fv-row mb-7">
                            <label class="required fw-semibold fs-6 mb-2">Nama</label>
                            <input type="text" name="name" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Nama Tengkulak" required />
                        </div>

                        <div class="fv-row mb-7">
                            <label class="fw-semibold fs-6 mb-2">No. Telepon (Opsional)</label>
                            <input type="text" name="phone" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="08xxxxxxxxxx" />
                        </div>
                    </div>
                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">Simpan</span>
                        </button>
                    </div>
                </form>
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
        $('#kt_table_tengkulaks').DataTable({
            "language": {
                "lengthMenu": "Tampilkan _MENU_",
                "zeroRecords": "Tidak ada data yang ditemukan",
                "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                "infoEmpty": "Tidak ada record yang tersedia",
                "infoFiltered": "(difilter dari _MAX_ total record)",
                "search": "Cari:"
            },
            "columnDefs": [
                { orderable: false, targets: 3 }
            ]
        });

        // SweetAlert for delete
        $('.delete-form').on('submit', function(e) {
            e.preventDefault();
            let form = this;
            Swal.fire({
                title: "Apakah Anda yakin?",
                text: "Data tengkulak ini akan dihapus permanen!",
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
