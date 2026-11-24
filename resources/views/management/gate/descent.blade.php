@extends('management.layout')

@section('title', 'Periksa turun · '.$registration->registration_code)
@section('page_title', 'Pemeriksaan turun')
@section('page_subtitle', $registration->registration_code.' · '.$registration->leader_name)

@section('content')
@php $photo = $registration->photoUrl(); @endphp

<a href="{{ route('management.gate.index', ['tab' => 'jalur']) }}" class="inline-flex items-center gap-1.5 text-sm text-muted hover:text-ink mb-3">
  <x-ui.icon name="chevron-right" class="w-4 h-4 rotate-180" /> Di jalur
</a>

<form id="descentForm" method="POST" action="{{ route('management.gate.descent.submit', $registration->id) }}" class="max-w-3xl mx-auto space-y-4">
  @csrf

  <div class="rounded-2xl bg-gradient-to-br from-violet-500/15 to-surface ring-1 ring-violet-500/25 p-4 flex items-start gap-4">
    @if($photo)
      <a href="{{ $photo }}" target="_blank" class="shrink-0"><img src="{{ $photo }}" alt="Foto tim" class="w-20 h-20 rounded-xl object-cover ring-1 ring-line"></a>
    @endif
    <div class="min-w-0 flex-1">
      <p class="font-code text-xs text-violet-300">{{ $registration->registration_code }}</p>
      <h1 class="font-display font-bold text-xl text-ink mt-0.5 truncate">{{ $registration->leader_name }}</h1>
      <p class="text-sm text-muted mt-0.5">{{ $registration->members->where('is_checked_in', true)->count() }} pendaki naik · berangkat {{ $registration->checked_in_at?->translatedFormat('d M H:i') }}</p>
      <p class="text-xs text-muted mt-1">Terakhir terlihat: <span class="text-ink">{{ str_replace('_', ' ', $registration->last_seen_pos ?? 'BASECAMP') }}</span> · {{ $registration->last_seen_at?->diffForHumans() }}</p>
    </div>
  </div>

  {{-- Logistik --}}
  <x-ui.card title="Hitung ulang logistik" subtitle="Isi jumlah yang dibawa turun (kemasan kosong / sisa). Harus sama dengan saat naik." icon="box">
    <x-slot:actions><span id="clearBadge"></span></x-slot:actions>
    @if($logisticItems->isEmpty())
      <x-ui.empty icon="box" title="Tidak ada logistik tercatat saat naik" />
    @else
      <div class="space-y-2">
        @foreach($logisticItems as $item)
          <div class="logistic-row flex items-center gap-3 p-2 pl-3 rounded-xl bg-raised/60 ring-1 ring-line transition" data-up="{{ $item->qty_up }}">
            <div class="min-w-0 flex-1">
              <p class="text-sm font-semibold text-ink truncate">{{ $item->name }}</p>
              <p class="text-[11px] text-muted">Naik: <strong class="text-ink tabular-nums">{{ $item->qty_up }}</strong> {{ $item->unit }} <span class="row-status ml-1"></span></p>
            </div>
            <div class="flex items-center shrink-0 rounded-xl bg-canvas ring-1 ring-line-strong">
              <button type="button" class="w-11 h-11 text-xl text-muted hover:text-ink" onclick="stepQty(this, -1)" aria-label="Kurangi">−</button>
              <input type="number" inputmode="numeric" min="0" name="qty_down[{{ $item->id }}]" value="{{ old('qty_down.'.$item->id, 0) }}" class="qty-input w-12 h-11 bg-transparent text-center font-display font-bold text-lg text-ink outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none" oninput="evaluate()">
              <button type="button" class="w-11 h-11 text-xl text-muted hover:text-ink" onclick="stepQty(this, 1)" aria-label="Tambah">+</button>
            </div>
          </div>
        @endforeach
      </div>
      <x-ui.button variant="ghost" size="sm" icon="check" class="mt-2" onclick="fillAll()">Semua lengkap (isi sama dengan naik)</x-ui.button>
    @endif
  </x-ui.card>

  {{-- Alat --}}
  @if($gearItems->where('checked_up', true)->isNotEmpty())
    <x-ui.card title="Alat dibawa turun" subtitle="Pastikan tenda & alat tidak ditinggal di jalur" icon="checklist">
      <div class="flex flex-wrap gap-2">
        @foreach($gearItems->where('checked_up', true) as $item)
          <label class="relative inline-flex cursor-pointer select-none">
            <input type="checkbox" name="gear_down[{{ $item->id }}]" value="1" class="peer absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10 gear-check" onchange="evaluate()" @checked(old('gear_down.'.$item->id))>
            <span class="inline-flex items-center gap-2 h-11 px-4 rounded-xl text-sm font-medium ring-1 ring-line-strong bg-raised text-muted peer-checked:bg-emerald-500/15 peer-checked:text-emerald-200 peer-checked:ring-emerald-500/40 transition pointer-events-none">
              <x-ui.icon name="check" class="w-4 h-4 opacity-40" /> {{ $item->name }}
            </span>
          </label>
        @endforeach
      </div>
    </x-ui.card>
  @endif

  <x-ui.card title="Catatan" icon="doc">
    <textarea name="notes" rows="2" class="field" placeholder="mis. 2 botol tertinggal di Pos 3, sudah dilaporkan ke ranger">{{ old('notes') }}</textarea>
  </x-ui.card>

  <div class="sticky bottom-0 z-20 -mx-3 sm:-mx-4 lg:mx-0 px-3 sm:px-4 lg:px-0 py-3 bg-canvas/95 backdrop-blur border-t border-line lg:border-0 lg:bg-transparent">
    <div class="flex items-center gap-3">
      <p id="summaryText" class="hidden sm:block flex-1 text-xs text-muted"></p>
      <x-ui.button id="submitDescent" type="submit" size="xl" variant="success" icon="check" class="w-full sm:w-auto">Selesai diperiksa · kirim ke kasir</x-ui.button>
    </div>
  </div>
