<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>E-Tiket SIMAKSI - {{ $registration->registration_code }}</title>
    <style>
        @page {
            margin: 15mm 15mm 15mm 15mm;
            size: a4 portrait;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1f2937;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #064e3b;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }

        .brand-title {
            font-size: 18px;
            font-weight: bold;
            color: #064e3b;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin: 0;
        }

        .brand-subtitle {
            font-size: 10px;
            color: #4b5563;
            margin-top: 2px;
            font-weight: 500;
        }

        .doc-title-box {
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 14px;
            text-align: center;
        }

        .doc-title {
            font-size: 13px;
            font-weight: bold;
            color: #065f46;
            margin: 0;
            letter-spacing: 0.5px;
        }

        .doc-desc {
            font-size: 9.5px;
            color: #047857;
            margin-top: 2px;
        }

        .meta-table {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: collapse;
        }

        .meta-col {
            width: 50%;
            vertical-align: top;
        }

        .card-box {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background-color: #f9fafb;
            padding: 10px;
        }

        .card-title {
            font-size: 10.5px;
            font-weight: bold;
            color: #111827;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .data-row {
            margin-bottom: 4px;
        }

        .data-label {
            color: #6b7280;
            font-size: 9.5px;
            display: inline-block;
            width: 110px;
        }

        .data-val {
            font-weight: 600;
            color: #111827;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .ticket-code-badge {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            font-weight: bold;
            background: #111827;
            color: #34d399;
            padding: 4px 10px;
            border-radius: 4px;
            display: inline-block;
            letter-spacing: 1px;
        }

        table.manifest-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 14px;
        }

        table.manifest-table th {
            background-color: #064e3b;
            color: #ffffff;
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #064e3b;
        }

        table.manifest-table td {
            font-size: 9.5px;
            padding: 5px 8px;
            border: 1px solid #e5e7eb;
            color: #1f2937;
        }

        table.manifest-table tr:nth-child(even) td {
            background-color: #f9fafb;
        }

        .finance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 14px;
        }

        .finance-table td {
            padding: 4px 6px;
            font-size: 9.5px;
        }

        .sop-box {
            border: 1px dashed #9ca3af;
            border-radius: 6px;
            background-color: #fffbeb;
            padding: 8px 12px;
            margin-top: 10px;
            margin-bottom: 12px;
        }

        .sop-title {
            font-size: 9.5px;
            font-weight: bold;
            color: #92400e;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .sop-list {
            margin: 0;
            padding-left: 14px;
            color: #78350f;
            font-size: 8.5px;
            line-height: 1.35;
        }

        .footer-table {
            width: 100%;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            margin-top: 10px;
            font-size: 8.5px;
            color: #6b7280;
        }

        .qr-placeholder {
            width: 75px;
            height: 75px;
            border: 2px solid #111827;
            padding: 4px;
            text-align: center;
            background: #ffffff;
        }

        .qr-inner {
            background: #111827;
            color: #ffffff;
            font-family: monospace;
            font-size: 7px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
            box-sizing: border-box;
            line-height: 1.1;
        }
    </style>
