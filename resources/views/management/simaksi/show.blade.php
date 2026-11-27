@extends('management.layout')

@section('title', 'Detail SIMAKSI ' . $registration->registration_code)
@section('page_title', 'Detail Permohonan ' . $registration->registration_code)
@section('page_subtitle', 'Verifikasi Dokumen, Anggota Rombongan, & Putusan Izin')

@section('content')
<div class="space-y-6">

  <!-- Top Breadcrumb & Status Header -->
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-surface border border-line rounded-2xl p-4">
    <div class="flex items-center gap-3 min-w-0">
      <a href="{{ route('management.simaksi.index') }}" class="p-2 rounded bg-raised hover:bg-hover text-stone-400 hover:text-white transition">
        ← Kembali
      </a>
      <div>
        <div class="flex flex-wrap items-center gap-2">
          <h2 class="font-display font-black text-xl text-white">{{ $registration->registration_code }}</h2>
          <span class="px-2.5 py-0.5 rounded text-xs font-mono-tech font-bold border {{ $registration->status_badge['bg'] }}">
            {{ $registration->status_badge['label'] }}
          </span>
        </div>
        <p class="font-mono-tech text-[10px] text-stone-400 mt-0.5">
          Diajukan: {{ $registration->created_at->format('d M Y H:i WIB') }} • Jalur {{ \App\Models\AppSetting::get('expedition_name', 'SAMSARA') }} {{ \App\Models\AppSetting::get('mountain_name', 'Cikuray') }}
        </p>
      </div>
    </div>

    <!-- Quick Payment & Check-in info & PDF Download -->
    <div class="flex flex-wrap items-center justify-between lg:justify-end gap-3 lg:gap-4">
      <div class="text-left lg:text-right font-mono-tech text-xs">
        <span class="text-stone-400 block text-[10px]">TOTAL BIAYA:</span>
        <span class="font-bold text-white text-base">Rp {{ number_format($registration->total_amount, 0, ',', '.') }}</span>
        <span class="block text-[10px] {{ $registration->payment_status === 'paid' ? 'text-emerald-400' : 'text-amber-400' }}">
          STATUS: {{ strtoupper($registration->payment_status) }} ({{ strtoupper($registration->payment_method ?? 'BELUM BAYAR') }})
        </span>
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ route('management.simaksi.ticket_pdf', $registration->id) }}" class="px-3 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-black font-bold font-mono-tech text-xs flex items-center gap-1.5 shadow-md shadow-emerald-500/20 transition">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
          <span>Unduh PDF</span>
        </a>
        <a href="{{ $registration->publicTicketUrl() }}" target="_blank" class="px-3 py-2 rounded-lg bg-raised hover:bg-hover border border-line-strong text-stone-200 font-bold font-mono-tech text-xs flex items-center gap-1 transition">
          <span>E-Tiket ↗</span>
        </a>
      </div>
    </div>
  </div>

  <!-- Rejection / Revision Alert if applicable -->
  @if($registration->status === 'needs_revision')
    <div class="p-4 bg-amber-500/10 border border-amber-500/30 rounded-xl space-y-1 font-mono-tech text-xs text-amber-300">
      <div class="flex items-center gap-2 font-bold uppercase">
        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <span>STATUS: PERLU PERBAIKAN DOKUMEN / DATA</span>
      </div>
      <p class="text-stone-300">Instruksi Petugas: "{{ $registration->revision_notes }}"</p>
    </div>
  @endif

  @if($registration->status === 'rejected')
    <div class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-xl space-y-1 font-mono-tech text-xs text-rose-300">
      <div class="flex items-center gap-2 font-bold uppercase">
        <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        <span>STATUS: PERMOHONAN DITOLAK RESMI</span>
      </div>
      <p class="text-stone-300">Alasan Penolakan: "{{ $registration->rejection_reason }}"</p>
    </div>
  @endif

  <!-- ======================================================== -->
  <!-- MAIN GRID: DETAILS & ACTIONS                             -->
  <!-- ======================================================== -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Left 2 Cols: Leader, Emergency Contact, Members Table -->
    <div class="lg:col-span-2 space-y-6">
      
      <!-- Leader & Schedule Card -->
      <div class="bg-surface border border-line rounded-xl p-5 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-line">
          <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-sm bg-brand"></div>
            <h3 class="font-mono-tech font-bold text-xs uppercase tracking-wider text-white">
              DATA KETUA ROMBONGAN & JADWAL
            </h3>
          </div>
          <span class="font-mono-tech text-[10px] text-stone-400">LEADER DATA</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 font-mono-tech text-xs">
          <div>
            <span class="text-[10px] text-stone-400 block uppercase">NAMA LENGKAP KETUA</span>
            <span class="font-bold text-white text-sm block mt-0.5">{{ $registration->leader_name }}</span>
          </div>
          <div>
            <span class="text-[10px] text-stone-400 block uppercase">NIK KTP</span>
            <span class="font-bold text-stone-200 block mt-0.5">{{ $registration->leader_nik }}</span>
          </div>
          <div>
            <span class="text-[10px] text-stone-400 block uppercase">NO. WHATSAPP</span>
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $registration->leader_phone) }}" target="_blank" class="font-bold text-brand hover:underline block mt-0.5">
              {{ $registration->leader_phone }}
            </a>
          </div>
          <div>
            <span class="text-[10px] text-stone-400 block uppercase">ALAMAT LENGKAP DOMISILI</span>
            <span class="text-stone-300 block mt-0.5">{{ $registration->leader_address ?? $registration->leader_city ?? '-' }}</span>
          </div>
          <div>
            <span class="text-[10px] text-stone-400 block uppercase">TANGGAL PENDAKIAN</span>
            <span class="font-bold text-emerald-400 block mt-0.5">{{ $registration->hiking_date->format('d M Y') }}</span>
          </div>
          <div>
            <span class="text-[10px] text-stone-400 block uppercase">ESTIMASI TURUN</span>
            <span class="text-stone-300 block mt-0.5">{{ $registration->return_date ? $registration->return_date->format('d M Y') : '-' }}</span>
          </div>
          <div class="col-span-2 sm:col-span-3 pt-2 border-t border-line">
            <span class="text-[10px] text-stone-400 block uppercase">RIWAYAT PENYAKIT BAWAAN / KONDISI MEDIS KETUA</span>
            <span class="font-bold {{ $registration->medical_conditions && $registration->medical_conditions !== 'Tidak Ada' ? 'text-amber-400' : 'text-stone-300' }} block mt-0.5">
              {{ $registration->medical_conditions ?? 'Tidak Ada' }}
            </span>
          </div>
        </div>

        <!-- Emergency Contact -->
        <div class="p-3 bg-raised border border-line rounded-xl text-xs space-y-1">
          <span class="text-[11px] text-brand font-bold uppercase tracking-wider block">Kontak Darurat (Keluarga Tidak Ikut Mendaki)</span>
          <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1 text-muted pt-1">
            <span>Nama: <strong class="text-ink">{{ $registration->emergency_contact_name }}</strong> ({{ $registration->emergency_relationship }})</span>
            <span>Telp: <a href="tel:{{ $registration->emergency_contact_phone }}" class="text-brand font-bold hover:underline">{{ $registration->emergency_contact_phone }}</a></span>
          </div>
        </div>

        <!-- Foto Dokumen KTP Asli Ketua -->
        <div class="p-3.5 bg-raised border border-line rounded-xl font-mono-tech text-xs space-y-2.5">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-1.5 text-stone-200">
              <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
              <span class="text-[10px] font-bold uppercase tracking-wider">FOTO / SCAN KTP ASLI KETUA:</span>
            </div>
            @if($registration->leader_ktp_path)
              <span class="text-[10px] text-emerald-400 font-bold bg-emerald-500/10 border border-emerald-500/30 px-2.5 py-0.5 rounded uppercase">
                [✓] TERUNGGAH & TERKOMPRESI
              </span>
            @elseif($registration->status === 'completed')
              <span class="text-[10px] text-stone-400 font-bold bg-stone-500/10 border border-stone-500/30 px-2.5 py-0.5 rounded uppercase">
                [✓] DIMUSNAHKAN OTOMATIS (PDP PRIVASI)
              </span>
            @else
              <span class="text-[10px] text-amber-400 font-bold bg-amber-500/10 border border-amber-500/30 px-2.5 py-0.5 rounded uppercase">
                [!] BELUM DILAMPIRKAN
              </span>
            @endif
          </div>

          @if($registration->leader_ktp_path)
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 pt-1">
              <a href="{{ route('management.simaksi.ktp', $registration->id) }}" target="_blank" class="group relative block overflow-hidden rounded-lg border border-line-strong hover:border-brand bg-black transition flex-shrink-0">
                <img src="{{ route('management.simaksi.ktp', $registration->id) }}" alt="KTP {{ $registration->leader_name }}" class="h-28 w-44 object-cover group-hover:opacity-90 transition">
                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center text-[10px] text-white font-bold transition">
                  [KLIK PERBESAR KTP]
                </div>
              </a>
              <div class="text-[11px] text-stone-300 space-y-1.5 leading-relaxed">
                <div>Cocokkan NIK KTP: <strong class="text-white text-xs">{{ $registration->leader_nik }}</strong></div>
                <div>Nama Lengkap: <strong class="text-white text-xs">{{ $registration->leader_name }}</strong></div>
                <div class="text-[10px] text-emerald-400 font-bold flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                  <span>DILINDUNGI RUTE TERPROTEKSI (UU PDP)</span>
                </div>
                <div class="text-[10px] text-stone-400">Verifikator wajib memvalidasi foto identitas dengan data manifest rombongan sebelum putusan izin.</div>
              </div>
            </div>
          @elseif($registration->status === 'completed')
            <div class="p-2.5 bg-canvas border border-emerald-500/20 rounded-lg text-[10px] text-stone-400 flex items-center gap-2">
              <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span>Sesuai SOP Perlindungan Data Pribadi (PDP), berkas fisik KTP ketua rombongan telah dimusnahkan secara otomatis saat pendaki check-out selesai turun gunung.</span>
            </div>
          @else
            <div class="p-2.5 bg-canvas border border-line-strong rounded-lg text-[10px] text-stone-400">
              Pemohon belum melampirkan foto KTP fisik. Jika diperlukan untuk SOP verifikasi, gunakan formulir <strong>Minta Perbaikan Berkas</strong> di samping.
            </div>
          @endif
        </div>
      </div>

      <!-- Climber Members Manifest Table -->
      <div class="bg-surface border border-line rounded-2xl p-4 sm:p-5 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-line">
          <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 rounded-sm bg-brand"></div>
            <h3 class="font-display font-bold text-xs uppercase tracking-wider text-ink">
              Manifest Anggota Rombongan ({{ $registration->members->count() }} Orang)
            </h3>
          </div>
          <span class="text-[10px] text-subtle">CREW ROSTER</span>
        </div>

        {{-- Mobile: kartu anggota --}}
        <div class="lg:hidden space-y-2.5">
          @foreach($registration->members as $idx => $m)
            <div class="p-3 rounded-xl bg-raised/70 ring-1 ring-line space-y-1.5 text-xs">
              <div class="flex items-center justify-between gap-2">
                <span class="font-semibold text-sm text-ink">{{ $idx + 1 }}. {{ $m->full_name }}</span>
                <div class="flex items-center gap-1 shrink-0">
                  @if($m->is_leader)
                    <x-ui.badge tone="brand" size="xs">Ketua</x-ui.badge>
                  @endif
                  @if($m->is_checked_in)
                    <x-ui.badge tone="success" size="xs">Hadir</x-ui.badge>
                  @endif
                </div>
              </div>
              <p class="text-muted font-code">NIK: {{ $m->nik ?? '-' }}</p>
              <p class="text-muted">{{ $m->gender === 'L' ? 'Laki-laki' : 'Perempuan' }} · {{ $m->city_origin ?? '-' }}</p>
              @if($m->medical_conditions && $m->medical_conditions !== 'Tidak Ada')
                <p class="text-amber-400 font-semibold">Medis: {{ $m->medical_conditions }}</p>
              @endif
            </div>
          @endforeach
        </div>

        {{-- Desktop: tabel --}}
        <div class="hidden lg:block overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="text-[10px] text-subtle uppercase border-b border-line">
                <th class="pb-2">NO</th>
                <th class="pb-2">NAMA LENGKAP</th>
                <th class="pb-2">NIK</th>
                <th class="pb-2">GENDER</th>
                <th class="pb-2">KOTA</th>
                <th class="pb-2">KONDISI MEDIS</th>
                <th class="pb-2">PERAN</th>
                <th class="pb-2">GATE CHECK-IN</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-line">
              @foreach($registration->members as $idx => $m)
                <tr class="hover:bg-raised">
                  <td class="py-2.5 text-muted">{{ $idx + 1 }}</td>
                  <td class="py-2.5 font-bold text-ink">{{ $m->full_name }}</td>
                  <td class="py-2.5 text-muted font-code">{{ $m->nik ?? '-' }}</td>
                  <td class="py-2.5 text-muted">{{ $m->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                  <td class="py-2.5 text-muted">{{ $m->city_origin ?? '-' }}</td>
                  <td class="py-2.5">
                    @if($m->medical_conditions && $m->medical_conditions !== 'Tidak Ada')
                      <span class="px-2 py-0.5 rounded bg-amber-500/10 border border-amber-500/30 text-amber-400 text-[10px] font-bold">
                        [!] {{ $m->medical_conditions }}
                      </span>
                    @else
                      <span class="text-subtle text-[10px]">Tidak Ada</span>
                    @endif
                  </td>
                  <td class="py-2.5">
                    @if($m->is_leader)
                      <span class="px-2 py-0.5 rounded bg-brand/10 border border-brand/30 text-brand text-[10px] font-bold">
                        KETUA TIM
                      </span>
                    @else
                      <span class="text-subtle text-[10px]">Anggota</span>
                    @endif
                  </td>
                  <td class="py-2.5">
                    @if($m->is_checked_in)
                      <span class="text-emerald-400 text-[10px] font-bold">✓ HADIR</span>
                    @else
                      <span class="text-subtle text-[10px]">Belum</span>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      <!-- Audit Trail for this registration -->
      <div class="bg-surface border border-line rounded-xl p-5 space-y-4">
        <div class="flex items-center gap-2 pb-3 border-b border-line">
          <div class="w-2.5 h-2.5 rounded-sm bg-brand"></div>
          <h3 class="font-mono-tech font-bold text-xs uppercase tracking-wider text-white">
            JEJAK AUDIT & RIWAYAT KEPUTUSAN PETUGAS
          </h3>
        </div>

        <div class="space-y-3 font-mono-tech text-xs">
          @forelse($auditLogs as $log)
            <div class="p-3 bg-raised border border-line rounded-lg space-y-1">
              <div class="flex justify-between items-center text-[10px]">
                <span class="font-bold text-brand">{{ $log->action }}</span>
                <span class="text-stone-400">{{ $log->created_at->format('d M Y H:i:s') }}</span>
              </div>
              <p class="text-stone-200 text-xs">{{ $log->description }}</p>
              @if($log->reason)
                <div class="text-[11px] text-amber-300/90 bg-amber-500/10 p-2 rounded border border-amber-500/20 mt-1">
                  Alasan: {{ $log->reason }}
                </div>
              @endif
              <div class="pt-1 border-t border-line text-[9px] text-stone-400 flex justify-between">
                <span>Oleh: {{ $log->actor_name }} ({{ $log->actor_role }})</span>
                <span>IP: {{ $log->ip_address }}</span>
              </div>
            </div>
          @empty
            <div class="text-center py-4 text-stone-400">Belum ada jejak audit untuk permohonan ini.</div>
          @endforelse
        </div>
      </div>

    </div>

    <!-- Right Col: Fee Breakdown & Verifier Decision Actions -->
    <div class="space-y-6">
      
      <!-- Fee Breakdown Card -->
      <div class="bg-surface border border-line rounded-xl p-5 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-line">
          <h3 class="font-mono-tech font-bold text-xs uppercase tracking-wider text-white">
            RINCIAN TARIF & LAYANAN
          </h3>
          <span class="font-mono-tech text-[10px] text-stone-400">RESMI</span>
        </div>

        <div class="space-y-2 font-mono-tech text-xs">
          <div class="flex justify-between text-stone-300">
            <span>Tiket SIMAKSI ({{ $registration->total_climbers }} orang{{ $registration->total_climbers > 0 ? ' @ ' . rupiah($registration->simaksi_fee / $registration->total_climbers) : '' }}):</span>
            <span class="font-bold">{{ rupiah($registration->simaksi_fee) }}</span>
          </div>
          <div class="flex justify-between text-stone-300">
            <span>Shuttle Ojek ({{ $registration->ojek_count ?? ($registration->ojek_fee > 0 ? 1 : 0) }} armada{{ (int)($registration->ojek_count ?? 0) > 0 ? ' @ ' . rupiah($registration->ojek_fee / $registration->ojek_count) : '' }}):</span>
            <span class="font-bold">{{ rupiah($registration->ojek_fee) }}</span>
          </div>
          @if($registration->has_vehicle || $registration->parking_fee > 0)
            <div class="flex justify-between text-cyan-300 bg-cyan-950/20 p-1.5 rounded border border-cyan-800/30">
              <span>Titip Parkir ({{ $registration->motorcycle_count }} Motor, {{ $registration->car_count }} Mobil):</span>
              <span class="font-bold">{{ rupiah($registration->parking_fee) }}</span>
            </div>
          @endif
          @if($registration->unique_code > 0)
            <div class="flex justify-between text-amber-400 bg-amber-500/10 p-2 rounded border border-amber-500/20">
              <span>Kode Unik Verifikasi Bank:</span>
              <span class="font-bold">+{{ rupiah($registration->unique_code) }}</span>
            </div>
          @endif
          <div class="pt-2 border-t border-line flex justify-between items-center text-sm font-bold text-white">
            <span>TOTAL TAGIHAN:</span>
            <span class="text-brand text-base">{{ rupiah($registration->total_amount) }}</span>
          </div>
          
          <div class="p-2.5 bg-canvas border border-line rounded-lg text-[10px] text-stone-400 mt-2 space-y-1">
            @if($registration->unique_code > 0)
              <div class="text-amber-300 font-bold pb-1 border-b border-line">
                // Verifikasi Mutasi Bank: Nominal transfer persis <strong class="text-white">{{ rupiah($registration->total_amount) }}</strong> (Kode unik: {{ $registration->unique_code }}).
              </div>
            @endif
            <div>
              @if($registration->trash_deposit_amount > 0)
                <span class="text-stone-300 font-bold">UANG JAMINAN SAMPAH: {{ rupiah($registration->trash_deposit_amount) }}</span> (Diserahkan saat Gate Check-in & dikembalikan 100% setelah sampah dibawa turun).
              @else
                <span class="text-stone-400 font-bold">JAMINAN SAMPAH: NONAKTIF / BEBAS DEPOSIT</span>
              @endif
            </div>
          </div>
        </div>

        <!-- Bukti Pembayaran yang Diunggah Pendaki -->
        @if($registration->payment_proof_path)
          <div class="p-3 bg-raised border border-blue-500/40 rounded-xl space-y-2 font-mono-tech">
            <div class="flex items-center justify-between text-[10px]">
              <span class="text-blue-400 font-bold uppercase">BUKTI BAYAR DIUNGGAH PENDAKI:</span>
              <span class="text-stone-400">Metode: {{ strtoupper($registration->payment_method) }}</span>
            </div>
            <button type="button" onclick="showProofModal('{{ asset('storage/' . $registration->payment_proof_path) }}', '{{ $registration->registration_code }}', '{{ addslashes($registration->leader_name) }}', '{{ number_format($registration->total_amount, 0, ',', '.') }}', '{{ strtoupper($registration->payment_method ?? 'QRIS') }}')" class="block w-full text-left group relative overflow-hidden rounded-lg border border-line-strong bg-black cursor-pointer">
              <img src="{{ asset('storage/' . $registration->payment_proof_path) }}" alt="Bukti Transfer" class="w-full max-h-48 object-contain group-hover:opacity-90 transition">
              <div class="p-1.5 text-center bg-raised text-[9px] text-stone-300 group-hover:text-cyan-300 transition flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span>[KLIK UNTUK MEMPERBESAR BUKTI BAYAR]</span>
              </div>
            </button>
          </div>
        @endif

        <!-- Payment action if pending -->
        @if($registration->status === 'pending' || $registration->payment_status !== 'paid')
          <form action="{{ route('management.simaksi.paid', $registration->id) }}" method="POST" class="pt-3 border-t border-line space-y-3">
            @csrf
            <div>
              <label for="payment_method" class="block text-[10px] font-mono-tech font-bold text-stone-300 uppercase mb-1.5 flex items-center justify-between">
                <span>Kanal Masuk Pembayaran:</span>
                <span class="text-cyan-400 font-normal">Pilihan Pendaki: {{ strtoupper($registration->payment_method ?? ($registration->payment_proof_path ? 'TRANSFER' : 'CASH')) }}</span>
              </label>
              @php
                $defaultMethod = old('payment_method', $registration->payment_method ?? ($registration->payment_proof_path ? 'transfer' : 'cash_pos'));
              @endphp
              <select id="payment_method" name="payment_method" class="field font-semibold">
                <option value="qris" {{ $defaultMethod === 'qris' ? 'selected' : '' }}>QRIS Resmi Basecamp</option>
                <option value="transfer" {{ in_array($defaultMethod, ['transfer', 'bank_transfer']) ? 'selected' : '' }}>Transfer Bank (BCA)</option>
                <option value="cash_pos" {{ in_array($defaultMethod, ['cash', 'cash_pos']) ? 'selected' : '' }}>Uang Tunai (Cash Fisik Posko)</option>
              </select>
              <p class="text-[11px] text-muted mt-1">Uang non-tunai (QRIS &amp; Transfer) tidak membebani laci fisik kasir posko.</p>
            </div>
            <button type="submit" onclick="return confirm('Konfirmasi pembayaran LUNAS? Info pendakian & e-SIMAKSI akan dikirim otomatis ke WhatsApp ketua.')" class="w-full h-11 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs sm:text-sm rounded-xl transition shadow-lg shadow-emerald-600/20 flex items-center justify-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
              <span>Konfirmasi lunas &amp; kirim WhatsApp</span>
            </button>
          </form>
        @else
          @php
            $flow = [
              'paid' => ['Lunas', 'paid'],
              'gate_cleared' => ['Lolos Gate', 'gate_cleared'],
              'checked_in' => ['Berangkat', 'checked_in'],
              'returned' => ['Turun', 'returned'],
              'completed' => ['Selesai', 'completed'],
            ];
            $order = array_keys($flow);
            $pos = array_search($registration->status, $order, true);
          @endphp
          <div class="pt-3 border-t border-line space-y-3">
            <ol class="grid grid-cols-5 gap-1">
              @foreach($flow as $key => [$label])
                @php $i = array_search($key, $order, true); @endphp
                <li class="text-center">
                  <span class="block h-1.5 rounded-full {{ $pos !== false && $i <= $pos ? 'bg-emerald-400' : 'bg-raised' }}"></span>
                  <span class="block mt-1 text-[10px] {{ $pos !== false && $i === $pos ? 'text-ink font-semibold' : 'text-subtle' }}">{{ $label }}</span>
                </li>
              @endforeach
            </ol>
            <p class="text-sm text-muted">Status: <strong class="text-ink">{{ $registration->statusLabel() }}</strong></p>
            @if(in_array($registration->status, ['gate_cleared', 'returned', 'checked_in', 'completed']))
              <x-ui.button :href="route('management.desk.show', $registration->id)" icon="inbox" block>Buka di meja kasir</x-ui.button>
            @elseif($registration->status === 'paid')
              <p class="text-xs text-muted">Menunggu pemeriksaan naik oleh Gatekeeper (alat, logistik, foto tim).</p>
            @endif
          </div>
        @endif
      </div>

      <!-- Decision Box (Revision & Rejection if pending) -->
      <div class="bg-surface border border-line rounded-xl p-5 space-y-4">
        <div class="flex items-center gap-2 pb-3 border-b border-line">
          <div class="w-2.5 h-2.5 rounded-sm bg-brand"></div>
          <h3 class="font-mono-tech font-bold text-xs uppercase tracking-wider text-white">
            KONTROL STATUS KASIR
          </h3>
        </div>

        <!-- 2. REQUEST REVISION FORM -->
        @if(!in_array($registration->status, array_merge(\App\Models\SimaksiRegistration::POST_PAYMENT_STATUSES, ['rejected'])))
          <div class="pt-3 border-t border-line space-y-2">
            <span class="block font-mono-tech text-[10px] text-amber-400 font-bold uppercase">MINTA PERBAIKAN BERKAS / DATA:</span>
            <form action="{{ route('management.simaksi.revision', $registration->id) }}" method="POST" class="space-y-2">
              @csrf
              <textarea name="revision_notes" required rows="2" placeholder="Tuliskan instruksi perbaikan untuk ketua tim..." class="field focus:!border-amber-500"></textarea>
              <button type="submit" class="w-full h-10 bg-amber-600/20 hover:bg-amber-600/30 text-amber-300 border border-amber-500/30 font-semibold text-xs rounded-xl transition">
                Kirim Permintaan Revisi
              </button>
            </form>
          </div>
        @endif

        <!-- 3. REJECT FORM -->
        @if(!in_array($registration->status, array_merge(\App\Models\SimaksiRegistration::POST_PAYMENT_STATUSES, ['rejected'])))
          <div class="pt-3 border-t border-line space-y-2">
            <span class="block text-[11px] text-rose-400 font-bold uppercase">Tolak Permohonan</span>
            <form action="{{ route('management.simaksi.reject', $registration->id) }}" method="POST" class="space-y-2">
              @csrf
              <textarea name="rejection_reason" required rows="2" placeholder="Tuliskan alasan penolakan resmi (wajib)..." class="field focus:!border-rose-500"></textarea>
              <button type="submit" onclick="return confirm('Yakin ingin MENOLAK permohonan SIMAKSI ini? Tindakan akan diaudit.')" class="w-full h-10 bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 border border-rose-500/30 font-semibold text-xs rounded-xl transition">
                Tolak Permohonan Resmi
              </button>
            </form>
          </div>
        @endif

      </div>

      <!-- WhatsApp Gateway -->
      @php $waMessages = $registration->whatsappMessages()->take(4)->get(); @endphp
      <x-ui.card title="WhatsApp ke ketua" :subtitle="$registration->leader_phone" icon="whatsapp">
        @if(in_array($registration->status, \App\Models\SimaksiRegistration::POST_PAYMENT_STATUSES))
          <p class="text-xs text-muted mb-3">Info pendakian & tautan e-SIMAKSI dikirim otomatis saat pembayaran dikonfirmasi.</p>
          <ul class="space-y-2 mb-3">
            @forelse($waMessages as $msg)
              <li class="flex items-center justify-between gap-2 p-2.5 rounded-xl bg-raised/60 ring-1 ring-line text-xs">
                <span class="text-muted truncate">{{ \App\Models\WhatsappMessage::EVENT_LABELS[$msg->event] ?? $msg->event }} · {{ $msg->created_at->diffForHumans() }}</span>
                <x-ui.badge :tone="$msg->statusTone()" size="xs" dot>{{ $msg->statusLabel() }}</x-ui.badge>
              </li>
            @empty
              <li class="text-xs text-subtle">Belum ada pesan tercatat.</li>
            @endforelse
          </ul>
          <form method="POST" action="{{ route('management.simaksi.whatsapp', $registration->id) }}">
            @csrf
            <x-ui.button type="submit" variant="secondary" icon="refresh" block>Kirim ulang info & e-SIMAKSI</x-ui.button>
          </form>
          <a href="{{ \App\Services\NotificationService::getWhatsAppUrl($registration->leader_phone, \App\Services\NotificationService::paymentConfirmedMessage($registration)) }}" target="_blank" class="mt-2 flex items-center justify-center gap-1.5 text-xs text-muted hover:text-ink h-9">
            <x-ui.icon name="external" class="w-3.5 h-3.5" /> Kirim manual lewat WhatsApp di perangkat ini
          </a>
        @elseif($registration->status === 'needs_revision')
          <x-ui.button variant="secondary" icon="whatsapp" target="_blank" :href="\App\Services\NotificationService::getWhatsAppUrl($registration->leader_phone, \App\Services\NotificationService::revisionRequestedMessage($registration, $registration->revision_notes ?? 'Harap unggah ulang foto KTP'))" block>Kirim instruksi perbaikan</x-ui.button>
        @else
          <x-ui.button variant="secondary" icon="whatsapp" target="_blank" :href="\App\Services\NotificationService::getWhatsAppUrl($registration->leader_phone, \App\Services\NotificationService::bookingCreatedMessage($registration))" block>Kirim tagihan & rekening</x-ui.button>
          <p class="text-[11px] text-subtle mt-2">Setelah LUNAS dikonfirmasi, info pendakian lengkap terkirim otomatis.</p>
        @endif
      </x-ui.card>

      @if(auth()->user()?->level() === \App\Models\User::ROLE_SUPERADMIN)
        <x-ui.card title="Zona Berbahaya (Superadmin)" subtitle="Penghapusan permanen SIMAKSI & seluruh data turunannya" icon="trash">
          <p class="text-xs text-muted leading-relaxed mb-3">
            Menghapus SIMAKSI akan ikut menghapus manifest anggota, voucher/trip ojek, catatan sewa alat, transaksi POS terhubung, log WhatsApp, checkpoint ranger, berkas foto/KTP, serta mengembalikan kuota &amp; stok barang.
          </p>
          <x-ui.button variant="danger" icon="trash" block onclick="document.getElementById('deleteSimaksiModal').classList.remove('hidden')">
            Hapus Permanen SIMAKSI
          </x-ui.button>
        </x-ui.card>
      @endif

    </div>

  </div>

</div>

@if(auth()->user()?->level() === \App\Models\User::ROLE_SUPERADMIN)
  <x-ui.modal id="deleteSimaksiModal" title="Hapus Permanen {{ $registration->registration_code }}" eyebrow="Otorisasi Superadmin" maxWidth="sm:max-w-md">
    <form method="POST" action="{{ route('management.simaksi.destroy', $registration->id) }}" class="space-y-4">
      @csrf
      @method('DELETE')
      <div class="p-3.5 rounded-xl bg-rose-500/10 ring-1 ring-rose-500/30 text-xs text-rose-200 space-y-1.5 leading-relaxed">
        <p class="font-bold text-rose-300">Tindakan ini tidak dapat dibatalkan.</p>
        <p>Seluruh data terkait <strong>{{ $registration->registration_code }}</strong> ({{ $registration->leader_name }} · {{ $registration->total_climbers }} pendaki) akan dihapus permanen:</p>
        <ul class="list-disc pl-4 space-y-0.5 text-rose-200/90">
          <li>Data manifest anggota, checklist gate, dan log checkpoint ranger</li>
          <li>Voucher / riwayat trip ojek terkait (driver aktif dikembalikan standby)</li>
          <li>Catatan sewa alat &amp; transaksi POS terhubung (stok dikembalikan)</li>
          <li>Log pesan WhatsApp, berkas foto/KTP, dan kuota harian dilepaskan</li>
        </ul>
      </div>

      <div>
        <label for="deleteSimaksiReason" class="label">Alasan Penghapusan (Opsional, tercatat di Audit Log)</label>
        <input id="deleteSimaksiReason" type="text" name="reason" maxlength="500" placeholder="Contoh: Data uji coba / duplikat permohonan" class="field">
      </div>

      <div class="flex items-center justify-end gap-2 pt-2 border-t border-line">
        <x-ui.button variant="secondary" onclick="document.getElementById('deleteSimaksiModal').classList.add('hidden')">Batal</x-ui.button>
        <x-ui.button type="submit" variant="danger" icon="trash">Ya, Hapus Permanen</x-ui.button>
      </div>
    </form>
  </x-ui.modal>
@endif
@endsection
