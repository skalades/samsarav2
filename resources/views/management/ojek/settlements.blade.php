@extends('management.layout')

@section('title', 'Rekonsiliasi & Payout Ojek — SAMSARA')
@section('page_title', 'Rekonsiliasi & Payout Ojek')
@section('page_subtitle', 'Pencairan Hak Pembagian Pendapatan Pengemudi Resmi')

@section('content')
<div class="space-y-6">

  <!-- Overview Summary -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 font-mono-tech">
    <div class="p-4 bg-[#121418] border border-[#1E2229] rounded-xl flex flex-col justify-between">
      <span class="text-[11px] text-stone-400">TOTAL UNSETTLED PAYOUT (HAK DRIVER)</span>
      <div class="mt-2 text-2xl font-display font-black text-emerald-400">
        Rp {{ number_format($totalUnsettledAmount, 0, ',', '.') }}
      </div>
      <span class="text-[10px] text-stone-400 mt-1">{{ $driversWithPending->count() }} Pengemudi menunggu pencairan</span>
    </div>

    <div class="p-4 bg-[#121418] border border-[#1E2229] rounded-xl flex flex-col justify-between">
      <span class="text-[11px] text-stone-400">KEBIJAKAN KASIR / SETTLEMENT</span>
      <div class="mt-2 text-sm text-white font-bold">
        Satu Pintu Kasir Loket
      </div>
      <span class="text-[10px] text-stone-400 mt-1">Struk & tanda terima tercetak untuk audit kas</span>
    </div>

    <div class="p-4 bg-[#121418] border border-[#1E2229] rounded-xl flex flex-col justify-between">
      <span class="text-[11px] text-stone-400">BAGI HASIL BASECAMP (PEMELIHARAAN)</span>
      <div class="mt-2 text-sm text-cyan-400 font-bold">
        Rp 5.000 / Trip
      </div>
      <span class="text-[10px] text-stone-400 mt-1">Otomatis masuk kas pemeliharaan jalur</span>
    </div>
  </div>

  @if(session('success'))
    <div class="p-3.5 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-400 font-mono-tech text-xs flex items-center gap-2">
      <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
      <span>{{ session('success') }}</span>
    </div>
  @endif

  @if(session('error'))
    <div class="p-3.5 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400 font-mono-tech text-xs flex items-center gap-2">
      <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      <span>{{ session('error') }}</span>
    </div>
  @endif

  <!-- Section 1: Drivers with Pending Payouts -->
  <div class="bg-[#121418] border border-[#1E2229] rounded-xl p-5 space-y-4 font-mono-tech text-xs">
    <div class="flex items-center justify-between pb-3 border-b border-[#1E2229]">
      <div class="flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
        <h3 class="font-display font-bold text-sm text-white uppercase tracking-wider">
          Pengemudi Menunggu Pencairan Saldo (Ready to Payout)
        </h3>
      </div>
      <span class="text-[10px] text-stone-400">Rekonsiliasi Shift Kasir</span>
    </div>

    @if($driversWithPending->isEmpty())
      <div class="py-8 text-center text-stone-500">
        <svg class="w-8 h-8 mx-auto mb-2 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p>Semua honor trip pengemudi telah lunas dicairkan. Tidak ada saldo pending.</p>
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="text-[10px] text-stone-400 uppercase border-b border-[#1E2229]">
              <th class="pb-3">NO ROMPI</th>
              <th class="pb-3">NAMA PENGEMUDI</th>
              <th class="pb-3">PLAT MOTOR</th>
              <th class="pb-3">TRIP SELESAI BELUM DIBAYAR</th>
              <th class="pb-3">TOTAL HAK DRIVER</th>
              <th class="pb-3 text-right">AKSI PEMBAYARAN</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#1A1E26]">
            @foreach($driversWithPending as $driver)
              <tr class="hover:bg-[#16191F]">
                <td class="py-3.5">
                  <span class="px-2 py-0.5 rounded bg-[#16191F] border border-[#262B35] text-amber-400 font-bold text-xs">
                    {{ $driver->registration_number }}
                  </span>
                </td>
                <td class="py-3.5 font-bold text-white">
                  {{ $driver->name }}
                  <span class="block text-[10px] text-stone-400 font-normal">{{ $driver->phone ?? '-' }}</span>
                </td>
                <td class="py-3.5 font-bold text-[#FF5722]">
                  {{ $driver->plate_number }}
                </td>
                <td class="py-3.5 text-stone-300">
                  <span class="font-bold text-white">{{ $driver->trips->count() }}</span> Trip Selesai
                </td>
                <td class="py-3.5 text-base font-bold text-emerald-400 font-display">
                  Rp {{ number_format($driver->trips->sum('driver_share'), 0, ',', '.') }}
                </td>
                <td class="py-3.5 text-right">
                  <button onclick="openPayoutModal({{ $driver->id }}, '{{ $driver->name }}', '{{ $driver->registration_number }}', {{ $driver->trips->count() }}, {{ $driver->trips->sum('driver_share') }})" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-lg transition shadow-lg shadow-emerald-600/20">
                    Cairkan Payout Sekarang →
                  </button>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

  <!-- Section 2: Recent Settlement History -->
  <div class="bg-[#121418] border border-[#1E2229] rounded-xl p-5 space-y-4 font-mono-tech text-xs">
    <div class="flex items-center justify-between pb-3 border-b border-[#1E2229]">
      <h3 class="font-display font-bold text-sm text-white uppercase tracking-wider">
        Riwayat Pencairan Honor (Settlement Records)
      </h3>
      <span class="text-[10px] text-stone-400">Jejak Audit Keuangan</span>
    </div>

    @if($recentSettlements->isEmpty())
      <div class="py-6 text-center text-stone-500">
        Belum ada riwayat pencairan payout yang tercatat.
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="text-[10px] text-stone-400 uppercase border-b border-[#1E2229]">
              <th class="pb-2">KODE SETTLEMENT</th>
              <th class="pb-2">PENGEMUDI</th>
              <th class="pb-2">JUMLAH TRIP</th>
              <th class="pb-2">NOMINAL DICAIRKAN</th>
              <th class="pb-2">METODE</th>
              <th class="pb-2">KASIR PETUGAS</th>
              <th class="pb-2">WAKTU</th>
              <th class="pb-2 text-right">STRUK</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#1A1E26]">
            @foreach($recentSettlements as $stl)
              <tr class="hover:bg-[#16191F]">
                <td class="py-3 font-bold text-[#FF5722]">{{ $stl->settlement_code }}</td>
                <td class="py-3 text-white">
                  <div class="font-bold">{{ $stl->driver?->name }}</div>
                  <div class="text-[10px] text-stone-400">#{{ $stl->driver?->registration_number }} • {{ $stl->driver?->plate_number }}</div>
                </td>
                <td class="py-3 text-stone-300">{{ $stl->total_trips }} Trip</td>
                <td class="py-3 font-bold text-emerald-400">Rp {{ number_format($stl->total_amount, 0, ',', '.') }}</td>
                <td class="py-3 uppercase text-stone-300">
                  <span class="px-2 py-0.5 rounded bg-[#16191F] border border-[#262B35] text-[10px]">
                    {{ $stl->payment_method }}
                  </span>
                </td>
                <td class="py-3 text-stone-300">{{ $stl->cashier?->name }}</td>
                <td class="py-3 text-stone-400">{{ $stl->settled_at->format('d/m/Y H:i') }} WIB</td>
                <td class="py-3 text-right">
                  <a href="{{ route('management.ojek.print_settlement', $stl->id) }}" target="_blank" class="px-2 py-1 bg-[#16191F] hover:bg-[#202530] border border-[#262B35] text-stone-300 hover:text-white rounded text-[10px] font-bold transition">
                    [PRINT] Slip
                  </a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

