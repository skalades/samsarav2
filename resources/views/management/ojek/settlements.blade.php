@extends('management.layout')

@section('title', 'Rekonsiliasi & Payout Ojek — SAMSARA')
@section('page_title', 'Rekonsiliasi & Payout Ojek')
@section('page_subtitle', 'Pencairan hak pembagian pendapatan pengemudi resmi')

@section('content')
<div class="space-y-5">

  <!-- Overview Summary -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
    <x-ui.stat label="Total unsettled payout" :value="'Rp '.number_format($totalUnsettledAmount, 0, ',', '.')" :hint="$driversWithPending->count().' pengemudi menunggu pencairan'" tone="success" icon="cash" />
    <x-ui.stat label="Kebijakan settlement" value="Satu Pintu Kasir" hint="Struk & tanda terima tercetak untuk audit kas" tone="neutral" icon="shield" />
    <x-ui.stat label="Bagi hasil basecamp" value="Rp 5.000 / Trip" hint="Otomatis masuk kas pemeliharaan jalur" tone="info" icon="bolt" />
  </div>

  <!-- Section 1: Drivers with Pending Payouts -->
  <x-ui.card title="Pengemudi Menunggu Pencairan" subtitle="Rekonsiliasi shift kasir" icon="wallet" :padding="false">
    @if($driversWithPending->isEmpty())
      <x-ui.empty icon="check" title="Semua honor trip telah lunas" text="Tidak ada saldo pending saat ini." />
    @else
      {{-- Mobile: kartu --}}
      <div class="lg:hidden divide-y divide-line">
        @foreach($driversWithPending as $driver)
          <div class="p-3.5 space-y-2.5">
            <div class="flex items-start justify-between gap-2">
              <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                  <x-ui.badge tone="warning" size="xs">{{ $driver->registration_number }}</x-ui.badge>
                  <span class="font-code text-xs text-brand font-bold">{{ $driver->plate_number }}</span>
                </div>
                <p class="font-semibold text-sm text-ink mt-1">{{ $driver->name }}</p>
                <p class="text-xs text-muted">{{ $driver->trips->count() }} trip selesai · {{ $driver->phone ?? '-' }}</p>
              </div>
              <p class="font-display font-bold text-base text-emerald-400 tabular-nums shrink-0">
                Rp {{ number_format($driver->trips->sum('driver_share'), 0, ',', '.') }}
              </p>
            </div>
            <x-ui.button variant="success" size="sm" block icon="cash" onclick="openPayoutModal({{ $driver->id }}, '{{ $driver->name }}', '{{ $driver->registration_number }}', {{ $driver->trips->count() }}, {{ $driver->trips->sum('driver_share') }})">
              Cairkan Payout Sekarang
            </x-ui.button>
          </div>
        @endforeach
      </div>

      {{-- Desktop: tabel --}}
      <div class="hidden lg:block overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-raised/40 border-b border-line text-[11px] text-subtle uppercase">
            <tr>
              <th class="py-3 px-4 font-semibold">No Rompi</th>
              <th class="py-3 px-3 font-semibold">Nama Pengemudi</th>
              <th class="py-3 px-3 font-semibold">Plat Motor</th>
              <th class="py-3 px-3 font-semibold">Trip Belum Dibayar</th>
              <th class="py-3 px-3 font-semibold text-right">Total Hak Driver</th>
              <th class="py-3 px-4 font-semibold text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-line">
            @foreach($driversWithPending as $driver)
              <tr class="hover:bg-hover/40">
                <td class="py-3 px-4 whitespace-nowrap"><x-ui.badge tone="warning">{{ $driver->registration_number }}</x-ui.badge></td>
                <td class="py-3 px-3">
                  <div class="font-semibold text-ink">{{ $driver->name }}</div>
                  <div class="text-xs text-muted">{{ $driver->phone ?? '-' }}</div>
                </td>
                <td class="py-3 px-3 font-code font-bold text-brand whitespace-nowrap">{{ $driver->plate_number }}</td>
                <td class="py-3 px-3 text-ink"><strong class="tabular-nums">{{ $driver->trips->count() }}</strong> Trip Selesai</td>
                <td class="py-3 px-3 text-right font-display font-bold text-base text-emerald-400 tabular-nums whitespace-nowrap">
                  Rp {{ number_format($driver->trips->sum('driver_share'), 0, ',', '.') }}
                </td>
                <td class="py-3 px-4 text-right whitespace-nowrap">
                  <x-ui.button variant="success" size="sm" onclick="openPayoutModal({{ $driver->id }}, '{{ $driver->name }}', '{{ $driver->registration_number }}', {{ $driver->trips->count() }}, {{ $driver->trips->sum('driver_share') }})">
                    Cairkan Payout →
                  </x-ui.button>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </x-ui.card>

  <!-- Section 2: Recent Settlement History -->
  <x-ui.card title="Riwayat Pencairan Honor" subtitle="Jejak audit keuangan" icon="receipt" :padding="false">
    @if($recentSettlements->isEmpty())
      <x-ui.empty icon="receipt" title="Belum ada riwayat pencairan" />
    @else
      {{-- Mobile: kartu --}}
      <div class="lg:hidden divide-y divide-line">
        @foreach($recentSettlements as $stl)
          <div class="p-3.5 space-y-2">
            <div class="flex items-start justify-between gap-2">
              <div class="min-w-0">
                <span class="font-code text-xs font-bold text-brand">{{ $stl->settlement_code }}</span>
                <p class="font-semibold text-sm text-ink mt-0.5">{{ $stl->driver?->name }}</p>
                <p class="text-[11px] text-muted">{{ $stl->driver?->registration_number }} · {{ $stl->total_trips }} Trip · {{ $stl->settled_at->format('d/m/Y H:i') }}</p>
              </div>
              <div class="text-right shrink-0">
                <p class="font-bold text-sm text-emerald-400 tabular-nums">Rp {{ number_format($stl->total_amount, 0, ',', '.') }}</p>
                <x-ui.badge size="xs" class="mt-1">{{ strtoupper($stl->payment_method) }}</x-ui.badge>
              </div>
            </div>
            <x-ui.button variant="secondary" size="sm" block icon="printer" :href="route('management.ojek.print_settlement', $stl->id)" target="_blank">Cetak Slip</x-ui.button>
          </div>
        @endforeach
      </div>

      {{-- Desktop: tabel --}}
      <div class="hidden lg:block overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-raised/40 border-b border-line text-[11px] text-subtle uppercase">
            <tr>
              <th class="py-3 px-4 font-semibold">Kode</th>
              <th class="py-3 px-3 font-semibold">Pengemudi</th>
              <th class="py-3 px-3 font-semibold">Trip</th>
              <th class="py-3 px-3 font-semibold text-right">Nominal</th>
              <th class="py-3 px-3 font-semibold">Metode</th>
              <th class="py-3 px-3 font-semibold">Kasir</th>
              <th class="py-3 px-3 font-semibold">Waktu</th>
              <th class="py-3 px-4 font-semibold text-right">Slip</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-line">
            @foreach($recentSettlements as $stl)
              <tr class="hover:bg-hover/40">
                <td class="py-3 px-4 font-code font-bold text-brand whitespace-nowrap">{{ $stl->settlement_code }}</td>
                <td class="py-3 px-3">
                  <div class="font-semibold text-ink">{{ $stl->driver?->name }}</div>
                  <div class="text-xs text-muted">#{{ $stl->driver?->registration_number }} · {{ $stl->driver?->plate_number }}</div>
                </td>
                <td class="py-3 px-3 text-muted tabular-nums">{{ $stl->total_trips }} Trip</td>
                <td class="py-3 px-3 text-right font-bold text-emerald-400 tabular-nums whitespace-nowrap">Rp {{ number_format($stl->total_amount, 0, ',', '.') }}</td>
                <td class="py-3 px-3"><x-ui.badge size="xs">{{ strtoupper($stl->payment_method) }}</x-ui.badge></td>
                <td class="py-3 px-3 text-muted">{{ $stl->cashier?->name }}</td>
                <td class="py-3 px-3 text-muted text-xs whitespace-nowrap tabular-nums">{{ $stl->settled_at->format('d/m/Y H:i') }} WIB</td>
                <td class="py-3 px-4 text-right whitespace-nowrap">
                  <x-ui.button variant="secondary" size="sm" icon="printer" :href="route('management.ojek.print_settlement', $stl->id)" target="_blank">Slip</x-ui.button>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </x-ui.card>

