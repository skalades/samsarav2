@extends('management.layout')

@section('title', 'Pengaturan Tarif, Sistem & Kebijakan')
@section('page_title', 'Pengaturan Tarif & Kebijakan Basecamp')
@section('page_subtitle', 'Kontrol Dinamis: Tarif, Kuota Harian, Logistik, Kontak SAR, dan Rekening Pembayaran')

@section('content')
<div class="space-y-6 font-mono-tech text-xs">

  <!-- Alert Notifikasi Sukses -->
  @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-between text-emerald-400">
      <div class="flex items-center gap-3">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span class="font-bold">{{ session('success') }}</span>
      </div>
      <span class="text-[10px] uppercase tracking-wider opacity-75">Tersinkronisasi Real-Time ke Portal Publik & POS</span>
    </div>
  @endif

  @if(isset($errors) && $errors->any())
    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 space-y-1">
      <div class="font-bold flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>Gagal menyimpan perubahan pengaturan:</span>
      </div>
      <ul class="list-disc list-inside text-[11px] pl-2 space-y-0.5">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <!-- Form Pengaturan Lengkap -->
  <form action="{{ route('management.settings.tariffs.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf

    <!-- ======================================================== -->
    <!-- SECTION 0: IDENTITAS EKSPEDISI, GUNUNG & BASECAMP        -->
    <!-- ======================================================== -->
    <div class="bg-surface border border-line rounded-xl p-5 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-line">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-sm bg-brand"></span>
          <h3 class="font-bold text-xs uppercase tracking-wider text-white">
            1. IDENTITAS EKSPEDISI, GUNUNG & BASECAMP
          </h3>
        </div>
        <span class="text-[10px] text-stone-400">DINAMIS DI SEMUA HEADER, PORTAL, STRUK & VOUCHER CETAK</span>
      </div>

      <!-- Upload Logo Resmi Basecamp & Struk Thermal -->
      <div class="p-4 rounded-xl bg-raised border border-line-strong space-y-3">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
          <div>
            <label class="text-[11px] font-bold text-white uppercase flex items-center gap-2">
              <span>Logo Resmi Basecamp & Struk Thermal</span>
              @if(!empty($profile['app_logo']))
                <span class="text-[9px] px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">[✓] LOGO AKTIF</span>
              @else
                <span class="text-[9px] px-2 py-0.5 rounded bg-stone-700 text-stone-300">MONOGRAM DEFAULT (▲)</span>
              @endif
            </label>
            <p class="text-[10px] text-stone-400 mt-1">
              Logo ini otomatis dicetak di atas <strong>Struk Kasir POS Thermal</strong> (58mm/80mm), Kupon Ojek, Karcis Parkir, Slip Honor, serta tampil di Sidebar Manajemen & Portal Registrasi Pendaki.
            </p>
          </div>
          <span class="text-[9px] text-brand bg-brand/10 border border-brand/30 px-2 py-1 rounded font-mono-tech whitespace-nowrap">
            PNG / JPG / WEBP / SVG (MAX 2MB)
          </span>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-4 pt-2 border-t border-line">
          <!-- Preview Box -->
          <div class="flex items-center gap-3">
            <div id="logoPreviewContainer" class="w-16 h-16 rounded-lg bg-canvas border-2 border-dashed border-line-strong flex items-center justify-center p-1 overflow-hidden shrink-0">
              @if(!empty($profile['app_logo']))
                <img id="logoPreviewImg" src="{{ asset('storage/' . $profile['app_logo']) }}" alt="Logo Basecamp" class="w-full h-full object-contain">
              @else
                <div id="logoPlaceholder" class="font-mono-tech text-xl font-black text-brand">▲</div>
                <img id="logoPreviewImg" src="" alt="Logo Preview" class="w-full h-full object-contain hidden">
              @endif
            </div>
            <div class="text-[10px] text-stone-400 space-y-0.5">
              <div class="text-stone-300 font-bold">Rekomendasi Cetak Thermal:</div>
              <div>• Format PNG transparan atau siluet hitam-putih</div>
              <div>• Kontras tajam agar teks thermal printer tidak buram</div>
            </div>
          </div>

          <!-- File Input & Action -->
          <div class="flex-1 w-full space-y-2">
            <input type="file" id="app_logo" name="app_logo" accept="image/png,image/jpeg,image/webp,image/svg+xml" onchange="previewLogo(this)"
                   class="block w-full text-xs text-stone-400 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-brand file:text-black hover:file:bg-brand-hover cursor-pointer bg-surface border border-line-strong rounded-lg p-1">
            
            @if(!empty($profile['app_logo']))
              <label class="inline-flex items-center gap-2 text-rose-400 text-[10px] cursor-pointer hover:text-rose-300">
                <input type="checkbox" name="remove_logo" value="1" class="rounded accent-rose-500">
                <span>Hapus logo kustom & kembalikan ke monogram segitiga default (▲)</span>
              </label>
            @endif
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Nama Ekspedisi -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <label for="expedition_name" class="text-[10px] font-bold text-stone-300 uppercase block">
            Nama Ekspedisi Resmi
          </label>
          <input type="text" id="expedition_name" name="expedition_name"
                 value="{{ old('expedition_name', $profile['expedition_name'] ?? 'SAMSARA EXPEDITION') }}" required
                 class="w-full bg-surface border border-line-strong rounded-lg px-3 py-2 text-white font-mono-tech text-xs focus:outline-none focus:border-brand">
          <p class="text-[9px] text-stone-400">Nama brand institusi pengelola pendakian.</p>
        </div>

        <!-- Nama Basecamp -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <label for="basecamp_name" class="text-[10px] font-bold text-stone-300 uppercase block">
            Nama Basecamp
          </label>
          <input type="text" id="basecamp_name" name="basecamp_name"
                 value="{{ old('basecamp_name', $profile['basecamp_name'] ?? 'Basecamp SAMSARA') }}" required
                 class="w-full bg-surface border border-line-strong rounded-lg px-3 py-2 text-white font-mono-tech text-xs focus:outline-none focus:border-brand">
          <p class="text-[9px] text-stone-400">Nama resmi posko / sekretariat registrasi.</p>
        </div>

        <!-- Nama Gunung -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <label for="mountain_name" class="text-[10px] font-bold text-stone-300 uppercase block">
            Nama Gunung
          </label>
          <input type="text" id="mountain_name" name="mountain_name"
                 value="{{ old('mountain_name', $profile['mountain_name'] ?? 'Gunung Cikuray') }}" required
                 class="w-full bg-surface border border-line-strong rounded-lg px-3 py-2 text-white font-mono-tech text-xs focus:outline-none focus:border-brand">
          <p class="text-[9px] text-stone-400">Nama kawasan gunung yang dinaungi izin SIMAKSI.</p>
        </div>

        <!-- Elevasi Puncak (Teks) -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <label for="mountain_elevation" class="text-[10px] font-bold text-stone-300 uppercase block">
            Elevasi Puncak (Tampilan)
          </label>
          <input type="text" id="mountain_elevation" name="mountain_elevation"
                 value="{{ old('mountain_elevation', $profile['mountain_elevation'] ?? '2.821 MDPL') }}" required
                 class="w-full bg-surface border border-line-strong rounded-lg px-3 py-2 text-white font-mono-tech text-xs focus:outline-none focus:border-brand">
          <p class="text-[9px] text-stone-400">Format elevasi lengkap dengan satuan MDPL.</p>
        </div>

        <!-- Elevasi Puncak (Angka) -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <label for="mountain_elevation_num" class="text-[10px] font-bold text-stone-300 uppercase block">
            Elevasi Puncak (Angka Meter)
          </label>
          <input type="number" id="mountain_elevation_num" name="mountain_elevation_num"
                 value="{{ old('mountain_elevation_num', $profile['mountain_elevation_num'] ?? 2821) }}" required
                 class="w-full bg-surface border border-line-strong rounded-lg px-3 py-2 text-white font-mono-tech text-xs focus:outline-none focus:border-brand">
          <p class="text-[9px] text-stone-400">Angka meter integer untuk visualisasi waypoint tracker.</p>
        </div>

        <!-- Elevasi Basecamp -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <label for="basecamp_elevation" class="text-[10px] font-bold text-stone-300 uppercase block">
            Elevasi Basecamp / Titik Mulai
          </label>
          <input type="text" id="basecamp_elevation" name="basecamp_elevation"
                 value="{{ old('basecamp_elevation', $profile['basecamp_elevation'] ?? '1.245 MDPL') }}" required
                 class="w-full bg-surface border border-line-strong rounded-lg px-3 py-2 text-white font-mono-tech text-xs focus:outline-none focus:border-brand">
          <p class="text-[9px] text-stone-400">Ketinggian pos awal jalur pendakian.</p>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- SECTION 2: TARIF PENDAKIAN & LOGISTIK UTAMA             -->
    <!-- ======================================================== -->
    <div class="bg-surface border border-line rounded-xl p-5 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-line">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-sm bg-brand"></span>
          <h3 class="font-bold text-xs uppercase tracking-wider text-white">
            2. TARIF PENDAKIAN & LOGISTIK UTAMA BASECAMP
          </h3>
        </div>
        <span class="text-[10px] text-stone-400">OTOMATIS TERSINKRON KE FORM REGISTRASI, KALKULATOR & POS</span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- SIMAKSI -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <div class="flex items-center justify-between">
            <label for="simaksi_fee" class="text-[10px] font-bold text-stone-300 uppercase">
              Permit SIMAKSI / Orang (Rp)
            </label>
            <span class="text-[9px] px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">WAJIB</span>
          </div>
          <div class="relative">
            <span class="absolute left-3 top-2.5 text-stone-500 font-bold">Rp</span>
            <input type="text" inputmode="numeric" data-currency-input id="simaksi_fee" name="simaksi_fee_per_person" value="{{ format_nominal(old('simaksi_fee_per_person', $tariffs['simaksi_fee_per_person'])) }}" required class="w-full bg-raised border border-line-strong rounded-lg pl-10 pr-3 py-2 text-white font-bold text-sm focus:outline-none focus:border-brand">
          </div>
          <p class="text-[9px] text-stone-400">Tarif izin resmi masuk kawasan konservasi {{ \App\Models\AppSetting::get('mountain_name', 'Gunung Cikuray') }} per pendaki.</p>
        </div>

        <!-- Shuttle Ojek Trail -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <div class="flex items-center justify-between">
            <label for="ojek_fee" class="text-[10px] font-bold text-stone-300 uppercase">
              Shuttle Ojek Trail / Trip (Rp)
            </label>
            <span class="text-[9px] px-1.5 py-0.5 rounded bg-brand/10 text-brand border border-brand/30">SHUTTLE</span>
          </div>
          <div class="relative">
            <span class="absolute left-3 top-2.5 text-stone-500 font-bold">Rp</span>
            <input type="text" inputmode="numeric" data-currency-input id="ojek_fee" name="ojek_fee_per_trip" value="{{ format_nominal(old('ojek_fee_per_trip', $tariffs['ojek_fee_per_trip'])) }}" required class="w-full bg-raised border border-line-strong rounded-lg pl-10 pr-3 py-2 text-white font-bold text-sm focus:outline-none focus:border-brand">
          </div>
          <p class="text-[9px] text-stone-400">Tarif shuttle armada ojek warga perkebunan rute Basecamp → Pos 1 per armada.</p>
        </div>

        <!-- Alokasi Kas Jalur Ojek -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <div class="flex items-center justify-between">
            <label for="ojek_basecamp_share" class="text-[10px] font-bold text-stone-300 uppercase">
              Kas Jalur Ojek / Trip (Rp)
            </label>
            <span class="text-[9px] px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/30">BAGI HASIL</span>
          </div>
          <div class="relative">
            <span class="absolute left-3 top-2.5 text-stone-500 font-bold">Rp</span>
            <input type="text" inputmode="numeric" data-currency-input id="ojek_basecamp_share" name="ojek_basecamp_share" value="{{ format_nominal(old('ojek_basecamp_share', $tariffs['ojek_basecamp_share'] ?? 5000)) }}" required class="w-full bg-raised border border-line-strong rounded-lg pl-10 pr-3 py-2 text-white font-bold text-sm focus:outline-none focus:border-brand">
          </div>
          <p class="text-[9px] text-stone-400">Bagian kas pengelola jalur basecamp. Sisanya otomatis menjadi hak driver ojek.</p>
        </div>

        <!-- Nama Trayek Ojek -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <div class="flex items-center justify-between">
            <label for="ojek_route_name" class="text-[10px] font-bold text-stone-300 uppercase">
              Nama Trayek Shuttle Ojek
            </label>
            <span class="text-[9px] px-1.5 py-0.5 rounded bg-stone-700 text-stone-300">RUTE</span>
          </div>
          <input type="text" id="ojek_route_name" name="ojek_route_name" value="{{ old('ojek_route_name', $tariffs['ojek_route_name'] ?? 'Basecamp SAMSARA - Pos 1 (Perkebunan Warga)') }}" required class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-white font-bold text-xs focus:outline-none focus:border-brand">
          <p class="text-[9px] text-stone-400">Deskripsi rute yang tercetak pada voucher ojek resmi.</p>
        </div>

        <!-- Porter Lokal -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <div class="flex items-center justify-between">
            <label for="porter_fee" class="text-[10px] font-bold text-stone-300 uppercase">
              Jasa Porter Tim Lokal (Rp)
            </label>
            <span class="text-[9px] px-1.5 py-0.5 rounded bg-purple-500/10 text-purple-400 border border-purple-500/30">PORTER</span>
          </div>
          <div class="relative">
            <span class="absolute left-3 top-2.5 text-stone-500 font-bold">Rp</span>
            <input type="text" inputmode="numeric" data-currency-input id="porter_fee" name="porter_fee_per_trip" value="{{ format_nominal(old('porter_fee_per_trip', $tariffs['porter_fee_per_trip'] ?? 350000)) }}" required class="w-full bg-raised border border-line-strong rounded-lg pl-10 pr-3 py-2 text-white font-bold text-sm focus:outline-none focus:border-brand">
          </div>
          <p class="text-[9px] text-stone-400">Estimasi tarif porter lokal (2H1M beban maks 20 KG hingga Pos 4 Camp).</p>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- SECTION 3: TARIF PARKIR KENDARAAN BASECAMP              -->
    <!-- ======================================================== -->
    <div class="bg-surface border border-line rounded-xl p-5 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-line">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-sm bg-cyan-400"></span>
          <h3 class="font-bold text-xs uppercase tracking-wider text-white">
            3. TARIF PARKIR KENDARAAN BASECAMP (PENITIPAN 24 JAM)
          </h3>
        </div>
        <span class="text-[10px] text-stone-400">TERKAIT DENGAN GATE PASS TIKET PARKIR KELUAR</span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Parkir Motor -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <div class="flex items-center justify-between">
            <label for="parking_motor" class="text-[10px] font-bold text-stone-300 uppercase">
              Tarif Parkir Sepeda Motor / Unit (Rp)
            </label>
            <span class="text-[9px] px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/30">RODA 2</span>
          </div>
          <div class="relative">
            <span class="absolute left-3 top-2.5 text-stone-500 font-bold">Rp</span>
            <input type="text" inputmode="numeric" data-currency-input id="parking_motor" name="parking_motor_fee" value="{{ format_nominal(old('parking_motor_fee', $tariffs['parking_motor_fee'])) }}" required class="w-full bg-raised border border-line-strong rounded-lg pl-10 pr-3 py-2 text-white font-bold text-sm focus:outline-none focus:border-brand">
          </div>
          <p class="text-[9px] text-stone-400">Dikenakan saat pendaki membawa sepeda motor ke basecamp selama pendakian.</p>
        </div>

        <!-- Parkir Mobil -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <div class="flex items-center justify-between">
            <label for="parking_car" class="text-[10px] font-bold text-stone-300 uppercase">
              Tarif Parkir Mobil Pribadi / Unit (Rp)
            </label>
            <span class="text-[9px] px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-400 border border-blue-500/30">RODA 4</span>
          </div>
          <div class="relative">
            <span class="absolute left-3 top-2.5 text-stone-500 font-bold">Rp</span>
            <input type="text" inputmode="numeric" data-currency-input id="parking_car" name="parking_car_fee" value="{{ format_nominal(old('parking_car_fee', $tariffs['parking_car_fee'])) }}" required class="w-full bg-raised border border-line-strong rounded-lg pl-10 pr-3 py-2 text-white font-bold text-sm focus:outline-none focus:border-brand">
          </div>
          <p class="text-[9px] text-stone-400">Dikenakan saat pendaki membawa mobil / minibus ke pelataran parkir basecamp.</p>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- SECTION 4: KEBIJAKAN JAMINAN DEPOSIT SAMPAH             -->
    <!-- ======================================================== -->
    <div class="bg-surface border border-line rounded-xl p-5 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-line">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-sm bg-emerald-400"></span>
          <h3 class="font-bold text-xs uppercase tracking-wider text-white">
            4. KEBIJAKAN DEPOSIT SAMPAH (ENABLE / DISABLE)
          </h3>
        </div>
        <span class="text-[10px] text-stone-400">JAMINAN BEBAS SAMPAH JALUR CONSERVATION</span>
      </div>

      <div class="p-4 rounded-xl bg-raised border border-line-strong space-y-4">
        <!-- Toggle Switch Enable / Disable -->
        <div class="flex items-center justify-between pb-3 border-b border-line-strong">
          <div class="space-y-0.5">
            <span class="text-xs font-bold text-white block">Status Fitur Deposit Sampah:</span>
            <p class="text-[10px] text-stone-400 max-w-xl">
              Jika <strong>Aktif (Enabled)</strong>, sistem akan mewajibkan deposit sampah yang dikembalikan saat pendaki turun membawa kantong sampah. Jika <strong>Nonaktif (Disabled)</strong>, pendaki tidak ditagih deposit sampah di portal dan gerbang.
            </p>
          </div>

          <label class="relative inline-flex items-center cursor-pointer">
            <input type="hidden" name="trash_deposit_enabled" value="0">
            <input type="checkbox" name="trash_deposit_enabled" id="trashToggle" value="1" {{ old('trash_deposit_enabled', $tariffs['trash_deposit_enabled']) ? 'checked' : '' }} onchange="onTrashToggleChange(this.checked)" class="sr-only peer">
            <div class="w-14 h-7 bg-hover peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-500"></div>
          </label>
        </div>

        <!-- Nominal Deposit Sampah -->
        <div id="trashNominalSection" class="transition-opacity duration-200 {{ old('trash_deposit_enabled', $tariffs['trash_deposit_enabled']) ? '' : 'opacity-40 pointer-events-none' }}">
          <div class="max-w-md space-y-2">
            <label for="trash_amount" class="text-[10px] font-bold text-stone-300 uppercase block">
              Besaran Uang Jaminan / Deposit Sampah (Rp)
            </label>
            <div class="relative">
              <span class="absolute left-3 top-2.5 text-stone-500 font-bold">Rp</span>
              <input type="text" inputmode="numeric" data-currency-input id="trash_amount" name="trash_deposit_amount" value="{{ format_nominal(old('trash_deposit_amount', $tariffs['trash_deposit_amount'])) }}" class="w-full bg-raised border border-line-strong rounded-lg pl-10 pr-3 py-2 text-white font-bold text-sm focus:outline-none focus:border-emerald-500">
            </div>
            <p class="text-[9px] text-stone-400">
              *Uang jaminan ini bersifat <em>refundable</em> (dikembalikan 100% tunai di Pos 00 Basecamp saat rombongan menyerahkan kantong sampah resmi).
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- SECTION 5: KAPASITAS & KUOTA HARIAN JALUR               -->
    <!-- ======================================================== -->
    <div class="bg-surface border border-line rounded-xl p-5 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-line">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-sm bg-amber-500"></span>
          <h3 class="font-bold text-xs uppercase tracking-wider text-white">
            5. KAPASITAS & KUOTA HARIAN JALUR (DAYA DUKUNG)
          </h3>
        </div>
        <span class="text-[10px] text-stone-400">MENCEGAH OVERBOOKING & MERAWAT DAYA DUKUNG HUTAN</span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Default Daily Quota -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <label for="default_daily_quota" class="text-[10px] font-bold text-stone-300 uppercase block">
            Kuota Maksimal Default (Orang / Hari)
          </label>
          <input type="number" id="default_daily_quota" name="default_daily_quota" value="{{ old('default_daily_quota', $profile['default_daily_quota'] ?? 150) }}" min="10" max="1000" required class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-white font-bold text-sm focus:outline-none focus:border-amber-500">
          <p class="text-[9px] text-stone-400">Kapasitas standar harian yang diterapkan saat hari baru diinisialisasi di sistem kalender.</p>
          <label class="flex items-center gap-2 pt-1 cursor-pointer">
            <input type="checkbox" name="sync_upcoming_quotas" value="1" checked class="w-4 h-4 rounded bg-surface border-line-strong text-brand focus:ring-brand">
            <span class="text-[10px] text-stone-300 font-medium">Sinkronkan ke seluruh tanggal kalender mendatang</span>
          </label>
        </div>

        <!-- Min Climbers per Group -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <label for="min_climbers" class="text-[10px] font-bold text-stone-300 uppercase block">
            Batas Minimal Pendaki / Rombongan
          </label>
          <input type="number" id="min_climbers" name="min_climbers_per_group" value="{{ old('min_climbers_per_group', $profile['min_climbers_per_group'] ?? 1) }}" min="1" max="10" required class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-white font-bold text-sm focus:outline-none focus:border-amber-500">
          <p class="text-[9px] text-stone-400">Batas bawah jumlah pendaki per formulir registrasi (1 untuk izin solo trekking).</p>
        </div>

        <!-- Max Climbers per Group -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <label for="max_climbers" class="text-[10px] font-bold text-stone-300 uppercase block">
            Batas Maksimal Pendaki / Rombongan
          </label>
          <input type="number" id="max_climbers" name="max_climbers_per_group" value="{{ old('max_climbers_per_group', $profile['max_climbers_per_group'] ?? 15) }}" min="1" max="50" required class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-white font-bold text-sm focus:outline-none focus:border-amber-500">
          <p class="text-[9px] text-stone-400">Batas maksimal anggota rombongan per kode registrasi SIMAKSI mandiri.</p>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- SECTION 6: KONTAK OPERASIONAL & RADIO SAR BASECAMP      -->
    <!-- ======================================================== -->
    <div class="bg-surface border border-line rounded-xl p-5 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-line">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-sm bg-purple-400"></span>
          <h3 class="font-bold text-xs uppercase tracking-wider text-white">
            6. KONTAK OPERASIONAL, HOTLINE DARURAT, & RADIO SAR
          </h3>
        </div>
        <span class="text-[10px] text-stone-400">TAMPIL PADA PORTAL PUBLIK, STRUK POS, & NOTIFIKASI WA</span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- WhatsApp Admin (Internasional) -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <label for="admin_whatsapp" class="text-[10px] font-bold text-stone-300 uppercase block">
            No. WhatsApp Admin (Format 62...)
          </label>
          <input type="text" id="admin_whatsapp" name="admin_whatsapp" value="{{ old('admin_whatsapp', $profile['admin_whatsapp'] ?? '6282122334455') }}" required class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-white font-bold text-xs focus:outline-none focus:border-purple-400">
          <p class="text-[9px] text-stone-400">Target nomor saat pendaki mengklik tombol "Buka WhatsApp Admin".</p>
        </div>

        <!-- WhatsApp Display -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <label for="admin_whatsapp_display" class="text-[10px] font-bold text-stone-300 uppercase block">
            Tampilan No. WhatsApp UI
          </label>
          <input type="text" id="admin_whatsapp_display" name="admin_whatsapp_display" value="{{ old('admin_whatsapp_display', $profile['admin_whatsapp_display'] ?? '') }}" required class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-white font-bold text-xs focus:outline-none focus:border-purple-400">
          <p class="text-[9px] text-stone-400">Format teks yang tampil di footer portal dan struk kasir.</p>
        </div>

        <!-- Frekuensi Radio SAR -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <div class="flex items-center justify-between">
            <label for="sar_frequency" class="text-[10px] font-bold text-stone-300 uppercase block">
              Frekuensi Radio HT SAR Basecamp
            </label>
            <label class="flex items-center gap-1.5 cursor-pointer">
              <input type="checkbox" name="sar_radio_enabled" value="1" {{ !empty($profile['sar_radio_enabled']) ? 'checked' : '' }} class="w-3.5 h-3.5 rounded bg-surface border-line-strong text-purple-500 focus:ring-purple-400">
              <span class="text-[9px] font-bold {{ !empty($profile['sar_radio_enabled']) ? 'text-emerald-400' : 'text-stone-400' }}">
                {{ !empty($profile['sar_radio_enabled']) ? 'AKTIF' : 'NONAKTIF' }}
              </span>
            </label>
          </div>
          <input type="text" id="sar_frequency" name="sar_frequency" value="{{ old('sar_frequency', $profile['sar_frequency_raw'] ?? $profile['sar_frequency'] ?? '') }}" class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-white font-bold text-xs focus:outline-none focus:border-purple-400">
          <p class="text-[9px] text-stone-400">Centang untuk mempublikasikan frekuensi radio HT ke pendaki. Saat nonaktif, sistem mengutamakan Hotline SAR.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
        <!-- Hotline Darurat -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <label for="emergency_hotline" class="text-[10px] font-bold text-stone-300 uppercase block">
            Hotline Call Center Darurat / Medis
          </label>
          <input type="text" id="emergency_hotline" name="emergency_hotline" value="{{ old('emergency_hotline', $profile['emergency_hotline'] ?? '') }}" required class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-white font-bold text-xs focus:outline-none focus:border-purple-400">
          <p class="text-[9px] text-stone-400">Nomor panggilan cepat telepon seluler jika terjadi kondisi darurat.</p>
        </div>

        <!-- Lokasi Basecamp -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <label for="basecamp_location" class="text-[10px] font-bold text-stone-300 uppercase block">
            Alamat / Lokasi Resmi Basecamp
          </label>
          <input type="text" id="basecamp_location" name="basecamp_location" value="{{ old('basecamp_location', $profile['basecamp_location'] ?? 'Kecamatan Cilawu / Bayongbong, Garut, Jawa Barat') }}" required class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-white font-bold text-xs focus:outline-none focus:border-purple-400">
          <p class="text-[9px] text-stone-400">Lokasi titik kumpul awal yang dicantumkan pada petunjuk kedatangan.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
        <!-- Google Maps Link -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <div class="flex items-center justify-between">
            <label for="basecamp_maps_url" class="text-[10px] font-bold text-stone-300 uppercase block">
              Tautan Google Maps Basecamp
            </label>
            @if(!empty($profile['basecamp_maps_url']))
              <a href="{{ $profile['basecamp_maps_url'] }}" target="_blank" rel="noopener" class="text-[9px] text-purple-400 hover:underline flex items-center gap-1 font-mono-tech">
                Buka Peta ↗
              </a>
            @endif
          </div>
          <input type="url" id="basecamp_maps_url" name="basecamp_maps_url" value="{{ old('basecamp_maps_url', $profile['basecamp_maps_url'] ?? '') }}" placeholder="https://maps.app.goo.gl/..." class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-white font-bold text-xs focus:outline-none focus:border-purple-400">
          <p class="text-[9px] text-stone-400">URL tautan navigasi Google Maps resmi (termasuk shortlink maps.app.goo.gl).</p>
        </div>

        <!-- Koordinat GPS Basecamp -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <label for="basecamp_coordinates" class="text-[10px] font-bold text-stone-300 uppercase block">
            Titik Koordinat GPS (Latitude, Longitude)
          </label>
          <input type="text" id="basecamp_coordinates" name="basecamp_coordinates" value="{{ old('basecamp_coordinates', $profile['basecamp_coordinates'] ?? '-7.331855, 107.8096294') }}" placeholder="-7.331855, 107.8096294" class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-white font-mono-tech font-bold text-xs focus:outline-none focus:border-purple-400">
          <p class="text-[9px] text-stone-400">Koordinat satelit untuk navigasi GPS offline dan preview peta landing page.</p>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- SECTION 7: REKENING PERBANKAN RESMI & QRIS               -->
    <!-- ======================================================== -->
    <div class="bg-surface border border-line rounded-xl p-5 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-line">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-sm bg-blue-400"></span>
          <h3 class="font-bold text-xs uppercase tracking-wider text-white">
            7. REKENING PERBANKAN RESMI & QRIS EXPEDITION
          </h3>
        </div>
        <span class="text-[10px] text-stone-400">TAMPIL DI WIZARD REGISTRASI SIMAKSI & NOTIFIKASI PEMBAYARAN</span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Nama Bank -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <label for="bank_name" class="text-[10px] font-bold text-stone-300 uppercase block">
            Nama Bank
          </label>
          <input type="text" id="bank_name" name="payment_bank_name" value="{{ old('payment_bank_name', $profile['payment_bank_name'] ?? '') }}" required class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-white font-bold text-xs focus:outline-none focus:border-blue-400">
          <p class="text-[9px] text-stone-400">Contoh: BANK CENTRAL ASIA (BCA) atau BANK MANDIRI.</p>
        </div>

        <!-- Nomor Rekening -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <label for="bank_account" class="text-[10px] font-bold text-stone-300 uppercase block">
            Nomor Rekening Bank
          </label>
          <input type="text" id="bank_account" name="payment_bank_account" value="{{ old('payment_bank_account', $profile['payment_bank_account'] ?? '') }}" required class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-white font-bold text-xs focus:outline-none focus:border-blue-400">
          <p class="text-[9px] text-stone-400">Nomor tujuan transfer yang dapat disalin satu-klik oleh pendaki.</p>
        </div>

        <!-- Nama Pemilik Rekening -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <label for="bank_holder" class="text-[10px] font-bold text-stone-300 uppercase block">
            Nama Atas Nama Rekening (A.N.)
          </label>
          <input type="text" id="bank_holder" name="payment_bank_holder" value="{{ old('payment_bank_holder', $profile['payment_bank_holder'] ?? '') }}" required class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-white font-bold text-xs focus:outline-none focus:border-blue-400">
          <p class="text-[9px] text-stone-400">Nama penerima resmi yang dicocokkan saat mutasi bank.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
        <!-- NMID QRIS -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <label for="qris_nmid" class="text-[10px] font-bold text-stone-300 uppercase block">
            NMID QRIS Nasional
          </label>
          <input type="text" id="qris_nmid" name="payment_qris_nmid" value="{{ old('payment_qris_nmid', $profile['payment_qris_nmid'] ?? '') }}" required class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-white font-bold text-xs focus:outline-none focus:border-blue-400">
          <p class="text-[9px] text-stone-400">Nomor identifikasi merchant nasional QRIS resmi.</p>
        </div>

        <!-- Nama Merchant QRIS -->
        <div class="p-3.5 rounded-lg bg-raised border border-line-strong space-y-2">
          <label for="qris_name" class="text-[10px] font-bold text-stone-300 uppercase block">
            Nama Merchant QRIS
          </label>
          <input type="text" id="qris_name" name="payment_qris_name" value="{{ old('payment_qris_name', $profile['payment_qris_name'] ?? '') }}" required class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-white font-bold text-xs focus:outline-none focus:border-blue-400">
          <p class="text-[9px] text-stone-400">Nama badan usaha yang terdaftar pada standar QRIS.</p>
        </div>
      </div>

      <!-- Upload Gambar QRIS Statis Resmi -->
      <div class="p-4 rounded-xl bg-raised border border-line-strong space-y-3">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
          <div>
            <label class="text-[11px] font-bold text-white uppercase flex items-center gap-2">
              <span>Barcode / Gambar QRIS Statis Resmi Basecamp</span>
              @if(!empty($profile['payment_qris_image']))
                <span class="text-[9px] px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">[✓] QRIS STATIS AKTIF</span>
              @else
                <span class="text-[9px] px-2 py-0.5 rounded bg-stone-700 text-stone-300">QR GENERATOR OTOMATIS (MOCKUP)</span>
              @endif
            </label>
            <p class="text-[10px] text-stone-400 mt-1">
              Unggah file gambar QRIS resmi (BCA, Mandiri, BRI, BNI, GoPay, OVO, Dana, dll.) agar pendaki dapat langsung <strong>scan & bayar langsung</strong> dari aplikasi mobile banking / e-wallet di Wizard Registrasi SIMAKSI.
            </p>
          </div>
          <span class="text-[9px] text-blue-400 bg-blue-500/10 border border-blue-500/30 px-2 py-1 rounded font-mono-tech whitespace-nowrap">
            PNG / JPG / WEBP (MAX 2MB)
          </span>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-4 pt-2 border-t border-line">
          <!-- QRIS Preview Box -->
          <div class="flex items-center gap-3">
            <div id="qrisPreviewContainer" class="w-24 h-24 rounded-lg bg-white border-2 border-dashed border-line-strong flex items-center justify-center p-1 overflow-hidden shrink-0 shadow-md">
              @if(!empty($profile['payment_qris_image']))
                <img id="qrisPreviewImg" src="{{ asset('storage/' . $profile['payment_qris_image']) }}" alt="QRIS Basecamp" class="w-full h-full object-contain">
              @else
                <div id="qrisPlaceholder" class="text-center p-1 text-slate-800 text-[9px] font-bold">
                  [BELUM ADA QRIS ASLI]
                </div>
                <img id="qrisPreviewImg" src="" alt="QRIS Preview" class="w-full h-full object-contain hidden">
              @endif
            </div>
            <div class="text-[10px] text-stone-400 space-y-0.5">
              <div class="text-stone-300 font-bold">Panduan QRIS Statis:</div>
              <div>• Screenshot atau ekspor file QRIS dari m-banking / merchant aggregator</div>
              <div>• Pastikan kode QR tajam & tidak terpotong agar mudah dibaca kamera HP</div>
            </div>
          </div>

          <!-- File Input & Action -->
          <div class="flex-1 w-full space-y-2">
            <input type="file" id="payment_qris_image" name="payment_qris_image" accept="image/png,image/jpeg,image/webp" onchange="previewQris(this)"
                   class="block w-full text-xs text-stone-400 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer bg-surface border border-line-strong rounded-lg p-1">
            
            @if(!empty($profile['payment_qris_image']))
              <label class="inline-flex items-center gap-2 text-rose-400 text-[10px] cursor-pointer hover:text-rose-300">
                <input type="checkbox" name="remove_qris_image" value="1" class="rounded accent-rose-500">
                <span>Hapus gambar QRIS kustom & kembali ke generator otomatis</span>
              </label>
            @endif
          </div>
        </div>
      </div>
    </div>

    <!-- ======================================================== -->
    <!-- ACTIONS BAR (STICKY FOOTER)                              -->
    <!-- ======================================================== -->
    <div class="p-3.5 sm:p-4 rounded-2xl bg-canvas/95 backdrop-blur border border-line-strong flex flex-col sm:flex-row sm:items-center justify-between gap-3 sticky bottom-2 sm:bottom-4 shadow-2xl z-20">
      <div class="text-xs text-muted">
        Perubahan tarif &amp; konfigurasi sistem akan <strong class="text-ink">langsung aktif</strong> di seluruh Portal Publik, Kasir POS, dan Modul Gate.
      </div>
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 shrink-0">
        <button type="reset" class="h-10 px-4 rounded-xl bg-raised border border-line-strong text-muted hover:text-ink transition font-semibold text-xs">
          Reset
        </button>
        <button type="submit" class="tap-active h-11 px-5 rounded-xl bg-brand hover:bg-brand-hover text-white font-semibold text-xs sm:text-sm transition shadow-lg shadow-brand/20 flex items-center justify-center gap-2">
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
          <span>Simpan Perubahan</span>
        </button>
      </div>
    </div>

  </form>

</div>

<script>
  function previewLogo(input) {
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        const img = document.getElementById('logoPreviewImg');
        const placeholder = document.getElementById('logoPlaceholder');
        if (img) {
          img.src = e.target.result;
          img.classList.remove('hidden');
        }
        if (placeholder) {
          placeholder.classList.add('hidden');
        }
      };
      reader.readAsDataURL(input.files[0]);
    }
  }

  function previewQris(input) {
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        const img = document.getElementById('qrisPreviewImg');
        const placeholder = document.getElementById('qrisPlaceholder');
        if (img) {
          img.src = e.target.result;
          img.classList.remove('hidden');
        }
        if (placeholder) {
          placeholder.classList.add('hidden');
        }
      };
      reader.readAsDataURL(input.files[0]);
    }
  }

  function onTrashToggleChange(isChecked) {
    const section = document.getElementById('trashNominalSection');
    if (isChecked) {
      section.classList.remove('opacity-40', 'pointer-events-none');
    } else {
      section.classList.add('opacity-40', 'pointer-events-none');
    }
  }
</script>
@endsection
