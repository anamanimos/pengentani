@extends('layouts.metronic')

@section('title', 'Kelola Bukti Transaksi')

<style>
    .proof-card-item {
        position: relative;
        overflow: hidden;
        width: 100%;
        height: 180px;
        background-color: #000;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .proof-card-item .proof-img {
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        transition: transform 0.4s ease;
    }
    
    .proof-card-item .proof-pdf-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background-color: #1e1e2d;
        color: #f1416c;
        transition: transform 0.4s ease;
    }
    
    .proof-card-item:hover .proof-img,
    .proof-card-item:hover .proof-pdf-placeholder {
        transform: scale(1.1);
    }
    
    .proof-card-item .proof-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.2) 50%, rgba(0,0,0,0.85) 100%);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 12px;
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 2;
        pointer-events: none;
    }
    
    .proof-card-item:hover .proof-overlay {
        opacity: 1;
    }
    
    .proof-overlay-btn,
    .proof-overlay-badge {
        pointer-events: auto;
    }
</style>

@section('page_title', 'Bukti Transaksi')

@section('page_actions')
<button type="button" class="btn btn-sm fw-bold btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_upload_proof">
    Upload Bukti Manual
</button>
@endsection

@section('content')
<div class="app-content flex-column-fluid">
    <div class="app-container container-fluid">
        @if(session('success'))
        <div class="alert alert-success d-flex align-items-center p-5 mb-5">
            <i class="ki-duotone ki-check-circle fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-success">Berhasil</h4>
                <span>{{ session('success') }}</span>
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center p-5 mb-5">
            <i class="ki-duotone ki-information fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-danger">Terjadi Kesalahan</h4>
                <span>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </span>
            </div>
        </div>
        @endif

        <div class="row g-5 g-xl-8">
            <!-- Left Side: Dropzone Area (4 columns) -->
            <div class="col-xl-4">
                <div class="card card-flush h-xl-100">
                    <div class="card-header pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-800">Upload Bukti</span>
                            <span class="text-gray-500 mt-1 fw-semibold fs-7">Tarik dan lepas file ke sini</span>
                        </h3>
                    </div>
                    <div class="card-body pt-5">
                        <form class="form" action="{{ route('transaction-proofs.store') }}" method="POST" enctype="multipart/form-data" id="kt_dropzone_form">
                            @csrf
                            <div class="fv-row mb-7">
                                <label class="required fs-6 fw-semibold form-label mb-2">Nama Bukti</label>
                                <input type="text" class="form-control form-control-solid" name="name" placeholder="Contoh: Nota 15 Jan" required id="proof_name_input"/>
                            </div>
                            
                            <!-- Dropzone Area -->
                            <div class="dropzone" id="kt_dropzone_proof">
                                <div class="dz-message needsclick">
                                    <i class="ki-duotone ki-file-up fs-3x text-primary"><span class="path1"></span><span class="path2"></span></i>
                                    <div class="ms-4">
                                        <h3 class="fs-5 fw-bold text-gray-900 mb-1">Tarik file ke sini atau klik untuk upload.</h3>
                                        <span class="fs-7 fw-semibold text-gray-500">Maksimal 5MB (JPG, PNG, PDF)</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-5 text-end">
                                <button type="submit" class="btn btn-primary w-100" id="submit_dropzone">Upload Bukti</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Side: Gallery Area (8 columns) -->
            <div class="col-xl-8">
                <div class="card card-flush h-xl-100">
                    <div class="card-header pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-800">Galeri Bukti Transaksi</span>
                            <span class="text-gray-500 mt-1 fw-semibold fs-7">{{ $proofs->count() }} bukti tersimpan</span>
                        </h3>
                        <div class="card-toolbar">
                            <form action="{{ route('transaction-proofs.index') }}" method="GET" class="m-0" id="filter-form">
                                <select name="status" class="form-select form-select-sm form-select-solid fw-bold" data-control="select2" data-hide-search="true" onchange="document.getElementById('filter-form').submit()">
                                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua Status</option>
                                    <option value="unused" {{ request('status') == 'unused' ? 'selected' : '' }}>Belum Digunakan</option>
                                    <option value="used" {{ request('status') == 'used' ? 'selected' : '' }}>Sudah Digunakan</option>
                                </select>
                            </form>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="row g-0">
                            @forelse($proofs as $proof)
                            <div class="col-xl-3 col-md-4 col-sm-6 proof-card" data-id="{{ $proof->id }}">
                                <div class="proof-card-item">
                                    <a href="{{ Storage::url($proof->file_path) }}" data-fslightbox="gallery" class="position-absolute top-0 start-0 w-100 h-100" style="z-index: 1;" title="Lihat Bukti"></a>
                                    
                                    @if(in_array(strtolower(pathinfo($proof->file_path, PATHINFO_EXTENSION)), ['pdf']))
                                        <div class="proof-pdf-placeholder">
                                            <i class="fas fa-file-pdf fs-2x mb-1 text-danger"></i>
                                            <span class="fs-9 fw-bold text-gray-400 text-uppercase">PDF</span>
                                        </div>
                                    @else
                                        <div class="proof-img" style="background-image:url('{{ Storage::url($proof->file_path) }}');"></div>
                                    @endif
                                    
                                    <!-- Overlay -->
                                    <div class="proof-overlay">
                                        <!-- Top Action (Delete) -->
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="proof-overlay-badge" style="z-index: 3;">
                                                @if($proof->is_used)
                                                    <span class="badge badge-success fw-bold fs-9 py-1" title="Terikat dengan data">Sudah Digunakan</span>
                                                @else
                                                    <span class="badge badge-secondary fw-bold text-gray-800 bg-white bg-opacity-75 fs-9 py-1" title="Belum terikat data">Belum Digunakan</span>
                                                @endif
                                            </div>
                                            <form action="{{ route('transaction-proofs.destroy', $proof->id) }}" method="POST" class="d-inline proof-overlay-btn" style="z-index: 3;" onsubmit="return confirm('Hapus bukti ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-icon btn-sm btn-light-danger bg-white bg-opacity-90 w-25px h-25px" title="Hapus Bukti">
                                                    <i class="ki-duotone ki-trash fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                                </button>
                                            </form>
                                        </div>
                                        
                                        <!-- Bottom Info -->
                                        <div class="d-flex justify-content-between align-items-end">
                                            <div class="text-white text-truncate pe-2 w-100">
                                                <span class="fw-bold d-block text-truncate proof-name-display fs-7" title="{{ $proof->name }}">{{ $proof->name }}</span>
                                                <span class="fs-9 opacity-75">{{ $proof->created_at->format('d M Y') }}</span>
                                            </div>
                                            <!-- Rename and History Buttons -->
                                            <div class="d-flex gap-1 proof-overlay-btn" style="z-index: 3;">
                                                @if(!empty($proof->rename_history))
                                                    <button type="button" class="btn btn-icon btn-sm btn-light bg-white bg-opacity-90 w-25px h-25px btn-view-history" 
                                                            title="Lihat Riwayat Nama" 
                                                            data-name="{{ $proof->name }}"
                                                            data-history="{{ json_encode($proof->rename_history) }}">
                                                        <i class="fa fa-history text-gray-700 fs-9"></i>
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-icon btn-sm btn-light bg-white bg-opacity-90 w-25px h-25px btn-view-detail" 
                                                        title="Detail Transaksi" 
                                                        data-id="{{ $proof->id }}">
                                                    <i class="ki-duotone ki-eye fs-5 text-gray-700"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                                </button>
                                                <button type="button" class="btn btn-icon btn-sm btn-light bg-white bg-opacity-90 w-25px h-25px btn-rename" 
                                                        title="Ganti Nama" 
                                                        data-id="{{ $proof->id }}" 
                                                        data-name="{{ $proof->name }}"
                                                        data-url="{{ route('transaction-proofs.rename', $proof->id) }}">
                                                    <i class="ki-duotone ki-pencil fs-5 text-gray-700"><span class="path1"></span><span class="path2"></span></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12 text-center text-muted py-10">
                                Belum ada bukti transaksi.
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Manual (Just in case they click the header button) -->
<div class="modal fade" id="kt_modal_upload_proof" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Upload Bukti Transaksi</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <form class="form" action="{{ route('transaction-proofs.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <div class="fv-row mb-7">
                        <label class="required fs-6 fw-semibold form-label mb-2">Nama Bukti</label>
                        <input type="text" class="form-control form-control-solid" name="name" placeholder="Contoh: Nota Pupuk 15 Jan" required />
                    </div>
                    <div class="fv-row mb-7">
                        <label class="required fs-6 fw-semibold form-label mb-2">File Bukti</label>
                        <input type="file" class="form-control form-control-solid" name="file" accept=".jpg,.jpeg,.png,.pdf" required />
                    </div>
                </div>
                <div class="modal-footer flex-center pt-4 pb-0">
                    <button type="button" data-bs-dismiss="modal" class="btn btn-light me-3">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail Bukti Transaksi -->
