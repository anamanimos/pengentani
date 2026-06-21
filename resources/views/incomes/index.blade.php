@extends('layouts.metronic')

@section('title', 'Pencatatan Pendapatan')
@section('page_title')
    <div class="d-flex align-items-center flex-row">
        Pencatatan Pendapatan
        <span id="auto-save-status" class="badge badge-light-success fw-bold fs-7 ms-3 d-none">
            <i class="fas fa-check-circle text-success me-1"></i> <span class="status-text">Tersimpan Otomatis</span>
        </span>
    </div>
@endsection

@section('page_actions')
    <a href="{{ route('tengkulaks.index') }}" class="btn btn-light-success btn-sm me-3 fw-bold">
        <i class="ki-duotone ki-address-book fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Kelola Tengkulak
    </a>
    <button type="button" id="btn-show-alert" class="btn btn-icon btn-light-info btn-sm me-3 d-none" title="Cara Penggunaan">
        <i class="ki-duotone ki-information-5 fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
    </button>
@endsection

@section('content')
<div class="alert alert-info d-flex align-items-center p-5 mb-5" id="usage-alert">
    <i class="ki-duotone ki-information fs-2hx text-info me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
    <div class="d-flex flex-column flex-grow-1 pe-8">
        <h4 class="mb-1 text-info">Cara Penggunaan</h4>
        <span>Klik pada sel tabel untuk mengisi atau mengubah data. Anda bisa melakukan <i>copy-paste</i> dari Excel. Klik kanan pada baris untuk menambah baris kosong atau menghapus data baris. Data akan <b>disimpan otomatis</b> setiap kali Anda selesai mengetik atau mengubah nilai.</span>
    </div>
    <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" id="btn-close-alert">
        <i class="ki-duotone ki-cross fs-2x text-info"><span class="path1"></span><span class="path2"></span></i>
    </button>
</div>

<div class="position-relative">
    <div class="d-flex justify-content-end mb-2">
        <button type="button" class="btn btn-sm btn-light-danger d-none" id="btn-global-reset-filter">
            <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i> Reset Semua Filter
        </button>
    </div>
    <div id="spreadsheet" class="w-100 overflow-auto"></div>
    <div class="d-flex justify-content-end align-items-center p-4 bg-light border-top sticky-bottom z-index-1" style="bottom: 0;">
        <h4 class="m-0 text-gray-800">Total Pendapatan: <span id="total-amount" class="text-success fw-bolder ms-2">Rp 0</span></h4>
    </div>
</div>

