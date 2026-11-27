@extends('management.layout')

@section('title', 'Audit Log Operasional')
@section('page_title', 'Audit Log & Jejak Akuntabilitas')
@section('page_subtitle', 'Catatan seluruh tindakan verifikator, kasir, & petugas lapangan')

@section('content')
<div class="space-y-4">

  <!-- Filter & Search Bar -->
  <div class="bg-surface ring-1 ring-line rounded-2xl p-3.5 sm:p-4">
    <form action="{{ route('management.audit.index') }}" method="GET" class="flex flex-col sm:flex-row sm:items-center gap-2.5">
      <div class="sm:w-56">
        <select name="action" onchange="this.form.submit()" class="field">
          <option value="">-- Semua Tindakan --</option>
          @foreach($actions as $act)
            <option value="{{ $act }}" {{ $action === $act ? 'selected' : '' }}>{{ $act }}</option>
          @endforeach
        </select>
      </div>

      <div class="flex-1 min-w-0 relative">
        <x-ui.icon name="search" class="w-4 h-4 text-subtle absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
        <input type="text" name="search" value="{{ $search }}" placeholder="Cari deskripsi, alasan, atau nama petugas..." class="field !pl-10">
      </div>

      <div class="flex items-center gap-2 shrink-0">
        <x-ui.button type="submit" variant="secondary" class="flex-1 sm:flex-none">Filter</x-ui.button>
        @if($action || $search)
          <x-ui.button variant="ghost" :href="route('management.audit.index')">Reset</x-ui.button>
        @endif
      </div>
    </form>
  </div>

  <!-- Audit Table / Mobile Cards -->
  <x-ui.card :title="'Log Aktivitas Petugas ('.$logs->total().')'" subtitle="Immutable log record" icon="shield" :padding="false">
    {{-- Mobile: kartu --}}
    <div class="lg:hidden divide-y divide-line">
      @forelse($logs as $log)
        <div class="p-3.5 space-y-1.5">
          <div class="flex items-center justify-between gap-2">
            <x-ui.badge tone="brand" size="xs">{{ $log->action }}</x-ui.badge>
            <span class="text-[11px] text-subtle tabular-nums">{{ $log->created_at->format('d/m/Y H:i') }}</span>
          </div>
          <p class="text-sm text-ink leading-snug">{{ $log->description }}</p>
          @if($log->reason)
            <p class="text-xs text-amber-300/90 bg-amber-500/10 px-2.5 py-1.5 rounded-lg">Alasan: {{ $log->reason }}</p>
          @endif
          <p class="text-[11px] text-muted">{{ $log->actor_name }} ({{ strtoupper($log->actor_role) }}) · <span class="font-code">{{ $log->ip_address }}</span></p>
        </div>
      @empty
        <x-ui.empty icon="shield" title="Tidak ada log aktivitas audit" />
      @endforelse
    </div>

    {{-- Desktop: tabel --}}
    <div class="hidden lg:block overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="bg-raised/40 border-b border-line text-[11px] text-subtle uppercase">
          <tr>
            <th class="py-3 px-4 font-semibold">Waktu (WIB)</th>
            <th class="py-3 px-3 font-semibold">Tindakan</th>
            <th class="py-3 px-3 font-semibold">Deskripsi Aktivitas</th>
            <th class="py-3 px-3 font-semibold">Petugas</th>
            <th class="py-3 px-3 font-semibold">Alasan / Catatan</th>
            <th class="py-3 px-4 font-semibold text-right">IP Addr</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-line">
          @forelse($logs as $log)
            <tr class="hover:bg-hover/40">
              <td class="py-3 px-4 text-muted text-xs whitespace-nowrap tabular-nums">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
              <td class="py-3 px-3 whitespace-nowrap"><x-ui.badge tone="brand" size="xs">{{ $log->action }}</x-ui.badge></td>
              <td class="py-3 px-3 text-ink max-w-sm">{{ $log->description }}</td>
              <td class="py-3 px-3 whitespace-nowrap">
                <div class="font-semibold text-ink text-xs">{{ $log->actor_name }}</div>
                <div class="text-[10px] text-brand uppercase">{{ $log->actor_role }}</div>
              </td>
              <td class="py-3 px-3 text-amber-300/90 text-xs max-w-xs">{{ $log->reason ?? '—' }}</td>
              <td class="py-3 px-4 text-right font-code text-subtle text-xs whitespace-nowrap">{{ $log->ip_address }}</td>
            </tr>
          @empty
            <tr><td colspan="6"><x-ui.empty icon="shield" title="Tidak ada log aktivitas audit" /></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($logs->hasPages())
      <div class="px-4 py-3 border-t border-line">{{ $logs->links() }}</div>
    @endif
  </x-ui.card>

</div>
@endsection
