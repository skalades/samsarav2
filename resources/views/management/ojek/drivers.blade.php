@extends('management.layout')

@section('title', 'Master Data Driver Ojek Resmi — SAMSARA')
@section('page_title', 'Master Data Driver Ojek Resmi')
@section('page_subtitle', 'Pendaftaran & Inventarisasi Pengemudi Resmi Jalur ' . \App\Models\AppSetting::get('mountain_name', 'Gunung Cikuray'))

@section('content')
<div class="space-y-6">

  <!-- Header & Add Button -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 font-mono-tech">
    <div>
      <h3 class="font-display font-bold text-lg text-white">Daftar Pengemudi Ojek Resmi Terdaftar</h3>
      <p class="text-xs text-stone-400">Total {{ $drivers->count() }} pengemudi resmi terdaftar dengan nomor rompi dan plat kendaraan legal.</p>
    </div>

    @if(in_array(auth()->user()?->role, ['superadmin', 'admin']))
      <button onclick="document.getElementById('modalAddDriver').classList.remove('hidden')" class="px-4 py-2.5 bg-[#FF5722] hover:bg-[#ff6f43] text-black font-bold text-xs rounded-lg transition flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>Tambah Pengemudi Baru</span>
      </button>
    @endif
  </div>

  @if(session('success'))
    <div class="p-3.5 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-400 font-mono-tech text-xs flex items-center gap-2">
      <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
      <span>{{ session('success') }}</span>
    </div>
  @endif

  <!-- Drivers Table -->
  <div class="bg-[#121418] border border-[#1E2229] rounded-xl p-5 font-mono-tech text-xs">
    <div class="overflow-x-auto">
      <table class="w-full text-left">
        <thead>
          <tr class="text-[10px] text-stone-400 uppercase border-b border-[#1E2229]">
            <th class="pb-3">NO ROMPI</th>
            <th class="pb-3">NAMA PENGEMUDI</th>
            <th class="pb-3">PLAT NOMOR</th>
            <th class="pb-3">WHATSAPP / HP</th>
            <th class="pb-3">STATUS OPERASIONAL</th>
            <th class="pb-3">TOTAL TRIP</th>
            <th class="pb-3">BELUM DICAIRKAN</th>
            <th class="pb-3 text-right">AKSI</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[#1A1E26]">
          @forelse($drivers as $driver)
            <tr class="hover:bg-[#16191F]">
              <td class="py-3.5">
                <span class="px-2 py-0.5 rounded bg-[#16191F] border border-[#262B35] text-amber-400 font-bold text-xs">
                  {{ $driver->registration_number }}
                </span>
              </td>
              <td class="py-3.5 font-bold text-white">
                {{ $driver->name }}
                @if($driver->notes)
                  <span class="block text-[10px] font-normal text-stone-400">{{ $driver->notes }}</span>
                @endif
              </td>
              <td class="py-3.5 font-bold text-[#FF5722]">
                {{ $driver->plate_number }}
              </td>
              <td class="py-3.5 text-stone-300">
                {{ $driver->phone ?? '-' }}
              </td>
              <td class="py-3.5">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $driver->status_badge['bg'] }}">
                  {{ $driver->status_badge['label'] }}
                </span>
              </td>
              <td class="py-3.5 text-stone-300">
                {{ $driver->trips_count }} trip
              </td>
              <td class="py-3.5 font-bold {{ $driver->pending_payout > 0 ? 'text-emerald-400' : 'text-stone-400' }}">
                Rp {{ number_format($driver->pending_payout, 0, ',', '.') }}
              </td>
              <td class="py-3.5 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  @if(in_array(auth()->user()?->role, ['superadmin', 'admin']))
                    <form action="{{ route('management.ojek.toggle_driver', $driver->id) }}" method="POST" class="inline">
                      @csrf
                      <button type="submit" class="px-2 py-1 bg-[#16191F] hover:bg-[#202530] border border-[#262B35] rounded text-[10px] text-stone-300 hover:text-white transition">
                        {{ $driver->status === 'standby' ? 'Set Off-Duty' : 'Set Standby' }}
                      </button>
                    </form>

                    <button onclick="openEditModal({{ json_encode($driver) }})" class="px-2 py-1 bg-cyan-950 hover:bg-cyan-900 border border-cyan-500/30 rounded text-[10px] text-cyan-300 font-bold transition">
                      Edit
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="py-8 text-center text-stone-500">
                Belum ada driver ojek resmi terdaftar. Klik "Tambah Pengemudi Baru" di atas.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Modal 1: Tambah Driver Baru -->
