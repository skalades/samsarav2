<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Keuangan & Mutasi Kas — {{ $profile['expedition_name'] ?? 'SAMSARA' }}</title>
  <style>
    @page {
      size: A4 portrait;
      margin: 15mm 12mm 15mm 12mm;
    }
    body {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 11px;
      color: #111;
      background: #fff;
      line-height: 1.4;
      margin: 0;
      padding: 10px;
    }
    .header {
      border-bottom: 2.5px solid #000;
      padding-bottom: 8px;
      margin-bottom: 12px;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
    }
    .brand-title {
      font-size: 18px;
      font-weight: 900;
      letter-spacing: 0.5px;
    }
    .brand-sub {
      font-size: 10px;
      color: #444;
      font-weight: bold;
    }
    .report-meta {
      text-align: right;
      font-size: 9.5px;
      color: #333;
    }
    .doc-title {
      text-align: center;
      font-size: 14px;
      font-weight: bold;
      text-transform: uppercase;
      margin: 12px 0 4px 0;
      text-decoration: underline;
    }
    .doc-subtitle {
      text-align: center;
      font-size: 10px;
      color: #555;
      margin-bottom: 14px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 14px;
    }
    th, td {
      border: 1px solid #ccc;
      padding: 5px 7px;
      font-size: 10px;
    }
    th {
      background-color: #f2f4f7;
      font-weight: bold;
      text-transform: uppercase;
      font-size: 9px;
    }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .font-bold { font-weight: bold; }
    .bg-summary {
      background-color: #fafbfc;
    }
    .highlight-green {
      color: #047857;
      font-weight: bold;
    }
    .highlight-red {
      color: #b91c1c;
      font-weight: bold;
    }
    .signature-grid {
      display: flex;
      justify-content: space-between;
      margin-top: 30px;
      page-break-inside: avoid;
    }
    .signature-box {
      width: 30%;
      text-align: center;
      font-size: 10px;
    }
    .signature-line {
      margin-top: 55px;
      border-top: 1px solid #000;
      font-weight: bold;
      padding-top: 3px;
    }
    .no-print {
      margin-bottom: 15px;
      background: #f8fafc;
      border: 1px solid #cbd5e1;
      padding: 10px;
      border-radius: 6px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .print-btn {
      background: #0f172a;
      color: #fff;
      padding: 7px 16px;
      border: none;
      border-radius: 4px;
      font-weight: bold;
      cursor: pointer;
    }
    @media print {
      .no-print { display: none !important; }
      body { padding: 0; }
    }
  </style>
</head>
<body>

  <!-- No-Print Action Toolbar -->
  <div class="no-print">
    <div>
      <strong>Pratinjau Cetak Laporan Keuangan Resmi</strong> &mdash; Siap cetak ke kertas A4 atau simpan sebagai PDF.
    </div>
    <button onclick="window.print()" class="print-btn">CETAK DOKUMEN / SIMPAN PDF</button>
  </div>

  <!-- Header / Kop Dokumen -->
  <div class="header">
    <div style="display: flex; align-items: center; gap: 12px;">
      @if(!empty($profile['app_logo']))
        <img src="{{ asset('storage/' . $profile['app_logo']) }}" style="max-height: 48px; max-width: 60px; object-fit: contain;" alt="Logo">
      @endif
      <div>
        <div class="brand-title">{{ strtoupper($profile['expedition_name'] ?? 'SAMSARA EXPEDITION') }}</div>
        <div class="brand-sub">SISTEM OPERASIONAL BASECAMP {{ strtoupper($profile['basecamp_name'] ?? 'SAMSARA') }}</div>
        <div style="font-size: 9px; color: #555;">Kawasan Konservasi {{ $profile['mountain_name'] ?? 'Gunung Cikuray' }} ({{ $profile['mountain_elevation'] ?? '2.821 MDPL' }})</div>
      </div>
    </div>
    <div class="report-meta">
      <div><strong>Kode Laporan:</strong> FIN-{{ date('Ymd-His') }}</div>
      <div><strong>Periode Audit:</strong> {{ $startDate->translatedFormat('d F Y') }} &mdash; {{ $endDate->translatedFormat('d F Y') }}</div>
      <div><strong>Waktu Cetak:</strong> {{ $printedAt->translatedFormat('d F Y, H:i') }} WIB</div>
    </div>
  </div>

  <!-- Judul Laporan -->
  <div class="doc-title">REKAPITULASI KEUANGAN & ARUS KAS OPERASIONAL</div>
  <div class="doc-subtitle">Laporan Resmi Pemasukan, Beban Pengeluaran, dan Rekonsiliasi Kas Basecamp</div>

  <!-- Tabel 1: Ringkasan Eksekutif -->
  <table>
    <thead>
      <tr>
        <th style="width: 45%;">Indikator Keuangan</th>
        <th style="width: 25%; text-align: right;">Nominal (Rp)</th>
        <th style="width: 30%;">Catatan Akuntabilitas</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="font-bold">1. Total Kas Masuk (Gross Revenue)</td>
        <td class="text-right font-bold highlight-green">Rp {{ number_format($summary['total_inflow'], 0, ',', '.') }}</td>
        <td>{{ $summary['total_transactions'] }} Transaksi (SIMAKSI, POS, Ojek, Parkir Motor & Mobil)</td>
      </tr>
      <tr>
        <td class="font-bold">2. Total Beban Pengeluaran (Total Expenses)</td>
        <td class="text-right font-bold highlight-red">- Rp {{ number_format($summary['total_outflow'], 0, ',', '.') }}</td>
        <td>Honor Payout Ojek + Beban Operasional Kas</td>
      </tr>
      <tr class="bg-summary font-bold">
        <td>3. LABA BERSIH OPERASIONAL (NET PROFIT)</td>
        <td class="text-right font-bold {{ $summary['net_profit'] >= 0 ? 'highlight-green' : 'highlight-red' }}">
          {{ $summary['net_profit'] < 0 ? '-' : '' }}Rp {{ number_format(abs($summary['net_profit']), 0, ',', '.') }}
        </td>
        <td>Margin Laba: <strong>{{ $summary['profit_margin'] }}%</strong> dari total omzet</td>
      </tr>
      <tr class="bg-summary">
        <td class="font-bold">4. Saldo Kas Fisik Laci (Cash in Drawer)</td>
        <td class="text-right font-bold" style="color: #0369a1;">Rp {{ number_format($summary['net_cash_in_drawer'], 0, ',', '.') }}</td>
        <td>Wajib cocok dengan fisik uang tunai di posko</td>
      </tr>
    </tbody>
  </table>

  <!-- Tabel 2: Rincian Pemasukan & Pengeluaran -->
  <div style="display: flex; gap: 10px;">
    
    <!-- Kolom Pemasukan -->
    <div style="flex: 1;">
      <div style="font-size: 9.5px; font-weight: bold; margin-bottom: 4px; text-transform: uppercase;">A. Rincian Sumber Pemasukan:</div>
      <table>
        <thead>
          <tr>
            <th>Sumber Pemasukan</th>
            <th class="text-right">Jumlah (Rp)</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              Izin Masuk SIMAKSI
              <div style="font-size: 8px; color: #555;">{{ $summary['total_climbers'] ?? 0 }} Pendaki Terdaftar</div>
            </td>
            <td class="text-right">Rp {{ number_format($summary['revenue_stream_breakdown']['simaksi_permit'], 0, ',', '.') }}</td>
          </tr>
          <tr>
            <td>
              Titipan Parkir Sepeda Motor (Roda 2)
              <div style="font-size: 8px; color: #555;">{{ $summary['total_motorcycles'] ?? 0 }} Unit &bull; Tarif: Rp {{ number_format($summary['tariff_rates']['motorcycle'] ?? 10000, 0, ',', '.') }}/unit</div>
            </td>
            <td class="text-right font-bold" style="color: #b45309;">Rp {{ number_format($summary['revenue_stream_breakdown']['parking_motor'] ?? 0, 0, ',', '.') }}</td>
          </tr>
          <tr>
            <td>
              Titipan Parkir Mobil Pribadi (Roda 4)
              <div style="font-size: 8px; color: #555;">{{ $summary['total_cars'] ?? 0 }} Unit &bull; Tarif: Rp {{ number_format($summary['tariff_rates']['car'] ?? 25000, 0, ',', '.') }}/unit</div>
            </td>
            <td class="text-right font-bold" style="color: #0369a1;">Rp {{ number_format($summary['revenue_stream_breakdown']['parking_car'] ?? 0, 0, ',', '.') }}</td>
          </tr>
          <tr>
            <td>Layanan Shuttle Ojek Terdaftar</td>
            <td class="text-right">Rp {{ number_format($summary['revenue_stream_breakdown']['ojek_shuttle'], 0, ',', '.') }}</td>
          </tr>
          <tr>
            <td>POS Kasir & Logistik Basecamp</td>
            <td class="text-right">Rp {{ number_format($summary['revenue_stream_breakdown']['pos_retail_and_rentals'], 0, ',', '.') }}</td>
          </tr>
          <tr>
            <td>Jaminan Sampah Hangus (Kas Jalur)</td>
            <td class="text-right">Rp {{ number_format($summary['revenue_stream_breakdown']['forfeited_deposit'], 0, ',', '.') }}</td>
          </tr>
          <tr class="font-bold bg-summary">
            <td>Kas Masuk Tunai</td>
            <td class="text-right">Rp {{ number_format($summary['inflow_channels']['cash'], 0, ',', '.') }}</td>
          </tr>
          <tr class="font-bold bg-summary">
            <td>Kas Masuk Non-Tunai (QRIS/TF)</td>
            <td class="text-right">Rp {{ number_format($summary['inflow_channels']['qris'] + $summary['inflow_channels']['transfer'], 0, ',', '.') }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Kolom Pengeluaran -->
    <div style="flex: 1;">
      <div style="font-size: 9.5px; font-weight: bold; margin-bottom: 4px; text-transform: uppercase;">B. Rincian Beban Operasional:</div>
      <table>
        <thead>
          <tr>
            <th>Pos Pengeluaran</th>
            <th class="text-right">Jumlah (Rp)</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Pencairan Honor Driver Ojek</td>
            <td class="text-right highlight-red">Rp {{ number_format($summary['ojek_payouts_total'], 0, ',', '.') }}</td>
          </tr>
          <tr>
            <td>BBM Genset & Listrik</td>
            <td class="text-right">Rp {{ number_format($summary['expense_categories']['bbm_genset'], 0, ',', '.') }}</td>
          </tr>
          <tr>
            <td>P3K & Logistik Darurat SAR</td>
            <td class="text-right">Rp {{ number_format($summary['expense_categories']['logistik_sar'], 0, ',', '.') }}</td>
          </tr>
          <tr>
            <td>Operasional Pos & ATK</td>
            <td class="text-right">Rp {{ number_format($summary['expense_categories']['operasional_pos'], 0, ',', '.') }}</td>
          </tr>
          <tr>
            <td>Konsumsi Ranger & Patroli</td>
            <td class="text-right">Rp {{ number_format($summary['expense_categories']['konsumsi_petugas'], 0, ',', '.') }}</td>
          </tr>
          <tr>
            <td>Sampah & Pemeliharaan Jalur</td>
            <td class="text-right">Rp {{ number_format($summary['expense_categories']['kebersihan_sampah'] + $summary['expense_categories']['perbaikan_alat'] + $summary['expense_categories']['lainnya'], 0, ',', '.') }}</td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>

  <!-- Tabel 3: Jurnal Mutasi Kas Terbaru -->
  <div style="font-size: 9.5px; font-weight: bold; margin-bottom: 4px; text-transform: uppercase;">C. Catatan Jurnal Mutasi Kas Terpadu:</div>
  <table>
    <thead>
      <tr>
        <th style="width: 12%;">Waktu</th>
        <th style="width: 8%; text-align: center;">Tipe</th>
        <th style="width: 18%;">Kode Bukti</th>
        <th style="width: 32%;">Keterangan Transaksi</th>
        <th style="width: 8%; text-align: center;">Kanal</th>
        <th style="width: 14%; text-align: right;">Nominal (Rp)</th>
        <th style="width: 8%;">Kasir</th>
      </tr>
    </thead>
    <tbody>
      @forelse(array_slice($ledger, 0, 40) as $row)
        <tr>
          <td>{{ $row['datetime']->format('d/m/y H:i') }}</td>
          <td class="text-center font-bold {{ $row['type'] === 'IN' ? 'highlight-green' : 'highlight-red' }}">
            {{ $row['type'] === 'IN' ? '+ MASUK' : '- KELUAR' }}
          </td>
          <td style="font-family: monospace;">{{ $row['code'] }}</td>
          <td>{{ $row['description'] }}</td>
          <td class="text-center">{{ strtoupper($row['payment_method']) }}</td>
          <td class="text-right font-bold {{ $row['type'] === 'IN' ? 'highlight-green' : 'highlight-red' }}">
            {{ $row['type'] === 'IN' ? '+' : '-' }} {{ number_format($row['amount'], 0, ',', '.') }}
          </td>
          <td>{{ $row['actor'] }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="text-center">Tidak ada transaksi tercatat pada periode ini.</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  <!-- Kolom Tanda Tangan Resmi -->
  <div class="signature-grid">
    <div class="signature-box">
      <div>Dibuat Oleh,</div>
      <div style="font-weight: bold; margin-top: 2px;">Kasir & Bendahara Posko</div>
      <div class="signature-line">( {{ auth()->user()->name ?? 'Petugas Kasir' }} )</div>
    </div>
    <div class="signature-box">
      <div>Diverifikasi Oleh,</div>
      <div style="font-weight: bold; margin-top: 2px;">Auditor Operasional</div>
      <div class="signature-line">( Petugas Verifikasi )</div>
    </div>
    <div class="signature-box">
      <div>Mengetahui & Menyetujui,</div>
      <div style="font-weight: bold; margin-top: 2px;">Ketua Pengelola Jalur</div>
      <div class="signature-line">( Pengelola SAMSARA )</div>
    </div>
  </div>

  <div style="margin-top: 20px; text-align: center; font-size: 8px; color: #666; border-top: 1px solid #ddd; padding-top: 5px;">
    Dokumen ini dicetak secara otomatis dari Sistem Manajemen Operasional Terpadu SAMSARA. Sah dan akuntabel sebagai bukti rekapitulasi keuangan basecamp.
  </div>

</body>
</html>
