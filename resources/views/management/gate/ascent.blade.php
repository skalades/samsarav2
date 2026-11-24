@extends('management.layout')

@section('title', 'Periksa naik · '.$registration->registration_code)
@section('page_title', 'Pemeriksaan naik')
@section('page_subtitle', $registration->registration_code.' · '.$registration->leader_name)

@section('content')
@php
  $members = $registration->members->sortByDesc('is_leader')->values();
  $existingPhoto = $registration->group_photo_path ? asset('storage/'.$registration->group_photo_path) : null;
  $oldGear = old('gear');
  $oldLogistics = old('logistics');
  $logisticRows = $oldLogistics ?: collect($template['logistic'])->map(fn ($r) => $r + ['qty' => 0])->all();
@endphp

<a href="{{ route('management.gate.index') }}" class="inline-flex items-center gap-1.5 text-sm text-muted hover:text-ink mb-3">
  <x-ui.icon name="chevron-right" class="w-4 h-4 rotate-180" /> Gate
</a>

<form id="ascentForm" method="POST" action="{{ route('management.gate.ascent.submit', $registration->id) }}" enctype="multipart/form-data" class="max-w-3xl mx-auto space-y-4">
  @csrf

  {{-- Ringkasan rombongan --}}
  <div class="rounded-2xl bg-gradient-to-br from-brand/15 to-surface ring-1 ring-brand/25 p-4">
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0">
        <p class="font-code text-xs text-brand-soft">{{ $registration->registration_code }}</p>
        <h1 class="font-display font-bold text-xl text-ink mt-0.5 truncate">{{ $registration->leader_name }}</h1>
        <p class="text-sm text-muted mt-0.5">{{ $registration->hiking_date?->translatedFormat('l, d M Y') }} · {{ $registration->isTektok() ? 'Tektok' : 'Camp' }}</p>
      </div>
      <div class="text-right shrink-0">
        <p class="font-display font-bold text-3xl text-ink leading-none">{{ $registration->total_climbers }}</p>
        <p class="text-xs text-muted">pendaki</p>
      </div>
    </div>
    <div class="flex flex-wrap gap-1.5 mt-3">
      @if($registration->ojek_count > 0)<x-ui.badge tone="warning">Ojek {{ $registration->ojek_count }}x</x-ui.badge>@endif
      @if($registration->tent_count > 0)<x-ui.badge tone="info">Sewa tenda {{ $registration->tent_count }}</x-ui.badge>@endif
      @php $regMedical = \App\Models\SimaksiRegistration::meaningfulMedical($registration->medical_conditions) ?? $registration->members->map(fn ($m) => \App\Models\SimaksiRegistration::meaningfulMedical($m->medical_conditions))->filter()->first(); @endphp
      @if($regMedical)<x-ui.badge tone="danger">Catatan medis: {{ \Illuminate\Support\Str::limit($regMedical, 40) }}</x-ui.badge>@endif
    </div>
  </div>

  {{-- 1. Anggota --}}
  <x-ui.card id="sectionMembers" title="1 · Anggota hadir" subtitle="Hapus centang anggota yang tidak datang" icon="users">
    <x-slot:actions><span id="presentCounter" class="text-sm font-semibold text-ink tabular-nums"></span></x-slot:actions>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
      @foreach($members as $m)
        <label class="flex items-center gap-3 p-3 rounded-xl bg-raised/60 ring-1 ring-line has-[:checked]:ring-emerald-500/40 has-[:checked]:bg-emerald-500/5 cursor-pointer select-none">
          <input type="checkbox" name="present_members[]" value="{{ $m->id }}" class="member-check w-5 h-5 shrink-0 rounded accent-emerald-500" @checked(old('present_members') ? in_array($m->id, old('present_members')) : true)>
          <span class="min-w-0 flex-1">
            <span class="block text-sm font-semibold text-ink truncate">{{ $m->full_name }}</span>
            <span class="block text-[11px] text-muted break-words">{{ $m->is_leader ? 'Ketua · ' : '' }}{{ $m->gender === 'P' || $m->gender === 'female' ? 'Perempuan' : 'Laki-laki' }}{{ \App\Models\SimaksiRegistration::meaningfulMedical($m->medical_conditions) ? ' · medis: '.$m->medical_conditions : '' }}</span>
          </span>
        </label>
      @endforeach
    </div>
  </x-ui.card>

  {{-- 2. Foto tim --}}
  <x-ui.card id="sectionPhoto" title="2 · Foto tim" subtitle="Cocokkan wajah dengan foto unggahan, atau ambil foto baru di gerbang" icon="camera">
    <div id="photoRequiredNotice" class="hidden mb-3 flex items-center gap-2 rounded-xl bg-rose-500/10 ring-1 ring-rose-500/30 px-3 py-2 text-xs font-medium text-rose-200">
      <x-ui.icon name="alert" class="w-4 h-4 text-rose-400 shrink-0" />
      <span>Foto tim wajib: centang "Foto unggahan sesuai" atau ambil foto baru.</span>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      @if($existingPhoto)
        <div>
          <a href="{{ $existingPhoto }}" target="_blank"><img src="{{ $existingPhoto }}" alt="Foto unggahan pendaki" class="w-full aspect-video object-cover rounded-xl ring-1 ring-line"></a>
          <label class="mt-2 flex items-center gap-3 p-3 rounded-xl bg-raised/60 ring-1 ring-line has-[:checked]:ring-emerald-500/40 cursor-pointer">
            <input type="checkbox" name="photo_verified" value="1" class="w-5 h-5 shrink-0 accent-emerald-500" @checked(old('photo_verified'))>
            <span class="text-sm text-ink font-semibold">Foto unggahan sesuai dengan rombongan</span>
          </label>
        </div>
      @endif
      <label class="relative flex flex-col items-center justify-center gap-2 min-h-[168px] rounded-xl border-2 border-dashed border-line-strong bg-canvas hover:border-brand/50 cursor-pointer overflow-hidden {{ $existingPhoto ? '' : 'sm:col-span-2' }}">
        <img id="teamPhotoPreview" class="hidden absolute inset-0 w-full h-full object-cover" alt="Pratinjau foto tim">
        <span id="teamPhotoRetakeBadge" class="hidden absolute bottom-2.5 right-2.5 z-10 px-2.5 py-1 rounded-lg bg-black/75 backdrop-blur-sm text-[11px] font-semibold text-white ring-1 ring-white/20 pointer-events-none">Ketuk untuk ganti</span>
        <span id="teamPhotoPlaceholder" class="flex flex-col items-center gap-2 text-center px-4">
          <span class="w-12 h-12 rounded-2xl bg-brand/15 text-brand flex items-center justify-center"><x-ui.icon name="camera" class="w-6 h-6" /></span>
          <span class="text-sm font-semibold text-ink">{{ $existingPhoto ? 'Ambil foto baru (opsional)' : 'Ambil foto tim' }}</span>
          <span class="text-xs text-muted">Kamera ponsel akan terbuka</span>
        </span>
        <input type="file" name="team_photo" accept="image/*" capture="environment" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10" onchange="previewTeamPhoto(this)">
      </label>
    </div>
  </x-ui.card>

  {{-- 3. Alat wajib --}}
  <x-ui.card title="3 · Alat wajib" subtitle="Ketuk untuk menandai alat yang dibawa" icon="checklist">
    <x-slot:actions><span id="gearCounter" class="text-sm font-semibold text-ink tabular-nums"></span></x-slot:actions>
    <div class="flex flex-wrap gap-2" id="gearList">
      @foreach($template['gear'] as $i => $gear)
        @php $checked = $oldGear ? !empty($oldGear[$i]['checked']) : false; @endphp
        <label class="relative inline-flex cursor-pointer select-none">
          <input type="hidden" name="gear[{{ $i }}][name]" value="{{ $gear }}">
          <input type="checkbox" name="gear[{{ $i }}][checked]" value="1" class="peer absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10 gear-check" @checked($checked)>
          <span class="inline-flex items-center gap-2 h-11 px-3.5 sm:px-4 rounded-xl text-sm font-medium ring-1 ring-line-strong bg-raised text-muted peer-checked:bg-emerald-500/15 peer-checked:text-emerald-200 peer-checked:ring-emerald-500/40 peer-checked:[&>svg]:opacity-100 peer-checked:[&>svg]:text-emerald-400 transition pointer-events-none">
            <x-ui.icon name="check" class="w-4 h-4 opacity-35 transition" /> {{ $gear }}
          </span>
        </label>
      @endforeach
    </div>
  </x-ui.card>

  {{-- 4. Logistik --}}
  <x-ui.card title="4 · Logistik terhitung" subtitle="Hitung barang yang bisa jadi sampah. Akan dicocokkan lagi saat turun." icon="box">
    <x-slot:actions><span id="logisticCounter" class="text-sm font-semibold text-ink tabular-nums"></span></x-slot:actions>
    <div id="logisticList" class="space-y-2">
      @foreach($logisticRows as $i => $row)
        <div class="logistic-row flex items-center gap-2 p-2 pl-3 rounded-xl bg-raised/60 ring-1 ring-line">
          <div class="min-w-0 flex-1">
            <input type="text" name="logistics[{{ $i }}][name]" value="{{ $row['name'] }}" class="w-full bg-transparent text-sm font-semibold text-ink outline-none truncate" placeholder="Nama barang">
            <input type="text" name="logistics[{{ $i }}][unit]" value="{{ $row['unit'] ?? 'pcs' }}" class="w-full bg-transparent text-[11px] text-muted outline-none" placeholder="satuan">
          </div>
          <div class="flex items-center shrink-0 rounded-xl bg-canvas ring-1 ring-line-strong">
            <button type="button" class="w-10 h-10 sm:w-11 sm:h-11 text-xl text-muted hover:text-ink" onclick="stepQty(this, -1)" aria-label="Kurangi">−</button>
            <input type="number" inputmode="numeric" min="0" name="logistics[{{ $i }}][qty]" value="{{ (int) ($row['qty'] ?? 0) }}" class="qty-input w-10 sm:w-12 h-10 sm:h-11 bg-transparent text-center font-display font-bold text-base sm:text-lg text-ink outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none" oninput="updateCounters()">
            <button type="button" class="w-10 h-10 sm:w-11 sm:h-11 text-xl text-muted hover:text-ink" onclick="stepQty(this, 1)" aria-label="Tambah">+</button>
          </div>
        </div>
      @endforeach
    </div>
    <x-ui.button variant="ghost" size="sm" icon="plus" class="mt-2" onclick="addLogisticRow()">Tambah barang lain</x-ui.button>
  </x-ui.card>

  {{-- 5. Catatan --}}
  <x-ui.card title="5 · Catatan untuk kasir" icon="doc">
    <textarea name="notes" rows="2" class="field" placeholder="mis. 1 anggota tidak bawa headlamp, sudah diingatkan">{{ old('notes') }}</textarea>
  </x-ui.card>

  {{-- Aksi --}}
  <div class="sticky bottom-0 z-20 px-3 py-3 rounded-2xl lg:rounded-none lg:px-0 bg-canvas/95 backdrop-blur ring-1 ring-line lg:ring-0 lg:bg-transparent">
    <div class="flex items-center gap-3">
      <div class="hidden sm:block flex-1 text-xs text-muted">Setelah lolos, rombongan diarahkan ke kasir untuk mengambil surat jalan{{ $registration->ojek_count > 0 ? ' & voucher ojek' : '' }}.</div>
      <x-ui.button type="submit" size="xl" variant="success" icon="check" class="w-full sm:w-auto !text-sm sm:!text-base !px-4 sm:!px-6">Lolos Gate · kirim ke kasir</x-ui.button>
    </div>
  </div>
