@extends('layouts.metronic')

@section('title', 'Riwayat Versi - ' . $transactionProof->name)

@section('page_title')
    Riwayat Versi: <span class="text-muted fw-normal">{{ $transactionProof->name }}</span>
@endsection

@section('page_actions')
<div class="d-flex align-items-center gap-2">
    @if(!in_array(strtolower(pathinfo($transactionProof->file_path, PATHINFO_EXTENSION)), ['pdf']))
        <a href="{{ route('transaction-proofs.edit', $transactionProof->id) }}" class="btn btn-sm fw-bold btn-warning">
            <i class="fas fa-edit me-1"></i> Edit Gambar
        </a>
    @endif
    <a href="{{ route('transaction-proofs.show', $transactionProof->id) }}" class="btn btn-sm fw-bold btn-light-primary">
        <i class="ki-duotone ki-eye fs-5 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Detail Bukti
    </a>
    <a href="{{ route('transaction-proofs.index') }}" class="btn btn-sm fw-bold btn-secondary">
        <i class="ki-duotone ki-black-left fs-5 me-1"></i> Kembali ke Galeri
    </a>
</div>
@endsection

@section('content')
<div class="row g-6">
    <!-- Left Column: Active Image Info Card (4 columns, Sticky) -->
    <div class="col-xl-4 text-center">
        <div class="card card-flush shadow-sm position-sticky" style="top: 90px; z-index: 5;">
            <div class="card-header border-0 pt-4 px-6 justify-content-center">
                <h3 class="card-title fw-bold fs-6 text-gray-800">Versi Gambar Aktif Saat Ini</h3>
            </div>
            <div class="card-body p-4 pt-0 text-center">
                <div class="position-relative overflow-hidden d-flex justify-content-center align-items-center bg-dark bg-opacity-5 rounded border shadow-2xs mb-4 p-2">
                    @if(in_array(strtolower(pathinfo($transactionProof->file_path, PATHINFO_EXTENSION)), ['pdf']))
                        <iframe src="{{ $transactionProof->url }}" class="w-100 rounded" style="border: none; min-height: 380px;"></iframe>
                    @else
                        <a href="{{ $transactionProof->url }}" data-fslightbox="gallery_active" data-type="image" class="d-block w-100 d-flex align-items-center justify-content-center group-hover-zoom" title="Klik untuk memperbesar gambar">
                            <img src="{{ $transactionProof->url }}" class="img-fluid rounded transition-transform shadow-xs" style="max-height: 420px; max-width: 100%; object-fit: contain;" alt="Gambar Aktif" />
                        </a>
                    @endif
                </div>

                <div class="fw-bold fs-5 text-gray-800 mb-2">{{ $transactionProof->name }}</div>
                <span class="badge badge-light-success fs-8 px-3 py-2 fw-bold rounded-pill mb-4">
                    <i class="fa fa-check-circle me-1 text-success"></i> Versi Utama Aktif
                </span>

                <!-- Action Toolbar -->
                <div class="d-flex gap-2 justify-content-center">
                    @if(!in_array(strtolower(pathinfo($transactionProof->file_path, PATHINFO_EXTENSION)), ['pdf']))
                        <a href="{{ $transactionProof->url }}" data-fslightbox="gallery_active_btn" data-type="image" class="btn btn-sm btn-light-primary fw-bold px-3 py-2 fs-8">
                            <i class="ki-duotone ki-eye fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Perbesar
                        </a>
                    @endif
                    <a href="{{ $transactionProof->url }}" target="_blank" class="btn btn-sm btn-light-info fw-bold px-3 py-2 fs-8">
                        <i class="ki-duotone ki-dots-square fs-5 me-1"></i> Tab Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Version & Name History Timelines (8 columns) -->
    <div class="col-xl-8">
        <div class="card card-flush shadow-sm">
            <div class="card-header border-0 pt-4 px-6">
                <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-6 fw-bold" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-info active py-3" data-bs-toggle="tab" href="#kt_tab_image_history" role="tab">
                            <i class="fa fa-layer-group me-2 fs-5"></i> Riwayat Edit Gambar <span class="badge badge-light-info fs-9 ms-1">{{ count($transactionProof->image_history ?? []) }}</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary py-3" data-bs-toggle="tab" href="#kt_tab_name_history" role="tab">
                            <i class="fa fa-history me-2 fs-5"></i> Riwayat Nama <span class="badge badge-light-primary fs-9 ms-1">{{ count($transactionProof->rename_history ?? []) }}</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body p-6 pt-2">
                <div class="tab-content">
                    <!-- Tab Image History -->
                    <div class="tab-pane fade show active" id="kt_tab_image_history" role="tabpanel">
                        @php
                            $imgHistory = array_reverse($transactionProof->image_history ?? []);
                        @endphp

                        @if(empty($imgHistory))
                            <div class="p-6 bg-light rounded-3 border text-center my-4">
                                <i class="ki-duotone ki-picture fs-3hx text-gray-400 mb-2"><span class="path1"></span><span class="path2"></span></i>
                                <div class="fw-bold text-gray-800 fs-6 mb-1">Versi 1 (Gambar Utama Asli)</div>
                                <div class="fs-8 text-muted mb-4">Belum ada riwayat editan atau coretan lanjutan pada gambar bukti transaksi ini.</div>
                                <span class="badge badge-light-primary fs-8 px-4 py-2 fw-bold rounded-pill">Versi Utama Asli</span>
                            </div>
                        @else
                            <div class="d-flex flex-column gap-4 my-2">
                                @foreach($imgHistory as $idx => $ver)
                                    @php
                                        $originalIndex = count($imgHistory) - 1 - $idx;
                                        $verNum = $ver['version'] ?? ($originalIndex + 1);
                                        $isLatest = ($idx === 0);
                                    @endphp
                                    <div class="card card-bordered shadow-2xs {{ $isLatest ? 'bg-light-info border-info border-opacity-50' : 'bg-body' }} p-4 rounded-3">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                            <div class="d-flex align-items-center gap-4">
                                                <!-- Thumbnail with Lightbox -->
                                                <a href="{{ $ver['url'] ?? '#' }}" data-fslightbox="history_list" data-type="image" title="Klik untuk memperbesar Versi {{ $verNum }}" class="position-relative overflow-hidden rounded border shadow-2xs">
                                                    <img src="{{ $ver['url'] ?? '' }}" class="rounded transition-transform" style="width: 75px; height: 75px; object-fit: cover;" alt="Versi {{ $verNum }}" />
                                                </a>

                                                <div class="d-flex flex-column">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="fw-bold text-gray-800 fs-6">Versi {{ $verNum }}</span>
                                                        @if($isLatest)
                                                            <span class="badge badge-info fs-9 px-2 py-1">Versi Terbaru</span>
                                                        @elseif($originalIndex === 0)
                                                            <span class="badge badge-secondary fs-9 px-2 py-1 text-gray-700">Gambar Asli</span>
                                                        @endif
                                                    </div>
                                                    <span class="fs-8 text-muted mt-1">
                                                        <i class="fa fa-clock text-gray-400 me-1"></i> {{ $ver['edited_at'] ?? '-' }} 
                                                        <span class="ms-2"><i class="fa fa-user text-gray-400 me-1"></i> {{ $ver['edited_by'] ?? 'User' }}</span>
                                                    </span>
                                                </div>
                                            </div>

                                            <button type="button" class="btn btn-sm {{ $isLatest ? 'btn-info' : 'btn-light-primary' }} rounded-pill px-4 btn-revert-version" 
                                                    data-revert-url="{{ route('transaction-proofs.revert-image', $transactionProof->id) }}" 
                                                    data-version-index="{{ $originalIndex }}">
                                                <i class="fa fa-undo me-1"></i> Pulihkan Versi Ini
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Tab Name History -->
                    <div class="tab-pane fade" id="kt_tab_name_history" role="tabpanel">
                        @php
                            $nameHistory = array_reverse($transactionProof->rename_history ?? []);
                        @endphp

                        @if(empty($nameHistory))
                            <div class="p-6 bg-light rounded-3 border text-center my-4">
                                <i class="ki-duotone ki-pencil fs-3hx text-gray-400 mb-2"><span class="path1"></span><span class="path2"></span></i>
                                <div class="fw-bold text-gray-800 fs-6 mb-1">Nama Asli Bukti</div>
                                <div class="fs-8 text-muted">Belum ada riwayat ganti nama pada bukti transaksi ini.</div>
                            </div>
                        @else
                            <div class="d-flex flex-column gap-3 my-2">
                                @foreach($nameHistory as $nh)
                                    <div class="p-4 bg-light rounded-3 border shadow-2xs">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <div>
                                                    <span class="fs-9 text-muted d-block text-uppercase fw-semibold">Nama Sebelumnya</span>
                                                    <span class="fw-bold text-danger text-decoration-line-through fs-7">{{ $nh['old_name'] ?? '-' }}</span>
                                                </div>
                                                <i class="ki-duotone ki-arrow-right fs-2 text-gray-500 mx-2"><span class="path1"></span><span class="path2"></span></i>
                                                <div>
                                                    <span class="fs-9 text-muted d-block text-uppercase fw-semibold">Nama Baru</span>
                                                    <span class="fw-bold text-success fs-7">{{ $nh['new_name'] ?? '-' }}</span>
                                                </div>
                                            </div>
                                            <div class="fs-9 text-muted text-end">
                                                <i class="fa fa-clock me-1"></i> {{ $nh['changed_at'] ?? '-' }}
                                                <span class="ms-2"><i class="fa fa-user me-1"></i> {{ $nh['changed_by'] ?? 'User' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
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
<script src="{{ asset('assets/plugins/custom/fslightbox/fslightbox.bundle.js') }}"></script>
<script>
$(document).ready(function() {
    $('.btn-revert-version').click(function(e) {
        e.preventDefault();
        let $btn = $(this);
        let revertUrl = $btn.attr('data-revert-url');
        let versionIdx = $btn.attr('data-version-index');

        Swal.fire({
            title: 'Pulihkan Versi Ini?',
            text: 'Gambar utama akan dikembalikan ke versi editan terdahulu ini.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Pulihkan!',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result.isConfirmed) {
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Memulihkan...');
                $.ajax({
                    url: revertUrl,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        version_index: versionIdx
                    },
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil Dipulihkan!',
                                text: res.message || 'Gambar berhasil dipulihkan.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(function() {
                                location.reload();
                            });
                        } else {
                            $btn.prop('disabled', false).html('<i class="fa fa-undo me-1"></i> Pulihkan Versi Ini');
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Gagal memulihkan versi.' });
                        }
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).html('<i class="fa fa-undo me-1"></i> Pulihkan Versi Ini');
                        let msg = 'Gagal memulihkan versi.';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                    }
                });
            }
        });
    });
});
</script>
@endpush
