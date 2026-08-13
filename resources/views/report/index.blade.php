@extends('layouts.metronic')

@section('title', 'Laporan Gabungan Transaksi')
@section('page_title')
    <div class="d-flex align-items-center flex-row">
        Laporan Gabungan Transaksi
        <span class="badge badge-light-primary fw-bold fs-7 ms-3">
            <i class="ki-duotone ki-file-sheet text-primary fs-6 me-1"><span class="path1"></span><span class="path2"></span></i> Mode Excel
        </span>
    </div>
@endsection

@section('page_actions')
    <button type="button" id="btn-show-alert" class="btn btn-icon btn-secondary btn-sm me-3 d-none" data-bs-toggle="tooltip" title="Cara Penggunaan">
        <i class="ki-duotone ki-information-5 fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
    </button>

    <div class="btn-group">
        <button type="button" class="btn btn-icon btn-success btn-sm" onclick="document.getElementById('export-form').submit()" data-bs-toggle="tooltip" title="Ekspor Excel (.xlsx)">
            <i class="ki-duotone ki-file-down fs-2"><span class="path1"></span><span class="path2"></span></i>
        </button>
        <button type="button" class="btn btn-icon btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#columnVisibilityModal" title="Tampilkan/Sembunyikan Kolom">
            <i class="ki-duotone ki-eye fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
        </button>
        <button type="button" class="btn btn-icon btn-secondary btn-sm" id="btn-toggle-fullscreen" data-bs-toggle="tooltip" title="Mode Layar Penuh">
            <i class="ki-duotone ki-maximize fs-2"><span class="path1"></span><span class="path2"></span></i>
        </button>
    </div>

    <!-- Hidden form for export -->
    <form id="export-form" action="{{ route('report.export') }}" method="GET" class="d-none">
        @if(request('pertanian_id'))
            <input type="hidden" name="pertanian_id" value="{{ request('pertanian_id') }}">
        @endif
        @if(request('type'))
            <input type="hidden" name="type" value="{{ request('type') }}">
        @endif
        @if(request('start_date'))
            <input type="hidden" name="start_date" value="{{ request('start_date') }}">
        @endif
        @if(request('end_date'))
            <input type="hidden" name="end_date" value="{{ request('end_date') }}">
        @endif
    </form>
@endsection

@section('content')
<div class="alert alert-info d-flex align-items-center p-5 mb-5 position-relative" id="usage-alert">
    <i class="ki-duotone ki-information fs-2hx text-info me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
    <div class="d-flex flex-column flex-grow-1 pe-8">
        <h4 class="mb-1 text-info">Laporan Gabungan Transaksi (Pendapatan, Pembelian, & Upah Pekerja)</h4>
        <span>Urutan kolom tabel: **Tanggal, Jenis Transaksi, Pertanian, Kategori, Pihak Terkait, Catatan, Qty, Satuan/Upah, Konsumsi, Total, Bukti Transaksi**. Klik ikon corong pada header kolom untuk menyaring data atau klik tombol **Ekspor Excel** untuk mengunduh laporan.</span>
    </div>
    <button type="button" class="btn btn-icon btn-sm btn-active-light-info position-absolute top-0 end-0 m-3" id="btn-close-alert">
        <i class="ki-duotone ki-cross fs-1 text-info"><span class="path1"></span><span class="path2"></span></i>
    </button>
</div>

