@extends('management.layout')

@section('title', 'Master Data Driver Ojek Resmi — SAMSARA')
@section('page_title', 'Master Data Driver Ojek Resmi')
@section('page_subtitle', 'Pendaftaran & inventarisasi pengemudi resmi jalur ' . \App\Models\AppSetting::get('mountain_name', 'Gunung Cikuray'))

@section('content')
<div class="space-y-5">

  <x-ui.page-header title="Pengemudi Ojek Resmi" :subtitle="'Total '.$drivers->count().' pengemudi terdaftar dengan nomor rompi & plat kendaraan legal'" eyebrow="Ojek Resmi">
    @if(in_array(auth()->user()?->role, ['superadmin', 'admin']))
      <x-slot:actions>
        <x-ui.button icon="plus" onclick="document.getElementById('modalAddDriver').classList.remove('hidden')">Tambah Pengemudi</x-ui.button>
      </x-slot:actions>
    @endif
  </x-ui.page-header>

  <x-ui.card :padding="false">
    {{-- Mobile: kartu --}}
    <div class="lg:hidden divide-y divide-line">
      @forelse($drivers as $driver)
        <div class="p-3.5 space-y-2.5">
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-1.5">
                <x-ui.badge tone="warning" size="xs">{{ $driver->registration_number }}</x-ui.badge>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $driver->status_badge['bg'] }}">{{ $driver->status_badge['label'] }}</span>
              </div>
              <p class="font-semibold text-sm text-ink mt-1">{{ $driver->name }}</p>
              <p class="text-xs text-muted">{{ $driver->plate_number }} · {{ $driver->phone ?? '-' }}</p>
              @if($driver->notes)<p class="text-[11px] text-subtle mt-0.5">{{ $driver->notes }}</p>@endif
            </div>
            <div class="text-right shrink-0 tabular-nums">
              <p class="text-xs text-muted">{{ $driver->trips_count }} trip</p>
              <p class="font-bold text-sm {{ $driver->pending_payout > 0 ? 'text-emerald-400' : 'text-subtle' }}">Rp {{ number_format($driver->pending_payout, 0, ',', '.') }}</p>
            </div>
          </div>

          @if(in_array(auth()->user()?->role, ['superadmin', 'admin']))
            <div class="flex items-center gap-2 pt-1">
              <form action="{{ route('management.ojek.toggle_driver', $driver->id) }}" method="POST" class="flex-1">
                @csrf
                <x-ui.button type="submit" variant="secondary" size="sm" block>
                  {{ $driver->status === 'standby' ? 'Set Off-Duty' : 'Set Standby' }}
                </x-ui.button>
              </form>
              <x-ui.button variant="info" size="sm" onclick="openEditModal({{ json_encode($driver) }})">Edit</x-ui.button>
            </div>
          @endif
        </div>
      @empty
        <x-ui.empty icon="users" title="Belum ada driver ojek terdaftar" />
      @endforelse
    </div>

    {{-- Desktop: tabel --}}
    <div class="hidden lg:block overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="bg-raised/40 border-b border-line text-[11px] text-subtle uppercase">
          <tr>
            <th class="py-3 px-4 font-semibold">No Rompi</th>
            <th class="py-3 px-3 font-semibold">Nama Pengemudi</th>
            <th class="py-3 px-3 font-semibold">Plat Nomor</th>
            <th class="py-3 px-3 font-semibold">WhatsApp / HP</th>
            <th class="py-3 px-3 font-semibold">Status</th>
            <th class="py-3 px-3 font-semibold text-right">Trip</th>
            <th class="py-3 px-3 font-semibold text-right">Belum Dicairkan</th>
            <th class="py-3 px-4 font-semibold text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-line">
          @forelse($drivers as $driver)
            <tr class="hover:bg-hover/40">
              <td class="py-3 px-4 whitespace-nowrap"><x-ui.badge tone="warning">{{ $driver->registration_number }}</x-ui.badge></td>
              <td class="py-3 px-3">
                <div class="font-semibold text-ink">{{ $driver->name }}</div>
                @if($driver->notes)<div class="text-xs text-muted">{{ $driver->notes }}</div>@endif
              </td>
              <td class="py-3 px-3 font-code font-bold text-brand whitespace-nowrap">{{ $driver->plate_number }}</td>
              <td class="py-3 px-3 text-muted whitespace-nowrap">{{ $driver->phone ?? '-' }}</td>
              <td class="py-3 px-3 whitespace-nowrap">
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold border {{ $driver->status_badge['bg'] }}">
                  {{ $driver->status_badge['label'] }}
                </span>
              </td>
              <td class="py-3 px-3 text-right text-muted tabular-nums whitespace-nowrap">{{ $driver->trips_count }} trip</td>
              <td class="py-3 px-3 text-right font-bold tabular-nums whitespace-nowrap {{ $driver->pending_payout > 0 ? 'text-emerald-400' : 'text-subtle' }}">
                Rp {{ number_format($driver->pending_payout, 0, ',', '.') }}
              </td>
              <td class="py-3 px-4 text-right whitespace-nowrap">
                @if(in_array(auth()->user()?->role, ['superadmin', 'admin']))
                  <div class="flex items-center justify-end gap-1.5">
                    <form action="{{ route('management.ojek.toggle_driver', $driver->id) }}" method="POST" class="inline">
                      @csrf
                      <x-ui.button type="submit" variant="secondary" size="sm">
                        {{ $driver->status === 'standby' ? 'Set Off-Duty' : 'Set Standby' }}
                      </x-ui.button>
                    </form>
                    <x-ui.button variant="info" size="sm" onclick="openEditModal({{ json_encode($driver) }})">Edit</x-ui.button>
                  </div>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="8"><x-ui.empty icon="users" title="Belum ada driver ojek terdaftar" /></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </x-ui.card>

