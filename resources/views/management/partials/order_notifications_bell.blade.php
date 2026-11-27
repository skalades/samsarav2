{{-- Lonceng notifikasi operasional lintas divisi (real-time, lihat order_notifications_script) --}}
@php
  $notifUser = auth()->user();
  $notifRole = $notifUser?->level();
  $notifFooterUrl = match ($notifRole) {
    'gatekeeper' => route('management.gate.index'),
    'ranger' => route('management.ranger.index'),
    default => route('management.desk.index'),
  };
  $notifFooterLabel = match ($notifRole) {
    'gatekeeper' => 'Buka Gate →',
    'ranger' => 'Pos Ranger →',
    default => 'Antrean Kasir →',
  };
@endphp
<details id="orderNotifMenu" class="relative">
  <summary class="relative w-9 h-9 rounded-xl bg-raised ring-1 ring-line text-muted hover:text-ink flex items-center justify-center cursor-pointer" title="Notifikasi operasional" aria-label="Notifikasi operasional">
    <x-ui.icon name="bell" class="w-[18px] h-[18px]" />
    <span id="orderNotifCount" class="hidden absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold items-center justify-center ring-2 ring-canvas">0</span>
  </summary>
  <div class="absolute right-0 top-full mt-2 w-80 max-w-[calc(100vw-1.5rem)] bg-surface ring-1 ring-line-strong rounded-2xl shadow-2xl z-50 overflow-hidden">
    <div class="flex items-center justify-between gap-2 px-3.5 py-2.5 border-b border-line">
      <p class="text-sm font-semibold text-ink">Notifikasi divisi</p>
      <button type="button" id="orderNotifReadAll" class="text-[11px] font-semibold text-brand hover:underline">Tandai dibaca</button>
    </div>
    <div id="orderNotifList" class="max-h-[60vh] overflow-y-auto divide-y divide-line">
      <p data-empty class="px-3.5 py-6 text-center text-xs text-muted">Belum ada notifikasi baru sejak halaman dibuka.</p>
    </div>
    <div class="flex items-center justify-between gap-2 px-3.5 py-2 border-t border-line bg-raised/40">
      <button type="button" id="orderNotifSoundToggle" class="text-[11px] text-muted hover:text-ink">Suara: aktif</button>
      <button type="button" id="orderNotifPermission" class="hidden text-[11px] font-semibold text-brand hover:underline">Aktifkan notifikasi perangkat</button>
      <a href="{{ $notifFooterUrl }}" class="text-[11px] font-semibold text-muted hover:text-ink">{{ $notifFooterLabel }}</a>
    </div>
  </div>
</details>