<div class="position-relative" id="spreadsheet-wrapper">
    <!-- Fullscreen Header -->
    <div class="spreadsheet-fs-header d-none">
        <div class="d-flex align-items-center">
            <h5 class="m-0 fw-bold text-gray-800">Laporan Gabungan Transaksi (Excel View)</h5>
            <span class="badge badge-light-primary fw-bold fs-8 ms-3">Gabungan All-In-One</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-icon btn-success" onclick="document.getElementById('export-form-fs').submit()" data-bs-toggle="tooltip" title="Ekspor Excel">
                    <i class="ki-duotone ki-file-down fs-2"><span class="path1"></span><span class="path2"></span></i>
                </button>
                <button type="button" class="btn btn-sm btn-icon btn-secondary d-none" id="btn-global-reset-filter-fs" data-bs-toggle="tooltip" title="Reset Filter">
                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                </button>
                <button type="button" class="btn btn-sm btn-icon btn-secondary" data-bs-toggle="modal" data-bs-target="#columnVisibilityModal" title="Tampilkan/Sembunyikan Kolom">
                    <i class="ki-duotone ki-eye fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                </button>
                <button type="button" class="btn btn-sm btn-icon btn-secondary" id="btn-exit-fullscreen" data-bs-toggle="tooltip" title="Keluar Fullscreen">
                    <i class="ki-duotone ki-arrow-down-left fs-2"><span class="path1"></span><span class="path2"></span></i>
                </button>
            </div>
            <!-- Hidden form for export fs -->
            <form id="export-form-fs" action="{{ route('report.export') }}" method="GET" class="d-none">
                @if(request('pertanian_id'))
                    <input type="hidden" name="pertanian_id" value="{{ request('pertanian_id') }}">
                @endif
                @if(request('type'))
                    <input type="hidden" name="type" value="{{ request('type') }}">
                @endif
            </form>
        </div>
    </div>

    <!-- Active Filters Display Bar -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div id="active-filters-display" class="d-flex flex-wrap align-items-center gap-2">
            <!-- Filter badges will be injected here dynamically -->
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-light-danger d-none" id="btn-global-reset-filter">
                <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i> Reset Semua Filter
            </button>
        </div>
    </div>

    <!-- Spreadsheet Mount Element -->
    <div id="spreadsheet" class="w-100 overflow-auto"></div>

    <!-- Footer Summary Totals Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between p-4 bg-light border-top sticky-bottom z-index-1 gap-3" id="spreadsheet-footer" style="bottom: 0;">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <h5 class="m-0 text-gray-800 fs-7">Total Pendapatan: <span id="total-income-amount" class="text-success fw-bolder ms-1 fs-6">Rp {{ number_format($totalIncome, 0, ',', '.') }}</span></h5>
            <span class="text-gray-400">|</span>
            <h5 class="m-0 text-gray-800 fs-7">Total Pembelian: <span id="total-purchase-amount" class="text-danger fw-bolder ms-1 fs-6">Rp {{ number_format($totalPurchase, 0, ',', '.') }}</span></h5>
            <span class="text-gray-400">|</span>
            <h5 class="m-0 text-gray-800 fs-7">Total Upah: <span id="total-worker-amount" class="text-warning fw-bolder ms-1 fs-6">Rp {{ number_format($totalWorker, 0, ',', '.') }}</span></h5>
            <span class="text-gray-400">|</span>
            <h5 class="m-0 text-gray-800 fs-7">Total Konsumsi: <span id="total-konsumsi-amount" class="text-warning fw-bolder ms-1 fs-6">Rp {{ number_format($totalKonsumsi, 0, ',', '.') }}</span></h5>
        </div>
        <div class="d-flex align-items-center gap-3">
            <h4 class="m-0 text-gray-800 fs-6">Arus Kas Bersih: <span id="total-net-amount" class="{{ $netCashflow >= 0 ? 'text-primary' : 'text-danger' }} fw-bolder ms-1 fs-5">Rp {{ number_format($netCashflow, 0, ',', '.') }}</span></h4>
        </div>
    </div>
</div>

<!-- Floating Selection Summary Bar -->
<div id="spreadsheet-selection-summary" style="display: none; position: fixed; bottom: 80px; left: 50%; transform: translate(-50%, 20px) scale(0.95); z-index: 1050; transition: all 0.2s ease-in-out; opacity: 0;">
    <div class="bg-dark text-white px-4 py-2 rounded-pill shadow-lg d-flex align-items-center gap-4 fs-7">
        <div>Banyak Sel: <span class="fw-bold text-warning sum-val-count">0</span></div>
        <div class="border-end border-gray-700 h-15px"></div>
        <div>Rata-rata: <span class="fw-bold text-info sum-val-avg">0</span></div>
        <div class="border-end border-gray-700 h-15px"></div>
        <div>Jumlah: <span class="fw-bold text-success sum-val-sum">0</span></div>
    </div>
</div>

<!-- Modal Universal Filter -->
<div class="modal fade" id="universalFilterModal" tabindex="-1" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered mw-400px">
        <div class="modal-content">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y pt-0 pb-15 px-5 px-xl-15">
                <div class="mb-13 text-center">
                    <h1 class="mb-3" id="filter-modal-title">Filter Kolom</h1>
                    <div class="text-muted fw-semibold fs-5">Pilih nilai untuk memfilter data tabel</div>
                </div>
                
                <input type="hidden" id="current-filter-col" value="">
                
                <div class="d-flex flex-column mb-8 filter-container d-none" id="filter-date-container">
                    <label class="fs-6 fw-semibold mb-2">Pilih Rentang Waktu</label>
                    <input class="form-control form-control-solid" placeholder="Pilih tanggal" id="filter-date-picker"/>
                </div>

                <div class="d-flex flex-column mb-8 filter-container d-none" id="filter-select-container">
                    <label class="fs-6 fw-semibold mb-2">Pilih Data</label>
                    <select class="form-select form-select-solid" id="filter-select-input" multiple="multiple" data-placeholder="Pilih satu atau lebih...">
                    </select>
                </div>

                <div class="d-flex flex-column mb-8 filter-container d-none" id="filter-text-container">
                    <label class="fs-6 fw-semibold mb-2">Cari Teks</label>
                    <input type="text" class="form-control form-control-solid" id="filter-text-input" placeholder="Masukkan kata kunci..."/>
                </div>

                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-sm btn-light me-3" id="btn-reset-filter">Reset Filter</button>
                    <button type="button" class="btn btn-sm btn-primary" id="btn-apply-filter">Terapkan</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Column Visibility -->
