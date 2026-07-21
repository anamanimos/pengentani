<div class="row g-5">
    <!-- Left Column: Compact Preview (4 columns) -->
    <div class="col-xl-4 text-center">
        <div class="mb-4 overflow-hidden d-flex justify-content-center align-items-center bg-light rounded border shadow-sm" style="height: 420px;">
            @if(in_array(strtolower(pathinfo($transactionProof->file_path, PATHINFO_EXTENSION)), ['pdf']))
                <iframe src="{{ Storage::url($transactionProof->file_path) }}" class="w-100 h-100 rounded" style="border: none;"></iframe>
            @else
                <a href="{{ Storage::url($transactionProof->file_path) }}" data-fslightbox="gallery_detail_modal" class="d-block w-100 h-100 d-flex align-items-center justify-content-center" title="Klik untuk memperbesar">
                    <img src="{{ Storage::url($transactionProof->file_path) }}" class="img-fluid rounded" style="max-height: 420px; max-width: 100%; object-fit: contain;" alt="Bukti Transaksi" />
                </a>
            @endif
        </div>
        <div class="d-flex gap-2 justify-content-center mb-5 mb-xl-0">
            <a href="{{ Storage::url($transactionProof->file_path) }}" target="_blank" class="btn btn-xs btn-light-primary fw-bold px-3 py-2 fs-8">
                <i class="ki-duotone ki-dots-square fs-5 me-1"></i> Tab Baru
            </a>
            <a href="{{ Storage::url($transactionProof->file_path) }}" download="{{ $transactionProof->name }}.{{ pathinfo($transactionProof->file_path, PATHINFO_EXTENSION) }}" class="btn btn-xs btn-primary fw-bold px-3 py-2 fs-8">
                <i class="ki-duotone ki-file-down fs-5 me-1"></i> Download
            </a>
        </div>
    </div>

    <!-- Right Column: Slim Summary & Tables (8 columns) -->
    <div class="col-xl-8">
        <!-- Slim Summary Bar -->
        @php
            $balance = $totalIncomes - ($totalPurchases + $totalWorkerJobs);
        @endphp
        <div class="card card-flush shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex flex-column">
                        <span class="fs-8 text-gray-500 fw-semibold">Nama Bukti & Tanggal Unggah</span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-6 fw-bold text-gray-800 modal-proof-display-name">{{ $transactionProof->name }}</span>
                            <span class="badge badge-light fs-8 text-gray-500">{{ $transactionProof->created_at->format('d M Y, H:i') }}</span>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center gap-4">
                        <div class="d-flex flex-column text-end">
                            <span class="fs-8 text-gray-500 fw-semibold">Pendapatan</span>
                            <span class="fs-7 fw-bold text-success">Rp {{ number_format($totalIncomes, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex flex-column text-end">
                            <span class="fs-8 text-gray-500 fw-semibold">Pembelian</span>
                            <span class="fs-7 fw-bold text-danger">Rp {{ number_format($totalPurchases, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex flex-column text-end">
                            <span class="fs-8 text-gray-500 fw-semibold">Upah Pekerja</span>
                            <span class="fs-7 fw-bold text-warning">Rp {{ number_format($totalWorkerJobs, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex flex-column text-end ps-3 border-start">
                            <span class="fs-8 text-gray-500 fw-semibold">Sisa Aliran Dana</span>
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
            <div class="card-body p-4 pt-3">
                <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-6 fw-bold mb-4" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-danger active py-3" data-bs-toggle="tab" href="#kt_modal_tab_purchases" role="tab">
                            Pembelian ({{ $transactionProof->purchaseItems->count() }})
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-success py-3" data-bs-toggle="tab" href="#kt_modal_tab_incomes" role="tab">
                            Pendapatan ({{ $transactionProof->incomes->count() }})
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-warning py-3" data-bs-toggle="tab" href="#kt_modal_tab_worker_jobs" role="tab">
                            Upah Pekerja ({{ $transactionProof->workerJobs->count() }})
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- Tab Purchases -->
                    <div class="tab-pane fade show active" id="kt_modal_tab_purchases" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-8 gy-2">
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
                                        <td>{{ $item->purchase->pertanian->kebun->name ?? '-' }}</td>
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
                                        <td colspan="8" class="text-center text-muted py-4">Tidak ada data pembelian terkait bukti ini.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                @if($transactionProof->purchaseItems->count() > 0)
                                <tfoot>
                                    <tr class="fw-bold fs-8 text-gray-800">
                                        <td colspan="7" class="text-end text-uppercase">Total Pembelian:</td>
                                        <td class="text-end text-danger text-nowrap">Rp {{ number_format($totalPurchases, 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Tab Incomes -->
                    <div class="tab-pane fade" id="kt_modal_tab_incomes" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-8 gy-2">
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
                                        <td colspan="8" class="text-center text-muted py-4">Tidak ada data pendapatan terkait bukti ini.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                @if($transactionProof->incomes->count() > 0)
                                <tfoot>
                                    <tr class="fw-bold fs-8 text-gray-800">
                                        <td colspan="7" class="text-end text-uppercase">Total Pendapatan:</td>
                                        <td class="text-end text-success text-nowrap">Rp {{ number_format($totalIncomes, 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Tab Worker Jobs -->
                    <div class="tab-pane fade" id="kt_modal_tab_worker_jobs" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-8 gy-2">
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
                                        <td colspan="8" class="text-center text-muted py-4">Tidak ada data upah pekerja terkait bukti ini.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                @if($transactionProof->workerJobs->count() > 0)
                                <tfoot>
                                    <tr class="fw-bold fs-8 text-gray-800">
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

<script>
    if (typeof refreshFsLightbox !== 'undefined') {
        refreshFsLightbox();
    }
</script>