<div class="modal fade" id="kt_modal_proof_detail" tabindex="-1" aria-hidden="true">
    <!-- Floating Navigation Buttons (Desktop only) -->
    <button type="button" class="btn btn-icon btn-circle btn-color-gray-600 btn-active-color-primary bg-white shadow btn-modal-prev position-fixed d-none d-md-flex" 
            style="left: 30px; top: 50%; transform: translateY(-50%); z-index: 9999; width: 60px; height: 60px; border: 1px solid #e1e3ea; box-shadow: 0 4px 15px rgba(0,0,0,0.15) !important;" 
            title="Sebelumnya">
        <i class="fa-solid fa-chevron-left fs-1"></i>
    </button>
    <button type="button" class="btn btn-icon btn-circle btn-color-gray-600 btn-active-color-primary bg-white shadow btn-modal-next position-fixed d-none d-md-flex" 
            style="right: 30px; top: 50%; transform: translateY(-50%); z-index: 9999; width: 60px; height: 60px; border: 1px solid #e1e3ea; box-shadow: 0 4px 15px rgba(0,0,0,0.15) !important;" 
            title="Selanjutnya">
        <i class="fa-solid fa-chevron-right fs-1"></i>
    </button>

    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header py-3">
                <div class="d-flex align-items-center gap-2">
                    <!-- Small Navigation Buttons (Mobile only) -->
                    <button type="button" class="btn btn-icon btn-sm btn-light btn-modal-prev d-inline-flex d-md-none" title="Bukti Sebelumnya">
                        <i class="fa-solid fa-chevron-left fs-4 text-gray-700"></i>
                    </button>
                    <button type="button" class="btn btn-icon btn-sm btn-light btn-modal-next d-inline-flex d-md-none" title="Bukti Selanjutnya">
                        <i class="fa-solid fa-chevron-right fs-4 text-gray-700"></i>
                    </button>
                    <h3 class="modal-title ms-3 ms-md-0 fw-bold text-gray-800" id="modal_proof_title">Detail Bukti Transaksi</h3>
                </div>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body p-6 bg-light" id="modal_proof_body">
                <!-- AJAX loaded content will be placed here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/custom/fslightbox/fslightbox.bundle.js') }}"></script>
