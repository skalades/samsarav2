{{--
  Tombol cetak ESC/POS Bluetooth (RP20N / printer thermal 58-80mm).
  Pemakaian: @include('management.partials.thermal_bt', ['doc' => $thermalDoc, 'label' => 'CETAK BLUETOOTH'])
--}}
@php
  $btLogo = \App\Models\AppSetting::get('app_logo');
  $btLogoUrl = $btLogo ? asset('storage/' . $btLogo) : null;
  $btId = 'btp-' . uniqid();
@endphp
<style>
  .btp-wrap { font-family: 'JetBrains Mono', ui-monospace, monospace; margin: 12px auto 0; width: 100%; max-width: 360px; font-size: 11px; color: #E7E5E4; }
  .btp-bar { display: flex; gap: 6px; }
  .btp-btn { flex: 1; font-family: inherit; font-size: 12px; font-weight: 700; padding: 9px 12px; border-radius: 6px; border: none; cursor: pointer; background: #2563EB; color: #fff; display: inline-flex; align-items: center; justify-content: center; gap: 6px; }
  .btp-btn:disabled { opacity: .6; cursor: wait; }
  .btp-gear { flex: 0 0 auto; background: #333; }
  .btp-status { margin-top: 6px; padding: 6px 8px; border-radius: 6px; background: #1C1F26; font-size: 10px; min-height: 14px; }
  .btp-status.ok { color: #6EE7B7; } .btp-status.err { color: #FCA5A5; }
  .btp-panel { display: none; margin-top: 6px; padding: 10px; border-radius: 6px; background: #16191F; border: 1px solid #2A303C; }
  .btp-panel.open { display: block; }
  .btp-panel label { display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 6px; }
  .btp-panel select, .btp-panel input[type=number] { font-family: inherit; font-size: 11px; background: #0F1115; color: #fff; border: 1px solid #2A303C; border-radius: 4px; padding: 3px 6px; width: 140px; }
  .btp-panel .btp-row { display: flex; gap: 6px; margin-top: 8px; }
  .btp-panel .btp-row button { flex: 1; font-family: inherit; font-size: 10px; font-weight: 700; padding: 6px; border-radius: 4px; border: 1px solid #2A303C; background: #222733; color: #fff; cursor: pointer; }
  .btp-hint { font-size: 9px; color: #A8A29E; margin-top: 6px; line-height: 1.4; }
  @media print { .btp-wrap { display: none !important; } }
</style>

<div class="btp-wrap" id="{{ $btId }}">
  <div class="btp-bar">
    <button type="button" class="btp-btn" data-btp-print>
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7l10 10-5 5V2l5 5L7 17"/></svg>
      {{ $label ?? 'CETAK BLUETOOTH (ESC/POS)' }}
    </button>
    <button type="button" class="btp-btn btp-gear" data-btp-toggle title="Pengaturan printer Bluetooth">&#9881;</button>
  </div>
  <div class="btp-status" data-btp-status>Printer: belum terhubung</div>

  <div class="btp-panel" data-btp-panel>
    <label>Koneksi
      <select data-btp-set="transport">
        <option value="ble">Bluetooth (BLE)</option>
        <option value="rawbt">Android RawBT (Classic/SPP)</option>
        <option value="serial">Serial/COM (PC)</option>
      </select>
    </label>
    <label>Lebar kertas
      <select data-btp-set="paper">
        <option value="58">58 mm (32 kolom)</option>
        <option value="80">80 mm (48 kolom)</option>
      </select>
    </label>
    <label>Cetak logo <input type="checkbox" data-btp-set="logo"></label>
    <label>Cetak QR code <input type="checkbox" data-btp-set="qr"></label>
    <label>Perintah potong kertas <input type="checkbox" data-btp-set="cut"></label>
    <label>Jumlah salinan <input type="number" min="1" max="5" data-btp-set="copies"></label>
    <label>Ukuran paket BLE <input type="number" min="20" max="512" step="10" data-btp-set="chunk"></label>
    <label>Baud rate serial <input type="number" min="1200" step="1200" data-btp-set="baud"></label>
    <div class="btp-row">
      <button type="button" data-btp-pair>PILIH / PAIR PRINTER</button>
      <button type="button" data-btp-test>CETAK TES</button>
    </div>
    <div class="btp-hint">
      RP20N: nyalakan printer &amp; Bluetooth HP. Jika printer tidak muncul di daftar BLE, pakai mode
      <b>RawBT</b> (Android, pair dulu di Pengaturan Bluetooth) atau <b>Serial/COM</b> (Windows).
      Web Bluetooth butuh Chrome/Edge &amp; HTTPS. Jika QR tidak tercetak/aneh, matikan opsi QR.
    </div>
  </div>
</div>

<script src="{{ asset('assets/js/escpos-bt.js') }}?v={{ filemtime(public_path('assets/js/escpos-bt.js')) }}"></script>
<script>
  (function () {
    const doc = @json($doc);
    const logoUrl = @json($btLogoUrl);
    const root = document.getElementById(@json($btId));
    const $ = (sel) => root.querySelector(sel);
    const status = $('[data-btp-status]');
    const P = window.SamsaraEscPos;

    function setStatus(msg, cls) { status.textContent = msg; status.className = 'btp-status ' + (cls || ''); }

    // Settings form <-> localStorage
    const s = P.loadSettings();
    root.querySelectorAll('[data-btp-set]').forEach((el) => {
      const key = el.dataset.btpSet;
      if (el.type === 'checkbox') el.checked = !!s[key]; else el.value = s[key];
      el.addEventListener('change', () => {
        const cur = P.loadSettings();
        cur[key] = el.type === 'checkbox' ? el.checked : (el.type === 'number' || key === 'paper' ? Number(el.value) : el.value);
        P.saveSettings(cur);
        setStatus('Pengaturan disimpan (' + cur.transport.toUpperCase() + ', ' + cur.paper + 'mm)');
      });
    });
    setStatus('Mode: ' + s.transport.toUpperCase() + ' • ' + s.paper + 'mm');

    async function run(btn, fn, busyMsg) {
      btn.disabled = true;
      setStatus(busyMsg);
      try {
        const msg = await fn();
        setStatus(msg || ('Terkirim ke printer' + (P.currentDevice() ? ': ' + P.currentDevice() : '')), 'ok');
      } catch (e) {
        if (e && e.name === 'NotFoundError') setStatus('Pemilihan printer dibatalkan.', 'err');
        else setStatus('Gagal: ' + (e && e.message ? e.message : e), 'err');
        console.error('[escpos]', e);
      } finally {
        btn.disabled = false;
      }
    }

    $('[data-btp-toggle]').addEventListener('click', () => $('[data-btp-panel]').classList.toggle('open'));
    $('[data-btp-print]').addEventListener('click', (ev) => run(ev.currentTarget, () => P.print(doc, { logoUrl }), 'Mengirim ke printer...'));
    $('[data-btp-pair]').addEventListener('click', (ev) => run(ev.currentTarget, () => P.pair(), 'Mencari printer...'));
    $('[data-btp-test]').addEventListener('click', (ev) => run(ev.currentTarget, () => P.testPage(), 'Mencetak halaman tes...'));
  })();
</script>
