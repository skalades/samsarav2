@extends('management.layout')

@section('title', 'Verifikasi Permohonan SIMAKSI')
@section('page_title', 'Antrean Verifikasi SIMAKSI')
@section('page_subtitle', 'Review Persyaratan, Dokumen, & Otorisasi Izin Pendakian ' . \App\Models\AppSetting::get('mountain_name', 'Gunung Cikuray'))

@section('content')
<div class="space-y-6">

  <!-- ======================================================== -->
  <!-- 1. STATUS FILTER PILLS & SEARCH                          -->
  <!-- ======================================================== -->
  <div class="bg-[#121418] border border-[#1E2229] rounded-xl p-4 space-y-4">
    
    <div class="flex flex-wrap items-center gap-2 font-mono-tech text-xs">
      <a href="{{ route('management.simaksi.index') }}" class="px-3.5 py-1.5 rounded-lg border transition {{ empty($status) ? 'bg-[#FF5722] text-black font-bold border-[#FF5722]' : 'bg-[#16191F] border-[#262B35] text-stone-300 hover:text-white' }}">
        SEMUA ({{ $statusCounts['all'] }})
      </a>
      <a href="{{ route('management.simaksi.index', ['status' => 'pending']) }}" class="px-3.5 py-1.5 rounded-lg border transition {{ $status === 'pending' ? 'bg-[#FF5722] text-black font-bold border-[#FF5722]' : 'bg-[#16191F] border-[#262B35] text-amber-400 hover:text-white' }}">
        PENDING ({{ $statusCounts['pending'] }})
      </a>
      <a href="{{ route('management.simaksi.index', ['status' => 'paid']) }}" class="px-3.5 py-1.5 rounded-lg border transition {{ $status === 'paid' ? 'bg-[#FF5722] text-black font-bold border-[#FF5722]' : 'bg-[#16191F] border-[#262B35] text-emerald-400 hover:text-white' }}">
        PAID / LUNAS ({{ $statusCounts['paid'] }})
      </a>
      <a href="{{ route('management.simaksi.index', ['status' => 'checked_in']) }}" class="px-3.5 py-1.5 rounded-lg border transition {{ $status === 'checked_in' ? 'bg-[#FF5722] text-black font-bold border-[#FF5722]' : 'bg-[#16191F] border-[#262B35] text-stone-300 hover:text-white' }}">
        DI JALUR ({{ $statusCounts['checked_in'] }})
      </a>
      <a href="{{ route('management.simaksi.index', ['status' => 'completed']) }}" class="px-3.5 py-1.5 rounded-lg border transition {{ $status === 'completed' ? 'bg-[#FF5722] text-black font-bold border-[#FF5722]' : 'bg-[#16191F] border-[#262B35] text-stone-300 hover:text-white' }}">
        SELESAI ({{ $statusCounts['completed'] }})
      </a>
    </div>

    <!-- Search input -->
    <form action="{{ route('management.simaksi.index') }}" method="GET" class="flex gap-2">
      @if($status)
        <input type="hidden" name="status" value="{{ $status }}">
      @endif
      <div class="relative flex-1">
        <input type="text" name="search" value="{{ $search }}" placeholder="Cari Kode SIMAKSI, Nama Ketua, No. WA, atau 3 Digit Kode Unik..." class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg px-4 py-2 text-xs font-mono-tech text-white focus:outline-none focus:border-[#FF5722]">
      </div>
      <button type="submit" class="px-4 py-2 bg-[#1A1E26] hover:bg-[#252A36] text-white border border-[#2E3544] font-mono-tech text-xs font-bold rounded-lg transition">
        CARI
      </button>
      @if($search)
        <a href="{{ route('management.simaksi.index', ['status' => $status]) }}" class="px-3 py-2 bg-rose-950/40 text-rose-300 border border-rose-500/30 rounded-lg text-xs font-mono-tech flex items-center">
          Reset
        </a>
      @endif
    </form>

  </div>

  <!-- ======================================================== -->
  <!-- 2. SIMAKSI QUEUE TABLE                                   -->
  <!-- ======================================================== -->
  <div class="bg-[#121418] border border-[#1E2229] rounded-xl p-5 space-y-4">
    
    <div class="overflow-x-auto">
      <table class="w-full text-left font-mono-tech text-xs">
        <thead>
          <tr class="text-[10px] text-stone-400 uppercase border-b border-[#1E2229]">
            <th class="pb-3">KODE REGISTRASI</th>
            <th class="pb-3">KETUA ROMBONGAN</th>
            <th class="pb-3">KONTAK WA</th>
            <th class="pb-3">JADWAL PENDAKIAN</th>
            <th class="pb-3">ANGGOTA</th>
            <th class="pb-3">TOTAL BIAYA</th>
            <th class="pb-3">STATUS SIMAKSI</th>
            <th class="pb-3 text-right">AKSI</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#1A1E26]">
          @forelse($registrations as $reg)
            <tr class="hover:bg-[#16191F] transition">
              <td class="py-3 font-bold text-[#FF5722]">
                <a href="{{ route('management.simaksi.show', $reg->id) }}" class="hover:underline">
                  {{ $reg->registration_code }}
                </a>
              </td>
              <td class="py-3 text-white">
                <div class="font-bold">{{ $reg->leader_name }}</div>
                <div class="text-[10px] text-stone-400 font-normal truncate max-w-xs" title="{{ $reg->leader_address ?? $reg->leader_city }}">KTP: {{ $reg->leader_nik }} • {{ $reg->leader_address ?? $reg->leader_city ?? 'Alamat belum diisi' }}</div>
              </td>
              <td class="py-3 text-stone-300">
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $reg->leader_phone) }}" target="_blank" class="hover:text-[#FF5722] hover:underline flex items-center gap-1">
                  <span>{{ $reg->leader_phone }}</span>
                </a>
              </td>
              <td class="py-3 text-stone-300">
                <div>{{ $reg->hiking_date->format('d M Y') }}</div>
                <span class="text-[10px] text-stone-400 font-normal">Turun: {{ $reg->return_date ? $reg->return_date->format('d M Y') : '-' }}</span>
              </td>
              <td class="py-3 text-stone-300">
                <span class="font-bold text-white">{{ $reg->total_climbers }} Orang</span>
              </td>
              <td class="py-3 font-bold text-stone-200">
                <div class="flex items-center gap-1.5">
                  <span>Rp {{ number_format($reg->total_amount, 0, ',', '.') }}</span>
                  @if($reg->unique_code > 0)
                    <span class="px-1.5 py-0.5 rounded bg-amber-500/15 border border-amber-500/30 text-amber-300 text-[9px] font-bold" title="Kode Unik Transfer">+{{ $reg->unique_code }}</span>
                  @endif
                </div>
                @if($reg->payment_status === 'paid')
                  <span class="block text-[9px] text-emerald-400 font-normal">LUNAS ({{ strtoupper($reg->payment_method ?? 'QRIS') }})</span>
                @else
                  <span class="block text-[9px] text-amber-400 font-normal">BELUM BAYAR</span>
                @endif
                @if($reg->payment_proof_path)
                  <button type="button" onclick="showProofModal('{{ asset('storage/' . $reg->payment_proof_path) }}', '{{ $reg->registration_code }}', '{{ addslashes($reg->leader_name) }}', '{{ number_format($reg->total_amount, 0, ',', '.') }}', '{{ strtoupper($reg->payment_method ?? 'QRIS') }}')" class="inline-flex items-center gap-1 text-[9px] text-cyan-400 hover:text-cyan-300 hover:underline mt-0.5 cursor-pointer">
                    <svg class="w-3 h-3 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>Bukti Bayar</span>
                  </button>
                @endif
              </td>
              <td class="py-3">
                <span class="px-2.5 py-1 rounded text-[10px] font-bold border {{ $reg->status_badge['bg'] }}">
                  {{ $reg->status_badge['label'] }}
                </span>
                @if($reg->status === 'needs_revision')
                  <span class="block text-[9px] text-amber-400/80 mt-1 max-w-xs truncate" title="{{ $reg->revision_notes }}">
                    Catatan: {{ $reg->revision_notes }}
                  </span>
                @endif
              </td>
              <td class="py-3 text-right space-x-1.5 whitespace-nowrap">
                @if($reg->status === 'pending' || $reg->payment_status !== 'paid')
                  @php
                    $methodGuess = $reg->payment_method ?? ($reg->payment_proof_path ? 'transfer' : 'cash_pos');
                  @endphp
                  <form action="{{ route('management.simaksi.paid', $reg->id) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="redirect_to" value="index">
                    <input type="hidden" name="payment_method" value="{{ $methodGuess }}">
                    <button type="submit" onclick="return confirm('Konfirmasi terima pembayaran via {{ strtoupper($methodGuess) }} untuk {{ $reg->registration_code }} dan set status ke PAID?')" class="px-2.5 py-1.5 rounded bg-emerald-600 hover:bg-emerald-500 text-white font-mono-tech text-[10px] font-bold transition">
                      Set PAID ({{ strtoupper($methodGuess) }})
                    </button>
                  </form>
                @elseif($reg->status === 'paid')
                  <a href="{{ route('management.gate.index', ['code' => $reg->registration_code]) }}" class="px-2.5 py-1.5 rounded bg-blue-600/20 hover:bg-blue-600 text-blue-400 hover:text-white border border-blue-500/30 text-[10px] font-bold transition">
                    Gate Check-in
                  </a>
                @endif
                <a href="{{ route('management.simaksi.show', $reg->id) }}" class="px-2.5 py-1.5 rounded bg-[#1A1E26] hover:bg-[#FF5722] hover:text-black hover:border-[#FF5722] text-stone-300 border border-[#2E3544] text-[10px] font-bold transition">
                  Detail
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="py-8 text-center text-stone-400">
                Tidak ada permohonan SIMAKSI yang sesuai dengan filter.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pt-4 border-t border-[#1E2229]">
      {{ $registrations->links() }}
    </div>

  </div>

</div>
@endsection
