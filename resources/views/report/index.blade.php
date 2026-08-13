@extends('layouts.metronic')

@section('title', 'Laporan Gabungan Transaksi (Excel View)')

@section('page_title')
    Laporan Gabungan Transaksi <span class="text-muted fw-normal fs-7 ms-2">(Excel Interactive Spreadsheet)</span>
@endsection

@section('page_actions')
<div class="d-flex align-items-center gap-2">
    <a href="{{ route('report.export', request()->all()) }}" class="btn btn-sm btn-success fw-bold me-1">
        <i class="ki-duotone ki-file-down fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Ekspor Excel (.xlsx)
    </a>
</div>
@endsection

@push('styles')
    <!-- Jspreadsheet / Jexcel CSS -->
    <link rel="stylesheet" href="https://bossanov.uk/jspreadsheet/v4/jexcel.css" type="text/css" />
    <link rel="stylesheet" href="https://jsuites.net/v4/jsuites.css" type="text/css" />
    <style>
        .jexcel_container {
            width: 100% !important;
            box-shadow: none !important;
        }
        .jexcel {
            width: 100% !important;
            font-family: inherit !important;
        }
        .jexcel > thead > tr:first-child > td {
            background-color: #1e1e2d !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            text-align: center !important;
            vertical-align: middle !important;
            padding: 8px 10px !important;
            border: 1px solid #2b2b40 !important;
        }
        .jexcel > tbody > tr > td {
            padding: 6px 8px !important;
            vertical-align: middle !important;
            font-size: 0.875rem !important;
        }
        [data-bs-theme="dark"] .jexcel_container {
            background-color: #1e1e2d !important;
        }
        [data-bs-theme="dark"] .jexcel {
            color: #cdcdde !important;
        }
        [data-bs-theme="dark"] .jexcel td {
            border-color: #2b2b40 !important;
        }
        [data-bs-theme="dark"] .jexcel > tbody > tr > td {
            background-color: #1b1b29 !important;
        }
        [data-bs-theme="dark"] .jexcel > tbody > tr > td.jexcel_row {
            background-color: #2b2b40 !important;
            color: #92929f !important;
        }
        .badge-type-income {
            background-color: #e8fff3 !important;
            color: #50cd89 !important;
            font-weight: 700;
        }
        .badge-type-worker {
            background-color: #fff8dd !important;
            color: #ffc700 !important;
            font-weight: 700;
        }
        .badge-type-purchase {
            background-color: #f1f0fe !important;
            color: #7239ea !important;
            font-weight: 700;
        }
    </style>
@endpush

