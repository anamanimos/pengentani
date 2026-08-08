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
                    @php
                        $ext = strtolower(pathinfo($proof->file_path, PATHINFO_EXTENSION));
                        $isPdf = in_array($ext, ['pdf']);
                        $dataType = $isPdf ? 'iframe' : 'image';
                    @endphp
                    <div class="proof-card" data-id="{{ $proof->id }}" data-name="{{ strtolower($proof->name) }}">
                        <div class="proof-card-item">
                            <a href="{{ $proof->url }}" class="proof-lightbox-link position-absolute top-0 start-0 w-100 h-100" data-type="{{ $dataType }}" style="z-index: 1;" title="Lihat Bukti"></a>
                            
                            @if($isPdf)
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
                                    <!-- Optimized Card Action Buttons (Max 3 icons) -->
                                    <div class="d-flex align-items-center gap-1 proof-overlay-btn" style="z-index: 3;">
                                        @if(!$isPdf)
                                            <button type="button" class="btn btn-icon btn-sm btn-light-warning bg-white bg-opacity-90 w-28px h-28px rounded-circle shadow-xs btn-edit-image" 
                                                    title="Edit / Coret Gambar" 
                                                    data-id="{{ $proof->id }}" 
                                                    data-name="{{ $proof->name }}"
                                                    data-url="{{ $proof->url }}"
                                                    data-proxy-url="{{ route('transaction-proofs.proxy-image', $proof->id) }}"
                                                    data-history="{{ json_encode($proof->image_history ?? []) }}"
                                                    data-save-url="{{ route('transaction-proofs.edit-image', $proof->id) }}"
                                                    data-revert-url="{{ route('transaction-proofs.revert-image', $proof->id) }}">
                                                <i class="ki-duotone ki-design-1 fs-4 text-warning"><span class="path1"></span><span class="path2"></span></i>
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-icon btn-sm btn-light-primary bg-white bg-opacity-90 w-28px h-28px rounded-circle shadow-xs btn-view-detail" 
                                                title="Detail Transaksi (Offcanvas)" 
                                                data-id="{{ $proof->id }}">
                                            <i class="ki-duotone ki-eye fs-4 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                        </button>

                                        <!-- Opsi Menu Dropdown -->
                                        <div class="dropdown d-inline-block">
                                            <button type="button" class="btn btn-icon btn-sm btn-light bg-white bg-opacity-90 w-28px h-28px rounded-circle shadow-xs" 
                                                    data-bs-toggle="dropdown" 
                                                    aria-expanded="false" 
                                                    title="Menu Opsi">
                                                <i class="ki-duotone ki-dots-square fs-4 text-gray-700"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-md border fs-7 py-2" style="z-index: 1050; min-width: 200px;">
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center gap-2 py-2 btn-rename" href="#" 
                                                       data-id="{{ $proof->id }}" 
                                                       data-name="{{ $proof->name }}"
                                                       data-url="{{ route('transaction-proofs.rename', $proof->id) }}">
                                                        <i class="ki-duotone ki-pencil fs-5 text-primary"><span class="path1"></span><span class="path2"></span></i>
                                                        <span>Ganti Nama</span>
                                                    </a>
                                                </li>
                                                @if(!empty($proof->image_history))
                                                    <li>
                                                        <a class="dropdown-item d-flex align-items-center gap-2 py-2 btn-view-image-history" href="#" 
                                                           data-id="{{ $proof->id }}" 
                                                           data-name="{{ $proof->name }}"
                                                           data-current-url="{{ $proof->url }}"
                                                           data-history="{{ json_encode($proof->image_history) }}"
                                                           data-revert-url="{{ route('transaction-proofs.revert-image', $proof->id) }}">
                                                            <i class="fa fa-layer-group text-info fs-7 me-1"></i>
                                                            <span>Riwayat Edit Gambar</span>
                                                            <span class="badge badge-light-info fs-9 ms-auto px-2 py-1">{{ count($proof->image_history) }}</span>
                                                        </a>
                                                    </li>
                                                @endif
                                                @if(!empty($proof->rename_history))
                                                    <li>
                                                        <a class="dropdown-item d-flex align-items-center gap-2 py-2 btn-view-history" href="#" 
                                                           data-name="{{ $proof->name }}"
                                                           data-history="{{ json_encode($proof->rename_history) }}">
                                                            <i class="fa fa-history text-gray-600 fs-7 me-1"></i>
                                                            <span>Riwayat Nama</span>
                                                        </a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
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
<!-- Modal Edit & Coret Gambar Bukti Transaksi -->
<div class="modal fade" id="kt_modal_edit_proof_image" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered mw-950px">
        <div class="modal-content rounded-3 shadow-lg">
            <div class="modal-header py-3 px-6 bg-light border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="ki-duotone ki-design-1 fs-1 text-warning me-2"><span class="path1"></span><span class="path2"></span></i>
                    <h3 class="fw-bold modal-title text-gray-800 mb-0">
                        Edit & Coret Gambar: <span id="editor_proof_name" class="text-primary fw-bolder"></span>
                    </h3>
                </div>
                <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary rounded-circle" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </button>
            </div>
            
            <div class="modal-body p-4 bg-body">
                <!-- Toolbar Editor Bar -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 p-3 mb-3 bg-light rounded border shadow-2xs">
                    <!-- Left: Mode Switcher -->
                    <div class="d-flex align-items-center gap-2">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-primary fw-bold active" id="editor_btn_draw" title="Coret / Garis Bebas">
                                <i class="ki-duotone ki-pencil fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Coret
                            </button>
                            <button type="button" class="btn btn-sm btn-outline btn-outline-primary fw-bold" id="editor_btn_text" title="Tambah Teks / Angka Koreksi">
                                <i class="ki-duotone ki-text fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Teks
                            </button>
                        </div>

                        <!-- Color Presets -->
                        <div class="d-flex align-items-center gap-1 ms-2 border-start ps-3">
                            <span class="fs-8 fw-semibold text-gray-600 me-1">Warna:</span>
                            <button type="button" class="btn btn-icon btn-xs rounded-circle editor-color-btn active" data-color="#ff0000" style="background-color: #ff0000; width: 22px; height: 22px; border: 2px solid white; box-shadow: 0 0 4px rgba(0,0,0,0.3);" title="Merah"></button>
                            <button type="button" class="btn btn-icon btn-xs rounded-circle editor-color-btn" data-color="#0066ff" style="background-color: #0066ff; width: 22px; height: 22px; border: 2px solid white;" title="Biru"></button>
                            <button type="button" class="btn btn-icon btn-xs rounded-circle editor-color-btn" data-color="#00a854" style="background-color: #00a854; width: 22px; height: 22px; border: 2px solid white;" title="Hijau"></button>
                            <button type="button" class="btn btn-icon btn-xs rounded-circle editor-color-btn" data-color="#ffcc00" style="background-color: #ffcc00; width: 22px; height: 22px; border: 2px solid white;" title="Kuning"></button>
                            <button type="button" class="btn btn-icon btn-xs rounded-circle editor-color-btn" data-color="#000000" style="background-color: #000000; width: 22px; height: 22px; border: 2px solid white;" title="Hitam"></button>
                            <button type="button" class="btn btn-icon btn-xs rounded-circle editor-color-btn" data-color="#ffffff" style="background-color: #ffffff; width: 22px; height: 22px; border: 2px solid #ccc;" title="Putih"></button>
                            <input type="color" id="editor_color_picker" value="#ff0000" class="form-control form-control-color form-control-xs rounded-circle ms-1 p-0" style="width: 24px; height: 24px; cursor: pointer;" title="Pilih Warna Custom">
                        </div>

                        <!-- Line / Font Size -->
                        <div class="d-flex align-items-center gap-1 ms-2 border-start ps-3">
                            <span class="fs-8 fw-semibold text-gray-600 me-1">Ukuran:</span>
                            <select id="editor_size_select" class="form-select form-select-sm form-select-solid py-1 px-2 fs-8 w-130px">
                                <option value="3" selected>Kuas: Tipis (3px)</option>
                                <option value="6">Kuas: Sedang (6px)</option>
                                <option value="12">Kuas: Tebal (12px)</option>
                                <option value="24">Kuas: X-Tebal (24px)</option>
                                <option value="font-18">Teks: Kecil (18px)</option>
                                <option value="font-28">Teks: Sedang (28px)</option>
                                <option value="font-40">Teks: Besar (40px)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Right: Text Input & Canvas Controls -->
                    <div class="d-flex align-items-center gap-2 ms-auto">
                        <!-- Text Input Wrapper -->
                        <div id="editor_text_input_wrapper" class="d-none me-2">
                            <input type="text" id="editor_text_input" class="form-control form-control-sm form-control-solid fs-7 w-200px" placeholder="Ketik teks & klik gambar..." autocomplete="off">
                        </div>

                        <button type="button" class="btn btn-xs btn-light-warning fw-bold px-3 py-2" id="editor_btn_undo" title="Urungkan Perubahan Terakhir">
                            <i class="fa fa-undo me-1"></i> Undo
                        </button>
                        <button type="button" class="btn btn-xs btn-light-danger fw-bold px-3 py-2" id="editor_btn_reset" title="Kembalikan Gambar Asli Canvas">
                            <i class="fa fa-refresh me-1"></i> Reset Canvas
                        </button>
                    </div>
                </div>

                <!-- Canvas Workbench Container -->
                <div class="d-flex justify-content-center align-items-center bg-dark bg-opacity-10 rounded border overflow-hidden p-2 position-relative" style="min-height: 420px; max-height: 580px;">
                    <div id="editor_loading_spinner" class="position-absolute top-50 start-50 translate-middle text-center">
                        <div class="spinner-border text-primary mb-2" role="status"></div>
                        <div class="fs-8 fw-semibold text-gray-600">Memuat berkas gambar...</div>
                    </div>
                    <canvas id="proof_annotation_canvas" class="rounded shadow-xs d-none" style="cursor: crosshair; max-width: 100%; max-height: 550px; object-fit: contain;"></canvas>
                </div>
            </div>

            <div class="modal-footer py-3 px-6 bg-light border-top d-flex align-items-center justify-content-between">
                <span class="fs-9 text-muted"><i class="ki-duotone ki-information fs-7 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Versi asli gambar sebelumnya akan tersimpan otomatis di riwayat.</span>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-light fw-bold rounded-pill px-5" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-sm btn-primary fw-bold rounded-pill px-7" id="editor_btn_save">
                        <i class="ki-duotone ki-check fs-3 me-1"><span class="path1"></span><span class="path2"></span></i> Simpan Perubahan (Versi Baru)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Riwayat Versi Edit Gambar -->
