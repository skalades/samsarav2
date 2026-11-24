@extends('management.layout')

@section('title', 'Checklist Gate')
@section('page_title', 'Checklist Gate')

@section('content')
<x-ui.page-header title="Checklist pemeriksaan Gate" subtitle="Daftar baku yang muncul di layar Gatekeeper. Alat wajib dicentang, logistik dihitung jumlahnya." eyebrow="Pengaturan" />

<form method="POST" action="{{ route('management.settings.inspection.update') }}" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
  @csrf

  <x-ui.card title="Alat wajib" subtitle="Dicentang saat naik & turun (ada / tidak)" icon="checklist">
    <div id="gearList" class="space-y-2">
      @foreach($template['gear'] as $gear)
        <div class="flex gap-2">
          <input type="text" name="gear[]" value="{{ $gear }}" class="field">
          <button type="button" onclick="this.parentElement.remove()" class="w-11 h-11 shrink-0 rounded-xl text-muted hover:text-rose-300 hover:bg-rose-500/10 flex items-center justify-center" aria-label="Hapus"><x-ui.icon name="trash" class="w-4 h-4" /></button>
        </div>
      @endforeach
    </div>
    <x-ui.button variant="ghost" size="sm" icon="plus" class="mt-2" onclick="addGear()">Tambah alat</x-ui.button>
  </x-ui.card>

  <x-ui.card title="Logistik terhitung" subtitle="Barang yang bisa jadi sampah — dihitung saat naik dan dicocokkan saat turun" icon="box">
    <div id="logisticList" class="space-y-2">
      @foreach($template['logistic'] as $i => $row)
        <div class="flex gap-2">
          <input type="text" name="logistic[{{ $i }}][name]" value="{{ $row['name'] }}" class="field flex-1" placeholder="Nama barang">
          <input type="text" name="logistic[{{ $i }}][unit]" value="{{ $row['unit'] }}" class="field !w-28" placeholder="satuan">
          <button type="button" onclick="this.parentElement.remove()" class="w-11 h-11 shrink-0 rounded-xl text-muted hover:text-rose-300 hover:bg-rose-500/10 flex items-center justify-center" aria-label="Hapus"><x-ui.icon name="trash" class="w-4 h-4" /></button>
        </div>
      @endforeach
    </div>
    <x-ui.button variant="ghost" size="sm" icon="plus" class="mt-2" onclick="addLogistic()">Tambah barang</x-ui.button>
  </x-ui.card>

  <div class="lg:col-span-2 flex justify-end">
    <x-ui.button type="submit" size="lg" icon="check">Simpan checklist</x-ui.button>
  </div>
</form>

@push('scripts')
<script>
  let li = {{ count($template['logistic']) }};
  const del = '<button type="button" onclick="this.parentElement.remove()" class="w-11 h-11 shrink-0 rounded-xl text-muted hover:text-rose-300 flex items-center justify-center">✕</button>';
  function addGear() {
    const d = document.createElement('div'); d.className = 'flex gap-2';
    d.innerHTML = '<input type="text" name="gear[]" class="field" placeholder="Nama alat">' + del;
    document.getElementById('gearList').appendChild(d); d.querySelector('input').focus();
  }
  function addLogistic() {
    const i = li++; const d = document.createElement('div'); d.className = 'flex gap-2';
    d.innerHTML = `<input type="text" name="logistic[${i}][name]" class="field flex-1" placeholder="Nama barang"><input type="text" name="logistic[${i}][unit]" value="pcs" class="field !w-28">` + del;
    document.getElementById('logisticList').appendChild(d); d.querySelector('input').focus();
  }
</script>
@endpush
@endsection
