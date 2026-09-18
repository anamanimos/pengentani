@extends('layouts.metronic')

@section('title', 'Backup Database & Telegram')

@section('page_title')
    Pengaturan Sistem <span class="text-gray-500 fw-semibold fs-7 ms-2">(Backup Database Terkompresi & Otomatisasi Telegram)</span>
@endsection

@section('content')
<div class="app-content flex-column-fluid pb-15">
    <div class="app-container container-fluid">
        <!-- Session Notifications -->
        @if(session('success'))
        <div class="alert alert-success d-flex align-items-center p-5 mb-5 rounded-3 shadow-xs">
            <i class="ki-duotone ki-check-circle fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-success">Berhasil</h4>
                <span>{{ session('success') }}</span>
            </div>
        </div>
        @endif

        @if(session('warning'))
        <div class="alert alert-warning d-flex align-items-center p-5 mb-5 rounded-3 shadow-xs">
            <i class="ki-duotone ki-information fs-2hx text-warning me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-warning">Peringatan</h4>
                <span>{{ session('warning') }}</span>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center p-5 mb-5 rounded-3 shadow-xs">
            <i class="ki-duotone ki-cross-circle fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span></i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-danger">Gagal</h4>
                <span>{{ session('error') }}</span>
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center p-5 mb-5 rounded-3 shadow-xs">
            <i class="ki-duotone ki-information fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-danger">Terjadi Kesalahan Input</h4>
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
                                <i class="ki-duotone ki-cloud-change fs-2 me-3 {{ request()->routeIs('settings.storage.*') ? 'text-primary' : 'text-gray-500' }}"><span class="path1"></span><span class="path2"></span></i>
                                <div class="d-flex flex-column">
                                    <span class="fs-7 fw-bold">Storage (R2)</span>
                                    <span class="fs-9 text-muted">Monitoring & Cloud Storage</span>
                                </div>
                            </a>

                            <!-- Backup Database Menu Item (Active) -->
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

            <!-- 9 Columns: Main Backup Content Area -->
            <div class="col-xl-9 col-lg-8">
                <!-- Stat Cards -->
                <div class="row g-4 mb-6">
                    <div class="col-sm-6 col-xl-3">
                        <div class="card card-flush shadow-sm bg-body">
                            <div class="card-body p-5">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-45px me-4">
                                        <span class="symbol-label bg-light-primary">
                                            <i class="ki-duotone ki-file-down fs-2x text-primary"><span class="path1"></span><span class="path2"></span></i>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="fs-2hx fw-bold text-gray-900 lh-1">{{ $totalFiles }}</span>
                                        <span class="text-gray-500 fw-semibold fs-7 mt-1">Total File Backup</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card card-flush shadow-sm bg-body">
                            <div class="card-body p-5">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-45px me-4">
                                        <span class="symbol-label bg-light-success">
                                            <i class="ki-duotone ki-hard-drive fs-2x text-success"><span class="path1"></span><span class="path2"></span></i>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="fs-2hx fw-bold text-gray-900 lh-1">{{ $formattedTotalSize }}</span>
                                        <span class="text-gray-500 fw-semibold fs-7 mt-1">Kapasitas Penyimpanan</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card card-flush shadow-sm bg-body">
                            <div class="card-body p-5">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-45px me-4">
                                        <span class="symbol-label bg-light-info">
                                            <i class="ki-duotone ki-send fs-2x text-info"><span class="path1"></span><span class="path2"></span></i>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        @if($isTelegramConfigured)
                                            <span class="badge badge-light-success fw-bold fs-7 align-self-start">Aktif</span>
                                            <span class="text-gray-500 fw-semibold fs-7 mt-1">Bot Telegram Terhubung</span>
                                        @else
                                            <span class="badge badge-light-danger fw-bold fs-7 align-self-start">Belum Diatur</span>
                                            <span class="text-gray-500 fw-semibold fs-7 mt-1">Telegram Belum Aktif</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card card-flush shadow-sm bg-body">
                            <div class="card-body p-5">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-45px me-4">
                                        <span class="symbol-label bg-light-warning">
                                            <i class="ki-duotone ki-time fs-2x text-warning"><span class="path1"></span><span class="path2"></span></i>
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        @if($schedule === 'daily')
                                            <span class="badge badge-light-primary fw-bold fs-7 align-self-start">Setiap Hari ({{ $scheduleTime }})</span>
                                        @elseif($schedule === 'weekly')
                                            <span class="badge badge-light-primary fw-bold fs-7 align-self-start">Mingguan ({{ $scheduleTime }})</span>
                                        @elseif($schedule === 'monthly')
                                            <span class="badge badge-light-primary fw-bold fs-7 align-self-start">Bulanan ({{ $scheduleTime }})</span>
                                        @else
                                            <span class="badge badge-light-secondary fw-bold fs-7 align-self-start">Nonaktif</span>
                                        @endif
                                        <span class="text-gray-500 fw-semibold fs-7 mt-1">Jadwal Backup Otomatis</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Action Bar Card -->
                <div class="card card-flush shadow-sm mb-6">
                    <div class="card-body p-6">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-4">
                            <div class="d-flex flex-column">
                                <h4 class="fw-bold text-gray-900 mb-1">Aksi Cepat Backup Database</h4>
                                <span class="text-muted fs-7">Cadangkan database MySQL ke format kompresi gzip (<code>.sql.gz</code>) secara instan.</span>
                            </div>
                            <div class="d-flex flex-wrap gap-3">
                                <!-- Direct Download Backup -->
                                <form action="{{ route('settings.backup.create') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="direct_download" value="1">
                                    <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center" id="btn-backup-download">
                                        <i class="ki-duotone ki-file-down fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>
                                        Backup & Unduh Sekarang
                                    </button>
                                </form>

                                <!-- Backup & Send to Telegram -->
                                <form action="{{ route('settings.backup.create') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="send_telegram" value="1">
                                    <button type="submit" class="btn btn-info btn-sm d-flex align-items-center" {{ !$isTelegramConfigured ? 'disabled' : '' }} title="{{ !$isTelegramConfigured ? 'Konfigurasikan Bot Token & Chat ID terlebih dahulu' : '' }}">
                                        <i class="ki-duotone ki-send fs-3 me-2"><span class="path1"></span><span class="path2"></span></i>
                                        Backup & Kirim ke Telegram
                                    </button>
                                </form>

                                <!-- Create and Save Local Backup -->
                                <form action="{{ route('settings.backup.create') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-light btn-sm d-flex align-items-center">
                                        <i class="ki-duotone ki-plus fs-3 me-2"></i>
                                        Simpan Backup Lokal
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Telegram & Scheduling Configuration -->
                <div class="card card-flush shadow-sm mb-6">
                    <div class="card-header border-0 pt-6">
                        <h3 class="card-title fw-bold text-gray-800">
                            <i class="ki-duotone ki-technology-4 fs-2 me-2 text-info"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span><span class="path7"></span></i>
                            Konfigurasi Telegram & Jadwal Otomatis
                        </h3>
                    </div>
                    <div class="card-body pt-2">
                        <form action="{{ route('settings.backup.save-settings') }}" method="POST" id="form-telegram-settings">
                            @csrf
                            <div class="row g-5 mb-5">
                                <!-- Telegram Bot Token -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold fs-7 required">Telegram Bot Token</label>
                                    <div class="input-group input-group-solid">
                                        <span class="input-group-text bg-light-info text-info border-0">
                                            <i class="ki-duotone ki-key fs-3"><span class="path1"></span><span class="path2"></span></i>
                                        </span>
                                        <input type="password" class="form-control form-control-solid fs-7" id="telegram_bot_token" name="telegram_bot_token" value="{{ old('telegram_bot_token', $botToken) }}" placeholder="Contoh: 123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ">
                                        <button class="btn btn-light-secondary border-0 px-3" type="button" onclick="toggleTokenVisibility()">
                                            <i class="ki-duotone ki-eye fs-4" id="toggle-token-icon"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                        </button>
                                    </div>
                                    <div class="text-muted fs-8 mt-1">Dapatkan bot token dari <a href="https://t.me/BotFather" target="_blank" class="text-primary fw-bold">@BotFather</a> di Telegram.</div>
                                </div>

                                <!-- Telegram Chat ID -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold fs-7 required">Telegram Chat ID / Group ID</label>
                                    <div class="input-group input-group-solid">
                                        <span class="input-group-text bg-light-info text-info border-0">
                                            <i class="ki-duotone ki-messages fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                        </span>
                                        <input type="text" class="form-control form-control-solid fs-7" id="telegram_chat_id" name="telegram_chat_id" value="{{ old('telegram_chat_id', $chatId) }}" placeholder="Contoh: 123456789 atau -1001234567890">
                                    </div>
                                    <div class="text-muted fs-8 mt-1">Gunakan Chat ID pribadi atau ID Grup/Channel (dapatkan via <a href="https://t.me/userinfobot" target="_blank" class="text-primary fw-bold">@userinfobot</a>).</div>
                                </div>
                            </div>

                            <div class="row g-5 mb-6">
                                <!-- Automated Schedule -->
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold fs-7">Jadwal Backup Otomatis</label>
                                    <select class="form-select form-select-solid fs-7" name="telegram_backup_schedule">
                                        <option value="disabled" {{ $schedule === 'disabled' ? 'selected' : '' }}>🚫 Nonaktif (Manual Saja)</option>
                                        <option value="daily" {{ $schedule === 'daily' ? 'selected' : '' }}>⏰ Setiap Hari (Daily)</option>
                                        <option value="weekly" {{ $schedule === 'weekly' ? 'selected' : '' }}>📅 Setiap Minggu (Weekly - Hari Minggu)</option>
                                        <option value="monthly" {{ $schedule === 'monthly' ? 'selected' : '' }}>🗓️ Setiap Bulan (Monthly - Tanggal 1)</option>
                                    </select>
                                    <div class="text-muted fs-8 mt-1">Frekuensi pengiriman backup otomatis ke Telegram.</div>
                                </div>

                                <!-- Schedule Execution Time -->
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold fs-7">Jam Eksekusi (WIB)</label>
                                    <div class="input-group input-group-solid">
                                        <span class="input-group-text bg-light border-0">
                                            <i class="ki-duotone ki-time fs-4"><span class="path1"></span><span class="path2"></span></i>
                                        </span>
                                        <input type="time" class="form-control form-control-solid fs-7" name="telegram_backup_time" value="{{ old('telegram_backup_time', $scheduleTime) }}">
                                    </div>
                                    <div class="text-muted fs-8 mt-1">Disarankan waktu sepi aktivitas (misal: 02:00 WIB).</div>
                                </div>

                                <!-- Old Backup Retention -->
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold fs-7">Pembersihan Otomatis Storage</label>
                                    <select class="form-select form-select-solid fs-7" name="telegram_backup_retention">
                                        <option value="0" {{ $retentionDays === 0 ? 'selected' : '' }}>Simpan Selamanya (Jangan Hapus)</option>
                                        <option value="3" {{ $retentionDays === 3 ? 'selected' : '' }}>Hapus jika lebih dari 3 Hari</option>
                                        <option value="7" {{ $retentionDays === 7 ? 'selected' : '' }}>Hapus jika lebih dari 7 Hari (Rekomendasi)</option>
                                        <option value="14" {{ $retentionDays === 14 ? 'selected' : '' }}>Hapus jika lebih dari 14 Hari</option>
                                        <option value="30" {{ $retentionDays === 30 ? 'selected' : '' }}>Hapus jika lebih dari 30 Hari</option>
                                    </select>
                                    <div class="text-muted fs-8 mt-1">Mencegah penyimpanan server penuh.</div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap align-items-center justify-content-between pt-4 border-top gap-3">
                                <button type="button" class="btn btn-light-info btn-sm" id="btn-test-telegram">
                                    <span class="indicator-label d-flex align-items-center">
                                        <i class="ki-duotone ki-send fs-4 me-2"><span class="path1"></span><span class="path2"></span></i>
                                        Test Koneksi Telegram
                                    </span>
                                    <span class="indicator-progress">
                                        Menguji koneksi... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                    </span>
                                </button>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="ki-duotone ki-check fs-3 me-1"><span class="path1"></span><span class="path2"></span></i>
                                    Simpan Pengaturan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Backup History Table Card -->
                <div class="card card-flush shadow-sm">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <h3 class="fw-bold text-gray-800 d-flex align-items-center gap-2">
                                <i class="ki-duotone ki-archive fs-2 text-primary"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                <span>Riwayat File Backup Database</span>
                                <span class="badge badge-light-primary fw-bold fs-8 ms-2">{{ count($backups) }} File</span>
                            </h3>
                        </div>
                    </div>
                    <div class="card-body pt-2 pb-6">
                        @if(empty($backups))
                            <div class="text-center py-10">
                                <i class="ki-duotone ki-files-tablet fs-4x text-muted mb-3"><span class="path1"></span><span class="path2"></span></i>
                                <h5 class="text-gray-700 fw-bold">Belum Ada File Backup</h5>
                                <p class="text-muted fs-7 mb-4">Klik tombol "Backup & Unduh Sekarang" atau "Simpan Backup Lokal" di atas untuk membuat cadangan database.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-row-dashed table-row-gray-300 align-middle gs-4 gy-4">
                                    <thead>
                                        <tr class="fw-bold text-muted bg-light">
                                            <th class="ps-4 min-w-40px rounded-start">No</th>
                                            <th class="min-w-220px">Nama File Backup</th>
                                            <th class="min-w-100px text-center">Ukuran</th>
                                            <th class="min-w-160px">Tanggal Pembuatan</th>
                                            <th class="min-w-140px text-end pe-4 rounded-end">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($backups as $index => $b)
                                        <tr>
                                            <td class="ps-4 text-muted fw-semibold">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-35px me-3">
                                                        <span class="symbol-label bg-light-primary text-primary fw-bold">
                                                            <i class="ki-duotone ki-data fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                        </span>
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-gray-900 fw-bold fs-7">{{ $b['filename'] }}</span>
                                                        <span class="text-muted fs-8">Format: Kompresi Gzip (.sql.gz)</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-light-success fw-bold fs-7">{{ $b['formatted_size'] }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-800 fw-semibold fs-7">{{ $b['created_at_formatted'] }}</span>
                                                    <span class="text-muted fs-8">{{ $b['time_ago'] }}</span>
                                                </div>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="btn-group gap-1">
                                                    <!-- Download Button -->
                                                    <a href="{{ route('settings.backup.download', $b['filename']) }}" class="btn btn-icon btn-light-primary btn-sm" data-bs-toggle="tooltip" title="Unduh File (.sql.gz)">
                                                        <i class="ki-duotone ki-file-down fs-4"><span class="path1"></span><span class="path2"></span></i>
                                                    </a>

                                                    <!-- Send to Telegram Button -->
                                                    <button type="button" class="btn btn-icon btn-light-info btn-sm btn-send-single-telegram" data-filename="{{ $b['filename'] }}" data-bs-toggle="tooltip" title="Kirim Ulang ke Telegram" {{ !$isTelegramConfigured ? 'disabled' : '' }}>
                                                        <i class="ki-duotone ki-send fs-4"><span class="path1"></span><span class="path2"></span></i>
                                                    </button>

                                                    <!-- Delete Button -->
                                                    <button type="button" class="btn btn-icon btn-light-danger btn-sm btn-delete-backup" data-filename="{{ $b['filename'] }}" data-bs-toggle="tooltip" title="Hapus File Backup">
                                                        <i class="ki-duotone ki-trash fs-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleTokenVisibility() {
        const input = document.getElementById('telegram_bot_token');
        const icon = document.getElementById('toggle-token-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('ki-eye');
            icon.classList.add('ki-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('ki-eye-slash');
            icon.classList.add('ki-eye');
        }
    }

    $(document).ready(function() {
        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();

        // Test Telegram Connection
        $('#btn-test-telegram').on('click', function() {
            const btn = $(this);
            const token = $('#telegram_bot_token').val();
            const chatId = $('#telegram_chat_id').val();

            if (!token) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Token Belum Diisi',
                    text: 'Silakan isi Telegram Bot Token terlebih dahulu.',
                    customClass: { confirmButton: 'btn btn-primary' }
                });
                return;
            }

            btn.attr('data-kt-indicator', 'on').prop('disabled', true);

            $.ajax({
                url: '{{ route("settings.backup.test-telegram") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    token: token,
                    chat_id: chatId
                },
                success: function(response) {
                    btn.removeAttr('data-kt-indicator').prop('disabled', false);
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Koneksi Berhasil!',
                            html: response.message,
                            customClass: { confirmButton: 'btn btn-primary' }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Koneksi Gagal',
                            html: response.message,
                            customClass: { confirmButton: 'btn btn-primary' }
                        });
                    }
                },
                error: function(xhr) {
                    btn.removeAttr('data-kt-indicator').prop('disabled', false);
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan Server',
                        text: xhr.responseJSON?.message || 'Gagal menghubungi server.',
                        customClass: { confirmButton: 'btn btn-primary' }
                    });
                }
            });
        });

        // Send Single Backup File to Telegram
        $('.btn-send-single-telegram').on('click', function() {
            const btn = $(this);
            const filename = btn.data('filename');

            Swal.fire({
                title: 'Kirim ke Telegram?',
                text: `Kirim file backup "${filename}" ke Telegram sekarang?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Kirim!',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-info',
                    cancelButton: 'btn btn-light'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Mengirim...',
                        text: 'Sedang mengunggah file backup ke Telegram...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: `{{ url('/console/settings/backup/send-telegram') }}/${filename}`,
                        method: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil Terkirim!',
                                    text: response.message,
                                    customClass: { confirmButton: 'btn btn-primary' }
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal Mengirim',
                                    text: response.message,
                                    customClass: { confirmButton: 'btn btn-primary' }
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Kesalahan Server',
                                text: xhr.responseJSON?.message || 'Gagal mengirim file backup.',
                                customClass: { confirmButton: 'btn btn-primary' }
                            });
                        }
                    });
                }
            });
        });

        // Delete Backup File with SweetAlert2 Confirmation
        $('.btn-delete-backup').on('click', function() {
            const filename = $(this).data('filename');

            Swal.fire({
                title: 'Hapus File Backup?',
                text: `Apakah Anda yakin ingin menghapus "${filename}"? Tindakan ini tidak dapat dibatalkan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-light'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ url('/console/settings/backup/delete') }}/${filename}`,
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus!',
                                    text: response.message,
                                    customClass: { confirmButton: 'btn btn-primary' }
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: response.message,
                                    customClass: { confirmButton: 'btn btn-primary' }
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Kesalahan Server',
                                text: xhr.responseJSON?.message || 'Gagal menghapus file backup.',
                                customClass: { confirmButton: 'btn btn-primary' }
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
