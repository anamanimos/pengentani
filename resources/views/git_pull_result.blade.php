@extends('layouts.metronic')

@section('title', 'Git Pull Repository')

@section('page_title')
    Git Pull Repository <span class="text-gray-500 fw-semibold fs-7 ms-2">(Khusus Super Admin)</span>
@endsection

@section('content')
<div class="app-content flex-column-fluid">
    <div class="app-container container-fluid">
        <div class="card card-flush shadow-sm rounded-3">
            <div class="card-header pt-6">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800 d-flex align-items-center">
                        <i class="ki-duotone ki-cloud-download fs-1 text-primary me-2"><span class="path1"></span><span class="path2"></span></i>
                        Eksekusi Git Pull Repository
                    </span>
                    <span class="text-gray-500 mt-1 fw-semibold fs-7">Memperbarui berkas sistem langsung dari branch utama repository</span>
                </h3>
                <div class="card-toolbar">
                    <a href="{{ route('console.dashboard') }}" class="btn btn-sm btn-light fw-bold me-2 rounded-pill">
                        <i class="ki-duotone ki-arrow-left fs-3 me-1"><span class="path1"></span><span class="path2"></span></i> Kembali ke Dashboard
                    </a>
                    <a href="{{ route('git.pull') }}" class="btn btn-sm btn-primary fw-bold rounded-pill">
                        <i class="ki-duotone ki-arrows-loop fs-3 me-1"><span class="path1"></span><span class="path2"></span></i> Pull Ulang (Execute)
                    </a>
                </div>
            </div>
            <div class="card-body pt-3 pb-8">
                @if(isset($isError) && $isError)
                    <div class="alert alert-danger d-flex align-items-center p-5 mb-5 rounded-3">
                        <i class="ki-duotone ki-information fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        <div class="d-flex flex-column">
                            <h4 class="mb-1 text-danger">Terjadi Kesalahan saat Git Pull</h4>
                            <span>Gagal memperbarui repository. Periksa terminal server atau log sistem.</span>
                        </div>
                    </div>
                @else
                    <div class="alert alert-success d-flex align-items-center p-5 mb-5 rounded-3">
                        <i class="ki-duotone ki-check-circle fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                        <div class="d-flex flex-column">
                            <h4 class="mb-1 text-success">Perintah Git Pull Berhasil Dieksekusi</h4>
                            <span>Proses pembaruan repositori selesai diproses oleh server.</span>
                        </div>
                    </div>
                @endif

                <label class="fs-6 fw-bold text-gray-800 mb-3 d-block">Log Output Perintah (`git pull 2>&1`):</label>
                <div class="bg-dark text-success p-5 rounded-3 border font-monospace fs-7 overflow-auto" style="max-height: 450px; white-space: pre-wrap; word-break: break-all; font-family: 'Courier New', Courier, monospace;">
<span class="text-gray-500">$ cd {{ base_path() }}</span>
<span class="text-gray-500">$ git pull 2>&1</span>

{{ $output }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