</div>

<x-ui.modal id="modalAddDriver" title="Tambah Pengemudi Ojek Resmi" eyebrow="Master Driver" maxWidth="sm:max-w-md">
  <form action="{{ route('management.ojek.store_driver') }}" method="POST" class="space-y-4">
    @csrf
    <div>
      <label class="label">Nomor Rompi / Lambung (Unik)</label>
      <input type="text" name="registration_number" placeholder="Contoh: OJK-07" required class="field font-bold uppercase">
    </div>
    <div>
      <label class="label">Nama Lengkap Pengemudi</label>
      <input type="text" name="name" placeholder="Contoh: Mang Dadang Sukmana" required class="field">
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div>
        <label class="label">Plat Nomor Kendaraan</label>
        <input type="text" name="plate_number" placeholder="Z 3182 FA" required class="field font-bold uppercase">
      </div>
      <div>
        <label class="label">Nomor WhatsApp / HP</label>
        <input type="tel" name="phone" placeholder="081234567890" class="field">
      </div>
    </div>
    <div>
      <label class="label">Status Operasional Awal</label>
      <select name="status" class="field">
        <option value="standby">STANDBY (Masuk antrean pangkalan)</option>
        <option value="off_duty">OFF DUTY (Libur / tidak bertugas)</option>
        <option value="suspended">DISUSPEN (Ditangguhkan)</option>
      </select>
    </div>
    <div>
      <label class="label">Catatan Tambahan (Opsional)</label>
      <input type="text" name="notes" placeholder="Contoh: Warga Desa Mekarjaya RT 02" class="field">
    </div>
    <div class="flex items-center justify-end gap-2 pt-2 border-t border-line">
      <x-ui.button variant="secondary" onclick="document.getElementById('modalAddDriver').classList.add('hidden')">Batal</x-ui.button>
      <x-ui.button type="submit" icon="check">Simpan Pengemudi</x-ui.button>
    </div>
  </form>
</x-ui.modal>

<x-ui.modal id="modalEditDriver" title="Edit Pengemudi Ojek" eyebrow="Master Driver" maxWidth="sm:max-w-md">
  <form id="formEditDriver" method="POST" class="space-y-4">
    @csrf
    @method('PUT')
    <div>
      <label class="label">Nomor Rompi / Lambung</label>
      <input type="text" id="editRegNumber" name="registration_number" required class="field font-bold uppercase">
    </div>
    <div>
      <label class="label">Nama Lengkap Pengemudi</label>
      <input type="text" id="editName" name="name" required class="field">
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div>
        <label class="label">Plat Nomor Kendaraan</label>
        <input type="text" id="editPlate" name="plate_number" required class="field font-bold uppercase">
      </div>
      <div>
        <label class="label">Nomor WhatsApp / HP</label>
        <input type="tel" id="editPhone" name="phone" class="field">
      </div>
    </div>
    <div>
      <label class="label">Status Operasional</label>
      <select id="editStatus" name="status" class="field">
        <option value="standby">STANDBY</option>
        <option value="on_trip">ON TRIP</option>
        <option value="off_duty">OFF DUTY</option>
        <option value="suspended">SUSPENDED</option>
      </select>
    </div>
    <div>
      <label class="label">Catatan Tambahan</label>
      <input type="text" id="editNotes" name="notes" class="field">
    </div>
    <div class="flex items-center justify-end gap-2 pt-2 border-t border-line">
      <x-ui.button variant="secondary" onclick="document.getElementById('modalEditDriver').classList.add('hidden')">Batal</x-ui.button>
      <x-ui.button type="submit" variant="info" icon="check">Simpan Perubahan</x-ui.button>
    </div>
  </form>
</x-ui.modal>

@push('scripts')
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
@endpush
@endsection
