@extends('layouts.metronic')

@section('title', 'Riwayat Versi - ' . $transactionProof->name)

@section('page_title')
    Riwayat Versi: <span class="text-muted fw-normal">{{ $transactionProof->name }}</span>
@endsection

@section('page_actions')
<div class="d-flex align-items-center gap-2">
    @if(!in_array(strtolower(pathinfo($transactionProof->file_path, PATHINFO_EXTENSION)), ['pdf']))
        <a href="{{ route('transaction-proofs.edit', $transactionProof->id) }}" class="btn btn-sm fw-bold btn-warning">
            <i class="ki-duotone ki-design-1 fs-5 me-1"><span class="path1"></span><span class="path2"></span></i> Edit Gambar
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
<div class="app-content flex-column-fluid">
    <div class="app-container container-fluid">
        <div class="row g-5">
            <!-- Left Column: Active Image Info (4 columns) -->
            <div class="col-xl-4 text-center">
                <div class="card card-flush shadow-sm mb-5">
                    <div class="card-header border-0 pt-4 px-6">
                        <h3 class="card-title fw-bold fs-6 text-gray-800">Versi Gambar Aktif</h3>
                    </div>
                    <div class="card-body p-6 pt-0">
                        <div class="mb-4 overflow-hidden d-flex justify-content-center align-items-center bg-light rounded border shadow-xs" style="height: 320px;">
                            @if(in_array(strtolower(pathinfo($transactionProof->file_path, PATHINFO_EXTENSION)), ['pdf']))
                                <iframe src="{{ $transactionProof->url }}" class="w-100 h-100 rounded" style="border: none;"></iframe>
                            @else
                                <a href="{{ $transactionProof->url }}" target="_blank" title="Klik untuk lihat ukuran asli">
                                    <img src="{{ $transactionProof->url }}" class="img-fluid rounded" style="max-height: 320px; max-width: 100%; object-fit: contain;" alt="Gambar Aktif" />
                                </a>
                            @endif
                        </div>
                        <div class="fw-bold fs-6 text-gray-800 mb-1">{{ $transactionProof->name }}</div>
                        <span class="badge badge-light-success fs-8 px-3 py-2 fw-bold">Gambar Utama Aktif Saat Ini</span>
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
                                    <i class="fa fa-layer-group me-1 fs-6"></i> Riwayat Edit Gambar ({{ count($transactionProof->image_history ?? []) }})
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link text-active-primary py-3" data-bs-toggle="tab" href="#kt_tab_name_history" role="tab">
                                    <i class="fa fa-history me-1 fs-6"></i> Riwayat Nama ({{ count($transactionProof->rename_history ?? []) }})
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body p-6">
                        <div class="tab-content">
                            <!-- Tab Image History -->
                            <div class="tab-pane fade show active" id="kt_tab_image_history" role="tabpanel">
                                @php
                                    $imgHistory = array_reverse($transactionProof->image_history ?? []);
                                @endphp

                                @if(empty($imgHistory))
                                    <div class="p-5 bg-light rounded border text-center my-4">
                                        <div class="fw-bold text-gray-800 fs-6 mb-1">Versi 1 (Gambar Asli)</div>
                                        <div class="fs-8 text-muted mb-3">Belum ada riwayat editan / coretan lanjutan pada gambar ini.</div>
                                        <span class="badge badge-light-primary fs-8 px-3 py-2 fw-bold">Gambar Utama Asli</span>
                                    </div>
                                @else
                                    <div class="d-flex flex-column gap-3">
                                        @foreach($imgHistory as $idx => $ver)
                                            @php
                                                $originalIndex = count($imgHistory) - 1 - $idx;
                                                $verNum = $ver['version'] ?? ($originalIndex + 1);
                                            @endphp
                                            <div class="d-flex align-items-center justify-content-between p-4 bg-light rounded border shadow-2xs">
                                                <div class="d-flex align-items-center gap-3">
                                                    <a href="{{ $ver['url'] ?? '#' }}" target="_blank" title="Lihat Gambar Versi {{ $verNum }}">
                                                        <img src="{{ $ver['url'] ?? '' }}" class="rounded border shadow-2xs" style="width: 60px; height: 60px; object-fit: cover;" />
                                                    </a>
                                                    <div>
                                                        <div class="fw-bold text-gray-800 fs-6">Versi {{ $verNum }}</div>
                                                        <div class="fs-8 text-muted">
                                                            <i class="fa fa-clock me-1"></i> {{ $ver['edited_at'] ?? '-' }} 
                                                            <span class="ms-1">oleh {{ $ver['edited_by'] ?? 'User' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-light-primary rounded-pill px-4 btn-revert-version" 
                                                        data-revert-url="{{ route('transaction-proofs.revert-image', $transactionProof->id) }}" 
                                                        data-version-index="{{ $originalIndex }}">
                                                    <i class="fa fa-undo me-1"></i> Pulihkan Versi Ini
                                                </button>
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
                                    <div class="p-5 bg-light rounded border text-center my-4">
                                        <div class="fw-bold text-gray-800 fs-6 mb-1">Nama Asli</div>
                                        <div class="fs-8 text-muted">Belum ada riwayat perubahan nama pada bukti transaksi ini.</div>
                                    </div>
                                @else
                                    <div class="d-flex flex-column gap-3">
                                        @foreach($nameHistory as $nh)
                                            <div class="p-4 bg-light rounded border shadow-2xs">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <span class="fs-8 text-muted d-block">Nama Lama:</span>
                                                        <span class="fw-bold text-danger text-decoration-line-through fs-7">{{ $nh['old_name'] ?? '-' }}</span>
                                                    </div>
                                                    <i class="ki-duotone ki-arrow-right fs-2 text-primary mx-3"><span class="path1"></span><span class="path2"></span></i>
                                                    <div>
                                                        <span class="fs-8 text-muted d-block">Nama Baru:</span>
                                                        <span class="fw-bold text-success fs-7">{{ $nh['new_name'] ?? '-' }}</span>
                                                    </div>
                                                </div>
                                                <div class="border-top pt-2 mt-3 text-end fs-9 text-muted">
                                                    Diubah pada {{ $nh['changed_at'] ?? '-' }} oleh {{ $nh['changed_by'] ?? 'User' }}
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
    </div>
</div>
@endsection

@push('scripts')
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
