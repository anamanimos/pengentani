@extends('layouts.metronic')

@section('title', 'Pengaturan Umum System')

@section('page_title')
    Pengaturan Sistem <span class="text-gray-500 fw-semibold fs-7 ms-2">(Pengaturan Umum & Identitas Aplikasi)</span>
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
                            <!-- General Settings Menu Item -->
                            <a href="{{ route('settings.general.index') }}" class="nav-link d-flex align-items-center py-3 px-4 mb-2 rounded-3 text-gray-700 text-hover-primary fw-semibold fs-7 {{ request()->routeIs('settings.general.*') ? 'active bg-light-primary text-primary fw-bold border border-primary border-opacity-25' : 'bg-hover-light' }}">
                                <i class="ki-duotone ki-setting-2 fs-2 me-3 {{ request()->routeIs('settings.general.*') ? 'text-primary' : 'text-gray-500' }}"><span class="path1"></span><span class="path2"></span></i>
                                <div class="d-flex flex-column">
                                    <span class="fs-7 fw-bold">Pengaturan Umum</span>
                                    <span class="fs-9 text-muted">Profil Aplikasi & Sistem</span>
                                </div>
                            </a>

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

                            <!-- Backup Database Menu Item -->
                            <a href="{{ route('settings.backup.index') }}" class="nav-link d-flex align-items-center py-3 px-4 mb-2 rounded-3 text-gray-700 text-hover-primary fw-semibold fs-7 {{ request()->routeIs('settings.backup.*') ? 'active bg-light-primary text-primary fw-bold border border-primary border-opacity-25' : 'bg-hover-light' }}">
                                <i class="ki-duotone ki-data fs-2 me-3 {{ request()->routeIs('settings.backup.*') ? 'text-primary' : 'text-gray-500' }}"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                <div class="d-flex flex-column">
                                    <span class="fs-7 fw-bold">Backup Database</span>
                                    <span class="fs-9 text-muted">Gzip Dump & Telegram</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 9 Columns: Form General Settings -->
            <div class="col-xl-9 col-lg-8">
                <form action="{{ route('settings.general.update') }}" method="POST">
                    @csrf
                    <div class="card card-flush shadow-sm">
                        <div class="card-header border-0 pt-6">
                            <h3 class="card-title fw-bold text-gray-800">
                                <i class="ki-duotone ki-setting-2 fs-2 me-2 text-primary"><span class="path1"></span><span class="path2"></span></i>
                                Form Pengaturan Umum Aplikasi
                            </h3>
                        </div>
                        <div class="card-body pt-2">
                            <div class="row g-5">
                                <!-- App Name -->
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold text-gray-700 fs-7 required">Nama Aplikasi / Sistem:</label>
                                    <input type="text" name="app_name" class="form-control form-control-solid fs-7" placeholder="Contoh: Pengen Tani" value="{{ old('app_name', $appName) }}" required>
                                    <div class="form-text fs-9 text-gray-500">Nama utama yang ditampilkan pada judul halaman dan navbar.</div>
                                </div>

                                <!-- App Tagline -->
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold text-gray-700 fs-7">Tagline / Slogan Aplikasi:</label>
                                    <input type="text" name="app_tagline" class="form-control form-control-solid fs-7" placeholder="Contoh: Pengelolaan Pertanian & Investasi Kebun" value="{{ old('app_tagline', $appTagline) }}">
                                    <div class="form-text fs-9 text-gray-500">Deskripsi singkat atau slogan aplikasi.</div>
                                </div>

                                <!-- Company Name -->
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold text-gray-700 fs-7">Nama Perusahaan / Organisasi:</label>
                                    <input type="text" name="company_name" class="form-control form-control-solid fs-7" placeholder="Contoh: PT Pengen Tani Indonesia" value="{{ old('company_name', $companyName) }}">
                                </div>

                                <!-- Contact Email -->
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold text-gray-700 fs-7">Email Kontak / Support:</label>
                                    <input type="email" name="contact_email" class="form-control form-control-solid fs-7" placeholder="Contoh: admin@pengentani.my.id" value="{{ old('contact_email', $contactEmail) }}">
                                </div>

                                <!-- Contact Phone -->
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold text-gray-700 fs-7">Nomor Telepon / WhatsApp CS:</label>
                                    <input type="text" name="contact_phone" class="form-control form-control-solid fs-7" placeholder="Contoh: 6281234567890" value="{{ old('contact_phone', $contactPhone) }}">
                                </div>

                                <!-- Currency Symbol -->
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold text-gray-700 fs-7 required">Simbol Mata Uang:</label>
                                    <input type="text" name="currency_symbol" class="form-control form-control-solid fs-7" placeholder="Contoh: Rp" value="{{ old('currency_symbol', $currencySymbol) }}" required>
                                </div>

                                <!-- Timezone -->
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold text-gray-700 fs-7 required">Zona Waktu Sistem:</label>
                                    <select name="timezone" class="form-select form-select-solid fs-7" required>
                                        <option value="Asia/Jakarta" {{ old('timezone', $timezone) == 'Asia/Jakarta' ? 'selected' : '' }}>Asia/Jakarta (WIB - UTC+7)</option>
                                        <option value="Asia/Makassar" {{ old('timezone', $timezone) == 'Asia/Makassar' ? 'selected' : '' }}>Asia/Makassar (WITA - UTC+8)</option>
                                        <option value="Asia/Jayapura" {{ old('timezone', $timezone) == 'Asia/Jayapura' ? 'selected' : '' }}>Asia/Jayapura (WIT - UTC+9)</option>
                                    </select>
                                </div>

                                <!-- Address -->
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold text-gray-700 fs-7">Alamat Utama / Kebun Pusat:</label>
                                    <textarea name="address" class="form-control form-control-solid fs-7" rows="2" placeholder="Alamat lengkap lokasi kantor / kebun utama">{{ old('address', $address) }}</textarea>
                                </div>
                            </div>

                            <div class="separator separator-dashed my-8"></div>

                            <!-- Google Gemini AI Section -->
                            <div class="d-flex align-items-center mb-6">
                                <div class="symbol symbol-45px symbol-circle bg-light-primary me-3">
                                    <i class="ki-duotone ki-technology-4 fs-2 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <h4 class="fw-bold text-gray-800 mb-0">Integrasi Google Gemini AI</h4>
                                    <span class="fs-8 text-muted">Dibutuhkan untuk ekstraksi otomatis foto buku catatan buruh tani ke data pekerjaan (Vision AI)</span>
                                </div>
                            </div>

                            <div class="row g-5">
                                <!-- Gemini API Key -->
                                <div class="col-md-8 mb-2">
                                    <label class="form-label fw-bold text-gray-700 fs-7">
                                        Gemini API Key:
                                        <span class="badge badge-light-primary fs-9 ms-1">Google AI Studio</span>
                                    </label>
                                    <div class="input-group input-group-solid">
                                        <input type="password" id="gemini_api_key" name="gemini_api_key" class="form-control form-control-solid fs-7" placeholder="AIzaSy..." value="{{ old('gemini_api_key', $geminiApiKey) }}" autocomplete="new-password">
                                        <button class="btn btn-icon btn-light" type="button" id="toggle-gemini-key" title="Lihat/Sembunyikan Key">
                                            <i class="ki-duotone ki-eye fs-3" id="icon-eye-gemini"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                        </button>
                                        <button class="btn btn-light-primary fw-bold fs-7 px-4" type="button" id="btn-test-gemini">
                                            <span class="indicator-label"><i class="ki-duotone ki-wifi fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i> Test Koneksi</span>
                                            <span class="indicator-progress d-none"><span class="spinner-border spinner-border-sm align-middle me-1"></span> Menguji...</span>
                                        </button>
                                    </div>
                                    <div class="form-text fs-9 text-gray-500">
                                        Dapatkan API Key di <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-primary fw-semibold">Google AI Studio</a>. Digunakan untuk membaca tulisan tangan dari foto buku catatan pekerja.
                                    </div>
                                </div>

                                <!-- Gemini Model -->
                                <div class="col-md-4 mb-2">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <label class="form-label fw-bold text-gray-700 fs-7 mb-1">Model Gemini AI:</label>
                                        <button type="button" id="btn-fetch-models" class="btn btn-link btn-color-primary btn-active-color-primary p-0 fs-9 fw-bold" title="Ambil daftar model yang aktif di akun Anda">
                                            <i class="ki-duotone ki-arrows-circle fs-8 me-1"><span class="path1"></span><span class="path2"></span></i> Muat dari Akun
                                        </button>
                                    </div>
                                    <select name="gemini_model" id="gemini_model" class="form-select form-select-solid fs-7">
                                        <option value="gemini-3.6-flash" {{ old('gemini_model', $geminiModel) == 'gemini-3.6-flash' ? 'selected' : '' }}>gemini-3.6-flash (Terbaru & Direkomendasikan)</option>
                                        <option value="gemini-2.5-flash" {{ old('gemini_model', $geminiModel) == 'gemini-2.5-flash' ? 'selected' : '' }}>gemini-2.5-flash</option>
                                        <option value="gemini-2.5-pro" {{ old('gemini_model', $geminiModel) == 'gemini-2.5-pro' ? 'selected' : '' }}>gemini-2.5-pro</option>
                                        <option value="gemini-1.5-flash" {{ old('gemini_model', $geminiModel) == 'gemini-1.5-flash' ? 'selected' : '' }}>gemini-1.5-flash</option>
                                        <option value="gemini-1.5-pro" {{ old('gemini_model', $geminiModel) == 'gemini-1.5-pro' ? 'selected' : '' }}>gemini-1.5-pro</option>
                                    </select>
                                    <div class="form-text fs-9 text-gray-500">Pilih model multimodal yang aktif di akun Anda.</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-end py-4 border-top mt-4">
                            <button type="submit" class="btn btn-primary fw-bold btn-sm rounded-pill px-6">
                                <i class="ki-duotone ki-check fs-3 me-1"></i> Simpan Pengaturan Umum
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle Gemini Key Visibility
        const toggleBtn = document.getElementById('toggle-gemini-key');
        const keyInput = document.getElementById('gemini_api_key');
        const eyeIcon = document.getElementById('icon-eye-gemini');

        if (toggleBtn && keyInput) {
            toggleBtn.addEventListener('click', function() {
                if (keyInput.type === 'password') {
                    keyInput.type = 'text';
                    eyeIcon.classList.remove('ki-eye');
                    eyeIcon.classList.add('ki-eye-slash');
                } else {
                    keyInput.type = 'password';
                    eyeIcon.classList.remove('ki-eye-slash');
                    eyeIcon.classList.add('ki-eye');
                }
            });
        }

        // Fetch Models from Account
        const fetchModelsBtn = document.getElementById('btn-fetch-models');
        const modelSelect = document.getElementById('gemini_model');
        if (fetchModelsBtn && modelSelect) {
            fetchModelsBtn.addEventListener('click', function() {
                const apiKey = document.getElementById('gemini_api_key').value.trim();
                if (!apiKey) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'API Key Kosong',
                        text: 'Silakan isi Gemini API Key terlebih dahulu untuk memuat daftar model dari akun Anda.',
                        customClass: { confirmButton: 'btn btn-primary' }
                    });
                    return;
                }

                fetchModelsBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memuat...';
                fetchModelsBtn.disabled = true;

                $.ajax({
                    url: '{{ route("settings.general.gemini-models") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        api_key: apiKey
                    },
                    success: function(res) {
                        if (res.success && res.models && res.models.length > 0) {
                            const currentVal = modelSelect.value;
                            modelSelect.innerHTML = '';
                            res.models.forEach(function(m) {
                                const opt = document.createElement('option');
                                opt.value = m.id;
                                opt.textContent = m.name ? `${m.name} (${m.id})` : m.id;
                                if (m.id === currentVal || m.id === 'gemini-3.6-flash') {
                                    opt.selected = true;
                                }
                                modelSelect.appendChild(opt);
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
                        fetchModelsBtn.innerHTML = '<i class="ki-duotone ki-arrows-circle fs-8 me-1"><span class="path1"></span><span class="path2"></span></i> Muat dari Akun';
                        fetchModelsBtn.disabled = false;
                    }
                });
            });
        }

        // Test Gemini Connection
        const testBtn = document.getElementById('btn-test-gemini');
        if (testBtn) {
            testBtn.addEventListener('click', function() {
                const apiKey = document.getElementById('gemini_api_key').value.trim();
                const model = document.getElementById('gemini_model').value;

                if (!apiKey) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'API Key Kosong',
                        text: 'Silakan masukkan Gemini API Key terlebih dahulu sebelum menguji koneksi.',
                        customClass: { confirmButton: 'btn btn-primary' }
                    });
                    return;
                }

                const label = testBtn.querySelector('.indicator-label');
                const progress = testBtn.querySelector('.indicator-progress');

                label.classList.add('d-none');
                progress.classList.remove('d-none');
                testBtn.disabled = true;

                $.ajax({
                    url: '{{ route("settings.general.test-gemini") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        api_key: apiKey,
                        model: model
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Koneksi Berhasil!',
                                text: response.message,
                                customClass: { confirmButton: 'btn btn-primary' }
                            });
                        } else {
                            // If model is outdated, suggest switching to gemini-3.6-flash
                            if (response.message.includes('gemini-3.6-flash') || response.message.includes('no longer available')) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Model Perlu Diperbarui',
                                    text: response.message + ' Ingin ganti model ke gemini-3.6-flash sekarang?',
                                    showCancelButton: true,
                                    confirmButtonText: 'Ya, Gunakan gemini-3.6-flash',
                                    cancelButtonText: 'Tutup',
                                    customClass: {
                                        confirmButton: 'btn btn-primary',
                                        cancelButton: 'btn btn-light'
                                    }
                                }).then((r) => {
                                    if (r.isConfirmed) {
                                        modelSelect.value = 'gemini-3.6-flash';
                                        testBtn.click();
                                    }
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Koneksi Gagal',
                                    text: response.message,
                                    customClass: { confirmButton: 'btn btn-primary' }
                                });
                            }
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Kesalahan Server',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan saat memverifikasi API Key.',
                            customClass: { confirmButton: 'btn btn-primary' }
                        });
                    },
                    complete: function() {
                        label.classList.remove('d-none');
                        progress.classList.add('d-none');
                        testBtn.disabled = false;
                    }
                });
            });
        }
    });
</script>
@endpush
