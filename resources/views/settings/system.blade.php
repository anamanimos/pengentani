@extends('layouts.metronic')

@section('title', 'Pembaruan Sistem & Migrasi')

@section('page_title')
    Pengaturan Sistem <span class="text-gray-500 fw-semibold fs-7 ms-2">(Git Pull & Migrasi Database)</span>
@endsection

@push('styles')
<style>
    .settings-nav-sticky {
        top: 90px;
        z-index: 10;
    }
    .terminal-console {
        min-height: 280px;
        max-height: 480px;
        white-space: pre-wrap;
        word-break: break-all;
    }
</style>
@endpush

@section('content')
<div class="app-content flex-column-fluid pb-15">
    <div class="app-container container-fluid">

        <div class="row g-6">
            <!-- 3 Columns: Settings Navigation Sidebar -->
            <div class="col-xl-3 col-lg-4">
                <div class="card card-flush shadow-sm sticky-top settings-nav-sticky">
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

                            <!-- Pembaruan Sistem (Git & Migrasi) -->
                            <a href="{{ route('settings.system.index') }}" class="nav-link d-flex align-items-center py-3 px-4 mb-2 rounded-3 text-gray-700 text-hover-primary fw-semibold fs-7 {{ request()->routeIs('settings.system.*') ? 'active bg-light-primary text-primary fw-bold border border-primary border-opacity-25' : 'bg-hover-light' }}">
                                <i class="ki-duotone ki-cloud-download fs-2 me-3 {{ request()->routeIs('settings.system.*') ? 'text-primary' : 'text-gray-500' }}"><span class="path1"></span><span class="path2"></span></i>
                                <div class="d-flex flex-column">
                                    <span class="fs-7 fw-bold">Pembaruan Sistem</span>
                                    <span class="fs-9 text-muted">Git Pull & Migrasi Database</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 9 Columns: Main Content -->
            <div class="col-xl-9 col-lg-8">
                <!-- Status KPI Cards -->
                <div class="row g-5 mb-6">
                    <!-- Git Branch & Commit -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card card-flush shadow-sm h-100">
                            <div class="card-body p-5">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="symbol symbol-40px symbol-circle bg-light-primary me-3">
                                        <i class="ki-duotone ki-git fs-2 text-primary"><span class="path1"></span><span class="path2"></span></i>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-500 fs-8 fw-semibold text-uppercase">Branch Aktif</span>
                                        <span class="text-gray-900 fs-6 fw-bold" id="kpi-branch">{{ $gitBranch }}</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between pt-1 border-top border-gray-200">
                                    <span class="text-muted fs-8">Commit</span>
                                    <span class="badge badge-light-info fw-bold font-monospace fs-8" id="kpi-commit">{{ $gitCommit }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Migrations Status -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card card-flush shadow-sm h-100">
                            <div class="card-body p-5">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="symbol symbol-40px symbol-circle {{ count($pendingMigrations) > 0 ? 'bg-light-warning' : 'bg-light-success' }} me-3">
                                        <i class="ki-duotone ki-data fs-2 {{ count($pendingMigrations) > 0 ? 'text-warning' : 'text-success' }}"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-500 fs-8 fw-semibold text-uppercase">Pending Migrasi</span>
                                        <span class="text-gray-900 fs-6 fw-bold" id="kpi-pending-count">{{ count($pendingMigrations) }} Berkas</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between pt-1 border-top border-gray-200">
                                    <span class="text-muted fs-8">Status Skema</span>
                                    @if(count($pendingMigrations) > 0)
                                        <span class="badge badge-light-warning fw-bold fs-8" id="kpi-pending-badge">Perlu Migrasi</span>
                                    @else
                                        <span class="badge badge-light-success fw-bold fs-8" id="kpi-pending-badge">Up to Date</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Environment & Versions -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card card-flush shadow-sm h-100">
                            <div class="card-body p-5">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="symbol symbol-40px symbol-circle bg-light-info me-3">
                                        <i class="ki-duotone ki-technology-4 fs-2 text-info"><span class="path1"></span><span class="path2"></span></i>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-500 fs-8 fw-semibold text-uppercase">Environment</span>
                                        <span class="text-gray-900 fs-6 fw-bold text-capitalize">{{ $environment }}</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between pt-1 border-top border-gray-200">
                                    <span class="text-muted fs-8">Laravel / PHP</span>
                                    <span class="text-gray-700 fw-bold fs-8">v{{ $laravelVersion }} ({{ $phpVersion }})</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Working Tree Status -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card card-flush shadow-sm h-100">
                            <div class="card-body p-5">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="symbol symbol-40px symbol-circle {{ $gitClean ? 'bg-light-success' : 'bg-light-warning' }} me-3">
                                        <i class="ki-duotone ki-shield-tick fs-2 {{ $gitClean ? 'text-success' : 'text-warning' }}"><span class="path1"></span><span class="path2"></span></i>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-500 fs-8 fw-semibold text-uppercase">Working Tree</span>
                                        <span class="text-gray-900 fs-6 fw-bold">{{ $gitClean ? 'Clean' : 'Modified' }}</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between pt-1 border-top border-gray-200">
                                    <span class="text-muted fs-8">Terakhir Commit</span>
                                    <span class="text-gray-700 fw-semibold fs-8 text-truncate" title="{{ $gitDate }}">{{ $gitDate ?: '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Last Commit Detail Banner -->
                @if($gitCommit !== 'unknown')
                <div class="card card-flush shadow-sm mb-6">
                    <div class="card-body p-5">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div class="d-flex align-items-center">
                                <span class="badge badge-light-primary fw-bold font-monospace fs-7 me-3">{{ $gitCommit }}</span>
                                <div>
                                    <div class="text-gray-900 fw-bold fs-6">{{ $gitMessage }}</div>
                                    <div class="text-gray-500 fs-8">Oleh <span class="text-gray-700 fw-semibold">{{ $gitAuthor }}</span> &bull; {{ $gitDate }}</div>
                                </div>
                            </div>
                            @if($gitRemoteUrl)
                            <div class="text-muted fs-8">
                                <i class="ki-duotone ki-geolocation fs-7 me-1"><span class="path1"></span><span class="path2"></span></i>
                                Remote: <span class="text-gray-700 font-monospace">{{ $gitRemoteUrl }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Action Cards Row -->
                <div class="row g-6 mb-6">
                    <!-- Card 1: Git Pull Actions -->
                    <div class="col-md-6">
                        <div class="card card-flush shadow-sm h-100">
                            <div class="card-header border-0 pt-6">
                                <h3 class="card-title fw-bold text-gray-800">
                                    <i class="ki-duotone ki-cloud-download fs-2 me-2 text-primary"><span class="path1"></span><span class="path2"></span></i>
                                    Pembaruan Repository (Git)
                                </h3>
                            </div>
                            <div class="card-body pt-2">
                                <p class="text-gray-600 fs-7 mb-4">
                                    Tarik commit terbaru dari GitHub ke server produksi. Fitur ini menjalankan perintah <code>git pull origin {{ $gitBranch }}</code> secara langsung.
                                </p>

                                <div class="bg-light-primary rounded p-4 mb-5 border border-primary border-opacity-25">
                                    <div class="d-flex align-items-center">
                                        <i class="ki-duotone ki-information-5 fs-2hx text-primary me-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                        <div class="text-gray-700 fs-8">
                                            Pastikan perubahan di lokal sudah di-push ke GitHub sebelum melakukan penarikan di server produksi.
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-primary btn-sm" id="btn-git-pull">
                                        <span class="indicator-label">
                                            <i class="ki-duotone ki-cloud-download fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Tarik Update (Git Pull)
                                        </span>
                                        <span class="indicator-progress">
                                            Menarik data... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                        </span>
                                    </button>

                                    <button type="button" class="btn btn-light-primary btn-sm" id="btn-check-remote">
                                        <span class="indicator-label">
                                            <i class="ki-duotone ki-arrows-circle fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Cek Update Remote
                                        </span>
                                        <span class="indicator-progress">
                                            Memeriksa... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Database Migration Actions -->
                    <div class="col-md-6">
                        <div class="card card-flush shadow-sm h-100">
                            <div class="card-header border-0 pt-6">
                                <h3 class="card-title fw-bold text-gray-800">
                                    <i class="ki-duotone ki-data fs-2 me-2 text-warning"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                    Migrasi Database (Artisan)
                                </h3>
                            </div>
                            <div class="card-body pt-2">
                                <p class="text-gray-600 fs-7 mb-4">
                                    Jalankan migrasi skema tabel database terbaru secara aman dengan perintah <code>php artisan migrate --force</code>.
                                </p>

                                @if(count($pendingMigrations) > 0)
                                <div class="alert alert-warning d-flex align-items-center p-3 mb-5 rounded">
                                    <i class="ki-duotone ki-abstract-26 fs-2hx text-warning me-3"><span class="path1"></span><span class="path2"></span></i>
                                    <div class="fs-8">
                                        <span class="fw-bold">Terdapat {{ count($pendingMigrations) }} migrasi belum dieksekusi:</span>
                                        <ul class="mb-0 ps-4 pt-1">
                                            @foreach($pendingMigrations as $pending)
                                                <li class="font-monospace">{{ $pending }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                @else
                                <div class="bg-light-success rounded p-4 mb-5 border border-success border-opacity-25">
                                    <div class="d-flex align-items-center">
                                        <i class="ki-duotone ki-check-circle fs-2hx text-success me-3"><span class="path1"></span><span class="path2"></span></i>
                                        <div class="text-gray-700 fs-8">
                                            Skema database sudah mutakhir. Tidak ada berkas migrasi pending.
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-warning btn-sm" id="btn-migrate">
                                        <span class="indicator-label">
                                            <i class="ki-duotone ki-double-check fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Jalankan Migrasi
                                        </span>
                                        <span class="indicator-progress">
                                            Memigrasi... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                        </span>
                                    </button>

                                    <button type="button" class="btn btn-light-warning btn-sm" id="btn-migrate-status">
                                        <span class="indicator-label">
                                            <i class="ki-duotone ki-notepad fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Cek Status Migrasi
                                        </span>
                                        <span class="indicator-progress">
                                            Memeriksa... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Utilities Card: Cache & Working Tree -->
                <div class="card card-flush shadow-sm mb-6">
                    <div class="card-header border-0 pt-6">
                        <h3 class="card-title fw-bold text-gray-800">
                            <i class="ki-duotone ki-element-11 fs-2 me-2 text-info"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                            Pemeliharaan & Utilitas Sistem
                        </h3>
                    </div>
                    <div class="card-body pt-2">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div class="text-gray-600 fs-7">
                                Bersihkan cache tampilan, rute, dan konfigurasi setelah melakukan update, atau periksa detail status file Git.
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-light-danger btn-sm" id="btn-clear-cache">
                                    <span class="indicator-label">
                                        <i class="ki-duotone ki-trash fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i> Bersihkan Cache (optimize:clear)
                                    </span>
                                    <span class="indicator-progress">
                                        Membersihkan... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                    </span>
                                </button>

                                <button type="button" class="btn btn-light-info btn-sm" id="btn-git-status">
                                    <span class="indicator-label">
                                        <i class="ki-duotone ki-code fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Git Status Lengkap
                                    </span>
                                    <span class="indicator-progress">
                                        Memuat... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terminal / Output Console -->
                <div class="card card-flush shadow-sm">
                    <div class="card-header border-0 pt-6">
                        <h3 class="card-title fw-bold text-gray-800">
                            <i class="ki-duotone ki-tablet fs-2 me-2 text-gray-700"><span class="path1"></span><span class="path2"></span></i>
                            Terminal Output & Log Eksekusi
                        </h3>
                        <div class="card-toolbar gap-2">
                            <button type="button" class="btn btn-light-secondary btn-sm" id="btn-copy-terminal" title="Salin Log Terminal">
                                <i class="ki-duotone ki-copy fs-4 me-1"></i> Salin Log
                            </button>
                            <button type="button" class="btn btn-light-secondary btn-sm" id="btn-clear-terminal" title="Bersihkan Tampilan Terminal">
                                <i class="ki-duotone ki-trash fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Bersihkan
                            </button>
                        </div>
                    </div>
                    <div class="card-body pt-2 pb-6">
                        <div class="bg-gray-900 text-light p-5 rounded-3 font-monospace fs-7 overflow-auto terminal-console" id="terminal-box">
<span class="text-success">[SISTEM SIAP]</span> Konsol Pembaruan Sistem PengenTani terhubung.
Branch aktif : <span class="text-info">{{ $gitBranch }}</span>
Commit aktif : <span class="text-warning">{{ $gitCommit }}</span> ({{ $gitMessage }})
PHP / Laravel: <span class="text-light">PHP {{ $phpVersion }} &bull; Laravel v{{ $laravelVersion }}</span>
--------------------------------------------------------------------------------------
Pilih aksi pembaruan di atas (Git Pull, Migrasi Database, atau Bersihkan Cache).
Output eksekusi akan ditampilkan di bawah ini secara langsung.
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
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var $terminal = $('#terminal-box');

    // Helper: Append log to terminal
    function logToTerminal(title, content, isSuccess = true) {
        var now = new Date();
        var timeStr = now.toLocaleDateString('id-ID') + ' ' + now.toLocaleTimeString('id-ID');
        var colorClass = isSuccess ? 'text-success' : 'text-danger';
        var badgeText = isSuccess ? '[BERHASIL]' : '[PERINGATAN/GAGAL]';

        var formatted = "\n\n" +
            "======================================================================================\n" +
            "[" + timeStr + "] " + title + " " + badgeText + "\n" +
            "--------------------------------------------------------------------------------------\n" +
            content;

        $terminal.append(formatted);
        $terminal.scrollTop($terminal[0].scrollHeight);
    }

    // Helper: Execute AJAX action with standard loader and terminal logging
    function runSystemAction(btnId, url, data, title, successMsg, reloadOnSuccess = false) {
        var $btn = $(btnId);
        $btn.attr('data-kt-indicator', 'on').prop('disabled', true);

        $.ajax({
            url: url,
            method: 'POST',
            data: Object.assign({ _token: csrfToken }, data || {}),
            dataType: 'json',
            success: function(res) {
                $btn.removeAttr('data-kt-indicator').prop('disabled', false);

                var isSuccess = res.success !== false;
                logToTerminal(title, res.output || res.message || '(Tidak ada output)', isSuccess);

                if (isSuccess) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message || successMsg,
                        icon: 'success',
                        confirmButtonText: 'Tutup',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    }).then(function() {
                        if (reloadOnSuccess) {
                            window.location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Perhatian',
                        text: res.message || 'Operasi selesai dengan catatan atau kendala. Silakan periksa log terminal.',
                        icon: 'warning',
                        confirmButtonText: 'Mengerti',
                        customClass: {
                            confirmButton: 'btn btn-warning'
                        }
                    });
                }
            },
            error: function(xhr) {
                $btn.removeAttr('data-kt-indicator').prop('disabled', false);

                var errText = xhr.responseJSON && xhr.responseJSON.message 
                    ? xhr.responseJSON.message 
                    : (xhr.statusText || 'Terjadi kesalahan sistem');

                logToTerminal(title + ' [ERROR HTTP ' + xhr.status + ']', errText, false);

                Swal.fire({
                    title: 'Kesalahan!',
                    text: 'Gagal menjalankan perintah: ' + errText,
                    icon: 'error',
                    confirmButtonText: 'Tutup',
                    customClass: {
                        confirmButton: 'btn btn-danger'
                    }
                });
            }
        });
    }

    // 1. Action: Git Pull
    $('#btn-git-pull').on('click', function() {
        Swal.fire({
            title: 'Tarik Pembaruan (Git Pull)?',
            text: 'Sistem akan mengeksekusi git pull dari repository GitHub untuk memperbarui berkas aplikasi.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Tarik Sekarang',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-light'
            }
        }).then(function(result) {
            if (result.isConfirmed) {
                runSystemAction(
                    '#btn-git-pull',
                    '{{ route("settings.system.pull") }}',
                    {},
                    'GIT PULL ORIGIN',
                    'Git pull berhasil dijalankan.',
                    true // Reload page to update commit info
                );
            }
        });
    });

    // 2. Action: Check Remote Updates
    $('#btn-check-remote').on('click', function() {
        runSystemAction(
            '#btn-check-remote',
            '{{ route("settings.system.check-remote") }}',
            {},
            'GIT FETCH & CEK REMOTE',
            'Pemeriksaan remote selesai.'
        );
    });

    // 3. Action: Run Migrations
    $('#btn-migrate').on('click', function() {
        Swal.fire({
            title: 'Jalankan Migrasi Database?',
            text: 'Perintah "php artisan migrate --force" akan mengeksekusi migrasi skema tabel baru.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Jalankan Migrasi',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-warning',
                cancelButton: 'btn btn-light'
            }
        }).then(function(result) {
            if (result.isConfirmed) {
                runSystemAction(
                    '#btn-migrate',
                    '{{ route("settings.system.migrate") }}',
                    {},
                    'ARTISAN MIGRATE',
                    'Migrasi database selesai.',
                    true // Reload page to update pending count
                );
            }
        });
    });

    // 4. Action: Check Migration Status
    $('#btn-migrate-status').on('click', function() {
        runSystemAction(
            '#btn-migrate-status',
            '{{ route("settings.system.migrate-status") }}',
            {},
            'ARTISAN MIGRATE:STATUS',
            'Pemeriksaan status migrasi selesai.'
        );
    });

    // 5. Action: Clear Cache
    $('#btn-clear-cache').on('click', function() {
        Swal.fire({
            title: 'Bersihkan Cache Sistem?',
            text: 'Sistem akan membersihkan cache view, config, route, dan application cache.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Bersihkan',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-light'
            }
        }).then(function(result) {
            if (result.isConfirmed) {
                runSystemAction(
                    '#btn-clear-cache',
                    '{{ route("settings.system.clear-cache") }}',
                    {},
                    'ARTISAN OPTIMIZE:CLEAR',
                    'Cache aplikasi berhasil dibersihkan.'
                );
            }
        });
    });

    // 6. Action: Detailed Git Status
    $('#btn-git-status').on('click', function() {
        runSystemAction(
            '#btn-git-status',
            '{{ route("settings.system.git-status") }}',
            {},
            'GIT STATUS & LOG',
            'Status Git berhasil dimuat.'
        );
    });

    // 7. Utility: Copy Terminal Log
    $('#btn-copy-terminal').on('click', function() {
        var text = $terminal.text();
        navigator.clipboard.writeText(text).then(function() {
            Swal.fire({
                title: 'Tersalin!',
                text: 'Log terminal berhasil disalin ke clipboard.',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        });
    });

    // 8. Utility: Clear Terminal View
    $('#btn-clear-terminal').on('click', function() {
        $terminal.html('<span class="text-muted fs-8">[Terminal dibersihkan pada ' + new Date().toLocaleTimeString('id-ID') + ']</span>');
    });
});
</script>
@endpush