<script>
    // Initialize Dropzone
    var myDropzone = new Dropzone("#kt_dropzone_proof", {
        url: "{{ route('transaction-proofs.store') }}", // Set the url for your upload script location
        paramName: "file", // The name that will be used to transfer the file
        maxFiles: 1,
        maxFilesize: 5, // MB
        addRemoveLinks: true,
        autoProcessQueue: false,
        acceptedFiles: ".jpeg,.jpg,.png,.pdf",
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        init: function() {
            var submitButton = document.querySelector("#submit_dropzone");
            var myDropzone = this;

            submitButton.addEventListener("click", function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (myDropzone.getQueuedFiles().length > 0) {
                    if(!document.getElementById('proof_name_input').value) {
                        alert("Nama Bukti harus diisi!");
                        return;
                    }
                    myDropzone.processQueue();
                } else {
                    alert("Pilih file terlebih dahulu!");
                }
            });

            this.on("sending", function(file, xhr, formData) {
                // Append name to form data
                formData.append("name", document.getElementById('proof_name_input').value);
            });

            this.on("success", function(file, response) {
                window.location.reload(); // Reload to see the new item in gallery
            });
            
            this.on("error", function(file, response) {
                alert("Upload gagal: " + (response.message || "Kesalahan server"));
            });
        }
    });

    $(document).ready(function() {
        // Handle Rename click
        $(document).on('click', '.btn-rename', function() {
            let button = $(this);
            let url = button.data('url');
            let currentName = button.data('name');
            let container = button.closest('.position-relative'); // container card
            let nameDisplay = container.find('.proof-name-display');

            Swal.fire({
                title: 'Ubah Nama Bukti',
                input: 'text',
                inputValue: currentName,
                inputPlaceholder: 'Masukkan nama bukti baru...',
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-light'
                },
                inputValidator: (value) => {
                    if (!value) {
                        return 'Nama tidak boleh kosong!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    let newName = result.value;
                    
                    Swal.fire({
                        title: 'Mohon tunggu...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: 'PATCH',
                            name: newName
                        },
                        dataType: 'json',
                        success: function(response) {
                            Swal.close();
                            if (response.success) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK',
                                    customClass: {
                                        confirmButton: 'btn btn-primary'
                                    }
                                });
                                
                                // Update dynamic values
                                nameDisplay.text(response.name).attr('title', response.name);
                                button.data('name', response.name);
                                
                                // Find or create the history button
                                let historyBtn = container.find('.btn-view-history');
                                if (historyBtn.length > 0) {
                                    historyBtn.data('name', response.name);
                                    historyBtn.data('history', response.rename_history);
                                } else {
                                    // Prepend history button if it was newly created
                                    let btnContainer = button.parent();
                                    let newHistoryBtn = `
                                        <button type="button" class="btn btn-icon btn-sm btn-light bg-white bg-opacity-75 btn-view-history" 
                                                title="Lihat Riwayat Nama" 
                                                data-name="${response.name}"
                                                data-history='${JSON.stringify(response.rename_history)}'>
                                            <i class="fa fa-history text-gray-700"></i>
                                        </button>
                                    `;
                                    btnContainer.prepend(newHistoryBtn);
                                }
                            } else {
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: response.message || 'Gagal mengubah nama',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.close();
                            let msg = "Terjadi kesalahan saat memproses data.";
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                title: 'Gagal!',
                                text: msg,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        });

        // Handle History click
        $(document).on('click', '.btn-view-history', function() {
            let button = $(this);
            let name = button.data('name');
            let history = button.data('history'); // Should be an array of objects
            
            if (typeof history === 'string') {
                history = JSON.parse(history);
            }

            let html = '<div class="table-responsive"><table class="table table-bordered table-striped fs-7 text-start align-middle">';
            html += '<thead><tr class="fw-bold text-gray-800 bg-light"><th>Nama Lama</th><th>Nama Baru</th><th>Pengubah</th><th>Tanggal</th></tr></thead><tbody>';
            
            history.forEach(function(item) {
                html += `<tr>
                    <td class="text-truncate" style="max-width: 120px;" title="${item.old_name}">${item.old_name}</td>
                    <td class="text-truncate" style="max-width: 120px;" title="${item.new_name}">${item.new_name}</td>
                    <td>${item.changed_by}</td>
                    <td class="text-nowrap">${item.changed_at}</td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';

            Swal.fire({
                title: 'Riwayat Nama: ' + name,
                html: html,
                icon: 'info',
                confirmButtonText: 'Tutup',
                customClass: {
                    confirmButton: 'btn btn-primary'
                },
                width: '600px'
            });
        });

        // Modal navigation variables
        let activeProofIds = [];
        let currentProofIndex = -1;

        // Function to populate activeProofIds array
        function updateActiveProofIds() {
            activeProofIds = [];
            $('.proof-card').each(function() {
                let id = parseInt($(this).data('id'));
                if (id) {
                    activeProofIds.push(id);
                }
            });
        }

        // Initialize active proof IDs
        updateActiveProofIds();

        // Re-evaluate whenever elements change or on page ready
        $(document).ajaxComplete(function() {
            updateActiveProofIds();
        });

        // Function to load proof details into modal
        function loadProofDetail(proofId) {
            let container = $('#modal_proof_body');
            
            // Show loading spinner
            container.html(`
                <div class="d-flex justify-content-center align-items-center py-20">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);

            // Find current index
            currentProofIndex = activeProofIds.indexOf(proofId);

            // Enable/disable navigation buttons
            $('.btn-modal-prev').prop('disabled', currentProofIndex <= 0);
            $('.btn-modal-next').prop('disabled', currentProofIndex === -1 || currentProofIndex >= activeProofIds.length - 1);

            // Fetch detail content via AJAX
            $.ajax({
                url: `/console/transaction-proofs/${proofId}`,
                type: 'GET',
                dataType: 'html',
                success: function(html) {
                    container.html(html);
                    
                    // Update modal title with current proof name
                    let proofName = container.find('.modal-proof-display-name').text() || 'Detail Bukti Transaksi';
                    $('#modal_proof_title').text('Detail Bukti: ' + proofName);
                },
                error: function(xhr) {
                    let msg = "Gagal memuat rincian bukti transaksi.";
                    if (xhr.status === 403) {
                        msg = "Anda tidak memiliki akses untuk bukti transaksi ini.";
                    }
                    container.html(`
                        <div class="alert alert-danger d-flex align-items-center p-5 m-5">
                            <i class="ki-duotone ki-information fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            <div class="d-flex flex-column">
                                <h4 class="mb-1 text-danger">Terjadi Kesalahan</h4>
                                <span>${msg}</span>
                            </div>
                        </div>
                    `);
                }
            });
        }

        // Handle eye/detail button click
        $(document).on('click', '.btn-view-detail', function() {
            let button = $(this);
            let proofId = parseInt(button.data('id'));
            
            // Update array before loading (in case cards were deleted)
            updateActiveProofIds();

            // Load details
            loadProofDetail(proofId);

            // Open modal
            $('#kt_modal_proof_detail').modal('show');
        });

        // Handle prev button click
        $('.btn-modal-prev').on('click', function() {
            if (currentProofIndex > 0) {
                let prevId = activeProofIds[currentProofIndex - 1];
                loadProofDetail(prevId);
            }
        });

        // Handle next button click
        $('.btn-modal-next').on('click', function() {
            if (currentProofIndex !== -1 && currentProofIndex < activeProofIds.length - 1) {
                let nextId = activeProofIds[currentProofIndex + 1];
                loadProofDetail(nextId);
            }
        });

        // Keyboard arrow navigation
        $(document).on('keydown', function(e) {
            // Only navigate if the detail modal is currently open/visible
            if ($('#kt_modal_proof_detail').hasClass('show')) {
                if (e.which === 37) { // Left arrow key
                    if (currentProofIndex > 0) {
                        let prevId = activeProofIds[currentProofIndex - 1];
                        loadProofDetail(prevId);
                    }
                } else if (e.which === 39) { // Right arrow key
                    if (currentProofIndex !== -1 && currentProofIndex < activeProofIds.length - 1) {
                        let nextId = activeProofIds[currentProofIndex + 1];
                        loadProofDetail(nextId);
                    }
                }
            }
        });
    });
</script>
@endpush
