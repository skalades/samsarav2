<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>E-Tiket SIMAKSI - {{ $registration->registration_code }}</title>
  <style>
    @page {
      margin: 12mm 13mm;
      size: a4 portrait;
    }

    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      color: #1f2937;
      font-size: 10px;
      line-height: 1.38;
      margin: 0;
      padding: 0;
    }

    table {
      border-collapse: collapse;
    }

    .w-full {
      width: 100%;
      table-layout: fixed;
    }

    /* Header */
    .header-wrap {
      width: 100%;
      table-layout: fixed;
      margin-bottom: 10px;
    }

    .header-wrap td {
      padding-bottom: 10px;
      border-bottom: 2px solid #064e3b;
      vertical-align: middle;
    }

    .brand-title {
      font-size: 16px;
      font-weight: bold;
      color: #064e3b;
      letter-spacing: 0.8px;
      text-transform: uppercase;
      margin: 0 0 2px 0;
    }

    .brand-subtitle {
      font-size: 9px;
      color: #4b5563;
      line-height: 1.35;
    }

    .ticket-code-badge {
      font-family: 'Courier New', Courier, monospace;
      font-size: 12px;
      font-weight: bold;
      background-color: #111827;
      color: #34d399;
      padding: 5px 10px;
      border-radius: 4px;
      display: inline-block;
      letter-spacing: 0.8px;
    }

    .status-badge {
      display: inline-block;
      margin-top: 5px;
      padding: 3px 8px;
      border-radius: 4px;
      font-size: 8.5px;
      font-weight: bold;
      color: #ffffff;
      text-transform: uppercase;
      letter-spacing: 0.4px;
    }

    /* Banner Judul */
    .doc-title-box {
      background-color: #ecfdf5;
      border: 1px solid #a7f3d0;
      border-radius: 5px;
      padding: 7px 10px;
      margin-bottom: 10px;
      text-align: center;
    }

    .doc-title {
      font-size: 11.5px;
      font-weight: bold;
      color: #065f46;
      letter-spacing: 0.4px;
    }

    .doc-desc {
      font-size: 8.5px;
      color: #047857;
      margin-top: 2px;
    }

    /* Cards & Key-Value Tables */
    .section-heading {
      font-weight: bold;
      font-size: 9.5px;
      color: #111827;
      text-transform: uppercase;
      letter-spacing: 0.3px;
      margin: 0 0 4px 0;
    }

    .card-panel {
      border: 1px solid #d1d5db;
      border-radius: 5px;
      background-color: #f9fafb;
      padding: 8px 10px;
      vertical-align: top;
    }

    .card-heading {
      font-size: 9.5px;
      font-weight: bold;
      color: #064e3b;
      text-transform: uppercase;
      letter-spacing: 0.3px;
      padding-bottom: 4px;
      margin-bottom: 5px;
      border-bottom: 1px solid #e5e7eb;
    }

    .kv-table {
      width: 100%;
      table-layout: fixed;
    }

    .kv-table td {
      padding: 2px 0;
      vertical-align: top;
      font-size: 9px;
      line-height: 1.32;
      word-wrap: break-word;
      overflow-wrap: break-word;
    }

    .kv-label {
      width: 37%;
      color: #6b7280;
      padding-right: 6px;
    }

    .kv-val {
      width: 63%;
      color: #111827;
      font-weight: bold;
    }

    .kv-sep td {
      padding-top: 5px;
      border-top: 1px dashed #d1d5db;
    }

    /* Manifest Table */
    .manifest-table {
      width: 100%;
      table-layout: fixed;
      margin-bottom: 10px;
    }

    .manifest-table thead {
      display: table-header-group;
    }

    .manifest-table tr {
      page-break-inside: avoid;
    }

    .manifest-table th {
      background-color: #064e3b;
      color: #ffffff;
      font-size: 8.5px;
      font-weight: bold;
      text-transform: uppercase;
      padding: 5px 6px;
      text-align: left;
      border: 1px solid #064e3b;
    }

    .manifest-table td {
      font-size: 8.8px;
      padding: 5px 6px;
      border: 1px solid #e5e7eb;
      color: #1f2937;
      vertical-align: middle;
      word-wrap: break-word;
      overflow-wrap: break-word;
    }

    .manifest-table tbody tr:nth-child(even) td {
      background-color: #f9fafb;
    }

    /* POS Ranger Table */
    .pos-table {
      width: 100%;
      table-layout: fixed;
      margin-bottom: 10px;
      page-break-inside: avoid;
    }

    .pos-table th {
      background-color: #f3f4f6;
      color: #111827;
      font-size: 8.5px;
      font-weight: bold;
      padding: 4px 6px;
      border: 1px solid #d1d5db;
      text-align: center;
    }

    .pos-table td {
      border: 1px solid #d1d5db;
      padding: 6px 7px;
      vertical-align: top;
      font-size: 8.5px;
      color: #374151;
      line-height: 1.5;
    }

    .chk {
      display: inline-block;
      width: 8px;
      height: 8px;
      border: 1px solid #4b5563;
      background-color: #ffffff;
      vertical-align: middle;
      margin-right: 4px;
    }

    .pos-sign {
      font-size: 8px;
      color: #6b7280;
      margin-top: 5px;
      padding-top: 4px;
      border-top: 1px dotted #d1d5db;
    }

    /* SOP & Finance */
    .bottom-grid {
      width: 100%;
      table-layout: fixed;
      margin-bottom: 10px;
      page-break-inside: avoid;
    }

    .sop-panel {
      border: 1px dashed #d97706;
      border-radius: 5px;
      background-color: #fffbeb;
      padding: 8px 10px;
      vertical-align: top;
    }

    .sop-title {
      font-size: 9px;
      font-weight: bold;
      color: #92400e;
      text-transform: uppercase;
      padding-bottom: 4px;
      margin-bottom: 4px;
      border-bottom: 1px dashed #fcd34d;
    }

    .sop-list {
      margin: 0;
      padding-left: 13px;
      color: #78350f;
      font-size: 8.2px;
      line-height: 1.4;
    }

    .sop-list li {
      margin-bottom: 2px;
    }

    .finance-table {
      width: 100%;
      table-layout: fixed;
    }

    .finance-table td {
      padding: 2.5px 0;
      font-size: 8.8px;
      vertical-align: top;
    }

    .finance-total td {
      padding-top: 5px;
      padding-bottom: 3px;
      border-top: 1px solid #cbd5e1;
      font-weight: bold;
      color: #065f46;
      font-size: 9.5px;
    }

    .finance-deposit td {
      padding-top: 4px;
      border-top: 1px dashed #d1d5db;
      color: #92400e;
      font-size: 8px;
    }

    /* Footer */
    .footer-table {
      width: 100%;
      table-layout: fixed;
      page-break-inside: avoid;
    }

    .footer-table td {
      border-top: 1px solid #d1d5db;
      padding-top: 8px;
      font-size: 8px;
      color: #6b7280;
      vertical-align: top;
    }

    .qr-code-tag {
      font-family: 'Courier New', Courier, monospace;
      font-size: 8.5px;
      font-weight: bold;
      color: #064e3b;
      border: 1px solid #064e3b;
      padding: 2px 6px;
      display: inline-block;
      background-color: #ecfdf5;
    }
  </style>
