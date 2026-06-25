@extends('layouts.metronic')

@section('title', 'Kelola Bukti Transaksi')

@section('content')
<div class="d-flex flex-column flex-column-fluid">
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
                    Bukti Transaksi
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('console.dashboard') }}" class="text-muted text-hover-primary">Console</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-500 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Bukti Transaksi</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <button type="button" class="btn btn-sm fw-bold btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_upload_proof">
                    Upload Bukti Baru
                </button>
            </div>
        </div>
    </div>

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            @if(session('success'))
            <div class="alert alert-success d-flex align-items-center p-5 mb-5">
                <i class="ki-duotone ki-check-circle fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-success">Berhasil</h4>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger d-flex align-items-center p-5 mb-5">
                <i class="ki-duotone ki-information fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-danger">Terjadi Kesalahan</h4>
                    <span>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </span>
                </div>
            </div>
            @endif

            <div class="card card-flush">
                <div class="card-body pt-0 mt-5">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_proofs_table">
                        <thead>
                            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                <th class="w-10px pe-2">No</th>
                                <th class="min-w-125px">Nama Bukti</th>
                                <th class="min-w-125px">Pratinjau</th>
                                <th class="min-w-125px">Diupload Pada</th>
                                <th class="text-end min-w-70px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                            @forelse($proofs as $index => $proof)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <span class="text-gray-800 text-hover-primary mb-1">{{ $proof->name }}</span>
                                </td>
                                <td>
                                    <a href="{{ Storage::url($proof->file_path) }}" target="_blank" class="symbol symbol-50px">
                                        @if(in_array(pathinfo($proof->file_path, PATHINFO_EXTENSION), ['pdf']))
                                        <div class="symbol-label bg-light-danger text-danger">
                                            <i class="fas fa-file-pdf fs-2x"></i>
                                        </div>
                                        @else
                                        <span class="symbol-label" style="background-image:url('{{ Storage::url($proof->file_path) }}');"></span>
                                        @endif
                                    </a>
                                </td>
                                <td>{{ $proof->created_at->format('d M Y H:i') }}</td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-sm btn-light btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                        Aksi <i class="ki-duotone ki-down fs-5 m-0"></i>
                                    </a>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                        <div class="menu-item px-3">
                                            <a href="{{ Storage::url($proof->file_path) }}" target="_blank" class="menu-link px-3">Lihat</a>
                                        </div>
                                        <div class="menu-item px-3">
                                            <form action="{{ route('transaction-proofs.destroy', $proof->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus bukti ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="menu-link px-3 text-danger border-0 bg-transparent w-100 text-start">Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    Belum ada bukti transaksi. Klik tombol "Upload Bukti Baru" untuk menambahkan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload -->
<div class="modal fade" id="kt_modal_upload_proof" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header" id="kt_modal_upload_proof_header">
                <h2 class="fw-bold">Upload Bukti Transaksi</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <form id="kt_modal_upload_proof_form" class="form" action="{{ route('transaction-proofs.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <div class="fv-row mb-7">
                        <label class="required fs-6 fw-semibold form-label mb-2">Nama Bukti</label>
                        <input type="text" class="form-control form-control-solid" name="name" placeholder="Contoh: Nota Pupuk 15 Jan" required />
                    </div>
                    <div class="fv-row mb-7">
                        <label class="required fs-6 fw-semibold form-label mb-2">File Bukti (Gambar / PDF)</label>
                        <input type="file" class="form-control form-control-solid" name="file" accept=".jpg,.jpeg,.png,.pdf" required />
                        <div class="text-muted fs-7 mt-2">Maksimal ukuran file: 5MB. Format yang didukung: JPG, PNG, PDF.</div>
                    </div>
                </div>
                <div class="modal-footer flex-center pt-4 pb-0">
                    <button type="button" data-bs-dismiss="modal" class="btn btn-light me-3">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">Upload</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