<!-- Modal Universal Filter -->
<div class="modal fade" id="universalFilterModal" tabindex="-1" aria-hidden="true">
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
                    <label class="fs-6 fw-semibold mb-2">Pilih Opsi</label>
                    <select class="form-select form-select-solid" data-control="select2" data-placeholder="Pilih opsi..." id="filter-select-input" multiple="multiple">
                    </select>
                </div>

                <div class="d-flex flex-column mb-8 filter-container d-none" id="filter-text-container">
                    <label class="fs-6 fw-semibold mb-2">Pencarian Teks</label>
                    <input type="text" class="form-control form-control-solid" placeholder="Ketik kata kunci pencarian..." id="filter-text-input"/>
                </div>

                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-sm btn-light me-3" id="btn-reset-filter">Reset Filter</button>
                    <button type="button" class="btn btn-sm btn-primary" id="btn-apply-filter">Terapkan</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
    <!-- Jspreadsheet CE -->
    <link rel="stylesheet" href="https://bossanova.uk/jspreadsheet/v4/jexcel.css" type="text/css" />
    <link rel="stylesheet" href="https://jsuites.net/v4/jsuites.css" type="text/css" />
    <style>
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

        /* Mobile Bottom Sheet Layout for jSuites Dropdown Searchbar */
        .jdropdown-searchbar.jdropdown-focus {
            position: fixed !important;
            top: 0px !important;
            left: 0px !important;
            width: 100% !important;
            height: 100% !important;
            background-color: transparent !important; /* Transparent wrapper to allow full-screen backdrop click */
            z-index: 9001 !important;
            overflow: hidden !important;
        }

        /* Show full screen backdrop */
        .jdropdown-searchbar.jdropdown-focus .jdropdown-backdrop {
            display: block !important;
            background-color: rgba(0, 0, 0, 0.4) !important;
        }

        /* Make container act as the bottom-sheet itself */
        .jdropdown-searchbar.jdropdown-focus .jdropdown-container {
            position: absolute !important;
            bottom: 0px !important;
            top: auto !important;
            left: 0px !important;
            width: 100% !important;
            height: 50vh !important; /* Setengah layar */
            border-top-left-radius: 16px !important;
            border-top-right-radius: 16px !important;
            box-shadow: 0px -5px 15px rgba(0, 0, 0, 0.15) !important;
            overflow: hidden !important;
            display: block !important;
            margin-top: 0px !important;
        }

        /* Position the search header at the top of the bottom sheet */
        .jdropdown-searchbar.jdropdown-focus .jdropdown-container-header {
            position: absolute !important;
            top: 0px !important;
            left: 0px !important;
            width: 100% !important;
            height: 56px !important;
            z-index: 9002 !important;
            padding: 10px !important;
            box-shadow: 0 1px 2px rgba(0,0,0,.05) !important;
        }

        .jdropdown-searchbar.jdropdown-focus .jdropdown-header {
            height: 36px !important;
            line-height: 36px !important;
            padding-left: 30px !important;
            padding-right: 60px !important;
            margin: 0px !important;
            width: calc(100% - 70px) !important; /* Make space for Done button */
            display: inline-block !important;
        }

        .jdropdown-searchbar.jdropdown-focus .jdropdown-close {
            position: absolute !important;
            top: 0px !important;
            right: 0px !important;
            height: 56px !important;
            line-height: 56px !important;
            padding: 0 15px !important;
            background-color: transparent !important;
            box-shadow: none !important;
        }

        /* Make the content list scrollable inside the bottom sheet */
        .jdropdown-searchbar.jdropdown-focus .jdropdown-content {
            position: absolute !important;
            top: 56px !important;
            bottom: 0px !important;
            left: 0px !important;
            width: 100% !important;
            height: calc(100% - 56px) !important;
            overflow-y: auto !important;
            max-height: none !important;
            margin-top: 0px !important;
            padding-bottom: 20px !important;
        }

        /* Mobile Bottom Sheet Layout for JSuites standard picker */
        .jdropdown-picker.jdropdown-focus .jdropdown-container {
            border-top-left-radius: 16px !important;
            border-top-right-radius: 16px !important;
            box-shadow: 0px -5px 15px rgba(0, 0, 0, 0.15) !important;
            height: 40vh !important;
            overflow: hidden !important;
        }
        .jdropdown-picker.jdropdown-focus .jdropdown-content {
            height: 100% !important;
            max-height: none !important;
            overflow-y: auto !important;
        }

        .jdropdown-container,
        .jdropdown-picker,
        .jdropdown-picker .jdropdown-container,
        .jdropdown-searchbar,
        .jdropdown-searchbar.jdropdown-focus {
            background-color: #ffffff !important;
            color: #181C32 !important;
        }

        .jdropdown-header,
        .jdropdown-picker .jdropdown-header,
        .jdropdown-container-header,
        .jdropdown-searchbar.jdropdown-focus .jdropdown-container-header {
            background-color: #ffffff !important;
            color: #181C32 !important;
            border-bottom: 1px solid #f1f3f9 !important;
        }

        .jdropdown-content,
        .jdropdown-picker .jdropdown-content,
        .jdropdown-searchbar.jdropdown-focus .jdropdown-content {
            background-color: #ffffff !important;
        }

        .jdropdown-item,
        .jdropdown-picker .jdropdown-item,
        .jdropdown-searchbar .jdropdown-item {
            background-color: #ffffff !important;
            color: #181C32 !important;
            border-bottom: 1px solid #f1f3f9 !important;
        }

        .jdropdown-item:hover,
        .jdropdown-cursor,
        .jdropdown-picker .jdropdown-cursor,
        .jdropdown-searchbar .jdropdown-cursor {
            background-color: #f1f3f9 !important;
            color: #181C32 !important;
        }

        .jdropdown-selected,
        .jdropdown-picker .jdropdown-selected,
        .jdropdown-searchbar .jdropdown-selected {
            background-color: #3e97ff !important;
            color: #ffffff !important;
        }

        .jdropdown-searchbar .jdropdown-group,
        .jdropdown-searchbar.jdropdown-focus .jdropdown-group {
            background-color: #ffffff !important;
        }

        .jdropdown-group-name,
        .jdropdown-picker .jdropdown-group-name,
        .jdropdown-searchbar .jdropdown-group-name {
            background-color: #f8f9fa !important;
            color: #5e6278 !important;
            border-top: 1px solid #f1f3f9 !important;
            border-bottom: 1px solid #f1f3f9 !important;
        }

        .jdropdown-picker .jdropdown-close,
        .jdropdown-searchbar .jdropdown-close {
            background-color: #ffffff !important;
            color: #3e97ff !important;
        }

        .jdropdown-container input,
        .jdropdown-searchbar input {
            background-color: #ffffff !important;
            color: #181C32 !important;
            border: 1px solid #e4e6ef !important;
        }

        /* Dark Mode */
        [data-bs-theme="dark"] .jdropdown-container,
        [data-bs-theme="dark"] .jdropdown-picker,
        [data-bs-theme="dark"] .jdropdown-picker .jdropdown-container,
        [data-bs-theme="dark"] .jdropdown-searchbar,
        [data-bs-theme="dark"] .jdropdown-searchbar.jdropdown-focus {
            background-color: #1e1e2d !important;
            color: #dbdbf4 !important;
            border: 1px solid #2b2b40 !important;
        }

        [data-bs-theme="dark"] .jdropdown-header,
        [data-bs-theme="dark"] .jdropdown-picker .jdropdown-header,
        [data-bs-theme="dark"] .jdropdown-container-header,
        [data-bs-theme="dark"] .jdropdown-searchbar.jdropdown-focus .jdropdown-container-header {
            background-color: #2b2b40 !important;
            color: #ffffff !important;
            border-bottom: 1px solid #151521 !important;
        }

        [data-bs-theme="dark"] .jdropdown-content,
        [data-bs-theme="dark"] .jdropdown-picker .jdropdown-content,
        [data-bs-theme="dark"] .jdropdown-searchbar.jdropdown-focus .jdropdown-content {
            background-color: #1e1e2d !important;
        }

        [data-bs-theme="dark"] .jdropdown-item,
        [data-bs-theme="dark"] .jdropdown-picker .jdropdown-item,
        [data-bs-theme="dark"] .jdropdown-searchbar .jdropdown-item {
            background-color: #1e1e2d !important;
            color: #dbdbf4 !important;
            border-bottom: 1px solid #151521 !important;
        }

        [data-bs-theme="dark"] .jdropdown-item:hover,
        [data-bs-theme="dark"] .jdropdown-cursor,
        [data-bs-theme="dark"] .jdropdown-picker .jdropdown-cursor,
        [data-bs-theme="dark"] .jdropdown-searchbar .jdropdown-cursor {
            background-color: rgba(9, 132, 227, 0.15) !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .jdropdown-selected,
        [data-bs-theme="dark"] .jdropdown-picker .jdropdown-selected,
        [data-bs-theme="dark"] .jdropdown-searchbar .jdropdown-selected {
            background-color: rgba(9, 132, 227, 0.25) !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .jdropdown-searchbar .jdropdown-group,
        [data-bs-theme="dark"] .jdropdown-searchbar.jdropdown-focus .jdropdown-group {
            background-color: #1e1e2d !important;
        }

        [data-bs-theme="dark"] .jdropdown-group-name,
        [data-bs-theme="dark"] .jdropdown-picker .jdropdown-group-name,
        [data-bs-theme="dark"] .jdropdown-searchbar .jdropdown-group-name {
            background-color: #2b2b40 !important;
            color: #ffffff !important;
            border-top: 1px solid #151521 !important;
            border-bottom: 1px solid #151521 !important;
        }

        [data-bs-theme="dark"] .jdropdown-picker .jdropdown-close,
        [data-bs-theme="dark"] .jdropdown-searchbar .jdropdown-close {
            background-color: #2b2b40 !important;
            color: #3b82f6 !important;
        }

        [data-bs-theme="dark"] .jdropdown-container input,
        [data-bs-theme="dark"] .jdropdown-searchbar input {
            background-color: #151521 !important;
            color: #ffffff !important;
            border: 1px solid #2b2b40 !important;
        }
    </style>
@endpush

@push('scripts')
    <!-- Jspreadsheet CE -->
    <script src="https://bossanova.uk/jspreadsheet/v4/jexcel.js"></script>
    <script src="https://jsuites.net/v4/jsuites.js"></script>

    <script>
        $(document).ready(function() {
            $('[data-control="select2"]').select2();

            @php
                $pertanianData = $pertanians->map(fn($p) => ['id' => $p->id, 'name' => '[' . ($p->kebun->name ?? 'Tanpa Kebun') . '] - ' . $p->name])->toArray();
                $tengkulakData = $tengkulaks->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->toArray();
                
                $initialData = $incomes->map(function($income) {
                    return [
                        $income->id,
                        $income->date,
                        $income->pertanian_id,
                        $income->tengkulak_id,
                        $income->type,
                        $income->description,
                        $income->amount
                    ];
                })->toArray();
            @endphp

            const pertanians = @json($pertanianData);
            const tengkulaks = @json($tengkulakData);
            const types = [
                { id: 'Panen', name: 'Panen' },
                { id: 'Lain-lain', name: 'Lain-lain' }
            ];

            const initialData = @json($initialData);

            if (initialData.length === 0) {
                initialData.push(['', '', '', '', 'Panen', '', '']);
            }

            function updateTotal() {
                if (!spreadsheet) return;
                let data = spreadsheet.getData();
                let rows = $('#spreadsheet > div > table > tbody > tr');
                let total = 0;
                for(let i=0; i<data.length; i++) {
                    if (rows.length === 0 || rows.eq(i).is(':visible')) {
                        let amountStr = data[i][6] !== null && data[i][6] !== '' ? String(data[i][6]).replace(/[^0-9.-]/g, '') : '0';
                        let val = parseFloat(amountStr);
                        if(!isNaN(val)) total += val;
                    }
                }
                $('#total-amount').text('Rp ' + new Intl.NumberFormat('id-ID').format(total));
            }

            var spreadsheet = jspreadsheet(document.getElementById('spreadsheet'), {
                data: initialData,
                tableOverflow: true,
                tableHeight: '70vh',
                tableWidth: '100%',
                columns: [
                    { type: 'hidden', title: 'ID' },
                    { type: 'calendar', title: 'Tanggal', width: 120, options: { format: 'YYYY-MM-DD' } },
                    { type: 'dropdown', title: 'Pertanian', width: 250, source: pertanians },
                    { type: 'dropdown', title: 'Tengkulak', width: 200, source: tengkulaks },
                    { type: 'dropdown', title: 'Kategori', width: 150, source: types },
                    { type: 'text', title: 'Deskripsi', width: 300 },
                    { type: 'numeric', title: 'Nominal (Rp)', width: 150, mask: 'Rp #,##0' }
                ],
                minDimensions: [7, {{ count($incomes) > 20 ? count($incomes) + 10 : 30 }}],
                defaultColAlign: 'left',
                allowInsertRow: true,
                allowManualInsertRow: true,
                allowInsertColumn: false,
                allowDeleteRow: true,
                allowDeleteColumn: false,
                wordWrap: false,
                onchange: function() {
                    updateTotal();
                    autoSave();
                },
                oninsertrow: function() {
                    updateTotal();
                    autoSave();
                },
                 onbeforedeleterow: function(instance, rowNumber) {
                    var id = spreadsheet.getValueFromCoords(0, rowNumber);
                    if (id) {
                        Swal.fire({
                            title: 'Apakah Anda yakin?',
                            text: 'Data pendapatan ini akan dihapus secara permanen dari database.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, Hapus!',
                            cancelButtonText: 'Batal',
                            customClass: {
                                confirmButton: 'btn btn-danger',
                                cancelButton: 'btn btn-light'
                            }
                        }).then(function(result) {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: '{{ url("console/incomes") }}/' + id,
                                    type: 'POST',
                                    data: {
                                        _method: 'DELETE',
                                        _token: '{{ csrf_token() }}'
                                    },
                                    success: function() {
                                        spreadsheet.options.onbeforedeleterow = null;
                                        spreadsheet.deleteRow(rowNumber);
                                        spreadsheet.options.onbeforedeleterow = incomeBeforeDeleteRow;
                                        updateTotal();
                                        toastr.success('Data pendapatan berhasil dihapus.');
                                    },
                                    error: function() {
                                        toastr.error('Gagal menghapus data di database. Harap muat ulang halaman.');
                                    }
                                });
                            }
                        });
                    } else {
                        setTimeout(function() {
                            spreadsheet.options.onbeforedeleterow = null;
                            spreadsheet.deleteRow(rowNumber);
                            spreadsheet.options.onbeforedeleterow = incomeBeforeDeleteRow;
                            updateTotal();
                        }, 0);
                    }
                    return false;
                },
                ondeleterow: function(instance, rowNumber, numOfRows, rowDOMElement, rowData) {
                    updateTotal();
                }
            });

            var incomeBeforeDeleteRow = spreadsheet.options.onbeforedeleterow;

            // Intercept keyboard delete / backspace to prevent browser's native confirm
            document.addEventListener('keydown', function(e) {
                if ((e.which === 46 || e.which === 8) && spreadsheet && spreadsheet.selectedRow !== null && spreadsheet.selectedRow !== undefined && spreadsheet.selectedRow !== false) {
                    e.preventDefault();
                    e.stopPropagation();
                    var selectedRows = spreadsheet.getSelectedRows(true);
                    if (selectedRows && selectedRows.length > 0) {
                        var rowsToDelete = [...selectedRows].sort(function(a, b) { return b - a; });
                        rowsToDelete.forEach(function(rowNum) {
                            spreadsheet.deleteRow(parseInt(rowNum));
                        });
                    }
                }
            }, true);

            setTimeout(function() {
                var headers = $('#spreadsheet > div > table > thead > tr:first-child > td');
                headers.each(function(index) {
                    if (index >= 0 && index <= spreadsheet.options.columns.length) {
                        var colIndex = index;
                        var colType = spreadsheet.options.columns[colIndex - 1]?.type;
                        if (colIndex > 0 && colType !== 'hidden') { 
                            var originalTitle = spreadsheet.options.columns[colIndex - 1].title;
                            var iconHtml = ' <i class="ki-duotone ki-filter ms-2 custom-filter-icon text-gray-500" data-col="'+(colIndex-1)+'" style="cursor: pointer;" onclick="openUniversalFilter(event, '+(colIndex-1)+')"><span class="path1"></span><span class="path2"></span></i>';
                            $(this).html(originalTitle + iconHtml);
                        }
                    }
                });
                applyAllFilters();
            }, 100);

            // Initial total calculation
            updateTotal();

            let debounceTimer;

            function autoSave() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    if (!spreadsheet) return;
                    var data = spreadsheet.getData();
                    var validData = [];
                    var rowMapping = []; // Tracks which spreadsheet row corresponds to which validData item
                    
                    for(var i = 0; i < data.length; i++) {
                        var row = data[i];
                        if (row[1] || row[2] || row[3] || row[4] || row[5] || row[6]) {
                            let cleanAmount = row[6] !== null && row[6] !== '' ? String(row[6]).replace(/[^\d.-]/g, '') : null;
                            validData.push({
                                index: i,
                                id: row[0] || null,
                                date: row[1] || null,
                                pertanian_id: row[2] || null,
                                tengkulak_id: row[3] || null,
                                type: row[4] || null,
                                description: row[5] || null,
                                amount: cleanAmount
                            });
                            rowMapping.push(i);
                        }
                    }

                    if (validData.length === 0) return;

                    $('#auto-save-status').html('<i class="fas fa-spinner fa-spin text-warning me-1"></i> Menyimpan...').removeClass('d-none badge-light-success badge-light-danger').addClass('badge-light-warning');

                    $.ajax({
                        url: '{{ route("incomes.store") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            data: validData
                        },
                        success: function(response) {
                            if (response.savedData) {
                                for(var j=0; j<response.savedData.length; j++) {
                                    var gridRowIndex = rowMapping[response.savedData[j].index];
                                    var newId = response.savedData[j].id;
                                    var currentId = spreadsheet.getValueFromCoords(0, gridRowIndex);
                                    if (!currentId) {
                                        spreadsheet.setValueFromCoords(0, gridRowIndex, newId, true);
                                    }
                                }
                            }
                            $('#auto-save-status').html('<i class="fas fa-check-circle text-success me-1"></i> <span class="status-text text-success">Tersimpan otomatis</span>').removeClass('badge-light-warning').addClass('badge-light-success');
                            setTimeout(() => {
                                if($('#auto-save-status .status-text').text() === 'Tersimpan otomatis') {
                                    $('#auto-save-status').addClass('d-none');
                                }
                            }, 3000);
                        },
                        error: function(xhr) {
                            $('#auto-save-status').html('<i class="fas fa-exclamation-circle text-danger me-1"></i> <span class="status-text text-danger">Gagal menyimpan</span>').removeClass('badge-light-warning').addClass('badge-light-danger');
                        }
                    });
                }, 1500);
            }

            $('#btn-close-alert').click(function() {
                $('#usage-alert').removeClass('d-flex').addClass('d-none');
                $('#btn-show-alert').removeClass('d-none');
            });

            $('#btn-show-alert').click(function() {
                $('#usage-alert').removeClass('d-none').addClass('d-flex');
                $('#btn-show-alert').addClass('d-none');
            });

            // Filter Logic
            let universalFilterModal = new bootstrap.Modal(document.getElementById('universalFilterModal'));
             let activeFilters = {};
             try {
                 const stored = localStorage.getItem('incomes_filters');
                 if (stored) {
                     activeFilters = JSON.parse(stored);
                 }
             } catch (e) {
                 console.error('Failed to load activeFilters:', e);
             }
            let datePicker = flatpickr("#filter-date-picker", {
                mode: "range",
                dateFormat: "Y-m-d",
                inline: true
            });

            window.openUniversalFilter = function(e, colIndex) {
                e.stopPropagation();
                $('#current-filter-col').val(colIndex);
                var column = spreadsheet.options.columns[colIndex];
                $('#filter-modal-title').text('Filter ' + column.title);
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
                    if(source) {
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
                if(column.type === 'calendar') {
                    let selectedDates = datePicker.selectedDates;
                    if(selectedDates.length === 2) activeFilters[colIndex] = [selectedDates[0], selectedDates[1]];
                    else if (selectedDates.length === 1) activeFilters[colIndex] = [selectedDates[0], selectedDates[0]];
                    else delete activeFilters[colIndex];
                } else if(column.type === 'dropdown') {
                    var val = $('#filter-select-input').val();
                    if(val && val.length > 0) activeFilters[colIndex] = val;
                    else delete activeFilters[colIndex];
                } else {
                    var val = $('#filter-text-input').val();
                    if(val) activeFilters[colIndex] = val;
                    else delete activeFilters[colIndex];
                }
                applyAllFilters();
                universalFilterModal.hide();
            });

            $('#btn-global-reset-filter').click(function() {
                activeFilters = {};
                datePicker.clear();
                applyAllFilters();
            });

            function applyAllFilters() {
                try {
                    localStorage.setItem('incomes_filters', JSON.stringify(activeFilters));
                } catch(e) {
                    console.error('Failed to save activeFilters:', e);
                }
                let data = spreadsheet.getData();
                let rows = $('#spreadsheet > div > table > tbody > tr');
                
                if(Object.keys(activeFilters).length > 0) {
                    $('#btn-global-reset-filter').removeClass('d-none');
                } else {
                    $('#btn-global-reset-filter').addClass('d-none');
                }

                $('#spreadsheet .custom-filter-icon').each(function() {
                    var cIdx = $(this).attr('data-col');
                    if(activeFilters[cIdx]) {
                        $(this).removeClass('text-gray-500').addClass('text-success');
                    } else {
                        $(this).removeClass('text-success').addClass('text-gray-500');
                    }
                });

                for(let i = 0; i < data.length; i++) {
                    let rowData = data[i];
                    
                    let isEmpty = true;
                    for(let j=1; j<=6; j++) {
                        if(rowData[j]) { isEmpty = false; break; }
                    }
                    if(isEmpty) {
                        rows.eq(i).show();
                        continue;
                    }

                    let match = true;
                    for(let colIndex in activeFilters) {
                        let filterVal = activeFilters[colIndex];
                        let cellVal = rowData[colIndex];
                        let colType = spreadsheet.options.columns[colIndex].type;

                        if(cellVal === null || cellVal === undefined || cellVal === '') {
                            match = false;
                            break;
                        }

                        if(colType === 'calendar') {
                            let rowDate = new Date(cellVal);
                            rowDate.setHours(0,0,0,0);
                            let start = new Date(filterVal[0]); start.setHours(0,0,0,0);
                            let end = new Date(filterVal[1]); end.setHours(0,0,0,0);
                            if(rowDate < start || rowDate > end) { 
                                match = false; 
                                break; 
                            }
                        } else if(colType === 'dropdown') {
                            let found = false;
                            for(let k=0; k<filterVal.length; k++) {
                                if(cellVal == filterVal[k]) { found = true; break; }
                            }
                            if(!found) { match = false; break; }
                        } else {
                            if(String(cellVal).toLowerCase().indexOf(String(filterVal).toLowerCase()) === -1) {
                                match = false;
                                break;
                            }
                        }
                    }

                    if(match) rows.eq(i).show();
                    else rows.eq(i).hide();
                }
            }
        });
    </script>
@endpush
