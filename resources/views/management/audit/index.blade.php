@extends('management.layout')

@section('title', 'Audit Log Operasional')
@section('page_title', 'Audit Log & Jejak Akuntabilitas')
@section('page_subtitle', 'Catatan Tak Terhapuskan Seluruh Tindakan Verifikator, Kasir, & Petugas Lapangan')

@section('content')
<div class="space-y-6">

  <!-- Filter & Search Bar -->
  <div class="bg-[#121418] border border-[#1E2229] rounded-xl p-4 font-mono-tech text-xs">
    <form action="{{ route('management.audit.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
      
      <div>
        <select name="action" onchange="this.form.submit()" class="bg-[#181B22] border border-[#2A303C] rounded-lg p-2 text-white text-xs">
          <option value="">-- Semua Jenis Tindakan --</option>
          @foreach($actions as $act)
            <option value="{{ $act }}" {{ $action === $act ? 'selected' : '' }}>{{ $act }}</option>
          @endforeach
        </select>
      </div>

      <div class="flex-1 min-w-[200px]">
        <input type="text" name="search" value="{{ $search }}" placeholder="Cari deskripsi, alasan, atau nama petugas..." class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg p-2 text-white text-xs">
      </div>

      <button type="submit" class="px-4 py-2 bg-[#1A1E26] hover:bg-[#252A36] text-white border border-[#2E3544] font-bold rounded-lg transition">
        FILTER
      </button>

      @if($action || $search)
        <a href="{{ route('management.audit.index') }}" class="px-3 py-2 bg-rose-950/40 text-rose-300 border border-rose-500/30 rounded-lg text-xs">
          Reset
        </a>
      @endif

    </form>
  </div>

  <!-- Audit Table -->
  <div class="bg-[#121418] border border-[#1E2229] rounded-xl p-5 space-y-4 font-mono-tech text-xs">
    <div class="flex items-center justify-between pb-3 border-b border-[#1E2229]">
      <div class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-sm bg-[#FF5722]"></span>
        <h3 class="font-mono-tech font-bold text-xs uppercase tracking-wider text-white">
          DAFTAR LOG AKTIVITAS PETUGAS ({{ $logs->total() }} CATATAN)
        </h3>
      </div>
      <span class="text-[10px] text-stone-400">IMMUTABLE LOG RECORD</span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left font-mono-tech text-xs">
        <thead>
          <tr class="text-[10px] text-stone-400 uppercase border-b border-[#1E2229]">
            <th class="pb-3">WAKTU (WIB)</th>
            <th class="pb-3">TINDAKAN</th>
            <th class="pb-3">DESKRIPSI AKTIVITAS</th>
            <th class="pb-3">PETUGAS / AKTOR</th>
            <th class="pb-3">ALASAN / CATATAN</th>
            <th class="pb-3 text-right">IP ADDR</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#1A1E26]">
          @forelse($logs as $log)
            <tr class="hover:bg-[#16191F]">
              <td class="py-3 text-stone-400 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
              <td class="py-3">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#FF5722]/10 text-[#FF5722] border border-[#FF5722]/30">
                  {{ $log->action }}
                </span>
              </td>
              <td class="py-3 text-white font-medium max-w-sm">{{ $log->description }}</td>
              <td class="py-3 text-stone-300">
                <div class="font-bold text-stone-200">{{ $log->actor_name }}</div>
                <div class="text-[9px] text-[#FF5722] uppercase">{{ $log->actor_role }}</div>
              </td>
              <td class="py-3 text-amber-300/90 text-[11px] max-w-xs">
                {{ $log->reason ?? '-' }}
              </td>
              <td class="py-3 text-right text-stone-400 text-[10px]">{{ $log->ip_address }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="py-8 text-center text-stone-400">Tidak ada log aktivitas audit yang ditemukan.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="pt-4 border-t border-[#1E2229]">
      {{ $logs->links() }}
    </div>
  </div>

</div>
@endsection
