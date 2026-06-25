@extends('layouts.metronic')

@section('title', 'Koneksi WhatsApp')

@section('content')
<div class="row g-5 g-xl-10 mb-5 mb-xl-10">
    <div class="col-xl-6">
        <div class="card card-flush h-xl-100">
            <div class="card-header pt-7">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">Status Gateway WhatsApp</span>
                    <span class="text-gray-500 mt-1 fw-semibold fs-6">Kelola koneksi bot WhatsApp (tanisync)</span>
                </h3>
            </div>
            
            <div class="card-body">
                @if($status === 'connected')
                    <div class="notice d-flex bg-light-success rounded border-success border border-dashed mb-9 p-6">
                        <i class="ki-duotone ki-check-circle fs-2tx text-success me-4">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        <div class="d-flex flex-stack flex-grow-1">
                            <div class="fw-semibold">
                                <h4 class="text-gray-900 fw-bold">WhatsApp Terhubung!</h4>
                                <div class="fs-6 text-gray-700">
                                    Bot WhatsApp berjalan lancar. Akun yang terhubung: <br>
                                    <strong>{{ $jid }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($status === 'disconnected')
                    <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed mb-9 p-6">
                        <i class="ki-duotone ki-information-5 fs-2tx text-warning me-4">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                        <div class="d-flex flex-stack flex-grow-1">
                            <div class="fw-semibold">
                                <h4 class="text-gray-900 fw-bold">WhatsApp Terputus</h4>
                                <div class="fs-6 text-gray-700">
                                    Bot WhatsApp belum terhubung. Silakan scan QR Code di bawah ini menggunakan aplikasi WhatsApp di HP Anda (Pilih Linked Devices -> Link a Device).
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($qrData)
                        <div class="text-center mt-5">
                            <h5>Scan QR Code ini:</h5>
                            <div class="border border-dashed border-gray-300 p-5 d-inline-block rounded bg-white">
                                <!-- go-whatsapp-web-multidevice usually returns qr_link (base64 or url) -->
                                @php
                                    $qrSrc = $qrData['qr_link'] ?? $qrData['qr_code'] ?? $qrData['qr'] ?? null;
                                @endphp
                                @if($qrSrc)
                                    <img src="{{ $qrSrc }}" alt="WhatsApp QR Code" class="img-fluid" style="max-width: 300px; max-height: 300px;">
                                @else
                                    <div class="alert alert-danger">Gagal memuat gambar QR Code. Coba muat ulang (refresh) halaman ini.</div>
                                    <p class="text-muted" style="font-size: 10px;">{{ json_encode($qrData) }}</p>
                                @endif
                            </div>
                            <p class="text-muted mt-3">Sistem akan secara otomatis me-refresh halaman ini dalam <span id="countdown">30</span> detik.</p>
                            <a href="{{ route('whatsapp.index') }}" class="btn btn-primary mt-2">Refresh Manual</a>
                        </div>
                    @endif
                @else
                    <div class="notice d-flex bg-light-danger rounded border-danger border border-dashed mb-9 p-6">
                        <i class="ki-duotone ki-cross-circle fs-2tx text-danger me-4">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        <div class="d-flex flex-stack flex-grow-1">
                            <div class="fw-semibold">
                                <h4 class="text-gray-900 fw-bold">Gagal Menghubungi Gateway</h4>
                                <div class="fs-6 text-gray-700">
                                    Terjadi kesalahan saat mencoba mengambil status dari gateway. Pastikan gateway WhatsApp menyala dan dapat diakses.
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card card-flush h-xl-100">
            <div class="card-header pt-7">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">Instruksi Login Ajaib (Auto Login)</span>
                </h3>
            </div>
            <div class="card-body">
                <p>Fitur Auto-Login memungkinkan pengguna untuk masuk ke dalam aplikasi secara otomatis hanya dengan mengirimkan pesan WhatsApp.</p>
                <ol class="fs-6 text-gray-700">
                    <li class="mb-2">Pastikan status gateway <strong>Terhubung</strong>.</li>
                    <li class="mb-2">Pastikan nomor pengguna sudah terdaftar di sistem dengan nomor WhatsApp aktif (awalan <code>628...</code>, <code>08...</code>, atau <code>+62...</code>).</li>
                    <li class="mb-2">Pengguna mengirimkan pesan chat dengan teks <strong><code>login</code></strong> ke nomor bot ini.</li>
                    <li class="mb-2">Sistem akan merespons dengan link unik yang otomatis kedaluwarsa dalam 5 menit.</li>
                    <li>Pengguna mengklik link tersebut dan akan langsung dialihkan ke Dashboard tanpa perlu memasukkan password.</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row g-5 g-xl-10 mb-5 mb-xl-10">
    <!-- Form Pengaturan Bukti Transaksi -->
    <div class="col-xl-6">
        <div class="card card-flush h-xl-100">
            <div class="card-header pt-7">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">Pengaturan Bukti Transaksi</span>
                    <span class="text-gray-500 mt-1 fw-semibold fs-6">Hubungkan Grup WA untuk otomatisasi bukti</span>
                </h3>
            </div>
            <div class="card-body">
                <form action="{{ route('whatsapp.settings.save') }}" method="POST">
                    @csrf
                    <div class="mb-5">
                        <label class="form-label fw-bold">ID Grup WhatsApp</label>
                        <input type="text" name="wa_proof_group_id" class="form-control" value="{{ $wa_proof_group_id }}" placeholder="Contoh: 120363041234567890@g.us" />
                        <div class="form-text">
                            Masukkan ID Grup yang akan digunakan sebagai tempat mengunggah bukti transaksi. Anda dapat mengirimkan pesan sembarang ke grup dan melihat ID-nya pada Log Webhook di samping.
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Log Webhook Viewer -->
    <div class="col-xl-6">
        <div class="card card-flush h-xl-100">
            <div class="card-header pt-7">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">Log Webhook</span>
                    <span class="text-gray-500 mt-1 fw-semibold fs-6">20 Log Terakhir</span>
                </h3>
            </div>
            <div class="card-body">
                <div class="bg-dark text-light p-4 rounded" style="max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 12px; white-space: pre-wrap;">
@forelse($webhookLogs as $log)
{{ $log }}
@empty
Tidak ada log webhook.
@endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    @if($status === 'disconnected' && $qrData)
    // Auto refresh after 30 seconds to get a fresh QR code
    let timeLeft = 30;
    const countdownEl = document.getElementById('countdown');
    setInterval(function() {
        timeLeft--;
        if (countdownEl) {
            countdownEl.textContent = timeLeft;
        }
        if (timeLeft <= 0) {
            window.location.reload();
        }
    }, 1000);
    @endif
</script>
@endpush
@endsection
