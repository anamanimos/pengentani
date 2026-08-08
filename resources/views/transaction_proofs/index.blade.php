@extends('layouts.metronic')

@section('title', 'Galeri Bukti Transaksi')

<style>
    :root {
        --grid-cols: 8;
    }

    .proof-gallery-grid {
        display: grid;
        grid-template-columns: repeat(var(--grid-cols, 8), minmax(0, 1fr));
        gap: 14px;
        transition: grid-template-columns 0.3s ease;
    }

    @media (max-width: 1600px) {
        .proof-gallery-grid {
            grid-template-columns: repeat(min(var(--grid-cols, 8), 6), minmax(0, 1fr));
        }
    }
    @media (max-width: 1200px) {
        .proof-gallery-grid {
            grid-template-columns: repeat(min(var(--grid-cols, 8), 4), minmax(0, 1fr));
        }
    }
    @media (max-width: 768px) {
        .proof-gallery-grid {
            grid-template-columns: repeat(min(var(--grid-cols, 8), 3), minmax(0, 1fr));
        }
    }
    @media (max-width: 480px) {
        .proof-gallery-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }

    .proof-card-item {
        position: relative;
        overflow: hidden;
        width: 100%;
        aspect-ratio: 1 / 1;
        background-color: #1a1a27;
        border-radius: 6px;
        border: 1px solid #e4e6ef;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    [data-bs-theme="dark"] .proof-card-item {
        border-color: #2b2b40;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.35);
    }
    
    .proof-card-item:hover {
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.16);
        transform: translateY(-3px);
    }

    .proof-card-item .proof-img {
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        transition: transform 0.4s cubic-bezier(0.25, 1, 0.5, 1);
    }
    
    .proof-card-item .proof-pdf-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background-color: #1e1e2d;
        color: #f1416c;
        transition: transform 0.4s ease;
    }
    
    .proof-card-item:hover .proof-img,
    .proof-card-item:hover .proof-pdf-placeholder {
        transform: scale(1.08);
    }
    
    .proof-card-item .proof-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, rgba(0,0,0,0.75) 0%, rgba(0,0,0,0.15) 50%, rgba(0,0,0,0.85) 100%);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 10px;
        opacity: 0;
        transition: opacity 0.25s ease;
        z-index: 2;
        pointer-events: none;
    }
    
    .proof-card-item:hover .proof-overlay {
        opacity: 1;
    }
    
    .proof-overlay-btn,
    .proof-overlay-badge {
        pointer-events: auto;
    }

    /* Floating Bottom Tools Bar */
    .sticky-tools-bar {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        width: calc(100% - 60px);
        max-width: 1300px;
        z-index: 999;
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(228, 230, 239, 0.8);
        border-radius: 50px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        transition: all 0.3s ease;
    }

    @media (max-width: 991.98px) {
        .sticky-tools-bar {
            width: calc(100% - 30px);
            bottom: 12px;
            border-radius: 20px;
        }
    }

    [data-bs-theme="dark"] .sticky-tools-bar {
        background: rgba(30, 30, 45, 0.92);
        border-color: rgba(43, 43, 64, 0.8);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }
</style>

@section('page_title')
    Galeri Bukti Transaksi <span class="text-gray-500 fw-semibold fs-7 ms-2">({{ $proofs->count() }} bukti tersimpan)</span>
@endsection

