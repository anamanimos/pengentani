@extends('layouts.metronic')

@section('title', 'Review & Validasi Catatan Lapangan')

@section('page_title')
    <div class="d-flex align-items-center">
        <span>Review Catatan Lapangan (Sesi #{{ $importLog->id }})</span>
        @if($needsReviewCount > 0)
            <span class="badge badge-light-warning fw-bold fs-8 ms-3" id="badge-review-status">
                <i class="ki-duotone ki-information-5 fs-6 text-warning me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> {{ $needsReviewCount }} Baris Wajib Dicek
            </span>
        @else
            <span class="badge badge-light-success fw-bold fs-8 ms-3" id="badge-review-status">
                <i class="ki-duotone ki-check-circle fs-6 text-success me-1"></i> Semua Baris Siap Disimpan
            </span>
        @endif
    </div>
@endsection

@section('page_actions')
    <a href="{{ route('worker-jobs.import.index') }}" class="btn btn-secondary btn-sm me-2">
        <i class="ki-duotone ki-arrow-left fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Kembali
    </a>

    @if($importLog->file_path)
    <button type="button" class="btn btn-light-info btn-sm me-2" data-bs-toggle="modal" data-bs-target="#modal-photo-viewer">
        <i class="ki-duotone ki-picture fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Lihat Foto Asli
    </button>
    @endif

    @if($importLog->status !== 'completed')
    <button type="button" id="btn-trigger-commit" class="btn btn-primary btn-sm fw-bold">
        <i class="ki-duotone ki-check fs-3 me-1"></i> Simpan ke Pencatatan Pekerjaan
    </button>
    @else
    <span class="badge badge-success fs-7 py-2 px-4">Sudah Tersimpan di Sistem</span>
    @endif
@endsection

@section('content')
<div class="app-content flex-column-fluid pb-15">
    <div class="app-container container-fluid">
        @if(session('success'))
        <div class="alert alert-success d-flex align-items-center p-5 mb-5 rounded-3 shadow-xs">
            <i class="ki-duotone ki-check-circle fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-success">Berhasil</h4>
                <span>{{ session('success') }}</span>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center p-5 mb-5 rounded-3 shadow-xs">
            <i class="ki-duotone ki-cross-circle fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span></i>
            <div class="d-flex flex-column">
                <h4 class="mb-1 text-danger">Terjadi Kesalahan</h4>
                <span>{{ session('error') }}</span>
            </div>
        </div>
        @endif

        <!-- Summary & Banner Info -->
        <div class="card card-flush shadow-sm mb-6">
            <div class="card-body p-5">
                <div class="row g-4 align-items-center">
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-40px symbol-circle bg-light-primary me-3">
                                <i class="ki-duotone ki-user fs-2 text-primary"><span class="path1"></span><span class="path2"></span></i>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fs-9 text-muted fw-semibold">Pekerja di Catatan:</span>
                                <span class="fs-7 fw-bold text-gray-800">{{ $importLog->detected_worker_name ?: 'Tidak terdeteksi' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-40px symbol-circle bg-light-info me-3">
                                <i class="ki-duotone ki-calendar fs-2 text-info"><span class="path1"></span><span class="path2"></span></i>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fs-9 text-muted fw-semibold">Periode Catatan:</span>
                                <span class="fs-7 fw-bold text-gray-800">{{ $importLog->period_summary ?: '-' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-40px symbol-circle bg-light-success me-3">
                                <i class="ki-duotone ki-dollar fs-2 text-success"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fs-9 text-muted fw-semibold">Total Upah Terpilih:</span>
                                <span class="fs-7 fw-bold text-gray-800" id="summary-total-wage">Rp {{ number_format($importLog->total_wage, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-40px symbol-circle bg-light-warning me-3">
                                <i class="ki-duotone ki-coffee fs-2 text-warning"><span class="path1"></span><span class="path2"></span></i>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fs-9 text-muted fw-semibold">Total Konsumsi Terpilih:</span>
                                <span class="fs-7 fw-bold text-gray-800" id="summary-total-konsumsi">Rp {{ number_format($importLog->total_konsumsi, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Form for Review & Commit -->
        <form id="form-commit-import" action="{{ route('worker-jobs.import.commit', $importLog->id) }}" method="POST">
            @csrf

            <div class="card card-flush shadow-sm">
                <div class="card-header border-0 pt-5">
                    <div class="card-title">
                        <h3 class="fw-bold text-gray-800 fs-5 mb-0">Tabel Hasil Ekstraksi Catatan Lapangan</h3>
                    </div>
                    <div class="card-toolbar d-flex gap-2">
                        @if($importLog->file_path)
                        <button type="button" class="btn btn-sm btn-light-info" data-bs-toggle="modal" data-bs-target="#modal-photo-viewer">
                            <i class="ki-duotone ki-eye fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Buka Foto Asli
                        </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-light-danger" id="btn-delete-unselected">
                            <i class="ki-duotone ki-trash fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i> Hapus Yang Tidak Dicentang
                        </button>
                    </div>
                </div>

                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-200 align-middle gs-2 gy-3" id="table-staging-rows">
                            <thead>
                                <tr class="fw-bold text-muted bg-light fs-8">
                                    <th class="w-40px text-center">
                                        <div class="form-check form-check-sm form-check-custom form-check-solid justify-content-center">
                                            <input class="form-check-input" type="checkbox" id="check-all-rows" checked />
                                        </div>
                                    </th>
                                    <th class="w-50px text-center">Status</th>
                                    <th class="min-w-130px">Tanggal</th>
                                    <th class="min-w-160px">Lahan / Kebun</th>
                                    <th class="min-w-140px">Pekerja</th>
                                    <th class="min-w-140px">Kategori</th>
                                    <th class="min-w-180px">Deskripsi / Catatan Mentah</th>
                                    <th class="min-w-100px">Upah (Rp)</th>
                                    <th class="min-w-90px">Konsumsi (Rp)</th>
                                    <th class="min-w-100px">Status Bayar</th>
                                    <th class="w-60px text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="fs-8">
                                @forelse($importLog->stagingRows as $row)
                                @php
                                    $isRowOk = $row->isValidForCommit() && !$row->confidence_rendah;
                                @endphp
                                <tr id="row-item-{{ $row->id }}" class="{{ !$isRowOk ? 'bg-light-warning bg-opacity-40' : '' }}" data-row-id="{{ $row->id }}">
                                    <!-- Checkbox Sertakan -->
                                    <td class="text-center">
                                        <div class="form-check form-check-sm form-check-custom form-check-solid justify-content-center">
                                            <input class="form-check-input row-select-checkbox" type="checkbox" name="rows[{{ $row->id }}][disertakan]" value="1" {{ $row->disertakan ? 'checked' : '' }} />
                                        </div>
                                    </td>

                                    <!-- Status Validation Badge -->
                                    <td class="text-center">
                                        @if(!$isRowOk)
                                            <span class="badge badge-light-warning badge-circle w-25px h-25px" data-bs-toggle="tooltip" title="{{ $row->review_notes ?: 'Data belum lengkap atau hasil pembacaan AI ragu' }}">
                                                <i class="ki-duotone ki-information fs-7 text-warning"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                            </span>
                                        @else
                                            <span class="badge badge-light-success badge-circle w-25px h-25px" data-bs-toggle="tooltip" title="Lengkap & Siap Simpan">
                                                <i class="ki-duotone ki-check fs-7 text-success"></i>
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Tanggal -->
                                    <td>
                                        <input type="date" name="rows[{{ $row->id }}][date]" class="form-control form-control-sm form-control-solid fs-8 row-input" value="{{ $row->date->format('Y-m-d') }}" required>
                                        @if($row->raw_date_text)
                                            <span class="text-muted fs-9 d-block mt-1">Asli: {{ $row->raw_date_text }}</span>
                                        @endif
                                    </td>

                                    <!-- Pertanian / Lahan -->
                                    <td>
                                        <select name="rows[{{ $row->id }}][pertanian_id]" class="form-select form-select-sm form-select-solid fs-8 row-input {{ empty($row->pertanian_id) ? 'border-warning' : '' }}" required>
                                            <option value="">-- Pilih Lahan --</option>
                                            @foreach($pertanians as $p)
                                                <option value="{{ $p->id }}" {{ $row->pertanian_id == $p->id ? 'selected' : '' }}>
                                                    {{ $p->name }} {{ $p->kebun ? '('.$p->kebun->name.')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($row->raw_kebun_code)
                                            <span class="badge badge-light fs-9 text-gray-600 mt-1">Kode: {{ $row->raw_kebun_code }}</span>
                                        @endif
                                    </td>

                                    <!-- Pekerja -->
                                    <td>
                                        <select name="rows[{{ $row->id }}][worker_id]" class="form-select form-select-sm form-select-solid fs-8 row-input {{ empty($row->worker_id) ? 'border-warning' : '' }}" required>
                                            <option value="">-- Pilih Pekerja --</option>
                                            @foreach($workers as $w)
                                                <option value="{{ $w->id }}" {{ $row->worker_id == $w->id ? 'selected' : '' }}>
                                                    {{ $w->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($row->raw_worker_name)
                                            <span class="badge badge-light fs-9 text-gray-600 mt-1">Asli: {{ $row->raw_worker_name }}</span>
                                        @endif
                                    </td>

                                    <!-- Kategori Pekerjaan -->
                                    <td>
                                        <select name="rows[{{ $row->id }}][job_category_id]" class="form-select form-select-sm form-select-solid fs-8 row-input {{ empty($row->job_category_id) ? 'border-warning' : '' }}" required>
                                            <option value="">-- Pilih Kategori --</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}" {{ $row->job_category_id == $cat->id ? 'selected' : '' }}>
                                                    {{ $cat->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($row->raw_job_name)
                                            <span class="badge badge-light fs-9 text-gray-600 mt-1">Asli: {{ $row->raw_job_name }}</span>
                                        @endif
                                    </td>

                                    <!-- Deskripsi -->
                                    <td>
                                        <input type="text" name="rows[{{ $row->id }}][description]" class="form-control form-control-sm form-control-solid fs-8 row-input" value="{{ $row->description }}" placeholder="Deskripsi pekerjaan">
                                        @if($row->review_notes)
                                            <span class="text-warning fs-9 d-block mt-1"><i class="ki-duotone ki-information fs-9 me-1"></i> {{ $row->review_notes }}</span>
                                        @endif
                                    </td>

                                    <!-- Upah -->
                                    <td>
                                        <input type="number" name="rows[{{ $row->id }}][wage]" class="form-control form-control-sm form-control-solid fs-8 row-input wage-input" value="{{ (int)$row->wage }}" min="0" step="1000" required>
                                    </td>

                                    <!-- Konsumsi -->
                                    <td>
                                        <input type="number" name="rows[{{ $row->id }}][konsumsi]" class="form-control form-control-sm form-control-solid fs-8 row-input konsumsi-input" value="{{ (int)$row->konsumsi }}" min="0" step="1000">
                                    </td>

                                    <!-- Status Bayar -->
                                    <td>
                                        <select name="rows[{{ $row->id }}][status]" class="form-select form-select-sm form-select-solid fs-8 row-input">
                                            <option value="unpaid" {{ $row->status == 'unpaid' ? 'selected' : '' }}>Belum Dibayar</option>
                                            <option value="paid" {{ $row->status == 'paid' ? 'selected' : '' }}>Lunas (Dibayar)</option>
                                        </select>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="text-center">
                                        <button type="button" class="btn btn-icon btn-light-danger btn-sm h-25px w-25px btn-remove-row" data-id="{{ $row->id }}" title="Hapus Baris">
                                            <i class="ki-duotone ki-trash fs-7"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center py-8 text-muted">
                                        Tidak ada baris catatan yang terekstraksi.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer d-flex align-items-center justify-content-between py-4 border-top">
                    <div class="text-muted fs-8">
                        Pastikan seluruh dropdown <span class="fw-bold text-gray-800">Lahan, Pekerja, dan Kategori</span> terisi sebelum menyimpan ke sistem.
                    </div>
                    <div class="d-flex gap-3">
                        <a href="{{ route('worker-jobs.import.index') }}" class="btn btn-light btn-sm">Batal / Kembali</a>
                        @if($importLog->status !== 'completed')
                        <button type="submit" class="btn btn-primary btn-sm fw-bold px-6">
                            <i class="ki-duotone ki-check fs-3 me-1"></i> Simpan ke Pencatatan Pekerjaan
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Photo Viewer -->
@if($importLog->file_path)
<div class="modal fade" id="modal-photo-viewer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title fs-6 fw-bold">Foto Asli Catatan Lapangan (#{{ $importLog->id }})</h5>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body p-2 text-center bg-dark" style="min-height: 500px; max-height: 80vh; overflow: auto;">
                <img src="{{ asset('storage/' . $importLog->file_path) }}" alt="Buku Catatan" class="img-fluid rounded" style="max-height: 75vh; object-fit: contain;">
            </div>
            <div class="modal-footer py-2 d-flex justify-content-between">
                <span class="text-muted fs-8">Gunakan foto ini sebagai referensi untuk mencocokkan tulisan tangan dengan data tabel.</span>
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll = document.getElementById('check-all-rows');
        const rowCheckboxes = document.querySelectorAll('.row-select-checkbox');
        const wageInputs = document.querySelectorAll('.wage-input');
        const konsumsiInputs = document.querySelectorAll('.konsumsi-input');
        const summaryWage = document.getElementById('summary-total-wage');
        const summaryKonsumsi = document.getElementById('summary-total-konsumsi');

        function formatRupiah(num) {
            return 'Rp ' + Number(num).toLocaleString('id-ID');
        }

        function updateTotals() {
            let totalWage = 0;
            let totalKonsumsi = 0;

            document.querySelectorAll('#table-staging-rows tbody tr').forEach(function(tr) {
                const cb = tr.querySelector('.row-select-checkbox');
                if (cb && cb.checked) {
                    const wInput = tr.querySelector('.wage-input');
                    const kInput = tr.querySelector('.konsumsi-input');
                    totalWage += Number(wInput ? wInput.value : 0) || 0;
                    totalKonsumsi += Number(kInput ? kInput.value : 0) || 0;
                }
            });

            if (summaryWage) summaryWage.textContent = formatRupiah(totalWage);
            if (summaryKonsumsi) summaryKonsumsi.textContent = formatRupiah(totalKonsumsi);
        }

        // Toggle Check All
        if (checkAll) {
            checkAll.addEventListener('change', function() {
                rowCheckboxes.forEach(cb => {
                    cb.checked = checkAll.checked;
                });
                updateTotals();
            });
        }

        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateTotals);
        });

        wageInputs.forEach(input => {
            input.addEventListener('input', updateTotals);
        });

        konsumsiInputs.forEach(input => {
            input.addEventListener('input', updateTotals);
        });

        // Trigger commit button
        const triggerCommit = document.getElementById('btn-trigger-commit');
        const commitForm = document.getElementById('form-commit-import');
        if (triggerCommit && commitForm) {
            triggerCommit.addEventListener('click', function() {
                commitForm.requestSubmit();
            });
        }

        // Confirm before submit
        if (commitForm) {
            commitForm.addEventListener('submit', function(e) {
                e.preventDefault();

                let emptyCount = 0;
                let checkedCount = 0;

                document.querySelectorAll('#table-staging-rows tbody tr').forEach(function(tr) {
                    const cb = tr.querySelector('.row-select-checkbox');
                    if (cb && cb.checked) {
                        checkedCount++;
                        const pertanian = tr.querySelector('select[name*="[pertanian_id]"]')?.value;
                        const worker = tr.querySelector('select[name*="[worker_id]"]')?.value;
                        const category = tr.querySelector('select[name*="[job_category_id]"]')?.value;

                        if (!pertanian || !worker || !category) {
                            emptyCount++;
                        }
                    }
                });

                if (checkedCount === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tidak Ada Baris Terpilih',
                        text: 'Pilih minimal 1 baris untuk disimpan ke sistem.',
                        customClass: { confirmButton: 'btn btn-primary' }
                    });
                    return;
                }

                if (emptyCount > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Belum Lengkap',
                        text: `Ada ${emptyCount} baris yang belum memiliki Lahan, Pekerja, atau Kategori. Lengkapi terlebih dahulu atau hilangkan centangnya.`,
                        customClass: { confirmButton: 'btn btn-primary' }
                    });
                    return;
                }

                Swal.fire({
                    title: 'Simpan ke Pencatatan Pekerjaan?',
                    text: `Sebanyak ${checkedCount} catatan akan dimasukkan ke data pekerjaan & upah buruh tani.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan Sekarang!',
                    cancelButtonText: 'Periksa Lagi',
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-light'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        commitForm.submit();
                    }
                });
            });
        }

        // Delete Row
        document.querySelectorAll('.btn-remove-row').forEach(button => {
            button.addEventListener('click', function() {
                const rowId = this.dataset.id;
                const rowElem = document.getElementById('row-item-' + rowId);

                Swal.fire({
                    title: 'Hapus baris ini?',
                    text: 'Baris ini akan dihapus dari daftar review import.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-light'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ url("console/worker-jobs/import/{$importLog->id}/rows") }}/' + rowId,
                            type: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(res) {
                                if (res.success) {
                                    rowElem.remove();
                                    updateTotals();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Terhapus',
                                        text: 'Baris berhasil dihapus.',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                }
                            }
                        });
                    }
                });
            });
        });

        // Delete unselected
        const btnDeleteUnselected = document.getElementById('btn-delete-unselected');
        if (btnDeleteUnselected) {
            btnDeleteUnselected.addEventListener('click', function() {
                const unselectedRows = [];
                document.querySelectorAll('#table-staging-rows tbody tr').forEach(function(tr) {
                    const cb = tr.querySelector('.row-select-checkbox');
                    if (cb && !cb.checked) {
                        const rowId = tr.dataset.rowId;
                        if (rowId) unselectedRows.push({ id: rowId, element: tr });
                    }
                });

                if (unselectedRows.length === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Tidak Ada Baris Tidak Dicentang',
                        text: 'Semua baris saat ini dalam kondisi dicentang.',
                        customClass: { confirmButton: 'btn btn-primary' }
                    });
                    return;
                }

                Swal.fire({
                    title: `Hapus ${unselectedRows.length} baris?`,
                    text: 'Baris yang tidak dicentang akan dihapus dari review ini.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-light'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        unselectedRows.forEach(item => {
                            $.ajax({
                                url: '{{ url("console/worker-jobs/import/{$importLog->id}/rows") }}/' + item.id,
                                type: 'DELETE',
                                data: { _token: '{{ csrf_token() }}' },
                                success: function() {
                                    item.element.remove();
                                    updateTotals();
                                }
                            });
                        });
                        Swal.fire({
                            icon: 'success',
                            title: 'Dibersihkan',
                            text: 'Baris yang tidak dicentang telah dihapus.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                });
            });
        }
    });
</script>
@endpush
