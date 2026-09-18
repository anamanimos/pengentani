@extends('layouts.metronic')

@section('title', 'Review & Validasi Catatan Lapangan')

@section('page_title')
    <div class="d-flex align-items-center">
        <span>Review Catatan Lapangan (Sesi #{{ $importLog->id }})</span>
        <span class="badge {{ $needsReviewCount > 0 ? 'badge-light-warning' : 'badge-light-success' }} fw-bold fs-8 ms-3" id="badge-review-status">
            @if($needsReviewCount > 0)
                <i class="ki-duotone ki-information-5 fs-6 text-warning me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> <span id="badge-review-count">{{ $needsReviewCount }}</span> Baris Wajib Dicek
            @else
                <i class="ki-duotone ki-check-circle fs-6 text-success me-1"></i> Semua Baris Siap Disimpan
            @endif
        </span>
    </div>
@endsection

@section('page_actions')
    <a href="{{ route('worker-jobs.import.index') }}" class="btn btn-secondary btn-sm me-2">
        <i class="ki-duotone ki-arrow-left fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> Kembali
    </a>

    <button type="button" class="btn btn-light-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#modal-dictionary">
        <i class="ki-duotone ki-book-open fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i> Kamus Auto-Learning 
        <span class="badge badge-success ms-1 fs-9" id="badge-mappings-count">{{ $mappings->count() }}</span>
    </button>

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

        <!-- Banner Pintasan Daftarkan Pekerja Baru -->
        @if(isset($unregisteredWorkers) && $unregisteredWorkers->isNotEmpty())
        <div class="alert alert-dismissible bg-light-primary border border-primary border-dashed d-flex flex-column flex-sm-row w-100 p-4 mb-5 align-items-center rounded-3 shadow-xs">
            <i class="ki-duotone ki-user-tick fs-2hx text-primary me-4 mb-3 mb-sm-0"><span class="path1"></span><span class="path2"></span></i>
            <div class="d-flex flex-column pe-0 pe-sm-10 flex-grow-1">
                <h5 class="fw-bold text-primary mb-1">Daftarkan Pekerja Baru Secara Instan</h5>
                <span class="fs-8 text-gray-700">Ditemukan nama pekerja di catatan yang belum terdaftar di database. Klik tombol di bawah untuk langsung mendaftarkannya sebagai pekerja dan memetakan ke semua baris terkait:</span>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    @foreach($unregisteredWorkers as $unreg)
                        <button type="button" class="btn btn-sm btn-primary py-1 px-3 btn-quick-add-worker" data-worker-name="{{ $unreg }}">
                            <i class="ki-duotone ki-plus fs-7 me-1"><span class="path1"></span><span class="path2"></span></i> Daftarkan "{{ $unreg }}" sebagai Pekerja
                        </button>
                    @endforeach
                </div>
            </div>
            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                <i class="ki-duotone ki-cross fs-2 text-primary"><span class="path1"></span><span class="path2"></span></i>
            </button>
        </div>
        @endif

        <!-- Banner Info Auto-Learning untuk Lahan Baru -->
        @if(isset($unmappedKebunCodes) && $unmappedKebunCodes->isNotEmpty())
        <div class="alert alert-dismissible bg-light-warning border border-warning border-dashed d-flex flex-column flex-sm-row w-100 p-4 mb-5 align-items-center rounded-3 shadow-xs">
            <i class="ki-duotone ki-information-5 fs-2hx text-warning me-4 mb-3 mb-sm-0"><span class="path1"></span><span class="path2"></span></i>
            <div class="d-flex flex-column pe-0 pe-sm-10 flex-grow-1">
                <h5 class="fw-bold text-warning mb-1">Fitur Auto-Learning Aktif</h5>
                <span class="fs-8 text-gray-700">Terdapat kode lahan <strong>({{ $unmappedKebunCodes->implode(', ') }})</strong> yang belum terpetakan. Anda cukup memilih lahan pada <strong>1 baris saja</strong>, sistem akan otomatis mengisi seluruh baris berkode sama dan mempelajarinya untuk sesi import selanjutnya!</span>
            </div>
            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                <i class="ki-duotone ki-cross fs-2 text-warning"><span class="path1"></span><span class="path2"></span></i>
            </button>
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
                        <button type="button" class="btn btn-sm btn-light-success" data-bs-toggle="modal" data-bs-target="#modal-dictionary">
                            <i class="ki-duotone ki-book-open fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i> Kamus ({{ $mappings->count() }})
                        </button>
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
                                    <th class="min-w-160px">Pekerja</th>
                                    <th class="min-w-150px">Kategori</th>
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
                                    $workerRaw = $row->raw_worker_name ?: $importLog->detected_worker_name;
                                @endphp
                                <tr id="row-item-{{ $row->id }}" class="staging-row {{ !$isRowOk ? 'bg-light-warning bg-opacity-40' : '' }}" data-row-id="{{ $row->id }}">
                                    <!-- Checkbox Sertakan -->
                                    <td class="text-center">
                                        <div class="form-check form-check-sm form-check-custom form-check-solid justify-content-center">
                                            <input class="form-check-input row-select-checkbox" type="checkbox" name="rows[{{ $row->id }}][disertakan]" value="1" {{ $row->disertakan ? 'checked' : '' }} />
                                        </div>
                                    </td>

                                    <!-- Status Validation Badge -->
                                    <td class="text-center col-status">
                                        @if(!$isRowOk)
                                            <span class="badge badge-light-warning badge-circle w-25px h-25px status-badge" data-bs-toggle="tooltip" title="{{ $row->review_notes ?: 'Data belum lengkap atau hasil pembacaan AI ragu' }}">
                                                <i class="ki-duotone ki-information fs-7 text-warning"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                            </span>
                                        @else
                                            <span class="badge badge-light-success badge-circle w-25px h-25px status-badge" data-bs-toggle="tooltip" title="Lengkap & Siap Simpan">
                                                <i class="ki-duotone ki-check fs-7 text-success"></i>
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Tanggal -->
                                    <td>
                                        <input type="date" name="rows[{{ $row->id }}][date]" class="form-control form-control-sm form-control-solid fs-8 row-input date-input" value="{{ $row->date ? $row->date->format('Y-m-d') : '' }}" required>
                                        @if($row->raw_date_text)
                                            <span class="text-muted fs-9 d-block mt-1">Asli: {{ $row->raw_date_text }}</span>
                                        @endif
                                    </td>

                                    <!-- Pertanian / Lahan -->
                                    <td>
                                        <div class="d-flex flex-column">
                                            <select name="rows[{{ $row->id }}][pertanian_id]" class="form-select form-select-sm form-select-solid fs-8 row-input select-pertanian {{ empty($row->pertanian_id) ? 'border-warning' : '' }}" data-raw-kebun="{{ $row->raw_kebun_code }}" required>
                                                <option value="">-- Pilih Lahan --</option>
                                                @foreach($pertanians as $p)
                                                    <option value="{{ $p->id }}" {{ $row->pertanian_id == $p->id ? 'selected' : '' }}>
                                                        {{ $p->name }} {{ $p->kebun ? '('.$p->kebun->name.')' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if($row->raw_kebun_code)
                                                <div class="d-flex align-items-center gap-1 mt-1">
                                                    <span class="badge badge-light-secondary fs-9 text-gray-700">Kode: <strong class="text-primary">{{ $row->raw_kebun_code }}</strong></span>
                                                    <span class="badge badge-light-success fs-9 d-none learned-badge-kebun" title="Otomatis diterapkan oleh auto-learning"><i class="ki-duotone ki-check fs-9 text-success"></i> Auto</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Pekerja -->
                                    <td>
                                        <div class="d-flex flex-column">
                                            <select name="rows[{{ $row->id }}][worker_id]" class="form-select form-select-sm form-select-solid fs-8 row-input select-worker {{ empty($row->worker_id) ? 'border-warning' : '' }}" data-raw-worker="{{ $workerRaw }}" required>
                                                <option value="">-- Pilih Pekerja --</option>
                                                @foreach($workers as $w)
                                                    <option value="{{ $w->id }}" {{ $row->worker_id == $w->id ? 'selected' : '' }}>
                                                        {{ $w->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="d-flex flex-wrap align-items-center gap-1 mt-1">
                                                @if($workerRaw)
                                                    <span class="badge badge-light-secondary fs-9 text-gray-700">Asli: <strong class="text-primary">{{ $workerRaw }}</strong></span>
                                                @endif
                                                @if(empty($row->worker_id) && $workerRaw)
                                                    <button type="button" class="btn btn-xs btn-outline btn-outline-primary btn-active-light-primary text-nowrap py-0 px-2 fs-9 btn-quick-add-worker" data-worker-name="{{ $workerRaw }}" title="Daftarkan '{{ $workerRaw }}' ke database pekerja">
                                                        <i class="ki-duotone ki-plus fs-9"><span class="path1"></span><span class="path2"></span></i> + Daftarkan
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Kategori Pekerjaan -->
                                    <td>
                                        <div class="d-flex flex-column">
                                            <select name="rows[{{ $row->id }}][job_category_id]" class="form-select form-select-sm form-select-solid fs-8 row-input select-category {{ empty($row->job_category_id) ? 'border-warning' : '' }}" data-raw-job="{{ $row->raw_job_name }}" required>
                                                <option value="">-- Pilih Kategori --</option>
                                                @foreach($categories as $cat)
                                                    <option value="{{ $cat->id }}" {{ $row->job_category_id == $cat->id ? 'selected' : '' }}>
                                                        {{ $cat->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if($row->raw_job_name)
                                                <div class="d-flex flex-wrap align-items-center gap-1 mt-1">
                                                    <span class="badge badge-light-secondary fs-9 text-gray-700">Asli: <strong class="text-primary">{{ $row->raw_job_name }}</strong></span>
                                                    @if(empty($row->job_category_id))
                                                        <button type="button" class="btn btn-xs btn-outline btn-outline-info btn-active-light-info text-nowrap py-0 px-2 fs-9 btn-quick-add-category" data-cat-name="{{ $row->raw_job_name }}" title="Buat kategori baru '{{ $row->raw_job_name }}'">
                                                            <i class="ki-duotone ki-plus fs-9"><span class="path1"></span><span class="path2"></span></i> + Kategori
                                                        </button>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
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

<!-- Modal Kamus Auto-Learning -->
<div class="modal fade" id="modal-dictionary" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-4">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-35px symbol-circle bg-light-success me-3">
                        <i class="ki-duotone ki-book-open fs-2 text-success"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                    </div>
                    <div class="d-flex flex-column">
                        <h5 class="modal-title fs-5 fw-bold text-gray-800">Kamus Auto-Learning Hacktani</h5>
                        <span class="text-muted fs-8">Aturan pencocokan otomatis yang dipelajari dari riwayat pemilihan Anda</span>
                    </div>
                </div>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body py-4">
                <div class="alert alert-light-primary d-flex align-items-center p-3 mb-4 rounded-2">
                    <i class="ki-duotone ki-information-5 fs-2 text-primary me-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    <div class="fs-8 text-gray-700">
                        Setiap kali Anda memilih Lahan, Pekerja, atau Kategori pada catatan tulisan tangan, sistem akan mengingatnya di kamus ini sehingga pembacaan foto selanjutnya akan otomatis terisi secara cerdas.
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="nav nav-pills gap-1" id="nav-dictionary-filters">
                        <button type="button" class="btn btn-sm btn-light active py-1 px-3 fs-8 filter-mapping-btn" data-filter="all">Semua (<span id="count-dict-all">{{ $mappings->count() }}</span>)</button>
                        <button type="button" class="btn btn-sm btn-light py-1 px-3 fs-8 filter-mapping-btn" data-filter="pertanian">Lahan (<span id="count-dict-pertanian">{{ $mappings->where('type', 'pertanian')->count() }}</span>)</button>
                        <button type="button" class="btn btn-sm btn-light py-1 px-3 fs-8 filter-mapping-btn" data-filter="pekerja">Pekerja (<span id="count-dict-pekerja">{{ $mappings->where('type', 'pekerja')->count() }}</span>)</button>
                        <button type="button" class="btn btn-sm btn-light py-1 px-3 fs-8 filter-mapping-btn" data-filter="kategori">Kategori (<span id="count-dict-kategori">{{ $mappings->where('type', 'kategori')->count() }}</span>)</button>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                    <table class="table table-row-bordered table-row-gray-200 align-middle gs-3 gy-2" id="table-dictionary-mappings">
                        <thead class="bg-light fs-8 text-muted fw-bold">
                            <tr>
                                <th class="w-100px">Tipe</th>
                                <th class="min-w-130px">Kode / Tulisan Asli</th>
                                <th class="min-w-180px">Dipetakan Ke (Sistem)</th>
                                <th class="w-120px">Tgl Dipelajari</th>
                                <th class="w-60px text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="fs-8" id="tbody-dictionary">
                            @forelse($mappings as $map)
                            <tr id="mapping-row-{{ $map->id }}" data-type="{{ $map->type }}">
                                <td>
                                    @if($map->type === 'pertanian')
                                        <span class="badge badge-light-primary fw-semibold fs-9">Lahan</span>
                                    @elseif($map->type === 'pekerja')
                                        <span class="badge badge-light-success fw-semibold fs-9">Pekerja</span>
                                    @else
                                        <span class="badge badge-light-info fw-semibold fs-9">Kategori</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold text-gray-800">{{ $map->raw_label }}</span>
                                </td>
                                <td>
                                    <span class="text-gray-700">{{ $map->target_name ?: '#' . $map->target_id }}</span>
                                </td>
                                <td>
                                    <span class="text-muted fs-9">{{ $map->created_at ? $map->created_at->format('d M Y H:i') : '-' }}</span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-icon btn-light-danger btn-xs btn-delete-mapping" data-id="{{ $map->id }}" data-label="{{ $map->raw_label }}" title="Hapus Aturan Ini">
                                        <i class="ki-duotone ki-trash fs-7"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr id="row-empty-dictionary">
                                <td colspan="5" class="text-center py-6 text-muted">
                                    Belum ada aturan yang dipelajari. Pilihlah Lahan/Pekerja/Kategori pada tabel untuk mulai melatih sistem.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll = document.getElementById('check-all-rows');
        const wageInputs = document.querySelectorAll('.wage-input');
        const konsumsiInputs = document.querySelectorAll('.konsumsi-input');
        const summaryWage = document.getElementById('summary-total-wage');
        const summaryKonsumsi = document.getElementById('summary-total-konsumsi');

        // Toast notification instance
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        function formatRupiah(num) {
            return 'Rp ' + Number(num).toLocaleString('id-ID');
        }

        function updateTotals() {
            let totalWage = 0;
            let totalKonsumsi = 0;

            document.querySelectorAll('#table-staging-rows tbody tr.staging-row').forEach(function(tr) {
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

        function checkRowValidity($tr) {
            if (!$tr || $tr.length === 0) return;

            const pVal = $tr.find('.select-pertanian').val();
            const wVal = $tr.find('.select-worker').val();
            const cVal = $tr.find('.select-category').val();
            const dateVal = $tr.find('input[type="date"]').val();
            const wageVal = Number($tr.find('.wage-input').val()) || 0;

            const isValid = pVal && wVal && cVal && dateVal && wageVal > 0;
            const $statusCol = $tr.find('.col-status');

            if (isValid) {
                $tr.removeClass('bg-light-warning bg-opacity-40');
                $statusCol.html(`
                    <span class="badge badge-light-success badge-circle w-25px h-25px status-badge" data-bs-toggle="tooltip" title="Lengkap & Siap Simpan">
                        <i class="ki-duotone ki-check fs-7 text-success"></i>
                    </span>
                `);
            } else {
                $tr.addClass('bg-light-warning bg-opacity-40');
                $statusCol.html(`
                    <span class="badge badge-light-warning badge-circle w-25px h-25px status-badge" data-bs-toggle="tooltip" title="Lahan, Pekerja, atau Kategori belum lengkap">
                        <i class="ki-duotone ki-information fs-7 text-warning"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                    </span>
                `);
            }

            // Update badge review count in page title
            let pendingCount = 0;
            $('#table-staging-rows tbody tr.staging-row').each(function() {
                const p = $(this).find('.select-pertanian').val();
                const w = $(this).find('.select-worker').val();
                const c = $(this).find('.select-category').val();
                const d = $(this).find('input[type="date"]').val();
                const wg = Number($(this).find('.wage-input').val()) || 0;
                if (!p || !w || !c || !d || wg <= 0) {
                    pendingCount++;
                }
            });

            const $badge = $('#badge-review-status');
            if (pendingCount > 0) {
                $badge.removeClass('badge-light-success').addClass('badge-light-warning')
                      .html(`<i class="ki-duotone ki-information-5 fs-6 text-warning me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> <span id="badge-review-count">${pendingCount}</span> Baris Wajib Dicek`);
            } else {
                $badge.removeClass('badge-light-warning').addClass('badge-light-success')
                      .html(`<i class="ki-duotone ki-check-circle fs-6 text-success me-1"></i> Semua Baris Siap Disimpan`);
            }
        }

        function saveMappingAjax(type, rawLabel, targetId, targetName, callback) {
            if (!rawLabel || !targetId) return;

            $.ajax({
                url: '{{ route("worker-jobs.import.save-mapping") }}',
                type: 'POST',
                data: {
                    type: type,
                    raw_label: rawLabel,
                    target_id: targetId,
                    target_name: targetName,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    if (callback) callback(res);
                },
                error: function(xhr) {
                    console.error('Gagal menyimpan auto-learning mapping:', xhr);
                }
            });
        }

        function updateDictionaryUI(mapping) {
            if (!mapping) return;
            $('#row-empty-dictionary').remove();

            const badgeClass = mapping.type === 'pertanian' ? 'badge-light-primary' : (mapping.type === 'pekerja' ? 'badge-light-success' : 'badge-light-info');
            const typeName = mapping.type === 'pertanian' ? 'Lahan' : (mapping.type === 'pekerja' ? 'Pekerja' : 'Kategori');

            const existingRow = $(`#mapping-row-${mapping.id}`);
            if (existingRow.length > 0) {
                existingRow.find('td:nth-child(3)').text(mapping.target_name || '#' + mapping.target_id);
            } else {
                const rowHtml = `
                    <tr id="mapping-row-${mapping.id}" data-type="${mapping.type}">
                        <td><span class="badge ${badgeClass} fw-semibold fs-9">${typeName}</span></td>
                        <td><span class="fw-bold text-gray-800">${mapping.raw_label}</span></td>
                        <td><span class="text-gray-700">${mapping.target_name || '#' + mapping.target_id}</span></td>
                        <td><span class="text-muted fs-9">Baru saja</span></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-icon btn-light-danger btn-xs btn-delete-mapping" data-id="${mapping.id}" data-label="${mapping.raw_label}" title="Hapus Aturan Ini">
                                <i class="ki-duotone ki-trash fs-7"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                            </button>
                        </td>
                    </tr>
                `;
                $('#tbody-dictionary').prepend(rowHtml);
            }

            const totalCount = $('#tbody-dictionary tr[id^="mapping-row-"]').length;
            $('#badge-mappings-count, #count-dict-all').text(totalCount);
            $('#count-dict-pertanian').text($('#tbody-dictionary tr[data-type="pertanian"]').length);
            $('#count-dict-pekerja').text($('#tbody-dictionary tr[data-type="pekerja"]').length);
            $('#count-dict-kategori').text($('#tbody-dictionary tr[data-type="kategori"]').length);
        }

        // 1. Cascade & Auto-Learning for Pertanian / Lahan
        $(document).on('change', '.select-pertanian', function() {
            const select = $(this);
            const val = select.val();
            const text = select.find('option:selected').text().trim();
            const rawKebun = (select.data('raw-kebun') || '').toString().trim();
            const tr = select.closest('tr');

            if (val) select.removeClass('border-warning');
            checkRowValidity(tr);

            if (rawKebun && val) {
                let matchedCount = 0;
                $('.select-pertanian').each(function() {
                    const otherSelect = $(this);
                    const otherRaw = (otherSelect.data('raw-kebun') || '').toString().trim();
                    if (otherRaw.toLowerCase() === rawKebun.toLowerCase()) {
                        otherSelect.val(val);
                        otherSelect.removeClass('border-warning');
                        otherSelect.closest('tr').find('.learned-badge-kebun').removeClass('d-none');
                        checkRowValidity(otherSelect.closest('tr'));
                        matchedCount++;
                    }
                });

                saveMappingAjax('pertanian', rawKebun, val, text, function(res) {
                    Toast.fire({
                        icon: 'success',
                        title: `Kode Lahan "${rawKebun}" dipelajari & diterapkan ke ${matchedCount} baris!`
                    });
                    updateDictionaryUI(res.mapping);
                });
            }
        });

        // 2. Cascade & Auto-Learning for Pekerja
        $(document).on('change', '.select-worker', function() {
            const select = $(this);
            const val = select.val();
            const text = select.find('option:selected').text().trim();
            const rawWorker = (select.data('raw-worker') || '').toString().trim();
            const tr = select.closest('tr');

            if (val) select.removeClass('border-warning');
            checkRowValidity(tr);

            if (rawWorker && val) {
                let matchedCount = 0;
                $('.select-worker').each(function() {
                    const otherSelect = $(this);
                    const otherRaw = (otherSelect.data('raw-worker') || '').toString().trim();
                    if (otherRaw.toLowerCase() === rawWorker.toLowerCase()) {
                        otherSelect.val(val);
                        otherSelect.removeClass('border-warning');
                        otherSelect.closest('td').find('.btn-quick-add-worker').fadeOut();
                        checkRowValidity(otherSelect.closest('tr'));
                        matchedCount++;
                    }
                });

                saveMappingAjax('pekerja', rawWorker, val, text, function(res) {
                    Toast.fire({
                        icon: 'success',
                        title: `Pekerja "${rawWorker}" dipelajari & diterapkan ke ${matchedCount} baris!`
                    });
                    updateDictionaryUI(res.mapping);
                });
            }
        });

        // 3. Cascade & Auto-Learning for Kategori
        $(document).on('change', '.select-category', function() {
            const select = $(this);
            const val = select.val();
            const text = select.find('option:selected').text().trim();
            const rawJob = (select.data('raw-job') || '').toString().trim();
            const tr = select.closest('tr');

            if (val) select.removeClass('border-warning');
            checkRowValidity(tr);

            if (rawJob && val) {
                let matchedCount = 0;
                $('.select-category').each(function() {
                    const otherSelect = $(this);
                    const otherRaw = (otherSelect.data('raw-job') || '').toString().trim();
                    if (otherRaw.toLowerCase() === rawJob.toLowerCase()) {
                        otherSelect.val(val);
                        otherSelect.removeClass('border-warning');
                        otherSelect.closest('td').find('.btn-quick-add-category').fadeOut();
                        checkRowValidity(otherSelect.closest('tr'));
                        matchedCount++;
                    }
                });

                saveMappingAjax('kategori', rawJob, val, text, function(res) {
                    Toast.fire({
                        icon: 'success',
                        title: `Kategori "${rawJob}" dipelajari & diterapkan ke ${matchedCount} baris!`
                    });
                    updateDictionaryUI(res.mapping);
                });
            }
        });

        // 4. Quick Add Worker Inline
        $(document).on('click', '.btn-quick-add-worker', function() {
            const btn = $(this);
            const workerName = btn.data('worker-name');

            Swal.fire({
                title: 'Daftarkan Pekerja Baru?',
                text: `Nama "${workerName}" akan didaftarkan sebagai pekerja baru di sistem dan langsung dipasangkan ke seluruh baris terkait.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Daftarkan!',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-light'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menambahkan...');

                    $.ajax({
                        url: '{{ route("worker-jobs.ajax-worker") }}',
                        type: 'POST',
                        data: {
                            name: workerName,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            // Append option to all worker dropdowns
                            const newOption = `<option value="${res.id}">${res.name}</option>`;
                            $('.select-worker').each(function() {
                                const s = $(this);
                                if (s.find(`option[value="${res.id}"]`).length === 0) {
                                    s.append(newOption);
                                }
                            });

                            // Select on all matching rows
                            let count = 0;
                            $('.select-worker').each(function() {
                                const s = $(this);
                                const rW = (s.data('raw-worker') || '').toString().trim();
                                if (rW.toLowerCase() === workerName.toLowerCase()) {
                                    s.val(res.id);
                                    s.removeClass('border-warning');
                                    checkRowValidity(s.closest('tr'));
                                    count++;
                                }
                            });

                            // Hide all quick-add buttons for this worker
                            $(`.btn-quick-add-worker[data-worker-name="${workerName}"]`).fadeOut();

                            // Save auto-learning mapping
                            saveMappingAjax('pekerja', workerName, res.id, res.name, function(mapRes) {
                                updateDictionaryUI(mapRes.mapping);
                            });

                            Swal.fire({
                                icon: 'success',
                                title: 'Pekerja Terdaftar!',
                                text: `"${res.name}" berhasil didaftarkan dan dipasangkan ke ${count} baris.`,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false).html('<i class="ki-duotone ki-plus fs-9 me-1"></i> + Daftarkan');
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Menambahkan Pekerja',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan sistem saat mendaftarkan pekerja.'
                            });
                        }
                    });
                }
            });
        });

        // 5. Quick Add Category Inline
        $(document).on('click', '.btn-quick-add-category', function() {
            const btn = $(this);
            const catName = btn.data('cat-name');

            Swal.fire({
                title: 'Buat Kategori Baru?',
                text: `Kategori "${catName}" akan dibuat di sistem dan langsung dipasangkan ke seluruh baris terkait.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Buat Kategori!',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-info',
                    cancelButton: 'btn btn-light'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menambahkan...');

                    $.ajax({
                        url: '{{ route("worker-jobs.ajax-category") }}',
                        type: 'POST',
                        data: {
                            name: catName,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            // Append option to all category dropdowns
                            const newOption = `<option value="${res.id}">${res.name}</option>`;
                            $('.select-category').each(function() {
                                const s = $(this);
                                if (s.find(`option[value="${res.id}"]`).length === 0) {
                                    s.append(newOption);
                                }
                            });

                            // Select on all matching rows
                            let count = 0;
                            $('.select-category').each(function() {
                                const s = $(this);
                                const rC = (s.data('raw-job') || '').toString().trim();
                                if (rC.toLowerCase() === catName.toLowerCase()) {
                                    s.val(res.id);
                                    s.removeClass('border-warning');
                                    checkRowValidity(s.closest('tr'));
                                    count++;
                                }
                            });

                            $(`.btn-quick-add-category[data-cat-name="${catName}"]`).fadeOut();

                            saveMappingAjax('kategori', catName, res.id, res.name, function(mapRes) {
                                updateDictionaryUI(mapRes.mapping);
                            });

                            Swal.fire({
                                icon: 'success',
                                title: 'Kategori Dibuat!',
                                text: `Kategori "${res.name}" berhasil dibuat dan dipasangkan ke ${count} baris.`,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false).html('<i class="ki-duotone ki-plus fs-9 me-1"></i> + Kategori');
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Membuat Kategori',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan sistem saat membuat kategori.'
                            });
                        }
                    });
                }
            });
        });

        // 6. Filter Dictionary Modal
        $(document).on('click', '.filter-mapping-btn', function() {
            $('.filter-mapping-btn').removeClass('active');
            $(this).addClass('active');
            const filter = $(this).data('filter');

            if (filter === 'all') {
                $('#tbody-dictionary tr[id^="mapping-row-"]').show();
            } else {
                $('#tbody-dictionary tr[id^="mapping-row-"]').hide();
                $(`#tbody-dictionary tr[data-type="${filter}"]`).show();
            }
        });

        // 7. Delete Mapping from Dictionary Modal
        $(document).on('click', '.btn-delete-mapping', function() {
            const btn = $(this);
            const id = btn.data('id');
            const label = btn.data('label');

            Swal.fire({
                title: 'Hapus Aturan Auto-Learning?',
                text: `Pemetaan untuk "${label}" akan dihapus dari kamus. AI tidak akan lagi memasangkan kode ini secara otomatis di import berikutnya.`,
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
                    $.ajax({
                        url: '{{ url("console/worker-jobs/import/mappings") }}/' + id,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            $(`#mapping-row-${id}`).fadeOut(300, function() {
                                $(this).remove();
                                const totalCount = $('#tbody-dictionary tr[id^="mapping-row-"]').length;
                                $('#badge-mappings-count, #count-dict-all').text(totalCount);
                                $('#count-dict-pertanian').text($('#tbody-dictionary tr[data-type="pertanian"]').length);
                                $('#count-dict-pekerja').text($('#tbody-dictionary tr[data-type="pekerja"]').length);
                                $('#count-dict-kategori').text($('#tbody-dictionary tr[data-type="kategori"]').length);

                                if (totalCount === 0) {
                                    $('#tbody-dictionary').html(`
                                        <tr id="row-empty-dictionary">
                                            <td colspan="5" class="text-center py-6 text-muted">
                                                Belum ada pemetaan yang dipelajari.
                                            </td>
                                        </tr>
                                    `);
                                }
                            });

                            Toast.fire({
                                icon: 'success',
                                title: res.message || 'Pemetaan berhasil dihapus.'
                            });
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Menghapus',
                                text: 'Terjadi kesalahan sistem saat menghapus aturan.'
                            });
                        }
                    });
                }
            });
        });

        // 8. Row inputs validity and totals
        $(document).on('change', '.row-input', function() {
            checkRowValidity($(this).closest('tr'));
        });

        if (checkAll) {
            checkAll.addEventListener('change', function() {
                document.querySelectorAll('.row-select-checkbox').forEach(cb => {
                    cb.checked = checkAll.checked;
                });
                updateTotals();
            });
        }

        $(document).on('change', '.row-select-checkbox', updateTotals);

        wageInputs.forEach(input => {
            input.addEventListener('input', updateTotals);
        });

        konsumsiInputs.forEach(input => {
            input.addEventListener('input', updateTotals);
        });

        // 9. Trigger Commit Form
        const triggerCommit = document.getElementById('btn-trigger-commit');
        const commitForm = document.getElementById('form-commit-import');
        if (triggerCommit && commitForm) {
            triggerCommit.addEventListener('click', function() {
                commitForm.requestSubmit();
            });
        }

        // 10. Commit Form Submit Validation
        if (commitForm) {
            commitForm.addEventListener('submit', function(e) {
                e.preventDefault();

                let emptyCount = 0;
                let checkedCount = 0;

                document.querySelectorAll('#table-staging-rows tbody tr.staging-row').forEach(function(tr) {
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
                        Swal.fire({
                            title: 'Menyimpan ke Sistem...',
                            html: `
                                <div class="py-4 text-center">
                                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <div class="text-gray-800 fw-bold fs-6">Menyimpan ${checkedCount} catatan ke data pekerjaan & upah...</div>
                                </div>
                            `,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false
                        });
                        commitForm.submit();
                    }
                });
            });
        }

        // 11. Delete Single Row
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
                                    checkRowValidity();
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

        // 12. Delete Unselected Rows
        const btnDeleteUnselected = document.getElementById('btn-delete-unselected');
        if (btnDeleteUnselected) {
            btnDeleteUnselected.addEventListener('click', function() {
                const unselectedRows = [];
                document.querySelectorAll('#table-staging-rows tbody tr.staging-row').forEach(function(tr) {
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
                                    checkRowValidity();
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
