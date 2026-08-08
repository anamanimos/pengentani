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
