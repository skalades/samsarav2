@extends('management.layout')

@section('title', 'WhatsApp Gateway')
@section('page_title', 'WhatsApp Gateway')

@section('content')
<x-ui.page-header title="WhatsApp Gateway" subtitle="Info pendakian & e-SIMAKSI dikirim otomatis ke ketua rombongan saat kasir mengonfirmasi LUNAS" eyebrow="Pengaturan" />

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
  <div class="space-y-4">
    <x-ui.card title="Status koneksi" icon="whatsapp">
      <x-slot:actions>
        <x-ui.badge :tone="$gateway['connected'] ? 'success' : 'warning'" dot>{{ $gateway['connected'] ? 'Terhubung' : 'Belum aktif' }}</x-ui.badge>
      </x-slot:actions>
      <dl class="space-y-2.5 text-sm">
        <div class="flex justify-between gap-3"><dt class="text-muted">Driver aktif</dt><dd class="text-ink font-semibold">{{ $gateway['label'] }}</dd></div>
        <div class="flex justify-between gap-3"><dt class="text-muted">Endpoint</dt><dd class="text-ink">{{ $gateway['has_endpoint'] ? 'Terisi' : '—' }}</dd></div>
        <div class="flex justify-between gap-3"><dt class="text-muted">Token</dt><dd class="text-ink">{{ $gateway['has_token'] ? 'Terisi' : '—' }}</dd></div>
        <div class="flex justify-between gap-3"><dt class="text-muted">Kirim otomatis saat lunas</dt><dd class="{{ $gateway['auto_send_on_paid'] ? 'text-emerald-300' : 'text-muted' }} font-semibold">{{ $gateway['auto_send_on_paid'] ? 'Aktif' : 'Mati' }}</dd></div>
      </dl>
      @if($gateway['misconfigured'])
        <p class="mt-3 p-3 rounded-xl bg-amber-500/10 ring-1 ring-amber-500/25 text-xs text-amber-200">Driver <strong>{{ $gateway['configured_driver'] }}</strong> dipilih tetapi kredensial belum lengkap, jadi pesan hanya dicatat.</p>
      @endif
    </x-ui.card>

    <x-ui.card title="Cara menghubungkan" icon="settings">
      <p class="text-sm text-muted">Isi di file <code class="font-code text-ink">.env</code> server lalu muat ulang konfigurasi:</p>
      <pre class="mt-2 p-3 rounded-xl bg-canvas ring-1 ring-line text-[11px] leading-relaxed text-ink font-code overflow-x-auto">WA_GATEWAY_DRIVER=fonnte
WA_GATEWAY_TOKEN=token-dari-penyedia
# wablas: isi juga
# WA_GATEWAY_ENDPOINT=https://domain.wablas.com
# WA_GATEWAY_SECRET=secret-key
WA_AUTO_SEND_ON_PAID=true</pre>
      <p class="text-xs text-muted mt-2">Pilihan driver: {{ implode(', ', array_keys($drivers)) }}. Pastikan <span class="font-code text-ink">queue:work</span> berjalan agar pesan terkirim di latar belakang.</p>
    </x-ui.card>

    <x-ui.card title="Uji kirim" icon="phone">
      <form method="POST" action="{{ route('management.settings.whatsapp.test') }}" class="space-y-3">
        @csrf
        <div>
          <label class="label">Nomor WhatsApp tujuan</label>
          <input type="text" name="phone" required inputmode="tel" class="field" placeholder="08xxxxxxxxxx">
        </div>
        <x-ui.button type="submit" variant="secondary" icon="whatsapp" block>Kirim pesan uji</x-ui.button>
      </form>
    </x-ui.card>
  </div>

  <x-ui.card class="lg:col-span-2" title="Log pesan" subtitle="Semua pesan yang diantrekan sistem" icon="list" :padding="false">
    <div class="flex gap-1.5 px-4 sm:px-5 pt-3 overflow-x-auto no-scrollbar">
      @foreach(['' => 'Semua', 'sent' => 'Terkirim', 'failed' => 'Gagal', 'queued' => 'Antrean', 'skipped' => 'Dicatat'] as $key => $label)
        <a href="{{ route('management.settings.whatsapp', $key ? ['status' => $key] : []) }}" class="h-8 px-3 rounded-lg text-xs font-semibold flex items-center gap-1.5 whitespace-nowrap {{ ($filter ?? '') === $key ? 'bg-raised text-ink ring-1 ring-line-strong' : 'text-muted hover:text-ink' }}">
          {{ $label }} @if($key)<span class="text-subtle">{{ $counts[$key] ?? 0 }}</span>@endif
        </a>
      @endforeach
    </div>
    <div class="divide-y divide-line mt-3">
      @forelse($messages as $msg)
        <details class="group">
          <summary class="flex items-center gap-3 px-4 sm:px-5 py-3 cursor-pointer hover:bg-hover/40">
            <x-ui.badge :tone="$msg->statusTone()" size="xs" dot>{{ $msg->statusLabel() }}</x-ui.badge>
            <div class="min-w-0 flex-1">
              <p class="text-sm text-ink truncate">{{ $msg->registration?->leader_name ?? 'Nomor '.$msg->phone }} <span class="text-muted">· {{ \App\Models\WhatsappMessage::EVENT_LABELS[$msg->event] ?? $msg->event }}</span></p>
              <p class="text-[11px] text-subtle">{{ $msg->phone }} · {{ $msg->driver }} · {{ $msg->created_at->diffForHumans() }}{{ $msg->attempts > 1 ? ' · '.$msg->attempts.'x percobaan' : '' }}</p>
            </div>
            <x-ui.icon name="chevron-down" class="w-4 h-4 text-subtle group-open:rotate-180 transition" />
          </summary>
          <div class="px-4 sm:px-5 pb-4">
            <pre class="p-3 rounded-xl bg-canvas ring-1 ring-line text-xs text-ink whitespace-pre-wrap font-sans leading-relaxed max-h-80 overflow-y-auto">{{ $msg->message }}</pre>
            @if($msg->response)<p class="mt-2 text-[11px] text-subtle break-all">Respons gateway: {{ \Illuminate\Support\Str::limit($msg->response, 300) }}</p>@endif
            @if($msg->status !== 'sent')
              <form method="POST" action="{{ route('management.settings.whatsapp.retry', $msg->id) }}" class="mt-2">
                @csrf
                <x-ui.button type="submit" variant="secondary" size="sm" icon="refresh">Kirim ulang</x-ui.button>
              </form>
            @endif
          </div>
        </details>
      @empty
        <x-ui.empty icon="whatsapp" title="Belum ada pesan" text="Pesan akan tercatat di sini setelah kasir mengonfirmasi pembayaran." />
      @endforelse
    </div>
    @if($messages->hasPages())<div class="px-4 sm:px-5 py-3 border-t border-line">{{ $messages->links() }}</div>@endif
  </x-ui.card>
</div>
@endsection
