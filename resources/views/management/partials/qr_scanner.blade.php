{{--
  Scanner QR Code & Barcode Multi-Kamera Terpadu (Gatekeeper & Ranger)
  Fitur:
  - Deteksi dan daftar semua kamera perangkat (Html5Qrcode.getCameras()).
  - Pilihan kamera berapa yang dipakai via dropdown dan tombol toggle cepat (flip camera).
  - Menyimpan kamera pilihan pengguna di localStorage.
  - Dukungan senter (flashlight/torch) untuk pemindaian malam hari di pos/gate.
  - Audio beep & haptic feedback visual saat kode berhasil dibaca.
  - Ekstraksi cerdas kode registrasi (SMK-YYYYMM-XXXX, link URL tiket, maupun barcode bintang *...*).
  - Input manual cepat langsung di overlay jika kamera terhalang/rusak.
  - Aset lokal offline-first (public/assets/js/html5-qrcode.min.js) dengan fallback CDN.
  
  Penggunaan:
  @include('management.partials.qr_scanner', ['formId' => 'gateSearchForm', 'inputId' => 'gateTicketInput', 'autoOpen' => bool])
--}}
<div id="qrScannerOverlay" class="hidden fixed inset-0 z-[60] bg-black/95 flex flex-col justify-between backdrop-blur-md select-none transition-opacity duration-200" role="dialog" aria-modal="true" aria-label="Scanner Barcode & QR Code">
  
  {{-- 1. Top Header Bar --}}
  <div class="flex items-center justify-between px-4 sm:px-6 h-16 safe-top shrink-0 border-b border-white/10 bg-black/60">
    <div class="flex items-center gap-2.5">
      <span class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center shrink-0">
        <svg class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
      </span>
      <div>
        <p class="text-white font-bold text-sm leading-tight tracking-wide flex items-center gap-2">
          SCANNER VALIDASI
          <span class="text-[9px] font-mono uppercase px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 font-semibold">LIVE</span>
        </p>
        <p class="text-[11px] text-white/50 leading-none mt-0.5 font-mono">QR Code & Barcode Gatekeeper</p>
      </div>
    </div>

    <button type="button" onclick="closeQrScanner()" class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 active:scale-95 text-white flex items-center justify-center transition border border-white/10" aria-label="Tutup scanner">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
  </div>

  {{-- 2. Camera Controls & Selector Toolbar --}}
  <div class="px-4 py-2.5 bg-white/5 border-b border-white/10 shrink-0">
    <div class="max-w-md mx-auto flex items-center gap-2 font-mono text-xs">
      {{-- Camera Selector Dropdown --}}
      <div class="relative flex-1">
        <label for="qrCameraSelect" class="sr-only">Pilih Kamera</label>
        <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-emerald-400">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <select id="qrCameraSelect" onchange="onQrCameraSelected(this.value)" class="w-full h-10 pl-8 pr-7 bg-white/10 hover:bg-white/15 border border-white/20 text-white font-medium rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-emerald-400 appearance-none truncate">
          <option value="" disabled selected class="text-neutral-900">Mendeteksi daftar kamera…</option>
        </select>
        <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-white/50">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
      </div>

      {{-- Quick Switch Camera (Cycle) Button --}}
      <button type="button" id="btnSwitchCamera" onclick="switchNextCamera()" title="Beralih ke kamera selanjutnya" class="h-10 px-3 rounded-xl bg-white/10 hover:bg-white/20 active:scale-95 text-white flex items-center gap-1.5 transition border border-white/20 shrink-0 font-medium">
        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        <span class="hidden sm:inline">Ganti</span>
      </button>

      {{-- Torch / Flashlight Button --}}
      <button type="button" id="btnToggleTorch" onclick="toggleQrTorch()" title="Nyalakan/matikan lampu senter" class="h-10 w-10 rounded-xl bg-white/10 hover:bg-white/20 active:scale-95 text-white flex items-center justify-center transition border border-white/20 shrink-0">
        <svg id="svgTorchOff" class="w-4 h-4 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
        <svg id="svgTorchOn" class="w-4 h-4 text-amber-400 hidden" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm0 15a1 1 0 011 1v3a1 1 0 11-2 0v-3a1 1 0 011-1zm8-6a1 1 0 110 2h-1a1 1 0 110-2h1zM5 11a1 1 0 110 2H4a1 1 0 110-2h1zm12.364-4.95a1 1 0 011.414 1.414l-.707.707a1 1 0 01-1.414-1.414l.707-.707zm-11.314 0l.707.707a1 1 0 01-1.414 1.414l-.707-.707a1 1 0 011.414-1.414zM12 6a5 5 0 00-3.536 8.536A3.375 3.375 0 019 16.924V18h6v-1.076a3.375 3.375 0 01.536-2.388A5 5 0 0012 6z"/></svg>
      </button>
    </div>
  </div>

  {{-- 3. Center Viewfinder --}}
  <div class="flex-1 flex flex-col items-center justify-center p-4 relative min-h-0">
    <div class="relative w-full max-w-xs sm:max-w-sm aspect-square rounded-2xl overflow-hidden bg-black border-2 border-emerald-500/50 shadow-2xl flex items-center justify-center">
      
      {{-- Viewport target element for Html5Qrcode --}}
      <div id="qrReaderViewport" class="w-full h-full object-cover"></div>

      {{-- Laser Scanning Bar Animation --}}
      <div class="pointer-events-none absolute inset-x-0 h-0.5 bg-gradient-to-r from-transparent via-emerald-400 to-transparent shadow-[0_0_12px_#34d399] animate-[scannerBeam_2.2s_ease-in-out_infinite]"></div>

      {{-- Corner Target Reticles --}}
      <div class="pointer-events-none absolute inset-3 border-2 border-transparent">
        <span class="absolute top-0 left-0 w-6 h-6 border-t-2 border-l-2 border-emerald-400 rounded-tl-lg"></span>
        <span class="absolute top-0 right-0 w-6 h-6 border-t-2 border-r-2 border-emerald-400 rounded-tr-lg"></span>
        <span class="absolute bottom-0 left-0 w-6 h-6 border-b-2 border-l-2 border-emerald-400 rounded-bl-lg"></span>
        <span class="absolute bottom-0 right-0 w-6 h-6 border-b-2 border-r-2 border-emerald-400 rounded-br-lg"></span>
      </div>

      {{-- Flash Overlay on Scan Success --}}
      <div id="qrSuccessFlash" class="hidden pointer-events-none absolute inset-0 bg-emerald-500/40 backdrop-blur-xs transition-opacity duration-200 flex items-center justify-center">
        <div class="p-3 bg-emerald-500 rounded-full text-black shadow-lg">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
        </div>
      </div>
    </div>

    {{-- Live Scanner Status Hint --}}
    <div class="mt-3 text-center px-4">
      <p id="qrScannerStatus" class="text-xs font-mono text-emerald-400/90 font-medium tracking-wide">
        Memulai inisialisasi kamera…
      </p>
      <p class="text-[10px] text-white/40 font-mono mt-0.5">
        Mendukung QR Code digital, cetak PDF A4, dan Barcode Code 128
      </p>
    </div>
  </div>

  {{-- 4. Bottom Manual Code Fallback Input --}}
  <div class="p-4 bg-black/60 border-t border-white/10 safe-bottom shrink-0">
    <div class="max-w-md mx-auto space-y-2">
      <div class="flex items-center gap-2">
        <div class="relative flex-1">
          <input type="text" id="qrManualCodeInput" placeholder="Ketik kode manual (mis. 50GQ / SMK-...)" autocomplete="off" class="w-full h-10 px-3 bg-white/10 border border-white/20 text-white placeholder:text-white/40 text-xs font-mono font-bold rounded-xl focus:outline-none focus:ring-1 focus:ring-emerald-400 uppercase">
        </div>
        <button type="button" onclick="submitQrManualCode()" class="h-10 px-4 bg-emerald-500 hover:bg-emerald-400 active:scale-95 text-black font-extrabold text-xs font-mono rounded-xl flex items-center gap-1.5 transition shrink-0">
          <span>KIRIM</span>
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </button>
      </div>
      <p class="text-[10px] text-white/40 font-mono text-center">
        Tip: Jika kamera silau atau kertas tiket kusut, ketik 4 digit terakhir kode.
      </p>
    </div>
  </div>