<div class="modal fade" id="columnVisibilityModal" tabindex="-1" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered mw-400px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Atur Tampilkan/Sembunyikan Kolom</h5>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-2x"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y pt-5 pb-5 px-5 px-xl-10" id="column-visibility-list">
                <!-- Checkboxes will be injected here dynamically -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    <!-- Jspreadsheet CE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jspreadsheet-ce@4/dist/jspreadsheet.min.css" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsuites@4/dist/jsuites.min.css" type="text/css" />
    <style>
        /* Fullscreen Mode */
        #spreadsheet-wrapper.fullscreen-mode {
            position: fixed !important;
            top: 0;
            left: 0;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 1040;
            background-color: var(--bs-body-bg, #fff);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        [data-bs-theme="dark"] #spreadsheet-wrapper.fullscreen-mode {
            background-color: #1e1e2d;
        }
        #spreadsheet-wrapper.fullscreen-mode .spreadsheet-fs-header {
            display: flex !important;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            border-bottom: 1px solid var(--bs-border-color, #e4e6ef);
            background-color: var(--bs-body-bg, #fff);
            flex-shrink: 0;
        }
        [data-bs-theme="dark"] #spreadsheet-wrapper.fullscreen-mode .spreadsheet-fs-header {
            border-bottom-color: #2b2b40;
            background-color: #1e1e2d;
        }
        #spreadsheet-wrapper.fullscreen-mode #spreadsheet {
            flex: 1;
            overflow: auto;
        }
        #spreadsheet-wrapper.fullscreen-mode #spreadsheet-footer {
            flex-shrink: 0;
        }
        #spreadsheet-wrapper.fullscreen-mode #btn-toggle-fullscreen {
            display: none !important;
        }
        #spreadsheet-wrapper.fullscreen-mode #btn-global-reset-filter {
            display: none !important;
        }

        .jexcel > thead > tr:first-child > td {
            font-size: 14px;
            font-weight: 600;
            background-color: #f4f6fa;
            white-space: nowrap !important;
            vertical-align: middle;
            padding-top: 10px !important;
            padding-bottom: 10px !important;
        }
        .jexcel > tbody > tr > td {
            font-size: 13px;
        }

        /* Dark Mode overrides for Jspreadsheet */
        [data-bs-theme="dark"] .jexcel_container {
            background-color: #1e1e2d;
        }
        [data-bs-theme="dark"] .jexcel {
            background-color: #1e1e2d;
            color: #dbdbf4;
            border-color: #151521 !important;
        }
        [data-bs-theme="dark"] .jexcel td {
            border-color: #151521 !important;
        }
        [data-bs-theme="dark"] .jexcel > thead > tr:first-child > td {
            background-color: #2b2b40;
            color: #ffffff;
            border-bottom: 1px solid #151521 !important;
            border-right: 1px solid #151521 !important;
        }
        [data-bs-theme="dark"] .jexcel > tbody > tr > td {
            background-color: #1e1e2d;
            color: #dbdbf4;
            border-bottom: 1px solid #151521 !important;
            border-right: 1px solid #151521 !important;
        }
        [data-bs-theme="dark"] .jexcel > tbody > tr > td.jexcel_row {
            background-color: #2b2b40;
            color: #a1a5b7;
            border-right: 1px solid #151521 !important;
            border-bottom: 1px solid #151521 !important;
        }
        [data-bs-theme="dark"] .jexcel_selectall {
            background-color: #2b2b40;
            border-right: 1px solid #151521 !important;
            border-bottom: 1px solid #151521 !important;
        }
        [data-bs-theme="dark"] .jexcel .jexcel_selected {
            background-color: rgba(9, 132, 227, 0.25) !important;
            color: #ffffff !important;
        }
        [data-bs-theme="dark"] .jexcel input,
        [data-bs-theme="dark"] .jexcel select,
        [data-bs-theme="dark"] .jexcel textarea {
            background-color: #151521 !important;
            color: #ffffff !important;
        }
    </style>
@endpush

