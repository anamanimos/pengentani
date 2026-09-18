@extends('layouts.metronic')

@section('title', 'Import Pekerjaan dari Catatan Lapangan')

@section('page_title')
    <div class="d-flex align-items-center">
        <span>Import Catatan Lapangan Buruh Tani</span>
        <span class="badge badge-light-primary fw-bold fs-8 ms-3">AI Vision Reader</span>
    </div>
@endsection

@section('page_actions')
    <a href="{{ route('worker-jobs.import.index') }}" class="btn btn-secondary btn-sm me-2">
        <i class="ki-duotone ki-document fs-3 me-1"><span class="path1"></span><span class="path2"></span></i> Riwayat Import
    </a>
    <a href="{{ route('worker-jobs.index') }}" class="btn btn-light-primary btn-sm">
        <i class="ki-duotone ki-arrow-left fs-3 me-1"><span class="path1"></span><span class="path2"></span></i> Kembali
    </a>
@endsection

@section('content')
<div class="app-content flex-column-fluid pb-15">
    <div class="app-container container-fluid">
        @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center p-5 mb-5 rounded-3 shadow-xs">
            <i class="ki-duotone ki-cross-circle fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span></i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-danger">Gagal Memproses</h4>
                <span>{{ session('error') }}</span>
            </div>
        </div>
        @endif

        @if(!$isGeminiConfigured)
        <div class="alert alert-warning d-flex align-items-center p-5 mb-5 rounded-3 shadow-xs border border-warning">
            <i class="ki-duotone ki-information-5 fs-2hx text-warning me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
            <div class="d-flex flex-column flex-grow-1">
                <h4 class="mb-1 text-warning fw-bold">Gemini API Key Belum Dikonfigurasi</h4>
                <span class="text-gray-700 fs-7">Untuk menggunakan fitur scan foto otomatis langsung dari aplikasi, silakan masukkan API Key Gemini Anda di menu Pengaturan. Anda tetap bisa menggunakan tab <strong>Tempel JSON Manual</strong> di bawah ini.</span>
            </div>
            <a href="{{ route('settings.general.index') }}" class="btn btn-warning btn-sm fw-bold ms-4 text-nowrap">
                <i class="ki-duotone ki-setting-2 fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Atur API Key
            </a>
        </div>
        @endif

        <!-- Card Container -->
        <div class="card card-flush shadow-sm">
            <div class="card-header border-0 pt-5">
                <div class="card-title">
                    <h3 class="fw-bold text-gray-800 fs-4 mb-0">Pilih Metode Input Data</h3>
                </div>
            </div>
            <div class="card-body pt-0">
                <!-- Nav Tabs -->
                <ul class="nav nav-tabs nav-line-tabs nav-stretch fs-6 border-0 fw-bold mb-6">
                    <li class="nav-item">
                        <a class="nav-link active text-active-primary py-3 px-4 d-flex align-items-center" data-bs-toggle="tab" href="#tab_photo">
                            <i class="ki-duotone ki-picture fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>
                            <span>Jalur A: Upload Foto Catatan (Otomatis AI)</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-active-primary py-3 px-4 d-flex align-items-center" data-bs-toggle="tab" href="#tab_json">
                            <i class="ki-duotone ki-code fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>
                            <span>Jalur B: Tempel Format JSON (Manual AI Chat)</span>
                        </a>
                    </li>
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content" id="myTabContent">
                    <!-- Tab 1: Upload Photo -->
                    <div class="tab-pane fade show active" id="tab_photo" role="tabpanel">
                        <form id="form-upload-photo" action="{{ route('worker-jobs.import.process-photo') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-6">
                                <div class="col-lg-7">
                                    <div class="border border-dashed border-primary border-opacity-50 rounded-4 p-8 text-center bg-light-primary bg-opacity-25 h-100 d-flex flex-column justify-content-center align-items-center" id="drop-zone" style="min-height: 280px; cursor: pointer;">
                                        <i class="ki-duotone ki-cloud-upload fs-4hx text-primary mb-3"><span class="path1"></span><span class="path2"></span></i>
                                        <h4 class="text-gray-800 fw-bold mb-2">Pilih atau Seret Foto Buku Catatan ke Sini</h4>
                                        <p class="text-muted fs-7 mb-4">Format yang didukung: JPG, PNG, WEBP (Maksimal 20MB)</p>
                                        
                                        <input type="file" name="photo" id="photo-input" class="d-none" accept="image/jpeg,image/png,image/webp,image/jpg" required>
                                        <button type="button" class="btn btn-primary btn-sm px-6 rounded-pill" onclick="document.getElementById('photo-input').click()">
                                            <i class="ki-duotone ki-folder-up fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Pilih Foto dari Perangkat
                                        </button>
                                        <div id="file-name-display" class="mt-3 text-primary fw-bold fs-7 d-none"></div>
                                    </div>
                                </div>

                                <div class="col-lg-5">
                                    <!-- Preview Image Card -->
                                    <div class="card border border-gray-300 shadow-none h-100">
                                        <div class="card-header border-0 py-3">
                                            <h5 class="card-title fs-7 fw-bold text-gray-700">Preview Foto</h5>
                                        </div>
                                        <div class="card-body p-4 d-flex align-items-center justify-content-center bg-light rounded-bottom" style="min-height: 220px;">
                                            <img id="image-preview" src="#" alt="Preview" class="img-fluid rounded shadow-sm d-none" style="max-height: 240px; object-fit: contain;">
                                            <div id="preview-placeholder" class="text-center text-muted fs-8">
                                                <i class="ki-duotone ki-file-sheet fs-3x text-gray-400 mb-2"><span class="path1"></span><span class="path2"></span></i>
                                                <div>Belum ada foto yang dipilih</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pilihan Model AI Vision -->
                            <div class="row mt-4">
                                <div class="col-md-6 col-lg-5">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label class="form-label fw-bold text-gray-700 fs-7 mb-0">Model Gemini Vision AI:</label>
                                        <button type="button" id="btn-import-fetch-models" class="btn btn-link btn-color-primary btn-active-color-primary p-0 fs-9 fw-bold" title="Ambil daftar model yang aktif di akun Anda">
                                            <i class="ki-duotone ki-arrows-circle fs-8 me-1"><span class="path1"></span><span class="path2"></span></i> Muat dari Akun
                                        </button>
                                    </div>
                                    <select name="model" id="import_gemini_model" class="form-select form-select-solid fs-7">
                                        <option value="gemini-3.6-flash" {{ ($configuredModel ?? 'gemini-3.6-flash') == 'gemini-3.6-flash' ? 'selected' : '' }}>gemini-3.6-flash (Terbaru & Direkomendasikan)</option>
                                        <option value="gemini-2.5-flash" {{ ($configuredModel ?? '') == 'gemini-2.5-flash' ? 'selected' : '' }}>gemini-2.5-flash</option>
                                        <option value="gemini-2.5-pro" {{ ($configuredModel ?? '') == 'gemini-2.5-pro' ? 'selected' : '' }}>gemini-2.5-pro</option>
                                        <option value="gemini-1.5-flash" {{ ($configuredModel ?? '') == 'gemini-1.5-flash' ? 'selected' : '' }}>gemini-1.5-flash</option>
                                        <option value="gemini-1.5-pro" {{ ($configuredModel ?? '') == 'gemini-1.5-pro' ? 'selected' : '' }}>gemini-1.5-pro</option>
                                    </select>
                                    <div class="form-text fs-9 text-gray-500">Pilih model yang akan memproses foto ini. Default: <code>gemini-3.6-flash</code>.</div>
                                </div>
                            </div>

                            <div class="separator separator-dashed my-6"></div>

                            <div class="d-flex align-items-center justify-content-between">
                                <div class="text-muted fs-8">
                                    <i class="ki-duotone ki-shield-tick text-success fs-5 me-1"><span class="path1"></span><span class="path2"></span></i>
                                    Data tidak langsung disimpan ke database. Anda akan memeriksa hasilnya terlebih dahulu pada tahap Review.
                                </div>
                                <button type="submit" id="btn-submit-photo" class="btn btn-primary fw-bold px-6" {{ !$isGeminiConfigured ? 'disabled' : '' }}>
                                    <span class="indicator-label">
                                        <i class="ki-duotone ki-magic-wand fs-3 me-1"><span class="path1"></span><span class="path2"></span></i> Analisis dengan Gemini AI
                                    </span>
                                    <span class="indicator-progress d-none">
                                        <span class="spinner-border spinner-border-sm align-middle me-2"></span> Membaca Tulisan Tangan...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab 2: Paste JSON Manual -->
                    <div class="tab-pane fade" id="tab_json" role="tabpanel">
                        <form id="form-upload-json" action="{{ route('worker-jobs.import.process-json') }}" method="POST">
                            @csrf
                            <div class="row g-6">
                                <div class="col-lg-8">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label fw-bold text-gray-700 fs-7 mb-0 required">Tempel Hasil Teks JSON dari AI Eksternal:</label>
                                        <button type="button" class="btn btn-light-primary btn-sm py-1 px-3 fs-8" id="btn-copy-prompt">
                                            <i class="ki-duotone ki-copy fs-4 me-1"></i> Salin Format Prompt AI
                                        </button>
                                    </div>
                                    <textarea name="raw_json" id="raw_json" class="form-control form-control-solid font-monospace fs-8" rows="12" placeholder='{"detected_worker_name": "Rina", "rows": [{"date": "2026-08-21", "raw_job_name": "Ngocor air", "wage": 35000, ...}]}' required></textarea>
                                    <div class="form-text fs-9 text-gray-500 mt-2">
                                        Jika Anda ingin menggunakan ChatGPT, Claude, atau Gemini Web secara manual: unggah foto Anda ke chat AI tersebut dengan menempelkan prompt di sebelah kanan, lalu salin JSON jawabannya ke sini.
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="card bg-light border-0 shadow-none h-100">
                                        <div class="card-header border-0 py-3">
                                            <h5 class="card-title fs-7 fw-bold text-gray-800">
                                                <i class="ki-duotone ki-information-5 fs-4 text-primary me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Panduan Prompt Eksternal
                                            </h5>
                                        </div>
                                        <div class="card-body p-4 pt-0 fs-8 text-gray-700">
                                            <p class="mb-2">Gunakan jalur ini jika:</p>
                                            <ul class="ps-4 mb-4">
                                                <li>Gemini API belum terhubung / limit kuota habis.</li>
                                                <li>Ingin memproses menggunakan ChatGPT Plus / Claude 3.5 Sonnet secara mandiri.</li>
                                            </ul>
                                            <p class="mb-2"><strong>Cara kerja:</strong></p>
                                            <ol class="ps-4 mb-0">
                                                <li>Klik tombol <strong>"Salin Format Prompt AI"</strong> di samping.</li>
                                                <li>Buka chat AI favorit Anda, lampirkan foto catatan dan tempel prompt.</li>
                                                <li>Salin balasan JSON dari AI, tempel ke kotak teks di samping, lalu klik tombol Proses.</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="separator separator-dashed my-6"></div>

                            <div class="d-flex align-items-center justify-content-end">
                                <button type="submit" id="btn-submit-json" class="btn btn-primary fw-bold px-6">
                                    <span class="indicator-label">
                                        <i class="ki-duotone ki-check-circle fs-3 me-1"><span class="path1"></span><span class="path2"></span></i> Proses Data JSON
                                    </span>
                                    <span class="indicator-progress d-none">
                                        <span class="spinner-border spinner-border-sm align-middle me-2"></span> Memvalidasi JSON...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden prompt template for copy -->