</head>
<body>

    <!-- Header Instansi & Verifikasi -->
    <table class="header-table">
        <tr>
            <td style="width: 70%; vertical-align: middle;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        @if(!empty($profile['app_logo']) && file_exists(public_path('storage/' . $profile['app_logo'])))
                            <td style="width: 50px; vertical-align: middle; padding-right: 10px;">
                                <img src="{{ public_path('storage/' . $profile['app_logo']) }}" style="max-height: 44px; max-width: 50px;" alt="Logo">
                            </td>
                        @endif
                        <td style="vertical-align: middle;">
                            <div class="brand-title">{{ $profile['expedition_name'] ?? 'SAMSARA EXPEDITION' }}</div>
                            <div class="brand-subtitle">
                                Otoritas Pengelola Jalur Resmi {{ $profile['mountain_name'] ?? 'Gunung Cikuray' }} ({{ $profile['mountain_elevation'] ?? '2.821 MDPL' }})<br>
                                Lokasi: {{ $profile['basecamp_location'] ?? 'Garut, Jawa Barat' }} | Hotline: {{ $profile['emergency_hotline'] ?? '+62 821-2233-4455' }}
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 30%; text-align: right; vertical-align: middle;">
                <div class="ticket-code-badge">{{ $registration->registration_code }}</div>
                <div style="margin-top: 4px;">
                    <span class="badge" style="background-color: {{ $statusColor }};">
                        {{ $statusLabel }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <!-- Banner Judul Dokumen Resmi -->
    <div class="doc-title-box">
        <div class="doc-title">SURAT IZIN MASUK KAWASAN KONSERVASI (SIMAKSI) & E-TIKET RESMI</div>
        <div class="doc-desc">Dokumen elektronik sah untuk verifikasi administrasi di Pos Registrasi & Gate Check-in Basecamp SAMSARA</div>
    </div>

    <!-- Informasi Rombongan & Jadwal -->
    <table class="meta-table">
        <tr>
            <!-- Kolom Kiri: Data Ketua & Kontak Darurat -->
            <td class="meta-col" style="padding-right: 8px;">
                <div class="card-box">
                    <div class="card-title">Penanggung Jawab (Ketua Rombongan)</div>
                    <div class="data-row">
                        <span class="data-label">Nama Ketua:</span>
                        <span class="data-val">{{ $registration->leader_name }}</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">NIK / Identitas:</span>
                        <span class="data-val">{{ $registration->leader_nik }}</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">No. WhatsApp:</span>
                        <span class="data-val">{{ $registration->leader_phone }}</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Kota Asal / Alamat:</span>
                        <span class="data-val">{{ $registration->leader_city ?: $registration->leader_address ?: '-' }}</span>
                    </div>
                    <div class="data-row" style="margin-top: 6px; border-top: 1px dashed #d1d5db; padding-top: 4px;">
                        <span class="data-label">Kontak Darurat:</span>
                        <span class="data-val">{{ $registration->emergency_contact_name }} ({{ $registration->emergency_relationship ?: 'Keluarga' }})</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Telp. Darurat:</span>
                        <span class="data-val">{{ $registration->emergency_contact_phone }}</span>
                    </div>
                </div>
            </td>

            <!-- Kolom Kanan: Jadwal & Fasilitas Logistik -->
            <td class="meta-col" style="padding-left: 8px;">
                <div class="card-box">
                    <div class="card-title">Jadwal & Fasilitas Ekspedisi</div>
                    <div class="data-row">
                        <span class="data-label">Tanggal Naik:</span>
                        <span class="data-val" style="color: #065f46;">{{ \Carbon\Carbon::parse($registration->hiking_date)->translatedFormat('l, d F Y') }}</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Rencana Turun:</span>
                        <span class="data-val">{{ $registration->return_date ? \Carbon\Carbon::parse($registration->return_date)->translatedFormat('l, d F Y') : 'Hari yang sama (Tek-tok)' }}</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Jumlah Anggota:</span>
                        <span class="data-val">{{ $registration->total_climbers }} Orang Pendaki</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Shuttle Ojek:</span>
                        <span class="data-val">{{ $registration->ojek_count > 0 ? $registration->ojek_count . ' Trip Voucher Ojek' : 'Tidak pesan (Jalan Kaki)' }}</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Parkir Kendaraan:</span>
                        <span class="data-val">
                            @if($registration->has_vehicle)
                                {{ $registration->motorcycle_count > 0 ? $registration->motorcycle_count . ' Motor' : '' }}
                                {{ $registration->car_count > 0 ? ($registration->motorcycle_count > 0 ? ', ' : '') . $registration->car_count . ' Mobil' : '' }}
                            @else
                                Tanpa Kendaraan Pribadi
                            @endif
                        </span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Manifest Rombongan Pendaki -->
    <div style="font-weight: bold; font-size: 10.5px; color: #111827; text-transform: uppercase; margin-bottom: 4px;">
        Manifest Resmi Pendaki (Daftar Peserta Rombongan)
    </div>
    <table class="manifest-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 32%;">Nama Lengkap Peserta</th>
                <th style="width: 25%;">NIK / No. Identitas</th>
                <th style="width: 8%; text-align: center;">L/P</th>
                <th style="width: 15%; text-align: center;">Peran</th>
                <th style="width: 15%;">Kondisi Medis</th>
            </tr>
        </thead>
        <tbody>
            @forelse($members as $index => $member)
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $index + 1 }}</td>
                    <td style="font-weight: 600;">
                        {{ $member->full_name }}
                        @if($member->is_leader)
                            <span style="color: #047857; font-size: 8px;">(KETUA)</span>
                        @endif
                    </td>
                    <td style="font-family: monospace;">{{ $member->nik ?: '-' }}</td>
                    <td style="text-align: center;">{{ $member->gender }}</td>
                    <td style="text-align: center;">
                        <span style="font-size: 8.5px; font-weight: bold; color: {{ $member->is_leader ? '#065f46' : '#4b5563' }};">
                            {{ $member->is_leader ? 'Ketua Rombongan' : 'Anggota Pendaki' }}
                        </span>
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

    <!-- Ringkasan Keuangan & Biaya Administrasi -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
        <tr>
            <td style="width: 60%; vertical-align: top; padding-right: 10px;">
                <div class="sop-box">
                    <div class="sop-title">Protokol Keselamatan & Kode Etik Pendakian SAMSARA</div>
                    <ul class="sop-list">
                        <li><strong>Zero Waste:</strong> Seluruh logistik pembungkus wajib dilaporkan di gerbang dan dibawa turun utuh.</li>
                        <li><strong>Registrasi Ulang:</strong> Tunjukkan e-tiket ini kepada petugas Pos 1 / Gerbang Basecamp untuk validasi barcode.</li>
                        <li><strong>Radio Darurat SAR:</strong> Frekuensi darurat resmi: <strong>{{ $profile['sar_frequency'] ?? '149.250 MHz' }}</strong>.</li>
                        <li><strong>Dilarang Keras:</strong> Membuat api unggun terbuka di area vegetasi kering, memetik edelweiss, dan membuang sampah sembarangan.</li>
                    </ul>
                </div>
            </td>
            <td style="width: 40%; vertical-align: top;">
                <div class="card-box">
                    <div class="card-title">Rekapitulasi Tarif & Administrasi</div>
                    <table class="finance-table">
                        <tr>
                            <td>Tiket SIMAKSI ({{ $registration->total_climbers }} org)</td>
                            <td style="text-align: right; font-weight: 600;">Rp {{ number_format($registration->simaksi_fee, 0, ',', '.') }}</td>
                        </tr>
                        @if($registration->ojek_fee > 0)
                        <tr>
                            <td>Shuttle Ojek ({{ $registration->ojek_count }} voucher)</td>
                            <td style="text-align: right;">Rp {{ number_format($registration->ojek_fee, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($registration->parking_fee > 0)
                        <tr>
                            <td>Retribusi Parkir Kendaraan</td>
                            <td style="text-align: right;">Rp {{ number_format($registration->parking_fee, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($registration->unique_code > 0)
                        <tr>
                            <td>Kode Unik Verifikasi</td>
                            <td style="text-align: right;">Rp {{ number_format($registration->unique_code, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        <tr style="border-top: 1px solid #d1d5db; font-weight: bold; color: #065f46;">
                            <td style="padding-top: 6px;">TOTAL BIAYA:</td>
                            <td style="text-align: right; font-size: 11px; padding-top: 6px;">Rp {{ number_format($registration->total_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 8.5px; color: #6b7280;">Metode Pembayaran:</td>
                            <td style="text-align: right; font-weight: bold; text-transform: uppercase;">{{ $registration->payment_method }}</td>
                        </tr>
                        @if($registration->trash_deposit_amount > 0)
                        <tr style="color: #92400e; font-size: 8.5px; border-top: 1px dashed #d1d5db;">
                            <td>Jaminan Sampah (Deposit)</td>
                            <td style="text-align: right; font-weight: 600;">Rp {{ number_format($registration->trash_deposit_amount, 0, ',', '.') }} ({{ strtoupper($registration->trash_deposit_status) }})</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- Footer Otentikasi & Verifikasi QR -->
    <table class="footer-table">
        <tr>
            <td style="width: 75%; vertical-align: top;">
                <div><strong>Kode Validasi Sistem:</strong> {{ hash('sha256', $registration->registration_code . '_' . $registration->created_at) }}</div>
                <div>Dokumen elektronik ini diterbitkan secara otomatis oleh Sistem Informasi & Manajemen Pendakian Terpadu SAMSARA. Sah tanpa stempel basah.</div>
                <div style="margin-top: 3px;">Waktu Cetak: {{ $printedAt }} | Petugas Verifikator: {{ $registration->reviewer?->name ?: 'Sistem Otomatis Basecamp' }}</div>
            </td>
            <td style="width: 25%; text-align: right; vertical-align: top;">
                <div style="font-weight: bold; font-size: 9px; margin-bottom: 3px;">SCAN VALIDASI GERBANG</div>
                <div style="font-family: monospace; font-size: 11px; font-weight: bold; color: #064e3b; border: 1px solid #064e3b; padding: 4px; display: inline-block; background: #ecfdf5;">
                    [ {{ $registration->registration_code }} ]
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
