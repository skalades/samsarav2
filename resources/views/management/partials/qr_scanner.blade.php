{{--
  Scanner QR layar penuh (kamera belakang). Setelah kode terbaca, isi input #{{ $inputId }} lalu kirim form #{{ $formId }}.
  Pakai: @include('management.partials.qr_scanner', ['formId' => 'x', 'inputId' => 'y', 'autoOpen' => bool])
--}}
<div id="qrScannerOverlay" class="hidden fixed inset-0 z-[60] bg-black flex flex-col">
  <div class="flex items-center justify-between px-4 h-14 safe-top shrink-0">
    <p class="text-white font-semibold text-sm">Arahkan kamera ke QR tiket / surat jalan</p>
    <button type="button" onclick="closeQrScanner()" class="w-10 h-10 rounded-full bg-white/10 text-white flex items-center justify-center" aria-label="Tutup scanner">
      <x-ui.icon name="x" class="w-5 h-5" />
    </button>
  </div>
  <div class="flex-1 flex items-center justify-center p-4">
    <div id="qrReaderViewport" class="w-full max-w-md aspect-square rounded-3xl overflow-hidden ring-2 ring-brand/60 bg-black"></div>
  </div>
  <p id="qrScannerStatus" class="text-center text-xs text-white/60 pb-8 safe-bottom px-6">Memulai kamera…</p>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
  (function () {
    let scanner = null;

    window.openQrScanner = function () {
      document.getElementById('qrScannerOverlay').classList.remove('hidden');
      if (scanner && scanner.isScanning) return;
      if (typeof Html5Qrcode === 'undefined') {
        document.getElementById('qrScannerStatus').innerText = 'Pustaka scanner gagal dimuat. Ketik kode secara manual.';
        return;
      }
      scanner = new Html5Qrcode('qrReaderViewport');
      scanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: { width: 240, height: 240 } }, (text) => {
        let code = text.trim();
        const match = code.match(/SMK-[A-Z0-9-]+/i);
        if (match) code = match[0].toUpperCase();
        document.getElementById(@json($inputId)).value = code;
        window.closeQrScanner();
        document.getElementById(@json($formId)).submit();
      }, () => {}).then(() => {
        document.getElementById('qrScannerStatus').innerText = 'Kamera aktif · kode akan terbaca otomatis';
      }).catch(() => {
        document.getElementById('qrScannerStatus').innerText = 'Kamera tidak bisa diakses. Izinkan kamera di peramban atau ketik kode manual.';
      });
    };

    window.closeQrScanner = function () {
      document.getElementById('qrScannerOverlay').classList.add('hidden');
      if (scanner && scanner.isScanning) {
        scanner.stop().then(() => scanner.clear()).catch(() => {});
      }
    };

    @if(!empty($autoOpen))
      document.addEventListener('DOMContentLoaded', () => window.openQrScanner());
    @endif
  })();
</script>
@endpush