<div id="modalAddDriver" class="hidden fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4">
  <div class="bg-[#121418] border border-[#262B35] rounded-xl max-w-md w-full p-6 space-y-4 font-mono-tech text-xs">
    <div class="flex items-center justify-between pb-3 border-b border-[#1E2229]">
      <h4 class="font-display font-bold text-base text-white">Tambah Pengemudi Ojek Resmi</h4>
      <button onclick="document.getElementById('modalAddDriver').classList.add('hidden')" class="text-stone-400 hover:text-white text-lg">✕</button>
    </div>

    <form action="{{ route('management.ojek.store_driver') }}" method="POST" class="space-y-4">
      @csrf
      <div>
        <label class="block text-[10px] text-stone-400 uppercase mb-1">Nomor Rompi / Lambung (Unik):</label>
        <input type="text" name="registration_number" placeholder="Contoh: OJK-07" required class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg p-2.5 text-white font-bold">
      </div>

      <div>
        <label class="block text-[10px] text-stone-400 uppercase mb-1">Nama Lengkap Pengemudi:</label>
        <input type="text" name="name" placeholder="Contoh: Mang Dadang Sukmana" required class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg p-2.5 text-white">
      </div>

      <div>
        <label class="block text-[10px] text-stone-400 uppercase mb-1">Plat Nomor Kendaraan (Motor):</label>
        <input type="text" name="plate_number" placeholder="Contoh: Z 3182 FA" required class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg p-2.5 text-white font-bold uppercase">
      </div>

      <div>
        <label class="block text-[10px] text-stone-400 uppercase mb-1">Nomor WhatsApp / HP:</label>
        <input type="text" name="phone" placeholder="Contoh: 081234567890" class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg p-2.5 text-white">
      </div>

      <div>
        <label class="block text-[10px] text-stone-400 uppercase mb-1">Status Operasional Awal:</label>
        <select name="status" class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg p-2.5 text-white">
          <option value="standby">STANDBY (Langsung masuk antrean pangkalan)</option>
          <option value="off_duty">OFF DUTY (Sedang tidak bertugas / libur)</option>
          <option value="suspended">DISUSPEN (Ditangguhkan)</option>
        </select>
      </div>

      <div>
        <label class="block text-[10px] text-stone-400 uppercase mb-1">Catatan Tambahan (Opsional):</label>
        <input type="text" name="notes" placeholder="Contoh: Warga Desa Mekarjaya RT 02" class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg p-2.5 text-white">
      </div>

      <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#1E2229]">
        <button type="button" onclick="document.getElementById('modalAddDriver').classList.add('hidden')" class="px-3 py-2 bg-[#16191F] text-stone-300 rounded-lg hover:text-white">Batal</button>
        <button type="submit" class="px-4 py-2 bg-[#FF5722] hover:bg-[#ff6f43] text-black font-bold rounded-lg transition">Simpan Pengemudi</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal 2: Edit Driver -->
<div id="modalEditDriver" class="hidden fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4">
  <div class="bg-[#121418] border border-[#262B35] rounded-xl max-w-md w-full p-6 space-y-4 font-mono-tech text-xs">
    <div class="flex items-center justify-between pb-3 border-b border-[#1E2229]">
      <h4 class="font-display font-bold text-base text-white">Edit Pengemudi Ojek</h4>
      <button onclick="document.getElementById('modalEditDriver').classList.add('hidden')" class="text-stone-400 hover:text-white text-lg">✕</button>
    </div>

    <form id="formEditDriver" method="POST" class="space-y-4">
      @csrf
      @method('PUT')
      <div>
        <label class="block text-[10px] text-stone-400 uppercase mb-1">Nomor Rompi / Lambung:</label>
        <input type="text" id="editRegNumber" name="registration_number" required class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg p-2.5 text-white font-bold">
      </div>

      <div>
        <label class="block text-[10px] text-stone-400 uppercase mb-1">Nama Lengkap Pengemudi:</label>
        <input type="text" id="editName" name="name" required class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg p-2.5 text-white">
      </div>

      <div>
        <label class="block text-[10px] text-stone-400 uppercase mb-1">Plat Nomor Kendaraan:</label>
        <input type="text" id="editPlate" name="plate_number" required class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg p-2.5 text-white font-bold uppercase">
      </div>

      <div>
        <label class="block text-[10px] text-stone-400 uppercase mb-1">Nomor WhatsApp / HP:</label>
        <input type="text" id="editPhone" name="phone" class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg p-2.5 text-white">
      </div>

      <div>
        <label class="block text-[10px] text-stone-400 uppercase mb-1">Status Operasional:</label>
        <select id="editStatus" name="status" class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg p-2.5 text-white">
          <option value="standby">STANDBY</option>
          <option value="on_trip">ON TRIP</option>
          <option value="off_duty">OFF DUTY</option>
          <option value="suspended">SUSPENDED</option>
        </select>
      </div>

      <div>
        <label class="block text-[10px] text-stone-400 uppercase mb-1">Catatan Tambahan:</label>
        <input type="text" id="editNotes" name="notes" class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg p-2.5 text-white">
      </div>

      <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#1E2229]">
        <button type="button" onclick="document.getElementById('modalEditDriver').classList.add('hidden')" class="px-3 py-2 bg-[#16191F] text-stone-300 rounded-lg hover:text-white">Batal</button>
        <button type="submit" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-bold rounded-lg transition">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openEditModal(driver) {
    document.getElementById('formEditDriver').action = '/management/ojek/drivers/' + driver.id;
    document.getElementById('editRegNumber').value = driver.registration_number;
    document.getElementById('editName').value = driver.name;
    document.getElementById('editPlate').value = driver.plate_number;
    document.getElementById('editPhone').value = driver.phone || '';
    document.getElementById('editStatus').value = driver.status;
    document.getElementById('editNotes').value = driver.notes || '';
    document.getElementById('modalEditDriver').classList.remove('hidden');
  }
</script>
@endsection
