<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  @include('management.partials.head', [
    'withViewMode' => false,
    'lockViewport' => false,
    'appTitle' => $profile['expedition_name'] ?? 'SAMSARA EXPEDITION',
  ])
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $seo['title'] }}</title>
  <meta name="description" content="{{ $seo['description'] }}">
  @include('portal.partials.seo-head')
  <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}?v={{ filemtime(public_path('assets/css/main.css')) }}">
</head>
<body class="min-h-screen flex flex-col bg-canvas selection:bg-brand selection:text-white pb-20 md:pb-8">

  @include('portal.partials.header')

  <main class="flex-1 max-w-5xl mx-auto w-full px-3.5 sm:px-6 pt-4 md:pt-6 space-y-5">

    <!-- ======================================================== -->
    <!-- DRAFT RECOVERY BANNER (ANTI-TERCLOSE RESUME)             -->
    <!-- ======================================================== -->
    <div id="draftRecoveryBanner" class="hidden p-3.5 rounded-xl bg-surface border border-brand/50 shadow-xl space-y-2 font-mono-tech text-xs">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-brand"></span>
          </span>
          <span class="text-white font-bold text-[11px] tracking-wider">// DRAF PENDAFTARAN TERSIMPAN</span>
        </div>
        <button type="button" onclick="App.draft.discard()" class="text-stone-400 hover:text-rose-400 text-[10px] underline">
          HAPUS DRAF
        </button>
      </div>
      <p id="draftRecoveryInfo" class="text-[10px] text-stone-300 leading-relaxed">
        Ditemukan sesi pendaftaran sebelumnya yang belum selesai. Anda dapat melanjutkan pengisian atau pembayaran langsung.
      </p>
      <div class="flex items-center gap-2 pt-0.5">
        <button type="button" onclick="App.draft.resume()" class="flex-1 py-2 rounded-lg bg-brand hover:bg-brand-hover text-black font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-1.5 transition">
          <span>LANJUTKAN PROSES</span>
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </button>
        <button type="button" onclick="App.draft.dismiss()" class="px-3 py-2 rounded-lg bg-[#1F242E] text-stone-300 text-xs hover:bg-hover transition">
          NANTI
        </button>
      </div>
    </div>

    @include('portal.partials.home')

    @include('portal.partials.booking')
    @include('portal.partials.telemetry')
    @include('portal.partials.waypoints')
    @include('portal.partials.services')

    <!-- ======================================================== -->
    <!-- BASECAMP FOOTER INFO                                     -->
    <!-- ======================================================== -->
    <footer class="pt-8 pb-4 border-t border-line text-center space-y-1.5 text-xs text-muted">
      <div class="flex items-center justify-center gap-1.5 text-stone-300">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
        <span>POST SAR {{ strtoupper($profile['basecamp_name'] ?? 'BASECAMP SAMSARA') }} AKTIF 24 JAM</span>
      </div>
      <p class="text-stone-300">
        Titik Kumpul: {{ $profile['basecamp_location'] ?? 'Kecamatan Cilawu / Bayongbong, Garut' }}
        • <a href="{{ $profile['basecamp_maps_url'] ?? '' }}" target="_blank" rel="noopener noreferrer" class="text-brand hover:underline font-bold inline-flex items-center gap-0.5">Google Maps ↗</a>
      </p>
      @if(!empty($profile['sar_radio_enabled']) && !empty($profile['sar_frequency']))
        <p>Radio Darurat: {{ $profile['sar_frequency'] }} • WhatsApp Admin: {{ $profile['admin_whatsapp_display'] ?? '' }}</p>
      @else
        <p>Hotline Darurat SAR: {{ $profile['emergency_hotline'] ?? '' }} • WhatsApp Admin: {{ $profile['admin_whatsapp_display'] ?? '' }}</p>
      @endif
      <p class="text-stone-500">© {{ date('Y') }} {{ strtoupper($profile['expedition_name'] ?? 'SAMSARA EXPEDITION') }}. LINDUNGI KELESTARIAN HUTAN {{ strtoupper($profile['mountain_name'] ?? 'CIKURAY') }}.</p>
    </footer>

  </main>

  @include('portal.partials.bottom-nav')

  <!-- ======================================================== -->
  <!-- MODAL 1: MULTI-STEP REGISTRATION MODAL                   -->
  <!-- ======================================================== -->
  <div id="regModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-md [&:not(.hidden)]:flex items-end sm:items-center justify-center sm:p-4" onclick="if(event.target === this) App.modal.close('regModal')">
    <div class="bg-surface border border-line-strong rounded-t-3xl sm:rounded-2xl w-full sm:max-w-lg max-h-[92dvh] flex flex-col shadow-2xl overflow-hidden">
      
      <!-- Top Modal Header (Sticky) -->
      <div class="px-4 sm:px-5 pt-4 pb-3 border-b border-line bg-surface shrink-0">
        <div class="flex items-center justify-between gap-3 mb-3">
          <div class="flex items-center gap-2.5 min-w-0">
            <span class="w-2.5 h-2.5 rounded-sm bg-brand shrink-0"></span>
            <div class="min-w-0">
              <span class="text-[11px] text-brand font-semibold tracking-wider uppercase block leading-none">Sistem Mandiri SIMAKSI</span>
              <h3 class="font-display font-bold text-base sm:text-lg text-ink leading-tight truncate mt-0.5">Reservasi &amp; Permit Pendakian</h3>
            </div>
          </div>
          <button type="button" onclick="App.modal.close('regModal')" class="tap-active w-9 h-9 shrink-0 rounded-full bg-raised border border-line-strong text-muted hover:text-ink flex items-center justify-center font-bold text-sm" aria-label="Tutup">
            ✕
          </button>
        </div>

        <!-- Step Indicator & Progress Bar -->
        <div class="space-y-2">
          <div class="flex items-center justify-between text-xs tabular-nums">
            <span id="wizardStepLabel" class="text-ink font-semibold tracking-wide truncate">LANGKAH 1/4: JADWAL &amp; LOGISTIK</span>
            <span id="wizardStepPercent" class="text-brand font-bold shrink-0">25%</span>
          </div>
          
          <!-- Progress Bar Track -->
          <div class="w-full h-1.5 bg-hover rounded-full overflow-hidden">
            <div id="wizardProgressBar" class="h-full bg-brand transition-all duration-300 rounded-full" style="width: 25%;"></div>
          </div>

          <!-- Step Badges (Compact) -->
          <div class="grid grid-cols-4 gap-1.5 pt-0.5 text-[11px] text-center">
            <button type="button" id="stepPill1" onclick="App.wizard.goTo(1)" class="py-1.5 px-1 rounded-lg bg-brand/15 text-brand border border-brand/40 font-semibold transition truncate">1. Jadwal</button>
            <button type="button" id="stepPill2" onclick="App.wizard.goTo(2)" class="py-1.5 px-1 rounded-lg bg-raised text-muted border border-line-strong font-medium transition truncate">2. Ketua</button>
            <button type="button" id="stepPill3" onclick="App.wizard.goTo(3)" class="py-1.5 px-1 rounded-lg bg-raised text-muted border border-line-strong font-medium transition truncate">3. Anggota</button>
            <button type="button" id="stepPill4" onclick="App.wizard.goTo(4)" class="py-1.5 px-1 rounded-lg bg-raised text-muted border border-line-strong font-medium transition truncate">4. Bayar</button>
          </div>
        </div>
      </div>

      <!-- Scrollable Wizard Steps Body -->
      <div class="flex-1 overflow-y-auto overscroll-y-contain p-4 sm:p-5 space-y-4 safe-bottom">
        
        <!-- ============================================== -->
        <!-- STEP 1: JADWAL & LOGISTIK                      -->
        <!-- ============================================== -->
        <div id="wizardStep1" class="space-y-4">
          <div class="p-3 rounded-xl bg-raised/70 border border-line text-xs text-muted leading-relaxed">
            <span class="text-brand font-semibold block mb-0.5">Panduan Jadwal &amp; Kuota</span>
            Pilih tanggal mulai pendakian. Kuota harian dibatasi maks. {{ $profile['default_daily_quota'] ?? 150 }} orang per hari sesuai daya dukung konservasi.
          </div>

          <!-- Kategori Pendakian: Tektok vs Camp -->
          <div>
            <label class="label">Kategori Pendakian</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
              <label id="catTektokLabel" class="relative flex flex-col p-3 rounded-xl border border-line-strong bg-raised cursor-pointer hover:border-cyan-400/50 transition">
                <div class="flex items-center gap-2 font-bold text-xs sm:text-sm text-cyan-400">
                  <input type="radio" name="wzCategory" value="tektok" class="accent-cyan-400 w-4 h-4" onchange="App.wizard.setCategory('tektok')">
                  <span>TEKTOK (1 HARI)</span>
                </div>
                <span class="text-xs text-muted mt-1 leading-snug">Buka Gerbang: 03:00–07:00 WIB. Turun hari yang sama (tanpa tenda).</span>
              </label>

              <label id="catCampLabel" class="relative flex flex-col p-3 rounded-xl border border-emerald-500/50 bg-emerald-500/10 cursor-pointer hover:border-emerald-400 transition">
                <div class="flex items-center gap-2 font-bold text-xs sm:text-sm text-emerald-400">
                  <input type="radio" name="wzCategory" value="camp" checked class="accent-emerald-400 w-4 h-4" onchange="App.wizard.setCategory('camp')">
                  <span>CAMP (BERMALAM)</span>
                </div>
                <span class="text-xs text-muted mt-1 leading-snug">Buka Gerbang: Mulai 07:00 WIB. Camp di Pos 2 atau Pos 3 (disarankan Pos 3).</span>
              </label>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label for="wzHikingDate" class="label">Tanggal Naik</label>
              <input type="date" id="wzHikingDate" min="{{ date('Y-m-d') }}" onchange="App.wizard.onDateChange()" class="field">
            </div>
            <div>
              <label id="wzReturnDateLabel" for="wzReturnDate" class="label">Estimasi Turun</label>
              <input type="date" id="wzReturnDate" min="{{ date('Y-m-d') }}" class="field">
            </div>
          </div>

          <!-- Pilihan Rencana Camp (Hanya Muncul jika Kategori Camp) -->
          <div id="wzCampsiteBox" class="p-3 rounded-xl bg-raised/40 border border-line-strong space-y-2">
            <div class="flex flex-wrap items-center justify-between gap-1">
              <span class="text-xs text-ink font-semibold">Rencana Area Camp Resmi</span>
              <span class="text-[11px] text-emerald-400">Disarankan Pos 3</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-ink">
              <label class="p-2.5 rounded-xl bg-raised border border-line-strong flex items-center gap-2.5 cursor-pointer hover:border-emerald-500/40">
                <input type="radio" name="wzCampsite" value="POS_2" class="accent-emerald-400 w-4 h-4 shrink-0">
                <span>Pos 2 Batu Gendrung (Hutan Pinus)</span>
              </label>
              <label class="p-2.5 rounded-xl bg-raised border border-line-strong flex items-center gap-2.5 cursor-pointer hover:border-emerald-500/40">
                <input type="radio" name="wzCampsite" value="POS_3" checked class="accent-emerald-400 w-4 h-4 shrink-0">
                <span>Pos 3 Thun 30 <span class="ml-1 px-1.5 py-0.5 rounded bg-emerald-500/15 text-[10px] font-bold text-emerald-300">Disarankan</span></span>
              </label>
            </div>
            <p class="text-[11px] text-muted leading-relaxed">Pos 2 dan Pos 3 dijaga ranger dengan air, toilet, mushola, dan shelter. <strong class="text-rose-300">Dilarang camp di puncak</strong>.</p>
          </div>

          <!-- Stepper Rombongan -->
          <div>
            <div class="flex justify-between items-center mb-1.5">
              <label class="label !mb-0">Jumlah Pendaki</label>
              <span id="wzClimberCountLabel" class="text-xs font-bold text-brand tabular-nums">4 ORANG</span>
            </div>
            <div class="flex items-center bg-raised border border-line-strong rounded-xl overflow-hidden">
              <button type="button" onclick="App.wizard.adjustClimbers(-1)" class="tap-active w-14 h-12 bg-hover text-white hover:bg-[#282E38] font-bold text-lg transition flex items-center justify-center" aria-label="Kurangi pendaki">−</button>
              <div id="wzClimberCountBox" class="flex-1 text-center font-display font-bold text-lg text-white tabular-nums">4</div>
              <button type="button" onclick="App.wizard.adjustClimbers(1)" class="tap-active w-14 h-12 bg-hover text-white hover:bg-[#282E38] font-bold text-lg transition flex items-center justify-center" aria-label="Tambah pendaki">+</button>
            </div>
            <p class="text-[11px] text-subtle mt-1.5">Mendukung solo ({{ $profile['min_climbers_per_group'] ?? 1 }} orang) hingga rombongan (maks. {{ $profile['max_climbers_per_group'] ?? 15 }} orang).</p>
          </div>

          <!-- Layanan Tambahan Logistik (Multi-Unit Stepper) -->
          <div class="space-y-2.5 pt-2 border-t border-line">
            <span class="label !mb-0">Pilihan Logistik Basecamp</span>
            
            <!-- Service 1: Shuttle Ojek Stepper -->
            <div class="p-3 rounded-xl bg-raised border border-line-strong space-y-2">
              <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                  <span class="block text-sm font-semibold text-ink">Shuttle Ojek Trail</span>
                  <span class="text-xs text-muted">{{ $tariffs['ojek_route_name'] ?? 'Basecamp → Pos 1' }} (Rp {{ number_format($tariffs['ojek_fee_per_trip'], 0, ',', '.') }} / armada)</span>
                </div>
                <span id="wzOjekTotalLabel" class="text-sm font-bold text-brand tabular-nums shrink-0">Rp 0</span>
              </div>

              <div class="flex items-center justify-between pt-1">
                <span class="text-[11px] text-subtle">1 Motor = 1 Pendaki / Tas</span>
                
                <div class="flex items-center bg-canvas border border-line-strong rounded-xl overflow-hidden">
                  <button type="button" onclick="App.wizard.adjustOjek(-1)" class="tap-active w-10 h-10 bg-hover text-white hover:bg-[#282E38] font-bold text-base transition flex items-center justify-center" aria-label="Kurangi ojek">−</button>
                  <div id="wzOjekCountBox" class="w-11 text-center font-bold text-sm text-white tabular-nums">0</div>
                  <button type="button" onclick="App.wizard.adjustOjek(1)" class="tap-active w-10 h-10 bg-hover text-white hover:bg-[#282E38] font-bold text-base transition flex items-center justify-center" aria-label="Tambah ojek">+</button>
                </div>
              </div>
            </div>

            <!-- Service 2: Parkir Kendaraan Pribadi (Checkbox Toggle & Multi-Vehicle Stepper) -->
            <div class="p-3 rounded-xl bg-raised border border-line-strong space-y-2">
              <label class="flex items-center justify-between gap-3 cursor-pointer select-none">
                <div class="flex items-center gap-3 min-w-0">
                  <input type="checkbox" id="wzHasVehicle" onchange="App.wizard.toggleVehicle(this.checked)" class="w-5 h-5 rounded bg-raised border-line-strong text-brand accent-orange-500 cursor-pointer shrink-0">
                  <div class="min-w-0">
                    <span class="block text-sm font-semibold text-ink">Membawa Kendaraan Pribadi</span>
                    <span class="text-xs text-muted">Titip parkir resmi basecamp 24 jam</span>
                  </div>
                </div>
                <span id="wzParkingTotalLabel" class="text-sm font-bold text-cyan-400 tabular-nums shrink-0">Rp 0</span>
              </label>

              <!-- Pilihan Kendaraan & Stepper (Muncul jika diceklis) -->
              <div id="wzVehicleOptions" class="hidden pt-2.5 border-t border-line space-y-2">
                <!-- Motor -->
                <div class="flex items-center justify-between bg-surface p-2.5 rounded-xl border border-line">
                  <div>
                    <span class="block text-xs font-semibold text-ink">Sepeda Motor</span>
                    <span class="text-[11px] text-muted tabular-nums">Rp {{ number_format($tariffs['parking_motor_fee'], 0, ',', '.') }} / unit</span>
                  </div>
                  <div class="flex items-center bg-raised border border-line-strong rounded-xl overflow-hidden">
                    <button type="button" onclick="App.wizard.adjustMotorcycle(-1)" class="tap-active w-9 h-9 bg-hover text-white hover:bg-[#282E38] font-bold text-sm flex items-center justify-center">−</button>
                    <div id="wzMotorCountBox" class="w-9 text-center font-bold text-sm text-white tabular-nums">0</div>
                    <button type="button" onclick="App.wizard.adjustMotorcycle(1)" class="tap-active w-9 h-9 bg-hover text-white hover:bg-[#282E38] font-bold text-sm flex items-center justify-center">+</button>
                  </div>
                </div>

                <!-- Mobil -->
                <div class="flex items-center justify-between bg-surface p-2.5 rounded-xl border border-line">
                  <div>
                    <span class="block text-xs font-semibold text-ink">Mobil Pribadi</span>
                    <span class="text-[11px] text-muted tabular-nums">Rp {{ number_format($tariffs['parking_car_fee'], 0, ',', '.') }} / unit</span>
                  </div>
                  <div class="flex items-center bg-raised border border-line-strong rounded-xl overflow-hidden">
                    <button type="button" onclick="App.wizard.adjustCar(-1)" class="tap-active w-9 h-9 bg-hover text-white hover:bg-[#282E38] font-bold text-sm flex items-center justify-center">−</button>
                    <div id="wzCarCountBox" class="w-9 text-center font-bold text-sm text-white tabular-nums">0</div>
                    <button type="button" onclick="App.wizard.adjustCar(1)" class="tap-active w-9 h-9 bg-hover text-white hover:bg-[#282E38] font-bold text-sm flex items-center justify-center">+</button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Cost preview card -->
          <div class="p-3.5 rounded-xl bg-canvas border border-line space-y-1.5 text-xs tabular-nums">
            <div class="flex justify-between gap-2 text-muted">
              <span id="wzTxtSimaksiSub">SIMAKSI (4 Org @ Rp 25.000):</span>
              <span id="wzValSimaksiSub" class="text-ink font-medium shrink-0">Rp 100.000</span>
            </div>
            <div class="flex justify-between gap-2 text-muted">
              <span id="wzTxtOjekSub">Shuttle Ojek (0 Armada):</span>
              <span id="wzValOjekSub" class="text-ink font-medium shrink-0">Rp 0</span>
            </div>
            <div class="flex justify-between gap-2 text-muted hidden" id="wzRowParkirSub">
              <span id="wzTxtParkirSub">Parkir Kendaraan:</span>
              <span id="wzValParkirSub" class="text-cyan-400 font-semibold shrink-0">Rp 0</span>
            </div>
            <div class="pt-2 border-t border-line flex justify-between items-center text-sm">
              <span class="font-bold text-ink">Estimasi Total:</span>
              <span id="wzValTotalSub" class="font-display font-bold text-brand text-lg">Rp 100.000</span>
            </div>
          </div>

          <button type="button" onclick="App.wizard.validateStep1()" class="tap-active w-full h-12 bg-brand hover:bg-brand-hover text-white font-semibold text-sm rounded-xl transition flex items-center justify-center gap-2 shadow-lg shadow-brand/20">
            <span>Lanjut: Data Ketua (2/4)</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
          </button>
        </div>

        <!-- ============================================== -->
        <!-- STEP 2: IDENTITAS KETUA & KONTAK DARURAT       -->
        <!-- ============================================== -->
        <div id="wizardStep2" class="hidden space-y-4">
          <div class="p-3 rounded-xl bg-raised/70 border border-line text-xs text-muted leading-relaxed">
            <span class="text-brand font-semibold block mb-0.5">Identitas Ketua Penanggung Jawab</span>
            Wajib mengisi NIK KTP 16-digit asli dan nomor WhatsApp aktif untuk verifikasi di pos gerbang basecamp.
          </div>

          <div>
            <label for="wzLeaderName" class="label">Nama Lengkap Ketua (Sesuai KTP) <span class="text-brand">*</span></label>
            <input type="text" id="wzLeaderName" placeholder="Contoh: Arif Rahmansyah" class="field">
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label for="wzLeaderNik" class="label">NIK KTP (16 Digit) <span class="text-brand">*</span></label>
              <input type="text" id="wzLeaderNik" inputmode="numeric" maxlength="16" placeholder="320501xxxxxxxxxx" class="field">
            </div>
            <div>
              <label for="wzLeaderPhone" class="label">No. WhatsApp Aktif <span class="text-brand">*</span></label>
              <input type="tel" id="wzLeaderPhone" placeholder="0812xxxxxxxx" class="field">
            </div>
          </div>

          <div>
            <label for="wzLeaderAddress" class="label">
              Alamat Lengkap Domisili (Sesuai KTP) <span class="text-brand">*</span>
            </label>
            <textarea id="wzLeaderAddress" rows="2" placeholder="Jl. Merdeka No. 45, RT 02/RW 05, Kel. Tarogong, Kab. Garut" class="field resize-none leading-relaxed"></textarea>
            <input type="hidden" id="wzLeaderCity" value="">
          </div>

          <!-- Upload Foto / Scan KTP Asli Ketua (dengan Auto Image Compression) -->
          <div class="space-y-2">
            <div class="flex flex-wrap justify-between items-center gap-1">
              <label class="label !mb-0">
                Foto / Scan KTP Asli Ketua <span class="text-brand">* (Wajib)</span>
              </label>
              <span id="wzKtpCompressBadge" class="text-[11px] font-medium text-emerald-400 hidden">Kompresi otomatis aktif</span>
            </div>

            <div onclick="document.getElementById('wzLeaderKtpInput').click()" class="border-2 border-dashed border-line-strong hover:border-brand bg-raised/40 hover:bg-raised rounded-xl p-3.5 text-center cursor-pointer transition">
              <input type="file" id="wzLeaderKtpInput" accept="image/*,application/pdf" class="hidden" onchange="App.wizard.handleKtpSelect(event)">
              
              <div id="wzKtpPlaceholder" class="space-y-1 py-1">
                <svg class="w-6 h-6 mx-auto text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                <div class="text-xs sm:text-sm font-semibold text-ink">Ambil Foto / Unggah KTP Asli</div>
                <div class="text-[11px] text-muted">JPG, PNG, WebP · Auto-compress ringan &amp; hemat kuota</div>
              </div>

              <!-- Preview State -->
              <div id="wzKtpPreviewBox" class="hidden flex items-center justify-between gap-3 text-left">
                <div class="flex items-center gap-2.5 min-w-0">
                  <img id="wzKtpPreviewImg" src="" alt="KTP Ketua" class="w-12 h-9 object-cover rounded-lg border border-line-strong shrink-0">
                  <div class="min-w-0">
                    <span id="wzKtpFileName" class="text-xs font-bold text-ink block truncate">ktp_ketua.jpg</span>
                    <span id="wzKtpFileSize" class="text-[11px] text-emerald-400 block">210 KB</span>
                  </div>
                </div>
                <button type="button" onclick="event.stopPropagation(); App.wizard.removeKtpFile()" class="tap-active h-8 px-2.5 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 border border-rose-500/30 text-xs font-semibold shrink-0">
                  Hapus
                </button>
              </div>
            </div>

            <!-- Disclaimer Perlindungan Data Pribadi (PDP) KTP Auto-Purge -->
            <div class="p-3 rounded-xl bg-canvas border border-emerald-500/30 flex items-start gap-2.5 text-xs leading-relaxed">
              <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
              <div class="space-y-0.5">
                <span class="text-emerald-400 font-semibold uppercase block text-[10px] tracking-wider">Privasi &amp; Perlindungan Data (PDP)</span>
                <p class="text-muted text-[11px] leading-relaxed">
                  Foto KTP hanya digunakan untuk validasi identitas. <strong class="text-emerald-300">Berkas foto KTP dihapus permanen secara otomatis</strong> segera setelah pendakian selesai (check-out di gerbang pos).
                </p>
              </div>
            </div>
          </div>

          <!-- Riwayat Penyakit Bawaan & Kondisi Medis Ketua -->
          <div>
            <div class="flex justify-between items-center mb-1.5">
              <label for="wzLeaderMedical" class="label !mb-0">Riwayat Penyakit / Alergi Ketua</label>
              <span class="text-[11px] text-subtle">SOP Medis SAR</span>
            </div>
            <input type="text" id="wzLeaderMedical" value="Tidak Ada" placeholder="Contoh: Tidak Ada / Asma / Alergi Dingin" class="field">
          </div>

          <!-- Emergency Contact Subcard -->
          <div class="p-3.5 bg-canvas border border-line-strong rounded-xl space-y-3">
            <div class="flex items-center gap-1.5 text-brand">
              <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
              <span class="text-xs font-bold uppercase tracking-wider">Kontak Darurat (Keluarga di Rumah)</span>
            </div>
            <p class="text-[11px] text-muted leading-snug">Cantumkan nomor keluarga yang TIDAK ikut mendaki untuk SOP darurat SAR.</p>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label for="wzEmergName" class="label">Nama Kontak Darurat <span class="text-brand">*</span></label>
                <input type="text" id="wzEmergName" placeholder="Contoh: Siti Fatimah" class="field">
              </div>
              <div>
                <label for="wzEmergRel" class="label">Hubungan</label>
                <select id="wzEmergRel" class="field">
                  <option value="Orang Tua">Orang Tua</option>
                  <option value="Pasangan">Pasangan (Suami/Istri)</option>
                  <option value="Saudara Kandung">Saudara Kandung</option>
                  <option value="Keluarga / Kerabat">Keluarga / Kerabat</option>
                </select>
              </div>
            </div>

            <div>
              <label for="wzEmergPhone" class="label">No. Telp / WhatsApp Darurat <span class="text-brand">*</span></label>
              <input type="tel" id="wzEmergPhone" placeholder="08xxxxxxxxxx" class="field">
            </div>
          </div>

          <div class="grid grid-cols-2 gap-2.5 pt-1">
            <button type="button" onclick="App.wizard.goTo(1)" class="tap-active h-11 bg-raised border border-line-strong text-ink font-semibold text-xs sm:text-sm rounded-xl transition hover:bg-hover">
              ← Kembali
            </button>
            <button type="button" onclick="App.wizard.validateStep2()" class="tap-active h-11 bg-brand text-white font-semibold text-xs sm:text-sm rounded-xl transition hover:bg-brand-hover flex items-center justify-center gap-1.5">
              <span>Manifest (3/4)</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- STEP 3: MANIFEST ANGGOTA ROMBONGAN             -->
        <!-- ============================================== -->
        <div id="wizardStep3" class="hidden space-y-4">
          <div class="p-3 rounded-xl bg-raised/70 border border-line text-xs text-muted leading-relaxed">
            <span class="text-brand font-semibold block mb-0.5">Manifest Keselamatan Anggota</span>
            Seluruh nama &amp; NIK anggota diinput untuk registrasi manifest resmi &amp; keselamatan pendakian.
          </div>

          <!-- Leader Card (Member #1 Summary) -->
          <div class="p-3 rounded-xl bg-canvas border border-line-strong flex items-center justify-between gap-2 text-xs">
            <div class="min-w-0">
              <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded-md text-[10px] bg-brand/15 text-brand border border-brand/30 font-bold shrink-0" id="wzLeaderRoleTag">ANGGOTA #1</span>
                <span class="font-bold text-ink truncate" id="wzLeaderSummaryName">Ketua Tim</span>
              </div>
              <span class="text-[11px] text-muted block mt-1 font-code" id="wzLeaderSummaryNik">NIK: -</span>
            </div>
            <span class="text-[10px] text-emerald-400 font-bold shrink-0" id="wzLeaderRoleBadge">KETUA TIM</span>
          </div>

          <!-- Dynamic Member Cards Container (#2 s.d. #N) -->
          <div id="wzMembersList" class="space-y-3">
            <!-- Dynamically generated via App.wizard.renderMembers() -->
          </div>

          <div class="grid grid-cols-2 gap-2.5 pt-1">
            <button type="button" onclick="App.wizard.goTo(2)" class="tap-active h-11 bg-raised border border-line-strong text-ink font-semibold text-xs sm:text-sm rounded-xl transition hover:bg-hover">
              ← Kembali
            </button>
            <button type="button" onclick="App.wizard.validateStep3()" class="tap-active h-11 bg-brand text-white font-semibold text-xs sm:text-sm rounded-xl transition hover:bg-brand-hover flex items-center justify-center gap-1.5">
              <span>Pembayaran (4/4)</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- STEP 4: PEMBAYARAN & UPLOAD BUKTI BAYAR        -->
        <!-- ============================================== -->
        <div id="wizardStep4" class="hidden space-y-4">
          <!-- Total Payment Callout with Unique Code -->
          <div class="p-3.5 rounded-xl bg-canvas border border-line-strong space-y-2 tabular-nums">
            <div class="flex items-center justify-between gap-2 text-xs text-muted">
              <span>Subtotal SIMAKSI &amp; Logistik:</span>
              <span id="wzSubtotalPay" class="text-ink font-semibold">Rp 100.000</span>
            </div>
            <div id="wzParkingPayRow" class="hidden flex items-center justify-between gap-2 text-xs text-cyan-400">
              <span>Biaya Parkir Kendaraan:</span>
              <span id="wzParkingPay" class="font-semibold">Rp 0</span>
            </div>
            <div id="wzUniqueCodeRow" class="flex items-center justify-between gap-2 text-xs text-amber-400">
              <span class="flex items-center gap-1.5">
                <span>Kode Unik Verifikasi:</span>
              </span>
              <span id="wzUniqueCodePay" class="font-bold">+Rp 348</span>
            </div>
            <div class="pt-2.5 border-t border-line flex items-center justify-between gap-2">
              <div>
                <span id="wzTotalLabel" class="text-[11px] text-muted block uppercase">Total Transfer</span>
                <span id="wzFinalTotal" class="font-display text-xl sm:text-2xl font-bold text-brand">Rp 100.348</span>
              </div>
              <button type="button" id="btnCopyNominal" onclick="App.wizard.copyTransferAmount()" class="tap-active h-9 px-3 rounded-xl bg-raised hover:bg-hover border border-line-strong text-xs text-ink flex items-center gap-1.5 font-semibold shrink-0">
                <svg class="w-3.5 h-3.5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <span>Salin Nominal</span>
              </button>
            </div>
            <p id="wzUniqueNote" class="text-[11px] text-amber-300/90 pt-2 leading-relaxed border-t border-line/60">
              Mohon transfer <strong>tepat hingga 3 digit terakhir</strong> agar kasir dapat memverifikasi mutasi rekening secara instan.
            </p>
          </div>

          <!-- Payment Method Options -->
          <div class="space-y-2">
            <label class="label !mb-0">Pilih Metode Pembayaran</label>
            
            <div class="grid grid-cols-3 gap-2">
              <label class="payment-method-card p-2.5 rounded-xl bg-raised border border-brand cursor-pointer text-center block transition" id="lblMethodQris">
                <input type="radio" name="wzPaymentMethod" value="qris" checked onchange="App.wizard.onMethodChange('qris')" class="hidden">
                <span class="text-xs sm:text-sm font-bold text-ink block">QRIS</span>
                <span class="text-[10px] text-muted block mt-0.5">E-Wallet &amp; Bank</span>
              </label>

              <label class="payment-method-card p-2.5 rounded-xl bg-raised border border-line-strong cursor-pointer text-center block transition" id="lblMethodTransfer">
                <input type="radio" name="wzPaymentMethod" value="transfer" onchange="App.wizard.onMethodChange('transfer')" class="hidden">
                <span class="text-xs sm:text-sm font-bold text-muted block">Transfer</span>
                <span class="text-[10px] text-muted block mt-0.5">Rekening Bank</span>
              </label>

              <label class="payment-method-card p-2.5 rounded-xl bg-raised border border-line-strong cursor-pointer text-center block transition" id="lblMethodCash">
                <input type="radio" name="wzPaymentMethod" value="cash_pos" onchange="App.wizard.onMethodChange('cash_pos')" class="hidden">
                <span class="text-xs sm:text-sm font-bold text-muted block">Tunai</span>
                <span class="text-[10px] text-muted block mt-0.5">Kasir Basecamp</span>
              </label>
            </div>
          </div>

          <!-- Method Details Panel: QRIS -->
          <div id="methodDetailQris" class="p-3.5 bg-canvas border border-line-strong rounded-xl space-y-3 text-center">
            <span class="text-[11px] tracking-wider text-muted block uppercase font-semibold">Scan QRIS Resmi {{ strtoupper($profile['basecamp_name'] ?? 'SAMSARA GARUT') }}</span>
            
            <!-- Visual QRIS Container -->
            <div class="max-w-[220px] mx-auto bg-white p-2.5 rounded-2xl shadow-xl border border-stone-700 flex items-center justify-center overflow-hidden">
              @if(!empty($profile['payment_qris_image']))
                <a href="{{ asset('storage/' . $profile['payment_qris_image']) }}" target="_blank" title="Klik untuk memperbesar / simpan QRIS" class="flex items-center justify-center w-full max-h-[240px]">
                  <img src="{{ asset('storage/' . $profile['payment_qris_image']) }}" alt="QRIS Resmi {{ $profile['payment_qris_name'] ?? 'Basecamp' }}" class="max-w-full max-h-[220px] w-auto h-auto object-contain rounded cursor-pointer transition hover:scale-[1.02]">
                </a>
              @else
                <div class="w-40 h-40 flex flex-col items-center justify-center p-3 text-center text-black">
                  <span class="text-xs font-bold">QRIS belum tersedia</span>
                  <span class="text-[11px] mt-1">Silakan pilih Transfer Bank atau Bayar Tunai di Pos.</span>
                </div>
              @endif
            </div>

            <div class="text-[11px] text-muted space-y-1.5">
              <span class="text-ink block font-semibold">NMID: {{ $profile['payment_qris_nmid'] ?? '' }} — {{ $profile['payment_qris_name'] ?? '' }}</span>
              <span class="text-subtle block">Mendukung: GoPay, OVO, Dana, ShopeePay, BCA, Mandiri, dll.</span>
              @if(!empty($profile['payment_qris_image']))
                <div class="pt-1">
                  <a href="{{ asset('storage/' . $profile['payment_qris_image']) }}" target="_blank" download class="inline-flex items-center gap-1.5 h-8 px-3 bg-raised hover:bg-hover text-brand hover:text-white border border-brand/40 rounded-lg text-[11px] font-semibold transition">
                    <span>Buka / Simpan QRIS Resolusi Penuh</span>
                  </a>
                </div>
              @endif
            </div>
          </div>

          <!-- Method Details Panel: Transfer Bank -->
          <div id="methodDetailTransfer" class="hidden p-3.5 bg-canvas border border-line-strong rounded-xl space-y-2 text-xs">
            <span class="text-[11px] text-muted block uppercase font-semibold">Rekening Resmi {{ strtoupper($profile['expedition_name'] ?? 'EXPEDITION') }}</span>
            <div class="p-3 bg-raised border border-line-strong rounded-xl flex items-center justify-between gap-2">
              <div class="min-w-0">
                <span class="text-[11px] text-muted block">{{ $profile['payment_bank_name'] ?? '' }}</span>
                <span class="text-sm sm:text-base font-bold text-ink tracking-wider font-code">{{ $profile['payment_bank_account'] ?? '' }}</span>
                <span class="text-[11px] text-muted block truncate">a.n. {{ $profile['payment_bank_holder'] ?? '' }}</span>
              </div>
              <button type="button" onclick="App.wizard.copyAccount('{{ $profile['payment_bank_account_raw'] ?? '' }}', '{{ $profile['payment_bank_name'] ?? 'BCA' }}')" class="tap-active h-9 px-3 bg-hover text-brand border border-brand/30 rounded-lg text-xs font-semibold shrink-0">
                Salin Rek
              </button>
            </div>
          </div>

          <!-- Method Details Panel: Cash Loket -->
          <div id="methodDetailCash" class="hidden p-3.5 bg-canvas border border-line-strong rounded-xl space-y-1 text-xs text-muted">
            <span class="text-[11px] text-brand font-bold block uppercase">Pembayaran Tunai di Basecamp</span>
            <p class="leading-relaxed">
              Kode registrasi Anda berstatus <strong>MENUNGGU VERIFIKASI (PENDING)</strong>. Pembayaran dilakukan langsung di Kasir Basecamp sebelum pemeriksaan fisik di Gate.
            </p>
          </div>

          <!-- File Upload: Bukti Pembayaran -->
          <div id="wzProofUploadContainer" class="space-y-1.5">
            <div class="flex items-center justify-between gap-2">
              <label class="label !mb-0">
                Upload Bukti Bayar <span id="wzProofRequiredBadge" class="text-brand">* (Wajib QRIS/Transfer)</span>
              </label>
              <span class="text-[11px] text-subtle">Maks. 5 MB</span>
            </div>

            <!-- Upload Area -->
            <div id="wzDropzone" onclick="document.getElementById('wzPaymentProofInput').click()" class="border-2 border-dashed border-line-strong hover:border-brand rounded-xl p-3.5 text-center cursor-pointer transition bg-raised/40">
              <input type="file" id="wzPaymentProofInput" accept="image/*,application/pdf" class="hidden" onchange="App.wizard.handleFileSelect(event)">
              
              <div id="wzUploadPlaceholder" class="space-y-1 py-1">
                <svg class="w-6 h-6 mx-auto text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span class="text-xs sm:text-sm text-ink block font-semibold">Pilih Foto Struk / Screenshot Bukti</span>
                <span class="text-[11px] text-subtle block">Format: JPG, PNG, WEBP, atau PDF</span>
              </div>

              <!-- Preview State -->
              <div id="wzUploadPreviewBox" class="hidden flex items-center gap-3 text-left">
                <img id="wzUploadPreviewImg" src="" alt="Bukti Transfer" class="w-12 h-12 object-cover rounded-lg border border-line-strong shrink-0">
                <div class="flex-1 min-w-0">
                  <span id="wzUploadFileName" class="text-xs font-bold text-ink block truncate">bukti_transfer.jpg</span>
                  <span id="wzUploadFileSize" class="text-[11px] text-muted block">240 KB</span>
                </div>
                <button type="button" onclick="event.stopPropagation(); App.wizard.removeFile()" class="tap-active h-8 px-2.5 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 border border-rose-500/30 text-xs font-semibold shrink-0">
                  Hapus
                </button>
              </div>
            </div>
          </div>

          <!-- SOP Checkbox Agreement -->
          <label class="flex items-start gap-2.5 p-3 rounded-xl bg-raised/50 border border-line-strong cursor-pointer">
            <input type="checkbox" id="wzAgreeCheck" checked class="mt-0.5 w-5 h-5 rounded bg-hover border-line-strong accent-orange-500 shrink-0">
            <span class="text-xs text-muted leading-relaxed">
              Saya menyatakan data identitas rombongan adalah sah &amp; siap mematuhi SOP Keselamatan SAMSARA
              @if(!empty($tariffs['trash_deposit_enabled']))
                serta menyiapkan <strong>Deposit Sampah Rp {{ number_format($tariffs['trash_deposit_amount'] ?? 50000, 0, ',', '.') }}</strong> di loket.
              @else
                serta menjaga kebersihan jalur pendakian.
              @endif
            </span>
          </label>

          <!-- Error Alert Banner inside modal -->
          <div id="wzErrorAlert" class="hidden p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs"></div>

          <div class="grid grid-cols-2 gap-2.5 pt-1">
            <button type="button" onclick="App.wizard.goTo(3)" class="tap-active h-11 bg-raised border border-line-strong text-ink font-semibold text-xs sm:text-sm rounded-xl transition hover:bg-hover">
              ← Kembali
            </button>
            <button type="button" id="btnSubmitRegistration" onclick="App.wizard.submit()" class="tap-active h-11 bg-brand text-white font-semibold text-xs sm:text-sm rounded-xl transition hover:bg-brand-hover flex items-center justify-center gap-1.5 shadow-lg shadow-brand/20">
              <span id="txtSubmitBtn">Kirim &amp; Buat Tiket</span>
              <svg id="spinnerSubmitBtn" class="hidden animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
            </button>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- STEP 5: HASIL TIKET RESMI TERBIT               -->
        <!-- ============================================== -->
        <div id="wizardStep5" class="hidden text-center py-2 space-y-4">
          <div id="wzResultIconWrap" class="w-14 h-14 mx-auto rounded-full bg-amber-500/15 border-2 border-amber-500/60 flex items-center justify-center text-amber-400 samsara-status-ring--verifying">
            <svg class="w-7 h-7 samsara-hourglass" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M6.5 3.5h11M6.5 20.5h11" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
              <path d="M8 3.5v3.1c0 2.3 2.5 4.2 4 5.4-1.5 1.2-4 3.1-4 5.4v3.1h8v-3.1c0-2.3-2.5-4.2-4-5.4 1.5-1.2 4-3.1 4-5.4V3.5H8z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
              <polygon class="samsara-hourglass-sand-top" points="9.2,5.6 14.8,5.6 12,11.1" fill="#FF5722"/>
              <polygon class="samsara-hourglass-sand-bottom" points="12,12.9 9.2,18.4 14.8,18.4" fill="#FF5722"/>
              <line class="samsara-hourglass-stream" x1="12" y1="11.5" x2="12" y2="18.2" stroke="#FF5722" stroke-width="1.4" stroke-linecap="round"/>
            </svg>
          </div>
          
          <div>
            <span id="wzResultStatusBadge" class="text-[11px] text-amber-400 font-bold px-2.5 py-0.5 bg-amber-500/10 border border-amber-500/30 rounded-full uppercase tracking-wider">
              MENUNGGU VERIFIKASI KASIR
            </span>
            <h4 id="wzResultTitle" class="font-display font-bold text-xl text-ink mt-2">RESERVASI BERHASIL DIREKAM</h4>
            <p id="wzResultSubtitle" class="text-xs text-muted mt-1 max-w-xs mx-auto">
              Simpan kode registrasi SIMAKSI berikut untuk ditunjukkan ke petugas loket Basecamp SAMSARA.
            </p>
            <div class="pt-3 flex flex-col items-center gap-1.5">
              <button type="button" id="wzRefreshStatusBtn" onclick="App.wizard.refreshStatus()" class="tap-active inline-flex items-center justify-center gap-2 h-9 px-4 rounded-xl bg-raised hover:bg-hover border border-line-strong hover:border-brand text-ink font-semibold text-xs transition">
                <svg id="wzRefreshStatusIcon" class="w-3.5 h-3.5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span id="wzRefreshStatusText">Perbarui Status</span>
              </button>
              <span id="wzStatusSyncNote" class="hidden text-[11px] text-muted"></span>
            </div>
          </div>

          <!-- Ticket Box -->
          <div class="p-3.5 bg-raised border border-line-strong rounded-xl text-left space-y-2.5 text-xs">
            <div class="flex justify-between items-center border-b border-line-strong pb-2 gap-2">
              <span class="text-muted text-[11px] uppercase">Kode SIMAKSI</span>
              <div class="flex items-center gap-2">
                <span id="wzResultCode" class="font-code text-base font-bold text-brand tracking-wider">SMK-2611-K8F2Q</span>
                <button type="button" onclick="App.wizard.copyResultCode()" class="tap-active px-2 py-1 rounded-lg bg-hover text-ink text-[11px] font-semibold">
                  Salin
                </button>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-2.5 text-xs text-muted py-0.5">
              <div>
                <span class="text-subtle text-[11px] block">Ketua Tim</span>
                <span id="wzResultLeader" class="font-semibold text-ink">-</span>
              </div>
              <div>
                <span class="text-subtle text-[11px] block">Rombongan</span>
                <span id="wzResultClimbers" class="font-semibold text-ink">-</span>
              </div>
              <div>
                <span class="text-subtle text-[11px] block">Tanggal Naik</span>
                <span id="wzResultDate" class="text-ink font-medium">-</span>
              </div>
              <div>
                <span class="text-subtle text-[11px] block">Total Biaya</span>
                <span id="wzResultTotal" class="text-emerald-400 font-bold tabular-nums">-</span>
              </div>
            </div>

            <div class="p-2.5 bg-canvas rounded-lg border border-line flex items-center justify-between gap-2 text-[11px]">
              <span class="text-muted">Metode Pembayaran</span>
              <span id="wzResultMethod" class="text-ink font-semibold uppercase">QRIS</span>
            </div>
          </div>

          <!-- WhatsApp Admin Notification Banner -->
          <div class="p-3 rounded-xl bg-[#25D366]/10 border border-[#25D366]/30 text-left space-y-1">
            <div class="flex items-center gap-1.5 text-[#25D366] text-xs font-bold">
              <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z"/></svg>
              <span>Konfirmasi WhatsApp Admin</span>
            </div>
            <p class="text-[11px] text-muted leading-snug">
              Rincian permohonan telah disiapkan. Jika WhatsApp belum terbuka otomatis, gunakan tombol di bawah:
            </p>
          </div>

          <div class="p-3 rounded-xl bg-raised/40 border border-line text-[11px] text-muted text-left space-y-1">
            <span class="text-ink font-semibold block">Panduan Saat Tiba di Basecamp</span>
            <ul class="list-disc pl-4 space-y-0.5">
              <li>Tunjukkan kode registrasi ke <strong>Kasir Loket</strong> Basecamp.</li>
              <li>Bawa fisik <strong>KTP Asli</strong> ketua tim untuk verifikasi manifest.</li>
              @if(!empty($tariffs['trash_deposit_enabled']))
                <li>Siapkan <strong>Deposit Sampah Rp {{ number_format($tariffs['trash_deposit_amount'] ?? 50000, 0, ',', '.') }}</strong> (dikembalikan saat turun).</li>
              @else
                <li>Rombongan wajib menjaga kebersihan jalur (bebas deposit sampah).</li>
              @endif
            </ul>
          </div>

          <!-- Direct Ticket Access & Download Buttons -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
            <a id="btnViewDigitalTicket" href="#" target="_blank" class="tap-active h-11 px-3 bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs rounded-xl transition flex items-center justify-center gap-1.5">
              <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              <span>Surat Jalan Digital</span>
            </a>

            <a id="btnDownloadTicketPdf" href="#" target="_blank" class="tap-active h-11 px-3 bg-brand hover:bg-brand-hover text-white font-semibold text-xs rounded-xl transition flex items-center justify-center gap-1.5">
              <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
              <span>Unduh PDF Resmi</span>
            </a>
          </div>

          <!-- Action Buttons -->
          <div class="space-y-2 pt-1">
            <a id="btnOpenWhatsApp" href="#" target="_blank" class="tap-active w-full h-11 bg-[#25D366] hover:bg-[#20BD5A] text-black font-bold text-xs sm:text-sm rounded-xl transition flex items-center justify-center gap-2">
              <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z"/></svg>
              <span>Buka Chat WhatsApp Admin</span>
            </a>

            <button type="button" id="btnCopyWhatsAppText" onclick="App.wizard.copyWhatsAppText()" class="tap-active w-full h-10 bg-raised hover:bg-hover border border-line-strong text-ink font-semibold text-xs rounded-xl transition flex items-center justify-center gap-2">
              <svg class="w-4 h-4 text-[#25D366]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
              <span id="txtCopyWhatsApp">Salin Format Pesan WA</span>
            </button>

            <button type="button" onclick="App.modal.close('regModal')" class="tap-active w-full h-10 bg-canvas border border-line-strong text-muted hover:text-ink font-semibold text-xs rounded-xl transition">
              Tutup &amp; Lihat Beranda
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- ======================================================== -->
  <!-- MODAL 2: DEDICATED TICKET TRACKING & PROOF UPLOAD        -->
  <!-- ======================================================== -->
  <div id="ticketModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-md [&:not(.hidden)]:flex items-end sm:items-center justify-center sm:p-4" onclick="if(event.target === this) App.modal.close('ticketModal')">
    <div class="bg-surface border border-line-strong rounded-t-3xl sm:rounded-2xl w-full sm:max-w-lg max-h-[92dvh] flex flex-col shadow-2xl overflow-hidden">
      
      <!-- Top Modal Header (Sticky) -->
      <div class="px-4 sm:px-5 pt-4 pb-3 border-b border-line bg-surface shrink-0">
        <div class="flex items-center justify-between gap-3">
          <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-2.5 h-2.5 rounded-sm bg-brand shrink-0"></div>
            <div class="min-w-0">
              <span class="text-[11px] text-brand font-semibold tracking-wider uppercase block leading-none">Pelacak Mandiri</span>
              <h3 class="font-display font-bold text-base sm:text-lg text-ink leading-tight truncate mt-0.5">Status SIMAKSI &amp; Tiket</h3>
            </div>
          </div>
          <button type="button" onclick="App.modal.close('ticketModal')" class="tap-active w-9 h-9 shrink-0 rounded-full bg-raised border border-line-strong text-muted hover:text-ink flex items-center justify-center font-bold text-sm" aria-label="Tutup">
            ✕
          </button>
        </div>
        <p class="text-xs text-muted mt-1.5 leading-snug">
          Lacak verifikasi permit dan unggah bukti transfer susulan jika sempat terclose saat bayar.
        </p>
      </div>

      <!-- Scrollable Tracking Body -->
      <div class="flex-1 overflow-y-auto overscroll-y-contain p-4 sm:p-5 space-y-4 safe-bottom">
        
        <!-- Search Input Bar -->
        <div class="space-y-2">
          <label for="searchTicketInput" class="label !mb-0">
            Kode Registrasi atau No. WhatsApp Ketua
          </label>
          <div class="flex gap-2">
            <input type="text" id="searchTicketInput" value="" placeholder="SMK-2611-K8F2Q atau 0812..." oninput="App.ticket.toggleNikField()" onkeydown="if(event.key==='Enter') App.ticket.search()" class="field flex-1 font-code uppercase placeholder:normal-case placeholder:font-sans">
            <button type="button" id="btnSearchTicket" onclick="App.ticket.search()" class="h-11 lg:h-10 px-4 bg-brand hover:bg-brand-hover text-white font-semibold text-sm rounded-xl tap-active transition flex items-center gap-1.5 shrink-0">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
              <span>Lacak</span>
            </button>
          </div>

          <!-- Verifikasi kepemilikan: wajib saat mencari tiket tanpa token (baik kode pendek maupun nomor WA) -->
          <div id="searchNikWrap" class="hidden pt-1 space-y-1">
            <label for="searchNikLast4" class="label">4 Digit Terakhir NIK Ketua</label>
            <input type="text" id="searchNikLast4" inputmode="numeric" maxlength="4" pattern="\d{4}" placeholder="••••" onkeydown="if(event.key==='Enter') App.ticket.search()" class="field !w-32 font-code tracking-[0.35em]">
            <p class="text-[11px] text-subtle">Demi keamanan data, pencarian tiket wajib disertai 4 digit terakhir NIK ketua.</p>
          </div>

          <!-- Tiket yang pernah dibuat dari perangkat ini -->
          <div id="ticketQuickChips" class="flex flex-wrap gap-1.5 pt-1 text-xs">
            <span id="recentTicketChipContainer"></span>
          </div>
        </div>

        <!-- Dynamic Result Container -->
        <div id="ticketSearchResult">
          <!-- Populated dynamically by App.ticket.search() -->
        </div>

      </div>
    </div>
  </div>

  <!-- ======================================================== -->
  <!-- MODAL 3: IMAGE PREVIEW LIGHTBOX MODAL                    -->
  <!-- ======================================================== -->
  <div id="imagePreviewModal" class="hidden fixed inset-0 z-50 bg-black/90 backdrop-blur-md [&:not(.hidden)]:flex items-end sm:items-center justify-center sm:p-4" onclick="if(event.target === this) App.modal.close('imagePreviewModal')">
    <div class="bg-surface border border-line-strong rounded-t-3xl sm:rounded-2xl w-full sm:max-w-xl max-h-[92dvh] flex flex-col shadow-2xl overflow-hidden">
      <div class="px-4 py-3.5 border-b border-line flex items-center justify-between shrink-0">
        <div class="flex items-center gap-2 min-w-0">
          <div class="w-2.5 h-2.5 rounded-sm bg-brand shrink-0"></div>
          <h3 id="imagePreviewTitle" class="font-display font-bold text-sm sm:text-base text-ink truncate">PREVIEW DOKUMEN</h3>
        </div>
        <button type="button" onclick="App.modal.close('imagePreviewModal')" class="w-9 h-9 rounded-full bg-raised border border-line-strong text-muted hover:text-ink flex items-center justify-center font-bold text-sm transition shrink-0" aria-label="Tutup">
          ✕
        </button>
      </div>
      <div class="flex-1 overflow-auto p-4 flex items-center justify-center bg-canvas">
        <img id="imagePreviewImg" src="" alt="Preview" class="max-h-[62dvh] w-auto max-w-full object-contain rounded-xl border border-line">
      </div>
      <div class="p-3.5 border-t border-line flex items-center justify-between gap-3 text-xs safe-bottom">
        <a id="imagePreviewRawLink" href="#" target="_blank" class="text-cyan-400 hover:underline text-xs flex items-center gap-1 font-semibold">
          <span>Buka Resolusi Penuh ↗</span>
        </a>
        <button type="button" onclick="App.modal.close('imagePreviewModal')" class="h-9 px-4 rounded-xl bg-raised ring-1 ring-line text-ink font-semibold text-xs">
          Tutup
        </button>
      </div>
    </div>
  </div>

  <!-- Live Pricing, Profile & Telemetry Configuration Injection -->
  <script>
    window.SAMSARA_LIVE_PRICING = @json($tariffs ?? \App\Models\AppSetting::getTariffs());
    window.SAMSARA_EXPEDITION_PROFILE = @json($profile ?? \App\Models\AppSetting::getExpeditionProfile());
    window.SAMSARA_LIVE_TELEMETRY = @json($trailCondition ?? null);
  </script>

  <!-- Modular JavaScript Scripts -->
  <script src="{{ asset('assets/js/currency-input.js') }}?v={{ filemtime(public_path('assets/js/currency-input.js')) }}"></script>
  <script src="{{ asset('assets/js/data.js') }}?v={{ filemtime(public_path('assets/js/data.js')) }}"></script>
  <script src="{{ asset('assets/js/app.js') }}?v={{ filemtime(public_path('assets/js/app.js')) }}"></script>
</body>
</html>
