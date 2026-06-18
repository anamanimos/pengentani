@extends('layouts.metronic')

@section('title', 'Beranda')
@section('page_title', 'Selamat Datang, ' . Auth::user()->name . '!')

@section('content')
    <!-- Nav tabs -->
    <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#tab_keuangan">Keuangan</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab_aktivitas">Aktivitas Pertanian</a>
        </li>
    </ul>

    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="tab_keuangan" role="tabpanel">
    <!--begin::Row-->
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <!--begin::Col-->
        <div class="col-sm-6 col-xxl-3">
            <div class="card card-flush h-xl-100 bg-success">
                <div class="card-body pt-5">
                    <div class="text-white fw-bold fs-2 mb-2 mt-5">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
                    <div class="fw-semibold text-white">Total Pendapatan</div>
                </div>
            </div>
        </div>
        <!--end::Col-->
        <!--begin::Col-->
        <div class="col-sm-6 col-xxl-3">
            <div class="card card-flush h-xl-100 bg-danger">
                <div class="card-body pt-5">
                    <div class="text-white fw-bold fs-2 mb-2 mt-5">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
                    <div class="fw-semibold text-white">Total Pengeluaran</div>
                </div>
            </div>
        </div>
        <!--end::Col-->
        <!--begin::Col-->
        <div class="col-sm-6 col-xxl-3">
            <div class="card card-flush h-xl-100 bg-primary">
                <div class="card-body pt-5">
                    <div class="text-white fw-bold fs-2 mb-2 mt-5">Rp {{ number_format($netProfit, 0, ',', '.') }}</div>
                    <div class="fw-semibold text-white">Keuntungan Bersih (Profit)</div>
                </div>
            </div>
        </div>
        <!--end::Col-->
        <!--begin::Col-->
        <div class="col-sm-6 col-xxl-3">
            <div class="card card-flush h-xl-100 bg-info">
                <div class="card-body pt-5">
                    <div class="text-white fw-bold fs-2 mb-2 mt-5">Rp {{ number_format($totalInvestment, 0, ',', '.') }}</div>
                    <div class="fw-semibold text-white">Total Investasi Masuk</div>
                </div>
            </div>
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->

    <!--begin::Row-->
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <div class="col-12">
            <div class="card card-flush">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-dark">Tren Keuangan 6 Bulan Terakhir</span>
                    </h3>
                </div>
                <div class="card-body pt-0">
                    <div id="finance_chart" style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Row-->

    <!--begin::Row-->
    <div class="row g-5 g-xl-10">
        <div class="col-md-6">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold text-dark">Pendapatan Terbaru</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle gs-0 gy-3 my-0">
                            <thead>
                                <tr class="fs-7 fw-bold text-gray-400 border-bottom-0">
                                    <th class="p-0 pb-3 min-w-100px text-start">TANGGAL</th>
                                    <th class="p-0 pb-3 min-w-100px text-end">PROYEK</th>
                                    <th class="p-0 pb-3 min-w-100px text-end">NOMINAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentIncomes as $income)
                                    <tr>
                                        <td><span class="text-gray-800 fw-bold">{{ $income->date->format('d M Y') }}</span></td>
                                        <td class="text-end"><span class="text-gray-800 fw-bold">{{ $income->pertanian->name ?? '-' }}</span></td>
                                        <td class="text-end"><span class="text-success fw-bold">Rp {{ number_format($income->amount, 0, ',', '.') }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">Belum ada data pendapatan</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold text-dark">Pengeluaran Terbaru</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle gs-0 gy-3 my-0">
                            <thead>
                                <tr class="fs-7 fw-bold text-gray-400 border-bottom-0">
                                    <th class="p-0 pb-3 min-w-100px text-start">TANGGAL</th>
                                    <th class="p-0 pb-3 min-w-100px text-end">PROYEK/TOKO</th>
                                    <th class="p-0 pb-3 min-w-100px text-end">NOMINAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPurchases as $purchase)
                                    <tr>
                                        <td><span class="text-gray-800 fw-bold">{{ $purchase->date->format('d M Y') }}</span></td>
                                        <td class="text-end">
                                            <span class="text-gray-800 fw-bold">{{ $purchase->pertanian->name ?? '-' }}</span><br>
                                            <span class="text-muted fs-7">{{ $purchase->store->name ?? '-' }}</span>
                                        </td>
                                        <td class="text-end"><span class="text-danger fw-bold">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">Belum ada data pengeluaran</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Row-->
        </div> <!-- end tab_keuangan -->
        
        <div class="tab-pane fade" id="tab_aktivitas" role="tabpanel">
            <!--begin::Row-->
            <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                <!--begin::Col-->
                <div class="col-sm-6 col-xxl-6">
                    <div class="card card-flush h-xl-100 bg-success">
                        <div class="card-body pt-5">
                            <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ $totalActiveProjects }}</div>
                            <div class="fw-semibold text-white">Proyek Aktif</div>
                        </div>
                    </div>
                </div>
                <!--end::Col-->
                <!--begin::Col-->
                <div class="col-sm-6 col-xxl-6">
                    <div class="card card-flush h-xl-100 bg-primary">
                        <div class="card-body pt-5">
                            <div class="text-white fw-bold fs-2 mb-2 mt-5">{{ $totalProjects }}</div>
                            <div class="fw-semibold text-white">Total Proyek</div>
                        </div>
                    </div>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->

            <!--begin::Row-->
            <div class="row g-5 g-xl-10">
                <div class="col-md-6">
                    <div class="card card-flush h-xl-100">
                        <div class="card-header pt-5">
                            <h3 class="card-title fw-bold text-dark">Proyek Terbaru</h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table table-row-dashed align-middle gs-0 gy-3 my-0">
                                    <thead>
                                        <tr class="fs-7 fw-bold text-gray-400 border-bottom-0">
                                            <th class="p-0 pb-3 min-w-100px text-start">NAMA PROYEK</th>
                                            <th class="p-0 pb-3 min-w-100px text-end">STATUS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentProjects as $project)
                                            <tr>
                                                <td><span class="text-gray-800 fw-bold">{{ $project->name }}</span></td>
                                                <td class="text-end"><span class="badge badge-light-{{ $project->status == 'aktif' ? 'success' : 'primary' }}">{{ ucfirst($project->status) }}</span></td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="2" class="text-center text-muted">Belum ada proyek</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-flush h-xl-100">
                        <div class="card-header pt-5">
                            <h3 class="card-title fw-bold text-dark">Pembaruan (Update) Terakhir</h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table table-row-dashed align-middle gs-0 gy-3 my-0">
                                    <thead>
                                        <tr class="fs-7 fw-bold text-gray-400 border-bottom-0">
                                            <th class="p-0 pb-3 min-w-100px text-start">TANGGAL</th>
                                            <th class="p-0 pb-3 min-w-100px text-start">PROYEK</th>
                                            <th class="p-0 pb-3 min-w-100px text-end">JUDUL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentUpdates as $update)
                                            <tr>
                                                <td><span class="text-gray-800 fw-bold">{{ $update->created_at->format('d M Y') }}</span></td>
                                                <td><span class="text-gray-800">{{ $update->pertanian->name ?? '-' }}</span></td>
                                                <td class="text-end"><span class="text-muted fw-bold">{{ Str::limit($update->title, 30) }}</span></td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center text-muted">Belum ada pembaruan</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Row-->
        </div> <!-- end tab_aktivitas -->
    </div> <!-- end tab-content -->
@endsection

@push('styles')
    <style>
        /* Card hover effects */
        .card-flush { transition: transform .2s ease-in-out; }
        .card-flush:hover { transform: translateY(-5px); }
    </style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    var options = {
        series: [{
            name: 'Pendapatan',
            data: {!! json_encode(array_reverse($monthlyIncome)) !!}
        }, {
            name: 'Pengeluaran',
            data: {!! json_encode(array_reverse($monthlyExpense)) !!}
        }],
        chart: {
            type: 'area',
            height: 350,
            toolbar: { show: false },
            fontFamily: 'Inter, sans-serif'
        },
        colors: ['#50cd89', '#f1416c'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        xaxis: {
            categories: {!! json_encode(array_reverse($months)) !!},
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                formatter: function (value) {
                    return "Rp " + value.toLocaleString("id-ID");
                }
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0.0,
                stops: [0, 100]
            }
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return "Rp " + val.toLocaleString("id-ID");
                }
            }
        }
    };

    var chart = new ApexCharts(document.querySelector("#finance_chart"), options);
    chart.render();
</script>
@endpush
