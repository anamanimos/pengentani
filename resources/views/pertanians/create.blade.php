@extends('layouts.metronic')

@section('title', 'Buat Rencana Pertanian')
@section('page_title', 'Buat Rencana Pertanian')

@section('content')
<form action="{{ route('pertanians.store') }}" method="POST" id="form_pertanian">
    @csrf
    
    <div class="row g-5 g-xl-8">
        <!-- Informasi Dasar -->
        <div class="col-xl-12">
            <div class="card mb-5 mb-xl-8">
                <div class="card-header">
                    <h3 class="card-title">Informasi Dasar</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-8">
                        <div class="col-md-6">
                            <label class="required form-label">Pilih Kebun</label>
                            <select name="kebun_id" class="form-select form-select-solid" data-control="select2" data-placeholder="Pilih kebun..." required>
                                <option></option>
                                @foreach($kebuns as $kebun)
                                    <option value="{{ $kebun->id }}" {{ old('kebun_id') == $kebun->id ? 'selected' : '' }}>{{ $kebun->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="required form-label">Nama Rencana</label>
                            <input type="text" name="name" class="form-control form-control-solid" placeholder="Contoh: Proyek Cabai Musim Kemarau" value="{{ old('name') }}" required />
                        </div>
                    </div>
                    <div class="row mb-8">
                        <div class="col-md-6">
                            <label class="required form-label">Admin</label>
                            <select name="admin_id" class="form-select form-select-solid" data-control="select2" data-placeholder="Pilih Admin" required>
                                <option></option>
                                @foreach($admins as $u)
                                    <option value="{{ $u->id }}" {{ old('admin_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="required form-label">Pengelola Lahan</label>
                            <select name="pengelola_entity_id" class="form-select form-select-solid" data-control="select2" data-placeholder="Pilih Pengelola" required>
                                <option></option>
                                @foreach($pengelolas as $entity)
                                    <option value="{{ $entity->id }}" {{ old('pengelola_entity_id') == $entity->id ? 'selected' : '' }}>{{ $entity->name }} ({{ $entity->users->count() }} Anggota)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-8">
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="text" name="start_date" class="form-control form-control-solid kt_datepicker" placeholder="Pilih tanggal" value="{{ old('start_date') }}" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="text" name="end_date" class="form-control form-control-solid kt_datepicker" placeholder="Pilih tanggal" value="{{ old('end_date') }}" />
                        </div>
                        <div class="col-md-4">
                            <label class="required form-label">Status</label>
                            <select name="status" class="form-select form-select-solid" required>
                                <option value="Draft" {{ old('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                                <option value="Pencarian Investor" {{ old('status') == 'Pencarian Investor' ? 'selected' : '' }}>Pencarian Investor</option>
                                <option value="Sedang Berjalan" {{ old('status') == 'Sedang Berjalan' ? 'selected' : '' }}>Sedang Berjalan</option>
                                <option value="Selesai" {{ old('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rencana Tanaman -->
        <div class="col-xl-12">
            <div class="card mb-5 mb-xl-8">
                <div class="card-header">
                    <h3 class="card-title">Rencana Tanaman</h3>
                </div>
                <div class="card-body">
                    <div id="kt_docs_repeater_tanaman">
                        <div class="form-group">
                            <!-- Header for Desktop View -->
                            <div class="d-none d-md-flex flex-stack mb-2 text-gray-700 fw-bold fs-6 ps-2">
                                <div class="row w-100 g-3">
                                    <div class="col-md-3">Jenis Tanaman</div>
                                    <div class="col-md-2">Jumlah Pohon/Bibit</div>
                                    <div class="col-md-3">Est. Panen / Pohon (Kg)</div>
                                    <div class="col-md-3">Est. Harga / Kg (Rp)</div>
                                    <div class="col-md-1 text-center">Hapus</div>
                                </div>
                            </div>

                            <div data-repeater-list="tanamans">
                                <div data-repeater-item class="mb-3">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-md-3">
                                            <label class="form-label d-md-none">Jenis Tanaman</label>
                                            <select name="tanaman_id" class="form-select form-select-solid tanaman-select2" data-placeholder="Pilih atau Ketik Baru...">
                                                <option></option>
                                                @foreach($tanamans as $tanaman)
                                                    <option value="{{ $tanaman->id }}">{{ $tanaman->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label d-md-none">Jumlah Pohon/Bibit</label>
                                            <input type="text" name="qty_pohon" class="form-control form-control-solid" placeholder="0" />
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label d-md-none">Est. Panen / Pohon (Kg)</label>
                                            <input type="text" name="estimasi_berat_per_pohon" class="form-control form-control-solid mask-decimal" placeholder="0.00" />
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label d-md-none">Est. Harga / Kg (Rp)</label>
                                            <input type="text" name="estimasi_harga_per_kg" class="form-control form-control-solid mask-currency" placeholder="0" />
                                        </div>
                                        <div class="col-md-1 text-center">
                                            <label class="form-label d-md-none">Aksi</label>
                                            <div>
                                                <button type="button" data-repeater-delete class="btn btn-sm btn-icon btn-light-danger">
                                                    <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mt-5">
                            <button type="button" data-repeater-create class="btn btn-light-primary">
                                <i class="ki-duotone ki-plus fs-3"></i> Tambah Tanaman
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rencana Biaya -->
        <div class="col-xl-12">
            <div class="card mb-5 mb-xl-8">
                <div class="card-header">
                    <h3 class="card-title">Rincian Estimasi Biaya</h3>
                </div>
                <div class="card-body">
                    <div id="kt_docs_repeater_biaya">
                        <div class="form-group">
                            <!-- Header for Desktop View -->
                            <div class="d-none d-md-flex flex-stack mb-2 text-gray-700 fw-bold fs-6 ps-2">
                                <div class="row w-100 g-3">
                                    <div class="col-md-4">Nama Biaya</div>
                                    <div class="col-md-2">Qty</div>
                                    <div class="col-md-3">Harga Satuan (Rp)</div>
                                    <div class="col-md-2">Total (Rp)</div>
                                    <div class="col-md-1 text-center">Hapus</div>
                                </div>
                            </div>

                            <div data-repeater-list="biayas">
                                <div data-repeater-item class="mb-3">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-md-4">
                                            <label class="form-label d-md-none">Nama Biaya</label>
                                            <input type="text" name="name" class="form-control form-control-solid" placeholder="Contoh: Pupuk Urea, Gaji Tenaga" />
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label d-md-none">Qty</label>
                                            <input type="text" name="qty" class="form-control form-control-solid biaya-qty" placeholder="1" value="1" />
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label d-md-none">Harga Satuan (Rp)</label>
                                            <input type="text" name="harga_satuan" class="form-control form-control-solid mask-currency biaya-harga" placeholder="0" />
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label d-md-none">Total (Rp)</label>
                                            <input type="text" name="total" class="form-control form-control-transparent biaya-total" readonly placeholder="0" />
                                        </div>
                                        <div class="col-md-1 text-center">
                                            <label class="form-label d-md-none">Aksi</label>
                                            <div>
                                                <button type="button" data-repeater-delete class="btn btn-sm btn-icon btn-light-danger">
                                                    <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mt-5">
                            <button type="button" data-repeater-create class="btn btn-light-primary">
                                <i class="ki-duotone ki-plus fs-3"></i> Tambah Biaya
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ringkasan Estimasi -->
        <div class="col-xl-12">
            <div class="card mb-5 mb-xl-8">
                <div class="card-header">
                    <h3 class="card-title">Ringkasan Estimasi</h3>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="border border-dashed border-gray-300 rounded py-4 px-5 mb-3">
                                <div class="fs-2 fw-bold text-success" id="summary_pendapatan">Rp 0</div>
                                <div class="fw-semibold text-muted fs-6">Estimasi Pendapatan</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border border-dashed border-gray-300 rounded py-4 px-5 mb-3">
                                <div class="fs-2 fw-bold text-danger" id="summary_biaya">Rp 0</div>
                                <div class="fw-semibold text-muted fs-6">Total Kebutuhan Modal</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border border-dashed border-gray-300 rounded py-4 px-5 mb-3">
                                <div class="fs-2 fw-bold text-primary" id="summary_laba">Rp 0</div>
                                <div class="fw-semibold text-muted fs-6 mb-3">Estimasi Laba Bersih</div>
                                <div class="separator separator-dashed my-2"></div>
                                <div class="d-flex flex-stack fs-7 text-muted">
                                    <span>Zakat (<span id="summary_zakat_percent">5</span>%):</span>
                                    <span class="fw-bold text-gray-800" id="summary_zakat_amount">Rp 0</span>
                                </div>
                                <div class="d-flex flex-stack fs-7 mt-1">
                                    <span class="text-success fw-semibold">Sisa Setelah Zakat:</span>
                                    <span class="fw-bold text-success" id="summary_laba_bersih">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Skema Bagi Hasil -->
        <div class="col-xl-12">
            <div class="card mb-5 mb-xl-8">
                <div class="card-header">
                    <h3 class="card-title">Skema Bagi Hasil</h3>
                </div>
                <div class="card-body">
                    <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-9 p-6">
                        <i class="fas fa-info-circle fs-1 text-primary me-4"></i>
                        <div class="d-flex flex-stack flex-grow-1">
                            <div class="fw-semibold">
                                <div class="fs-6 text-gray-700">Persentase Zakat akan dipotong terlebih dahulu dari Laba Bersih. Sisa laba kemudian dibagi untuk Investor, Pengelola Lahan, dan Admin dengan total persentase <strong>100%</strong>.</div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-8">
                        <div class="col-md-6">
                            <label class="form-label">Batasan Nilai Investasi (Opsional)</label>
                            <input type="text" name="batasan_investasi" id="batasan_investasi" class="form-control form-control-solid mask-currency" placeholder="0" value="{{ old('batasan_investasi') }}" />
                            <div class="text-muted fs-7 mt-2">Biarkan kosong jika tidak ada batasan. Jika diisi, porsi tiap investor akan dihitung dari batasan ini, bukan dari total investasi yang terkumpul.</div>
                        </div>
                    </div>
                    <div class="row mb-8">
                        <div class="col-md-3">
                            <label class="required form-label">Zakat (%)</label>
                            <input type="text" name="persentase_zakat" id="persen_zakat" class="form-control form-control-solid mask-decimal" placeholder="5.00" value="{{ old('persentase_zakat', '5.00') }}" required />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Investor (%)</label>
                            <input type="text" name="persentase_investor" id="persen_investor" class="form-control form-control-solid persen-input" placeholder="0" value="{{ old('persentase_investor', 0) }}" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Pengelola Lahan (%)</label>
                            <input type="text" name="persentase_pengelola" id="persen_pengelola" class="form-control form-control-solid persen-input" placeholder="0" value="{{ old('persentase_pengelola', 0) }}" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Admin (%)</label>
                            <input type="text" name="persentase_admin" id="persen_admin" class="form-control form-control-solid persen-input" placeholder="0" value="{{ old('persentase_admin', 0) }}" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex align-items-center">
                                <span class="fw-bold me-3">Total Distribusi Laba:</span>
                                <span id="persen_total" class="badge badge-light-success fs-5">0%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-12 text-end mb-10">
            <a href="{{ route('pertanians.index') }}" class="btn btn-light me-3">Batal</a>
            <button type="submit" class="btn btn-primary" id="btn_submit">Simpan Rencana</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<!-- Include jQuery Repeater -->
<script src="{{ asset('assets/plugins/custom/formrepeater/formrepeater.bundle.js') }}"></script>

<script>
    $(document).ready(function () {
        // Init Select2 for static fields
        $('[data-control="select2"]').select2();

        // Helper: Initialize input masks
        function initMasks() {
            try {
                if (typeof Inputmask !== 'undefined') {
                    Inputmask({
                        "alias": "numeric",
                        "groupSeparator": ",",
                        "autoGroup": true,
                        "digits": 0,
                        "digitsOptional": false,
                        "placeholder": "0",
                        "removeMaskOnSubmit": true,
                    }).mask(".mask-currency");

                    Inputmask({
                        "alias": "numeric",
                        "groupSeparator": ",",
                        "autoGroup": true,
                        "digits": 2,
                        "digitsOptional": false,
                        "placeholder": "0",
                        "removeMaskOnSubmit": true,
                    }).mask(".mask-decimal");
                }
            } catch(e) {
                console.warn('Inputmask not loaded');
            }
        }

        // Helper: Initialize Select2 with tags for Tanaman
        function initSelect2Tanaman() {
            $('.tanaman-select2').select2({
                tags: true,
                createTag: function (params) {
                    var term = $.trim(params.term);
                    if (term === '') { return null; }
                    return {
                        id: term,
                        text: term,
                        newTag: true 
                    }
                }
            });
        }

        // Init Tanaman Repeater
        $('#kt_docs_repeater_tanaman').repeater({
            initEmpty: false,
            defaultValues: {
                'qty_pohon': '0'
            },
            show: function () {
                $(this).slideDown();
                // Re-init plugins on new element
                $(this).find('.tanaman-select2').removeClass('select2-hidden-accessible').next('.select2-container').remove();
                initSelect2Tanaman();
                initMasks();
            },
            hide: function (deleteElement) {
                let el = $(this);
                Swal.fire({
                    title: "Apakah Anda yakin?",
                    text: "Ingin menghapus tanaman ini?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Ya, Hapus!",
                    cancelButtonText: "Batal",
                    customClass: {
                        confirmButton: "btn btn-danger",
                        cancelButton: "btn btn-light"
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        el.slideUp(deleteElement);
                    }
                });
            }
        });

        // Init Biaya Repeater
        $('#kt_docs_repeater_biaya').repeater({
            initEmpty: false,
            defaultValues: {
                'qty': '1'
            },
            show: function () {
                $(this).slideDown();
                initMasks();
                bindBiayaCalculation();
            },
            hide: function (deleteElement) {
                let el = $(this);
                Swal.fire({
                    title: "Apakah Anda yakin?",
                    text: "Ingin menghapus biaya ini?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Ya, Hapus!",
                    cancelButtonText: "Batal",
                    customClass: {
                        confirmButton: "btn btn-danger",
                        cancelButton: "btn btn-light"
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        el.slideUp(deleteElement);
                    }
                });
            }
        });

        // Initial setup
        initSelect2Tanaman();
        initMasks();
        bindBiayaCalculation();

        // Auto-calculate summary
        function calculateSummary() {
            let totalPendapatan = 0;
            $('#kt_docs_repeater_tanaman [data-repeater-item]:visible').each(function() {
                let qty = parseFloat($(this).find('[name*="qty_pohon"]').val()) || 0;
                let berat = parseFloat(String($(this).find('[name*="estimasi_berat_per_pohon"]').val()).replace(/,/g, '')) || 0;
                let harga = parseFloat(String($(this).find('[name*="estimasi_harga_per_kg"]').val()).replace(/,/g, '')) || 0;
                totalPendapatan += qty * berat * harga;
            });

            let totalBiaya = 0;
            $('#kt_docs_repeater_biaya [data-repeater-item]:visible').each(function() {
                let qty = parseFloat($(this).find('.biaya-qty').val()) || 0;
                let harga = parseFloat(String($(this).find('.biaya-harga').val()).replace(/,/g, '')) || 0;
                totalBiaya += qty * harga;
            });

            let laba = totalPendapatan - totalBiaya;

            // Zakat calculation
            let persenZakat = parseFloat($('#persen_zakat').val().replace(/,/g, '')) || 0;
            let zakat = laba > 0 ? laba * (persenZakat / 100) : 0;
            let sisaLaba = laba - zakat;

            $('#summary_pendapatan').text('Rp ' + totalPendapatan.toLocaleString('id-ID'));
            $('#summary_biaya').text('Rp ' + totalBiaya.toLocaleString('id-ID'));
            $('#summary_laba').text('Rp ' + laba.toLocaleString('id-ID'));

            $('#summary_zakat_percent').text(persenZakat);
            $('#summary_zakat_amount').text('Rp ' + Math.round(zakat).toLocaleString('id-ID'));
            $('#summary_laba_bersih').text('Rp ' + Math.round(sisaLaba).toLocaleString('id-ID'));

            if (laba >= 0) {
                $('#summary_laba').removeClass('text-danger').addClass('text-primary');
            } else {
                $('#summary_laba').removeClass('text-primary').addClass('text-danger');
            }
        }

        $(document).on('input change', '[name*="qty_pohon"], [name*="estimasi_berat_per_pohon"], [name*="estimasi_harga_per_kg"], .biaya-qty, .biaya-harga, #persen_zakat', function() {
            calculateSummary();
        });

        calculateSummary();

        // Initialize Flatpickr
        try {
            if ($.fn.flatpickr) {
                $(".kt_datepicker").flatpickr({
                    altInput: true,
                    altFormat: "d M Y",
                    dateFormat: "Y-m-d",
                });
            }
        } catch(e) {
            console.warn('Flatpickr not loaded');
        }

        // Calculate Total Biaya on the fly
        function bindBiayaCalculation() {
            $('.biaya-qty, .biaya-harga').off('input').on('input', function() {
                let row = $(this).closest('[data-repeater-item]');
                let qty = parseFloat(row.find('.biaya-qty').val()) || 0;
                let harga = parseFloat(row.find('.biaya-harga').val().replace(/,/g, '')) || 0;
                let total = qty * harga;
                
                // Format format to locale string without decimals if whole number
                row.find('.biaya-total').val(total.toLocaleString('en-US'));
            });
        }

        // Calculate percentage total on the fly
        function bindPersenCalculation() {
            $('.persen-input').off('input').on('input', function() {
                let investor = parseInt($('#persen_investor').val()) || 0;
                let pengelola = parseInt($('#persen_pengelola').val()) || 0;
                let admin = parseInt($('#persen_admin').val()) || 0;
                let total = investor + pengelola + admin;
                let badge = $('#persen_total');
                badge.text(total + '%');
                if (total === 100) {
                    badge.removeClass('badge-light-danger badge-light-warning').addClass('badge-light-success');
                } else if (total > 100) {
                    badge.removeClass('badge-light-success badge-light-warning').addClass('badge-light-danger');
                } else {
                    badge.removeClass('badge-light-success badge-light-danger').addClass('badge-light-warning');
                }
            });
            // Trigger initial calculation
            $('.persen-input').first().trigger('input');
        }
        bindPersenCalculation();

        // AJAX Form Submission
        $('#form_pertanian').on('submit', function (e) {
            e.preventDefault();
            let form = $(this);
            let btn = $('#btn_submit');
            let url = form.attr('action');

            btn.prop('disabled', true).text('Menyimpan...');

            $.ajax({
                url: url,
                type: 'POST',
                data: form.serialize(),
                success: function (response) {
                    Swal.fire({
                        text: response.message || "Data berhasil disimpan!",
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, Mengerti!",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            window.location.href = response.redirect;
                        }
                    });
                },
                error: function (xhr) {
                    btn.prop('disabled', false).text('Simpan Rencana');
                    let message = "Terjadi kesalahan, silakan coba lagi.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorMsg = '';
                        $.each(errors, function (key, value) {
                            errorMsg += value[0] + '<br>';
                        });
                        message = errorMsg;
                    }

                    Swal.fire({
                        html: message,
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, Mengerti!",
                        customClass: {
                            confirmButton: "btn btn-danger"
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