</form>

@push('scripts')
<script>
  function stepQty(btn, delta) {
    const input = btn.parentElement.querySelector('.qty-input');
    input.value = Math.max(0, (parseInt(input.value) || 0) + delta);
    evaluate();
  }

  function fillAll() {
    document.querySelectorAll('.logistic-row').forEach(row => row.querySelector('.qty-input').value = row.dataset.up);
    document.querySelectorAll('.gear-check').forEach(c => c.checked = true);
    evaluate();
  }

  function evaluate() {
    let missing = 0;
    document.querySelectorAll('.logistic-row').forEach(row => {
      const up = parseInt(row.dataset.up) || 0;
      const down = parseInt(row.querySelector('.qty-input').value) || 0;
      const diff = up - down;
      const status = row.querySelector('.row-status');
      row.classList.toggle('ring-emerald-500/40', diff <= 0);
      row.classList.toggle('ring-rose-500/40', diff > 0);
      status.innerHTML = diff > 0 ? `<span class="text-rose-300 font-semibold">kurang ${diff}</span>` : '<span class="text-emerald-300 font-semibold">lengkap</span>';
      if (diff > 0) missing += diff;
    });
    const gearMissing = [...document.querySelectorAll('.gear-check')].filter(c => !c.checked).length;
    const clear = missing === 0 && gearMissing === 0;
    document.getElementById('clearBadge').innerHTML = clear
      ? '<span class="inline-flex items-center gap-1.5 rounded-full text-[11px] font-semibold px-2 py-0.5 bg-emerald-500/10 text-emerald-300 ring-1 ring-inset ring-emerald-500/25">CLEAR</span>'
      : '<span class="inline-flex items-center gap-1.5 rounded-full text-[11px] font-semibold px-2 py-0.5 bg-rose-500/10 text-rose-300 ring-1 ring-inset ring-rose-500/25">Selisih</span>';
    document.getElementById('summaryText').innerText = clear
      ? 'Semua logistik & alat kembali lengkap.'
      : `Masih kurang ${missing} unit logistik${gearMissing ? ' dan ' + gearMissing + ' alat' : ''}. Tetap bisa dikirim ke kasir dengan catatan selisih.`;
    const btn = document.getElementById('submitDescent');
    btn.classList.toggle('bg-emerald-600', clear);
    btn.classList.toggle('hover:bg-emerald-500', clear);
    btn.classList.toggle('bg-rose-600', !clear);
    btn.classList.toggle('hover:bg-rose-500', !clear);
  }
  evaluate();
</script>
@endpush
@endsection
