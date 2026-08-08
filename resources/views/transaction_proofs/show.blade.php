@extends('layouts.metronic')

@section('title', 'Detail Bukti Transaksi - ' . $transactionProof->name)

@section('page_title')
    Detail Bukti: <span class="text-muted fw-normal" id="header_proof_name">{{ $transactionProof->name }}</span>
@endsection

@section('page_actions')
<div class="d-flex align-items-center gap-2">
    @if(!in_array(strtolower(pathinfo($transactionProof->file_path, PATHINFO_EXTENSION)), ['pdf']))
        <a href="{{ route('transaction-proofs.edit', $transactionProof->id) }}" class="btn btn-sm fw-bold btn-warning">
            <i class="fas fa-edit me-1"></i> Edit & Coret Gambar
        </a>
    @endif
    <a href="{{ route('transaction-proofs.history', $transactionProof->id) }}" class="btn btn-sm fw-bold btn-info">
        <i class="fa fa-layer-group me-1"></i> Riwayat Versi
    </a>
    <a href="{{ route('transaction-proofs.index') }}" class="btn btn-sm fw-bold btn-secondary">
        <i class="ki-duotone ki-black-left fs-5 me-1"></i> Kembali ke Galeri
    </a>
</div>
@endsection

@section('content')
@php
    $balance = $totalIncomes - ($totalPurchases + $totalWorkerJobs);
    $purchasesCount = $transactionProof->purchaseItems->count();
    $incomesCount = $transactionProof->incomes->count();
    $workerJobsCount = $transactionProof->workerJobs->count();

    // Smart Tab Selection: Automatically activate the first tab that has data
    $defaultTab = 'purchases';
    if ($purchasesCount > 0) {
        $defaultTab = 'purchases';
    } elseif ($incomesCount > 0) {
        $defaultTab = 'incomes';
    } elseif ($workerJobsCount > 0) {
        $defaultTab = 'worker_jobs';
    }
@endphp