@section('content')
<div class="app-content flex-column-fluid pb-28">
    <div class="app-container container-fluid">
        @if(session('success'))
        <div class="alert alert-success d-flex align-items-center p-5 mb-5 rounded-3 shadow-xs">
            <i class="ki-duotone ki-check-circle fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-success">Berhasil</h4>
                <span>{{ session('success') }}</span>
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center p-5 mb-5 rounded-3 shadow-xs">
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

        <!-- Full Width Gallery Grid Container -->
        <div class="row mb-8">
            <div class="col-12">
                <div class="proof-gallery-grid" id="proof_gallery_grid">
                    @forelse($proofs as $proof)
                    <div class="proof-card" data-id="{{ $proof->id }}" data-name="{{ strtolower($proof->name) }}">
                        <div class="proof-card-item">
                            <a href="{{ $proof->url }}" data-fslightbox="gallery" class="position-absolute top-0 start-0 w-100 h-100" style="z-index: 1;" title="Lihat Bukti"></a>
                            
                            @if(in_array(strtolower(pathinfo($proof->file_path, PATHINFO_EXTENSION)), ['pdf']))
                                <div class="proof-pdf-placeholder">
                                    <i class="fas fa-file-pdf fs-2x mb-1 text-danger"></i>
                                    <span class="fs-9 fw-bold text-gray-400 text-uppercase">PDF</span>
                                </div>
                            @else
                                <div class="proof-img" style="background-image:url('{{ $proof->url }}');"></div>
                            @endif
                            
                            <!-- Overlay -->
                            <div class="proof-overlay">
                                <!-- Top Action (Delete) -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="proof-overlay-badge" style="z-index: 3;">
                                        @if($proof->is_used)
                                            <span class="badge badge-success fw-bold fs-9 py-1 rounded-pill" title="Terikat dengan data">Sudah Digunakan</span>
                                        @else
                                            <span class="badge badge-secondary fw-bold text-gray-800 bg-white bg-opacity-75 fs-9 py-1 rounded-pill" title="Belum terikat data">Belum Digunakan</span>
                                        @endif
                                    </div>
                                    <form action="{{ route('transaction-proofs.destroy', $proof->id) }}" method="POST" class="d-inline proof-overlay-btn" style="z-index: 3;" onsubmit="return confirm('Hapus bukti ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-sm btn-light-danger bg-white bg-opacity-90 w-25px h-25px rounded-circle" title="Hapus Bukti">
                                            <i class="ki-duotone ki-trash fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                        </button>
                                    </form>
                                </div>
                                
                                <!-- Bottom Info -->
                                <div class="d-flex justify-content-between align-items-end">
                                    <div class="text-white text-truncate pe-2 w-100">
                                        <span class="fw-bold d-block text-truncate proof-name-display fs-7" title="{{ $proof->name }}">{{ $proof->name }}</span>
                                        <span class="fs-9 opacity-75">{{ $proof->created_at->format('d M Y') }}</span>
                                    </div>
                                    <!-- Rename and History Buttons -->
                                    <div class="d-flex gap-1 proof-overlay-btn" style="z-index: 3;">
                                        @if(!empty($proof->rename_history))
                                            <button type="button" class="btn btn-icon btn-sm btn-light bg-white bg-opacity-90 w-25px h-25px rounded-circle btn-view-history" 
                                                    title="Lihat Riwayat Nama" 
                                                    data-name="{{ $proof->name }}"
                                                    data-history="{{ json_encode($proof->rename_history) }}">
                                                <i class="fa fa-history text-gray-700 fs-9"></i>
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-icon btn-sm btn-light bg-white bg-opacity-90 w-25px h-25px rounded-circle btn-view-detail" 
                                                title="Detail Transaksi (Offcanvas)" 
                                                data-id="{{ $proof->id }}">
                                            <i class="ki-duotone ki-eye fs-5 text-gray-700"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                        </button>
                                        <button type="button" class="btn btn-icon btn-sm btn-light bg-white bg-opacity-90 w-25px h-25px rounded-circle btn-rename" 
                                                title="Ganti Nama" 
                                                data-id="{{ $proof->id }}" 
                                                data-name="{{ $proof->name }}"
                                                data-url="{{ route('transaction-proofs.rename', $proof->id) }}">
                                            <i class="ki-duotone ki-pencil fs-5 text-gray-700"><span class="path1"></span><span class="path2"></span></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center text-muted py-15 bg-body rounded-3 border">
                        <i class="ki-duotone ki-file-slash fs-3x text-gray-400 mb-3"><span class="path1"></span><span class="path2"></span></i>
                        <div class="fs-5 fw-bold text-gray-700">Belum Ada Bukti Transaksi</div>
                        <span class="fs-7 text-gray-500">Klik tombol Upload Bukti untuk menambahkan berkas baru.</span>
                    </div>
                    @endforelse
                </div>

                <!-- Empty Search Result Indicator -->
                <div id="no_search_results" class="d-none text-center text-muted py-15 bg-body rounded-3 border mt-4">
                    <i class="ki-duotone ki-magnifier fs-3x text-gray-400 mb-3"><span class="path1"></span><span class="path2"></span></i>
                    <div class="fs-5 fw-bold text-gray-700">Tidak Ada Bukti Transaksi Sesuai</div>
                    <span class="fs-7 text-gray-500">Coba ubah kata kunci pencarian Anda.</span>
                </div>
            </div>
        </div>

        <!-- Floating Bottom Tools Bar Container -->
        <div class="sticky-tools-bar p-3 px-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <!-- Left: Cluster Tools (Upload Button + Grid Slider + Status Filter) -->
                <div class="d-flex align-items-center flex-wrap gap-3">
                    <!-- Upload Modal Trigger Button -->
                    <button type="button" class="btn btn-sm btn-primary fw-bold rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#kt_modal_upload_proof">
                        <i class="ki-duotone ki-file-up fs-3 me-1"><span class="path1"></span><span class="path2"></span></i> Upload Bukti
                    </button>

                    <!-- Grid Volume/Column Slider -->
                    <div class="d-flex align-items-center gap-2 bg-light rounded-pill px-3 py-1 border border-gray-300 shadow-2xs" title="Atur Ukuran Grid Galeri">
                        <i class="ki-duotone ki-element-11 fs-4 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                        <input type="range" class="form-range" id="grid_cols_range" min="3" max="8" value="8" step="1" style="width: 80px; cursor: pointer;">
                        <span class="fs-8 fw-bold text-gray-700 min-w-45px text-end" id="grid_cols_label">8 Kolom</span>
                    </div>

                    <!-- Filter Status -->
                    <form action="{{ route('transaction-proofs.index') }}" method="GET" class="m-0" id="filter-form">
                        <select name="status" class="form-select form-select-sm form-select-solid fw-bold rounded-pill" data-control="select2" data-hide-search="true" onchange="document.getElementById('filter-form').submit()">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua Status</option>
                            <option value="unused" {{ request('status') == 'unused' ? 'selected' : '' }}>Belum Digunakan</option>
                            <option value="used" {{ request('status') == 'used' ? 'selected' : '' }}>Sudah Digunakan</option>
                        </select>
                    </form>
                </div>

                <!-- Right: Wider Live Search Input -->
                <div class="d-flex align-items-center flex-grow-1 ms-auto" style="min-width: 280px; max-width: 600px;">
                    <div class="position-relative w-100">
                        <i class="ki-duotone ki-magnifier fs-2 text-primary position-absolute top-50 translate-middle-y ms-3"><span class="path1"></span><span class="path2"></span></i>
                        <input type="text" id="floating_search_input" class="form-control form-control-solid ps-10 pe-10 fs-7 fw-semibold rounded-pill" placeholder="Cari nama bukti transaksi..." autocomplete="off">
                        <button type="button" class="btn btn-icon btn-sm btn-active-color-primary position-absolute top-50 translate-middle-y end-0 me-2 d-none" id="clear_search_btn" title="Hapus pencarian">
                            <i class="ki-duotone ki-cross fs-3"><span class="path1"></span><span class="path2"></span></i>
                        </button>
                    </div>
                    <span class="badge badge-light-primary fw-bold px-3 py-2 rounded-pill fs-8 ms-2 text-nowrap" id="search_count_badge">{{ $proofs->count() }} bukti</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Bukti Transaksi -->
