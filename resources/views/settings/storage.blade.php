@extends('layouts.metronic')

@section('title', 'Pengaturan Penyimpanan Storage (R2)')

@section('page_title')
    Pengaturan Penyimpanan Media <span class="text-gray-500 fw-semibold fs-7 ms-2">(Cloudflare R2 / Local Storage)</span>
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

        <div class="row g-7">
            <!-- Status Card -->
            <div class="col-12">
                <div class="card card-flush shadow-sm">
                    <div class="card-body p-6">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                            <div class="d-flex align-items-center gap-4">
                                <div class="symbol symbol-50px symbol-circle bg-light-primary p-3">
                                    <i class="ki-duotone ki-cloud-change fs-2hx text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <div class="d-flex align-items-center gap-2">
                                        <h3 class="fw-bold text-gray-800 mb-0">Status Disposisi Penyimpanan Media</h3>
                                        @if($driver === 'r2')
                                            <span class="badge badge-light-success fw-bold fs-7 px-3 py-2 rounded-pill">Cloudflare R2 (Aktif)</span>
                                        @else
                                            <span class="badge badge-light-secondary fw-bold fs-7 px-3 py-2 rounded-pill">Local Storage (Aktif)</span>
                                        @endif
                                    </div>
                                    <span class="text-gray-500 fs-7 mt-1">
                                        Media & bukti transaksi saat ini disimpan di <strong>{{ $driver === 'r2' ? 'Cloudflare R2 Storage (Awan)' : 'Penyimpanan Lokal Server (storage/app/public)' }}</strong>.
                                    </span>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center gap-6 bg-light rounded-3 p-4 border">
                                <div class="d-flex flex-column text-end pe-4 border-end">
                                    <span class="fs-8 text-gray-500 fw-bold text-uppercase">Berkas Lokal Server</span>
                                    <span class="fs-6 fw-bolder text-gray-800">{{ $localFilesCount }} File</span>
                                </div>
                                <div class="d-flex flex-column text-end">
                                    <span class="fs-8 text-gray-500 fw-bold text-uppercase">Total Ukuran Lokal</span>
                                    <span class="fs-6 fw-bolder text-primary">{{ $localTotalFormatted }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuration Form -->
            <div class="col-xl-7">
                <form action="{{ route('settings.storage.update') }}" method="POST">
                    @csrf
                    <div class="card card-flush shadow-sm h-100">
                        <div class="card-header border-0 pt-6">
                            <h3 class="card-title fw-bold text-gray-800">
                                <i class="ki-duotone ki-setting-2 fs-2 me-2 text-primary"><span class="path1"></span><span class="path2"></span></i>
                                Konfigurasi Cloudflare R2
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <!-- Driver Selector -->
                            <div class="mb-7">
                                <label class="form-label fw-bold text-gray-800 fs-6 required">Pilihan Driver Penyimpanan Utama:</label>
                                <div class="row g-4">
                                    <div class="col-6">
                                        <input type="radio" class="btn-check" name="storage_driver" value="local" id="driver_local" {{ $driver === 'local' ? 'checked' : '' }}>
                                        <label class="btn btn-outline btn-outline-dashed btn-active-light-primary d-flex flex-column align-items-start p-4 text-start w-100" for="driver_local">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <i class="ki-duotone ki-folder fs-2 text-gray-700"><span class="path1"></span><span class="path2"></span></i>
                                                <span class="fw-bold fs-6 text-gray-800">Local Storage</span>
                                            </div>
                                            <span class="fs-8 text-gray-500">Menyimpan berkas di server fisik (storage/app/public).</span>
                                        </label>
                                    </div>
                                    <div class="col-6">
                                        <input type="radio" class="btn-check" name="storage_driver" value="r2" id="driver_r2" {{ $driver === 'r2' ? 'checked' : '' }}>
                                        <label class="btn btn-outline btn-outline-dashed btn-active-light-primary d-flex flex-column align-items-start p-4 text-start w-100" for="driver_r2">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <i class="ki-duotone ki-cloud-change fs-2 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                <span class="fw-bold fs-6 text-gray-800">Cloudflare R2</span>
                                            </div>
                                            <span class="fs-8 text-gray-500">Menyimpan berkas di awan Cloudflare R2 S3 Object Storage.</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Account ID -->
                            <div class="mb-5">
                                <label class="form-label fw-bold text-gray-700 fs-7">Cloudflare Account ID:</label>
                                <input type="text" name="r2_account_id" class="form-control form-control-solid fs-7" placeholder="Contoh: 8a5f3e9c..." value="{{ old('r2_account_id', $accountId) }}">
                                <div class="form-text fs-9 text-gray-500">Dapat ditemukan di Dashboard Cloudflare -> R2 -> Overview.</div>
                            </div>

                            <!-- Access Key ID -->
                            <div class="mb-5">
                                <label class="form-label fw-bold text-gray-700 fs-7">R2 Access Key ID:</label>
                                <input type="text" name="r2_access_key_id" class="form-control form-control-solid fs-7" placeholder="Access Key ID API R2" value="{{ old('r2_access_key_id', $accessKey) }}">
                            </div>

                            <!-- Secret Access Key -->
                            <div class="mb-5">
                                <label class="form-label fw-bold text-gray-700 fs-7">R2 Secret Access Key:</label>
                                <div class="position-relative">
                                    <input type="password" name="r2_secret_access_key" id="r2_secret_input" class="form-control form-control-solid fs-7 pe-10" placeholder="Secret Access Key API R2" value="{{ old('r2_secret_access_key', $secretKey) }}">
                                    <button type="button" class="btn btn-icon btn-sm btn-active-color-primary position-absolute top-50 translate-middle-y end-0 me-2" id="toggle_secret_btn">
                                        <i class="ki-duotone ki-eye fs-3" id="toggle_secret_icon"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Bucket Name -->
                            <div class="mb-5">
                                <label class="form-label fw-bold text-gray-700 fs-7">R2 Bucket Name:</label>
                                <input type="text" name="r2_bucket" class="form-control form-control-solid fs-7" placeholder="Contoh: pengentani-media" value="{{ old('r2_bucket', $bucket) }}">
                            </div>

                            <!-- Public Custom URL / Domain -->
                            <div class="mb-5">
                                <label class="form-label fw-bold text-gray-700 fs-7">Public URL / Custom Domain Bucket:</label>
                                <input type="text" name="r2_url" class="form-control form-control-solid fs-7" placeholder="Contoh: https://pub-xxx.r2.dev atau https://media.pengentani.my.id" value="{{ old('r2_url', $url) }}">
                                <div class="form-text fs-9 text-gray-500">Domain publik R2 atau Custom Domain yang sudah terhubung dengan R2 Bucket.</div>
                            </div>

                            <!-- Custom Endpoint (Optional) -->
                            <div class="mb-5">
                                <label class="form-label fw-bold text-gray-700 fs-7">Endpoint S3 (Opsional):</label>
                                <input type="text" name="r2_endpoint" class="form-control form-control-solid fs-7" placeholder="Auto-generated dari Account ID jika dikosongkan" value="{{ old('r2_endpoint', $endpoint) }}">
                                <div class="form-text fs-9 text-gray-500">Format default: <code>https://&lt;ACCOUNT_ID&gt;.r2.cloudflarestorage.com</code></div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between py-4 border-top">
                            <button type="button" class="btn btn-light-info fw-bold btn-sm rounded-pill px-4" id="btn_test_r2">
                                <i class="ki-duotone ki-wifi fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i> Test Koneksi R2
                            </button>
                            <button type="submit" class="btn btn-primary fw-bold btn-sm rounded-pill px-6">
                                <i class="ki-duotone ki-check fs-3 me-1"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Migration Card -->
            <div class="col-xl-5">
                <div class="card card-flush shadow-sm h-100">
                    <div class="card-header border-0 pt-6">
                        <h3 class="card-title fw-bold text-gray-800">
                            <i class="ki-duotone ki-cloud-add fs-2 me-2 text-success"><span class="path1"></span><span class="path2"></span></i>
                            Migrasi Gambar Lokal ke Cloudflare R2
                        </h3>
                    </div>
                    <div class="card-body pt-0">
                        <div class="alert alert-light-primary border-dashed border-primary p-4 rounded-3 mb-6">
                            <div class="d-flex align-items-start gap-3">
                                <i class="ki-duotone ki-information fs-2x text-primary mt-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                <div class="fs-8 text-gray-700">
                                    Fitur ini akan menyalin seluruh gambar & berkas bukti transaksi yang berada di direktori lokal server (<code>storage/app/public/</code>) langsung ke dalam bucket **Cloudflare R2** secara otomatis.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-3 mb-6">
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3 border">
                                <span class="fs-8 fw-bold text-gray-600">Jumlah File Lokal:</span>
                                <span class="fs-7 fw-bolder text-gray-800">{{ $localFilesCount }} File</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3 border">
                                <span class="fs-8 fw-bold text-gray-600">Total Ukuran:</span>
                                <span class="fs-7 fw-bolder text-primary">{{ $localTotalFormatted }}</span>
                            </div>
                        </div>

                        <!-- Migration Progress Log Window -->
                        <div id="migration_log_box" class="d-none mb-6">
                            <label class="form-label fw-bold text-gray-700 fs-8">Status Process Migrasi:</label>
                            <div class="p-3 bg-dark text-success font-monospace rounded-3 fs-9 overflow-auto" id="migration_log_text" style="max-height: 180px; min-height: 80px; white-space: pre-wrap;">
                                Menunggu perintah migrasi...
                            </div>
                        </div>

                        <div class="text-center pt-2">
                            <button type="button" class="btn btn-success fw-bold rounded-pill w-100 py-3" id="btn_start_migration" {{ $localFilesCount == 0 ? 'disabled' : '' }}>
                                <i class="ki-duotone ki-cloud-add fs-2 me-2"><span class="path1"></span><span class="path2"></span></i>
                                Mulai Migrasi Gambar ke R2
                            </button>
                        </div>
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

        // Test Connection AJAX
        $('#btn_test_r2').on('click', function() {
            let btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Testing...');

            $.ajax({
                url: "{{ route('settings.storage.test') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    storage_driver: $('input[name="storage_driver"]:checked').val(),
                    r2_account_id: $('input[name="r2_account_id"]').val(),
                    r2_access_key_id: $('input[name="r2_access_key_id"]').val(),
                    r2_secret_access_key: $('input[name="r2_secret_access_key"]').val(),
                    r2_bucket: $('input[name="r2_bucket"]').val(),
                    r2_url: $('input[name="r2_url"]').val(),
                    r2_endpoint: $('input[name="r2_endpoint"]').val()
                },
                dataType: "json",
                success: function(response) {
                    btn.prop('disabled', false).html('<i class="ki-duotone ki-wifi fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i> Test Koneksi R2');
                    Swal.fire({
                        icon: 'success',
                        title: 'Koneksi Berhasil!',
                        text: response.message,
                        confirmButtonText: 'OK',
                        customClass: { confirmButton: 'btn btn-primary' }
                    });
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="ki-duotone ki-wifi fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i> Test Koneksi R2');
                    let msg = "Terjadi kesalahan saat menguji koneksi.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Koneksi Gagal',
                        text: msg,
                        confirmButtonText: 'Tutup',
                        customClass: { confirmButton: 'btn btn-danger' }
                    });
                }
            });
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
                            btn.prop('disabled', false).html('<i class="ki-duotone ki-cloud-add fs-2 me-2"><span class="path1"></span><span class="path2"></span></i> Mulai Migrasi Gambar ke R2');
                            
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
                            btn.prop('disabled', false).html('<i class="ki-duotone ki-cloud-add fs-2 me-2"><span class="path1"></span><span class="path2"></span></i> Mulai Migrasi Gambar ke R2');
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
