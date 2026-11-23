@extends('management.layout')

@section('title', 'Pos Ranger Scanner — ' . $activePos)

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

  <!-- Header & Station Selector -->
  <div class="bg-[#121418] border border-[#20242C] rounded-2xl p-4 sm:p-6 shadow-xl">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-[#1E2229] pb-4">
      <div>
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
          <span class="text-[10px] font-mono-tech text-emerald-400 font-bold uppercase tracking-widest">// POSKO LAPANGAN JALUR</span>
        </div>
        <h1 class="text-xl sm:text-2xl font-display font-black text-white mt-1">POS RANGER CHECKPOINT</h1>
        <p class="text-xs text-stone-400 font-mono-tech mt-0.5">Catat arah pergerakan rombongan pendaki untuk telemetri keselamatan real-time di Basecamp.</p>
      </div>

      <div class="flex items-center gap-2 bg-[#0A0B0D] px-3 py-1.5 rounded-xl border border-[#222731] font-mono-tech text-xs">
        <span class="text-stone-500">Petugas:</span>
        <span class="text-white font-bold">{{ auth()->user()->name ?? 'Ranger' }}</span>
      </div>
    </div>

    <!-- Pos Station Tabs (Pos 1 / Pos 2 / Pos 3) -->
    <div class="pt-4">
      <label class="block text-[10px] font-mono-tech text-stone-400 uppercase tracking-wider mb-2">PILIH STASIUN POS PENUGASAN ANDA:</label>
      <div class="grid grid-cols-3 gap-2 font-mono-tech text-xs">
        <a href="{{ route('management.ranger.index', ['pos' => 'POS_1']) }}" class="p-3 rounded-xl border text-center transition font-bold flex flex-col items-center justify-center {{ $activePos === 'POS_1' ? 'bg-[#FF5722] text-black border-[#FF5722] shadow-lg shadow-[#FF5722]/20' : 'bg-[#181B22] text-stone-300 border-[#282D37] hover:border-[#FF5722]/40' }}">
          <span class="text-xs">POS 1</span>
          <span class="text-[9px] opacity-80 font-normal">Transit Ojek (1.780M)</span>
        </a>

        <a href="{{ route('management.ranger.index', ['pos' => 'POS_2']) }}" class="p-3 rounded-xl border text-center transition font-bold flex flex-col items-center justify-center {{ $activePos === 'POS_2' ? 'bg-emerald-500 text-black border-emerald-500 shadow-lg shadow-emerald-500/20' : 'bg-[#181B22] text-stone-300 border-[#282D37] hover:border-emerald-500/40' }}">
          <span class="text-xs">POS 2</span>
          <span class="text-[9px] opacity-80 font-normal">Mata Air & Camp 1</span>
        </a>

        <a href="{{ route('management.ranger.index', ['pos' => 'POS_3']) }}" class="p-3 rounded-xl border text-center transition font-bold flex flex-col items-center justify-center {{ $activePos === 'POS_3' ? 'bg-purple-500 text-white border-purple-500 shadow-lg shadow-purple-500/20' : 'bg-[#181B22] text-stone-300 border-[#282D37] hover:border-purple-500/40' }}">
          <span class="text-xs">POS 3</span>
          <span class="text-[9px] opacity-80 font-normal">Camp Atas & Puncak</span>
        </a>
      </div>
    </div>
  </div>

  <!-- Search / Scan Barcode Bar -->
  <div class="bg-[#121418] border border-[#20242C] rounded-2xl p-4 sm:p-5 shadow-xl font-mono-tech">
    <form method="GET" action="{{ route('management.ranger.index') }}" class="space-y-2">
      <input type="hidden" name="pos" value="{{ $activePos }}">
      <label class="block text-[10px] text-stone-400 uppercase tracking-wider">
        SCAN QR SURAT JALAN / KETIK KODE REGISTRASI:
      </label>
      <div class="flex gap-2">
        <input type="text" name="code" value="{{ $code }}" autofocus placeholder="Contoh: SMK-202611-001 atau 001" class="flex-1 bg-[#0A0B0D] border border-[#2A303C] rounded-xl px-4 py-3 text-sm font-bold text-white uppercase focus:outline-none focus:border-[#FF5722] transition">
        <button type="submit" class="px-5 py-3 bg-[#FF5722] hover:bg-[#F4511E] text-black font-extrabold text-xs uppercase tracking-wider rounded-xl transition shadow-lg shadow-[#FF5722]/20 flex items-center gap-2 shrink-0">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <span>CARI</span>
        </button>
      </div>
    </form>
  </div>

  @if($selectedRegistration)
    <!-- SELECTED REGISTRATION CARD & INSTANT ACTION BUTTONS -->
    <div class="bg-[#121418] border-2 {{ $selectedRegistration->isTektok() ? 'border-cyan-500/50 shadow-cyan-500/10' : 'border-emerald-500/50 shadow-emerald-500/10' }} rounded-3xl p-5 sm:p-6 shadow-2xl space-y-5">
      
      <!-- Group Header Summary -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-[#1E2229] pb-4">
        <div>
          <div class="flex items-center gap-2">
            @if($selectedRegistration->isTektok())
              <span class="px-2.5 py-1 rounded-full text-[10px] font-mono-tech font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/40">
                KATEGORI: TEKTOK (03:00 - 07:00)
              </span>
            @else
              <span class="px-2.5 py-1 rounded-full text-[10px] font-mono-tech font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                KATEGORI: CAMP (POS 2 / POS 3)
              </span>
            @endif

            <span class="px-2.5 py-1 rounded-full text-[10px] font-mono-tech font-bold bg-[#181B22] text-stone-300 border border-[#282E3A]">
              {{ $selectedRegistration->total_climbers }} PENDAKI
            </span>
          </div>

          <h2 class="text-xl sm:text-2xl font-display font-black text-white mt-2">
            {{ strtoupper($selectedRegistration->leader_name) }}
          </h2>
          <p class="text-xs text-stone-400 font-mono-tech">
            Kode: <strong class="text-[#FF5722]">{{ $selectedRegistration->registration_code }}</strong> • Kontak: {{ $selectedRegistration->leader_phone }}
          </p>
        </div>

        <div class="text-left sm:text-right font-mono-tech text-xs bg-[#0A0B0D] p-3 rounded-xl border border-[#222731]">
          <span class="text-stone-500 text-[10px] block uppercase">Posisi Terakhir:</span>
          <span class="text-white font-bold">{{ $selectedRegistration->last_seen_pos ?: 'Basecamp' }}</span>
          <span class="text-stone-400 text-[10px] block mt-0.5">
            {{ $selectedRegistration->last_seen_at ? $selectedRegistration->last_seen_at->diffForHumans() : 'Baru Check-in' }}
          </span>
        </div>
      </div>

      <!-- Specific Category Rules Reminder for Ranger -->
      @if($selectedRegistration->isTektok())
        <div class="p-3 bg-cyan-950/40 border border-cyan-500/30 rounded-xl text-xs font-mono-tech text-cyan-200 flex items-start gap-2.5">
          <span class="text-cyan-400 font-bold shrink-0">[!]</span>
          <div>
            <strong class="font-bold text-white block">SOP PENDAKI TEKTOK:</strong>
            Pendaki wajib kembali ke Basecamp sebelum jam <strong>19:00 WIB</strong> hari ini. Dilarang keras mendirikan tenda di Pos manapun.
          </div>
        </div>
      @else
        <div class="p-3 bg-emerald-950/40 border border-emerald-500/30 rounded-xl text-xs font-mono-tech text-emerald-200 flex items-start gap-2.5">
          <span class="text-emerald-400 font-bold shrink-0">[CAMP]</span>
          <div>
            <strong class="font-bold text-white block">SOP PENDAKI CAMP:</strong>
            Bebas memilih tempat camp resmi di <strong>Pos 2 (Mata Air)</strong> atau <strong>Pos 3 (Camp Atas)</strong>. Di luar pos 2 & 3 dilarang pasang tenda.
          </div>
        </div>
      @endif

      <!-- ACTION FORM WITH BIG TOUCH BUTTONS -->
      <form method="POST" action="{{ route('management.ranger.checkpoint') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="registration_id" value="{{ $selectedRegistration->id }}">
        <input type="hidden" name="pos_code" value="{{ $activePos }}">

        <label class="block text-xs font-mono-tech text-stone-300 font-bold uppercase tracking-wider">
          PILIH TINDAKAN PENGAWASAN DI {{ $activePos }}:
        </label>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 font-mono-tech">
          <!-- Button 1: Naik [↑] -->
          <button type="submit" name="direction" value="ascending" class="p-4 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-sm uppercase tracking-wider transition shadow-lg shadow-emerald-600/20 flex items-center justify-center gap-2 transform active:scale-95">
            <span class="text-base font-mono-tech">[↑]</span>
            <span>SEDANG NAIK (MENUJU ATAS)</span>
          </button>

          <!-- Button 2: Turun [↓] -->
          <button type="submit" name="direction" value="descending" class="p-4 rounded-2xl bg-amber-600 hover:bg-amber-500 text-black font-black text-sm uppercase tracking-wider transition shadow-lg shadow-amber-600/20 flex items-center justify-center gap-2 transform active:scale-95">
            <span class="text-base font-mono-tech">[↓]</span>
            <span>SEDANG TURUN (MENUJU BAWAH)</span>
          </button>
        </div>

        <!-- Optional Camping Action for Camp Category at Pos 2 & Pos 3 -->
        @if(!$selectedRegistration->isTektok() && in_array($activePos, ['POS_2', 'POS_3']))
          <div class="pt-1">
            <button type="submit" name="direction" value="camping" class="w-full p-4 rounded-2xl bg-purple-600 hover:bg-purple-500 text-white font-black text-sm uppercase tracking-wider transition shadow-lg shadow-purple-600/20 flex items-center justify-center gap-2 transform active:scale-95">
              <span class="text-base font-mono-tech">[CAMP]</span>
              <span>BUKA TENDA / BERMALAM DI {{ $activePos }}</span>
            </button>
          </div>
        @endif

        <!-- Quick Condition Notes -->
        <div class="pt-2">
          <label class="block text-[10px] font-mono-tech text-stone-400 uppercase tracking-wider mb-1">
            CATATAN KONDISI LAPANGAN (OPSIONAL):
          </label>
          <input type="text" name="notes" placeholder="Misal: Anggota fit, cuaca gerimis, logistik cukup..." class="w-full bg-[#0A0B0D] border border-[#282E3A] rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-[#FF5722] font-mono-tech">
        </div>
      </form>
    </div>
  @elseif($code)
    <div class="bg-[#121418] border border-rose-500/30 rounded-2xl p-6 text-center space-y-2 font-mono-tech">
      <div class="w-10 h-10 mx-auto rounded-full bg-rose-500/10 border border-rose-500/30 flex items-center justify-center text-rose-400 font-bold text-sm">!</div>
      <h3 class="text-white font-bold text-base">ROMBONGAN TIDAK DITEMUKAN</h3>
      <p class="text-xs text-stone-400 max-w-md mx-auto">
        Kode pendaftaran <strong>"{{ $code }}"</strong> tidak terdaftar atau belum menyelesaikan proses izin di loket Basecamp.
      </p>
    </div>
  @endif

  <!-- RECENT CHECKPOINTS LOG AT THIS POS TODAY -->
  <div class="bg-[#121418] border border-[#20242C] rounded-2xl p-4 sm:p-5 shadow-xl font-mono-tech space-y-3">
    <div class="flex items-center justify-between border-b border-[#1E2229] pb-3">
      <div class="flex items-center gap-2">
        <span class="text-[#FF5722] font-bold text-sm">#</span>
        <h3 class="text-xs font-bold text-white uppercase tracking-wider">RIWAYAT SCAN TERAKHIR DI {{ $activePos }} HARI INI</h3>
      </div>
      <span class="text-[10px] text-stone-500">{{ count($todayLogs) }} Rombongan</span>
    </div>

    @if(count($todayLogs) > 0)
      <div class="divide-y divide-[#1A1E26]">
        @foreach($todayLogs as $log)
          <div class="py-2.5 flex items-center justify-between text-xs">
            <div class="flex items-center gap-2.5">
              <span class="font-bold text-white">{{ $log->registration?->leader_name ?: 'Rombongan' }}</span>
              <span class="text-[10px] text-stone-400">({{ $log->registration?->registration_code }})</span>
              @if($log->registration?->isTektok())
                <span class="text-[9px] px-1.5 py-0.2 rounded bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 font-bold">TEKTOK</span>
              @endif
            </div>

            <div class="flex items-center gap-3">
              @if($log->direction === 'ascending')
                <span class="text-[10px] font-bold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/30">NAIK [↑]</span>
              @elseif($log->direction === 'descending')
                <span class="text-[10px] font-bold text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/30">TURUN [↓]</span>
              @elseif($log->direction === 'camping')
                <span class="text-[10px] font-bold text-purple-400 bg-purple-500/10 px-2 py-0.5 rounded border border-purple-500/30">CAMP</span>
              @endif
              <span class="text-[10px] text-stone-400">{{ $log->scanned_at ? $log->scanned_at->format('H:i') . ' WIB' : '-' }}</span>
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="text-center py-6 text-stone-500 text-xs">
        Belum ada rombongan yang di-scan di {{ $activePos }} hari ini.
      </div>
    @endif
  </div>

  <!-- RADIO HT BACKUP ADVISORY -->
  <div class="bg-[#16191F] border border-[#222731] rounded-2xl p-4 flex items-start gap-3 text-xs font-mono-tech text-stone-300">
    <div class="w-8 h-8 rounded-lg bg-[#FF5722]/10 border border-[#FF5722]/30 flex items-center justify-center text-[#FF5722] shrink-0 font-bold text-xs">
      HT
    </div>
    <div class="space-y-1">
      <div class="font-bold text-white flex items-center gap-2">
        <span>PROTOKOL DARURAT BATERAI HP RANGER DROP / MATI:</span>
        <span class="text-[10px] px-2 py-0.5 rounded bg-[#FF5722]/15 text-[#FF5722] border border-[#FF5722]/30">RADIO HT: {{ $profile['sar_frequency'] ?? '149.250 MHz' }}</span>
      </div>
      <p class="text-stone-400 text-[11px] leading-relaxed">
        Jika smartphone petugas di Pos 1, 2, atau 3 mati/kehabisan baterai, segera gunakan radio komunikasi HT. Sebutkan <strong>3 digit kode registrasi tiket</strong> (contoh: <em>"Pos 2 lapor rombongan 001 melintas arah turun jam 15:30"</em>). Operator di Basecamp akan menginput data langsung ke sistem.
      </p>
    </div>
  </div>

</div>
@endsection