</form>

@push('scripts')
<script>
  let logisticIndex = {{ count($logisticRows) }};
  let previewObjectUrl = null;

  function stepQty(btn, delta) {
    const input = btn.parentElement.querySelector('.qty-input');
    input.value = Math.max(0, (parseInt(input.value) || 0) + delta);
    updateCounters();
  }

  function addLogisticRow() {
    const i = logisticIndex++;
    const row = document.createElement('div');
    row.className = 'logistic-row flex items-center gap-2 p-2 pl-3 rounded-xl bg-raised/60 ring-1 ring-brand/30';
    row.innerHTML = `
      <div class="min-w-0 flex-1">
        <input type="text" name="logistics[${i}][name]" class="w-full bg-transparent text-sm font-semibold text-ink outline-none truncate" placeholder="Nama barang">
        <input type="text" name="logistics[${i}][unit]" value="pcs" class="w-full bg-transparent text-[11px] text-muted outline-none" placeholder="satuan">
      </div>
      <div class="flex items-center shrink-0 rounded-xl bg-canvas ring-1 ring-line-strong">
        <button type="button" class="w-10 h-10 sm:w-11 sm:h-11 text-xl text-muted hover:text-ink" onclick="stepQty(this, -1)" aria-label="Kurangi">−</button>
        <input type="number" inputmode="numeric" min="0" name="logistics[${i}][qty]" value="1" class="qty-input w-10 sm:w-12 h-10 sm:h-11 bg-transparent text-center font-display font-bold text-base sm:text-lg text-ink outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none" oninput="updateCounters()">
        <button type="button" class="w-10 h-10 sm:w-11 sm:h-11 text-xl text-muted hover:text-ink" onclick="stepQty(this, 1)" aria-label="Tambah">+</button>
      </div>`;
    document.getElementById('logisticList').appendChild(row);
    row.querySelector('input').focus();
    updateCounters();
  }

  function previewTeamPhoto(input) {
    const file = input.files && input.files[0];
    if (!file) return;
    if (previewObjectUrl) URL.revokeObjectURL(previewObjectUrl);
    previewObjectUrl = URL.createObjectURL(file);
    const img = document.getElementById('teamPhotoPreview');
    img.src = previewObjectUrl;
    img.classList.remove('hidden');
    document.getElementById('teamPhotoPlaceholder').classList.add('hidden');
    document.getElementById('teamPhotoRetakeBadge')?.classList.remove('hidden');
    document.getElementById('photoRequiredNotice')?.classList.add('hidden');
  }

  function updateCounters() {
    const members = document.querySelectorAll('.member-check');
    document.getElementById('presentCounter').innerText = [...members].filter(c => c.checked).length + ' / ' + members.length;
    const gear = document.querySelectorAll('.gear-check');
    document.getElementById('gearCounter').innerText = [...gear].filter(c => c.checked).length + ' / ' + gear.length;
    const total = [...document.querySelectorAll('.qty-input')].reduce((s, i) => s + (parseInt(i.value) || 0), 0);
    document.getElementById('logisticCounter').innerText = total + ' unit';
  }

  document.querySelectorAll('.member-check, .gear-check').forEach(c => c.addEventListener('change', updateCounters));
  document.querySelector('input[name="photo_verified"]')?.addEventListener('change', (e) => {
    if (e.target.checked) document.getElementById('photoRequiredNotice')?.classList.add('hidden');
  });
  updateCounters();

  document.getElementById('ascentForm').addEventListener('submit', (e) => {
    const presentCount = [...document.querySelectorAll('.member-check')].filter(c => c.checked).length;
    if (presentCount === 0) {
      e.preventDefault();
      document.getElementById('sectionMembers')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }

    const hasPhoto = document.querySelector('input[name="team_photo"]').files.length > 0
      || (document.querySelector('input[name="photo_verified"]')?.checked ?? false);
    if (!hasPhoto) {
      e.preventDefault();
      const notice = document.getElementById('photoRequiredNotice');
      notice?.classList.remove('hidden');
      document.getElementById('sectionPhoto')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });
</script>
@endpush
@endsection
