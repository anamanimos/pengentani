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
    <a href="{{ route('income-categories.index') }}" class="btn btn-light-info btn-sm me-3 fw-bold">
        <i class="ki-duotone ki-category fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i> Kelola Kategori
    </a>
    <a href="{{ route('tengkulaks.index') }}" class="btn btn-light-success btn-sm me-3 fw-bold">
        <i class="ki-duotone ki-address-book fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Kelola Tengkulak
    </a>
    <button type="button" class="btn btn-light-primary btn-sm me-3" id="btn-toggle-fullscreen" title="Mode Layar Penuh">
        <i class="ki-duotone ki-maximize fs-2"><span class="path1"></span><span class="path2"></span></i> Layar Penuh
    </button>
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

<div class="position-relative" id="spreadsheet-wrapper">
    <!-- Fullscreen Header -->
    <div class="spreadsheet-fs-header d-none">
        <div class="d-flex align-items-center">
            <h5 class="m-0 fw-bold text-gray-800">Pencatatan Pendapatan</h5>
            <span id="auto-save-status-fs" class="badge badge-light-success fw-bold fs-8 ms-3 d-none"></span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <h6 class="m-0 text-gray-700">Total: <span class="total-amount-fs text-success fw-bolder">Rp 0</span></h6>
            <button type="button" class="btn btn-sm btn-light-danger d-none" id="btn-global-reset-filter-fs">
                <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i> Reset Filter
            </button>
            <button type="button" class="btn btn-sm btn-light-primary" id="btn-exit-fullscreen" title="Keluar Fullscreen">
                <i class="ki-duotone ki-arrow-down-left fs-2"><span class="path1"></span><span class="path2"></span></i> Keluar Fullscreen
            </button>
        </div>
    </div>
    <div class="d-flex justify-content-end mb-2 gap-2">
        <button type="button" class="btn btn-sm btn-light-danger d-none" id="btn-global-reset-filter">
            <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i> Reset Semua Filter
        </button>
    </div>
    <div id="spreadsheet" class="w-100 overflow-auto"></div>
    <div class="d-flex justify-content-end align-items-center p-4 bg-light border-top sticky-bottom z-index-1" id="spreadsheet-footer" style="bottom: 0;">
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
        /* Fullscreen Mode */
        #spreadsheet-wrapper.fullscreen-mode {
            position: fixed !important;
            top: 0;
            left: 0;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 1060;
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

        /* Position the search header at the top of the 50vh bottom sheet (bottom: 50vh) */
        .jdropdown-searchbar.jdropdown-focus .jdropdown-container-header {
            position: absolute !important;
            bottom: 50vh !important; /* Duduk tepat di atas area setengah layar */
            top: auto !important;
            left: 0px !important;
            width: 100% !important;
            height: 56px !important;
            z-index: 9002 !important;
            padding: 10px !important;
            box-shadow: 0 -2px 10px rgba(0,0,0,.05) !important;
            border-top-left-radius: 16px !important;
            border-top-right-radius: 16px !important;
            border-bottom: 1px solid #f1f3f9 !important;
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

        /* Make container act as the bottom-sheet scrollable list */
        .jdropdown-searchbar.jdropdown-focus .jdropdown-container {
            position: absolute !important;
            bottom: 0px !important;
            top: auto !important;
            left: 0px !important;
            width: 100% !important;
            height: 50vh !important; /* Setengah layar */
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.15) !important;
            overflow-y: auto !important;
            display: block !important;
            margin-top: 0px !important;
            border-top-left-radius: 0px !important; /* Header has the rounded corners now */
            border-top-right-radius: 0px !important;
        }

        /* Make the content list scrollable inside the bottom sheet */
        .jdropdown-searchbar.jdropdown-focus .jdropdown-content {
            height: 100% !important;
            max-height: none !important;
            overflow-y: auto !important;
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
        .jdropdown-searchbar {
            background-color: #ffffff !important;
            color: #181C32 !important;
        }
        .jdropdown-searchbar.jdropdown-focus {
            background-color: transparent !important;
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
        [data-bs-theme="dark"] .jdropdown-searchbar {
            background-color: #1e1e2d !important;
            color: #dbdbf4 !important;
            border: 1px solid #2b2b40 !important;
        }
        [data-bs-theme="dark"] .jdropdown-searchbar.jdropdown-focus {
            background-color: transparent !important;
            color: #dbdbf4 !important;
            border: none !important;
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
                $proofsData = isset($proofs) ? $proofs->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'url' => Storage::url($p->file_path)])->toArray() : [];
                
                $initialData = $incomes->map(function($income) {
                    return [
                        $income->id,
                        $income->date,
                        $income->pertanian_id,
                        $income->tengkulak_id,
                        $income->income_category_id,
                        $income->description,
                        (float) $income->qty,
                        (float) $income->unit_price,
                        (float) $income->amount,
                        $income->transaction_proof_id
                    ];
                })->toArray();
            @endphp

            const pertanians = @json($pertanianData);
            const tengkulaks = @json($tengkulakData);
            const types = @json($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->toArray());
            types.push({id: 'NEW_CATEGORY', name: '+ Tambah Kategori Baru...'});
            const proofs = @json($proofsData);
            const proofUrls = {};
            proofs.forEach(function(p) {
                proofUrls[p.id] = p.url;
            });

            const initialData = @json($initialData);

            if (initialData.length === 0) {
                initialData.push(['', '', '', '', '', '', '', '', '', '']);
            }

            function updateTotal() {
                if (!spreadsheet) return;
                let data = spreadsheet.getData();
                let rows = $('#spreadsheet > div > table > tbody > tr');
                let total = 0;
                for(let i=0; i<data.length; i++) {
                    if (rows.length === 0 || rows.eq(i).is(':visible')) {
                        let amountStr = data[i][8] !== null && data[i][8] !== '' ? String(data[i][8]).replace(/[^0-9.-]/g, '') : '0';
                        let val = parseFloat(amountStr);
                        if(!isNaN(val)) total += val;
                    }
                }
                $('#total-amount').text('Rp ' + new Intl.NumberFormat('id-ID').format(total));
            }

            var spreadsheet = jspreadsheet(document.getElementById('spreadsheet'), {
                data: initialData,
                tableOverflow: true,
                onbeforepaste: function(instance, data, x, y) {
                    var sheetInstance = instance.jexcel || instance.jspreadsheet || spreadsheet;
                    if (!data) return data;
                    
                    var isString = typeof data === 'string';
                    var rows = isString ? data.split(/\r?\n/) : data;
                    var processedData = [];

                    for (var row = 0; row < rows.length; row++) {
                        var cols = isString ? rows[row].split('\t') : rows[row];
                        if (isString && cols.length === 1 && cols[0] === '') continue; // skip empty trailing row

                        var processedCols = [];
                        for (var col = 0; col < cols.length; col++) {
                            var targetCol = parseInt(x) + col;
                            var targetCol = parseInt(x) + col;
                            var colOptions = sheetInstance.options.columns[targetCol];
                            var val = String(cols[col]);

                            if (colOptions && colOptions.type === 'numeric') {
                                // Strip Rp, IDR, spaces
                                val = val.replace(/Rp|IDR/gi, '').trim();
                                
                                // Determine decimal separator
                                var cleanVal = val;
                                if (val.includes('.') && val.includes(',')) {
                                    var lastDot = val.lastIndexOf('.');
                                    var lastComma = val.lastIndexOf(',');
                                    if (lastComma > lastDot) {
                                        cleanVal = val.replace(/\./g, '').replace(',', '.');
                                    } else {
                                        cleanVal = val.replace(/,/g, '');
                                    }
                                } else if (val.includes(',')) {
                                    if (val.match(/,\d{1,2}$/)) cleanVal = val.replace(',', '.');
                                    else cleanVal = val.replace(/,/g, '');
                                } else if (val.includes('.')) {
                                    if (val.match(/\.\d{1,2}$/)) cleanVal = val;
                                    else cleanVal = val.replace(/\./g, '');
                                }

                                var num = parseFloat(cleanVal);
                                if (!isNaN(num)) {
                                    val = Math.round(num).toString();
                                }
                            }
                            processedCols.push(val);
                        }
                        processedData.push(isString ? processedCols.join('\t') : processedCols);
                    }
                    
                    return isString ? processedData.join('\n') : processedData;
                },
                onselection: function(instance, x1, y1, x2, y2, origin) {
                    var sheetInstance = instance.jexcel || instance.jspreadsheet || spreadsheet;
                    handleSelection(sheetInstance, x1, y1, x2, y2);
                },
                tableHeight: '70vh',
                tableWidth: '100%',
                search: false,
                columns: [
                    { type: 'hidden', title: 'ID' },
                    { type: 'calendar', title: 'Tanggal <span class="text-danger">*</span>', width: 120, options: { format: 'YYYY-MM-DD' } },
                    { type: 'dropdown', title: 'Pertanian <span class="text-danger">*</span>', width: 250, source: pertanians },
                    { type: 'dropdown', title: 'Tengkulak', width: 200, source: tengkulaks },
                    { type: 'dropdown', title: 'Kategori', width: 150, source: types },
                    { type: 'text', title: 'Deskripsi', width: 250 },
                    { type: 'numeric', title: 'Qty', width: 100, mask: '#,##0.##' },
                    { type: 'numeric', title: 'Harga Satuan (Rp)', width: 150, mask: 'Rp #,##0' },
                    { type: 'numeric', title: 'Total (Rp)', width: 150, mask: 'Rp #,##0', readOnly: true },
                    { type: 'dropdown', title: 'Bukti Transaksi', width: 250, source: proofs }
                ],
                updateTable: function(instance, cell, col, row, val, label, cellName) {
                    if (col == 9 && val && proofUrls[val]) {
                        cell.innerHTML = '<span onclick="openLightbox(event, \'' + proofUrls[val] + '\')" class="cursor-pointer me-2" title="Lihat Bukti"><i class="ki-duotone ki-eye text-primary fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></span> ' + label;
                    }
                },
                minDimensions: [8, {{ count($incomes) > 20 ? count($incomes) + 10 : 30 }}],
                defaultColAlign: 'left',
                allowInsertRow: true,
                allowManualInsertRow: true,
                allowInsertColumn: false,
                allowDeleteRow: true,
                allowDeleteColumn: false,
                wordWrap: false,
                onchange: function(instance, cell, x, y, value) {
                    var sheetInstance = instance.jexcel || instance.jspreadsheet || spreadsheet;

                    if (x == 4 && value === 'NEW_CATEGORY') {
                        sheetInstance.setValueFromCoords(x, y, '', true);
                        Swal.fire({
                            title: 'Tambah Kategori Baru',
                            input: 'text',
                            inputAttributes: {
                                autocapitalize: 'off',
                                placeholder: 'Nama Kategori'
                            },
                            showCancelButton: true,
                            confirmButtonText: 'Simpan',
                            cancelButtonText: 'Batal',
                            showLoaderOnConfirm: true,
                            preConfirm: (name) => {
                                if (!name) {
                                    Swal.showValidationMessage('Nama Kategori tidak boleh kosong');
                                    return false;
                                }
                                return $.ajax({
                                    url: '{{ route("incomes.ajax-category") }}',
                                    type: 'POST',
                                    data: {
                                        _token: '{{ csrf_token() }}',
                                        name: name
                                    }
                                }).catch(error => {
                                    Swal.showValidationMessage(`Request failed: ${error.responseJSON?.message || error.statusText}`);
                                });
                            },
                            allowOutsideClick: () => !Swal.isLoading()
                        }).then((result) => {
                            if (result.isConfirmed) {
                                var newCategory = result.value;
                                
                                // Update types array and dropdown configuration for column 4
                                types.splice(types.length - 1, 0, newCategory); // Insert before "Tambah Kategori Baru"
                                spreadsheet.options.columns[4].source = types;
                                
                                // Set the new value in the cell
                                sheetInstance.setValueFromCoords(x, y, newCategory.id, true);
                                autoSave();
                                
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: 'Kategori baru ditambahkan.',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            }
                        });
                        return;
                    }

                    if (x == 6 || x == 7) {
                        var qty = sheetInstance.getValueFromCoords(6, y) || 0;
                        var price = sheetInstance.getValueFromCoords(7, y) || 0;
                        var qClean = String(qty).replace(/[^0-9.-]/g, '');
                        var pClean = String(price).replace(/[^0-9.-]/g, '');
                        var qVal = parseFloat(qClean) || 0;
                        var pVal = parseFloat(pClean) || 0;
                        sheetInstance.setValueFromCoords(8, y, qVal * pVal, true);
                    }
                    updateTotal();
                    autoSave();

                    // Auto insert row if last row is filled
                    var sheetInstance = instance.jexcel || instance.jspreadsheet || spreadsheet;
                    var totalRows = sheetInstance.options.data.length;
                    if (parseInt(y) === totalRows - 1) {
                        var rowData = sheetInstance.getRowData(y);
                        var hasData = false;
                        for (var i = 1; i < rowData.length; i++) {
                            if (rowData[i] !== null && rowData[i] !== '') {
                                hasData = true;
                                break;
                            }
                        }
                        if (hasData) {
                            sheetInstance.insertRow(5);
                        }
                    }
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
                
                // Scroll to bottom (WhatsApp style)
                var contentDiv = document.querySelector('.jexcel_content');
                if (contentDiv) {
                    contentDiv.scrollTop = contentDiv.scrollHeight;
                }
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
                    var hasIncompleteRow = false;
                    var styles = {};
                    
                    for(var i = 0; i < data.length; i++) {
                        var row = data[i];
                        
                        var pertanianVal = row[2];
                        if (!pertanianVal) {
                            var cellEl = spreadsheet.getCell(jspreadsheet.helpers.getColumnNameFromCoords(2, i));
                            if (cellEl && cellEl.innerText.trim() !== '') pertanianVal = cellEl.innerText.trim();
                        }

                        var tengkulakVal = row[3];
                        if (!tengkulakVal) {
                            var cellEl = spreadsheet.getCell(jspreadsheet.helpers.getColumnNameFromCoords(3, i));
                            if (cellEl && cellEl.innerText.trim() !== '') tengkulakVal = cellEl.innerText.trim();
                        }

                        var typeVal = row[4];
                        if (!typeVal) {
                            var cellEl = spreadsheet.getCell(jspreadsheet.helpers.getColumnNameFromCoords(4, i));
                            if (cellEl && cellEl.innerText.trim() !== '') typeVal = cellEl.innerText.trim();
                        }

                        var hasAnyData = false;
                        for(var j=1; j<row.length; j++) {
                            if (j === 2 && pertanianVal) { hasAnyData = true; break; }
                            if (j === 3 && tengkulakVal) { hasAnyData = true; break; }
                            if (j === 4 && typeVal) { hasAnyData = true; break; }
                            if (row[j] !== null && row[j] !== '') {
                                hasAnyData = true;
                                break;
                            }
                        }

                        var requiredCols = [1, 2];

                        if (row[0] || (row[1] && pertanianVal)) {
                            if (!row[1] || !pertanianVal) {
                                hasIncompleteRow = true;
                                requiredCols.forEach(function(colIdx) {
                                    var val = colIdx === 2 ? pertanianVal : row[colIdx];
                                    if (!val) styles[jspreadsheet.helpers.getColumnNameFromCoords(colIdx, i)] = 'background-color: rgba(241, 65, 108, 0.15) !important;';
                                    else styles[jspreadsheet.helpers.getColumnNameFromCoords(colIdx, i)] = '';
                                });
                            } else {
                                requiredCols.forEach(function(colIdx) {
                                    styles[jspreadsheet.helpers.getColumnNameFromCoords(colIdx, i)] = '';
                                });
                            }

                            let cleanQty = row[6] !== null && row[6] !== '' ? String(row[6]).replace(/[^0-9.-]+/g, '') : 0;
                            let cleanUnitPrice = row[7] !== null && row[7] !== '' ? String(row[7]).replace(/[^0-9.-]+/g, '') : 0;
                            let cleanAmount = row[8] !== null && row[8] !== '' ? String(row[8]).replace(/[^0-9.-]+/g, '') : 0;
                            
                            validData.push({
                                index: i,
                                id: row[0] || null,
                                date: row[1] || null,
                                pertanian_id: pertanianVal || null,
                                tengkulak_id: tengkulakVal || null,
                                income_category_id: typeVal || null,
                                description: row[5] || null,
                                qty: cleanQty,
                                unit_price: cleanUnitPrice,
                                amount: cleanAmount,
                                transaction_proof_id: row[9] || null
                            });
                            rowMapping.push(i);
                        } else if (hasAnyData) {
                            hasIncompleteRow = true;
                            requiredCols.forEach(function(colIdx) {
                                if (!row[colIdx]) styles[jspreadsheet.helpers.getColumnNameFromCoords(colIdx, i)] = 'background-color: rgba(241, 65, 108, 0.15) !important;';
                                else styles[jspreadsheet.helpers.getColumnNameFromCoords(colIdx, i)] = '';
                            });
                        } else {
                            requiredCols.forEach(function(colIdx) {
                                styles[jspreadsheet.helpers.getColumnNameFromCoords(colIdx, i)] = '';
                            });
                        }
                    }

                    spreadsheet.setStyle(styles);

                    if (validData.length === 0) {
                        if (hasIncompleteRow) {
                            $('#auto-save-status').html('<i class="fas fa-info-circle text-warning me-1"></i> <span class="status-text text-warning">Menunggu Data Lengkap</span>').removeClass('badge-light-success badge-light-danger d-none').addClass('badge-light-warning');
                            $('#auto-save-status-fs').html('<i class="fas fa-info-circle text-warning me-1"></i> <span class="status-text text-warning">Menunggu Data Lengkap</span>').removeClass('badge-light-success badge-light-danger d-none').addClass('badge-light-warning');
                        }
                        return;
                    }

                    $('#auto-save-status').html('<i class="fas fa-spinner fa-spin text-warning me-1"></i> Menyimpan...').removeClass('d-none badge-light-success badge-light-danger').addClass('badge-light-warning');
                    $('#auto-save-status-fs').html('<i class="fas fa-spinner fa-spin text-warning me-1"></i> Menyimpan...').removeClass('d-none badge-light-success badge-light-danger').addClass('badge-light-warning');

                    $.ajax({
                        url: '{{ route("incomes.store") }}',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            _token: '{{ csrf_token() }}',
                            data: validData
                        }),
                        success: function(response) {
                            if (hasIncompleteRow) {
                                $('#auto-save-status').html('<i class="fas fa-info-circle text-warning me-1"></i> <span class="status-text text-warning">Menunggu Data Lengkap</span>').removeClass('badge-light-success badge-light-danger d-none').addClass('badge-light-warning');
                                $('#auto-save-status-fs').html('<i class="fas fa-info-circle text-warning me-1"></i> <span class="status-text text-warning">Menunggu Data Lengkap</span>').removeClass('badge-light-success badge-light-danger d-none').addClass('badge-light-warning');
                            } else {
                                $('#auto-save-status').html('<i class="fas fa-check-circle text-success me-1"></i> <span class="status-text text-success">Tersimpan Otomatis</span>').removeClass('badge-light-warning badge-light-danger d-none').addClass('badge-light-success');
                                $('#auto-save-status-fs').html('<i class="fas fa-check-circle text-success me-1"></i> <span class="status-text text-success">Tersimpan Otomatis</span>').removeClass('badge-light-warning badge-light-danger d-none').addClass('badge-light-success');
                            }

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
                            setTimeout(() => {
                                if (!hasIncompleteRow) {
                                    $('#auto-save-status').addClass('d-none');
                                    $('#auto-save-status-fs').addClass('d-none');
                                }
                            }, 3000);
                        },
                        error: function(xhr) {
                            var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menyimpan';
                            $('#auto-save-status').html('<i class="fas fa-exclamation-circle text-danger me-1"></i> <span class="status-text text-danger">' + msg + '</span>').removeClass('badge-light-warning badge-light-success d-none').addClass('badge-light-danger');
                            $('#auto-save-status-fs').html('<i class="fas fa-exclamation-circle text-danger me-1"></i> <span class="status-text text-danger">' + msg + '</span>').removeClass('badge-light-warning badge-light-success d-none').addClass('badge-light-danger');
                        }
                    });
                }, 1500);
            }

            // Cek local storage saat load
            if (localStorage.getItem('hideUsageAlert') === 'true') {
                $('#usage-alert').removeClass('d-flex').addClass('d-none');
                $('#btn-show-alert').removeClass('d-none');
            }

            $('#btn-close-alert').click(function() {
                $('#usage-alert').removeClass('d-flex').addClass('d-none');
                $('#btn-show-alert').removeClass('d-none');
                localStorage.setItem('hideUsageAlert', 'true');
            });

            $('#btn-show-alert').click(function() {
                $('#usage-alert').removeClass('d-none').addClass('d-flex');
                $('#btn-show-alert').addClass('d-none');
                localStorage.removeItem('hideUsageAlert');
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
                    for(let j=1; j<=8; j++) {
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

            // Selection summary helper
            function handleSelection(instance, x1, y1, x2, y2) {
                let startX = Math.min(x1, x2);
                let endX = Math.max(x1, x2);
                let startY = Math.min(y1, y2);
                let endY = Math.max(y1, y2);

                let sum = 0;
                let count = 0;
                let numericCount = 0;
                let hasCurrency = false;

                // Check if any of the selected columns has currency title/formatting
                for (let col = startX; col <= endX; col++) {
                    let colHeader = instance.options.columns[col];
                    if (colHeader && colHeader.title) {
                        let title = colHeader.title.toLowerCase();
                        if (title.includes('rp') || title.includes('harga') || title.includes('nominal') || title.includes('upah')) {
                            hasCurrency = true;
                        }
                    }
                }

                let rows = $('#spreadsheet > div > table > tbody > tr');
                for (let row = startY; row <= endY; row++) {
                    // Check if row is visible (skip filtered out rows)
                    let rowEl = rows.eq(row);
                    if (rowEl.length > 0 && !rowEl.is(':visible')) {
                        continue;
                    }

                    for (let col = startX; col <= endX; col++) {
                        let valStr = instance.getValueFromCoords(col, row);
                        if (valStr !== null && valStr !== undefined && valStr !== '') {
                            // Strip currency signs, spaces, and commas
                            let cleanValStr = String(valStr).replace(/Rp|[\s,]/g, '');
                            if (cleanValStr.trim() === '') continue;
                            let val = parseFloat(cleanValStr);
                            if (!isNaN(val)) {
                                sum += val;
                                numericCount++;
                            }
                            count++;
                        }
                    }
                }

                // Show only if selection is more than a single cell and we found numbers
                if (numericCount > 0 && (endX - startX > 0 || endY - startY > 0)) {
                    let avg = sum / numericCount;
                    
                    // Format output
                    let sumText, avgText;
                    if (hasCurrency) {
                        sumText = 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(sum));
                        avgText = 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(avg));
                    } else {
                        sumText = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(sum);
                        avgText = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(avg);
                    }

                    showFloatingSummary(avgText, numericCount, sumText);
                } else {
                    hideFloatingSummary();
                }
            }

            function showFloatingSummary(avg, count, sum) {
                let summaryDiv = $('#spreadsheet-selection-summary');
                if (summaryDiv.length === 0) {
                    summaryDiv = $(`
                        <div id="spreadsheet-selection-summary" class="position-fixed bottom-0 start-50 translate-middle-x mb-10 shadow-lg d-flex align-items-center gap-4 px-6 py-3 rounded-pill" style="transition: all 0.25s ease-in-out; z-index: 1050; opacity: 0; transform: translate(-50%, 20px) scale(0.95); pointer-events: none;">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted fw-semibold fs-8 text-uppercase">Rata-rata:</span>
                                <span class="fw-bold fs-7 sum-val-avg"></span>
                            </div>
                            <div class="vr bg-gray-300" style="height: 16px; width: 1px;"></div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted fw-semibold fs-8 text-uppercase">Jumlah Sel:</span>
                                <span class="fw-bold fs-7 sum-val-count"></span>
                            </div>
                            <div class="vr bg-gray-300" style="height: 16px; width: 1px;"></div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted fw-semibold fs-8 text-uppercase">Jumlah:</span>
                                <span class="fw-bold fs-6 sum-val-sum"></span>
                            </div>
                        </div>
                    `);
                    $('body').append(summaryDiv);
                }

                // Check Dark Mode
                const isDark = $('html').attr('data-bs-theme') === 'dark' || $('body').attr('data-bs-theme') === 'dark';
                if (isDark) {
                    summaryDiv.css({
                        'background-color': 'rgba(30, 30, 45, 0.95)',
                        'border': '1px solid rgba(255, 255, 255, 0.1)',
                        'color': '#ffffff'
                    });
                    summaryDiv.find('.sum-val-avg, .sum-val-count').css('color', '#ffffff');
                    summaryDiv.find('.sum-val-sum').css('color', '#50cd89'); // green
                    summaryDiv.find('.vr').css('background-color', 'rgba(255, 255, 255, 0.15)');
                } else {
                    summaryDiv.css({
                        'background-color': 'rgba(255, 255, 255, 0.95)',
                        'border': '1px solid rgba(0, 0, 0, 0.1)',
                        'color': '#181C32'
                    });
                    summaryDiv.find('.sum-val-avg, .sum-val-count').css('color', '#181C32');
                    summaryDiv.find('.sum-val-sum').css('color', '#009EF7'); // primary blue
                    summaryDiv.find('.vr').css('background-color', 'rgba(0, 0, 0, 0.1)');
                }

                summaryDiv.find('.sum-val-avg').text(avg);
                summaryDiv.find('.sum-val-count').text(count);
                summaryDiv.find('.sum-val-sum').text(sum);

                summaryDiv.show();
                // Trigger reflow
                summaryDiv[0].offsetHeight;
                summaryDiv.css({
                    'opacity': '1',
                    'transform': 'translate(-50%, 0) scale(1)'
                });
            }

            function hideFloatingSummary() {
                let summaryDiv = $('#spreadsheet-selection-summary');
                if (summaryDiv.length > 0 && summaryDiv.css('opacity') !== '0') {
                    summaryDiv.css({
                        'opacity': '0',
                        'transform': 'translate(-50%, 20px) scale(0.95)'
                    });
                    setTimeout(function() {
                        if (summaryDiv.css('opacity') === '0') {
                            summaryDiv.hide();
                        }
                    }, 250);
                }
            }

            $(document).on('click', function(e) {
                if (!$(e.target).closest('#spreadsheet').length) {
                    hideFloatingSummary();
                }
            });
            // Fullscreen toggle
            var savedTableHeight = null;

            function enterFullscreen() {
                var wrapper = $('#spreadsheet-wrapper');
                wrapper.addClass('fullscreen-mode');
                var el = document.getElementById('spreadsheet');
                if (el && el.jexcel) {
                    savedTableHeight = el.jexcel.options.tableHeight;
                }
                resizeSpreadsheetForFullscreen();
                $('.total-amount-fs').text($('#total-amount').text());
                $('body').css('overflow', 'hidden');
            }

            function exitFullscreen() {
                var wrapper = $('#spreadsheet-wrapper');
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
                var headerH = $('.spreadsheet-fs-header').outerHeight() || 50;
                var footerH = $('#spreadsheet-footer').outerHeight() || 50;
                var toolbarH = $('#spreadsheet-wrapper > .d-flex.justify-content-end.mb-2').outerHeight() || 0;
                var availableH = window.innerHeight - headerH - footerH - toolbarH - 2;
                el.jexcel.options.tableHeight = availableH + 'px';
                el.jexcel.setHeight();
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
