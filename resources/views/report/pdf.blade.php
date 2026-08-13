<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Gabungan Transaksi - PengenTani</title>
    <style>
        @page {
            margin: 15px 20px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333333;
            line-height: 1.3;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border-bottom: 2px solid #2b2b40;
            padding-bottom: 10px;
        }
        .header-title {
            font-size: 18px;
            font-weight: bold;
            color: #1e1e2d;
            margin: 0;
        }
        .header-subtitle {
            font-size: 11px;
            color: #7e8299;
            margin-top: 3px;
        }
        .meta-text {
            text-align: right;
            font-size: 9px;
            color: #5e6278;
        }
        
        /* Summary Box */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .summary-card {
            background-color: #f4f6fa;
            border: 1px solid #e4e6ef;
            padding: 8px 12px;
            border-radius: 4px;
            text-align: center;
        }
        .summary-label {
            font-size: 9px;
            color: #7e8299;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .summary-value {
            font-size: 12px;
            font-weight: bold;
        }
        .text-success { color: #50cd89; }
        .text-danger { color: #f1416c; }
        .text-warning { color: #ffc700; }
        .text-primary { color: #009ef7; }
        .text-info { color: #7239ea; }

        /* Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th {
            background-color: #1e1e2d;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            padding: 7px 5px;
            text-align: left;
            border: 1px solid #1e1e2d;
        }
        .data-table td {
            padding: 6px 5px;
            border: 1px solid #e4e6ef;
            font-size: 9px;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9f9fb;
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 3px;
            text-align: center;
        }
        .badge-success { background-color: #e8fff3; color: #50cd89; border: 1px solid #b5edd4; }
        .badge-info { background-color: #f8f5ff; color: #7239ea; border: 1px solid #d4c2f8; }
        .badge-warning { background-color: #fff8dd; color: #f1416c; border: 1px solid #ffe399; }

        .filter-summary-box {
            background-color: #f4f6fa;
            border: 1px solid #e4e6ef;
            padding: 6px 10px;
            border-radius: 4px;
            margin-bottom: 12px;
            font-size: 9px;
        }
        .filter-badge {
            display: inline-block;
            background-color: #ffffff;
            border: 1px solid #d8d8d8;
            padding: 2px 6px;
            border-radius: 3px;
            margin-left: 3px;
            margin-bottom: 2px;
            color: #1e1e2d;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }

        .footer-note {
            margin-top: 10px;
            font-size: 8px;
            color: #a1a5b7;
            text-align: justify;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <div class="header-title">PengenTani — Laporan Gabungan Transaksi</div>
                <div class="header-subtitle">Gabungan Pendapatan, Pembelian Material, & Upah Pekerja</div>
            </td>
            <td class="meta-text" style="vertical-align: middle;">
                <div><strong>Dicetak Pada:</strong> {{ date('d/m/Y H:i') }}</div>
                <div><strong>Pengguna:</strong> {{ Auth::user()->name ?? 'PengenTani Console' }}</div>
                <div><strong>Rentang Filter:</strong> {{ $startDate ? date('d/m/Y', strtotime($startDate)) : 'Awal' }} - {{ $endDate ? date('d/m/Y', strtotime($endDate)) : 'Akhir' }}</div>
            </td>
        </tr>
    </table>

    <!-- Summary Box -->
    <table class="summary-table">
        <tr>
            <td width="20%" style="padding-right: 5px;">
                <div class="summary-card">
                    <div class="summary-label">Total Pendapatan</div>
                    <div class="summary-value text-success">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
                </div>
            </td>
            <td width="20%" style="padding: 0 5px;">
                <div class="summary-card">
                    <div class="summary-label">Total Pembelian</div>
                    <div class="summary-value text-danger">Rp {{ number_format($totalPurchase, 0, ',', '.') }}</div>
                </div>
            </td>
            <td width="20%" style="padding: 0 5px;">
                <div class="summary-card">
                    <div class="summary-label">Total Upah</div>
                    <div class="summary-value text-warning">Rp {{ number_format($totalWorker, 0, ',', '.') }}</div>
                </div>
            </td>
            <td width="20%" style="padding: 0 5px;">
                <div class="summary-card">
                    <div class="summary-label">Total Konsumsi</div>
                    <div class="summary-value text-warning">Rp {{ number_format($totalKonsumsi, 0, ',', '.') }}</div>
                </div>
            </td>
            <td width="20%" style="padding-left: 5px;">
                <div class="summary-card" style="background-color: #f1f0fe;">
                    <div class="summary-label">Arus Kas Bersih</div>
                    <div class="summary-value {{ $netCashflow >= 0 ? 'text-primary' : 'text-danger' }}">Rp {{ number_format($netCashflow, 0, ',', '.') }}</div>
                </div>
            </td>
        </tr>
    </table>

    @if(!empty($activeFilterSummary) && is_array($activeFilterSummary) && count($activeFilterSummary) > 0)
        <div class="filter-summary-box">
            <strong style="color: #1e1e2d;">Filter Aktif:</strong>
            @foreach($activeFilterSummary as $f)
                <span class="filter-badge">
                    <strong>{{ $f['title'] }}:</strong> {{ $f['value'] }}
                </span>
            @endforeach
        </div>
    @endif

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="3%" class="text-center">No</th>
                <th width="8%">Tanggal</th>
                <th width="12%">Jenis Transaksi</th>
                <th width="14%">Pertanian</th>
                <th width="12%">Kategori</th>
                <th width="11%">Pihak Terkait</th>
                <th width="12%">Catatan</th>
                <th width="5%" class="text-right">Qty</th>
                <th width="9%" class="text-right">Satuan / Upah</th>
                <th width="7%" class="text-right">Konsumsi</th>
                <th width="9%" class="text-right">Total</th>
                <th width="8%" class="text-center">Bukti</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row['date'] }}</td>
                    <td>
                        @if($row['type_label'] === 'Pendapatan')
                            <span class="badge badge-success">Pendapatan</span>
                        @elseif($row['type_label'] === 'Upah Pekerja')
                            <span class="badge badge-warning">Upah Pekerja</span>
                        @else
                            <span class="badge badge-info">Pembelian</span>
                        @endif
                    </td>
                    <td>{{ $row['pertanian_name'] }}</td>
                    <td>{{ $row['item_name'] }}</td>
                    <td>{{ $row['party_name'] }}</td>
                    <td>{{ $row['notes'] }}</td>
                    <td class="text-right">{{ number_format($row['qty'], 2, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($row['unit_price'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($row['konsumsi'], 0, ',', '.') }}</td>
                    <td class="text-right fw-bold {{ $row['type_code'] === 'income' ? 'text-success' : 'text-danger' }}">
                        Rp {{ number_format($row['total'], 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        @if(!empty($row['proof_url']))
                            <a href="{{ $row['proof_url'] }}" target="_blank" style="color: #009ef7; font-weight: bold; text-decoration: underline;">Lihat Bukti</a>
                        @else
                            <span style="color: #a1a5b7;">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="padding: 20px; color: #a1a5b7;">
                        Tidak ada data transaksi yang ditemukan untuk filter ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-note">
        * Dokumen ini dibuat secara otomatis oleh Sistem Manajemen Pertanian PengenTani pada {{ date('d F Y, H:i:s') }}.
    </div>
</body>
</html>
