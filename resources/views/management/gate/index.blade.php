@extends('management.layout')

@section('title', 'Gate Check-in & Manifest')
@section('page_title', 'Pos Gerbang Basecamp (Check-in & Check-out)')
@section('page_subtitle', 'Verifikasi Kehadiran Manifest Rombongan, Kantong Sampah, & Jaminan Bebas Sampah')

@section('content')
<div class="space-y-6">

  <!-- ======================================================== -->
  <!-- 1. SEARCH / SCAN TICKET                                  -->
  <!-- ======================================================== -->
  <div class="bg-[#121418] border border-[#1E2229] rounded-xl p-5 space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-[#1E2229]">
      <div class="flex items-center gap-2">
        <div class="w-2.5 h-2.5 rounded-sm bg-[#FF5722]"></div>
        <h3 class="font-mono-tech font-bold text-xs uppercase tracking-wider text-white">
          PEMINDAI / PENCARIAN TIKET SIMAKSI GERBANG
        </h3>
      </div>
      <div class="flex items-center gap-3">
        <a href="{{ route('management.ojek.index') }}" class="px-2 py-0.5 rounded bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 font-mono-tech text-[10px] font-bold hover:bg-emerald-500/20 transition flex items-center gap-1.5">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
          <span>PANGKALAN OJEK: {{ $standbyDrivers->count() }} STANDBY</span>
        </a>
        <span class="font-mono-tech text-[10px] text-stone-400">VERIFIKASI FISIK BASECAMP</span>
      </div>
    </div>

    <form id="gateSearchForm" action="{{ route('management.gate.index') }}" method="GET" class="flex flex-col sm:flex-row gap-2">
      <input type="text" id="gateTicketInput" name="code" value="{{ $code ?? '' }}" placeholder="Ketik Kode SIMAKSI (Contoh: SMK-202611-001)..." autofocus required class="flex-1 bg-[#181B22] border border-[#2A303C] rounded-lg px-4 py-2.5 text-xs font-mono-tech text-white uppercase focus:outline-none focus:border-[#FF5722]">
      <div class="flex gap-2">
        <button type="submit" class="px-5 py-2.5 bg-[#FF5722] hover:bg-[#F4511E] text-black font-mono-tech font-bold text-xs uppercase rounded-lg transition shrink-0">
          VERIFIKASI TIKET
        </button>
        <button type="button" onclick="toggleCameraScanner()" id="btnToggleScanner" class="px-4 py-2.5 bg-[#16191F] hover:bg-[#202530] text-emerald-400 border border-emerald-500/40 font-mono-tech font-bold text-xs uppercase rounded-lg transition flex items-center gap-1.5 shrink-0">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
          <span>SCAN QR HP</span>
        </button>
      </div>
    </form>

    <!-- Interactive HTML5 Camera QR Scanner Box -->
    <div id="scannerWrapper" class="hidden p-4 bg-[#0E1014] border border-emerald-500/40 rounded-xl space-y-3 font-mono-tech">
      <div class="flex items-center justify-between text-xs">
        <span class="text-emerald-400 font-bold uppercase flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
          <span>KAMERA PEMINDAI BARCODE / QR CODE TIKET PENDAKI</span>
        </span>
        <button type="button" onclick="stopCameraScanner()" class="px-2.5 py-1 rounded bg-[#16191F] border border-stone-700 text-stone-300 hover:text-white text-[10px]">
          [✕ TUTUP KAMERA]
        </button>
      </div>
      <p class="text-[10px] text-stone-400">
        Arahkan kamera ke layar ponsel pendaki yang menampilkan Barcode / QR Code SIMAKSI. Sistem akan memverifikasi otomatis dalam 1 detik.
      </p>
      <div class="max-w-md mx-auto overflow-hidden rounded-xl border-2 border-dashed border-emerald-500/40 bg-black">
        <div id="qrReaderViewport" class="w-full"></div>
      </div>
    </div>

    <!-- Quick Ready List -->
    @if(!$selectedRegistration && $readyForCheckIn->isNotEmpty())
      <div class="pt-2 border-t border-[#1E2229] font-mono-tech text-xs">
        <span class="text-[10px] text-stone-400 uppercase block mb-1.5">ROMBONGAN SIAP CHECK-IN HARI INI:</span>
        <div class="flex flex-wrap gap-2">
          @foreach($readyForCheckIn as $r)
            <a href="{{ route('management.gate.index', ['code' => $r->registration_code]) }}" class="px-2.5 py-1.5 rounded bg-[#16191F] hover:bg-[#202530] border border-[#262B35] text-stone-300 hover:text-white flex items-center gap-1.5 transition">
              <span class="text-[#FF5722] font-bold">{{ $r->registration_code }}</span>
              <span>— {{ $r->leader_name }} ({{ $r->total_climbers }} Org)</span>
            </a>
          @endforeach
        </div>
      </div>
    @endif
  </div>

  <!-- ======================================================== -->
  <!-- 2. SELECTED REGISTRATION VERIFICATION PANE               -->
  <!-- ======================================================== -->
  @if($selectedRegistration)
    <div class="bg-[#121418] border border-[#262B35] rounded-xl p-5 space-y-5 font-mono-tech text-xs">
      
      <div class="flex items-center justify-between pb-3 border-b border-[#1E2229]">
        <div>
          <span class="text-[#FF5722] font-bold text-base">{{ $selectedRegistration->registration_code }}</span>
          <h3 class="font-display font-bold text-lg text-white">Rombongan: {{ $selectedRegistration->leader_name }}</h3>
          <span class="text-[10px] text-stone-400">Total: {{ $selectedRegistration->total_climbers }} Orang • WhatsApp: {{ $selectedRegistration->leader_phone }}</span>
          @if($selectedRegistration->has_vehicle || $selectedRegistration->parking_fee > 0)
            <div class="mt-1 flex items-center gap-1.5 px-2 py-0.5 rounded bg-cyan-500/10 border border-cyan-500/30 text-cyan-300 text-[10px] w-fit">
              <span>Parkir Basecamp: <strong>{{ $selectedRegistration->motorcycle_count }} Motor</strong>, <strong>{{ $selectedRegistration->car_count }} Mobil</strong></span>
            </div>
          @endif
        </div>
        <div class="flex flex-col items-end gap-2">
          <span class="px-3 py-1 rounded text-xs font-bold border {{ $selectedRegistration->status_badge['bg'] }}">
            {{ $selectedRegistration->status_badge['label'] }}
          </span>

          <div class="flex items-center gap-2">
            <a href="{{ route('management.gate.print_climber_pass', $selectedRegistration->id) }}" target="_blank" class="px-2.5 py-1 rounded bg-amber-500/20 hover:bg-amber-500/30 border border-amber-500/50 text-amber-300 text-[10px] font-bold flex items-center gap-1 transition">
              <span>[PRINT] Surat Jalan Saku</span>
            </a>

            <a href="{{ route('management.gate.ticket_pdf', $selectedRegistration->id) }}" class="px-2.5 py-1 rounded bg-emerald-500/20 hover:bg-emerald-500/30 border border-emerald-500/50 text-emerald-300 text-[10px] font-bold flex items-center gap-1 transition">
              <span>[PDF] Unduh E-Tiket</span>
            </a>

            @if($selectedRegistration->ojek_count > 0)
              <a href="{{ route('management.gate.print_ojek_voucher', $selectedRegistration->id) }}" target="_blank" class="px-2.5 py-1 rounded bg-amber-500/20 hover:bg-amber-500/30 border border-amber-500/50 text-amber-300 text-[10px] font-bold flex items-center gap-1 transition">
                <span>[PRINT] Voucher Ojek ({{ $selectedRegistration->ojek_count }})</span>
              </a>
            @endif

            @if($selectedRegistration->has_vehicle || $selectedRegistration->parking_fee > 0 || $selectedRegistration->motorcycle_count > 0 || $selectedRegistration->car_count > 0)
              <a href="{{ route('management.gate.print_parking_pass', $selectedRegistration->id) }}" target="_blank" class="px-2.5 py-1 rounded bg-cyan-500/20 hover:bg-cyan-500/30 border border-cyan-500/50 text-cyan-300 text-[10px] font-bold flex items-center gap-1 transition">
                <span>[PRINT] Pass Parkir</span>
              </a>
            @endif
          </div>
        </div>
      </div>

      <!-- Action 1: If ready for Check-In -->
      @if(in_array($selectedRegistration->status, ['paid', 'approved']))
        <!-- FOTO REGU KEBERANGKATAN VERIFICATION -->
        <div class="p-4 bg-[#16191F] border border-[#20242C] rounded-lg space-y-3 font-mono-tech">
          <div class="flex items-center justify-between pb-2 border-b border-[#20242C]">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full {{ $selectedRegistration->group_photo_path ? 'bg-emerald-400' : 'bg-amber-400 animate-pulse' }}"></span>
              <span class="font-bold text-xs uppercase text-white">
                FOTO REGU KEBERANGKATAN (IDENTIFIKASI VISUAL SAR):
              </span>
            </div>
            <div>
              @if($selectedRegistration->group_photo_path)
                <span class="px-2 py-0.5 rounded bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-[10px] font-bold">
                  ✓ TERSEDIA & TERVALIDASI WATERMARK
                </span>
              @else
                <span class="px-2 py-0.5 rounded bg-amber-500/10 border border-amber-500/30 text-amber-400 text-[10px] font-bold">
                  [!] BELUM DIUNGGAH OLEH PENDAKI
                </span>
              @endif
            </div>
          </div>

          @if($selectedRegistration->group_photo_path)
            <div class="flex flex-col sm:flex-row gap-4 items-start">
              <div class="relative w-full sm:w-64 rounded-lg overflow-hidden border border-[#2A303C] bg-black shrink-0">
                <a href="{{ asset('storage/' . $selectedRegistration->group_photo_path) }}" target="_blank" title="Klik untuk memperbesar">
                  <img src="{{ asset('storage/' . $selectedRegistration->group_photo_path) }}" alt="Foto Regu {{ $selectedRegistration->registration_code }}" class="w-full h-40 object-cover hover:opacity-90 transition cursor-zoom-in">
                </a>
                <div class="absolute bottom-1 right-1 bg-black/80 px-1.5 py-0.5 rounded text-[8px] text-stone-300">
                  Klik perbesar
                </div>
              </div>
              <div class="flex-1 space-y-2 text-xs">
                <p class="text-stone-300">
                  Foto telah dibubuhi watermark koordinat identitas resmi sistem SAMSARA. Cocokkan wajah dan pakaian seluruh <strong>{{ $selectedRegistration->total_climbers }} orang pendaki</strong> yang berdiri di depan pos gerbang.
                </p>
                <div class="text-[10px] text-stone-400">
                  Waktu Unggah: <span class="text-white">{{ $selectedRegistration->group_photo_uploaded_at ? $selectedRegistration->group_photo_uploaded_at->translatedFormat('d M Y, H:i') : '-' }} WIB</span>
                </div>
                <div class="flex items-center gap-2 pt-1">
                  <a href="{{ asset('storage/' . $selectedRegistration->group_photo_path) }}" target="_blank" class="px-2.5 py-1 rounded bg-[#181B22] border border-[#2A303C] text-stone-200 hover:text-white text-[10px] flex items-center gap-1">
                    <span>Buka Foto Resolusi Penuh</span>
                  </a>
                </div>
              </div>
            </div>
          @else
            <div class="p-3 bg-amber-500/10 border border-amber-500/30 rounded-lg text-xs text-amber-300 flex items-start gap-2.5">
              <span class="text-amber-400 font-bold shrink-0">[!]</span>
              <div>
                <span class="font-bold block">Rombongan belum mengunggah foto regu bersama di portal e-tiket.</span>
                <span class="text-[10px] text-stone-400 block mt-0.5">
                  Minta ketua rombongan membuka link tiket mereka di ponsel untuk memotret regu di pelataran basecamp. Jika dalam kondisi khusus/darurat sinyal, petugas tetap dapat memproses check-in.
                </span>
              </div>
            </div>
          @endif
        </div>

        <form action="{{ route('management.gate.checkin', $selectedRegistration->id) }}" method="POST" class="space-y-4">
          @csrf
          
          <div class="p-4 bg-[#16191F] border border-[#20242C] rounded-lg space-y-3">
            <span class="text-[#FF5722] font-bold text-[11px] uppercase block">
              1. VERIFIKASI KEHADIRAN FISIK ANGGOTA DI POS BASECAMP (CHECKLIST MANIFEST):
            </span>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
              @foreach($selectedRegistration->members as $m)
                <label class="flex items-start gap-2.5 p-2.5 rounded bg-[#121418] border border-[#262B35] cursor-pointer hover:border-[#FF5722]">
                  <input type="checkbox" name="checked_members[]" value="{{ $m->id }}" checked class="w-4 h-4 rounded bg-black text-[#FF5722] mt-0.5">
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                      <span class="font-bold text-white truncate">{{ $m->full_name }}</span>
                      @if($m->is_leader)
                        <span class="text-[9px] font-bold text-[#FF5722] bg-[#FF5722]/10 border border-[#FF5722]/30 px-1.5 py-0.2 rounded">KETUA</span>
                      @endif
                    </div>
                    <span class="text-[10px] text-stone-400 block">NIK: {{ $m->nik ?? '-' }} • {{ $m->gender === 'L' ? 'Pria' : 'Wanita' }}</span>
                    @if($m->medical_conditions && $m->medical_conditions !== 'Tidak Ada')
                      <span class="inline-block mt-1 text-[9px] font-bold text-amber-400 bg-amber-500/10 border border-amber-500/30 px-1.5 py-0.5 rounded">
                        MEDIS: {{ $m->medical_conditions }}
                      </span>
                    @endif
                  </div>
                </label>
              @endforeach
            </div>
          </div>

          <div class="p-3 bg-[#16191F] border border-[#20242C] rounded-lg flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <span class="text-[10px] text-stone-400 uppercase">2. UANG JAMINAN SAMPAH:</span>
            <div class="flex items-baseline gap-3">
              @if($selectedRegistration->trash_deposit_amount > 0)
                <span class="text-white font-bold text-sm">Rp {{ number_format($selectedRegistration->trash_deposit_amount, 0, ',', '.') }} (DITERIMA POS)</span>
                <span class="text-emerald-400 text-[10px] font-bold">DIKEMBALIKAN SAAT TURUN</span>
              @else
                <span class="text-stone-300 font-bold text-sm">NONAKTIF / BEBAS DEPOSIT</span>
                <span class="text-stone-400 text-[10px]">KEBIJAKAN BASECAMP</span>
              @endif
            </div>
          </div>

          <!-- 4. Penugasan Ojek Standby Pangkalan (Jika Rombongan Memesan Ojek) -->
          @if($selectedRegistration->ojek_count > 0)
            <div class="p-4 bg-[#16191F] border border-amber-500/30 rounded-xl space-y-3 font-mono-tech">
              <div class="flex items-center justify-between pb-2 border-b border-[#20242C]">
                <div class="flex items-center gap-2">
                  <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                  <span class="text-amber-400 font-bold text-xs uppercase">
                    4. PENUGASAN OJEK STANDBY PANGKALAN ({{ $selectedRegistration->ojek_count }} PENUMPANG):
                  </span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="px-2 py-0.5 rounded bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-[10px] font-bold">
                    Pangkalan: {{ $standbyDrivers->count() }} Motor Standby
                  </span>
                </div>
              </div>

              <p class="text-[10px] text-stone-400">
                Sistem otomatis memilihkan driver urutan terdepan (FIFO) dari antrean pangkalan basecamp. Tiket kupon yang dicetak akan langsung mencantumkan Nama Pengemudi dan Plat Nomor resmi.
              </p>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                @for($k = 0; $k < $selectedRegistration->ojek_count; $k++)
                  @php
                    $suggestedDriver = $standbyDrivers->get($k);
                  @endphp
                  <div class="p-2.5 bg-[#121418] border border-[#262B35] rounded-lg space-y-1.5">
                    <div class="flex items-center justify-between text-[11px]">
                      <span class="font-bold text-white">Penumpang #{{ $k + 1 }}</span>
                      @if($suggestedDriver)
                        <span class="text-[9px] text-amber-400 font-bold">Giliran #{{ $k + 1 }} Antrean</span>
                      @endif
                    </div>
                    <select name="ojek_driver_ids[]" class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg p-2 text-xs text-white">
                      <option value="">-- Lewati / Antre Manual di Pangkalan --</option>
                      @foreach($standbyDrivers as $idx => $driver)
                        <option value="{{ $driver->id }}" {{ ($suggestedDriver && $suggestedDriver->id === $driver->id) ? 'selected' : '' }}>
                          #{{ $idx + 1 }} {{ $driver->registration_number }} — {{ $driver->name }} ({{ $driver->plate_number }})
                        </option>
                      @endforeach
                    </select>
                  </div>
                @endfor
              </div>
            </div>
          @endif

          <button type="submit" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider rounded-lg transition shadow-lg shadow-emerald-600/20 flex items-center justify-center gap-2">
            @if($selectedRegistration->ojek_count > 0)
              <span>✓ KONFIRMASI CHECK-IN & TUGASKAN OJEK JALAN</span>
            @else
              <span>✓ KONFIRMASI CHECK-IN & IZINKAN MEMASUKI JALUR PENDAKIAN</span>
            @endif
          </button>
        </form>

      <!-- Action 2: If currently Checked-In (Process Check-Out) -->
      @elseif($selectedRegistration->status === 'checked_in')
        <form action="{{ route('management.gate.checkout', $selectedRegistration->id) }}" method="POST" class="p-4 bg-[#16191F] border border-cyan-500/30 rounded-xl space-y-4">
          @csrf
          <div class="flex items-center justify-between">
            <span class="text-cyan-400 font-bold uppercase">PROSES CHECK-OUT (ROMBONGAN SELESAI TURUN GUNUNG)</span>
            @if($selectedRegistration->trash_bag_code)
              <span class="text-stone-400 text-[10px]">Kantong Sampah: <strong>{{ $selectedRegistration->trash_bag_code }}</strong></span>
            @endif
          </div>

          <!-- PDP Privacy Protection Notice -->
          <div class="p-2.5 bg-black/40 border border-emerald-500/30 rounded-lg flex items-center gap-2 text-xs">
            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <span class="text-emerald-400 font-mono-tech text-[11px]">
              <strong>PROTEKSI DATA PRIBADI (PDP):</strong> Berkas foto fisik KTP ketua rombongan akan dihapus otomatis secara permanen dari server begitu proses check-out kepulangan ini selesai.
            </span>
          </div>

          <label class="flex items-start gap-3 p-3 bg-[#121418] border border-[#262B35] rounded-lg cursor-pointer">
            <input type="checkbox" name="trash_verified" value="1" checked class="w-5 h-5 rounded bg-black text-emerald-500 mt-0.5">
            <div>
              <span class="font-bold text-white block">Sampah Dibawa Turun & Sesuai Berat Standar</span>
              <span class="text-[10px] text-stone-400 block mt-0.5">
                @if($selectedRegistration->trash_deposit_amount > 0)
                  Uang jaminan sampah Rp {{ number_format($selectedRegistration->trash_deposit_amount, 0, ',', '.') }} wajib dikembalikan tunai kepada ketua tim.
                @else
                  Rombongan tertib membawa turun sampah (kebijakan bebas uang jaminan).
                @endif
              </span>
            </div>
          </label>

          <div>
            <label class="block text-[10px] text-stone-400 uppercase mb-1">Catatan Ranger / Petugas Pos:</label>
            <input type="text" name="notes" placeholder="Contoh: Kondisi seluruh anggota sehat dan lengkap..." class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg p-2 text-white">
          </div>

          <button type="submit" onclick="return confirm('Selesaikan proses check-out rombongan ini?')" class="w-full py-3 bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs uppercase rounded-lg transition">
            SELESAIKAN CHECK-OUT KEPULANGAN
          </button>
        </form>

      @else
        <div class="p-4 bg-amber-500/10 border border-amber-500/30 rounded-lg text-amber-300 text-center">
          Permohonan ini berstatus <strong>{{ $selectedRegistration->status }}</strong> dan belum dapat di-check-in di gerbang. Harap selesaikan verifikasi / pembayaran terlebih dahulu.
        </div>
      @endif

    </div>
  @endif

  <!-- ======================================================== -->
  <!-- 2.5 RADAR PERGERAKAN JALUR & DISPATCH RADIO HT          -->
  <!-- ======================================================== -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 font-mono-tech">
    
    <!-- LIVE TELEMETRI PERGERAKAN JALUR -->
    <div class="lg:col-span-2 bg-[#121418] border border-[#1E2229] rounded-xl p-5 space-y-4">
      <div class="flex items-center justify-between pb-3 border-b border-[#1E2229]">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-pulse"></span>
          <h3 class="font-bold text-xs uppercase tracking-wider text-white">
            RADAR JALUR & STATUS SEKTOR POS (LIVE SAR TELEMETRY)
          </h3>
        </div>
        <span class="text-[10px] text-stone-400">POS 1 • POS 2 • POS 3</span>
      </div>

      <!-- Quick Metrics Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center text-xs">
        <div class="p-2.5 rounded-lg bg-[#16191F] border border-[#222731]">
          <span class="text-[9px] text-stone-400 uppercase block">TOTAL DI TREK</span>
          <span class="font-bold text-base text-white">{{ $radarData['counts']['total_active_climbers'] ?? 0 }}</span>
          <span class="text-[8px] text-stone-500 block">Pendaki</span>
        </div>

        <div class="p-2.5 rounded-lg bg-cyan-950/30 border border-cyan-500/30">
          <span class="text-[9px] text-cyan-400 uppercase block">TEKTOK (03-07)</span>
          <span class="font-bold text-base text-cyan-300">{{ $radarData['counts']['tektok_count'] ?? 0 }}</span>
          <span class="text-[8px] text-cyan-400/70 block">Maks Turun 19:00</span>
        </div>

        <div class="p-2.5 rounded-lg bg-emerald-950/30 border border-emerald-500/30">
          <span class="text-[9px] text-emerald-400 uppercase block">CAMP AKTIF</span>
          <span class="font-bold text-base text-emerald-300">{{ $radarData['counts']['camp_count'] ?? 0 }}</span>
          <span class="text-[8px] text-emerald-400/70 block">{{ ($radarData['counts']['pos2_tents'] ?? 0) + ($radarData['counts']['pos3_tents'] ?? 0) }} Tenda Resmi</span>
        </div>

        <div class="p-2.5 rounded-lg {{ ($radarData['counts']['overdue_count'] ?? 0) > 0 ? 'bg-rose-950/50 border-rose-500 text-rose-300 animate-pulse' : 'bg-[#16191F] border-[#222731] text-stone-400' }}">
          <span class="text-[9px] uppercase block">OVERDUE / SAR</span>
          <span class="font-bold text-base {{ ($radarData['counts']['overdue_count'] ?? 0) > 0 ? 'text-rose-400' : 'text-stone-300' }}">{{ $radarData['counts']['overdue_count'] ?? 0 }}</span>
          <span class="text-[8px] block">Perlu Pantauan</span>
        </div>
      </div>

      <!-- Sektor Pos Live Status -->
      <div class="space-y-2 pt-1 text-xs">
        <div class="p-2 rounded-lg bg-[#16191F] border border-[#222731] flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="text-stone-400 font-bold">POS 1</span>
            <span class="text-stone-300">Transit Ojek (1.780M)</span>
          </div>
          <span class="text-stone-400 font-bold">{{ count($radarData['by_pos']['POS_1'] ?? []) }} Rombongan Terakhir Terdata</span>
        </div>

        <div class="p-2 rounded-lg bg-emerald-950/20 border border-emerald-500/30 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="text-emerald-400 font-bold">POS 2</span>
            <span class="text-emerald-200">Mata Air & Camp Resmi 1</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-purple-400 font-bold">{{ $radarData['counts']['pos2_tents'] ?? 0 }} Tenda [CAMP]</span>
            <span class="text-stone-400">({{ count($radarData['by_pos']['POS_2'] ?? []) }} Rombongan)</span>
          </div>
        </div>

        <div class="p-2 rounded-lg bg-purple-950/20 border border-purple-500/30 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="text-purple-400 font-bold">POS 3</span>
            <span class="text-purple-200">Camp Resmi 2 & Puncak</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-purple-400 font-bold">{{ $radarData['counts']['pos3_tents'] ?? 0 }} Tenda [CAMP]</span>
            <span class="text-stone-400">({{ count($radarData['by_pos']['POS_3'] ?? []) }} Rombongan)</span>
          </div>
        </div>
      </div>
    </div>

    <!-- PANEL DISPATCH CEPAT RADIO HT (FALLBACK SAAT HP RANGER MATI) -->
    <div class="bg-[#121418] border border-amber-500/30 rounded-xl p-5 space-y-3 font-mono-tech text-xs">
      <div class="flex items-center justify-between pb-2 border-b border-[#1E2229]">
        <div class="flex items-center gap-2">
          <span class="text-amber-400 font-bold">[HT]</span>
          <h3 class="font-bold text-xs uppercase tracking-wider text-amber-300">
            DISPATCH RADIO HT (FALLBACK)
          </h3>
        </div>
        <span class="text-[9px] px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/30 font-bold">
          SAR: 149.250 MHz
        </span>
      </div>

      <p class="text-[10px] text-stone-400 leading-tight">
        Gunakan saat baterai smartphone ranger di Pos 1, 2, atau 3 mati/drop. Input laporan radio HT langsung ke sistem:
      </p>

      <form method="POST" action="{{ route('management.gate.ht_dispatch') }}" class="space-y-2.5">
        @csrf
        <div>
          <label class="block text-[9px] text-stone-400 uppercase mb-1">KODE SIMAKSI / 3 DIGIT TERAKHIR:</label>
          <input type="text" name="registration_code" required placeholder="Contoh: 001 atau SMK-202611-001" class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg px-3 py-2 text-xs text-white uppercase focus:outline-none focus:border-amber-400">
        </div>

        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="block text-[9px] text-stone-400 uppercase mb-1">POS PELAPOR:</label>
            <select name="pos_code" required class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg px-2.5 py-2 text-xs text-white focus:outline-none focus:border-amber-400">
              <option value="POS_1">POS 1 (Ojek)</option>
              <option value="POS_2" selected>POS 2 (Mata Air)</option>
              <option value="POS_3">POS 3 (Camp)</option>
              <option value="PUNCAK">PUNCAK</option>
            </select>
          </div>

          <div>
            <label class="block text-[9px] text-stone-400 uppercase mb-1">ARAH PERGERAKAN:</label>
            <select name="direction" required class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg px-2.5 py-2 text-xs text-white focus:outline-none focus:border-amber-400">
              <option value="ascending">Naik [↑]</option>
              <option value="descending">Turun [↓]</option>
              <option value="camping">Buka Tenda [CAMP]</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-[9px] text-stone-400 uppercase mb-1">CATATAN PESAN HT (OPSIONAL):</label>
          <input type="text" name="notes" placeholder="Misal: Laporan HT Pos 2, HP ranger drop..." class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg px-3 py-1.5 text-xs text-white focus:outline-none focus:border-amber-400">
        </div>

        <button type="submit" class="w-full py-2.5 bg-amber-600 hover:bg-amber-500 text-black font-bold text-xs uppercase rounded-lg transition shadow-md shadow-amber-600/20 flex items-center justify-center gap-1.5">
          <span>CATAT LOG DISPATCH HT</span>
        </button>
      </form>
    </div>

  </div>

  <!-- ======================================================== -->
  <!-- 3. ACTIVE CLIMBERS CURRENTLY ON MOUNTAIN                 -->
  <!-- ======================================================== -->
  <div class="bg-[#121418] border border-[#1E2229] rounded-xl p-5 space-y-4 font-mono-tech text-xs">
    <div class="flex items-center justify-between pb-3 border-b border-[#1E2229]">
      <div class="flex items-center gap-2">
        <span class="relative flex h-2 w-2">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
        </span>
        <h3 class="font-mono-tech font-bold text-xs uppercase tracking-wider text-white">
          PENDAKI AKTIF DI JALUR {{ strtoupper(\App\Models\AppSetting::get('mountain_name', 'CIKURAY')) }} SAAT INI ({{ $activeOnMountain->sum('total_climbers') }} ORANG)
        </h3>
      </div>
      <span class="text-[10px] text-stone-400">MONITORING SAR LAPANGAN</span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left font-mono-tech text-xs">
        <thead>
          <tr class="text-[10px] text-stone-400 uppercase border-b border-[#1E2229]">
            <th class="pb-2">KODE & KATEGORI</th>
            <th class="pb-2">KETUA ROMBONGAN</th>
            <th class="pb-2">POSISI TERAKHIR & ARAH</th>
            <th class="pb-2">JAM CHECK-IN</th>
            <th class="pb-2">ANGGOTA</th>
            <th class="pb-2">FOTO REGU</th>
            <th class="pb-2 text-right">AKSI</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#1A1E26]">
          @forelse($activeOnMountain as $active)
            <tr class="hover:bg-[#16191F]">
              <td class="py-3">
                <span class="font-bold text-[#FF5722] block">{{ $active->registration_code }}</span>
                @if($active->isTektok())
                  <span class="text-[9px] px-1.5 py-0.2 rounded bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 font-bold inline-block mt-0.5">TEKTOK</span>
                @else
                  <span class="text-[9px] px-1.5 py-0.2 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 font-bold inline-block mt-0.5">CAMP</span>
                @endif
              </td>
              <td class="py-3 text-white">
                <div class="font-bold">{{ $active->leader_name }}</div>
                <div class="text-[10px] text-stone-400">{{ $active->leader_phone }}</div>
              </td>
              <td class="py-3 text-stone-300">
                <div class="flex items-center gap-1.5">
                  <span class="font-bold text-white">{{ $active->last_seen_pos ?: 'Basecamp' }}</span>
                  @if($active->last_seen_direction === 'ascending')
                    <span class="text-[9px] font-bold text-emerald-400 bg-emerald-500/10 px-1.5 py-0.2 rounded">NAIK [↑]</span>
                  @elseif($active->last_seen_direction === 'descending')
                    <span class="text-[9px] font-bold text-amber-400 bg-amber-500/10 px-1.5 py-0.2 rounded">TURUN [↓]</span>
                  @elseif($active->last_seen_direction === 'camping')
                    <span class="text-[9px] font-bold text-purple-400 bg-purple-500/10 px-1.5 py-0.2 rounded">CAMP</span>
                  @endif
                </div>
                <div class="text-[9px] text-stone-400 mt-0.5">
                  {{ $active->last_seen_at ? $active->last_seen_at->format('H:i') . ' WIB (' . $active->last_seen_at->diffForHumans() . ')' : 'Saat gerbang' }}
                </div>
              </td>
              <td class="py-3 text-stone-300">{{ $active->checked_in_at ? $active->checked_in_at->format('d/m H:i') : '-' }} WIB</td>
              <td class="py-3 font-bold text-white">{{ $active->total_climbers }} Orang</td>
              <td class="py-3">
                @if($active->group_photo_path)
                  <a href="{{ asset('storage/' . $active->group_photo_path) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] text-emerald-400 hover:text-emerald-300 font-bold bg-emerald-500/10 border border-emerald-500/30 px-2 py-0.5 rounded transition">
                    <span>[FOTO] Lihat</span>
                  </a>
                @else
                  <span class="text-[10px] text-stone-500 italic">Belum ada</span>
                @endif
              </td>
              <td class="py-3 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <a href="{{ route('management.gate.print_climber_pass', $active->id) }}" target="_blank" title="Cetak Surat Jalan Pendaki (Struk Saku)" class="px-1.5 py-1 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 rounded text-[10px] font-bold">
                    Surat Jalan
                  </a>
                  @if($active->ojek_count > 0)
                    <a href="{{ route('management.gate.print_ojek_voucher', $active->id) }}" target="_blank" title="Cetak Voucher Ojek" class="px-1.5 py-1 bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 border border-amber-500/30 rounded text-[10px] font-bold">
                      Voucher Ojek
                    </a>
                  @endif
                  @if($active->has_vehicle || $active->parking_fee > 0 || $active->motorcycle_count > 0 || $active->car_count > 0)
                    <a href="{{ route('management.gate.print_parking_pass', $active->id) }}" target="_blank" title="Cetak Gate Pass Parkir" class="px-1.5 py-1 bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 rounded text-[10px] font-bold">
                      Pass Parkir
                    </a>
                  @endif
                  <a href="{{ route('management.gate.index', ['code' => $active->registration_code]) }}" class="px-2.5 py-1 bg-cyan-950 hover:bg-cyan-900 text-cyan-300 border border-cyan-500/30 rounded text-[10px] font-bold">
                    Check-out
                  </a>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="py-6 text-center text-stone-400">Tidak ada rombongan yang sedang di jalur saat ini.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- HTML5 QR Code Scanner Library for 1-Second Mobile Check-in -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
  let html5QrCode = null;

  function toggleCameraScanner() {
    const wrapper = document.getElementById('scannerWrapper');
    if (wrapper.classList.contains('hidden')) {
      wrapper.classList.remove('hidden');
      startCameraScanner();
    } else {
      stopCameraScanner();
    }
  }

  function startCameraScanner() {
    if (html5QrCode && html5QrCode.isScanning) {
      return;
    }

    html5QrCode = new Html5Qrcode("qrReaderViewport");
    const config = { fps: 10, qrbox: { width: 250, height: 250 } };

    html5QrCode.start(
      { facingMode: "environment" },
      config,
      (decodedText) => {
        // Success callback: text detected!
        let cleanCode = decodedText.trim();
        // Extract registration code pattern SMK-YYYYMM-XXX if URL or string
        const match = cleanCode.match(/SMK-\d{6}-\d{3}/i) || cleanCode.match(/SMK-[A-Z0-9-]+/i);
        if (match) {
          cleanCode = match[0].toUpperCase();
        }

        document.getElementById('gateTicketInput').value = cleanCode;
        stopCameraScanner();
        document.getElementById('gateSearchForm').submit();
      },
      (errorMessage) => {
        // scanning in progress, do nothing
      }
    ).catch(err => {
      console.warn("Unable to start camera scanner:", err);
      alert("Tidak dapat mengakses kamera. Pastikan izin kamera aktif pada peramban Anda.");
      stopCameraScanner();
    });
  }

  function stopCameraScanner() {
    const wrapper = document.getElementById('scannerWrapper');
    if (wrapper) wrapper.classList.add('hidden');

    if (html5QrCode) {
      html5QrCode.stop().then(() => {
        html5QrCode.clear();
      }).catch(err => {
        console.warn("Camera stop error:", err);
      });
    }
  }
</script>
@endsection
