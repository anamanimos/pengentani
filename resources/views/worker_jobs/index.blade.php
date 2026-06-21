@extends('layouts.metronic')

@section('title', 'Pencatatan Pekerjaan')
@section('page_title')
    <div class="d-flex align-items-center flex-row">
        Pencatatan Pekerjaan & Upah
        <span id="auto-save-status" class="badge badge-light-success fw-bold fs-7 ms-3 d-none">
            <i class="fas fa-check-circle text-success me-1"></i> <span class="status-text">Tersimpan Otomatis</span>
        </span>
    </div>
@endsection

@section('page_actions')
    <button type="button" id="btn-show-alert" class="btn btn-icon btn-light-info btn-sm me-3 d-none" title="Cara Penggunaan">
        <i class="ki-duotone ki-information-5 fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
    </button>
    <form action="{{ route('worker-jobs.export') }}" method="GET" class="d-inline">
        @if(request('pertanian_id'))
            <input type="hidden" name="pertanian_id" value="{{ request('pertanian_id') }}">
        @endif
        @if(request('date'))
            <input type="hidden" name="date" value="{{ request('date') }}">
        @endif
        <button type="submit" class="btn btn-success btn-sm me-3">
            <i class="ki-duotone ki-file-down fs-2"><span class="path1"></span><span class="path2"></span></i> Ekspor Excel
        </button>
    </form>
    <a href="{{ route('job-categories.index') }}" class="btn btn-primary btn-sm">
        <i class="ki-duotone ki-category fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i> Kategori
    </a>
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
        <h4 class="m-0 text-gray-800">Total Upah: <span id="total-amount" class="text-success fw-bolder ms-2">Rp 0</span></h4>
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
                        <!-- Options injected dynamically -->
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
                $workerData = $workers->map(fn($w) => ['id' => $w->id, 'name' => $w->name])->toArray();
                $categoryData = $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->toArray();
                
                $initialData = $jobs->map(function($job) {
                    return [
                        $job->id,
                        $job->date ? \Carbon\Carbon::parse($job->date)->format('Y-m-d') : null,
                        $job->pertanian_id,
                        $job->worker_id,
                        $job->job_category_id,
                        $job->start_time ? \Carbon\Carbon::parse($job->start_time)->format("H:i") : null,
                        $job->end_time ? \Carbon\Carbon::parse($job->end_time)->format("H:i") : null,
                        $job->wage ? (float) $job->wage : null,
                        $job->status
                    ];
                })->toArray();
            @endphp

            let pertanians = @json($pertanianData);
            let workers = @json($workerData);
            let categories = @json($categoryData);

            const statuses = [
                { id: 'unpaid', name: 'Belum Dibayar' },
                { id: 'paid', name: 'Dibayar' }
            ];

            const initialData = @json($initialData);

            if (initialData.length === 0) {
                initialData.push(['', '', '', '', '', '', '', '', 'unpaid']);
            }

            function updateTotal() {
                if (!spreadsheet) return;
                let data = spreadsheet.getData();
                let total = 0;
                for(let i=0; i<data.length; i++) {
                    let wageStr = data[i][7] !== null && data[i][7] !== '' ? String(data[i][7]).replace(/,/g, '') : '0';
                    let val = parseInt(wageStr, 10);
                    if(!isNaN(val)) total += val;
                }
                $('#total-amount').text('Rp ' + new Intl.NumberFormat('id-ID').format(total));
            }

            var spreadsheet = jspreadsheet(document.getElementById('spreadsheet'), {
                data: initialData,
                tableOverflow: true,
                tableHeight: '70vh',
                tableWidth: '100%',
                search: false,
                columns: [
                    { type: 'hidden', title: 'ID' },
                    { type: 'calendar', title: 'Tanggal', width: 140, options: { format: 'YYYY-MM-DD' } },
                    { type: 'dropdown', title: 'Pertanian', width: 200, source: pertanians },
                    { type: 'dropdown', title: 'Pekerja', width: 200, source: workers, autocomplete: true, options: { newOptions: true } },
                    { type: 'dropdown', title: 'Kategori Pekerjaan', width: 180, source: categories, autocomplete: true, options: { newOptions: true } },
                    { type: 'text', title: 'Jam Mulai (HH:mm)', width: 120, mask: '00:00' },
                    { type: 'text', title: 'Jam Selesai (HH:mm)', width: 120, mask: '00:00' },
                    { type: 'numeric', title: 'Upah (Rp)', width: 150, mask: '#,##0' },
                    { type: 'dropdown', title: 'Status', width: 120, source: statuses }
                ],
                onload: function() {
                    // Inject icons after a short delay to ensure Jexcel has finished rendering the headers
                    setTimeout(function() {
                        // The first td is usually the row number column, so we skip it.
                        // We target td elements that correspond to our columns based on index
                        var headers = $('#spreadsheet .jexcel > thead > tr:first-child > td:not(.jexcel_selectall)');
                        headers.each(function(index) {
                            if (index >= 0 && index < spreadsheet.options.columns.length) {
                                var colIndex = index;
                                if(colIndex > 0) { // Skip hidden ID column 0
                                    var originalTitle = spreadsheet.options.columns[colIndex].title;
                                    var iconHtml = ' <i class="ki-duotone ki-filter ms-2 custom-filter-icon text-gray-500" data-col="'+colIndex+'" style="cursor: pointer;" onclick="openUniversalFilter(event, '+colIndex+')"><span class="path1"></span><span class="path2"></span></i>';
                                    $(this).html(originalTitle + iconHtml);
                                }
                            }
                        });
                    }, 100);
                },
                minDimensions: [9, {{ count($jobs) > 20 ? count($jobs) + 10 : 30 }}],
                defaultColAlign: 'left',
                allowInsertRow: true,
                allowManualInsertRow: true,
                allowInsertColumn: false,
                onchange: function(instance, cell, x, y, value) {
                    if (x == 3 && value && isNaN(value)) {
                        // New worker
                        $.post('{{ route("worker-jobs.ajax-worker") }}', { name: value, _token: '{{ csrf_token() }}' }, function(res) {
                            workers.push({ id: res.id, name: res.name });
                            spreadsheet.setValueFromCoords(x, y, res.id, true);
                            updateTotal();
                            autoSave();
                        });
                    } else if (x == 4 && value && isNaN(value)) {
                        // New category
                        $.post('{{ route("worker-jobs.ajax-category") }}', { name: value, _token: '{{ csrf_token() }}' }, function(res) {
                            categories.push({ id: res.id, name: res.name });
                            spreadsheet.setValueFromCoords(x, y, res.id, true);
                            updateTotal();
                            autoSave();
                        });
                    } else {
                        updateTotal();
                        autoSave();
                    }
                },
                oninsertrow: function() {
                    updateTotal();
                    autoSave();
                },
                onbeforedeleterow: function(instance, rowNumber) {
                    var id = instance.getValueFromCoords(0, rowNumber);
                    if (id) {
                        return confirm("Anda akan menghapus baris yang sudah tersimpan di database. Apakah Anda yakin?");
                    }
                    return true;
                },
                ondeleterow: function(instance, rowNumber, numOfRows, rowDOMElement, rowData) {
                    updateTotal();
                    var id = rowData[0];
                    if (id) {
                        $.ajax({
                            url: '{{ url("worker-jobs") }}/' + id,
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: '{{ csrf_token() }}'
                            },
                            success: function() {
                                toastr.success('Data pekerjaan berhasil dihapus.');
                            },
                            error: function() {
                                toastr.error('Gagal menghapus data di database. Harap muat ulang halaman.');
                            }
                        });
                    }
                }
            });

            // Initial total calculation
            updateTotal();

            let debounceTimer;

            function autoSave() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    if (!spreadsheet) return;
                    var data = spreadsheet.getData();
                    var validData = [];
                    
                    for(var i = 0; i < data.length; i++) {
                        var row = data[i];
                        if (row[1] || row[2] || row[3] || row[4] || row[5] || row[6] || row[7] || row[8]) { // Save if any field is filled
                            var cleanWage = row[7] ? row[7].toString().replace(/\D/g, '') : null;
                            validData.push({
                                index: i,
                                id: row[0] || null,
                                date: row[1] || null,
                                pertanian_id: row[2] || null,
                                worker_id: row[3] || null,
                                job_category_id: row[4] || null,
                                start_time: row[5] || null,
                                end_time: row[6] || null,
                                wage: cleanWage,
                                status: row[8] || null
                            });
                        }
                    }

                    if (validData.length === 0) return;

                    $('#auto-save-status').html('<i class="fas fa-spinner fa-spin text-warning me-1"></i> Menyimpan...').removeClass('d-none badge-light-success badge-light-danger').addClass('badge-light-warning');

                    $.ajax({
                        url: '{{ route("worker-jobs.store") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            data: validData
                        },
                        success: function(response) {
                            if (response.savedData) {
                                for(var j=0; j<response.savedData.length; j++) {
                                    var gridRowIndex = response.savedData[j].index;
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

            // Auto-update dropdown options every 5 seconds
            setInterval(function() {
                $.get('{{ route("worker-jobs.ajax-dropdowns") }}', function(res) {
                    if (spreadsheet) {
                        // Update local arrays for manually added options
                        workers = res.workers;
                        categories = res.categories;
                        
                        // Update Jspreadsheet column source configurations
                        spreadsheet.options.columns[3].source = workers;
                        spreadsheet.options.columns[4].source = categories;
                    }
                });
            }, 5000);
            // Universal Column Filter Logic
            let universalFilterModal = new bootstrap.Modal(document.getElementById('universalFilterModal'));
            let activeFilters = {}; // Stores active filters by column index
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
                
                // Hide all filter inputs initially
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
                let data = spreadsheet.getData();
                let rows = $('#spreadsheet > div > table > tbody > tr');
                
                // Show or hide global reset button
                if(Object.keys(activeFilters).length > 0) {
                    $('#btn-global-reset-filter').removeClass('d-none');
                } else {
                    $('#btn-global-reset-filter').addClass('d-none');
                }

                // Update icon colors based on active filters
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
                    
                    // Always show completely empty rows (for new entries)
                    let isEmpty = true;
                    for(let j=1; j<=8; j++) { // Columns 1 to 8 contain data
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

                        // If a filter is applied but cell is empty, hide row
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
                            // Select2 multiple select returns an array of string values
                            let found = false;
                            for(let k=0; k<filterVal.length; k++) {
                                if(cellVal == filterVal[k]) { found = true; break; }
                            }
                            if(!found) { match = false; break; }
                        } else {
                            // Text match (case insensitive partial match)
                            if(String(cellVal).toLowerCase().indexOf(String(filterVal).toLowerCase()) === -1) {
                                match = false;
                                break;
                            }
                        }
                    }

                    if(match) rows.eq(i).show();
                    else rows.eq(i).hide();
                }

                // Recalculate total based on visible rows only
                let filteredTotal = 0;
                for(let i = 0; i < data.length; i++) {
                    if(rows.eq(i).is(':visible')) {
                        let wageStr = data[i][7] !== null && data[i][7] !== '' ? String(data[i][7]).replace(/,/g, '') : '0';
                        let val = parseInt(wageStr, 10);
                        if(!isNaN(val)) filteredTotal += val;
                    }
                }
                $('#total-amount').text('Rp ' + new Intl.NumberFormat('id-ID').format(filteredTotal));
            }
        });
    </script>
@endpush
