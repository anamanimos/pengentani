@extends('layouts.metronic')

@section('title', 'Riwayat Import Catatan Pekerjaan')

@section('page_title')
    <div class="d-flex align-items-center">
        <span>Riwayat Import Catatan Lapangan (AI)</span>
        <span class="badge badge-light-primary fw-bold fs-8 ms-3">Gemini Vision OCR</span>
    </div>
@endsection

@section('page_actions')
    <a href="{{ route('worker-jobs.index') }}" class="btn btn-secondary btn-sm me-3">
        <i class="ki-duotone ki-arrow-left fs-3 me-1"><span class="path1"></span><span class="path2"></span></i> Kembali
    </a>
    <a href="{{ route('worker-jobs.import.create') }}" class="btn btn-primary btn-sm">
        <i class="ki-duotone ki-plus-circle fs-3 me-1"><span class="path1"></span><span class="path2"></span></i> Import Catatan Baru
    </a>
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
                <h4 class="mb-1 text-danger">Gagal</h4>
                <span>{{ session('error') }}</span>
            </div>
        </div>
        @endif

        <div class="card card-flush shadow-sm">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-duotone ki-document fs-1 text-primary me-3"><span class="path1"></span><span class="path2"></span></i>
                        <div class="d-flex flex-column">
                            <h3 class="fw-bold text-gray-800 fs-4 mb-0">Daftar Sesi Import Catatan</h3>
                            <span class="text-muted fs-8">Catatan buku buruh tani yang diunggah dan diverifikasi</span>
                        </div>
                    </div>
                </div>
                <div class="card-toolbar">
                    <a href="{{ route('worker-jobs.import.create') }}" class="btn btn-light-primary btn-sm fw-bold">
                        <i class="ki-duotone ki-scan-barcode fs-3 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span><span class="path7"></span><span class="path8"></span></i> Mulai Scan Catatan
                    </a>
                </div>
            </div>

            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-200 align-middle gs-4 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted bg-light fs-7">
                                <th class="min-w-60px text-center">ID</th>
                                <th class="min-w-150px">Waktu & Sumber</th>
                                <th class="min-w-140px">Pekerja & Periode</th>
                                <th class="min-w-90px text-center">Jumlah Baris</th>
                                <th class="min-w-130px">Total Upah</th>
                                <th class="min-w-110px text-center">Status</th>
                                <th class="min-w-120px text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="fs-7">
                            @forelse($logs as $log)
                            <tr>
                                <td class="text-center fw-bold text-gray-700">#{{ $log->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($log->source_channel === 'photo')
                                            <div class="symbol symbol-35px me-3">
                                                <span class="symbol-label bg-light-primary text-primary">
                                                    <i class="ki-duotone ki-picture fs-3 text-primary"><span class="path1"></span><span class="path2"></span></i>
                                                </span>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-800 fw-bold fs-7">{{ $log->file_name ?: 'Foto Catatan' }}</span>
                                                <span class="text-muted fs-9">{{ $log->created_at->format('d M Y, H:i') }}</span>
                                            </div>
                                        @else
                                            <div class="symbol symbol-35px me-3">
                                                <span class="symbol-label bg-light-info text-info">
                                                    <i class="ki-duotone ki-code fs-3 text-info"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                                </span>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-800 fw-bold fs-7">Input JSON Manual</span>
                                                <span class="text-muted fs-9">{{ $log->created_at->format('d M Y, H:i') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-900 fw-bold">{{ $log->detected_worker_name ?: '-' }}</span>
                                        <span class="text-muted fs-8">{{ $log->period_summary ?: '-' }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light fw-bold fs-7">{{ $log->total_rows ?: $log->total_staged_count }} Baris</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-gray-900">Rp {{ number_format($log->total_wage, 0, ',', '.') }}</span>
                                        @if($log->total_konsumsi > 0)
                                            <span class="text-muted fs-9">Konsumsi: Rp {{ number_format($log->total_konsumsi, 0, ',', '.') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($log->status === 'completed')
                                        <span class="badge badge-light-success fw-bold fs-8">
                                            <i class="ki-duotone ki-check fs-6 text-success me-1"></i> Tersimpan
                                        </span>
                                    @elseif($log->status === 'waiting_review')
                                        <span class="badge badge-light-warning fw-bold fs-8">
                                            <i class="ki-duotone ki-time fs-6 text-warning me-1"><span class="path1"></span><span class="path2"></span></i> Perlu Review
                                        </span>
                                    @elseif($log->status === 'processing')
                                        <span class="badge badge-light-primary fw-bold fs-8">
                                            <span class="spinner-border spinner-border-sm me-1"></span> Diproses
                                        </span>
                                    @elseif($log->status === 'failed')
                                        <span class="badge badge-light-danger fw-bold fs-8" data-bs-toggle="tooltip" title="{{ $log->error_message }}">
                                            <i class="ki-duotone ki-cross fs-6 text-danger me-1"></i> Gagal
                                        </span>
                                    @else
                                        <span class="badge badge-light-secondary fw-bold fs-8">{{ ucfirst($log->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        @if($log->status === 'waiting_review' || $log->status === 'draft')
                                            <a href="{{ route('worker-jobs.import.review', $log->id) }}" class="btn btn-primary btn-sm py-1 px-3 fs-8" title="Review dan Simpan">
                                                <i class="ki-duotone ki-pencil fs-5 me-1"><span class="path1"></span><span class="path2"></span></i> Review
                                            </a>
                                        @elseif($log->status === 'completed')
                                            <a href="{{ route('worker-jobs.import.review', $log->id) }}" class="btn btn-light btn-sm py-1 px-3 fs-8" title="Lihat Rekap Data">
                                                <i class="ki-duotone ki-eye fs-5 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Detail
                                            </a>
                                        @endif

                                        <form action="{{ route('worker-jobs.import.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus sesi import ini? Data pekerjaan yang sudah tersimpan di sistem tidak akan terhapus.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-icon btn-light-danger btn-sm h-30px w-30px" title="Hapus Sesi">
                                                <i class="ki-duotone ki-trash fs-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-duotone ki-document fs-3x text-muted mb-3"><span class="path1"></span><span class="path2"></span></i>
                                        <span class="text-gray-600 fw-semibold fs-6">Belum ada riwayat import catatan lapangan.</span>
                                        <span class="text-muted fs-8 mb-4">Anda dapat mengunggah foto buku catatan harian buruh tani untuk otomatisasi input upah.</span>
                                        <a href="{{ route('worker-jobs.import.create') }}" class="btn btn-primary btn-sm">
                                            <i class="ki-duotone ki-plus fs-3 me-1"></i> Unggah Catatan Sekarang
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