<div class="row g-6">
            <!-- Left Column: Image Preview Box (4 columns, Sticky) -->
            <div class="col-xl-4 text-center">
                <div class="card card-flush shadow-sm position-sticky" style="top: 90px; z-index: 5;">
                    <div class="card-body p-4">
                        <div class="position-relative overflow-hidden d-flex justify-content-center align-items-center bg-dark bg-opacity-5 rounded border shadow-2xs mb-4 p-2">
                            @if(in_array(strtolower(pathinfo($transactionProof->file_path, PATHINFO_EXTENSION)), ['pdf']))
                                <iframe src="{{ $transactionProof->url }}" class="w-100 rounded" style="border: none; min-height: 450px;"></iframe>
                            @else
                                <a href="{{ $transactionProof->url }}" data-fslightbox="gallery_detail" data-type="image" class="d-block w-100 d-flex align-items-center justify-content-center group-hover-zoom" title="Klik untuk memperbesar gambar">
                                    <img src="{{ $transactionProof->url }}" class="img-fluid rounded transition-transform shadow-xs" style="max-width: 100%; height: auto; object-fit: contain;" alt="Bukti Transaksi" />
                                </a>
                            @endif
                        </div>

                        <!-- Action Toolbar below image -->
                        <div class="d-flex gap-2 justify-content-center">
                            @if(!in_array(strtolower(pathinfo($transactionProof->file_path, PATHINFO_EXTENSION)), ['pdf']))
                                <a href="{{ $transactionProof->url }}" data-fslightbox="gallery_detail_btn" data-type="image" class="btn btn-sm btn-light-primary fw-bold px-3 py-2 fs-8">
                                    <i class="ki-duotone ki-eye fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Perbesar
                                </a>
                            @endif
                            <a href="{{ $transactionProof->url }}" target="_blank" class="btn btn-sm btn-light-info fw-bold px-3 py-2 fs-8">
                                <i class="ki-duotone ki-dots-square fs-5 me-1"></i> Tab Baru
                            </a>
                            <a href="{{ $transactionProof->url }}" download="{{ $transactionProof->name }}.{{ pathinfo($transactionProof->file_path, PATHINFO_EXTENSION) }}" class="btn btn-sm btn-primary fw-bold px-3 py-2 fs-8">
                                <i class="ki-duotone ki-file-down fs-5 me-1"></i> Download
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Summary Widgets & Transaction Tables (8 columns) -->
            <div class="col-xl-8">
                <!-- Slim Summary Header Card -->
                <div class="card card-flush shadow-sm mb-5">
                    <div class="card-body p-5">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-4">
                            <!-- Proof Title & Date -->
                            <div class="d-flex flex-column">
                                <span class="fs-8 text-gray-500 fw-semibold mb-1">Nama Bukti & Tanggal Unggah</span>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fs-5 fw-bold text-gray-800" id="display_proof_name">{{ $transactionProof->name }}</span>
                                    <button type="button" class="btn btn-icon btn-sm btn-light-primary w-24px h-24px rounded-circle" id="btn_inline_rename" title="Ganti Nama Bukti">
                                        <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span class="path2"></span></i>
                                    </button>
                                </div>
                                <span class="fs-8 text-muted mt-1">
                                    <i class="fa fa-calendar-alt text-gray-400 me-1"></i> {{ $transactionProof->created_at->format('d M Y, H:i') }}
                                </span>
                            </div>

                            <!-- Key Metrics Pills -->
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <div class="bg-light-success p-3 rounded border border-success border-opacity-25 text-end min-w-100px">
                                    <span class="fs-9 text-success fw-bold d-block text-uppercase">Pendapatan</span>
                                    <span class="fs-7 fw-bold text-success">Rp {{ number_format($totalIncomes, 0, ',', '.') }}</span>
                                </div>

                                <div class="bg-light-danger p-3 rounded border border-danger border-opacity-25 text-end min-w-100px">
                                    <span class="fs-9 text-danger fw-bold d-block text-uppercase">Pembelian</span>
                                    <span class="fs-7 fw-bold text-danger">Rp {{ number_format($totalPurchases, 0, ',', '.') }}</span>
                                </div>

                                <div class="bg-light-warning p-3 rounded border border-warning border-opacity-25 text-end min-w-100px">
                                    <span class="fs-9 text-warning fw-bold d-block text-uppercase">Upah Pekerja</span>
                                    <span class="fs-7 fw-bold text-warning">Rp {{ number_format($totalWorkerJobs, 0, ',', '.') }}</span>
                                </div>

                                <div class="p-3 rounded border {{ $balance >= 0 ? 'bg-light-success border-success' : 'bg-light-danger border-danger' }} border-opacity-25 text-end min-w-120px">
                                    <span class="fs-9 text-gray-600 fw-bold d-block text-uppercase">Sisa Aliran Dana</span>
                                    <span class="fs-6 fw-bold {{ $balance >= 0 ? 'text-success' : 'text-danger' }}">
                                        Rp {{ number_format($balance, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction Details Card -->
                <div class="card card-flush shadow-sm">
                    <div class="card-header border-0 pt-4 px-6">
                        <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-6 fw-bold" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link text-active-danger {{ $defaultTab === 'purchases' ? 'active' : '' }} py-3" data-bs-toggle="tab" href="#kt_tab_purchases" role="tab">
                                    <i class="ki-duotone ki-basket me-1 fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                    Pembelian <span class="badge {{ $purchasesCount > 0 ? 'badge-light-danger' : 'badge-light' }} fs-9 ms-1">{{ $purchasesCount }}</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link text-active-success {{ $defaultTab === 'incomes' ? 'active' : '' }} py-3" data-bs-toggle="tab" href="#kt_tab_incomes" role="tab">
                                    <i class="ki-duotone ki-dollar me-1 fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                    Pendapatan <span class="badge {{ $incomesCount > 0 ? 'badge-light-success' : 'badge-light' }} fs-9 ms-1">{{ $incomesCount }}</span>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link text-active-warning {{ $defaultTab === 'worker_jobs' ? 'active' : '' }} py-3" data-bs-toggle="tab" href="#kt_tab_worker_jobs" role="tab">
                                    <i class="ki-duotone ki-profile-user me-1 fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                    Upah Pekerja <span class="badge {{ $workerJobsCount > 0 ? 'badge-light-warning' : 'badge-light' }} fs-9 ms-1">{{ $workerJobsCount }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body p-6 pt-2">
                        <div class="tab-content">
                            <!-- Tab Purchases -->
                            <div class="tab-pane fade {{ $defaultTab === 'purchases' ? 'show active' : '' }}" id="kt_tab_purchases" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed table-hover fs-8 gy-3">
                                        <thead>
                                            <tr class="text-start text-muted fw-bold fs-9 text-uppercase gs-0">
                                                <th class="min-w-30px">No</th>
                                                <th class="min-w-80px">Tanggal</th>
                                                <th class="min-w-100px">Kebun</th>
                                                <th class="min-w-150px">Nama Item / Deskripsi</th>
                                                <th class="min-w-90px">Kategori</th>
                                                <th class="min-w-40px text-center">Qty</th>
                                                <th class="min-w-80px text-end">Harga Satuan</th>
                                                <th class="min-w-90px text-end">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-600 fw-semibold">
                                            @forelse($transactionProof->purchaseItems as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $item->purchase->date ? $item->purchase->date->format('Y-m-d') : '-' }}</td>
                                                <td><span class="text-gray-800 fw-bold">{{ $item->purchase->pertanian->kebun->name ?? '-' }}</span></td>
                                                <td>
                                                    <span class="text-gray-800 fw-bold">{{ $item->category }}</span>
                                                    @if($item->description)
                                                        <span class="d-block text-gray-400 fs-9">{{ $item->description }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-light-danger fs-9">{{ $item->purchaseCategory->name ?? '-' }}</span>
                                                </td>
                                                <td class="text-center">{{ $item->qty }}</td>
                                                <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                                <td class="text-end fw-bold text-gray-800">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-6">Tidak ada data pembelian terkait bukti ini.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                        @if($purchasesCount > 0)
                                        <tfoot>
                                            <tr class="fw-bold fs-8 text-gray-800 bg-light">
                                                <td colspan="7" class="text-end text-uppercase py-3 ps-4">Total Pembelian:</td>
                                                <td class="text-end text-danger text-nowrap py-3 pe-4">Rp {{ number_format($totalPurchases, 0, ',', '.') }}</td>
                                            </tr>
                                        </tfoot>
                                        @endif
                                    </table>
                                </div>
                            </div>

                            <!-- Tab Incomes -->
                            <div class="tab-pane fade {{ $defaultTab === 'incomes' ? 'show active' : '' }}" id="kt_tab_incomes" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed table-hover fs-8 gy-3">
                                        <thead>
                                            <tr class="text-start text-muted fw-bold fs-9 text-uppercase gs-0">
                                                <th class="min-w-30px">No</th>
                                                <th class="min-w-80px">Tanggal</th>
                                                <th class="min-w-120px">Proyek Pertanian</th>
                                                <th class="min-w-150px">Deskripsi</th>
                                                <th class="min-w-90px">Kategori</th>
                                                <th class="min-w-40px text-center">Qty</th>
                                                <th class="min-w-80px text-end">Harga Satuan</th>
                                                <th class="min-w-90px text-end">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-600 fw-semibold">
                                            @forelse($transactionProof->incomes as $index => $income)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $income->date ? $income->date->format('Y-m-d') : '-' }}</td>
                                                <td><span class="text-gray-800 fw-bold">{{ $income->pertanian->name ?? '-' }}</span></td>
                                                <td>
                                                    <span class="text-gray-800">{{ $income->description }}</span>
                                                    @if($income->tengkulak)
                                                        <span class="d-block text-gray-400 fs-9">Tengkulak: {{ $income->tengkulak->name }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-light-success fs-9">{{ $income->category->name ?? '-' }}</span>
                                                </td>
                                                <td class="text-center">{{ $income->qty }}</td>
                                                <td class="text-end">Rp {{ number_format($income->unit_price, 0, ',', '.') }}</td>
                                                <td class="text-end fw-bold text-gray-800">Rp {{ number_format($income->amount, 0, ',', '.') }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-6">Tidak ada data pendapatan terkait bukti ini.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                        @if($incomesCount > 0)
                                        <tfoot>
                                            <tr class="fw-bold fs-8 text-gray-800 bg-light">
                                                <td colspan="7" class="text-end text-uppercase py-3 ps-4">Total Pendapatan:</td>
                                                <td class="text-end text-success text-nowrap py-3 pe-4">Rp {{ number_format($totalIncomes, 0, ',', '.') }}</td>
                                            </tr>
                                        </tfoot>
                                        @endif
                                    </table>
                                </div>
                            </div>

                            <!-- Tab Worker Jobs -->
                            <div class="tab-pane fade {{ $defaultTab === 'worker_jobs' ? 'show active' : '' }}" id="kt_tab_worker_jobs" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed table-hover fs-8 gy-3">
                                        <thead>
                                            <tr class="text-start text-muted fw-bold fs-9 text-uppercase gs-0">
                                                <th class="min-w-30px">No</th>
                                                <th class="min-w-80px">Tanggal</th>
                                                <th class="min-w-100px">Pekerja</th>
                                                <th class="min-w-130px">Kategori Pekerjaan</th>
                                                <th class="min-w-120px">Proyek Pertanian</th>
                                                <th class="min-w-80px text-end">Upah</th>
                                                <th class="min-w-70px text-end">Konsumsi</th>
                                                <th class="min-w-85px text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-600 fw-semibold">
                                            @forelse($transactionProof->workerJobs as $index => $job)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $job->date }}</td>
                                                <td>
                                                    <span class="text-gray-800 fw-bold">{{ $job->worker->name ?? '-' }}</span>
                                                    @if($job->worker && $job->worker->whatsapp)
                                                        <span class="d-block text-gray-400 fs-9">{{ $job->worker->whatsapp }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-light-warning fw-bold fs-9 mb-1">{{ $job->category->name ?? '-' }}</span>
                                                    @if($job->description)
                                                        <span class="d-block fs-9 text-gray-500 text-truncate" style="max-width: 140px;" title="{{ $job->description }}">{{ $job->description }}</span>
                                                    @endif
                                                </td>
                                                <td><span class="text-gray-800 fw-bold">{{ $job->pertanian->name ?? '-' }}</span></td>
                                                <td class="text-end">Rp {{ number_format($job->wage, 0, ',', '.') }}</td>
                                                <td class="text-end">Rp {{ number_format($job->konsumsi, 0, ',', '.') }}</td>
                                                <td class="text-end fw-bold text-gray-800">Rp {{ number_format($job->wage + $job->konsumsi, 0, ',', '.') }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-6">Tidak ada data upah pekerja terkait bukti ini.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                        @if($workerJobsCount > 0)
                                        <tfoot>
                                            <tr class="fw-bold fs-8 text-gray-800 bg-light">
                                                <td colspan="5" class="text-end text-uppercase py-3 ps-4">Subtotal Upah & Konsumsi:</td>
                                                <td class="text-end text-nowrap py-3">Rp {{ number_format($totalWages, 0, ',', '.') }}</td>
                                                <td class="text-end text-nowrap py-3">Rp {{ number_format($totalKonsumsi, 0, ',', '.') }}</td>
                                                <td class="text-end text-warning text-nowrap py-3 pe-4">Rp {{ number_format($totalWorkerJobs, 0, ',', '.') }}</td>
                                            </tr>
                                        </tfoot>
                                        @endif
                                    </table>
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
<script src="{{ asset('assets/plugins/custom/fslightbox/fslightbox.bundle.js') }}"></script>
<script>
$(document).ready(function() {
    // Inline Rename Handler
    $('#btn_inline_rename').click(function() {
        let currentName = $('#display_proof_name').text();
        let newName = prompt("Masukkan nama bukti baru:", currentName);
        if (newName && newName.trim() !== '' && newName.trim() !== currentName) {
            $.ajax({
                url: "{{ route('transaction-proofs.rename', $transactionProof->id) }}",
                type: 'PATCH',
                data: {
                    _token: '{{ csrf_token() }}',
                    name: newName.trim()
                },
                success: function(res) {
                    if (res.success) {
                        $('#display_proof_name').text(res.name);
                        $('#header_proof_name').text(res.name);
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Nama bukti berhasil diperbarui.', timer: 1500, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message || 'Gagal mengubah nama.' });
                    }
                },
                error: function(xhr) {
                    let msg = 'Gagal mengubah nama.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                }
            });
        }
    });
});
</script>
@endpush
