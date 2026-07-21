@extends('layouts.metronic')

@section('title', 'Detail Bukti Transaksi')

@section('page_title')
    Detail Bukti: <span class="text-muted fw-normal">{{ $transactionProof->name }}</span>
@endsection

@section('page_actions')
<a href="{{ route('transaction-proofs.index') }}" class="btn btn-sm fw-bold btn-secondary">
    <i class="ki-duotone ki-black-left fs-4 me-1"></i> Kembali ke Galeri
</a>
@endsection

@section('content')
<div class="app-content flex-column-fluid">
    <div class="app-container container-fluid">
        <div class="row g-5 g-xl-8">
            <!-- Left Column: Large Preview (5 columns) -->
            <div class="col-xl-5">
                <div class="card card-flush shadow-sm h-xl-100">
                    <div class="card-header pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-800">Pratinjau Bukti Transaksi</span>
                            <span class="text-gray-500 mt-1 fw-semibold fs-7">Format berkas: {{ strtoupper(pathinfo($transactionProof->file_path, PATHINFO_EXTENSION)) }}</span>
                        </h3>
                    </div>
                    <div class="card-body pt-2 text-center">
                        <div class="mb-5 overflow-hidden d-flex justify-content-center align-items-center bg-light rounded" style="min-height: 400px;">
                            @if(in_array(strtolower(pathinfo($transactionProof->file_path, PATHINFO_EXTENSION)), ['pdf']))
                                <iframe src="{{ Storage::url($transactionProof->file_path) }}" class="w-100 rounded" style="min-height: 550px; border: none;"></iframe>
                            @else
                                <a href="{{ Storage::url($transactionProof->file_path) }}" data-fslightbox="gallery_detail" title="Klik untuk memperbesar">
                                    <img src="{{ Storage::url($transactionProof->file_path) }}" class="img-fluid rounded border shadow-sm" style="max-height: 600px; width: auto;" alt="Bukti Transaksi" />
                                </a>
                            @endif
                        </div>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ Storage::url($transactionProof->file_path) }}" target="_blank" class="btn btn-sm btn-light-primary fw-bold">
                                <i class="ki-duotone ki-dots-square fs-4 me-1"></i> Buka di Tab Baru
                            </a>
                            <a href="{{ Storage::url($transactionProof->file_path) }}" download="{{ $transactionProof->name }}.{{ pathinfo($transactionProof->file_path, PATHINFO_EXTENSION) }}" class="btn btn-sm btn-primary fw-bold">
                                <i class="ki-duotone ki-file-down fs-4 me-1"></i> Download Berkas Asli
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Transactions & Totals (7 columns) -->
            <div class="col-xl-7">
                <!-- Summary Card -->
                <div class="card card-flush shadow-sm mb-5">
                    <div class="card-header pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-800">Informasi & Ringkasan Bukti</span>
                            <span class="text-gray-500 mt-1 fw-semibold fs-7">Diunggah pada: {{ $transactionProof->created_at->format('d M Y, H:i') }}</span>
                        </h3>
                    </div>
                    <div class="card-body pt-2">
                        <div class="row g-4 mb-4">
                            <!-- Incomes Summary -->
                            <div class="col-md-4">
                                <div class="alert alert-light-success d-flex align-items-center p-4 rounded mb-0 h-100">
                                    <i class="ki-duotone ki-arrow-up fs-2hx text-success me-3"><span class="path1"></span><span class="path2"></span></i>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold text-gray-600 fs-7">Total Pendapatan</span>
                                        <span class="fw-bold text-success fs-5">Rp {{ number_format($totalIncomes, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Purchases Summary -->
                            <div class="col-md-4">
                                <div class="alert alert-light-danger d-flex align-items-center p-4 rounded mb-0 h-100">
                                    <i class="ki-duotone ki-arrow-down fs-2hx text-danger me-3"><span class="path1"></span><span class="path2"></span></i>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold text-gray-600 fs-7">Total Pembelian</span>
                                        <span class="fw-bold text-danger fs-5">Rp {{ number_format($totalPurchases, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Worker Jobs Summary -->
                            <div class="col-md-4">
                                <div class="alert alert-light-warning d-flex align-items-center p-4 rounded mb-0 h-100">
                                    <i class="ki-duotone ki-profile-user fs-2hx text-warning me-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold text-gray-600 fs-7">Total Upah Pekerja</span>
                                        <span class="fw-bold text-warning fs-5">Rp {{ number_format($totalWorkerJobs, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Grand Total Balance -->
                        @php
                            $balance = $totalIncomes - ($totalPurchases + $totalWorkerJobs);
                        @endphp
                        <div class="border rounded p-4 d-flex justify-content-between align-items-center bg-light">
                            <span class="fw-bold text-gray-800 fs-6">Sisa Aliran Dana Terkait Bukti (Pendapatan - Pengeluaran)</span>
                            <span class="fw-bold fs-4 {{ $balance >= 0 ? 'text-success' : 'text-danger' }}">
                                Rp {{ number_format($balance, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Accordion/Tabs of related transactions -->
                <div class="card card-flush shadow-sm">
                    <div class="card-header pt-5">
                        <h3 class="card-title">
                            <span class="card-label fw-bold text-gray-800">Daftar Transaksi Terkait</span>
                        </h3>
                    </div>
                    <div class="card-body pt-2">
                        <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold mb-5" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link text-active-danger active" data-bs-toggle="tab" href="#kt_tab_purchases" role="tab">
                                    Pembelian ({{ $transactionProof->purchaseItems->count() }})
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link text-active-success" data-bs-toggle="tab" href="#kt_tab_incomes" role="tab">
                                    Pendapatan ({{ $transactionProof->incomes->count() }})
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link text-active-warning" data-bs-toggle="tab" href="#kt_tab_worker_jobs" role="tab">
                                    Upah Pekerja ({{ $transactionProof->workerJobs->count() }})
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <!-- Tab Purchases -->
                            <div class="tab-pane fade show active" id="kt_tab_purchases" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-7 gy-3">
                                        <thead>
                                            <tr class="text-start text-muted fw-bold fs-8 text-uppercase gs-0">
                                                <th class="min-w-30px">No</th>
                                                <th class="min-w-100px">Tanggal</th>
                                                <th class="min-w-100px">Kebun</th>
                                                <th class="min-w-150px">Nama Item / Deskripsi</th>
                                                <th class="min-w-100px">Kategori</th>
                                                <th class="min-w-50px text-center">Qty</th>
                                                <th class="min-w-80px text-end">Harga Satuan</th>
                                                <th class="min-w-90px text-end">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-600 fw-semibold">
                                            @forelse($transactionProof->purchaseItems as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $item->purchase->date ?? '-' }}</td>
                                                <td>{{ $item->purchase->kebun->name ?? '-' }}</td>
                                                <td>
                                                    <span class="text-gray-800 fw-bold">{{ $item->category }}</span>
                                                    @if($item->description)
                                                        <span class="d-block text-gray-400 fs-8">{{ $item->description }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-light-danger">{{ $item->purchaseCategory->name ?? '-' }}</span>
                                                </td>
                                                <td class="text-center">{{ $item->qty }}</td>
                                                <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                                <td class="text-end fw-bold text-gray-800">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-5">Tidak ada data pembelian terkait bukti ini.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                        @if($transactionProof->purchaseItems->count() > 0)
                                        <tfoot>
                                            <tr class="fw-bold fs-7 text-gray-800">
                                                <td colspan="7" class="text-end text-uppercase">Total Pembelian:</td>
                                                <td class="text-end text-danger text-nowrap">Rp {{ number_format($totalPurchases, 0, ',', '.') }}</td>
                                            </tr>
                                        </tfoot>
                                        @endif
                                    </table>
                                </div>
                            </div>

                            <!-- Tab Incomes -->
                            <div class="tab-pane fade" id="kt_tab_incomes" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-7 gy-3">
                                        <thead>
                                            <tr class="text-start text-muted fw-bold fs-8 text-uppercase gs-0">
                                                <th class="min-w-30px">No</th>
                                                <th class="min-w-100px">Tanggal</th>
                                                <th class="min-w-120px">Pertanian / Proyek</th>
                                                <th class="min-w-150px">Deskripsi</th>
                                                <th class="min-w-100px">Kategori</th>
                                                <th class="min-w-50px text-center">Qty</th>
                                                <th class="min-w-80px text-end">Harga Satuan</th>
                                                <th class="min-w-90px text-end">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-600 fw-semibold">
                                            @forelse($transactionProof->incomes as $index => $income)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $income->date ? $income->date->format('Y-m-d') : '-' }}</td>
                                                <td>{{ $income->pertanian->tanaman->name ?? '-' }}</td>
                                                <td>
                                                    <span class="text-gray-800 fw-bold">{{ $income->description }}</span>
                                                    @if($income->tengkulak)
                                                        <span class="d-block text-gray-400 fs-8">Tengkulak: {{ $income->tengkulak->name }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-light-success">{{ $income->category->name ?? '-' }}</span>
                                                </td>
                                                <td class="text-center">{{ $income->qty }}</td>
                                                <td class="text-end">Rp {{ number_format($income->unit_price, 0, ',', '.') }}</td>
                                                <td class="text-end fw-bold text-gray-800">Rp {{ number_format($income->amount, 0, ',', '.') }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-5">Tidak ada data pendapatan terkait bukti ini.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                        @if($transactionProof->incomes->count() > 0)
                                        <tfoot>
                                            <tr class="fw-bold fs-7 text-gray-800">
                                                <td colspan="7" class="text-end text-uppercase">Total Pendapatan:</td>
                                                <td class="text-end text-success text-nowrap">Rp {{ number_format($totalIncomes, 0, ',', '.') }}</td>
                                            </tr>
                                        </tfoot>
                                        @endif
                                    </table>
                                </div>
                            </div>

                            <!-- Tab Worker Jobs -->
                            <div class="tab-pane fade" id="kt_tab_worker_jobs" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-7 gy-3">
                                        <thead>
                                            <tr class="text-start text-muted fw-bold fs-8 text-uppercase gs-0">
                                                <th class="min-w-30px">No</th>
                                                <th class="min-w-100px">Tanggal</th>
                                                <th class="min-w-120px">Pekerja</th>
                                                <th class="min-w-150px">Kategori & Pekerjaan</th>
                                                <th class="min-w-100px">Proyek Pertanian</th>
                                                <th class="min-w-85px text-end">Upah</th>
                                                <th class="min-w-85px text-end">Konsumsi</th>
                                                <th class="min-w-95px text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-600 fw-semibold">
                                            @forelse($transactionProof->workerJobs as $index => $job)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $job->date }}</td>
                                                <td>
                                                    <span class="text-gray-800 fw-bold">{{ $job->worker->name ?? '-' }}</span>
                                                    <span class="d-block text-gray-400 fs-8">{{ $job->worker->whatsapp ?? '-' }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-light-warning fw-bold mb-1">{{ $job->category->name ?? '-' }}</span>
                                                    <span class="d-block fs-8 text-gray-600 text-truncate" style="max-width: 150px;" title="{{ $job->description }}">{{ $job->description }}</span>
                                                </td>
                                                <td>{{ $job->pertanian->tanaman->name ?? '-' }}</td>
                                                <td class="text-end">Rp {{ number_format($job->wage, 0, ',', '.') }}</td>
                                                <td class="text-end">Rp {{ number_format($job->konsumsi, 0, ',', '.') }}</td>
                                                <td class="text-end fw-bold text-gray-800">Rp {{ number_format($job->wage + $job->konsumsi, 0, ',', '.') }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-5">Tidak ada data upah pekerja terkait bukti ini.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                        @if($transactionProof->workerJobs->count() > 0)
                                        <tfoot>
                                            <tr class="fw-bold fs-7 text-gray-800">
                                                <td colspan="5" class="text-end text-uppercase">Subtotal Upah & Konsumsi:</td>
                                                <td class="text-end text-nowrap">Rp {{ number_format($totalWages, 0, ',', '.') }}</td>
                                                <td class="text-end text-nowrap">Rp {{ number_format($totalKonsumsi, 0, ',', '.') }}</td>
                                                <td class="text-end text-warning text-nowrap">Rp {{ number_format($totalWorkerJobs, 0, ',', '.') }}</td>
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
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/custom/fslightbox/fslightbox.bundle.js') }}"></script>
@endpush