<div id="hidden-prompt-template" class="d-none">{{ $promptTemplate }}</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Photo preview & drag drop
        const photoInput = document.getElementById('photo-input');
        const dropZone = document.getElementById('drop-zone');
        const imgPreview = document.getElementById('image-preview');
        const placeholder = document.getElementById('preview-placeholder');
        const fileNameDisplay = document.getElementById('file-name-display');
        const formPhoto = document.getElementById('form-upload-photo');
        const btnPhoto = document.getElementById('btn-submit-photo');

        function showPreview(file) {
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imgPreview.src = e.target.result;
                    imgPreview.classList.remove('d-none');
                    placeholder.classList.add('d-none');
                    fileNameDisplay.textContent = 'File terpilih: ' + file.name + ' (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB)';
                    fileNameDisplay.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
        }

        if (photoInput) {
            photoInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    showPreview(this.files[0]);
                }
            });
        }

        if (dropZone) {
            dropZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                dropZone.classList.add('bg-light-primary');
            });
            dropZone.addEventListener('dragleave', function() {
                dropZone.classList.remove('bg-light-primary');
            });
            dropZone.addEventListener('drop', function(e) {
                e.preventDefault();
                dropZone.classList.remove('bg-light-primary');
                if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                    photoInput.files = e.dataTransfer.files;
                    showPreview(e.dataTransfer.files[0]);
                }
            });
        }

        if (formPhoto && btnPhoto) {
            formPhoto.addEventListener('submit', function(e) {
                // Ensure photo is selected
                if (!photoInput.files || photoInput.files.length === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Foto Belum Dipilih',
                        text: 'Silakan pilih atau seret foto buku catatan terlebih dahulu.',
                        customClass: { confirmButton: 'btn btn-primary' }
                    });
                    return false;
                }

                const label = btnPhoto.querySelector('.indicator-label');
                const progress = btnPhoto.querySelector('.indicator-progress');
                if (label) label.classList.add('d-none');
                if (progress) progress.classList.remove('d-none');
                btnPhoto.disabled = true;

                // Show full loading modal
                Swal.fire({
                    title: 'Menganalisis Foto Catatan...',
                    html: `
                        <div class="py-4 text-center">
                            <div class="spinner-border text-primary mb-4" style="width: 3.5rem; height: 3.5rem;" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="text-gray-800 fw-bold fs-5 mb-2">Gemini AI sedang membaca tulisan tangan</div>
                            <div class="text-muted fs-7 mb-3">Mengekstrak tanggal, nama pekerja, shift kerja, dan nominal upah...</div>
                            <div class="badge badge-light-primary fs-8 py-2 px-4 rounded-pill">Mohon tunggu beberapa saat (5 - 15 detik)</div>
                        </div>
                    `,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false
                });
            });
        }

        // Fetch models from account on Import page
        const btnImportFetch = document.getElementById('btn-import-fetch-models');
        const importModelSelect = document.getElementById('import_gemini_model');
        if (btnImportFetch && importModelSelect) {
            btnImportFetch.addEventListener('click', function() {
                btnImportFetch.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memuat...';
                btnImportFetch.disabled = true;

                $.ajax({
                    url: '{{ route("settings.general.gemini-models") }}',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        if (res.success && res.models && res.models.length > 0) {
                            const currentVal = importModelSelect.value;
                            importModelSelect.innerHTML = '';
                            res.models.forEach(function(m) {
                                const opt = document.createElement('option');
                                opt.value = m.id;
                                opt.textContent = m.name ? `${m.name} (${m.id})` : m.id;
                                if (m.id === currentVal || m.id === 'gemini-3.6-flash') {
                                    opt.selected = true;
                                }
                                importModelSelect.appendChild(opt);
                            });
                            Swal.fire({
                                icon: 'success',
                                title: 'Model Berhasil Dimuat!',
                                text: `Ditemukan ${res.models.length} model aktif dari akun Anda.`,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Memuat Model',
                                text: res.message || 'Tidak ada model yang ditemukan.',
                                customClass: { confirmButton: 'btn btn-primary' }
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Kesalahan Server',
                            text: xhr.responseJSON?.message || 'Gagal menghubungi server.',
                            customClass: { confirmButton: 'btn btn-primary' }
                        });
                    },
                    complete: function() {
                        btnImportFetch.innerHTML = '<i class="ki-duotone ki-arrows-circle fs-8 me-1"><span class="path1"></span><span class="path2"></span></i> Muat dari Akun';
                        btnImportFetch.disabled = false;
                    }
                });
            });
        }

        // Copy prompt template
        const copyBtn = document.getElementById('btn-copy-prompt');
        if (copyBtn) {
            copyBtn.addEventListener('click', function() {
                const promptText = document.getElementById('hidden-prompt-template').textContent;
                navigator.clipboard.writeText(promptText).then(function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Prompt Berhasil Disalin!',
                        text: 'Silakan tempel prompt ini bersama foto catatan ke ChatGPT / Claude / Gemini Web.',
                        timer: 2500,
                        showConfirmButton: false
                    });
                }).catch(function() {
                    // Fallback
                    const dummy = document.createElement('textarea');
                    document.body.appendChild(dummy);
                    dummy.value = promptText;
                    dummy.select();
                    document.execCommand('copy');
                    document.body.removeChild(dummy);
                    Swal.fire({
                        icon: 'success',
                        title: 'Prompt Berhasil Disalin!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
            });
        }

        // Form JSON submit indicator
        const formJson = document.getElementById('form-upload-json');
        const btnJson = document.getElementById('btn-submit-json');
        if (formJson && btnJson) {
            formJson.addEventListener('submit', function(e) {
                const rawJson = document.getElementById('raw_json').value.trim();
                if (!rawJson) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Teks JSON Kosong',
                        text: 'Silakan tempel teks JSON hasil ekstraksi dari AI terlebih dahulu.',
                        customClass: { confirmButton: 'btn btn-primary' }
                    });
                    return false;
                }

                const label = btnJson.querySelector('.indicator-label');
                const progress = btnJson.querySelector('.indicator-progress');
                if (label) label.classList.add('d-none');
                if (progress) progress.classList.remove('d-none');
                btnJson.disabled = true;

                Swal.fire({
                    title: 'Memvalidasi Data JSON...',
                    html: `
                        <div class="py-4 text-center">
                            <div class="spinner-border text-primary mb-4" style="width: 3.5rem; height: 3.5rem;" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="text-gray-800 fw-bold fs-5 mb-2">Memproses Format Data JSON</div>
                            <div class="text-muted fs-7">Mencocokkan lahan, pekerja, dan kategori ke database...</div>
                        </div>
                    `,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false
                });
            });
        }
    });
</script>
@endpush