@push('scripts')
    <!-- Jspreadsheet CE JS -->
    <script src="https://cdn.jsdelivr.net/npm/jspreadsheet-ce@4/dist/index.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsuites@4/dist/jsuites.min.js"></script>
    <script src="{{ asset('assets/plugins/custom/fslightbox/fslightbox.bundle.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Check if user previously closed usage alert
            if (localStorage.getItem('hideReportUsageAlert') === 'true') {
                $('#usage-alert').removeClass('d-flex').addClass('d-none');
                $('#btn-show-alert').removeClass('d-none');
            }

            // Handle closing usage alert and save state to localStorage
            $(document).on('click', '#btn-close-alert', function(e) {
                e.preventDefault();
                $('#usage-alert').slideUp(200, function() {
                    $(this).removeClass('d-flex').addClass('d-none');
                    $('#btn-show-alert').removeClass('d-none');
                });
                localStorage.setItem('hideReportUsageAlert', 'true');
            });

            // Handle re-opening usage alert from header button
            $('#btn-show-alert').click(function() {
                $('#usage-alert').removeClass('d-none').addClass('d-flex').hide().slideDown(200);
                $('#btn-show-alert').addClass('d-none');
                localStorage.removeItem('hideReportUsageAlert');
            });

            @php
                $pertanianData = $pertanians->map(fn($p) => ['id' => $p->id, 'name' => '[' . ($p->kebun->name ?? 'Tanpa Kebun') . '] - ' . $p->name])->toArray();
                $proofsData = isset($proofs) ? $proofs->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'url' => $p->url])->toArray() : [];

                $initialData = $reportData->map(function($item) {
                    return [
                        $item['id'],
                        $item['date'],
                        $item['type_label'],
                        $item['pertanian_id'],
                        $item['item_name'],
                        $item['party_name'],
                        $item['notes'],
                        (float) $item['qty'],
                        (float) $item['unit_price'],
                        (float) $item['konsumsi'],
                        (float) $item['total'],
                        $item['proof_id']
                    ];
                })->toArray();
            @endphp

            const pertanians = @json($pertanianData);
            const proofsData = @json($proofsData);
            const proofs = proofsData;
            const proofUrls = {};
            proofs.forEach(function(p) {
                proofUrls[p.id] = p.url;
            });

            const transactionTypes = [
                { id: 'Pendapatan', name: 'Pendapatan' },
                { id: 'Pembelian Material', name: 'Pembelian Material' },
                { id: 'Upah Pekerja', name: 'Upah Pekerja' }
            ];

            const initialData = @json($initialData);

            // Add 10 empty rows at bottom for easy entry / viewing
            for (let i = 0; i < 10; i++) {
                initialData.push(['', '', '', '', '', '', '', '', '', '', '', '']);
            }

            var activeFilters = {};
            try {
                let savedFilters = localStorage.getItem('report_filters');
                if (savedFilters && Object.keys(JSON.parse(savedFilters)).length > 0) {
                    activeFilters = JSON.parse(savedFilters);
                } else {
                    @if($startDate && $endDate)
                        activeFilters['1'] = ['{{ $startDate }}', '{{ $endDate }}'];
                    @endif
                    @if($selectedPertanianId && $selectedPertanianId !== 'all')
                        activeFilters['3'] = ['{{ $selectedPertanianId }}'];
                    @endif
                    @if($selectedType && $selectedType !== 'all')
                        @if($selectedType === 'income')
                            activeFilters['2'] = ['Pendapatan'];
                        @elseif($selectedType === 'purchase')
                            activeFilters['2'] = ['Pembelian Material'];
                        @elseif($selectedType === 'worker_job')
                            activeFilters['2'] = ['Upah Pekerja'];
                        @endif
                    @endif
                }
            } catch(e) {
                console.error('Failed to load activeFilters:', e);
            }

            var universalFilterModal = new bootstrap.Modal(document.getElementById('universalFilterModal'));
            var datePicker = flatpickr("#filter-date-picker", {
                mode: "range",
                dateFormat: "Y-m-d",
                inline: true
            });

            window.openUniversalFilter = function(e, colIndex) {
                e.stopPropagation();
                $('#current-filter-col').val(colIndex);

                var column = spreadsheet.options.columns[colIndex];
                var cleanTitle = column.title.replace(/<[^>]*>?/gm, '').trim();
                $('#filter-modal-title').html('Filter ' + cleanTitle);

                $('.filter-container').addClass('d-none');
                let currentVal = activeFilters[colIndex] || null;

                if (column.type === 'calendar') {
                    $('#filter-date-container').removeClass('d-none');
                    if (currentVal) datePicker.setDate(currentVal);
                    else datePicker.clear();
                } else if (column.type === 'dropdown') {
                    $('#filter-select-container').removeClass('d-none');
                    var select = $('#filter-select-input');
                    select.empty();

                    var source = column.source;
                    if (source) {
                        source.forEach(function(item) {
                            var id = typeof item === 'object' ? item.id : item;
                            var name = typeof item === 'object' ? item.name : item;
                            select.append(new Option(name, id));
                        });
                    }
                    select.val(currentVal || []).trigger('change');
                    select.select2({
                        dropdownParent: $('#universalFilterModal'),
                        allowClear: true
                    });
                } else {
                    $('#filter-text-container').removeClass('d-none');
                    $('#filter-text-input').val(currentVal || '');
                }

                universalFilterModal.show();
            };

            $('#btn-reset-filter').click(function() {
                var colIndex = $('#current-filter-col').val();
                delete activeFilters[colIndex];
                applyAllFilters();
                universalFilterModal.hide();
            });

            $('#btn-apply-filter').click(function() {
                var colIndex = $('#current-filter-col').val();
                var column = spreadsheet.options.columns[colIndex];

                if (column.type === 'calendar') {
                    let selectedDates = datePicker.selectedDates;
                    if (selectedDates.length === 2) activeFilters[colIndex] = [selectedDates[0], selectedDates[1]];
                    else if (selectedDates.length === 1) activeFilters[colIndex] = [selectedDates[0], selectedDates[0]];
                    else delete activeFilters[colIndex];
                } else if (column.type === 'dropdown') {
                    var val = $('#filter-select-input').val();
                    if (val && val.length > 0) activeFilters[colIndex] = val;
                    else delete activeFilters[colIndex];
                } else {
                    var val = $('#filter-text-input').val();
                    if (val) activeFilters[colIndex] = val;
                    else delete activeFilters[colIndex];
                }

                applyAllFilters();
                universalFilterModal.hide();
            });

            $('#btn-global-reset-filter, #btn-global-reset-filter-fs').click(function() {
                activeFilters = {};
                if (datePicker) datePicker.clear();
                applyAllFilters();
            });

            window.removeFilter = function(colIndex) {
                delete activeFilters[colIndex];
                applyAllFilters();
            };

            function applyAllFilters() {
                try {
                    localStorage.setItem('report_filters', JSON.stringify(activeFilters));
                } catch(e) {
                    console.error('Failed to save activeFilters:', e);
                }

                let data = spreadsheet.getData();

                if (Object.keys(activeFilters).length > 0) {
                    $('#btn-global-reset-filter, #btn-global-reset-filter-fs').removeClass('d-none');
                } else {
                    $('#btn-global-reset-filter, #btn-global-reset-filter-fs').addClass('d-none');
                }

                // Update header filter icon colors if present
                $('#spreadsheet .custom-filter-icon').each(function() {
                    var cIdx = $(this).attr('data-col');
                    if (activeFilters[cIdx]) {
                        $(this).removeClass('text-gray-500').addClass('text-success');
                    } else {
                        $(this).removeClass('text-success').addClass('text-gray-500');
                    }
                });

                // Build active filter badges directly from activeFilters object keys
                let activeFilterHtml = '';
                for (let cIdx in activeFilters) {
                    if (!activeFilters[cIdx] || !spreadsheet || !spreadsheet.options || !spreadsheet.options.columns || !spreadsheet.options.columns[cIdx]) continue;

                    let colTitle = spreadsheet.options.columns[cIdx].title.replace(/<[^>]*>?/gm, '').trim();
                    let filterVal = activeFilters[cIdx];
                    let filterText = '';

                    if (spreadsheet.options.columns[cIdx].type === 'calendar') {
                        let start = new Date(filterVal[0]);
                        let end = new Date(filterVal[1]);
                        filterText = start.toLocaleDateString('id-ID') + ' - ' + end.toLocaleDateString('id-ID');
                    } else if (spreadsheet.options.columns[cIdx].type === 'dropdown') {
                        let names = [];
                        let source = spreadsheet.options.columns[cIdx].source;
                        if (Array.isArray(filterVal)) {
                            for (let k = 0; k < filterVal.length; k++) {
                                let match = source ? source.find(s => (s.id || s) == filterVal[k]) : null;
                                if (match) names.push(match.name || match);
                                else names.push(filterVal[k]);
                            }
                            filterText = names.join(', ');
                        } else {
                            let match = source ? source.find(s => (s.id || s) == filterVal) : null;
                            filterText = match ? (match.name || match) : filterVal;
                        }
                    } else {
                        filterText = Array.isArray(filterVal) ? filterVal.join(', ') : filterVal;
                    }

                    activeFilterHtml += '<span class="badge badge-light-primary fw-bold px-3 py-2 border border-primary d-inline-flex align-items-center me-2 mb-2"><span class="text-gray-600 me-2">' + colTitle + ':</span> <span>' + filterText + '</span><i class="ki-duotone ki-cross fs-2 ms-2 cursor-pointer text-hover-danger" onclick="removeFilter(' + cIdx + ')" title="Hapus Filter"><span class="path1"></span><span class="path2"></span></i></span>';
                }

                if (activeFilterHtml !== '') {
                    $('#active-filters-display').html('<span class="text-muted fw-semibold fs-7 me-2">Filter Aktif:</span>' + activeFilterHtml);
                    $('#active-filters-display').removeClass('d-none');
                } else {
                    $('#active-filters-display').addClass('d-none');
                }

                let sumIncome = 0;
                let sumPurchase = 0;
                let sumWorker = 0;
                let sumKonsumsi = 0;

                for (let i = 0; i < data.length; i++) {
                    let rowData = data[i];

                    let isEmpty = true;
                    for (let j = 1; j <= 11; j++) {
                        if (rowData[j]) { isEmpty = false; break; }
                    }
                    if (isEmpty) {
                        spreadsheet.showRow(i);
                        continue;
                    }

                    let match = true;
                    for (let colIndex in activeFilters) {
                        let filterVal = activeFilters[colIndex];
                        let cellVal = rowData[colIndex];
                        let colType = spreadsheet.options.columns[colIndex].type;

                        if (cellVal === null || cellVal === undefined || cellVal === '') {
                            match = false;
                            break;
                        }

                        if (colType === 'calendar') {
                            let rowDate = new Date(cellVal);
                            rowDate.setHours(0,0,0,0);
                            let start = new Date(filterVal[0]); start.setHours(0,0,0,0);
                            let end = new Date(filterVal[1]); end.setHours(0,0,0,0);
                            if (rowDate < start || rowDate > end) {
                                match = false;
                                break;
                            }
                        } else if (colType === 'dropdown') {
                            let found = false;
                            for (let k = 0; k < filterVal.length; k++) {
                                if (cellVal == filterVal[k]) { found = true; break; }
                            }
                            if (!found) { match = false; break; }
                        } else {
                            if (String(cellVal).toLowerCase().indexOf(String(filterVal).toLowerCase()) === -1) {
                                match = false;
                                break;
                            }
                        }
                    }

                    if (match) {
                        spreadsheet.showRow(i);
                        var typeVal = rowData[2]; // Col 2 is Jenis Transaksi
                        var totalVal = parseFloat(String(rowData[10]).replace(/[^0-9.-]/g, '')) || 0; // Col 10 is Total
                        var konsumsiVal = parseFloat(String(rowData[9]).replace(/[^0-9.-]/g, '')) || 0; // Col 9 is Konsumsi

                        if (typeVal === 'Pendapatan') {
                            sumIncome += totalVal;
                        } else if (typeVal === 'Pembelian Material') {
                            sumPurchase += totalVal;
                        } else if (typeVal === 'Upah Pekerja') {
                            sumWorker += totalVal;
                            sumKonsumsi += konsumsiVal;
                        }
                    } else {
                        spreadsheet.hideRow(i);
                    }
                }

                var netCash = sumIncome - (sumPurchase + sumWorker);
                $('#total-income-amount').text('Rp ' + Math.round(sumIncome).toLocaleString('id-ID'));
                $('#total-purchase-amount').text('Rp ' + Math.round(sumPurchase).toLocaleString('id-ID'));
                $('#total-worker-amount').text('Rp ' + Math.round(sumWorker).toLocaleString('id-ID'));
                $('#total-konsumsi-amount').text('Rp ' + Math.round(sumKonsumsi).toLocaleString('id-ID'));
                $('#total-net-amount').text('Rp ' + Math.round(netCash).toLocaleString('id-ID'));
                if (netCash < 0) $('#total-net-amount').removeClass('text-primary').addClass('text-danger');
                else $('#total-net-amount').removeClass('text-danger').addClass('text-primary');
            }

            // Requested column order: Tanggal, Jenis Transaksi, Pertanian, Kategori, Pihak Terkait, Catatan, Qty, Satuan/Upah, Konsumsi, Total, Bukti Transaksi
            var spreadsheet = jspreadsheet(document.getElementById('spreadsheet'), {
                data: initialData,
                tableOverflow: true,
                tableHeight: '70vh',
                tableWidth: '100%',
                search: false,
                columns: [
                    { type: 'hidden', title: 'ID' },
                    { type: 'calendar', title: 'Tanggal <span class="text-danger">*</span>', width: 120, options: { format: 'YYYY-MM-DD' } },
                    { type: 'dropdown', title: 'Jenis Transaksi <span class="text-danger">*</span>', width: 160, source: transactionTypes, autocomplete: true },
                    { type: 'dropdown', title: 'Pertanian <span class="text-danger">*</span>', width: 220, source: pertanians, autocomplete: true },
                    { type: 'text', title: 'Kategori', width: 180 },
                    { type: 'text', title: 'Pihak Terkait', width: 180 },
                    { type: 'text', title: 'Catatan', width: 220 },
                    { type: 'numeric', title: 'Qty', width: 80, mask: '#,##0.00' },
                    { type: 'numeric', title: 'Satuan / Upah (Rp)', width: 150, mask: 'Rp #,##0' },
                    { type: 'numeric', title: 'Konsumsi (Rp)', width: 130, mask: 'Rp #,##0' },
                    { type: 'numeric', title: 'Total (Rp)', width: 150, mask: 'Rp #,##0', readOnly: true },
                    { type: 'dropdown', title: 'Bukti Transaksi', width: 220, source: proofs, autocomplete: true }
                ],
                onselection: function(instance, x1, y1, x2, y2) {
                    var sheetInstance = instance.jexcel || instance.jspreadsheet || spreadsheet;
                    handleSelection(sheetInstance, x1, y1, x2, y2);
                },
                updateTable: function(instance, cell, col, row, val, label, cellName) {
                    // Col 2: Jenis Transaksi Badge
                    if (col == 2 && val) {
                        if (val === 'Pendapatan') {
                            cell.innerHTML = '<span class="badge badge-light-success fw-bold fs-8 px-2 py-1">Pendapatan</span>';
                        } else if (val === 'Upah Pekerja') {
                            cell.innerHTML = '<span class="badge badge-light-warning fw-bold fs-8 px-2 py-1">Upah Pekerja</span>';
                        } else if (val === 'Pembelian Material') {
                            cell.innerHTML = '<span class="badge badge-light-info fw-bold fs-8 px-2 py-1">Pembelian</span>';
                        }
                    }

                    // Col 10: Total Nominal styling
                    if (col == 10 && val) {
                        var sheetInstance = instance.jexcel || instance.jspreadsheet || spreadsheet;
                        var typeVal = sheetInstance.getValueFromCoords(2, row);
                        if (typeVal === 'Pendapatan') {
                            cell.style.color = '#50cd89';
                            cell.style.fontWeight = 'bold';
                        } else {
                            cell.style.color = '#f1416c';
                            cell.style.fontWeight = 'bold';
                        }
                    }

                    // Col 11: Bukti Transaksi
                    if (col == 11 && val) {
                        if (proofUrls[val]) {
                            cell.innerHTML = '<span onclick="openLightbox(event, \'' + proofUrls[val] + '\')" class="cursor-pointer me-2" title="Lihat Bukti"><i class="ki-duotone ki-eye text-primary fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span> ' + label;
                        } else if (String(val).startsWith('http')) {
                            cell.innerHTML = '<a href="' + val + '" data-fslightbox="report_proofs" class="btn btn-xs btn-light-primary py-1 px-2 fs-9 fw-bold"><i class="ki-duotone ki-eye fs-8 me-1"><span class="path1"></span><span class="path2"></span></i> Bukti</a>';
                        }
                    }
                },
                onload: function() {
                    setTimeout(function() {
                        var headers = $('#spreadsheet .jexcel > thead > tr:first-child > td:not(.jexcel_selectall)');
                        headers.each(function(index) {
                            if (index >= 0 && index < spreadsheet.options.columns.length) {
                                var colIndex = index;
                                if (colIndex > 0) {
                                    var originalTitle = spreadsheet.options.columns[colIndex].title;
                                    var iconHtml = ' <i class="ki-duotone ki-filter ms-2 custom-filter-icon text-gray-500" data-col="'+colIndex+'" style="cursor: pointer;" onclick="openUniversalFilter(event, '+colIndex+')"><span class="path1"></span><span class="path2"></span></i>';
                                    $(this).html(originalTitle + iconHtml);
                                }
                            }
                        });
                        applyAllFilters();
                        initColumnVisibilityModal();
                    }, 100);
                },
                minDimensions: [12, Math.max(30, initialData.length)],
                defaultColAlign: 'left',
                allowInsertRow: true,
                allowDeleteRow: true
            });

            window.openLightbox = function(e, url) {
                e.stopPropagation();
                if (typeof refreshFsLightbox === 'function') {
                    var a = document.createElement('a');
                    a.href = url;
                    a.setAttribute('data-fslightbox', 'dynamic_gallery');
                    document.body.appendChild(a);
                    refreshFsLightbox();
                    a.click();
                    document.body.removeChild(a);
                } else {
                    window.open(url, '_blank');
                }
            };

            function handleSelection(sheetInstance, x1, y1, x2, y2) {
                var minX = Math.min(x1, x2);
                var maxX = Math.max(x1, x2);
                var minY = Math.min(y1, y2);
                var maxY = Math.max(y1, y2);

                var count = 0;
                var sum = 0;
                var hasNumeric = false;

                for (var r = minY; r <= maxY; r++) {
                    var tr = sheetInstance.tbody.children[r];
                    if (tr && (tr.style.display === 'none' || tr.classList.contains('jexcel_row_hidden') || tr.classList.contains('jss_row_hidden'))) continue;

                    for (var c = minX; c <= maxX; c++) {
                        count++;
                        var rawVal = sheetInstance.getValueFromCoords(c, r);
                        if (rawVal !== null && rawVal !== undefined && rawVal !== '') {
                            var cleanVal = String(rawVal).replace(/[^0-9.-]/g, '');
                            var num = parseFloat(cleanVal);
                            if (!isNaN(num)) {
                                sum += num;
                                hasNumeric = true;
                            }
                        }
                    }
                }

                if (count > 1) {
                    var avg = hasNumeric ? (sum / count) : 0;
                    showFloatingSummary(count, formatCurrency(sum), formatCurrency(avg));
                } else {
                    hideFloatingSummary();
                }
            }

            function formatCurrency(val) {
                return 'Rp ' + Math.round(val).toLocaleString('id-ID');
            }

            function showFloatingSummary(count, sum, avg) {
                let summaryDiv = $('#spreadsheet-selection-summary');
                summaryDiv.find('.sum-val-avg').text(avg);
                summaryDiv.find('.sum-val-count').text(count);
                summaryDiv.find('.sum-val-sum').text(sum);

                summaryDiv.show();
                summaryDiv[0].offsetHeight;
                summaryDiv.css({ opacity: '1', transform: 'translate(-50%, 0) scale(1)' });
            }

            function hideFloatingSummary() {
                let summaryDiv = $('#spreadsheet-selection-summary');
                if (summaryDiv.length > 0 && summaryDiv.css('opacity') !== '0') {
                    summaryDiv.css({ opacity: '0', transform: 'translate(-50%, 20px) scale(0.95)' });
                    setTimeout(function() {
                        if (summaryDiv.css('opacity') === '0') summaryDiv.hide();
                    }, 250);
                }
            }

            $(document).on('click', function(e) {
                if (!$(e.target).closest('#spreadsheet').length) hideFloatingSummary();
            });

            function initColumnVisibilityModal() {
                var modalList = $('#column-visibility-list');
                modalList.empty();

                spreadsheet.options.columns.forEach(function(col, index) {
                    if (index === 0) return;
                    var isChecked = (col.type !== 'hidden');
                    var itemHtml = `
                        <div class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input col-toggle-checkbox" type="checkbox" value="${index}" id="col_chk_${index}" ${isChecked ? 'checked' : ''} />
                            <label class="form-check-label fw-semibold text-gray-800" for="col_chk_${index}">
                                ${col.title.replace(/<[^>]*>?/gm, '')}
                            </label>
                        </div>
                    `;
                    modalList.append(itemHtml);
                });

                $('.col-toggle-checkbox').off('change').on('change', function() {
                    var colIndex = parseInt($(this).val());
                    if (this.checked) {
                        spreadsheet.showColumn(colIndex);
                    } else {
                        spreadsheet.hideColumn(colIndex);
                    }
                });
            }

            // Fullscreen mode logic
            var savedTableHeight = null;
            var wrapperPlaceholder = $('<div id="spreadsheet-placeholder" style="display:none;"></div>');

            function enterFullscreen() {
                var wrapper = $('#spreadsheet-wrapper');
                wrapper.before(wrapperPlaceholder);
                wrapper.appendTo('body');
                wrapper.addClass('fullscreen-mode');
                var el = document.getElementById('spreadsheet');
                if (el && el.jexcel) savedTableHeight = el.jexcel.options.tableHeight;
                $('body').css('overflow', 'hidden');
                requestAnimationFrame(function() { resizeSpreadsheetForFullscreen(); });
            }

            function exitFullscreen() {
                var wrapper = $('#spreadsheet-wrapper');
                wrapperPlaceholder.after(wrapper);
                wrapperPlaceholder.hide();
                wrapper.removeClass('fullscreen-mode');
                var el = document.getElementById('spreadsheet');
                if (el && el.jexcel && savedTableHeight) {
                    el.jexcel.options.tableHeight = savedTableHeight;
                    el.jexcel.setHeight();
                }
                $('body').css('overflow', '');
            }

            function resizeSpreadsheetForFullscreen() {
                var el = document.getElementById('spreadsheet');
                if (!el || !el.jexcel) return;
                var headerH = $('.spreadsheet-fs-header:visible').outerHeight(true) || 0;
                var footerH = $('#spreadsheet-footer:visible').outerHeight(true) || 0;
                var availableH = window.innerHeight - headerH - footerH;
                el.jexcel.options.tableHeight = availableH + 'px';
                el.jexcel.setHeight();
                $(el).find('.jexcel_content').css('max-height', availableH + 'px');
            }

            $('#btn-toggle-fullscreen').click(function() { enterFullscreen(); });
            $('#btn-exit-fullscreen').click(function() { exitFullscreen(); });
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('#spreadsheet-wrapper').hasClass('fullscreen-mode')) exitFullscreen();
            });
            $(window).on('resize', function() {
                if ($('#spreadsheet-wrapper').hasClass('fullscreen-mode')) resizeSpreadsheetForFullscreen();
            });
        });
    </script>
@endpush
