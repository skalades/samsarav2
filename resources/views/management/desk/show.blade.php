@extends('management.layout')

@section('title', 'Meja Kasir · '.$registration->registration_code)
@section('page_title', $registration->registration_code)
@section('page_subtitle', $registration->leader_name.' · '.$registration->statusLabel())

@section('content')
@php
  $status = $registration->status;
  $presentCount = $registration->members->where('is_checked_in', true)->count();
  $rp = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');
@endphp

<a href="{{ route('management.desk.index', $status === 'returned' || $status === 'completed' ? ['tab' => 'turun'] : []) }}" class="inline-flex items-center gap-1.5 text-sm text-muted hover:text-ink mb-3">
  <x-ui.icon name="chevron-right" class="w-4 h-4 rotate-180" /> Antrean dari Gate
</a>

{{-- Langsung cetak setelah aksi --}}
@if(session('print_ascent') || session('print_descent'))
  <div class="mb-4 p-4 rounded-2xl bg-brand/10 ring-1 ring-brand/30">
    <p class="text-sm font-semibold text-ink mb-3 flex items-center gap-2"><x-ui.icon name="printer" class="w-5 h-5 text-brand" /> Cetak sekarang & serahkan ke ketua rombongan</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
      @if(session('print_ascent'))
        <x-ui.button size="lg" icon="printer" :href="route('management.gate.print_climber_pass', $registration->id).'?print=1'" target="_blank">Surat jalan</x-ui.button>
        @if($registration->ojek_count > 0)
          <x-ui.button size="lg" icon="bolt" :href="route('management.gate.print_ojek_voucher', $registration->id)" target="_blank">Voucher ojek</x-ui.button>
        @endif
      @else
        <x-ui.button size="lg" icon="printer" :href="route('management.gate.print_parking_pass', $registration->id)" target="_blank">Izin keluar parkir</x-ui.button>
      @endif
    </div>
  </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
  <div class="lg:col-span-2 space-y-4">

    {{-- Kartu rombongan --}}
    <x-ui.card>
      <div class="flex items-start gap-4">
        @if($registration->photoUrl())
          <a href="{{ $registration->photoUrl() }}" target="_blank" class="shrink-0">
            <img src="{{ $registration->photoUrl() }}" alt="Foto tim" class="w-24 h-24 sm:w-32 sm:h-24 rounded-xl object-cover ring-1 ring-line">
          </a>
        @endif
        <div class="min-w-0 flex-1">
          <div class="flex flex-wrap items-center gap-2">
            <h2 class="font-display font-bold text-lg text-ink">{{ $registration->leader_name }}</h2>
            <x-ui.badge :tone="$registration->statusTone()" dot>{{ $registration->statusLabel() }}</x-ui.badge>
          </div>
          <p class="font-code text-sm text-muted mt-0.5">{{ $registration->registration_code }}</p>
          <dl class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3 text-sm">
            <div><dt class="text-[11px] text-subtle">Naik</dt><dd class="text-ink font-semibold">{{ $registration->hiking_date?->translatedFormat('d M Y') }}</dd></div>
            <div><dt class="text-[11px] text-subtle">Kategori</dt><dd class="text-ink font-semibold">{{ $registration->isTektok() ? 'Tektok' : 'Camp' }}</dd></div>
            <div><dt class="text-[11px] text-subtle">Hadir</dt><dd class="text-ink font-semibold">{{ $presentCount }} / {{ $registration->total_climbers }} orang</dd></div>
            <div><dt class="text-[11px] text-subtle">Telepon</dt><dd class="text-ink font-semibold">{{ $registration->leader_phone }}</dd></div>
          </dl>
        </div>
      </div>
    </x-ui.card>

    {{-- Hasil pemeriksaan Gate --}}
    <x-ui.card title="Hasil pemeriksaan Gate" icon="checklist"
      :subtitle="'Naik: '.($registration->gateUpOfficer?->name ?? '-').' · '.($registration->gate_up_at?->format('d M H:i') ?? '-').($registration->gate_down_at ? ' — Turun: '.($registration->gateDownOfficer?->name ?? '-').' · '.$registration->gate_down_at->format('d M H:i') : '')">
      @if($logisticItems->isEmpty() && $gearItems->isEmpty())
        <x-ui.empty icon="checklist" title="Belum ada data pemeriksaan" />
      @else
        @if($logisticItems->isNotEmpty())
          <p class="text-xs font-semibold text-muted mb-2">Logistik terhitung</p>
          <div class="rounded-xl ring-1 ring-line overflow-hidden mb-4">
            <table class="w-full text-sm">
              <thead class="bg-raised/60 text-[11px] text-subtle">
                <tr><th class="text-left font-semibold px-3 py-2">Barang</th><th class="text-right font-semibold px-3 py-2">Naik</th><th class="text-right font-semibold px-3 py-2">Turun</th></tr>
              </thead>
              <tbody class="divide-y divide-line">
                @foreach($logisticItems as $item)
                  <tr>
                    <td class="px-3 py-2 text-ink">{{ $item->name }}</td>
                    <td class="px-3 py-2 text-right tabular-nums text-muted">{{ $item->qty_up }} {{ $item->unit }}</td>
                    <td class="px-3 py-2 text-right tabular-nums">
                      @if($item->qty_down === null)
                        <span class="text-subtle">—</span>
                      @elseif($item->missingQty() > 0)
                        <span class="text-rose-300 font-semibold">{{ $item->qty_down }} (−{{ $item->missingQty() }})</span>
                      @else
                        <span class="text-emerald-300 font-semibold">{{ $item->qty_down }}</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
        @if($gearItems->isNotEmpty())
          <p class="text-xs font-semibold text-muted mb-2">Alat wajib</p>
          <div class="flex flex-wrap gap-1.5">
            @foreach($gearItems as $item)
              <x-ui.badge :tone="$item->checked_up ? ($item->checked_down === false ? 'danger' : 'success') : 'neutral'">
                {{ $item->checked_up ? '✓' : '–' }} {{ $item->name }}
              </x-ui.badge>
            @endforeach
          </div>
        @endif
      @endif

      @if($registration->gate_up_notes || $registration->gate_down_notes)
        <div class="mt-4 space-y-2 text-sm">
          @if($registration->gate_up_notes)<p class="p-3 rounded-xl bg-raised/60 ring-1 ring-line text-muted"><span class="text-ink font-semibold">Catatan naik:</span> {{ $registration->gate_up_notes }}</p>@endif
          @if($registration->gate_down_notes)<p class="p-3 rounded-xl {{ $registration->gate_down_clear ? 'bg-raised/60 ring-1 ring-line text-muted' : 'bg-rose-500/10 ring-1 ring-rose-500/25 text-rose-200' }} whitespace-pre-line"><span class="font-semibold">Catatan turun:</span> {{ $registration->gate_down_notes }}</p>@endif
        </div>
      @endif
    </x-ui.card>

    {{-- Sewa alat aktif --}}
    @if(in_array($status, ['returned', 'completed']) && $rentals->isNotEmpty())
      <x-ui.card title="Sewa alat belum kembali" subtitle="Terima alat & cek kerusakan di modul Sewa Alat" icon="box">
        <ul class="space-y-2 text-sm">
          @foreach($rentals as $rental)
            <li class="flex items-center justify-between p-3 rounded-xl bg-raised/60 ring-1 ring-line">
              <span class="text-ink">{{ $rental->item?->name }} × {{ $rental->quantity }}</span>
              <x-ui.badge tone="warning" size="xs">Disewa</x-ui.badge>
            </li>
          @endforeach
        </ul>
        <x-ui.button variant="secondary" size="sm" class="mt-3" icon="box" :href="route('management.inventory.index')">Buka Sewa Alat</x-ui.button>
      </x-ui.card>
    @endif
  </div>

  {{-- Panel aksi --}}
  <div class="space-y-4">
    @if($status === 'gate_cleared')
      <x-ui.card title="Berangkatkan rombongan" subtitle="Status menjadi Sedang Mendaki setelah dikonfirmasi" icon="arrow-up">
        <form method="POST" action="{{ route('management.desk.release', $registration->id) }}" class="space-y-4">
          @csrf
          @if($registration->ojek_count > 0)
            <div>
              <p class="label">Driver ojek ({{ $registration->ojek_count }} perjalanan)</p>
              <div class="space-y-2">
                @for($i = 0; $i < $registration->ojek_count; $i++)
                  <select name="ojek_driver_ids[]" class="field">
                    <option value="">Tugaskan nanti di Dispatch Ojek</option>
                    @foreach($standbyDrivers as $driver)
                      <option value="{{ $driver->id }}" @selected(isset($standbyDrivers[$i]) && $standbyDrivers[$i]->id === $driver->id)>{{ $driver->name }} · {{ $driver->plate_number }}</option>
                    @endforeach
                  </select>
                @endfor
              </div>
              @if($standbyDrivers->isEmpty())<p class="text-xs text-amber-300 mt-1.5">Tidak ada driver standby saat ini.</p>@endif
            </div>
          @endif
          <ul class="text-xs text-muted space-y-1.5">
            <li class="flex gap-2"><x-ui.icon name="check" class="w-4 h-4 text-emerald-400 shrink-0" /> Surat Jalan pendaki (struk saku)</li>
            @if($registration->ojek_count > 0)<li class="flex gap-2"><x-ui.icon name="check" class="w-4 h-4 text-emerald-400 shrink-0" /> Voucher ojek resmi</li>@endif
          </ul>
          <x-ui.button type="submit" size="lg" icon="arrow-up" block>Berangkatkan & cetak</x-ui.button>
        </form>
      </x-ui.card>
    @elseif($status === 'returned')
      <x-ui.card title="Selesaikan pendakian" subtitle="KTP ketua otomatis dimusnahkan setelah selesai (UU PDP)" icon="check">
        <div class="p-3 rounded-xl mb-4 {{ $registration->gate_down_clear ? 'bg-emerald-500/10 ring-1 ring-emerald-500/25 text-emerald-200' : 'bg-rose-500/10 ring-1 ring-rose-500/25 text-rose-200' }} text-sm">
          {{ $registration->gate_down_clear ? 'Gate: logistik & alat kembali lengkap.' : 'Gate: ada selisih logistik/alat. Catat tindak lanjut di bawah.' }}
        </div>
        @if((float) $registration->trash_deposit_amount > 0)
          <p class="text-sm text-muted mb-3">Deposit sampah {{ $rp($registration->trash_deposit_amount) }} akan <strong class="text-ink">{{ $registration->gate_down_clear ? 'dikembalikan' : 'hangus' }}</strong>.</p>
        @endif
        <form method="POST" action="{{ route('management.desk.finalize', $registration->id) }}" class="space-y-3">
          @csrf
          <div>
            <label class="label" for="notes">Catatan kasir (opsional)</label>
            <textarea id="notes" name="notes" rows="2" class="field" placeholder="mis. alat sewa sudah kembali, denda dibayar"></textarea>
          </div>
          <x-ui.button type="submit" size="lg" variant="success" icon="check" block>Selesaikan & izin parkir</x-ui.button>
        </form>
      </x-ui.card>
    @endif

    {{-- Dokumen --}}
    <x-ui.card title="Dokumen" icon="printer">
      <div class="space-y-2">
        @if(in_array($status, ['checked_in', 'returned', 'completed']))
          <x-ui.button :variant="session('print_ascent') ? 'primary' : 'secondary'" icon="printer" :href="route('management.gate.print_climber_pass', $registration->id).'?print=1'" target="_blank" block>Cetak surat jalan</x-ui.button>
          @if($registration->ojek_count > 0)
            <x-ui.button :variant="session('print_ascent') ? 'primary' : 'secondary'" icon="bolt" :href="route('management.gate.print_ojek_voucher', $registration->id)" target="_blank" block>Cetak voucher ojek</x-ui.button>
          @endif
        @endif
        @if($status === 'completed')
          <x-ui.button :variant="session('print_descent') ? 'primary' : 'secondary'" icon="printer" :href="route('management.gate.print_parking_pass', $registration->id)" target="_blank" block>Cetak izin keluar parkir</x-ui.button>
        @endif
        <x-ui.button variant="ghost" icon="doc" :href="route('management.simaksi.ticket_pdf', $registration->id)" block>Unduh e-SIMAKSI (PDF)</x-ui.button>
        <x-ui.button variant="ghost" icon="ticket" :href="route('management.simaksi.show', $registration->id)" block>Detail SIMAKSI</x-ui.button>
      </div>
    </x-ui.card>
  </div>
</div>
@endsection