</div>

<style>
@keyframes scannerBeam {
  0%, 100% { top: 6%; opacity: 0.3; }
  50% { top: 92%; opacity: 1; }
}
</style>

@push('scripts')
{{-- Offline-First Local Script with CDN Fallback --}}
<script src="{{ asset('assets/js/html5-qrcode.min.js') }}"></script>
<script>
  if (typeof Html5Qrcode === 'undefined') {
    document.write('<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"><\/script>');
  }
</script>

<script>
(function () {
  let html5QrCode = null;
  let availableCameras = [];
  let currentCameraIndex = 0;
  let isTorchActive = false;
  let audioContext = null;

  const targetFormId = @json($formId);
  const targetInputId = @json($inputId);
  const STORAGE_KEY_CAM = 'samsara_preferred_camera_id';

  // 1. Play Short Crisp Synthesized Beep on Scan (100% Offline Audio)
  function playSuccessBeep() {
    try {
      if (!audioContext) {
        audioContext = new (window.AudioContext || window.webkitAudioContext)();
      }
      if (audioContext.state === 'suspended') {
        audioContext.resume();
      }
      const osc = audioContext.createOscillator();
      const gain = audioContext.createGain();

      osc.type = 'sine';
      osc.frequency.setValueAtTime(880, audioContext.currentTime); // 880 Hz (A5 tone)
      osc.frequency.exponentialRampToValueAtTime(1320, audioContext.currentTime + 0.08); // chirp up to 1320 Hz

      gain.gain.setValueAtTime(0.25, audioContext.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + 0.12);

      osc.connect(gain);
      gain.connect(audioContext.destination);

      osc.start();
      osc.stop(audioContext.currentTime + 0.12);
    } catch (e) {
      // AudioContext unavailable or blocked by browser policy
    }
  }

  // 2. Open Scanner Overlay
  window.openQrScanner = function () {
    const overlay = document.getElementById('qrScannerOverlay');
    if (!overlay) return;
    overlay.classList.remove('hidden');

    if (html5QrCode && html5QrCode.isScanning) {
      return;
    }

    setStatus('Mendeteksi perangkat kamera…');
    initCamerasAndStart();
  };

  // 3. Close Scanner Overlay
  window.closeQrScanner = function () {
    const overlay = document.getElementById('qrScannerOverlay');
    if (overlay) overlay.classList.add('hidden');
    turnOffTorchUi();

    if (html5QrCode && html5QrCode.isScanning) {
      html5QrCode.stop().then(() => {
        try { html5QrCode.clear(); } catch (e) {}
      }).catch(() => {});
    }
  };

  // 4. Enumerate Available Cameras and Populate Dropdown
  function initCamerasAndStart() {
    if (typeof Html5Qrcode === 'undefined') {
      setStatus('Pustaka pemindai belum siap. Gunakan input kode manual.');
      return;
    }

    Html5Qrcode.getCameras().then(devices => {
      const select = document.getElementById('qrCameraSelect');
      select.innerHTML = '';

      if (!devices || devices.length === 0) {
        // Fallback: Environment facingMode
        const opt = document.createElement('option');
        opt.value = 'environment';
        opt.text = 'Kamera Default (Otomatis)';
        select.appendChild(opt);
        startScannerWithConfig({ facingMode: 'environment' });
        return;
      }

      availableCameras = devices;
      const savedCamId = localStorage.getItem(STORAGE_KEY_CAM);
      let targetIndex = 0;

      devices.forEach((dev, index) => {
        const opt = document.createElement('option');
        opt.value = dev.id;

        let label = dev.label ? dev.label.trim() : `Kamera ${index + 1}`;
        // Clean label names for field clarity
        if (/back|rear|environment/i.test(label)) {
          label = `📷 Belakang [${index + 1}]: ` + label.replace(/camera|back|rear/gi, '').trim();
        } else if (/front|user|selfie/i.test(label)) {
          label = `🤳 Depan [${index + 1}]: ` + label.replace(/camera|front|user/gi, '').trim();
        } else {
          label = `📷 Kamera ${index + 1}: ` + label;
        }

        opt.text = label;
        select.appendChild(opt);

        if (savedCamId && dev.id === savedCamId) {
          targetIndex = index;
        }
      });

      select.selectedIndex = targetIndex;
      currentCameraIndex = targetIndex;

      startScannerWithConfig(devices[targetIndex].id);
    }).catch(err => {
      console.warn('Gagal membaca kamera via getCameras():', err);
      // Fallback: facingMode environment
      startScannerWithConfig({ facingMode: 'environment' });
    });
  }

  // 5. Start Scanner with Camera ID or Constraint Object
  function startScannerWithConfig(cameraConfig) {
    setStatus('Mengaktifkan lensa kamera…');
    turnOffTorchUi();

    if (!html5QrCode) {
      // Initialize with support for standard QR and 1D Barcodes
      html5QrCode = new Html5Qrcode('qrReaderViewport', {
        experimentalFeatures: {
          useBarCodeDetectorIfSupported: true
        },
        verbose: false
      });
    }

    const scanConfig = {
      fps: 15, // Smooth 15 frames per second for high-responsiveness
      qrbox: function(viewfinderWidth, viewfinderHeight) {
        // Dynamic responsive scan box: min 200px, max 280px
        const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
        const boxSize = Math.max(180, Math.min(minEdge * 0.78, 280));
        return { width: Math.floor(boxSize), height: Math.floor(boxSize) };
      },
      aspectRatio: 1.0
    };

    html5QrCode.start(
      cameraConfig,
      scanConfig,
      onCodeDetected,
      () => {} // silent on frame without barcode
    ).then(() => {
      setStatus('Kamera aktif · Arahkan ke barcode / QR tiket');
    }).catch(err => {
      console.error('Gagal menjalankan kamera:', err);
      setStatus('Izin kamera ditolak atau lensa sibuk. Gunakan input manual.');
    });
  }

  // 6. User Selected Camera from Dropdown
  window.onQrCameraSelected = function (cameraId) {
    if (!cameraId) return;
    localStorage.setItem(STORAGE_KEY_CAM, cameraId);

    // Update index
    const foundIdx = availableCameras.findIndex(c => c.id === cameraId);
    if (foundIdx !== -1) currentCameraIndex = foundIdx;

    restartScannerWith(cameraId);
  };

  // 7. Quick Switch / Cycle Camera Button
  window.switchNextCamera = function () {
    if (!availableCameras || availableCameras.length <= 1) {
      setStatus('Hanya 1 lensa kamera yang terdeteksi.');
      return;
    }

    currentCameraIndex = (currentCameraIndex + 1) % availableCameras.length;
    const nextCam = availableCameras[currentCameraIndex];

    const select = document.getElementById('qrCameraSelect');
    if (select) select.selectedIndex = currentCameraIndex;

    localStorage.setItem(STORAGE_KEY_CAM, nextCam.id);
    restartScannerWith(nextCam.id);
  };

  // Helper: Stop and Restart with new Camera
  function restartScannerWith(cameraConfig) {
    if (html5QrCode && html5QrCode.isScanning) {
      setStatus('Mengalihkan kamera…');
      html5QrCode.stop().then(() => {
        startScannerWithConfig(cameraConfig);
      }).catch(() => {
        startScannerWithConfig(cameraConfig);
      });
    } else {
      startScannerWithConfig(cameraConfig);
    }
  }

  // 8. Flashlight / Torch Toggle
  window.toggleQrTorch = function () {
    if (!html5QrCode || !html5QrCode.isScanning) return;

    isTorchActive = !isTorchActive;
    html5QrCode.applyVideoConstraints({
      advanced: [{ torch: isTorchActive }]
    }).then(() => {
      updateTorchUi(isTorchActive);
    }).catch(err => {
      isTorchActive = false;
      updateTorchUi(false);
      setStatus('Perangkat tidak mendukung senter kamera ini.');
    });
  };

  function updateTorchUi(active) {
    const offIcon = document.getElementById('svgTorchOff');
    const onIcon = document.getElementById('svgTorchOn');
    const btn = document.getElementById('btnToggleTorch');
    if (offIcon && onIcon) {
      offIcon.classList.toggle('hidden', active);
      onIcon.classList.toggle('hidden', !active);
    }
    if (btn) {
      btn.classList.toggle('bg-amber-500/20', active);
      btn.classList.toggle('border-amber-400/40', active);
    }
  }

  function turnOffTorchUi() {
    isTorchActive = false;
    updateTorchUi(false);
  }

  // 9. Successfully Scanned & Decoded Handler
  function onCodeDetected(decodedText) {
    if (!decodedText) return;

    // Trigger visual flash
    const flash = document.getElementById('qrSuccessFlash');
    if (flash) {
      flash.classList.remove('hidden');
      setTimeout(() => flash.classList.add('hidden'), 350);
    }

    // Audio & Haptic
    playSuccessBeep();
    if (navigator.vibrate) {
      try { navigator.vibrate([60, 40, 60]); } catch (e) {}
    }

    let code = decodedText.trim();

    // Regex extraction: extracts SMK-YYYYMM-XXXX from raw text or full URLs
    const smkMatch = code.match(/SMK-[A-Z0-9-]+/i);
    if (smkMatch) {
      code = smkMatch[0].toUpperCase();
    } else {
      // Strip any asterisks or formatting wrappers
      code = code.replace(/[\*#\s]/g, '').toUpperCase();
    }

    setStatus(`Kode terbaca: [${code}] · Memproses data…`);

    const input = document.getElementById(targetInputId);
    if (input) input.value = code;

    window.closeQrScanner();

    const form = document.getElementById(targetFormId);
    if (form) form.submit();
  }

  // 10. Manual Code Submission Fallback
  window.submitQrManualCode = function () {
    const manualInput = document.getElementById('qrManualCodeInput');
    if (!manualInput) return;

    let val = manualInput.value.trim().toUpperCase();
    if (!val) {
      manualInput.focus();
      return;
    }

    // Strip wrapping symbols
    val = val.replace(/[\*#\s]/g, '');

    const targetInput = document.getElementById(targetInputId);
    if (targetInput) targetInput.value = val;

    window.closeQrScanner();

    const form = document.getElementById(targetFormId);
    if (form) form.submit();
  };

  // Allow pressing Enter in manual input
  document.addEventListener('DOMContentLoaded', function () {
    const manualInput = document.getElementById('qrManualCodeInput');
    if (manualInput) {
      manualInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          submitQrManualCode();
        }
      });
    }

    @if(!empty($autoOpen))
      window.openQrScanner();
    @endif
  });

  function setStatus(msg) {
    const el = document.getElementById('qrScannerStatus');
    if (el) el.innerText = msg;
  }
})();
</script>
@endpush
