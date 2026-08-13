@extends('layouts.metronic')

@section('title', 'Laporan Gabungan Transaksi')

@section('page_title')
    <div class="d-flex align-items-center flex-row">
        Laporan Gabungan Transaksi
        <span class="badge badge-light-primary fw-bold fs-7 ms-3">
            <i class="ki-duotone ki-file-sheet text-primary fs-6 me-1"><span class="path1"></span><span class="path2"></span></i> Tampilan Excel
        </span>
    </div>
@endsection

@section('page_actions')
    <form action="{{ route('report.index') }}" method="GET" id="report_filter_form" class="d-flex flex-wrap align-items-center gap-2 me-3">
        <!-- Proyek Pertanian Filter -->
        <select name="pertanian_id" class="form-select form-select-sm form-select-solid w-175px" onchange="this.form.submit()">
            <option value="all" {{ $selectedPertanianId == 'all' || !$selectedPertanianId ? 'selected' : '' }}>Semua Proyek Pertanian</option>
            @foreach($pertanians as $p)
                <option value="{{ $p->id }}" {{ $selectedPertanianId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
            @endforeach
        </select>

        <!-- Jenis Transaksi Filter -->
        <select name="type" class="form-select form-select-sm form-select-solid w-160px" onchange="this.form.submit()">
            <option value="all" {{ $selectedType == 'all' ? 'selected' : '' }}>Semua Transaksi</option>
            <option value="income" {{ $selectedType == 'income' ? 'selected' : '' }}>Pendapatan Saja</option>
            <option value="purchase" {{ $selectedType == 'purchase' ? 'selected' : '' }}>Pembelian Saja</option>
            <option value="worker_job" {{ $selectedType == 'worker_job' ? 'selected' : '' }}>Upah Pekerja Saja</option>
        </select>

        <!-- Date Range Filter -->
        <input type="date" name="start_date" class="form-control form-control-sm form-control-solid w-130px" value="{{ $startDate }}" onchange="this.form.submit()" placeholder="Tgl Mulai">
        <span class="text-muted fs-8">s/d</span>
        <input type="date" name="end_date" class="form-control form-control-sm form-control-solid w-130px" value="{{ $endDate }}" onchange="this.form.submit()" placeholder="Tgl Selesai">

        @if($selectedPertanianId != 'all' || $selectedType != 'all' || $startDate || $endDate)
            <a href="{{ route('report.index') }}" class="btn btn-icon btn-sm btn-light-danger" data-bs-toggle="tooltip" title="Reset Filter">
                <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
            </a>
        @endif
    </form>

    <div class="btn-group">
        <a href="{{ route('report.export', request()->all()) }}" class="btn btn-icon btn-success btn-sm me-1" data-bs-toggle="tooltip" title="Ekspor Excel (.xlsx)">
            <i class="ki-duotone ki-file-down fs-2"><span class="path1"></span><span class="path2"></span></i>
        </a>
        <button type="button" class="btn btn-icon btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#columnVisibilityModal" title="Tampilkan/Sembunyikan Kolom">
            <i class="ki-duotone ki-eye fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
        </button>
        <button type="button" class="btn btn-icon btn-secondary btn-sm" id="btn-toggle-fullscreen" data-bs-toggle="tooltip" title="Mode Layar Penuh">
            <i class="ki-duotone ki-maximize fs-2"><span class="path1"></span><span class="path2"></span></i>
        </button>
    </div>
@endsection

@section('content')
<div class="alert alert-info d-flex align-items-center p-4 mb-4" id="usage-alert">
    <i class="ki-duotone ki-information fs-2hx text-info me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
    <div class="d-flex flex-column flex-grow-1 pe-8">
        <h5 class="mb-1 text-info">Laporan Gabungan (Pendapatan, Pembelian, & Upah Pekerja)</h5>
        <span class="fs-8">Tampilan spreadsheet Excel interaktif. Gunakan filter di bagian atas untuk menyaring data atau klik tombol <b>Ekspor Excel (.xlsx)</b> untuk mengunduh berkas spreadsheet.</span>
    </div>
    <button type="button" class="btn btn-icon ms-auto" id="btn-close-alert">
        <i class="ki-duotone ki-cross fs-2x text-info"><span class="path1"></span><span class="path2"></span></i>
    </button>
</div>

<div class="position-relative" id="spreadsheet-wrapper">
    <!-- Fullscreen Header -->
    <div class="spreadsheet-fs-header d-none">
        <div class="d-flex align-items-center">
            <h5 class="m-0 fw-bold text-gray-800">Laporan Gabungan Transaksi</h5>
            <span class="badge badge-light-primary fw-bold fs-8 ms-3">Mode Excel</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('report.export', request()->all()) }}" class="btn btn-sm btn-success fw-bold me-2">
                <i class="ki-duotone ki-file-down fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Ekspor Excel (.xlsx)
            </a>
            <button type="button" class="btn btn-sm btn-icon btn-secondary" data-bs-toggle="modal" data-bs-target="#columnVisibilityModal" title="Tampilkan/Sembunyikan Kolom">
                <i class="ki-duotone ki-eye fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
            </button>
            <button type="button" class="btn btn-sm btn-icon btn-secondary" id="btn-exit-fullscreen" data-bs-toggle="tooltip" title="Keluar Fullscreen">
                <i class="ki-duotone ki-arrow-down-left fs-2"><span class="path1"></span><span class="path2"></span></i>
            </button>
        </div>
    </div>

    <!-- Main Spreadsheet Container -->
    <div id="spreadsheet" class="w-100 overflow-auto"></div>

    <!-- Footer Summary Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between p-4 bg-light border-top sticky-bottom z-index-1 gap-3" id="spreadsheet-footer" style="bottom: 0;">
        <div class="d-flex flex-wrap align-items-center gap-4">
            <span class="fs-7 fw-semibold text-gray-700">Total Pendapatan: <span class="text-success fw-bolder fs-6">Rp {{ number_format($totalIncome, 0, ',', '.') }}</span></span>
            <span class="text-gray-300">|</span>
            <span class="fs-7 fw-semibold text-gray-700">Total Pembelian: <span class="text-danger fw-bolder fs-6">Rp {{ number_format($totalPurchase, 0, ',', '.') }}</span></span>
            <span class="text-gray-300">|</span>
            <span class="fs-7 fw-semibold text-gray-700">Total Upah: <span class="text-warning fw-bolder fs-6">Rp {{ number_format($totalWorker, 0, ',', '.') }}</span></span>
        </div>
        <div>
            <span class="fs-7 fw-bold text-gray-800 me-3">Arus Kas Bersih: <span class="{{ $netCashflow >= 0 ? 'text-primary' : 'text-danger' }} fw-bolder fs-5">Rp {{ number_format($netCashflow, 0, ',', '.') }}</span></span>
            <span class="badge badge-light-dark fs-8 fw-bold">{{ $totalRows }} Baris</span>
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
            $('#btn-close-alert').click(function() {
                $('#usage-alert').fadeOut();
            });

            var rawData = @json($reportData);

            // Format initial rows for Jspreadsheet
            var spreadsheetData = rawData.map(function(item) {
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
                    item.proof_url ? item.proof_url : '',
                    item.notes
                ];
            });

            var spreadsheet = jspreadsheet(document.getElementById('spreadsheet'), {
                data: spreadsheetData,
                columns: [
                    { type: 'text', title: 'Ref ID', width: 90, readOnly: true },
                    { type: 'text', title: 'Jenis Transaksi', width: 140, readOnly: true },
                    { type: 'calendar', title: 'Tanggal', width: 110, options: { format: 'YYYY-MM-DD' }, readOnly: true },
                    { type: 'text', title: 'Proyek Pertanian', width: 220, readOnly: true },
                    { type: 'text', title: 'Kategori / Item', width: 200, readOnly: true },
                    { type: 'text', title: 'Pihak Terkait', width: 180, readOnly: true },
                    { type: 'numeric', title: 'Qty', width: 80, mask: '#,##0.00', readOnly: true },
                    { type: 'text', title: 'Satuan', width: 80, readOnly: true },
                    { type: 'numeric', title: 'Harga Satuan (Rp)', width: 150, mask: 'Rp #,##0', readOnly: true },
                    { type: 'numeric', title: 'Total Nominal (Rp)', width: 150, mask: 'Rp #,##0', readOnly: true },
                    { type: 'html', title: 'Bukti Transaksi', width: 130, readOnly: true },
                    { type: 'text', title: 'Catatan', width: 250, readOnly: true }
                ],
                tableHeight: '70vh',
                tableWidth: '100%',
                search: true,
                pagination: 50,
                columnSorting: true,
                contextMenu: true,
                updateTable: function(instance, cell, col, row, val, label, cellName) {
                    // Col 1: Jenis Transaksi Badges
                    if (col === 1) {
                        if (val === 'Pendapatan') {
                            cell.innerHTML = '<span class="badge badge-light-success fw-bold fs-8 px-2 py-1">Pendapatan</span>';
                        } else if (val === 'Upah Pekerja') {
                            cell.innerHTML = '<span class="badge badge-light-warning fw-bold fs-8 px-2 py-1">Upah Pekerja</span>';
                        } else if (val === 'Pembelian Material') {
                            cell.innerHTML = '<span class="badge badge-light-info fw-bold fs-8 px-2 py-1">Pembelian</span>';
                        }
                    }

                    // Col 9: Total Nominal styling
                    if (col === 9) {
                        var sheetInstance = instance.jexcel || instance.jspreadsheet || spreadsheet;
                        var typeVal = sheetInstance.getValueFromCoords(1, row);
                        if (typeVal === 'Pendapatan') {
                            cell.style.color = '#50cd89';
                            cell.style.fontWeight = 'bold';
                        } else {
                            cell.style.color = '#f1416c';
                            cell.style.fontWeight = 'bold';
                        }
                    }

                    // Col 10: Bukti Transaksi Lightbox link
                    if (col === 10 && val && val.trim() !== '') {
                        cell.innerHTML = `<a href="${val}" data-fslightbox="report_proofs" class="btn btn-xs btn-light-primary py-1 px-2 fs-9 fw-bold"><i class="ki-duotone ki-eye fs-8 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Bukti</a>`;
                    }
                },
                onselection: function(instance, x1, y1, x2, y2) {
                    var sheetInstance = instance.jexcel || instance.jspreadsheet || spreadsheet;
                    handleSelection(sheetInstance, x1, y1, x2, y2);
                },
                minDimensions: [12, Math.max(20, spreadsheetData.length)],
                defaultColAlign: 'left',
                allowInsertRow: false,
                allowDeleteRow: false
            });

            // Floating Selection Summary Handler
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
                    if (tr && (tr.style.display === 'none' || tr.classList.contains('jexcel_row_hidden') || tr.classList.contains('jss_row_hidden'))) {
                        continue;
                    }

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

            // Column Visibility Modal Logic
            function initColumnVisibilityModal() {
                var modalList = $('#column-visibility-list');
                modalList.empty();

                spreadsheet.options.columns.forEach(function(col, index) {
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

                $('.col-toggle-checkbox').on('change', function() {
                    var colIndex = parseInt($(this).val());
                    if (this.checked) {
                        spreadsheet.showColumn(colIndex);
                    } else {
                        spreadsheet.hideColumn(colIndex);
                    }
                });
            }

            initColumnVisibilityModal();

            // Fullscreen toggle logic
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