<div class="modal fade" id="kt_modal_upload_proof" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content rounded-3">
            <div class="modal-header py-4 px-6">
                <h3 class="fw-bold modal-title text-gray-800 d-flex align-items-center">
                    <i class="ki-duotone ki-file-up fs-1 text-primary me-2"><span class="path1"></span><span class="path2"></span></i>
                    Upload Bukti Transaksi
                </h3>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <form class="form" action="{{ route('transaction-proofs.store') }}" method="POST" enctype="multipart/form-data" id="kt_dropzone_form">
                @csrf
                <div class="modal-body scroll-y px-6 py-5">
                    <!-- Dropzone Area -->
                    <div class="dropzone border-dashed border-primary bg-light-primary rounded-3" id="kt_dropzone_proof">
                        <div class="dz-message needsclick text-center py-5">
                            <i class="ki-duotone ki-cloud-upload fs-4x text-primary mb-3"><span class="path1"></span><span class="path2"></span></i>
                            <div class="ms-0">
                                <h3 class="fs-6 fw-bold text-gray-900 mb-1">Tarik file ke sini, Paste (CTRL+V), atau klik untuk upload.</h3>
                                <span class="fs-8 fw-semibold text-gray-500">Dapat pilih banyak file sekaligus (JPG, PNG, PDF max 5MB/file)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Multi-naming container -->
                    <div class="mt-4" id="multi_naming_wrapper" style="display: none;">
                        <label class="fs-7 fw-bold text-gray-800 mb-2 d-block">Penamaan File Bukti (Opsional):</label>
                        <div id="file_names_container" class="d-flex flex-column gap-2 pe-1" style="max-height: 250px; overflow-y: auto;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-center py-4 px-6">
                    <button type="button" data-bs-dismiss="modal" class="btn btn-light me-3 fw-bold rounded-pill">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold rounded-pill px-6" id="submit_dropzone">Upload Semua Bukti</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Offcanvas Detail Bukti Transaksi Drawer -->
