<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  @include('management.partials.head', ['withViewMode' => false, 'lockViewport' => false])
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $profile['expedition_name'] ?? 'SAMSARA EXPEDITION' }} — {{ $profile['mountain_name'] ?? 'Gunung Cikuray' }} {{ $profile['mountain_elevation'] ?? '2.821 MDPL' }} | Portal Resmi</title>
  <meta name="description" content="Portal resmi SIMAKSI pendakian {{ $profile['mountain_name'] ?? 'Gunung Cikuray' }} via {{ $profile['basecamp_name'] ?? 'Basecamp SAMSARA' }}. Cek kuota harian, pesan e-SIMAKSI, dan terima tiket lewat WhatsApp.">
  <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}?v={{ filemtime(public_path('assets/css/main.css')) }}">
</head>
<body class="min-h-screen flex flex-col bg-canvas selection:bg-brand selection:text-white pb-24 md:pb-8">

  @include('portal.partials.header')

  <main class="flex-1 max-w-6xl mx-auto w-full px-4 pt-4 md:pt-6 space-y-4">

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
        • <a href="{{ $profile['basecamp_maps_url'] ?? 'https://maps.app.goo.gl/xGzRkQTTArmDxpQf9' }}" target="_blank" rel="noopener noreferrer" class="text-brand hover:underline font-bold inline-flex items-center gap-0.5">Google Maps ↗</a>
      </p>
      <p>Radio Darurat: {{ $profile['sar_frequency'] ?? '149.250 MHz' }} • WhatsApp Admin: {{ $profile['admin_whatsapp_display'] ?? '+62 821-2233-4455' }}</p>
      <p class="text-stone-500">© {{ date('Y') }} {{ strtoupper($profile['expedition_name'] ?? 'SAMSARA EXPEDITION') }}. LINDUNGI KELESTARIAN HUTAN {{ strtoupper($profile['mountain_name'] ?? 'CIKURAY') }}.</p>
    </footer>

  </main>

  @include('portal.partials.bottom-nav')

  <!-- ======================================================== -->
  <!-- MODAL 1: MULTI-STEP REGISTRATION MODAL                   -->
  <!-- ======================================================== -->
  <div id="regModal" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-4">
    <div class="bg-surface border border-line-strong rounded-2xl w-full max-w-md max-h-[90vh] flex flex-col shadow-2xl overflow-hidden">
      
      <!-- Top Modal Header (Sticky) -->
      <div class="p-4 pb-3 border-b border-line bg-surface flex-shrink-0">
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-sm bg-brand"></span>
            <div>
              <span class="font-mono-tech text-[10px] text-brand font-bold tracking-widest uppercase block leading-none">SISTEM MANDIRI SIMAKSI</span>
              <h3 class="font-display font-bold text-base text-white leading-tight">RESERVASI & PERMIT PENDAKIAN</h3>
            </div>
          </div>
          <button type="button" onclick="App.modal.close('regModal')" class="tap-active w-8 h-8 rounded-full bg-raised border border-line-strong text-stone-400 hover:text-white flex items-center justify-center font-bold text-sm">
            ✕
          </button>
        </div>

        <!-- Step Indicator & Progress Bar -->
        <div class="space-y-1.5">
          <div class="flex items-center justify-between font-mono-tech text-[10px]">
            <span id="wizardStepLabel" class="text-white font-bold tracking-wider">LANGKAH 1/4: JADWAL & LOGISTIK</span>
            <span id="wizardStepPercent" class="text-brand font-bold">25%</span>
          </div>
          
          <!-- Progress Bar Track -->
          <div class="w-full h-1.5 bg-hover rounded-full overflow-hidden">
            <div id="wizardProgressBar" class="h-full bg-brand transition-all duration-300 rounded-full" style="width: 25%;"></div>
          </div>

          <!-- Step Badges (Compact) -->
          <div class="grid grid-cols-4 gap-1 pt-1 font-mono-tech text-[9px] text-center">
            <button type="button" id="stepPill1" onclick="App.wizard.goTo(1)" class="py-1 rounded bg-brand/15 text-brand border border-brand/40 font-bold transition">1. JADWAL</button>
            <button type="button" id="stepPill2" onclick="App.wizard.goTo(2)" class="py-1 rounded bg-raised text-stone-500 border border-line-strong font-medium transition">2. KETUA</button>
            <button type="button" id="stepPill3" onclick="App.wizard.goTo(3)" class="py-1 rounded bg-raised text-stone-500 border border-line-strong font-medium transition">3. ANGGOTA</button>
            <button type="button" id="stepPill4" onclick="App.wizard.goTo(4)" class="py-1 rounded bg-raised text-stone-500 border border-line-strong font-medium transition">4. BAYAR</button>
          </div>
        </div>
      </div>

      <!-- Scrollable Wizard Steps Body -->
      <div class="flex-1 overflow-y-auto p-4 space-y-4 font-mono-tech">
        
        <!-- ============================================== -->
        <!-- STEP 1: JADWAL & LOGISTIK                      -->
        <!-- ============================================== -->
        <div id="wizardStep1" class="space-y-4">
          <div class="p-2.5 rounded-lg bg-raised border border-line-strong text-[11px] text-stone-300 leading-relaxed font-mono-tech">
            <span class="text-brand font-bold block mb-0.5">// PANDUAN JADWAL & KUOTA:</span>
            Pilih tanggal mulai pendakian. Kuota harian dibatasi max {{ $profile['default_daily_quota'] ?? 150 }} orang per hari sesuai daya dukung konservasi.
          </div>

          <!-- Kategori Pendakian: Tektok vs Camp -->
          <div>
            <label class="block text-[10px] text-stone-400 uppercase mb-1">KATEGORI PENDAKIAN</label>
            <div class="grid grid-cols-2 gap-2">
              <label id="catTektokLabel" class="relative flex flex-col p-2.5 rounded-xl border border-line-strong bg-raised cursor-pointer hover:border-cyan-400/50 transition">
                <div class="flex items-center gap-1.5 font-bold text-xs text-cyan-400">
                  <input type="radio" name="wzCategory" value="tektok" class="accent-cyan-400" onchange="App.wizard.setCategory('tektok')">
                  <span>TEKTOK (1 HARI)</span>
                </div>
                <span class="text-[9px] text-stone-400 mt-1 leading-tight">Buka Gerbang: 03:00 - 07:00 WIB. Turun hari yang sama (tanpa tenda).</span>
              </label>

              <label id="catCampLabel" class="relative flex flex-col p-2.5 rounded-xl border border-emerald-500/50 bg-emerald-500/10 cursor-pointer hover:border-emerald-400 transition">
                <div class="flex items-center gap-1.5 font-bold text-xs text-emerald-400">
                  <input type="radio" name="wzCategory" value="camp" checked class="accent-emerald-400" onchange="App.wizard.setCategory('camp')">
                  <span>CAMP (BERMALAM)</span>
                </div>
                <span class="text-[9px] text-stone-400 mt-1 leading-tight">Buka Gerbang: Mulai 07:00 WIB. Bebas camp di Pos 2 atau Pos 3.</span>
              </label>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-2.5">
            <div>
              <label class="block text-[10px] text-stone-400 uppercase mb-1">Tanggal Naik</label>
              <input type="date" id="wzHikingDate" min="{{ date('Y-m-d') }}" onchange="App.wizard.onDateChange()" class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-brand">
            </div>
            <div>
              <label id="wzReturnDateLabel" class="block text-[10px] text-stone-400 uppercase mb-1">Estimasi Turun</label>
              <input type="date" id="wzReturnDate" min="{{ date('Y-m-d') }}" class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-brand">
            </div>
          </div>

          <!-- Pilihan Rencana Camp (Hanya Muncul jika Kategori Camp) -->
          <div id="wzCampsiteBox" class="p-2.5 rounded-lg bg-surface border border-line-strong space-y-1.5">
            <div class="flex items-center justify-between">
              <span class="text-[10px] text-stone-300 font-bold uppercase">RENCANA AREA CAMP RESMI</span>
              <span class="text-[9px] text-emerald-400">*Bebas fleksibel di pos</span>
            </div>
            <div class="grid grid-cols-2 gap-2 text-xs text-stone-200">
              <label class="p-2 rounded bg-raised border border-line-strong flex items-center gap-2 cursor-pointer hover:border-emerald-500/40">
                <input type="radio" name="wzCampsite" value="POS_2" checked class="accent-emerald-400">
                <span>Pos 2 (Mata Air)</span>
              </label>
              <label class="p-2 rounded bg-raised border border-line-strong flex items-center gap-2 cursor-pointer hover:border-emerald-500/40">
                <input type="radio" name="wzCampsite" value="POS_3" class="accent-emerald-400">
                <span>Pos 3 (Camp Atas)</span>
              </label>
            </div>
            <p class="text-[9px] text-stone-400 leading-tight">Pos 2 dan Pos 3 adalah tempat camp resmi yang dijaga ranger. Di luar kedua pos ini dilarang mendirikan tenda.</p>
          </div>

          <!-- Stepper Rombongan -->
          <div>
            <div class="flex justify-between items-center mb-1">
              <label class="text-[10px] text-stone-400 uppercase">Jumlah Pendaki</label>
              <span id="wzClimberCountLabel" class="text-xs font-bold text-brand">4 ORANG</span>
            </div>
            <div class="flex items-center bg-raised border border-line-strong rounded-lg overflow-hidden">
              <button type="button" onclick="App.wizard.adjustClimbers(-1)" class="tap-active w-12 py-2.5 bg-hover text-white hover:bg-[#282E38] font-bold text-base transition flex items-center justify-center">-</button>
              <div id="wzClimberCountBox" class="flex-1 text-center font-bold text-sm text-white py-2">4</div>
              <button type="button" onclick="App.wizard.adjustClimbers(1)" class="tap-active w-12 py-2.5 bg-hover text-white hover:bg-[#282E38] font-bold text-base transition flex items-center justify-center">+</button>
            </div>
            <p class="text-[9px] text-stone-500 mt-1">*Mendukung pendakian solo ({{ $profile['min_climbers_per_group'] ?? 1 }} orang) hingga rombongan (maks. {{ $profile['max_climbers_per_group'] ?? 15 }} orang).</p>
          </div>

          <!-- Layanan Tambahan Logistik (Multi-Unit Stepper) -->
          <div class="space-y-2 pt-1 border-t border-line">
            <span class="block text-[10px] text-stone-400 uppercase">PILIHAN LOGISTIK BASECAMP</span>
            
            <!-- Service 1: Shuttle Ojek Stepper -->
            <div class="p-3 rounded-lg bg-raised border border-line-strong space-y-2">
              <div class="flex items-center justify-between">
                <div>
                  <span class="block text-xs font-bold text-stone-200">Shuttle Ojek Trail</span>
                  <span class="text-[9px] text-stone-400">{{ $tariffs['ojek_route_name'] ?? 'Basecamp → Pos 1' }} (Rp {{ number_format($tariffs['ojek_fee_per_trip'], 0, ',', '.') }} / armada)</span>
                </div>
                <span id="wzOjekTotalLabel" class="text-xs font-bold text-brand">Rp 0</span>
              </div>

              <div class="flex items-center justify-between pt-1">
                <span class="text-[9px] text-stone-500">1 Motor = 1 Pendaki / Tas</span>
                
                <div class="flex items-center bg-raised border border-line-strong rounded-lg overflow-hidden">
                  <button type="button" onclick="App.wizard.adjustOjek(-1)" class="tap-active w-8 py-1.5 bg-hover text-white hover:bg-[#282E38] font-bold text-sm transition flex items-center justify-center">-</button>
                  <div id="wzOjekCountBox" class="w-10 text-center font-bold text-xs text-white py-1">0</div>
                  <button type="button" onclick="App.wizard.adjustOjek(1)" class="tap-active w-8 py-1.5 bg-hover text-white hover:bg-[#282E38] font-bold text-sm transition flex items-center justify-center">+</button>
                </div>
              </div>
            </div>

            <!-- Service 2: Parkir Kendaraan Pribadi (Checkbox Toggle & Multi-Vehicle Stepper) -->
            <div class="p-3 rounded-lg bg-raised border border-line-strong space-y-2">
              <label class="flex items-center justify-between cursor-pointer select-none">
                <div class="flex items-center gap-2.5">
                  <input type="checkbox" id="wzHasVehicle" onchange="App.wizard.toggleVehicle(this.checked)" class="w-4 h-4 rounded bg-raised border-line-strong text-brand focus:ring-0 focus:ring-offset-0 cursor-pointer">
                  <div>
                    <span class="block text-xs font-bold text-stone-200">Membawa Kendaraan Pribadi</span>
                    <span class="text-[9px] font-mono-tech text-stone-400">Titip parkir resmi basecamp 24 jam</span>
                  </div>
                </div>
                <span id="wzParkingTotalLabel" class="font-mono-tech text-xs font-bold text-cyan-400">Rp 0</span>
              </label>

              <!-- Pilihan Kendaraan & Stepper (Muncul jika diceklis) -->
              <div id="wzVehicleOptions" class="hidden pt-2 border-t border-line space-y-2">
                <!-- Motor -->
                <div class="flex items-center justify-between bg-surface p-2 rounded-lg border border-line">
                  <div>
                    <span class="block text-[11px] font-bold text-stone-200">Sepeda Motor</span>
                    <span class="text-[9px] font-mono-tech text-stone-400">Rp {{ number_format($tariffs['parking_motor_fee'], 0, ',', '.') }} / unit</span>
                  </div>
                  <div class="flex items-center bg-raised border border-line-strong rounded-lg overflow-hidden">
                    <button type="button" onclick="App.wizard.adjustMotorcycle(-1)" class="tap-active w-7 py-1 bg-hover text-white hover:bg-[#282E38] font-bold text-xs flex items-center justify-center">-</button>
                    <div id="wzMotorCountBox" class="w-8 text-center font-bold text-xs text-white py-1">0</div>
                    <button type="button" onclick="App.wizard.adjustMotorcycle(1)" class="tap-active w-7 py-1 bg-hover text-white hover:bg-[#282E38] font-bold text-xs flex items-center justify-center">+</button>
                  </div>
                </div>

                <!-- Mobil -->
                <div class="flex items-center justify-between bg-surface p-2 rounded-lg border border-line">
                  <div>
                    <span class="block text-[11px] font-bold text-stone-200">Mobil Pribadi</span>
                    <span class="text-[9px] font-mono-tech text-stone-400">Rp {{ number_format($tariffs['parking_car_fee'], 0, ',', '.') }} / unit</span>
                  </div>
                  <div class="flex items-center bg-raised border border-line-strong rounded-lg overflow-hidden">
                    <button type="button" onclick="App.wizard.adjustCar(-1)" class="tap-active w-7 py-1 bg-hover text-white hover:bg-[#282E38] font-bold text-xs flex items-center justify-center">-</button>
                    <div id="wzCarCountBox" class="w-8 text-center font-bold text-xs text-white py-1">0</div>
                    <button type="button" onclick="App.wizard.adjustCar(1)" class="tap-active w-7 py-1 bg-hover text-white hover:bg-[#282E38] font-bold text-xs flex items-center justify-center">+</button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Cost preview card -->
          <div class="p-3 rounded-lg bg-base border border-line space-y-1 text-xs">
            <div class="flex justify-between text-stone-400 text-[11px]">
              <span id="wzTxtSimaksiSub">SIMAKSI (4 Org @ Rp 25.000):</span>
              <span id="wzValSimaksiSub" class="text-stone-200">Rp 100.000</span>
            </div>
            <div class="flex justify-between text-stone-400 text-[11px]">
              <span id="wzTxtOjekSub">Shuttle Ojek (0 Armada):</span>
              <span id="wzValOjekSub" class="text-stone-200">Rp 0</span>
            </div>
            <div class="flex justify-between text-stone-400 text-[11px] hidden" id="wzRowParkirSub">
              <span id="wzTxtParkirSub">Parkir Kendaraan:</span>
              <span id="wzValParkirSub" class="text-cyan-400 font-bold">Rp 0</span>
            </div>
            <div class="pt-2 border-t border-line flex justify-between items-center text-sm">
              <span class="font-bold text-white">ESTIMASI TOTAL:</span>
              <span id="wzValTotalSub" class="font-bold text-brand text-base">Rp 100.000</span>
            </div>
          </div>

          <button type="button" onclick="App.wizard.validateStep1()" class="tap-active w-full py-3 bg-brand hover:bg-brand-hover text-black font-bold text-xs uppercase tracking-wider rounded-lg transition flex items-center justify-center gap-2 shadow-lg shadow-[#FF5722]/20">
            <span>LANJUT: DATA KETUA [2/4]</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
          </button>
        </div>

        <!-- ============================================== -->
        <!-- STEP 2: IDENTITAS KETUA & KONTAK DARURAT       -->
        <!-- ============================================== -->
        <div id="wizardStep2" class="hidden space-y-3.5">
          <div class="p-2.5 rounded-lg bg-raised border border-line-strong text-[11px] text-stone-300 leading-relaxed font-mono-tech">
            <span class="text-brand font-bold block mb-0.5">// IDENTITAS KETUA PENANGGUNG JAWAB:</span>
            Wajib mengisi NIK KTP 16-digit asli dan nomor WhatsApp aktif untuk verifikasi di pos gerbang basecamp.
          </div>

          <div>
            <label class="block text-[10px] text-stone-400 uppercase mb-1">Nama Lengkap Ketua (Sesuai KTP) <span class="text-brand">*</span></label>
            <input type="text" id="wzLeaderName" placeholder="Contoh: Arif Rahmansyah" class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-brand">
          </div>

          <div class="grid grid-cols-2 gap-2.5">
            <div>
              <label class="block text-[10px] text-stone-400 uppercase mb-1">NIK KTP (16 Digit) <span class="text-brand">*</span></label>
              <input type="text" id="wzLeaderNik" maxlength="16" placeholder="320501xxxxxxxxxx" class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-brand">
            </div>
            <div>
              <label class="block text-[10px] text-stone-400 uppercase mb-1">No. WhatsApp Aktif <span class="text-brand">*</span></label>
              <input type="tel" id="wzLeaderPhone" placeholder="0812xxxxxxxx" class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-brand">
            </div>
          </div>

          <div>
            <label class="block text-[10px] text-stone-400 uppercase mb-1">
              Alamat Lengkap Domisili (Sesuai KTP) <span class="text-brand">*</span>
            </label>
            <textarea id="wzLeaderAddress" rows="2" placeholder="Contoh: Jl. Merdeka No. 45, RT 02/RW 05, Kel. Tarogong, Kec. Tarogong Kidul, Kab. Garut, Jawa Barat" class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-brand resize-none font-mono-tech leading-relaxed"></textarea>
            <input type="hidden" id="wzLeaderCity" value="">
          </div>

          <!-- Upload Foto / Scan KTP Asli Ketua (dengan Auto Image Compression) -->
          <div class="space-y-1.5">
            <div class="flex justify-between items-center">
              <label class="block text-[10px] text-stone-400 uppercase">
                Foto / Scan KTP Asli Ketua / Solo <span class="text-brand">* (Wajib Verifikasi)</span>
              </label>
              <span id="wzKtpCompressBadge" class="text-[9px] font-mono-tech text-emerald-400 hidden">// KOMPRESI OTOMATIS AKTIF</span>
            </div>

            <div onclick="document.getElementById('wzLeaderKtpInput').click()" class="border-2 border-dashed border-line-strong hover:border-brand bg-surface hover:bg-[#151820] rounded-xl p-3 text-center cursor-pointer transition">
              <input type="file" id="wzLeaderKtpInput" accept="image/*,application/pdf" class="hidden" onchange="App.wizard.handleKtpSelect(event)">
              
              <div id="wzKtpPlaceholder" class="space-y-1 py-1">
                <svg class="w-6 h-6 mx-auto text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                <div class="text-xs font-bold text-stone-200">Ambil Foto / Unggah KTP Asli</div>
                <div class="text-[9px] text-stone-400">JPG, PNG, WebP • Auto-compress ringan & hemat kuota data</div>
              </div>

              <!-- Preview State -->
              <div id="wzKtpPreviewBox" class="hidden flex items-center justify-between gap-3 text-left">
                <div class="flex items-center gap-2.5 truncate">
                  <img id="wzKtpPreviewImg" src="" alt="KTP Ketua" class="w-12 h-8 object-cover rounded border border-line-strong">
                  <div class="truncate">
                    <span id="wzKtpFileName" class="text-xs font-bold text-white block truncate">ktp_ketua.jpg</span>
                    <span id="wzKtpFileSize" class="text-[10px] text-emerald-400 font-mono-tech block">210 KB (Hemat 92%)</span>
                  </div>
                </div>
                <button type="button" onclick="event.stopPropagation(); App.wizard.removeKtpFile()" class="tap-active p-1.5 rounded bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 border border-rose-500/30 text-[10px] font-bold">
                  HAPUS
                </button>
              </div>
            </div>
            
            <p class="text-[9px] text-stone-400 leading-tight">Foto KTP digunakan verifikator untuk mencocokkan identitas sebelum izin pendakian disetujui.</p>

            <!-- Disclaimer Perlindungan Data Pribadi (PDP) KTP Auto-Purge -->
            <div class="p-2.5 rounded-lg bg-[#0E1116] border border-emerald-500/30 flex items-start gap-2 text-[10px] leading-relaxed">
              <svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
              <div class="space-y-0.5">
                <span class="text-emerald-400 font-bold uppercase block text-[9px] font-mono-tech tracking-wider">// DISCLAIMER PRIVASI & PERLINDUNGAN DATA (PDP):</span>
                <p class="text-stone-300 text-[10px] leading-relaxed">
                  Foto KTP hanya digunakan sementara untuk validasi identitas & izin SIMAKSI. Sesuai prinsip Perlindungan Data Pribadi (PDP), <strong class="text-emerald-300">seluruh berkas foto fisik KTP akan dihapus otomatis dan dimusnahkan secara permanen dari server</strong> segera setelah pendakian selesai (saat check-out di gerbang pos).
                </p>
              </div>
            </div>
          </div>

          <!-- Riwayat Penyakit Bawaan & Kondisi Medis Ketua -->
          <div>
            <div class="flex justify-between items-center mb-1">
              <label class="block text-[10px] text-stone-400 uppercase">
                Riwayat Penyakit Bawaan / Alergi Ketua
              </label>
              <span class="text-[9px] text-stone-500 font-mono-tech">// SOP MEDIS SAR</span>
            </div>
            <input type="text" id="wzLeaderMedical" value="Tidak Ada" placeholder="Contoh: Tidak Ada / Asma, Alergi Dingin, Jantung, dsb." class="w-full bg-raised border border-line-strong rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-brand transition">
            <span class="text-[9px] text-stone-500 block mt-1">Wajib diisi jujur untuk kesiapsiagaan tim evakuasi SAR SAMSARA di jalur gunung.</span>
          </div>

          <!-- Emergency Contact Subcard -->
          <div class="p-3 bg-[#101216] border border-[#242A36] rounded-xl space-y-2.5">
            <div class="flex items-center gap-1.5 text-brand">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
              <span class="text-[10px] font-bold uppercase tracking-wider">KONTAK DARURAT (KELUARGA DI RUMAH)</span>
            </div>
            <p class="text-[9px] text-stone-400 leading-tight">Wajib mencantumkan nomor keluarga yang TIDAK ikut mendaki untuk SOP darurat SAR SAMSARA.</p>
            
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block text-[9px] text-stone-400 uppercase mb-1">Nama Kontak Darurat <span class="text-brand">*</span></label>
                <input type="text" id="wzEmergName" placeholder="Contoh: Siti Fatimah" class="w-full bg-raised border border-line-strong rounded-lg px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-brand">
              </div>
              <div>
                <label class="block text-[9px] text-stone-400 uppercase mb-1">Hubungan</label>
                <select id="wzEmergRel" class="w-full bg-raised border border-line-strong rounded-lg px-2 py-1.5 text-xs text-white focus:outline-none focus:border-brand">
                  <option value="Orang Tua">Orang Tua</option>
                  <option value="Pasangan">Pasangan (Suami/Istri)</option>
                  <option value="Saudara Kandung">Saudara Kandung</option>
                  <option value="Keluarga / Kerabat">Keluarga / Kerabat</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-[9px] text-stone-400 uppercase mb-1">No. Telp / WhatsApp Darurat <span class="text-brand">*</span></label>
              <input type="tel" id="wzEmergPhone" placeholder="08xxxxxxxxxx" class="w-full bg-raised border border-line-strong rounded-lg px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-brand">
            </div>
          </div>

          <div class="grid grid-cols-2 gap-2.5 pt-1">
            <button type="button" onclick="App.wizard.goTo(1)" class="tap-active py-2.5 bg-raised border border-line-strong text-stone-300 font-bold text-xs uppercase tracking-wider rounded-lg transition hover:bg-hover">
              ← KEMBALI
            </button>
            <button type="button" onclick="App.wizard.validateStep2()" class="tap-active py-2.5 bg-brand text-black font-bold text-xs uppercase tracking-wider rounded-lg transition hover:bg-brand-hover flex items-center justify-center gap-1.5">
              <span>MANIFEST [3/4]</span>
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- STEP 3: MANIFEST ANGGOTA ROMBONGAN             -->
        <!-- ============================================== -->
        <div id="wizardStep3" class="hidden space-y-3.5">
          <div class="p-2.5 rounded-lg bg-raised border border-line-strong text-[11px] text-stone-300 leading-relaxed font-mono-tech">
            <span class="text-brand font-bold block mb-0.5">// MANIFEST KESELAMATAN ANGGOTA:</span>
            Seluruh nama & NIK anggota diinput untuk registrasi manifest resmi & jaminan asuransi pendakian.
          </div>

          <!-- Leader Card (Member #1 Summary) -->
          <div class="p-2.5 rounded-lg bg-[#111317] border border-line-strong flex items-center justify-between text-xs">
            <div>
              <div class="flex items-center gap-2">
                <span class="px-1.5 py-0.5 rounded text-[9px] bg-brand/15 text-brand border border-brand/30 font-bold" id="wzLeaderRoleTag">ANGGOTA #1</span>
                <span class="font-bold text-white" id="wzLeaderSummaryName">Ketua Tim</span>
              </div>
              <span class="text-[10px] text-stone-400 block mt-0.5" id="wzLeaderSummaryNik">NIK: -</span>
            </div>
            <span class="text-[9px] text-emerald-400 font-bold font-mono-tech" id="wzLeaderRoleBadge">[KETUA TIM]</span>
          </div>

          <!-- Dynamic Member Cards Container (#2 s.d. #N) -->
          <div id="wzMembersList" class="space-y-3">
            <!-- Dynamically generated via App.wizard.renderMembers() -->
          </div>

          <div class="grid grid-cols-2 gap-2.5 pt-1">
            <button type="button" onclick="App.wizard.goTo(2)" class="tap-active py-2.5 bg-raised border border-line-strong text-stone-300 font-bold text-xs uppercase tracking-wider rounded-lg transition hover:bg-hover">
              ← KEMBALI
            </button>
            <button type="button" onclick="App.wizard.validateStep3()" class="tap-active py-2.5 bg-brand text-black font-bold text-xs uppercase tracking-wider rounded-lg transition hover:bg-brand-hover flex items-center justify-center gap-1.5">
              <span>PEMBAYARAN [4/4]</span>
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- STEP 4: PEMBAYARAN & UPLOAD BUKTI BAYAR        -->
        <!-- ============================================== -->
        <div id="wizardStep4" class="hidden space-y-4">
          <!-- Total Payment Callout with Unique Code -->
          <div class="p-3 rounded-xl bg-base border border-line-strong space-y-1.5">
            <div class="flex items-center justify-between text-xs text-stone-400">
              <span>Subtotal SIMAKSI & Logistik:</span>
              <span id="wzSubtotalPay" class="text-stone-300 font-bold">Rp 100.000</span>
            </div>
            <div id="wzParkingPayRow" class="hidden flex items-center justify-between text-xs text-cyan-400">
              <span>Biaya Parkir Kendaraan:</span>
              <span id="wzParkingPay" class="font-bold font-mono-tech">Rp 0</span>
            </div>
            <div id="wzUniqueCodeRow" class="flex items-center justify-between text-xs text-amber-400">
              <span class="flex items-center gap-1.5">
                <span>Kode Unik Verifikasi:</span>
                <span class="text-[8px] px-1.5 py-0.2 rounded bg-amber-500/20 text-amber-300 font-bold uppercase">Manual Admin</span>
              </span>
              <span id="wzUniqueCodePay" class="font-bold font-mono-tech">+Rp 348</span>
            </div>
            <div class="pt-2 border-t border-line flex items-center justify-between">
              <div>
                <span id="wzTotalLabel" class="text-[10px] text-stone-400 block uppercase">TOTAL TRANSFER</span>
                <span id="wzFinalTotal" class="text-xl font-bold text-brand">Rp 100.348</span>
              </div>
              <button type="button" id="btnCopyNominal" onclick="App.wizard.copyTransferAmount()" class="tap-active px-2.5 py-1.5 rounded bg-hover hover:bg-hover border border-line-strong text-[10px] text-stone-300 hover:text-white flex items-center gap-1.5 font-bold">
                <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <span>SALIN NOMINAL</span>
              </button>
            </div>
            <p id="wzUniqueNote" class="text-[9px] text-amber-300/90 pt-1 leading-tight border-t border-line/60">
              *Penting: Mohon transfer <strong>tepat hingga 3 digit terakhir</strong> agar admin/kasir dapat memverifikasi mutasi rekening bank secara instan.
            </p>
          </div>

          <!-- Payment Method Options -->
          <div class="space-y-2">
            <label class="block text-[10px] text-stone-400 uppercase">PILIH METODE PEMBAYARAN</label>
            
            <div class="grid grid-cols-3 gap-2">
              <label class="payment-method-card p-2 rounded-lg bg-raised border border-brand cursor-pointer text-center block transition" id="lblMethodQris">
                <input type="radio" name="wzPaymentMethod" value="qris" checked onchange="App.wizard.onMethodChange('qris')" class="hidden">
                <span class="text-xs font-bold text-white block">QRIS</span>
                <span class="text-[8px] text-stone-400 block mt-0.5">E-Wallet & Bank</span>
              </label>

              <label class="payment-method-card p-2 rounded-lg bg-raised border border-line-strong cursor-pointer text-center block transition" id="lblMethodTransfer">
                <input type="radio" name="wzPaymentMethod" value="transfer" onchange="App.wizard.onMethodChange('transfer')" class="hidden">
                <span class="text-xs font-bold text-stone-300 block">TRANSFER</span>
                <span class="text-[8px] text-stone-400 block mt-0.5">BCA / Mandiri</span>
              </label>

              <label class="payment-method-card p-2 rounded-lg bg-raised border border-line-strong cursor-pointer text-center block transition" id="lblMethodCash">
                <input type="radio" name="wzPaymentMethod" value="cash_pos" onchange="App.wizard.onMethodChange('cash_pos')" class="hidden">
                <span class="text-xs font-bold text-stone-300 block">TUNAI</span>
                <span class="text-[8px] text-stone-400 block mt-0.5">Kasir Loket BC</span>
              </label>
            </div>
          </div>

          <!-- Method Details Panel: QRIS -->
          <div id="methodDetailQris" class="p-3.5 bg-[#101216] border border-line-strong rounded-xl space-y-3 text-center">
            <span class="text-[10px] font-mono-tech tracking-wider text-stone-400 block uppercase">SCAN QRIS RESMI {{ strtoupper($profile['basecamp_name'] ?? 'SAMSARA GARUT') }}:</span>
            
            <!-- Visual QRIS Container (Bounded & Responsive, Anti-Overflow) -->
            <div class="max-w-[240px] mx-auto bg-white p-2.5 rounded-2xl shadow-xl border border-stone-700 flex items-center justify-center overflow-hidden">
              @if(!empty($profile['payment_qris_image']))
                <a href="{{ asset('storage/' . $profile['payment_qris_image']) }}" target="_blank" title="Klik untuk memperbesar / simpan QRIS" class="flex items-center justify-center w-full max-h-[280px]">
                  <img src="{{ asset('storage/' . $profile['payment_qris_image']) }}" alt="QRIS Resmi {{ $profile['payment_qris_name'] ?? 'Basecamp' }}" class="max-w-full max-h-[260px] w-auto h-auto object-contain rounded cursor-pointer transition hover:scale-[1.02]">
                </a>
              @else
                <div class="w-44 h-44 flex items-center justify-center p-1">
                  <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=00020101021226600016ID.SAMSARA.GARUT01189360099900000000015204581253033605802ID5918SAMSARA+CIKURAY6005GARUT6304A1B2" alt="QRIS Samsara" class="w-full h-full object-contain" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'text-black text-[10px] font-bold\'>[QRIS RESMI]<br>NMID: {{ $profile['payment_qris_nmid'] ?? 'ID102026110099' }}</div>'">
                </div>
              @endif
            </div>

            <div class="text-[9px] font-mono-tech text-stone-400 space-y-1">
              <span class="text-stone-200 block font-bold">NMID: {{ $profile['payment_qris_nmid'] ?? 'ID102026110099' }} — {{ $profile['payment_qris_name'] ?? 'PT SAMSARA ADVENTURE GARUT' }}</span>
              <span class="text-stone-400 block text-[8.5px]">Mendukung: GoPay, OVO, Dana, ShopeePay, BCA, Livin, Mandiri, dll.</span>
              @if(!empty($profile['payment_qris_image']))
                <div class="pt-1">
                  <a href="{{ asset('storage/' . $profile['payment_qris_image']) }}" target="_blank" download class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-raised hover:bg-hover text-brand hover:text-white border border-brand/40 rounded-lg text-[9px] font-bold transition shadow-sm">
                    <span>[+] BUKA / SIMPAN QRIS FULL RESOLUSI</span>
                  </a>
                </div>
              @endif
            </div>
          </div>

          <!-- Method Details Panel: Transfer Bank -->
          <div id="methodDetailTransfer" class="hidden p-3 bg-[#101216] border border-line-strong rounded-xl space-y-2 text-xs">
            <span class="text-[10px] text-stone-400 block uppercase">REKENING RESMI {{ strtoupper($profile['expedition_name'] ?? 'EXPEDITION') }}:</span>
            <div class="p-2.5 bg-raised border border-line-strong rounded-lg flex items-center justify-between">
              <div>
                <span class="text-[9px] text-stone-400 block">{{ $profile['payment_bank_name'] ?? 'BANK CENTRAL ASIA (BCA)' }}</span>
                <span class="text-sm font-bold text-white tracking-wider">{{ $profile['payment_bank_account'] ?? '810-0982-111' }}</span>
                <span class="text-[9px] text-stone-400 block">a.n. {{ $profile['payment_bank_holder'] ?? 'PT SAMSARA ADVENTURE GARUT' }}</span>
              </div>
              <button type="button" onclick="App.wizard.copyAccount('{{ $profile['payment_bank_account_raw'] ?? '8100982111' }}', '{{ $profile['payment_bank_name'] ?? 'BCA' }}')" class="tap-active px-2.5 py-1.5 bg-hover text-brand hover:bg-[#282E38] border border-brand/30 rounded text-[10px] font-bold">
                SALIN REK
              </button>
            </div>
          </div>

          <!-- Method Details Panel: Cash Loket -->
          <div id="methodDetailCash" class="hidden p-3 bg-[#101216] border border-line-strong rounded-xl space-y-1 text-xs text-stone-300">
            <span class="text-[10px] text-brand font-bold block uppercase">PEMBAYARAN TUNAI DI BASECAMP:</span>
            <p class="text-[11px] leading-relaxed text-stone-400">
              Kode registrasi Anda akan berstatus <strong>MENUNGGU VERIFIKASI (PENDING)</strong>. Pembayaran dilakukan langsung tunai di meja Kasir POS Basecamp sebelum verifikasi fisik di Gate Check-in.
            </p>
          </div>

          <!-- File Upload: Bukti Pembayaran -->
          <div id="wzProofUploadContainer" class="space-y-1.5">
            <div class="flex items-center justify-between">
              <label class="block text-[10px] text-stone-400 uppercase">
                UPLOAD BUKTI BAYAR <span id="wzProofRequiredBadge" class="text-brand">* (WAJIB UNTUK QRIS/TRANSFER)</span>
              </label>
              <span class="text-[9px] text-stone-500">Maks. 5 MB</span>
            </div>

            <!-- Upload Area -->
            <div id="wzDropzone" onclick="document.getElementById('wzPaymentProofInput').click()" class="border-2 border-dashed border-line-strong hover:border-brand rounded-xl p-3 text-center cursor-pointer transition bg-raised/50">
              <input type="file" id="wzPaymentProofInput" accept="image/*,application/pdf" class="hidden" onchange="App.wizard.handleFileSelect(event)">
              
              <div id="wzUploadPlaceholder" class="space-y-1 py-1">
                <svg class="w-6 h-6 mx-auto text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span class="text-xs text-stone-300 block font-bold">Pilih Foto Struk / Screenshot Bukti</span>
                <span class="text-[9px] text-stone-500 block">Format: JPG, PNG, WEBP, atau PDF</span>
              </div>

              <!-- Preview State (Initially Hidden) -->
              <div id="wzUploadPreviewBox" class="hidden flex items-center gap-3 text-left">
                <img id="wzUploadPreviewImg" src="" alt="Bukti Transfer" class="w-12 h-12 object-cover rounded border border-line-strong">
                <div class="flex-1 min-w-0">
                  <span id="wzUploadFileName" class="text-xs font-bold text-white block truncate">bukti_transfer.jpg</span>
                  <span id="wzUploadFileSize" class="text-[10px] text-stone-400 block">240 KB</span>
                </div>
                <button type="button" onclick="event.stopPropagation(); App.wizard.removeFile()" class="tap-active p-1.5 rounded bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 border border-rose-500/30 text-[10px] font-bold">
                  HAPUS
                </button>
              </div>
            </div>
          </div>

          <!-- SOP Checkbox Agreement -->
          <label class="flex items-start gap-2.5 p-2.5 rounded-lg bg-[#14161C] border border-line-strong cursor-pointer">
            <input type="checkbox" id="wzAgreeCheck" checked class="mt-0.5 w-4 h-4 rounded bg-hover border-stone-600 text-brand focus:ring-0">
            <span class="text-[10px] text-stone-300 leading-relaxed">
              Saya menyatakan seluruh data identitas rombongan adalah sah & rombongan siap mematuhi SOP Keselamatan SAMSARA
              @if(!empty($tariffs['trash_deposit_enabled']))
                serta menyiapkan <strong>Deposit Sampah Rp {{ number_format($tariffs['trash_deposit_amount'] ?? 50000, 0, ',', '.') }}</strong> di loket (dikembalikan saat turun membawa kantong sampah).
              @else
                serta menjaga kebersihan jalur pendakian (bebas biaya deposit sampah).
              @endif
            </span>
          </label>

          <!-- Error Alert Banner inside modal -->
          <div id="wzErrorAlert" class="hidden p-3 rounded-lg bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs"></div>

          <div class="grid grid-cols-2 gap-2.5 pt-1">
            <button type="button" onclick="App.wizard.goTo(3)" class="tap-active py-2.5 bg-raised border border-line-strong text-stone-300 font-bold text-xs uppercase tracking-wider rounded-lg transition hover:bg-hover">
              ← KEMBALI
            </button>
            <button type="button" id="btnSubmitRegistration" onclick="App.wizard.submit()" class="tap-active py-2.5 bg-brand text-black font-bold text-xs uppercase tracking-wider rounded-lg transition hover:bg-brand-hover flex items-center justify-center gap-1.5 shadow-lg shadow-[#FF5722]/20">
              <span id="txtSubmitBtn">KIRIM & BUAT TIKET</span>
              <svg id="spinnerSubmitBtn" class="hidden animate-spin w-4 h-4 text-black" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
            </button>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- STEP 5: HASIL TIKET RESMI TERBIT               -->
        <!-- ============================================== -->
        <div id="wizardStep5" class="hidden text-center py-2 space-y-4">
          <div class="w-14 h-14 mx-auto rounded-full bg-emerald-500/20 border-2 border-emerald-500 flex items-center justify-center text-emerald-400">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
          </div>
          
          <div>
            <span id="wzResultStatusBadge" class="text-[10px] text-amber-400 font-bold px-2.5 py-0.5 bg-amber-500/10 border border-amber-500/30 rounded uppercase tracking-wider font-mono-tech">
              MENUNGGU VERIFIKASI KASIR
            </span>
            <h4 class="font-display font-black text-xl text-white mt-1.5">RESERVASI BERHASIL DIREKAM</h4>
            <p class="text-xs text-stone-400 mt-1 max-w-xs mx-auto">
              Simpan kode registrasi SIMAKSI berikut untuk ditunjukkan ke petugas loket Basecamp SAMSARA.
            </p>
          </div>

          <!-- Ticket Box -->
          <div class="p-3.5 bg-raised border border-line-strong rounded-xl text-left font-mono-tech space-y-2 text-xs">
            <div class="flex justify-between items-center border-b border-line-strong pb-2">
              <span class="text-stone-400 text-[10px] uppercase">KODE SIMAKSI:</span>
              <div class="flex items-center gap-1.5">
                <span id="wzResultCode" class="text-base font-bold text-brand tracking-wider">SMK-202611-005</span>
                <button type="button" onclick="App.wizard.copyResultCode()" class="tap-active px-2 py-0.5 rounded bg-hover text-stone-300 text-[9px] hover:text-white">
                  SALIN
                </button>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-2 text-[11px] text-stone-300 py-1">
              <div>
                <span class="text-stone-500 text-[9px] block">KETUA TIM:</span>
                <span id="wzResultLeader" class="font-bold text-white">-</span>
              </div>
              <div>
                <span class="text-stone-500 text-[9px] block">ROMBONGAN:</span>
                <span id="wzResultClimbers" class="font-bold text-white">-</span>
              </div>
              <div>
                <span class="text-stone-500 text-[9px] block">TANGGAL PENDAKIAN:</span>
                <span id="wzResultDate" class="text-white">-</span>
              </div>
              <div>
                <span class="text-stone-500 text-[9px] block">TOTAL BIAYA:</span>
                <span id="wzResultTotal" class="text-emerald-400 font-bold">-</span>
              </div>
            </div>

            <div class="p-2 bg-base rounded-lg border border-line flex items-center justify-between text-[10px]">
              <span class="text-stone-400">METODE PEMBAYARAN:</span>
              <span id="wzResultMethod" class="text-stone-200 font-bold uppercase">QRIS (BUKTI TERUNGGAH)</span>
            </div>
          </div>

          <!-- WhatsApp Admin Notification Banner -->
          <div class="p-2.5 rounded-lg bg-[#25D366]/10 border border-[#25D366]/30 text-left space-y-1 font-mono-tech">
            <div class="flex items-center gap-1.5 text-[#25D366] text-xs font-bold">
              <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z"/></svg>
              <span>WHATSAPP ADMIN TERBUKA OTOMATIS</span>
            </div>
            <p class="text-[10px] text-stone-300 leading-tight">
              Rincian permohonan & total transfer Anda telah disiapkan. Jika WhatsApp belum terbuka otomatis, silakan klik tombol di bawah:
            </p>
          </div>

          <div class="p-2.5 rounded-lg bg-surface border border-line-strong text-[10px] text-stone-400 text-left space-y-1 font-mono-tech">
            <span class="text-stone-300 font-bold block">// PANDUAN SAAT TIBA DI BASECAMP:</span>
            <ul class="list-disc pl-4 space-y-0.5">
              <li>Tunjukkan kode registrasi ini ke <strong>Kasir Loket</strong> Basecamp.</li>
              <li>Bawa fisik <strong>KTP Asli</strong> ketua tim untuk verifikasi manifest.</li>
              @if(!empty($tariffs['trash_deposit_enabled']))
                <li>Serahkan <strong>Deposit Sampah Rp {{ number_format($tariffs['trash_deposit_amount'] ?? 50000, 0, ',', '.') }}</strong> (dikembalikan saat turun membawa kantong sampah).</li>
              @else
                <li>Rombongan wajib menjaga kebersihan jalur (bebas biaya uang jaminan sampah).</li>
              @endif
            </ul>
          </div>

          <!-- Direct Ticket Access & Download Buttons -->
          <div class="grid grid-cols-2 gap-2 pt-1 font-mono-tech">
            <a id="btnViewDigitalTicket" href="#" target="_blank" class="tap-active py-2.5 px-3 bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs uppercase tracking-wider rounded-lg transition flex items-center justify-center gap-1.5 shadow-lg shadow-cyan-600/20">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              <span>SURAT JALAN DIGITAL</span>
            </a>

            <a id="btnDownloadTicketPdf" href="#" target="_blank" class="tap-active py-2.5 px-3 bg-brand hover:bg-brand-hover text-black font-bold text-xs uppercase tracking-wider rounded-lg transition flex items-center justify-center gap-1.5 shadow-lg shadow-[#FF5722]/20">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
              <span>UNDUH PDF RESMI</span>
            </a>
          </div>

          <!-- Action Buttons -->
          <div class="space-y-2 pt-1 font-mono-tech">
            <a id="btnOpenWhatsApp" href="#" target="_blank" class="tap-active w-full py-3 bg-[#25D366] hover:bg-[#20BD5A] text-black font-bold text-xs uppercase tracking-wider rounded-lg transition flex items-center justify-center gap-2 shadow-lg shadow-[#25D366]/20">
              <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z"/></svg>
              <span>BUKA CHAT WHATSAPP ADMIN</span>
            </a>

            <button type="button" id="btnCopyWhatsAppText" onclick="App.wizard.copyWhatsAppText()" class="tap-active w-full py-2.5 bg-[#181C24] hover:bg-[#202632] border border-line-strong hover:border-[#25D366] text-stone-200 hover:text-white font-bold text-xs uppercase tracking-wider rounded-lg transition flex items-center justify-center gap-2">
              <svg class="w-4 h-4 text-[#25D366]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
              <span id="txtCopyWhatsApp">SALIN FORMAT PESAN WA</span>
            </button>

            <button type="button" onclick="App.modal.close('regModal')" class="tap-active w-full py-2 bg-surface border border-line-strong text-stone-400 hover:text-white font-bold text-xs uppercase tracking-wider rounded-lg transition">
              TUTUP & LIHAT BERANDA
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- ======================================================== -->
  <!-- MODAL 2: DEDICATED TICKET TRACKING & PROOF UPLOAD        -->
  <!-- ======================================================== -->
  <div id="ticketModal" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-4">
    <div class="bg-surface border border-line-strong rounded-2xl w-full max-w-md max-h-[90vh] flex flex-col shadow-2xl overflow-hidden font-mono-tech">
      
      <!-- Top Modal Header (Sticky) -->
      <div class="p-4 pb-3 border-b border-line bg-surface flex-shrink-0">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-sm bg-brand"></div>
            <div>
              <span class="text-[10px] text-brand font-bold tracking-widest uppercase block leading-none">// SISTEM PELACAK MANDIRI</span>
              <h3 class="font-display font-bold text-base text-white leading-tight">STATUS SIMAKSI & TIKET</h3>
            </div>
          </div>
          <button type="button" onclick="App.modal.close('ticketModal')" class="tap-active w-8 h-8 rounded-full bg-raised border border-line-strong text-stone-400 hover:text-white flex items-center justify-center font-bold text-sm">
            ✕
          </button>
        </div>
        <p class="text-[10px] text-stone-400 mt-1 leading-tight">
          Lacak verifikasi permit dan unggah bukti transfer susulan jika sempat terclose saat bayar.
        </p>
      </div>

      <!-- Scrollable Tracking Body -->
      <div class="flex-1 overflow-y-auto p-4 space-y-4">
        
        <!-- Search Input Bar -->
        <div class="space-y-1.5">
          <label for="searchTicketInput" class="block text-[10px] text-stone-400 uppercase">
            CARI KODE REGISTRASI ATAU NO. WHATSAPP KETUA
          </label>
          <div class="flex gap-2">
            <input type="text" id="searchTicketInput" value="SMK-202611-001" placeholder="Contoh: SMK-202611-001 atau 0812..." onkeydown="if(event.key==='Enter') App.ticket.search()" class="flex-1 bg-raised border border-line-strong rounded-lg px-3 py-2 text-xs text-white uppercase focus:outline-none focus:border-brand transition">
            <button type="button" id="btnSearchTicket" onclick="App.ticket.search()" class="px-4 py-2 bg-brand hover:bg-brand-hover text-black font-bold text-xs rounded-lg tap-active transition flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
              <span>LACAK</span>
            </button>
          </div>

          <!-- Quick Chip Suggestions -->
          <div id="ticketQuickChips" class="flex flex-wrap gap-1.5 pt-1 text-[9px]">
            <span class="text-stone-500 py-0.5">Contoh Cepat:</span>
            <button type="button" onclick="App.ticket.quickSearch('SMK-202611-001')" class="px-2 py-0.5 rounded bg-raised border border-line-strong text-stone-300 hover:border-brand">
              SMK-202611-001
            </button>
            <button type="button" onclick="App.ticket.quickSearch('SMK-202611-002')" class="px-2 py-0.5 rounded bg-raised border border-line-strong text-stone-300 hover:border-brand">
              SMK-202611-002
            </button>
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
  <div id="imagePreviewModal" class="hidden fixed inset-0 z-50 bg-black/90 backdrop-blur-md flex items-center justify-center p-3 sm:p-4" onclick="if(event.target === this) App.modal.close('imagePreviewModal')">
    <div class="bg-surface border border-line-strong rounded-2xl w-full max-w-xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden font-mono-tech">
      <div class="p-4 border-b border-line flex items-center justify-between shrink-0">
        <div class="flex items-center gap-2">
          <div class="w-2.5 h-2.5 rounded-sm bg-brand"></div>
          <h3 id="imagePreviewTitle" class="font-display font-bold text-sm text-white">PREVIEW DOKUMEN</h3>
        </div>
        <button type="button" onclick="App.modal.close('imagePreviewModal')" class="w-8 h-8 rounded-full bg-raised border border-line-strong text-stone-400 hover:text-white flex items-center justify-center font-bold text-sm transition">
          ✕
        </button>
      </div>
      <div class="flex-1 overflow-auto p-4 flex items-center justify-center bg-base">
        <img id="imagePreviewImg" src="" alt="Preview" class="max-h-[62vh] w-auto max-w-full object-contain rounded-lg border border-line">
      </div>
      <div class="p-3 border-t border-line flex items-center justify-between text-xs">
        <a id="imagePreviewRawLink" href="#" target="_blank" class="text-cyan-400 hover:underline text-[11px] flex items-center gap-1 font-bold">
          <span>Buka Resolusi Penuh ↗</span>
        </a>
        <button type="button" onclick="App.modal.close('imagePreviewModal')" class="px-4 py-1.5 rounded-lg bg-hover hover:bg-hover text-stone-200 font-bold text-[11px]">
          Tutup [ESC]
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
  <script src="{{ asset('assets/js/currency-input.js') }}"></script>
  <script src="{{ asset('assets/js/data.js') }}?v={{ filemtime(public_path('assets/js/data.js')) }}"></script>
  <script src="{{ asset('assets/js/app.js') }}?v={{ filemtime(public_path('assets/js/app.js')) }}"></script>
</body>
</html>