@section('content')
<div class="d-flex flex-column gap-6">

    <!-- KPI Summary Cards Row -->
    <div class="row g-4">
        <!-- Total Pendapatan -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-flush bg-light-success border-success border-opacity-25 shadow-2xs">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="fs-8 text-gray-600 fw-bold text-uppercase d-block mb-1">Total Pendapatan</span>
                        <span class="fs-2x fw-bolder text-success">Rp {{ number_format($totalIncome, 0, ',', '.') }}</span>
                    </div>
                    <div class="btn btn-icon btn-light-success w-45px h-45px rounded-circle">
                        <i class="ki-duotone ki-graph-up fs-1 text-success"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Pembelian -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-flush bg-light-danger border-danger border-opacity-25 shadow-2xs">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="fs-8 text-gray-600 fw-bold text-uppercase d-block mb-1">Total Pembelian</span>
                        <span class="fs-2x fw-bolder text-danger">Rp {{ number_format($totalPurchase, 0, ',', '.') }}</span>
                    </div>
                    <div class="btn btn-icon btn-light-danger w-45px h-45px rounded-circle">
                        <i class="ki-duotone ki-basket fs-1 text-danger"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Upah Pekerja -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-flush bg-light-warning border-warning border-opacity-25 shadow-2xs">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="fs-8 text-gray-600 fw-bold text-uppercase d-block mb-1">Total Upah Pekerja</span>
                        <span class="fs-2x fw-bolder text-warning">Rp {{ number_format($totalWorker, 0, ',', '.') }}</span>
                    </div>
                    <div class="btn btn-icon btn-light-warning w-45px h-45px rounded-circle">
                        <i class="ki-duotone ki-profile-user fs-1 text-warning"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Laba Bersih / Sisa Kas -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-flush {{ $netCashflow >= 0 ? 'bg-light-primary border-primary' : 'bg-light-danger border-danger' }} border-opacity-25 shadow-2xs">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="fs-8 text-gray-600 fw-bold text-uppercase d-block mb-1">Laba Bersih (Arus Kas)</span>
                        <span class="fs-2x fw-bolder {{ $netCashflow >= 0 ? 'text-primary' : 'text-danger' }}">Rp {{ number_format($netCashflow, 0, ',', '.') }}</span>
                    </div>
                    <div class="btn btn-icon {{ $netCashflow >= 0 ? 'btn-light-primary text-primary' : 'btn-light-danger text-danger' }} w-45px h-45px rounded-circle">
                        <i class="ki-duotone ki-wallet fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="card card-flush shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('report.index') }}" method="GET" id="report_filter_form" class="row g-3 align-items-end">
                <!-- Proyek Pertanian Filter -->
                <div class="col-md-3">
                    <label class="form-label fs-8 fw-bold text-gray-700">Proyek Pertanian</label>
                    <select name="pertanian_id" class="form-select form-select-sm form-select-solid" data-control="select2">
                        <option value="all" {{ $selectedPertanianId == 'all' || !$selectedPertanianId ? 'selected' : '' }}>Semua Proyek Pertanian</option>
                        @foreach($pertanians as $p)
                            <option value="{{ $p->id }}" {{ $selectedPertanianId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Jenis Transaksi Filter -->
                <div class="col-md-3">
                    <label class="form-label fs-8 fw-bold text-gray-700">Jenis Transaksi</label>
                    <select name="type" class="form-select form-select-sm form-select-solid">
                        <option value="all" {{ $selectedType == 'all' ? 'selected' : '' }}>Semua Transaksi (Gabungan)</option>
                        <option value="income" {{ $selectedType == 'income' ? 'selected' : '' }}>Pendapatan Saja</option>
                        <option value="purchase" {{ $selectedType == 'purchase' ? 'selected' : '' }}>Pembelian Material Saja</option>
                        <option value="worker_job" {{ $selectedType == 'worker_job' ? 'selected' : '' }}>Upah Pekerja Saja</option>
                    </select>
                </div>

                <!-- Date Range Filter -->
                <div class="col-md-4">
                    <label class="form-label fs-8 fw-bold text-gray-700">Rentang Tanggal</label>
                    <div class="d-flex gap-2 align-items-center">
                        <input type="date" name="start_date" class="form-control form-control-sm form-control-solid" value="{{ $startDate }}" placeholder="Mulai">
                        <span class="text-muted fs-8">s/d</span>
                        <input type="date" name="end_date" class="form-control form-control-sm form-control-solid" value="{{ $endDate }}" placeholder="Sampai">
                    </div>
                </div>

                <!-- Actions Button -->
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold">
                        <i class="ki-duotone ki-filter fs-5 me-1"><span class="path1"></span><span class="path2"></span></i> Filter
                    </button>
                    <a href="{{ route('report.index') }}" class="btn btn-sm btn-light fw-bold" title="Reset Filter">
                        <i class="fa fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Interactive Spreadsheet Card -->
    <div class="card card-flush shadow-sm">
        <div class="card-header border-0 pt-4 px-6 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="ki-duotone ki-file-sheet fs-1 text-primary me-1"><span class="path1"></span><span class="path2"></span></i>
                <h3 class="card-title fw-bold fs-5 text-gray-800 m-0">Tampilan Excel Transaksi</h3>
                <span class="badge badge-light-primary fs-8 fw-bold ms-2">{{ $totalRows }} Transaksi</span>
            </div>

            <div class="d-flex align-items-center gap-3">
                <!-- Search Input for Jspreadsheet -->
                <div class="position-relative">
                    <i class="ki-duotone ki-magnifier fs-4 position-absolute ms-3 top-50 translate-middle-y text-gray-500"><span class="path1"></span><span class="path2"></span></i>
                    <input type="text" id="spreadsheet_search" class="form-control form-control-sm form-control-solid ps-9 w-200px" placeholder="Cari di spreadsheet...">
                </div>

                <a href="{{ route('report.export', request()->all()) }}" class="btn btn-sm btn-light-success fw-bold">
                    <i class="ki-duotone ki-file-down fs-5 me-1"><span class="path1"></span><span class="path2"></span></i> Export Excel
                </a>
            </div>
        </div>

        <div class="card-body p-4 pt-0">
            <!-- Jspreadsheet Mount Container -->
            <div class="overflow-x-auto rounded border shadow-2xs">
                <div id="spreadsheet_container"></div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    <!-- Jspreadsheet / Jexcel JS -->
    <script src="https://bossanov.uk/jspreadsheet/v4/jexcel.js"></script>
    <script src="https://jsuites.net/v4/jsuites.js"></script>
    <script src="{{ asset('assets/plugins/custom/fslightbox/fslightbox.bundle.js') }}"></script>
    <script>
        $(document).ready(function() {
            var rawData = @json($reportData);

            // Map data into Jspreadsheet row format
            var spreadsheetData = rawData.map(function(item, index) {
                var proofHtml = item.proof_url ? `<a href="${item.proof_url}" data-fslightbox="report_proofs" class="btn btn-xs btn-light-primary py-1 px-2 fs-9 fw-bold" target="_blank">Lihat Bukti</a>` : '-';
                
                return [
                    item.id,
                    item.type_label,
                    item.date,
                    item.pertanian_name,
                    item.item_name,
                    item.party_name,
                    item.qty,
                    item.unit,
                    item.unit_price,
                    item.total,
                    proofHtml,
                    item.notes
                ];
            });

            // Initialize Jspreadsheet / Jexcel
            var spreadsheet = jspreadsheet(document.getElementById('spreadsheet_container'), {
                data: spreadsheetData,
                columns: [
                    { type: 'text', title: 'Ref ID', width: 90, readOnly: true },
                    { type: 'text', title: 'Jenis Transaksi', width: 140, readOnly: true },
                    { type: 'text', title: 'Tanggal', width: 110, readOnly: true },
                    { type: 'text', title: 'Proyek Pertanian', width: 180, readOnly: true },
                    { type: 'text', title: 'Kategori / Item', width: 180, readOnly: true },
                    { type: 'text', title: 'Pihak Terkait', width: 160, readOnly: true },
                    { type: 'numeric', title: 'Qty', width: 70, mask: '#,##0.00' },
                    { type: 'text', title: 'Satuan', width: 80 },
                    { type: 'numeric', title: 'Harga Satuan (Rp)', width: 140, mask: 'Rp #,##0' },
                    { type: 'numeric', title: 'Total (Rp)', width: 140, mask: 'Rp #,##0' },
                    { type: 'html', title: 'Bukti Transaksi', width: 110 },
                    { type: 'text', title: 'Catatan', width: 220 }
                ],
                minDimensions: [12, Math.max(10, spreadsheetData.length)],
                tableOverflow: true,
                tableHeight: '520px',
                columnSorting: true,
                contextMenu: true,
                updateTable: function(instance, cell, col, row, val, label, cellName) {
                    // Highlight Jenis Transaksi column (col 1)
                    if (col === 1) {
                        if (val === 'Pendapatan') {
                            cell.innerHTML = `<span class="badge badge-light-success fs-8 px-2 py-1">Pendapatan</span>`;
                        } else if (val === 'Upah Pekerja') {
                            cell.innerHTML = `<span class="badge badge-light-warning fs-8 px-2 py-1">Upah Pekerja</span>`;
                        } else if (val === 'Pembelian Material') {
                            cell.innerHTML = `<span class="badge badge-light-info fs-8 px-2 py-1">Pembelian</span>`;
                        }
                    }

                    // Format Total column (col 9) with color
                    if (col === 9) {
                        var typeVal = instance.jexcel.getValueFromCoords(1, row);
                        if (typeVal === 'Pendapatan') {
                            cell.style.color = '#50cd89';
                            cell.style.fontWeight = 'bold';
                        } else {
                            cell.style.color = '#f1416c';
                            cell.style.fontWeight = 'bold';
                        }
                    }
                }
            });

            // Live Search in Jspreadsheet
            $('#spreadsheet_search').on('keyup', function() {
                var query = $(this).val().toLowerCase().trim();
                var rows = $('#spreadsheet_container tbody tr');

                if (!query) {
                    rows.show();
                    return;
                }

                rows.each(function() {
                    var rowText = $(this).text().toLowerCase();
                    if (rowText.indexOf(query) !== -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Refresh Lightbox for dynamically generated image links
            if (typeof refreshFsLightbox === 'function') {
                refreshFsLightbox();
            }
        });
    </script>
@endpush
