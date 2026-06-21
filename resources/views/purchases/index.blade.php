@extends('layouts.metronic')

@section('title', 'Pencatatan Pembelian')
@section('page_title')
    <div class="d-flex align-items-center flex-row">
        Pencatatan Pembelian & Pengeluaran
        <span id="auto-save-status" class="badge badge-light-success fw-bold fs-7 ms-3 d-none">
            <i class="fas fa-check-circle text-success me-1"></i> <span class="status-text">Tersimpan Otomatis</span>
        </span>
    </div>
@endsection

@section('page_actions')
    <button type="button" id="btn-show-alert" class="btn btn-icon btn-light-info btn-sm me-3 d-none" title="Cara Penggunaan">
        <i class="ki-duotone ki-information-5 fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
    </button>
    <a href="{{ route('stores.index') }}" class="btn btn-info btn-sm me-3">
        <i class="ki-duotone ki-shop fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i> Toko / Vendor
    </a>
    <a href="{{ route('purchase-categories.index') }}" class="btn btn-primary btn-sm">
        <i class="ki-duotone ki-category fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i> Kategori
    </a>
@endsection

@section('content')
<div class="alert alert-info d-flex align-items-center p-5 mb-5" id="usage-alert">
    <i class="ki-duotone ki-information fs-2hx text-info me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
    <div class="d-flex flex-column flex-grow-1 pe-8">
        <h4 class="mb-1 text-info">Cara Penggunaan</h4>
        <span>Sistem akan mengelompokkan barang belanja secara otomatis ke dalam satu Nota jika **Tanggal, Pertanian, Toko, dan No. Nota**-nya sama. Isi baris kosong di bawah tabel untuk mencatat barang belanjaan. Data otomatis tersimpan ketika Anda mengetik.</span>
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
        <h4 class="m-0 text-gray-800">Total Pengeluaran: <span id="total-amount" class="text-danger fw-bolder ms-2">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</span></h4>
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
    <script src="https://bossanova.uk/jspreadsheet/v4/jexcel.js"></script>
    <script src="https://jsuites.net/v4/jsuites.js"></script>

    <script>
        $(document).ready(function() {
            $('[data-control="select2"]').select2();

            @php
                $pertanianData = $pertanians->map(fn($p) => ['id' => $p->id, 'name' => '[' . ($p->kebun->name ?? 'Tanpa Kebun') . '] - ' . $p->name])->toArray();
                $storeData = $stores->map(fn($w) => ['id' => $w->id, 'name' => $w->name])->toArray();
                $categoryData = $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->toArray();
            @endphp

            let pertanians = @json($pertanianData);
            let stores = @json($storeData);
            let categories = @json($categoryData);

            const initialData = @json($initialData);

            if (initialData.length === 0) {
                initialData.push(['', '', '', '', '', '', '', '', '', '']);
            }

            function updateTotalAndRow() {
                if (!spreadsheet) return;
                let data = spreadsheet.getData();
                let total = 0;
                for(let i=0; i<data.length; i++) {
                    let qtyStr = data[i][7] !== null && data[i][7] !== '' ? String(data[i][7]).replace(/,/g, '') : '0';
                    let priceStr = data[i][8] !== null && data[i][8] !== '' ? String(data[i][8]).replace(/,/g, '') : '0';
                    let qty = parseFloat(qtyStr);
                    let price = parseFloat(priceStr);
                    if(!isNaN(qty) && !isNaN(price)) {
                        let rowTotal = qty * price;
                        // update row total cell if empty or different
                        let currentTotal = parseFloat(data[i][9]);
                        if(currentTotal !== rowTotal) {
                            spreadsheet.setValueFromCoords(9, i, rowTotal, false);
                        }
                        total += rowTotal;
                    }
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
                    { type: 'hidden', title: 'ID' }, // 0
                    { type: 'calendar', title: 'Tanggal', width: 120, options: { format: 'YYYY-MM-DD' } }, // 1
                    { type: 'dropdown', title: 'Pertanian', width: 220, source: pertanians }, // 2
                    { type: 'dropdown', title: 'Toko / Vendor', width: 180, source: stores, autocomplete: true, options: { newOptions: true } }, // 3
                    { type: 'text', title: 'No Nota', width: 120 }, // 4
                    { type: 'dropdown', title: 'Kategori Barang', width: 150, source: categories, autocomplete: true, options: { newOptions: true } }, // 5
                    { type: 'text', title: 'Nama Barang / Deskripsi', width: 220 }, // 6
                    { type: 'numeric', title: 'Qty', width: 80, mask: '#,##0' }, // 7
                    { type: 'numeric', title: 'Harga Satuan (Rp)', width: 130, mask: '#,##0' }, // 8
                    { type: 'numeric', title: 'Total (Rp)', width: 150, mask: '#,##0', readOnly: true } // 9
                ],
                onload: function() {
                    setTimeout(function() {
                        var headers = $('#spreadsheet .jexcel > thead > tr:first-child > td:not(.jexcel_selectall)');
                        headers.each(function(index) {
                            if (index >= 0 && index < spreadsheet.options.columns.length) {
                                var colIndex = index;
                                if(colIndex > 0) { 
                                    var originalTitle = spreadsheet.options.columns[colIndex].title;
                                    var iconHtml = ' <i class="ki-duotone ki-filter ms-2 custom-filter-icon text-gray-500" data-col="'+colIndex+'" style="cursor: pointer;" onclick="openUniversalFilter(event, '+colIndex+')"><span class="path1"></span><span class="path2"></span></i>';
                                    $(this).html(originalTitle + iconHtml);
                                }
                            }
                        });
                    }, 100);
                },
                minDimensions: [10, {{ count($initialData) > 20 ? count($initialData) + 10 : 30 }}],
                defaultColAlign: 'left',
                allowInsertRow: true,
                allowManualInsertRow: true,
                allowInsertColumn: false,
                onchange: function(instance, cell, x, y, value) {
                    if (x == 3 && value && isNaN(value)) {
                        $.post('{{ route("purchases.ajax-store") }}', { name: value, _token: '{{ csrf_token() }}' }, function(res) {
                            stores.push({ id: res.id, name: res.name });
                            spreadsheet.setValueFromCoords(x, y, res.id, true);
                            updateTotalAndRow();
                            autoSave();
                        });
                    } else if (x == 5 && value && isNaN(value)) {
                        $.post('{{ route("purchases.ajax-category") }}', { name: value, _token: '{{ csrf_token() }}' }, function(res) {
                            categories.push({ id: res.id, name: res.name });
                            spreadsheet.setValueFromCoords(x, y, res.id, true);
                            updateTotalAndRow();
                            autoSave();
                        });
                    } else {
                        updateTotalAndRow();
                        autoSave();
                    }
                },
                oninsertrow: function() {
                    updateTotalAndRow();
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
                    updateTotalAndRow();
                    var id = rowData[0];
                    if (id) {
                        $.ajax({
                            url: '{{ url("purchases") }}/' + id,
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: '{{ csrf_token() }}'
                            },
                            success: function() {
                                toastr.success('Data pembelian berhasil dihapus.');
                            },
                            error: function() {
                                toastr.error('Gagal menghapus data di database. Harap muat ulang halaman.');
                            }
                        });
                    }
                }
            });

            updateTotalAndRow();

            let debounceTimer;

            function autoSave() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    if (!spreadsheet) return;
                    var data = spreadsheet.getData();
                    var validData = [];
                    var rowMapping = [];
                    
                    for(var i = 0; i < data.length; i++) {
                        var row = data[i];
                        if (row[1] || row[2] || row[3] || row[4] || row[5] || row[6] || row[7] || row[8]) {
                            validData.push({
                                index: i, // We use this in backend
                                id: row[0] || null,
                                date: row[1] || null,
                                pertanian_id: row[2] || null,
                                store_id: row[3] || null,
                                invoice_number: row[4] || null,
                                category_id: row[5] || null,
                                description: row[6] || null,
                                qty: row[7] !== "" && row[7] !== null ? row[7] : null,
                                unit_price: row[8] !== "" && row[8] !== null ? row[8] : null,
                                total_price: row[9] || null
                            });
                            rowMapping.push(i);
                        }
                    }

                    if (validData.length === 0) return;

                    $('#auto-save-status').html('<i class="fas fa-spinner fa-spin text-warning me-1"></i> Menyimpan...').removeClass('d-none badge-light-success badge-light-danger').addClass('badge-light-warning');

                    $.ajax({
                        url: '{{ route("purchases.store") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            data: validData
                        },
                        success: function(response) {
                            if (response.savedData) {
                                for(var j=0; j<response.savedData.length; j++) {
                                    var gridRowIndex = response.savedData[j].index; // Use the index passed back directly
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

            setInterval(function() {
                $.get('{{ route("purchases.ajax-dropdowns") }}', function(res) {
                    if (spreadsheet) {
                        stores = res.stores;
                        categories = res.categories;
                        spreadsheet.options.columns[3].source = stores;
                        spreadsheet.options.columns[5].source = categories;
                    }
                });
            }, 5000);

            // Filter Logic
            let universalFilterModal = new bootstrap.Modal(document.getElementById('universalFilterModal'));
            let activeFilters = {};
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
        });
    </script>
@endpush