<div class="modal fade" id="kt_modal_image_history" tabindex="-1" aria-hidden="true" style="z-index: 1080;">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content rounded-3 shadow-lg">
            <div class="modal-header py-4 px-6 border-bottom">
                <h3 class="fw-bold modal-title text-gray-800 d-flex align-items-center">
                    <i class="fa fa-layer-group text-info me-2 fs-4"></i>
                    Riwayat Versi Gambar: <span id="history_proof_name" class="text-primary ms-1"></span>
                </h3>
                <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary rounded-circle" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </button>
            </div>
            <div class="modal-body p-6 scroll-y" style="max-height: 480px;">
                <div id="image_history_list" class="d-flex flex-column gap-4">
                    <!-- History version items injected via JS -->
                </div>
            </div>
            <div class="modal-footer py-3 px-6 bg-light border-top flex-center">
                <button type="button" class="btn btn-sm btn-secondary fw-bold rounded-pill px-6" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
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

        // Dynamic Lightbox launcher for visible search result cards
        $(document).on('click', '.proof-lightbox-link', function(e) {
            e.preventDefault();
            e.stopPropagation();

            let clickedEl = this;
            let $visibleCards = $('.proof-card:not(.d-none)');
            let $visibleLinks = $visibleCards.find('.proof-lightbox-link');

            if ($visibleLinks.length === 0) return;

            let visibleUrls = [];
            let visibleTypes = [];

            $visibleLinks.each(function() {
                let url = $(this).attr('href');
                let type = $(this).attr('data-type') || 'image';

                if (!/^https?:\/\//i.test(url) && !url.startsWith('/')) {
                    url = 'https://' + url;
                }

                visibleUrls.push(url);
                visibleTypes.push(type);
            });

            let clickedIndex = $visibleLinks.index(clickedEl);
            if (clickedIndex < 0) clickedIndex = 0;

            var lightbox = new FsLightbox();
            lightbox.props.sources = visibleUrls;
            lightbox.props.types = visibleTypes;

            let activeStageIdx = clickedIndex;

            lightbox.props.onOpen = function(instance) {
                setTimeout(function() {
                    let idx = (instance && typeof instance.stageIndex !== 'undefined') ? instance.stageIndex : activeStageIdx;
                    updateFsLightboxCustomTools(lightbox, $visibleCards, idx);
                }, 100);
            };

            lightbox.props.onSlideChange = function(instance) {
                let idx = (instance && typeof instance.stageIndex !== 'undefined') ? instance.stageIndex : activeStageIdx;
                updateFsLightboxCustomTools(lightbox, $visibleCards, idx);
            };

            lightbox.props.onClose = function() {
                $('#fslightbox_custom_toolbar').remove();
            };

            lightbox.open(clickedIndex);
        });

        // Floating Action Toolbar inside Full-Screen Lightbox Preview
        function updateFsLightboxCustomTools(lightbox, $visibleCards, stageIndex) {
            let $container = $('.fslightbox-container');
            if ($container.length === 0) return;

            let $card = $visibleCards.eq(stageIndex);
            if ($card.length === 0) return;

            let $editBtn = $card.find('.btn-edit-image');
            let isImage = $editBtn.length > 0;

            if ($('#fslightbox_custom_toolbar').length === 0) {
                let html = `
                    <div id="fslightbox_custom_toolbar" class="position-fixed bottom-0 start-50 translate-middle-x mb-6 p-2 px-4 bg-dark bg-opacity-75 rounded-pill shadow-lg border border-gray-700 d-flex align-items-center gap-3" style="z-index: 100000000; backdrop-filter: blur(8px);">
                        <button type="button" class="btn btn-sm btn-warning fw-bold rounded-pill px-4 py-2" id="fslightbox_action_edit" style="display: ${isImage ? 'inline-block' : 'none'};">
                            <i class="ki-duotone ki-design-1 fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Edit / Coret Gambar
                        </button>
                        <button type="button" class="btn btn-sm btn-light fw-bold rounded-pill px-4 py-2" id="fslightbox_action_detail">
                            <i class="ki-duotone ki-eye fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Detail Transaksi
                        </button>
                    </div>
                `;
                $container.append(html);

                // Action Edit Click Handler
                $(document).off('click', '#fslightbox_action_edit').on('click', '#fslightbox_action_edit', function(evt) {
                    evt.preventDefault();
                    let currentCardIdx = $('#fslightbox_custom_toolbar').data('card-index') || 0;
                    let $targetCard = $visibleCards.eq(currentCardIdx);
                    let $targetEditBtn = $targetCard.find('.btn-edit-image');

                    if ($targetEditBtn.length) {
                        lightbox.close();
                        setTimeout(function() {
                            $targetEditBtn.trigger('click');
                        }, 250);
                    }
                });

                // Action Detail Click Handler
                $(document).off('click', '#fslightbox_action_detail').on('click', '#fslightbox_action_detail', function(evt) {
                    evt.preventDefault();
                    let currentCardIdx = $('#fslightbox_custom_toolbar').data('card-index') || 0;
                    let $targetCard = $visibleCards.eq(currentCardIdx);
                    let $targetDetailBtn = $targetCard.find('.btn-view-detail');

                    if ($targetDetailBtn.length) {
                        lightbox.close();
                        setTimeout(function() {
                            $targetDetailBtn.trigger('click');
                        }, 250);
                    }
                });
            }

            // Update state & visibility for current slide index
            $('#fslightbox_custom_toolbar').data('card-index', stageIndex);
            if (isImage) {
                $('#fslightbox_action_edit').show();
            } else {
                $('#fslightbox_action_edit').hide();
            }
        }

        // ==========================================
        // CANVAS IMAGE ANNOTATION & EDITING SYSTEM
        // ==========================================
        let canvas = document.getElementById('proof_annotation_canvas');
        let ctx = canvas ? canvas.getContext('2d') : null;
        let canvasImg = new Image();
        let undoStack = [];
        let isDrawing = false;
        let lastPos = { x: 0, y: 0 };
        let currentMode = 'draw'; // 'draw' or 'text'
        let currentColor = '#ff0000';
        let currentLineWidth = 3;
        let currentFontSize = 28;
        let activeEditorSaveUrl = '';
        let activeEditorProofId = null;

        // Open Image Editor Modal
        $(document).on('click', '.btn-edit-image', function(e) {
            e.preventDefault();
            e.stopPropagation();

            let proofId = $(this).attr('data-id');
            let proofName = $(this).attr('data-name');
            let proofUrl = $(this).attr('data-url');
            let proxyUrl = $(this).attr('data-proxy-url') || proofUrl;
            activeEditorSaveUrl = $(this).attr('data-save-url');
            activeEditorProofId = proofId;

            $('#editor_proof_name').text(proofName);
            $('#editor_loading_spinner').removeClass('d-none');
            $(canvas).addClass('d-none');

            // Reset tools UI
            setEditorMode('draw');
            setEditorColor('#ff0000');
            $('#editor_size_select').val('3');
            currentLineWidth = 3;
            currentFontSize = 28;
            $('#editor_text_input').val('');
            undoStack = [];

            let modalEl = document.getElementById('kt_modal_edit_proof_image');
            let modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();

            // Load Image onto Canvas via same-origin Proxy Endpoint to prevent CORS errors
            canvasImg = new Image();
            canvasImg.crossOrigin = 'anonymous';
            canvasImg.onload = function() {
                canvas.width = canvasImg.naturalWidth;
                canvas.height = canvasImg.naturalHeight;
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(canvasImg, 0, 0);

                $('#editor_loading_spinner').addClass('d-none');
                $(canvas).removeClass('d-none');

                pushUndoState();
            };
            canvasImg.onerror = function() {
                // If proxy fails, attempt direct URL fallback
                if (canvasImg.src !== proofUrl && proofUrl) {
                    canvasImg.removeAttribute('crossOrigin');
                    canvasImg.src = proofUrl;
                    return;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memuat Gambar',
                    text: 'Tidak dapat memuat berkas gambar untuk diedit.'
                });
                modal.hide();
            };
            canvasImg.src = proxyUrl;
        });

        // Mode Switcher Buttons
        $('#editor_btn_draw').click(function() { setEditorMode('draw'); });
        $('#editor_btn_text').click(function() { setEditorMode('text'); });

        function setEditorMode(mode) {
            currentMode = mode;
            if (mode === 'draw') {
                $('#editor_btn_draw').removeClass('btn-outline btn-outline-primary').addClass('btn-primary active');
                $('#editor_btn_text').removeClass('btn-primary active').addClass('btn-outline btn-outline-primary');
                $('#editor_text_input_wrapper').addClass('d-none');
                if (canvas) canvas.style.cursor = 'crosshair';
            } else {
                $('#editor_btn_text').removeClass('btn-outline btn-outline-primary').addClass('btn-primary active');
                $('#editor_btn_draw').removeClass('btn-primary active').addClass('btn-outline btn-outline-primary');
                $('#editor_text_input_wrapper').removeClass('d-none');
                $('#editor_text_input').focus();
                if (canvas) canvas.style.cursor = 'text';
            }
        }

        // Color Selection
        $(document).on('click', '.editor-color-btn', function() {
            let color = $(this).attr('data-color');
            setEditorColor(color);
            $('#editor_color_picker').val(color);
        });

        $('#editor_color_picker').on('change input', function() {
            setEditorColor($(this).val());
        });

        function setEditorColor(color) {
            currentColor = color;
            $('.editor-color-btn').removeClass('active').css('box-shadow', 'none');
            $(`.editor-color-btn[data-color="${color}"]`).addClass('active').css('box-shadow', '0 0 4px rgba(0,0,0,0.5)');
        }

        // Size Selection (Line width or font size)
        $('#editor_size_select').on('change', function() {
            let val = $(this).val();
            if (val.startsWith('font-')) {
                currentFontSize = parseInt(val.replace('font-', ''));
                if (currentMode === 'draw') setEditorMode('text');
            } else {
                currentLineWidth = parseInt(val);
                if (currentMode === 'text') setEditorMode('draw');
            }
        });

        // Get Event Coordinates scaled to actual Canvas Resolution
        function getCanvasCoords(e) {
            let rect = canvas.getBoundingClientRect();
            let clientX = e.clientX;
            let clientY = e.clientY;

            if (e.touches && e.touches.length > 0) {
                clientX = e.touches[0].clientX;
                clientY = e.touches[0].clientY;
            }

            return {
                x: (clientX - rect.left) * (canvas.width / rect.width),
                y: (clientY - rect.top) * (canvas.height / rect.height)
            };
        }

        // Drawing Event Handlers
        if (canvas) {
            $(canvas).on('mousedown touchstart', function(e) {
                if (currentMode === 'draw') {
                    isDrawing = true;
                    lastPos = getCanvasCoords(e);
                } else if (currentMode === 'text') {
                    let pos = getCanvasCoords(e);
                    let text = $('#editor_text_input').val().trim();
                    if (!text) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Ketik Teks Dulu',
                            text: 'Silakan isi kolom teks di toolbar terlebih dahulu, lalu klik pada lokasi gambar yang ingin ditambahi teks.',
                            confirmButtonText: 'Mengerti'
                        });
                        $('#editor_text_input').focus();
                        return;
                    }

                    ctx.font = 'bold ' + currentFontSize + 'px sans-serif';
                    ctx.fillStyle = currentColor;
                    ctx.textBaseline = 'middle';
                    ctx.fillText(text, pos.x, pos.y);

                    pushUndoState();
                }
            });

            $(canvas).on('mousemove touchmove', function(e) {
                if (!isDrawing || currentMode !== 'draw') return;
                e.preventDefault();

                let pos = getCanvasCoords(e);
                ctx.beginPath();
                ctx.moveTo(lastPos.x, lastPos.y);
                ctx.lineTo(pos.x, pos.y);
                ctx.strokeStyle = currentColor;
                ctx.lineWidth = currentLineWidth;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.stroke();

                lastPos = pos;
            });

            $(canvas).on('mouseup touchend mouseleave', function() {
                if (isDrawing) {
                    isDrawing = false;
                    pushUndoState();
                }
            });
        }

        // Undo & Reset Functions
        function pushUndoState() {
            if (!canvas) return;
            if (undoStack.length > 20) undoStack.shift();
            undoStack.push(canvas.toDataURL());
        }

        $('#editor_btn_undo').click(function() {
            if (undoStack.length > 1) {
                undoStack.pop(); // Remove current
                let prevState = undoStack[undoStack.length - 1];
                let img = new Image();
                img.onload = function() {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(img, 0, 0);
                };
                img.src = prevState;
            }
        });

        $('#editor_btn_reset').click(function() {
            if (canvasImg.src) {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(canvasImg, 0, 0);
                undoStack = [];
                pushUndoState();
            }
        });

        // Save Edited Image
        $('#editor_btn_save').click(function() {
            let $btn = $(this);
            if (!canvas) return;

            // Use JPEG with 92% quality to compress payload size from ~8MB to ~350KB for fast uploads
            let base64Image = canvas.toDataURL('image/jpeg', 0.92);

            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');

            $.ajax({
                url: activeEditorSaveUrl,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    image: base64Image
                },
                timeout: 30000,
                success: function(res) {
                    $btn.prop('disabled', false).html('<i class="ki-duotone ki-check fs-3 me-1"><span class="path1"></span><span class="path2"></span></i> Simpan Perubahan (Versi Baru)');
                    if (res.success) {
                        let modalEl = document.getElementById('kt_modal_edit_proof_image');
                        if (modalEl) bootstrap.Modal.getInstance(modalEl).hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Disimpan!',
                            text: res.message || 'Gambar berhasil diperbarui. Versi sebelumnya telah tersimpan di riwayat.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Terjadi kesalahan saat menyimpan gambar.' });
                    }
                },
                error: function(xhr, status, error) {
                    $btn.prop('disabled', false).html('<i class="ki-duotone ki-check fs-3 me-1"><span class="path1"></span><span class="path2"></span></i> Simpan Perubahan (Versi Baru)');
                    let msg = 'Gagal menyimpan gambar.';
                    if (status === 'timeout') {
                        msg = 'Proses menyimpan melebihi batas waktu (timeout). Silakan coba lagi.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                }
            });
        });

        // ==========================================
        // IMAGE VERSION HISTORY & REVERT SYSTEM
        // ==========================================
        $(document).on('click', '.btn-view-image-history', function(e) {
            e.preventDefault();
            e.stopPropagation();

            let name = $(this).attr('data-name');
            let historyJson = $(this).attr('data-history');
            let revertUrl = $(this).attr('data-revert-url');
            let history = [];

            try {
                history = JSON.parse(historyJson);
            } catch (err) {
                history = [];
            }

            $('#history_proof_name').text(name);

            let html = '';
            if (!history || history.length === 0) {
                html = '<div class="text-center text-muted py-6">Belum ada riwayat edit gambar.</div>';
            } else {
                for (let i = history.length - 1; i >= 0; i--) {
                    let v = history[i];
                    let verNum = v.version || (i + 1);
                    html += `
                        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded border">
                            <div class="d-flex align-items-center gap-3">
                                <a href="${v.url}" target="_blank" title="Lihat Gambar Full">
                                    <img src="${v.url}" class="rounded border shadow-2xs" style="width: 50px; height: 50px; object-fit: cover;" />
                                </a>
                                <div>
                                    <div class="fw-bold text-gray-800 fs-7">Versi ${verNum}</div>
                                    <div class="fs-9 text-muted">${v.edited_at || ''} (${v.edited_by || 'User'})</div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-light-primary rounded-pill px-4 btn-revert-image" 
                                    data-revert-url="${revertUrl}" 
                                    data-version-index="${i}">
                                <i class="fa fa-undo me-1"></i> Pulihkan
                            </button>
                        </div>
                    `;
                }
            }

            $('#image_history_list').html(html);
            let modalEl = document.getElementById('kt_modal_image_history');
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });

        // Revert Image Version Handler
        $(document).on('click', '.btn-revert-image', function(e) {
            e.preventDefault();
            let revertUrl = $(this).attr('data-revert-url');
            let versionIndex = $(this).attr('data-version-index');
            let $btn = $(this);

            Swal.fire({
                title: 'Kembalikan Gambar?',
                text: 'Gambar aktif akan diganti dengan versi yang dipilih. Gambar saat ini akan disimpan ke riwayat.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Pulihkan',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Memproses...');

                    $.ajax({
                        url: revertUrl,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            version_index: versionIndex
                        },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil Dipulihkan!',
                                    text: res.message || 'Gambar berhasil dikembalikan ke versi sebelumnya.',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Gagal memulihkan versi gambar.' });
                            }
                        },
                        error: function() {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan sistem saat memulihkan versi.' });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