<div class="offcanvas offcanvas-end w-100 w-md-650px w-xl-800px shadow-lg" tabindex="-1" id="kt_offcanvas_proof_detail" aria-labelledby="offcanvas_proof_title" style="z-index: 1060;">
    <div class="offcanvas-header py-4 px-6 border-bottom bg-body d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-icon btn-sm btn-light btn-offcanvas-prev me-1" title="Bukti Sebelumnya">
                <i class="fa-solid fa-chevron-left fs-4 text-gray-700"></i>
            </button>
            <button type="button" class="btn btn-icon btn-sm btn-light btn-offcanvas-next me-2" title="Bukti Selanjutnya">
                <i class="fa-solid fa-chevron-right fs-4 text-gray-700"></i>
            </button>
            <h4 class="offcanvas-title fw-bold text-gray-800 text-truncate mb-0" id="offcanvas_proof_title" style="max-width: 450px;">Detail Bukti Transaksi</h4>
        </div>
        <button type="button" class="btn btn-icon btn-sm btn-active-light-primary rounded-circle" data-bs-dismiss="offcanvas" aria-label="Close">
            <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
        </button>
    </div>
    <div class="offcanvas-body p-6 bg-light" id="offcanvas_proof_body">
        <!-- AJAX loaded content will be placed here -->
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/custom/fslightbox/fslightbox.bundle.js') }}"></script>
<script>
    $(document).ready(function() {
        // Grid Column Slider Logic
        var savedCols = localStorage.getItem('proof_grid_cols') || '8';
        $('#grid_cols_range').val(savedCols);
        updateGridCols(savedCols);

        $('#grid_cols_range').on('input change', function() {
            var cols = $(this).val();
            updateGridCols(cols);
            localStorage.setItem('proof_grid_cols', cols);
        });

        function updateGridCols(cols) {
            document.documentElement.style.setProperty('--grid-cols', cols);
            $('#grid_cols_label').text(cols + ' Kolom');
        }

        // Initialize Dropzone for Multi-Upload
        Dropzone.autoDiscover = false;
        var myDropzone = new Dropzone("#kt_dropzone_proof", {
            url: "{{ route('transaction-proofs.store') }}",
            paramName: "file",
            maxFiles: 50,
            maxFilesize: 5, // MB
            parallelUploads: 5,
            addRemoveLinks: true,
            autoProcessQueue: false,
            acceptedFiles: ".jpeg,.jpg,.png,.pdf",
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            init: function() {
                var submitButton = document.querySelector("#submit_dropzone");
                var myDropzone = this;

                function renderNamingContainer() {
                    var files = myDropzone.files;
                    var wrapper = $("#multi_naming_wrapper");
                    var container = $("#file_names_container");

                    if (files.length === 0) {
                        wrapper.hide();
                        container.empty();
                        return;
                    }

                    wrapper.show();

                    // Preserve existing input values
                    var existingValues = {};
                    container.find('.proof-file-name-input').each(function() {
                        var uuid = $(this).data('uuid');
                        existingValues[uuid] = $(this).val();
                    });

                    container.empty();

                    files.forEach(function(file, index) {
                        var uuid = file.upload ? file.upload.uuid : index;
                        var defaultName = file.name.replace(/\.[^/.]+$/, "");
                        var currentVal = existingValues[uuid] !== undefined ? existingValues[uuid] : defaultName;

                        var fileRow = `
                            <div class="d-flex align-items-center gap-2 p-2 bg-light rounded border">
                                <i class="fa ${file.type === 'application/pdf' ? 'fa-file-pdf text-danger' : 'fa-file-image text-primary'} fs-5"></i>
                                <span class="fs-8 text-gray-700 text-truncate fw-semibold flex-grow-1" style="max-width: 140px;" title="${file.name}">${file.name}</span>
                                <input type="text" 
                                       class="form-control form-control-sm form-control-solid proof-file-name-input flex-grow-1" 
                                       data-uuid="${uuid}" 
                                       placeholder="Nama Bukti..." 
                                       value="${currentVal}">
                            </div>
                        `;
                        container.append(fileRow);
                    });
                }

                this.on("addedfile", function(file) {
                    renderNamingContainer();
                });

                this.on("removedfile", function(file) {
                    renderNamingContainer();
                });

                this.on("reset", function() {
                    renderNamingContainer();
                });

                submitButton.addEventListener("click", function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    if (myDropzone.getQueuedFiles().length > 0) {
                        Swal.fire({
                            title: 'Mengunggah Bukti Transaksi...',
                            text: 'Mohon tunggu hingga seluruh berkas terunggah',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading()
                            }
                        });
                        myDropzone.processQueue();
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Peringatan',
                            text: 'Pilih minimal satu file bukti transaksi untuk diunggah.'
                        });
                    }
                });

                this.on("sendingmultiple", function(data, xhr, formData) {
                    var container = $("#file_names_container");
                    myDropzone.files.forEach(function(file, index) {
                        var uuid = file.upload ? file.upload.uuid : index;
                        var input = container.find(`.proof-file-name-input[data-uuid="${uuid}"]`);
                        var customName = input.length ? input.val() : '';
                        formData.append(`names[${index}]`, customName);
                    });
                });

                this.on("successmultiple", function(files, response) {
                    Swal.close();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'File bukti transaksi berhasil diunggah',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                });

                this.on("errormultiple", function(files, response) {
                    Swal.close();
                    let errMsg = typeof response === 'string' ? response : (response.message || 'Gagal mengunggah file.');
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Upload',
                        text: errMsg
                    });
                });
            }
        });

        // Global Paste (CTRL+V) Event for Image Upload
        $(document).on('paste', function(e) {
            var clipboardData = (e.originalEvent || e).clipboardData;
            if (!clipboardData || !clipboardData.items) return;

            var items = clipboardData.items;
            var hasImage = false;

            for (var i = 0; i < items.length; i++) {
                if (items[i].type.indexOf("image") !== -1) {
                    var file = items[i].getAsFile();
                    if (file) {
                        var timeStamp = new Date().toISOString().replace(/[-:T.]/g, "").slice(0, 14);
                        var ext = file.type.split('/')[1] || 'png';
                        var newFileName = "Pasted_Proof_" + timeStamp + "." + ext;
                        
                        var renamedFile = new File([file], newFileName, { type: file.type });
                        myDropzone.addFile(renamedFile);
                        hasImage = true;
                    }
                }
            }

            if (hasImage) {
                var modalEl = document.getElementById('kt_modal_upload_proof');
                var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();

                Toastify({
                    text: "Gambar berhasil dipaste dari clipboard!",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-[#1E1E2D]",
                        borderRadius: "8px"
                    }
                }).showToast();
            }
        });

        // Live Search Input Filtering
        $('#floating_search_input').on('input', function() {
            let query = $(this).val().toLowerCase().trim();
            let cards = $('.proof-card');
            let totalCards = cards.length;
            let matchCount = 0;

            if (query.length > 0) {
                $('#clear_search_btn').removeClass('d-none');
            } else {
                $('#clear_search_btn').addClass('d-none');
            }

            cards.each(function() {
                let cardName = $(this).attr('data-name') || '';
                if (cardName.indexOf(query) !== -1) {
                    $(this).removeClass('d-none');
                    matchCount++;
                } else {
                    $(this).addClass('d-none');
                }
            });

            $('#search_count_badge').text(matchCount + ' / ' + totalCards + ' bukti');

            if (matchCount === 0 && totalCards > 0) {
                $('#no_search_results').removeClass('d-none');
            } else {
                $('#no_search_results').addClass('d-none');
            }
        });

        $('#clear_search_btn').on('click', function() {
            $('#floating_search_input').val('').trigger('input').focus();
        });

        // Handle Rename click
        $(document).on('click', '.btn-rename', function() {
            let button = $(this);
            let url = button.data('url');
            let currentName = button.data('name');
            let container = button.closest('.proof-card-item'); // container card
            let nameDisplay = container.find('.proof-name-display');

            Swal.fire({
                title: 'Ubah Nama Bukti',
                input: 'text',
                inputValue: currentName,
                inputPlaceholder: 'Masukkan nama bukti baru...',
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-light'
                },
                inputValidator: (value) => {
                    if (!value) {
                        return 'Nama tidak boleh kosong!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    let newName = result.value;
                    
                    Swal.fire({
                        title: 'Mohon tunggu...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: 'PATCH',
                            name: newName
                        },
                        dataType: 'json',
                        success: function(response) {
                            Swal.close();
                            if (response.success) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK',
                                    customClass: {
                                        confirmButton: 'btn btn-primary'
                                    }
                                });
                                
                                // Update dynamic values
                                nameDisplay.text(response.name).attr('title', response.name);
                                button.data('name', response.name);
                                button.closest('.proof-card').attr('data-name', response.name.toLowerCase());
                                
                                // Find or create the history button
                                let historyBtn = container.find('.btn-view-history');
                                if (historyBtn.length > 0) {
                                    historyBtn.data('name', response.name);
                                    historyBtn.data('history', response.rename_history);
                                } else {
                                    // Prepend history button if it was newly created
                                    let btnContainer = button.parent();
                                    let newHistoryBtn = `
                                        <button type="button" class="btn btn-icon btn-sm btn-light bg-white bg-opacity-75 btn-view-history rounded-circle" 
                                                title="Lihat Riwayat Nama" 
                                                data-name="${response.name}"
                                                data-history='${JSON.stringify(response.rename_history)}'>
                                            <i class="fa fa-history text-gray-700"></i>
                                        </button>
                                    `;
                                    btnContainer.prepend(newHistoryBtn);
                                }
                            } else {
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: response.message || 'Gagal mengubah nama',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.close();
                            let msg = "Terjadi kesalahan saat memproses data.";
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                title: 'Gagal!',
                                text: msg,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        });

        // Handle History click
        $(document).on('click', '.btn-view-history', function() {
            let button = $(this);
            let name = button.data('name');
            let history = button.data('history'); // Should be an array of objects
            
            if (typeof history === 'string') {
                history = JSON.parse(history);
            }

            let html = '<div class="table-responsive"><table class="table table-bordered table-striped fs-7 text-start align-middle">';
            html += '<thead><tr class="fw-bold text-gray-800 bg-light"><th>Nama Lama</th><th>Nama Baru</th><th>Pengubah</th><th>Tanggal</th></tr></thead><tbody>';
            
            history.forEach(function(item) {
                html += `<tr>
                    <td class="text-truncate" style="max-width: 120px;" title="${item.old_name}">${item.old_name}</td>
                    <td class="text-truncate" style="max-width: 120px;" title="${item.new_name}">${item.new_name}</td>
                    <td>${item.changed_by}</td>
                    <td class="text-nowrap">${item.changed_at}</td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';

            Swal.fire({
                title: 'Riwayat Nama: ' + name,
                html: html,
                icon: 'info',
                confirmButtonText: 'Tutup',
                customClass: {
                    confirmButton: 'btn btn-primary'
                },
                width: '600px'
            });
        });

        // Offcanvas Navigation & Instance
        let activeProofIds = [];
        let currentProofIndex = -1;
        let offcanvasEl = document.getElementById('kt_offcanvas_proof_detail');
        let proofOffcanvas = offcanvasEl ? bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl) : null;

        // Function to populate activeProofIds array
        function updateActiveProofIds() {
            activeProofIds = [];
            $('.proof-card:visible').each(function() {
                let id = parseInt($(this).data('id'));
                if (id) {
                    activeProofIds.push(id);
                }
            });
        }

        // Initialize active proof IDs
        updateActiveProofIds();

        // Re-evaluate whenever elements change or on page ready
        $(document).ajaxComplete(function() {
            updateActiveProofIds();
        });

        // Function to load proof details into Offcanvas
        function loadProofDetail(proofId) {
            let container = $('#offcanvas_proof_body');
            
            // Show loading spinner
            container.html(`
                <div class="d-flex justify-content-center align-items-center py-20">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);

            // Find current index
            currentProofIndex = activeProofIds.indexOf(proofId);

            // Enable/disable navigation buttons
            $('.btn-offcanvas-prev').prop('disabled', currentProofIndex <= 0);
            $('.btn-offcanvas-next').prop('disabled', currentProofIndex === -1 || currentProofIndex >= activeProofIds.length - 1);

            // Fetch detail content via AJAX
            $.ajax({
                url: `/console/transaction-proofs/${proofId}`,
                type: 'GET',
                dataType: 'html',
                success: function(html) {
                    container.html(html);
                    
                    // Re-init FSLightbox if available
                    if (typeof refreshFsLightbox === 'function') {
                        refreshFsLightbox();
                    }

                    // Update offcanvas title with current proof name
                    let proofName = container.find('.modal-proof-display-name').text() || 'Detail Bukti Transaksi';
                    $('#offcanvas_proof_title').text('Detail: ' + proofName).attr('title', proofName);
                },
                error: function(xhr) {
                    let msg = "Gagal memuat rincian bukti transaksi.";
                    if (xhr.status === 403) {
                        msg = "Anda tidak memiliki akses untuk bukti transaksi ini.";
                    }
                    container.html(`
                        <div class="alert alert-danger d-flex align-items-center p-5 m-5">
                            <i class="ki-duotone ki-information fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            <div class="d-flex flex-column">
                                <h4 class="mb-1 text-danger">Terjadi Kesalahan</h4>
                                <span>${msg}</span>
                            </div>
                        </div>
                    `);
                }
            });
        }

        // Handle eye/detail button click -> Open Offcanvas
        $(document).on('click', '.btn-view-detail', function() {
            let button = $(this);
            let proofId = parseInt(button.data('id'));
            
            // Update array before loading (in case cards were deleted/filtered)
            updateActiveProofIds();

            // Load details
            loadProofDetail(proofId);

            // Open Offcanvas drawer
            if (proofOffcanvas) {
                proofOffcanvas.show();
            }
        });

        // Handle prev button click in Offcanvas
        $('.btn-offcanvas-prev').on('click', function() {
            if (currentProofIndex > 0) {
                let prevId = activeProofIds[currentProofIndex - 1];
                loadProofDetail(prevId);
            }
        });

        // Handle next button click in Offcanvas
        $('.btn-offcanvas-next').on('click', function() {
            if (currentProofIndex !== -1 && currentProofIndex < activeProofIds.length - 1) {
                let nextId = activeProofIds[currentProofIndex + 1];
                loadProofDetail(nextId);
            }
        });

        // Keyboard arrow navigation for Offcanvas & Ctrl+F search shortcut
        $(document).on('keydown', function(e) {
            // Shortcut Ctrl+F / Cmd+F -> Focus Search Input
            if ((e.ctrlKey || e.metaKey) && (e.key === 'f' || e.key === 'F')) {
                e.preventDefault();
                let $searchInput = $('#floating_search_input');
                if ($searchInput.length) {
                    $searchInput.focus().select();
                }
                return;
            }

            // Only navigate if the offcanvas drawer is currently open/visible
            if ($('#kt_offcanvas_proof_detail').hasClass('show')) {
                if (e.which === 37) { // Left arrow key
                    if (currentProofIndex > 0) {
                        let prevId = activeProofIds[currentProofIndex - 1];
                        loadProofDetail(prevId);
                    }
                } else if (e.which === 39) { // Right arrow key
                    if (currentProofIndex !== -1 && currentProofIndex < activeProofIds.length - 1) {
                        let nextId = activeProofIds[currentProofIndex + 1];
                        loadProofDetail(nextId);
                    }
                }
            }
        });
    });
</script>
@endpush