</head>
<body>

  <!-- Header Instansi & Verifikasi -->
  <table class="header-wrap">
    <tr>
      <td style="width: 65%;">
        <table class="w-full">
          <tr>
            @if(!empty($profile['app_logo']) && file_exists(public_path('storage/' . $profile['app_logo'])))
              <td style="width: 48px; border: none; padding: 0 8px 0 0; vertical-align: middle;">
                <img src="{{ public_path('storage/' . $profile['app_logo']) }}" style="max-height: 42px; max-width: 44px;" alt="Logo">
              </td>
            @endif
            <td style="border: none; padding: 0; vertical-align: middle;">
              <div class="brand-title">{{ $profile['expedition_name'] ?? 'SAMSARA EXPEDITION' }}</div>
              <div class="brand-subtitle">
                Otoritas Pengelola Jalur Resmi {{ $profile['mountain_name'] ?? 'Gunung Cikuray' }} ({{ $profile['mountain_elevation'] ?? '2.821 MDPL' }})<br>
                Lokasi: {{ $profile['basecamp_location'] ?? 'Garut, Jawa Barat' }} | Hotline: {{ $profile['emergency_hotline'] ?? '' }}
              </div>
            </td>
          </tr>
        </table>
      </td>
      <td style="width: 35%; text-align: right;">
        <div class="ticket-code-badge">{{ $registration->registration_code }}</div><br>
        <span class="status-badge" style="background-color: {{ $statusColor }};">{{ $statusLabel }}</span>
      </td>
    </tr>
  </table>

  <!-- Banner Judul Dokumen Resmi -->
  <div class="doc-title-box">
    <div class="doc-title">SURAT IZIN MASUK KAWASAN KONSERVASI (SIMAKSI) &amp; E-TIKET RESMI</div>
    <div class="doc-desc">Dokumen elektronik sah untuk verifikasi administrasi di Pos Registrasi &amp; Gate Check-in Basecamp SAMSARA</div>
  </div>

  <!-- Informasi Rombongan & Jadwal (Strict Table Grid) -->
  <table class="w-full" style="margin-bottom: 10px; border-collapse: separate; border-spacing: 0;">
    <tr>
      <!-- Kolom Kiri: Data Ketua & Kontak Darurat -->
      <td class="card-panel" style="width: 49%;">
        <div class="card-heading">Penanggung Jawab (Ketua Rombongan)</div>
        <table class="kv-table">
          <tr>
            <td class="kv-label">Nama Ketua</td>
            <td class="kv-val">{{ $registration->leader_name }}</td>
          </tr>
          <tr>
            <td class="kv-label">NIK / Identitas</td>
            <td class="kv-val" style="font-family: 'Courier New', Courier, monospace;">{{ $registration->leader_nik }}</td>
          </tr>
          <tr>
            <td class="kv-label">No. WhatsApp</td>
            <td class="kv-val">{{ $registration->leader_phone }}</td>
          </tr>
          <tr>
            <td class="kv-label">Kota / Alamat</td>
            <td class="kv-val">{{ $registration->leader_city ?: $registration->leader_address ?: '-' }}</td>
          </tr>
          <tr class="kv-sep">
            <td class="kv-label">Kontak Darurat</td>
            <td class="kv-val">{{ $registration->emergency_contact_name }} ({{ $registration->emergency_relationship ?: 'Keluarga' }})</td>
          </tr>
          <tr>
            <td class="kv-label">Telp. Darurat</td>
            <td class="kv-val">{{ $registration->emergency_contact_phone }}</td>
          </tr>
        </table>
      </td>

      <td style="width: 2%;"></td>

      <!-- Kolom Kanan: Jadwal & Fasilitas Logistik -->
      <td class="card-panel" style="width: 49%;">
        <div class="card-heading">Jadwal &amp; Fasilitas Ekspedisi</div>
        <table class="kv-table">
          <tr>
            <td class="kv-label">Kategori Izin</td>
            <td class="kv-val" style="color: {{ $registration->isTektok() ? '#0284c7' : '#047857' }};">
              {{ $registration->isTektok() ? 'TEKTOK (03:00 - 07:00 WIB)' : 'CAMP (MULAI 07:00 WIB)' }}
            </td>
          </tr>
          <tr>
            <td class="kv-label">Aturan Camp</td>
            <td class="kv-val">{{ $registration->isTektok() ? 'DILARANG CAMP (Turun Hari Sama)' : 'Resmi di Pos 2 atau Pos 3' }}</td>
          </tr>
          <tr>
            <td class="kv-label">Tanggal Naik</td>
            <td class="kv-val" style="color: #065f46;">{{ \Carbon\Carbon::parse($registration->hiking_date)->translatedFormat('l, d F Y') }}</td>
          </tr>
          <tr>
            <td class="kv-label">Rencana Turun</td>
            <td class="kv-val">{{ $registration->return_date ? \Carbon\Carbon::parse($registration->return_date)->translatedFormat('l, d F Y') : 'Hari yang sama (Tek-tok)' }}</td>
          </tr>
          <tr>
            <td class="kv-label">Jumlah Anggota</td>
            <td class="kv-val">{{ $registration->total_climbers }} Orang Pendaki</td>
          </tr>
          <tr>
            <td class="kv-label">Shuttle Ojek</td>
            <td class="kv-val">{{ $registration->ojek_count > 0 ? $registration->ojek_count . ' Trip Voucher Ojek' : 'Tidak pesan (Jalan Kaki)' }}</td>
          </tr>
          <tr>
            <td class="kv-label">Parkir Kendaraan</td>
            <td class="kv-val">
              @if($registration->has_vehicle)
                {{ $registration->motorcycle_count > 0 ? $registration->motorcycle_count . ' Motor' : '' }}{{ $registration->car_count > 0 ? ($registration->motorcycle_count > 0 ? ', ' : '') . $registration->car_count . ' Mobil' : '' }}
              @else
                Tanpa Kendaraan Pribadi
              @endif
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <!-- Manifest Rombongan Pendaki -->
  <div class="section-heading">Manifest Resmi Pendaki (Daftar Peserta Rombongan)</div>
  <table class="manifest-table">
    <thead>
      <tr>
        <th style="width: 5%; text-align: center;">No</th>
        <th style="width: 31%;">Nama Lengkap Peserta</th>
        <th style="width: 23%;">NIK / No. Identitas</th>
        <th style="width: 7%; text-align: center;">L/P</th>
        <th style="width: 17%; text-align: center;">Peran</th>
        <th style="width: 17%;">Kondisi Medis</th>
      </tr>
    </thead>
    <tbody>
      @forelse($members as $index => $member)
        <tr>
          <td style="text-align: center; font-weight: bold;">{{ $index + 1 }}</td>
          <td style="font-weight: bold;">
            {{ $member->full_name }}
            @if($member->is_leader)
              <span style="color: #047857; font-size: 7.5px;">(KETUA)</span>
            @endif
          </td>
          <td style="font-family: 'Courier New', Courier, monospace;">{{ $member->nik ?: '-' }}</td>
          <td style="text-align: center;">{{ $member->gender }}</td>
          <td style="text-align: center; font-size: 8.2px; font-weight: bold; color: {{ $member->is_leader ? '#065f46' : '#4b5563' }};">
            {{ $member->is_leader ? 'Ketua Rombongan' : 'Anggota Pendaki' }}
          </td>
          <td style="color: {{ ($member->medical_conditions && $member->medical_conditions !== 'Tidak Ada') ? '#b91c1c' : '#4b5563' }};">
            {{ $member->medical_conditions ?: 'Tidak Ada' }}
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" style="text-align: center; color: #6b7280;">Tidak ada anggota terdaftar.</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  <!-- Bukti Fisik Pemeriksaan Setiap Pos Ranger -->
  <div class="section-heading">Bukti Pemeriksaan Pos Ranger Jalur SAMSARA (Verifikasi Lapangan)</div>
  <table class="pos-table">
    <thead>
      <tr>
        <th style="width: 33.33%;">POS 1 (Tanjakan EOD)</th>
        <th style="width: 33.34%;">POS 2 (Batu Gendrung - Hutan Pinus)</th>
        <th style="width: 33.33%;">POS 3 (Thun 30)</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>
          <div><span class="chk"></span>Lewat Naik (Jam: _____)</div>
          <div><span class="chk"></span>Lewat Turun (Jam: _____)</div>
          <div class="pos-sign">Paraf Ranger: _______________</div>
        </td>
        <td>
          <div><span class="chk"></span>Lewat Transit (Jam: _____)</div>
          <div><span class="chk"></span>Buka Tenda Camp (Jam: _____)</div>
          <div class="pos-sign">Paraf Ranger: _______________</div>
        </td>
        <td>
          <div><span class="chk"></span>Menuju Puncak (Jam: _____)</div>
          <div><span class="chk"></span>Buka Tenda Camp (Jam: _____)</div>
          <div class="pos-sign">Paraf Ranger: _______________</div>
        </td>
      </tr>
    </tbody>
  </table>

  <!-- Protokol Keselamatan & Ringkasan Tarif -->
  <table class="bottom-grid">
    <tr>
      <td class="sop-panel" style="width: 58%;">
        <div class="sop-title">Protokol Keselamatan &amp; Kode Etik Pendakian SAMSARA</div>
        <ul class="sop-list">
          <li><strong>Zero Waste:</strong> Seluruh logistik pembungkus wajib dilaporkan di gerbang dan dibawa turun utuh.</li>
          <li><strong>Registrasi Ulang:</strong> Tunjukkan e-tiket ini kepada petugas Pos 1 / Gerbang Basecamp untuk validasi barcode.</li>
          @if(!empty($profile['sar_radio_enabled']) && !empty($profile['sar_frequency']))
            <li><strong>Radio Darurat SAR:</strong> Frekuensi darurat resmi: <strong>{{ $profile['sar_frequency'] }}</strong>.</li>
          @else
            <li><strong>Hotline Darurat SAR:</strong> Hubungi nomor siaga resmi basecamp di <strong>{{ $profile['emergency_hotline'] ?? '' }}</strong>.</li>
          @endif
          <li><strong>Dilarang Keras:</strong> Membuat api unggun terbuka di area vegetasi kering, memetik edelweiss, dan membuang sampah sembarangan.</li>
        </ul>
      </td>

      <td style="width: 2%;"></td>

      <td class="card-panel" style="width: 40%;">
        <div class="card-heading">Rekapitulasi Tarif &amp; Administrasi</div>
        <table class="finance-table">
          <tr>
            <td style="width: 60%; color: #4b5563;">Tiket SIMAKSI ({{ $registration->total_climbers }} org)</td>
            <td style="width: 40%; text-align: right; font-weight: bold;">Rp {{ number_format($registration->simaksi_fee, 0, ',', '.') }}</td>
          </tr>
          @if($registration->ojek_fee > 0)
            <tr>
              <td style="color: #4b5563;">Shuttle Ojek ({{ $registration->ojek_count }} voucher)</td>
              <td style="text-align: right;">Rp {{ number_format($registration->ojek_fee, 0, ',', '.') }}</td>
            </tr>
          @endif
          @if($registration->parking_fee > 0)
            <tr>
              <td style="color: #4b5563;">Retribusi Parkir</td>
              <td style="text-align: right;">Rp {{ number_format($registration->parking_fee, 0, ',', '.') }}</td>
            </tr>
          @endif
          @if($registration->unique_code > 0)
            <tr>
              <td style="color: #4b5563;">Kode Unik Verifikasi</td>
              <td style="text-align: right;">Rp {{ number_format($registration->unique_code, 0, ',', '.') }}</td>
            </tr>
          @endif
          <tr class="finance-total">
            <td>TOTAL BIAYA</td>
            <td style="text-align: right;">Rp {{ number_format($registration->total_amount, 0, ',', '.') }}</td>
          </tr>
          <tr>
            <td style="font-size: 8px; color: #6b7280;">Metode Pembayaran</td>
            <td style="text-align: right; font-weight: bold; text-transform: uppercase;">{{ $registration->payment_method }}</td>
          </tr>
          @if($registration->trash_deposit_amount > 0)
            <tr class="finance-deposit">
              <td>Jaminan Sampah</td>
              <td style="text-align: right; font-weight: bold;">Rp {{ number_format($registration->trash_deposit_amount, 0, ',', '.') }} ({{ strtoupper($registration->trash_deposit_status) }})</td>
            </tr>
          @endif
        </table>
      </td>
    </tr>
  </table>

  <!-- Footer Otentikasi & Verifikasi QR -->
  <table class="footer-table">
    <tr>
      <td style="width: 74%; padding-right: 10px;">
        <div style="word-break: break-all;"><strong>Kode Validasi Sistem:</strong> {{ hash('sha256', $registration->registration_code . '_' . $registration->created_at) }}</div>
        <div style="margin-top: 2px;">Dokumen elektronik ini diterbitkan secara otomatis oleh Sistem Informasi &amp; Manajemen Pendakian Terpadu SAMSARA. Sah tanpa stempel basah.</div>
        <div style="margin-top: 4px;">Waktu Cetak: {{ $printedAt }} | Petugas Verifikator: {{ $registration->reviewer?->name ?: 'Sistem Otomatis Basecamp' }}</div>
      </td>
      <td style="width: 26%; text-align: right;">
        <div style="font-weight: bold; font-size: 8.5px; margin-bottom: 3px; color: #111827;">SCAN VALIDASI GERBANG</div>
        <div style="margin-bottom: 4px;">
          <img src="{{ \App\Support\QrCode::pngDataUri($registration->registration_code, 6, 4) }}" width="86" height="86" alt="QR Code" style="display: inline-block; background: #ffffff; padding: 2px; border: 1px solid #e5e7eb; image-rendering: pixelated;">
        </div>
        <div class="qr-code-tag">[ {{ $registration->registration_code }} ]</div>
      </td>
    </tr>
  </table>

</body>
</html>