</div>

<x-ui.modal id="modalPayout" title="Konfirmasi Pencairan Honor Driver" eyebrow="Settlement Ojek" maxWidth="sm:max-w-md">
  <form id="formPayout" method="POST" class="space-y-4">
    @csrf
    <div class="p-3.5 bg-raised border border-line-strong rounded-xl space-y-2 text-sm">
      <div class="flex justify-between gap-2">
        <span class="text-muted">Pengemudi</span>
        <span id="payoutDriverName" class="font-semibold text-ink">-</span>
      </div>
      <div class="flex justify-between gap-2">
        <span class="text-muted">Jumlah Trip</span>
        <span id="payoutTotalTrips" class="font-semibold text-ink">-</span>
      </div>
      <div class="flex justify-between gap-2 border-t border-line pt-2">
        <span class="text-muted">Total Dibayarkan</span>
        <span id="payoutAmount" class="font-display font-bold text-emerald-400 text-base tabular-nums">-</span>
      </div>
    </div>

    <div>
      <label class="label">Metode Pembayaran</label>
      <select name="payment_method" class="field">
        <option value="cash">Tunai (Diserahkan di loket kasir)</option>
        <option value="transfer">Transfer Bank / E-Wallet</option>
      </select>
    </div>

    <div>
      <label class="label">Catatan Kasir (Opsional)</label>
      <input type="text" name="notes" placeholder="Contoh: Honor trip shift pagi s/d sore" class="field">
    </div>

    <div class="flex items-center justify-end gap-2 pt-2 border-t border-line">
      <x-ui.button variant="secondary" onclick="document.getElementById('modalPayout').classList.add('hidden')">Batal</x-ui.button>
      <x-ui.button type="submit" variant="success" icon="check">Konfirmasi &amp; Cetak Bukti</x-ui.button>
    </div>
  </form>
</x-ui.modal>

@push('scripts')
<script>
  function openPayoutModal(driverId, driverName, regNumber, tripsCount, amount) {
    document.getElementById('formPayout').action = '/management/ojek/payout/' + driverId;
    document.getElementById('payoutDriverName').innerText = driverName + ' (' + regNumber + ')';
    document.getElementById('payoutTotalTrips').innerText = tripsCount + ' Trip Selesai';
    document.getElementById('payoutAmount').innerText = 'Rp ' + amount.toLocaleString('id-ID');
    document.getElementById('modalPayout').classList.remove('hidden');
  }
</script>
@endpush
@endsection