</div>

<!-- Modal Payout Confirmation -->
<div id="modalPayout" class="hidden fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4">
  <div class="bg-[#121418] border border-[#262B35] rounded-xl max-w-md w-full p-6 space-y-4 font-mono-tech text-xs">
    <div class="flex items-center justify-between pb-3 border-b border-[#1E2229]">
      <h4 class="font-display font-bold text-base text-white">Konfirmasi Pencairan Honor Driver</h4>
      <button onclick="document.getElementById('modalPayout').classList.add('hidden')" class="text-stone-400 hover:text-white text-lg">✕</button>
    </div>

    <form id="formPayout" method="POST" class="space-y-4">
      @csrf
      <div class="p-3 bg-[#16191F] border border-[#262B35] rounded-lg space-y-2">
        <div class="flex justify-between">
          <span class="text-stone-400">Pengemudi:</span>
          <span id="payoutDriverName" class="font-bold text-white">-</span>
        </div>
        <div class="flex justify-between">
          <span class="text-stone-400">Jumlah Trip:</span>
          <span id="payoutTotalTrips" class="font-bold text-white">-</span>
        </div>
        <div class="flex justify-between border-t border-[#1E2229] pt-2">
          <span class="text-stone-400">Total Yang Dibayarkan:</span>
          <span id="payoutAmount" class="font-bold text-emerald-400 text-sm font-display">-</span>
        </div>
      </div>

      <div>
        <label class="block text-[10px] text-stone-400 uppercase mb-1">Metode Pembayaran:</label>
        <select name="payment_method" class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg p-2.5 text-white">
          <option value="cash">TUNAI (Diserahkan langsung di loket kasir)</option>
          <option value="transfer">TRANSFER BANK / E-WALLET</option>
        </select>
      </div>

      <div>
        <label class="block text-[10px] text-stone-400 uppercase mb-1">Catatan Kasir (Opsional):</label>
        <input type="text" name="notes" placeholder="Contoh: Honor trip shift pagi s/d sore" class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg p-2.5 text-white">
      </div>

      <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#1E2229]">
        <button type="button" onclick="document.getElementById('modalPayout').classList.add('hidden')" class="px-3 py-2 bg-[#16191F] text-stone-300 rounded-lg hover:text-white">Batal</button>
        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg transition">Konfirmasi & Cetak Bukti</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openPayoutModal(driverId, driverName, regNumber, tripsCount, amount) {
    document.getElementById('formPayout').action = '/management/ojek/payout/' + driverId;
    document.getElementById('payoutDriverName').innerText = driverName + ' (' + regNumber + ')';
    document.getElementById('payoutTotalTrips').innerText = tripsCount + ' Trip Selesai';
    document.getElementById('payoutAmount').innerText = 'Rp ' + amount.toLocaleString('id-ID');
    document.getElementById('modalPayout').classList.remove('hidden');
  }
</script>
@endsection
