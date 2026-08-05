@extends('layouts.metronic')

@section('title', 'Monitoring & Pengaturan Cloudflare R2 Storage')

@section('page_title')
    Pengaturan Sistem <span class="text-gray-500 fw-semibold fs-7 ms-2">(Cloudflare R2 Media Storage)</span>
@endsection

@section('content')
<div class="app-content flex-column-fluid pb-15">
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
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <div class="row g-6">
            <!-- 3 Columns: Settings Navigation Sidebar -->
            <div class="col-xl-3 col-lg-4">
                <div class="card card-flush shadow-sm sticky-top" style="top: 90px; z-index: 10;">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title fw-bold text-gray-800 fs-5">
                            <i class="ki-duotone ki-setting-3 fs-3 me-2 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                            Menu Pengaturan
                        </h3>
                    </div>
                    <div class="card-body pt-2 pb-5">
                        <div class="nav flex-column nav-pills" role="tablist" aria-orientation="vertical">
                            <!-- WhatsApp Menu Item -->
                            <a href="{{ route('whatsapp.index') }}" class="nav-link d-flex align-items-center py-3 px-4 mb-2 rounded-3 text-gray-700 text-hover-primary fw-semibold fs-7 {{ request()->routeIs('whatsapp.*') ? 'active bg-light-primary text-primary fw-bold border border-primary border-opacity-25' : 'bg-hover-light' }}">
                                <i class="ki-duotone ki-whatsapp fs-2 me-3 {{ request()->routeIs('whatsapp.*') ? 'text-primary' : 'text-gray-500' }}"><span class="path1"></span><span class="path2"></span></i>
                                <div class="d-flex flex-column">
                                    <span class="fs-7 fw-bold">WhatsApp Gateway</span>
                                    <span class="fs-9 text-muted">Bot, QR Code & Webhook</span>
                                </div>
                            </a>
                            
                            <!-- Storage Menu Item -->
                            <a href="{{ route('settings.storage.index') }}" class="nav-link d-flex align-items-center py-3 px-4 mb-2 rounded-3 text-gray-700 text-hover-primary fw-semibold fs-7 {{ request()->routeIs('settings.storage.*') ? 'active bg-light-primary text-primary fw-bold border border-primary border-opacity-25' : 'bg-hover-light' }}">
                                <i class="ki-duotone ki-cloud-change fs-2 me-3 {{ request()->routeIs('settings.storage.*') ? 'text-primary' : 'text-gray-500' }}"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                <div class="d-flex flex-column">
                                    <span class="fs-7 fw-bold">Storage (R2)</span>
                                    <span class="fs-9 text-muted">Monitoring & Cloud Storage</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 9 Columns: Right Column with Tabs -->
            <div class="col-xl-9 col-lg-8">
                <!-- Nav Tabs Header -->
                <div class="card card-flush shadow-sm mb-6">
                    <div class="card-body p-4 sm-p-5">
                        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-6 fw-bold gap-6" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link text-active-primary pb-3 {{ $errors->any() ? '' : 'active' }} d-flex align-items-center gap-2" data-bs-toggle="tab" href="#tab_monitoring" role="tab">
                                    <i class="ki-duotone ki-chart-line fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    <span>Monitoring & Status</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link text-active-primary pb-3 {{ $errors->any() ? 'active' : '' }} d-flex align-items-center gap-2" data-bs-toggle="tab" href="#tab_form_config" role="tab">
                                    <i class="ki-duotone ki-setting-2 fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    <span>Form Konfigurasi R2</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Tab Contents -->
                <div class="tab-content" id="storage_tab_content">
                    <!-- Tab 1: Monitoring & Status -->
                    <div class="tab-pane fade {{ $errors->any() ? '' : 'show active' }}" id="tab_monitoring" role="tabpanel">
                        <div class="d-flex flex-column gap-6">
                            <!-- Dashboard KPI Card -->
                            <div class="card card-flush shadow-sm border border-primary border-opacity-25 bg-gradient-light">
                                <div class="card-body p-6">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-4 mb-6">
                                        <div class="d-flex align-items-center gap-4">
                                            <div class="symbol symbol-50px symbol-circle bg-light-primary p-3">
                                                <i class="ki-duotone ki-cloud-change fs-2hx text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <div class="d-flex align-items-center gap-2">
                                                    <h3 class="fw-bold text-gray-800 mb-0">Monitoring Cloudflare R2 Media Storage</h3>
                                                    <span class="badge badge-light-success fw-bold fs-7 px-3 py-2 rounded-pill d-inline-flex align-items-center">
                                                        <span class="bullet bullet-dot bg-success me-2"></span> Active Cloud Driver
                                                    </span>
                                                </div>
                                                <span class="text-gray-600 fs-7 mt-1">
                                                    Semua media, foto kebun, dan bukti transaksi disimpan secara otomatis di awan <strong>Cloudflare R2 Object Storage</strong>.
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex align-items-center gap-3">
                                            <button type="button" class="btn btn-sm btn-light-info fw-bold rounded-pill px-4" id="btn_quick_test_r2">
                                                <i class="ki-duotone ki-wifi fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i> Cek Health Status R2
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Monitoring KPIs -->
                                    <div class="row g-4">
                                        <div class="col-6 col-md-3">
                                            <div class="bg-body rounded-3 p-4 border border-dashed border-gray-300">
                                                <div class="text-gray-500 fs-8 fw-bold text-uppercase mb-1">Status Storage</div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="ki-duotone ki-check-circle fs-3 text-success"><span class="path1"></span><span class="path2"></span></i>
                                                    <span class="fs-6 fw-bolder text-gray-800">Cloudflare R2</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="bg-body rounded-3 p-4 border border-dashed border-gray-300">
                                                <div class="text-gray-500 fs-8 fw-bold text-uppercase mb-1">Bucket Active</div>
                                                <div class="fs-6 fw-bolder text-primary text-truncate" title="{{ $bucket }}">
                                                    {{ $bucket ?: 'Belum diatur' }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="bg-body rounded-3 p-4 border border-dashed border-gray-300">
                                                <div class="text-gray-500 fs-8 fw-bold text-uppercase mb-1">Public Domain</div>
                                                <div class="fs-6 fw-bolder text-gray-800 text-truncate" title="{{ $url }}">
                                                    {{ $url ? parse_url($url, PHP_URL_HOST) ?? $url : 'Belum diatur' }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <div class="bg-body rounded-3 p-4 border border-dashed border-gray-300">
                                                <div class="text-gray-500 fs-8 fw-bold text-uppercase mb-1">Sisa Berkas Lokal</div>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <span class="fs-6 fw-bolder text-gray-800">{{ $localFilesCount }} File</span>
                                                    <span class="badge badge-light-{{ $localFilesCount > 0 ? 'warning' : 'success' }} fs-9 fw-bold">{{ $localTotalFormatted }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Migration Card -->
                            <div class="card card-flush shadow-sm">
                                <div class="card-header border-0 pt-6">
                                    <h3 class="card-title fw-bold text-gray-800">
                                        <i class="ki-duotone ki-cloud-add fs-2 me-2 text-success"><span class="path1"></span><span class="path2"></span></i>
                                        Migrasi Gambar Lokal ke R2
                                    </h3>
                                </div>
                                <div class="card-body pt-0">
                                    <div class="alert alert-light-primary border-dashed border-primary p-4 rounded-3 mb-6">
                                        <div class="d-flex align-items-start gap-3">
                                            <i class="ki-duotone ki-information fs-2x text-primary mt-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                            <div class="fs-8 text-gray-700">
                                                Menyalin sisa berkas gambar & bukti transaksi yang berada di server lokal (<code>storage/app/public/</code>) langsung ke dalam bucket <strong>Cloudflare R2</strong>.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-4 mb-6">
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-between align-items-center p-4 bg-light rounded-3 border">
                                                <span class="fs-7 fw-bold text-gray-600">Sisa File di Server:</span>
                                                <span class="fs-6 fw-bolder text-gray-800">{{ $localFilesCount }} File</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex justify-content-between align-items-center p-4 bg-light rounded-3 border">
                                                <span class="fs-7 fw-bold text-gray-600">Total Ukuran:</span>
                                                <span class="fs-6 fw-bolder text-primary">{{ $localTotalFormatted }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Migration Progress Log Window -->
                                    <div id="migration_log_box" class="d-none mb-6">
                                        <label class="form-label fw-bold text-gray-700 fs-8">Status Process Migrasi:</label>
                                        <div class="p-3 bg-dark text-success font-monospace rounded-3 fs-9 overflow-auto" id="migration_log_text" style="max-height: 180px; min-height: 80px; white-space: pre-wrap;">
Menunggu perintah migrasi...
                                        </div>
                                    </div>

                                    <div class="pt-2">
                                        <button type="button" class="btn btn-success fw-bold rounded-pill w-100 py-3" id="btn_start_migration" {{ $localFilesCount == 0 ? 'disabled' : '' }}>
                                            <i class="ki-duotone ki-cloud-add fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
                                            {{ $localFilesCount == 0 ? 'Semua Berkas Telah Berada di Cloud R2' : 'Mulai Migrasi Sisa Gambar ke R2' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Form Konfigurasi R2 -->
                    <div class="tab-pane fade {{ $errors->any() ? 'show active' : '' }}" id="tab_form_config" role="tabpanel">
                        <form action="{{ route('settings.storage.update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="storage_driver" value="r2">
                            
                            <div class="card card-flush shadow-sm">
                                <div class="card-header border-0 pt-6">
                                    <h3 class="card-title fw-bold text-gray-800">
                                        <i class="ki-duotone ki-setting-2 fs-2 me-2 text-primary"><span class="path1"></span><span class="path2"></span></i>
                                        Form Konfigurasi Cloudflare R2
                                    </h3>
                                </div>
                                <div class="card-body pt-0">
                                    <div class="row g-5">
                                        <!-- Account ID -->
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label fw-bold text-gray-700 fs-7 required">Cloudflare Account ID:</label>
                                            <input type="text" name="r2_account_id" class="form-control form-control-solid fs-7" placeholder="Contoh: 8a5f3e9c..." value="{{ old('r2_account_id', $accountId) }}" required>
                                            <div class="form-text fs-9 text-gray-500">Account ID dapat ditemukan pada Dashboard Cloudflare -> R2 -> Overview.</div>
                                        </div>

                                        <!-- Bucket Name -->
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label fw-bold text-gray-700 fs-7 required">R2 Bucket Name:</label>
                                            <input type="text" name="r2_bucket" class="form-control form-control-solid fs-7" placeholder="Contoh: pengentani-media" value="{{ old('r2_bucket', $bucket) }}" required>
                                        </div>

                                        <!-- Access Key ID -->
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label fw-bold text-gray-700 fs-7 required">R2 Access Key ID:</label>
                                            <input type="text" name="r2_access_key_id" class="form-control form-control-solid fs-7" placeholder="Access Key ID API R2" value="{{ old('r2_access_key_id', $accessKey) }}" required>
                                        </div>

                                        <!-- Secret Access Key -->
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label fw-bold text-gray-700 fs-7 required">R2 Secret Access Key:</label>
                                            <div class="position-relative">
                                                <input type="password" name="r2_secret_access_key" id="r2_secret_input" class="form-control form-control-solid fs-7 pe-10" placeholder="Secret Access Key API R2" value="{{ old('r2_secret_access_key', $secretKey) }}" required>
                                                <button type="button" class="btn btn-icon btn-sm btn-active-color-primary position-absolute top-50 translate-middle-y end-0 me-2" id="toggle_secret_btn">
                                                    <i class="ki-duotone ki-eye fs-3" id="toggle_secret_icon"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Public Custom URL / Domain -->
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label fw-bold text-gray-700 fs-7 required">Public URL / Custom Domain Bucket:</label>
                                            <input type="text" name="r2_url" class="form-control form-control-solid fs-7" placeholder="Contoh: https://pub-xxx.r2.dev atau https://media.pengentani.my.id" value="{{ old('r2_url', $url) }}" required>
                                            <div class="form-text fs-9 text-gray-500">Domain publik R2 (pub-xxx.r2.dev) atau Custom Domain yang sudah terhubung.</div>
                                        </div>

                                        <!-- Custom Endpoint (Optional) -->
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label fw-bold text-gray-700 fs-7">Endpoint S3 (Opsional):</label>
                                            <input type="text" name="r2_endpoint" class="form-control form-control-solid fs-7" placeholder="Auto-generated dari Account ID jika dikosongkan" value="{{ old('r2_endpoint', $endpoint) }}">
                                            <div class="form-text fs-9 text-gray-500">Format default: <code>https://&lt;ACCOUNT_ID&gt;.r2.cloudflarestorage.com</code></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer d-flex align-items-center justify-content-between py-4 border-top mt-4">
                                    <button type="button" class="btn btn-light-info fw-bold btn-sm rounded-pill px-4" id="btn_test_r2">
                                        <i class="ki-duotone ki-wifi fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i> Test Koneksi R2
                                    </button>
                                    <button type="submit" class="btn btn-primary fw-bold btn-sm rounded-pill px-6">
                                        <i class="ki-duotone ki-check fs-3 me-1"></i> Simpan Konfigurasi
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Toggle password view for R2 Secret
        $('#toggle_secret_btn').on('click', function() {
            let input = $('#r2_secret_input');
            let icon = $('#toggle_secret_icon');
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('ki-eye').addClass('ki-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('ki-eye-slash').addClass('ki-eye');
            }
        });

        function runTestR2(btnElement) {
            let btn = $(btnElement);
            let oldHtml = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Testing...');

            $.ajax({
                url: "{{ route('settings.storage.test') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    storage_driver: 'r2',
                    r2_account_id: $('input[name="r2_account_id"]').val(),
                    r2_access_key_id: $('input[name="r2_access_key_id"]').val(),
                    r2_secret_access_key: $('input[name="r2_secret_access_key"]').val(),
                    r2_bucket: $('input[name="r2_bucket"]').val(),
                    r2_url: $('input[name="r2_url"]').val(),
                    r2_endpoint: $('input[name="r2_endpoint"]').val()
                },
                dataType: "json",
                success: function(response) {
                    btn.prop('disabled', false).html(oldHtml);
                    Swal.fire({
                        icon: 'success',
                        title: 'Koneksi R2 Online & Berhasil!',
                        text: response.message,
                        confirmButtonText: 'OK',
                        customClass: { confirmButton: 'btn btn-primary' }
                    });
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html(oldHtml);
                    let msg = "Terjadi kesalahan saat menguji koneksi.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Koneksi R2 Gagal',
                        text: msg,
                        confirmButtonText: 'Tutup',
                        customClass: { confirmButton: 'btn btn-danger' }
                    });
                }
            });
        }

        // Test Connection AJAX
        $('#btn_test_r2').on('click', function() {
            runTestR2(this);
        });

        $('#btn_quick_test_r2').on('click', function() {
            runTestR2(this);
        });

        // Start Local to R2 Migration AJAX
        $('#btn_start_migration').on('click', function() {
            Swal.fire({
                title: 'Konfirmasi Migrasi',
                text: 'Apakah Anda yakin ingin memindahkan seluruh gambar dari lokal server ke Cloudflare R2?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Mulai Migrasi',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-light'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    let btn = $('#btn_start_migration');
                    let logBox = $('#migration_log_box');
                    let logText = $('#migration_log_text');

                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Memproses Migrasi...');
                    logBox.removeClass('d-none');
                    logText.text('⏳ Memulai proses mengunggah file gambar ke Cloudflare R2...\nMohon jangan menutup halaman ini.');

                    $.ajax({
                        url: "{{ route('settings.storage.migrate') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        dataType: "json",
                        success: function(response) {
                            btn.prop('disabled', false).html('<i class="ki-duotone ki-cloud-add fs-2 me-2"><span class="path1"></span><span class="path2"></span></i> Mulai Migrasi Sisa Gambar ke R2');
                            
                            let log = `✅ MIGRASI SELESAI!\n-------------------------\n` +
                                      `File Berhasil: ${response.migrated_count} file\n` +
                                      `Total Ukuran : ${response.total_bytes_formatted}\n` +
                                      `File Gagal   : ${response.failed_count} file\n`;

                            if (response.errors && response.errors.length > 0) {
                                log += `\nRincian Error:\n` + response.errors.join('\n');
                            }

                            logText.text(log);

                            Swal.fire({
                                icon: response.failed_count === 0 ? 'success' : 'warning',
                                title: 'Migrasi Selesai',
                                text: response.message,
                                confirmButtonText: 'OK',
                                customClass: { confirmButton: 'btn btn-primary' }
                            });
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false).html('<i class="ki-duotone ki-cloud-add fs-2 me-2"><span class="path1"></span><span class="path2"></span></i> Mulai Migrasi Sisa Gambar ke R2');
                            let msg = "Terjadi kesalahan saat migrasi berkas.";
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            logText.text('❌ GAGAL MIGRASI:\n' + msg);
                            Swal.fire({
                                icon: 'error',
                                title: 'Migrasi Gagal',
                                text: msg,
                                confirmButtonText: 'Tutup',
                                customClass: { confirmButton: 'btn btn-danger' }
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
