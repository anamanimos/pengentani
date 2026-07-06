@extends('layouts.metronic')

@section('title', 'Kelola Bukti Transaksi')

@section('page_title', 'Bukti Transaksi')

@section('page_actions')
<button type="button" class="btn btn-sm fw-bold btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_upload_proof">
    Upload Bukti Manual
</button>
@endsection

@section('content')
<div class="app-content flex-column-fluid">
    <div class="app-container container-fluid">
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

        <div class="row g-5 g-xl-8">
            <!-- Left Side: Dropzone Area (4 columns) -->
            <div class="col-xl-4">
                <div class="card card-flush h-xl-100">
                    <div class="card-header pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-800">Upload Bukti</span>
                            <span class="text-gray-500 mt-1 fw-semibold fs-7">Tarik dan lepas file ke sini</span>
                        </h3>
                    </div>
                    <div class="card-body pt-5">
                        <form class="form" action="{{ route('transaction-proofs.store') }}" method="POST" enctype="multipart/form-data" id="kt_dropzone_form">
                            @csrf
                            <div class="fv-row mb-7">
                                <label class="required fs-6 fw-semibold form-label mb-2">Nama Bukti</label>
                                <input type="text" class="form-control form-control-solid" name="name" placeholder="Contoh: Nota 15 Jan" required id="proof_name_input"/>
                            </div>
                            
                            <!-- Dropzone Area -->
                            <div class="dropzone" id="kt_dropzone_proof">
                                <div class="dz-message needsclick">
                                    <i class="ki-duotone ki-file-up fs-3x text-primary"><span class="path1"></span><span class="path2"></span></i>
                                    <div class="ms-4">
                                        <h3 class="fs-5 fw-bold text-gray-900 mb-1">Tarik file ke sini atau klik untuk upload.</h3>
                                        <span class="fs-7 fw-semibold text-gray-500">Maksimal 5MB (JPG, PNG, PDF)</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-5 text-end">
                                <button type="submit" class="btn btn-primary w-100" id="submit_dropzone">Upload Bukti</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Side: Gallery Area (8 columns) -->
            <div class="col-xl-8">
                <div class="card card-flush h-xl-100">
                    <div class="card-header pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-800">Galeri Bukti Transaksi</span>
                            <span class="text-gray-500 mt-1 fw-semibold fs-7">{{ $proofs->count() }} bukti tersimpan</span>
                        </h3>
                        <div class="card-toolbar">
                            <form action="{{ route('transaction-proofs.index') }}" method="GET" class="m-0" id="filter-form">
                                <select name="status" class="form-select form-select-sm form-select-solid fw-bold" data-control="select2" data-hide-search="true" onchange="document.getElementById('filter-form').submit()">
                                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua Status</option>
                                    <option value="unused" {{ request('status') == 'unused' ? 'selected' : '' }}>Belum Digunakan</option>
                                    <option value="used" {{ request('status') == 'used' ? 'selected' : '' }}>Sudah Digunakan</option>
                                </select>
                            </form>
                        </div>
                    </div>
                    <div class="card-body pt-5">
                        <div class="row g-5">
                            @forelse($proofs as $proof)
                            <div class="col-md-3 col-sm-6">
                                <div class="card shadow-sm border-0 position-relative overflow-hidden" style="border-radius: 0.475rem;">
                                    <a href="{{ Storage::url($proof->file_path) }}" data-fslightbox="gallery" class="position-absolute top-0 start-0 w-100 h-100" style="z-index: 1;" title="Lihat Bukti"></a>
                                    
                                    <div class="h-150px d-flex justify-content-center align-items-center bg-light">
                                        @if(in_array(pathinfo($proof->file_path, PATHINFO_EXTENSION), ['pdf']))
                                            <i class="fas fa-file-pdf fs-3x text-danger"></i>
                                        @else
                                            <div class="w-100 h-100" style="background-image:url('{{ Storage::url($proof->file_path) }}'); background-size: cover; background-position: center;"></div>
                                        @endif
                                    </div>
                                    
                                    <!-- Overlay -->
                                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-between p-3 pe-none" 
                                         style="background: linear-gradient(to bottom, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0) 40%, rgba(0,0,0,0) 60%, rgba(0,0,0,0.8) 100%);">
                                        
                                        <!-- Top Action (Delete) -->
                                        <div class="d-flex justify-content-between pe-auto" style="z-index: 2; position: relative;">
                                            <div>
                                                @if($proof->is_used)
                                                    <span class="badge badge-success fw-bold" title="Terikat dengan data">Sudah Digunakan</span>
                                                @else
                                                    <span class="badge badge-secondary fw-bold text-gray-800 bg-white bg-opacity-75" title="Belum terikat data">Belum Digunakan</span>
                                                @endif
                                            </div>
                                            <form action="{{ route('transaction-proofs.destroy', $proof->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus bukti ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-icon btn-sm btn-light-danger bg-white bg-opacity-75" title="Hapus Bukti">
                                                    <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                </button>
                                            </form>
                                        </div>
                                        
                                        <!-- Bottom Info -->
                                        <div class="d-flex justify-content-between align-items-end">
                                            <div class="text-white text-truncate pe-2 w-100">
                                                <span class="fw-bold d-block text-truncate" title="{{ $proof->name }}">{{ $proof->name }}</span>
                                                <span class="fs-8 opacity-75">{{ $proof->created_at->format('d M Y') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12 text-center text-muted py-10">
                                Belum ada bukti transaksi.
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Manual (Just in case they click the header button) -->
<div class="modal fade" id="kt_modal_upload_proof" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Upload Bukti Transaksi</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <form class="form" action="{{ route('transaction-proofs.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <div class="fv-row mb-7">
                        <label class="required fs-6 fw-semibold form-label mb-2">Nama Bukti</label>
                        <input type="text" class="form-control form-control-solid" name="name" placeholder="Contoh: Nota Pupuk 15 Jan" required />
                    </div>
                    <div class="fv-row mb-7">
                        <label class="required fs-6 fw-semibold form-label mb-2">File Bukti</label>
                        <input type="file" class="form-control form-control-solid" name="file" accept=".jpg,.jpeg,.png,.pdf" required />
                    </div>
                </div>
                <div class="modal-footer flex-center pt-4 pb-0">
                    <button type="button" data-bs-dismiss="modal" class="btn btn-light me-3">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/custom/fslightbox/fslightbox.bundle.js') }}"></script>
<script>
    // Initialize Dropzone
    var myDropzone = new Dropzone("#kt_dropzone_proof", {
        url: "{{ route('transaction-proofs.store') }}", // Set the url for your upload script location
        paramName: "file", // The name that will be used to transfer the file
        maxFiles: 1,
        maxFilesize: 5, // MB
        addRemoveLinks: true,
        autoProcessQueue: false,
        acceptedFiles: ".jpeg,.jpg,.png,.pdf",
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        init: function() {
            var submitButton = document.querySelector("#submit_dropzone");
            var myDropzone = this;

            submitButton.addEventListener("click", function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (myDropzone.getQueuedFiles().length > 0) {
                    if(!document.getElementById('proof_name_input').value) {
                        alert("Nama Bukti harus diisi!");
                        return;
                    }
                    myDropzone.processQueue();
                } else {
                    alert("Pilih file terlebih dahulu!");
                }
            });

            this.on("sending", function(file, xhr, formData) {
                // Append name to form data
                formData.append("name", document.getElementById('proof_name_input').value);
            });

            this.on("success", function(file, response) {
                window.location.reload(); // Reload to see the new item in gallery
            });
            
            this.on("error", function(file, response) {
                alert("Upload gagal: " + (response.message || "Kesalahan server"));
            });
        }
    });
</script>
@endpush
