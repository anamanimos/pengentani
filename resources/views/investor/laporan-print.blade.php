<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan - {{ $pertanian->name }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
            font-size: 14px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #10B981;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0 0 5px 0;
            color: #10B981;
            font-size: 24px;
        }
        .header p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        .info-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .info-col {
            flex: 1;
        }
        .info-label {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-val {
            font-weight: 600;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background-color: #f3f4f6;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }
        td {
            font-size: 13px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .section-title {
            font-size: 16px;
            color: #111827;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 8px;
            margin-bottom: 15px;
            margin-top: 40px;
        }
        .summary-box {
            float: right;
            width: 300px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .summary-row.total {
            font-weight: 700;
            font-size: 16px;
            border-top: 1px solid #d1d5db;
            padding-top: 10px;
            margin-top: 5px;
        }
        .text-success { color: #059669; }
        .text-danger { color: #dc2626; }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .summary-box {
                break-inside: avoid;
            }
        }
        .btn-print {
            background: #10B981;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 20px;
            display: inline-block;
            text-decoration: none;
        }
        .btn-print:hover {
            background: #059669;
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align: center;">
        <button onclick="window.print()" class="btn-print">Cetak PDF / Print</button>
        <p style="font-size: 12px; color: #666; margin-top: -10px; margin-bottom: 20px;">Gunakan margin "Default" atau "None" saat mencetak untuk hasil terbaik.</p>
    </div>

    <div class="header">
        <h1>Laporan Keuangan Proyek</h1>
        <p>Aplikasi PengenTani - Dicetak pada {{ date('d M Y H:i') }} WIB</p>
    </div>

    <div class="info-grid">
        <div class="info-col">
            <div class="info-label">Nama Proyek</div>
            <div class="info-val">{{ $pertanian->name }}</div>
        </div>
        <div class="info-col">
            <div class="info-label">Kebun</div>
            <div class="info-val">{{ $pertanian->kebun->name ?? '-' }}</div>
        </div>
        <div class="info-col">
            <div class="info-label">Status</div>
            <div class="info-val">{{ $pertanian->status }}</div>
        </div>
        <div class="info-col">
            <div class="info-label">Periode</div>
            <div class="info-val">
                {{ \Carbon\Carbon::parse($pertanian->start_date)->format('d M Y') }} - 
                {{ $pertanian->end_date ? \Carbon\Carbon::parse($pertanian->end_date)->format('d M Y') : 'Berjalan' }}
            </div>
        </div>
    </div>

    <!-- Pemasukan -->
    <h2 class="section-title text-success">Pencatatan Pemasukan (Penjualan / Panen)</h2>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="20%">Jenis</th>
                <th width="40%">Deskripsi</th>
                <th width="20%" class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pertanian->incomes as $index => $income)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($income->date)->format('d M Y') }}</td>
                <td>{{ $income->type ?? 'Panen' }}</td>
                <td>{{ $income->description ?? '-' }}</td>
                <td class="text-right text-success fw-bold">+ {{ number_format($income->nominal, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center" style="color: #9ca3af; font-style: italic;">Belum ada pencatatan pemasukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pengeluaran Barang -->
    <h2 class="section-title text-danger">Pencatatan Pengeluaran (Pembelian Barang/Pupuk)</h2>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="20%">Toko & Nota</th>
                <th width="40%">Rincian Barang</th>
                <th width="20%" class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php $noPurchase = 1; @endphp
            @forelse($pertanian->purchases as $purchase)
                @foreach($purchase->items as $item)
                <tr>
                    <td class="text-center">{{ $noPurchase++ }}</td>
                    <td>{{ \Carbon\Carbon::parse($purchase->date)->format('d M Y') }}</td>
                    <td>
                        <strong>{{ $purchase->store->name ?? '-' }}</strong><br>
                        <span style="font-size: 11px; color: #6b7280;">Nota: {{ $purchase->invoice_number ?? '-' }}</span>
                    </td>
                    <td>
                        {{ $item->name }}<br>
                        <span style="font-size: 11px; color: #6b7280;">{{ $item->qty }} x Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                    </td>
                    <td class="text-right text-danger">- {{ number_format($item->total_price, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            @empty
            <tr>
                <td colspan="5" class="text-center" style="color: #9ca3af; font-style: italic;">Belum ada pencatatan pembelian.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pengeluaran Upah Pekerja -->
    <h2 class="section-title text-danger">Pencatatan Pengeluaran (Upah Pekerja)</h2>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="25%">Nama Pekerja</th>
                <th width="35%">Jenis Pekerjaan</th>
                <th width="20%" class="text-right">Upah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pertanian->workerJobs as $index => $job)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($job->date)->format('d M Y') }}</td>
                <td>{{ $job->worker->name ?? '-' }}</td>
                <td>{{ $job->jobCategory->name ?? '-' }}</td>
                <td class="text-right text-danger">- {{ number_format($job->wage, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center" style="color: #9ca3af; font-style: italic;">Belum ada pencatatan upah pekerja.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Summary / Ringkasan -->
    <div class="clearfix" style="margin-top: 40px; margin-bottom: 40px;">
        <div class="summary-box">
            <div class="summary-row">
                <span>Total Pemasukan</span>
                <span class="text-success">Rp {{ number_format($totalIncome, 0, ',', '.') }}</span>
            </div>
            <div class="summary-row">
                <span>Total Belanja Barang</span>
                <span class="text-danger">- Rp {{ number_format($totalPurchase, 0, ',', '.') }}</span>
            </div>
            <div class="summary-row">
                <span>Total Upah Pekerja</span>
                <span class="text-danger">- Rp {{ number_format($totalWorker, 0, ',', '.') }}</span>
            </div>
            <div class="summary-row total">
                <span>Laba Sementara</span>
                <span class="{{ $laba_sementara >= 0 ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format($laba_sementara, 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    <script>
        // Opsional: Otomatis memicu dialog print saat halaman dimuat
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
