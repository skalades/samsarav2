/**
 * SAMSARA EXPEDITION — PWA Client Controller
 * Modul: Service Worker Registrar, Install Manager & Real-time Network Monitor
 * Arsitektur: Vanilla ES6+, Modular, Scalable & Event-driven
 */

(function (window, document) {
  'use strict';

  class SamsaraPWA {
    constructor() {
      this.swRegistration = null;
      this.deferredInstallPrompt = null;
      this.isOnlineState = navigator.onLine;
      this.waitingWorker = null;

      this.init();
    }

    init() {
      // 1. Deteksi Standalone Display Mode
      this.checkStandaloneMode();

      // 2. Registrasi Service Worker jika didukung
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => this.registerServiceWorker());
      }

      // 3. Listener Event Instalasi PWA
      window.addEventListener('beforeinstallprompt', (e) => this.handleBeforeInstallPrompt(e));
      window.addEventListener('appinstalled', (e) => this.handleAppInstalled(e));

      // 4. Listener Status Koneksi Jaringan
      window.addEventListener('online', () => this.handleNetworkChange(true));
      window.addEventListener('offline', () => this.handleNetworkChange(false));

      // Update badge awal saat load
      document.addEventListener('DOMContentLoaded', () => {
        this.updateNetworkUI(this.isOnlineState);
        this.bindActionButtons();
      });
    }

    /**
     * Registrasi Service Worker & Monitor Siklus Pembaruan
     */
    async registerServiceWorker() {
      try {
        const registration = await navigator.serviceWorker.register('/sw.js', { scope: '/' });
        this.swRegistration = registration;
        console.info('[PWA] Service Worker terdaftar dengan scope:', registration.scope);

        // Deteksi jika sudah ada worker yang 'waiting' saat dibuka
        if (registration.waiting) {
          this.waitingWorker = registration.waiting;
          this.notifyUpdateAvailable();
        }

        // Listener jika ada pembaruan baru yang ditemukan
        registration.addEventListener('updatefound', () => {
          const installingWorker = registration.installing;
          if (!installingWorker) return;

          installingWorker.addEventListener('statechange', () => {
            if (installingWorker.state === 'installed' && navigator.serviceWorker.controller) {
              // Service Worker baru siap menggantikan yang lama
              this.waitingWorker = installingWorker;
              this.notifyUpdateAvailable();
            }
          });
        });

        // Kontrol refresh otomatis setelah worker berganti
        let refreshing = false;
        navigator.serviceWorker.addEventListener('controllerchange', () => {
          if (!refreshing) {
            refreshing = true;
            window.location.reload();
          }
        });
      } catch (error) {
        console.warn('[PWA] Gagal meregistrasi Service Worker:', error);
      }
    }

    /**
     * Intersepsi event prompt instalasi bawaan browser
     */
    handleBeforeInstallPrompt(event) {
      // Cegah banner otomatis Chrome/Edge agar dikontrol UI SAMSARA
      event.preventDefault();
      this.deferredInstallPrompt = event;
      console.info('[PWA] Aplikasi dapat diinstal ke perangkat.');

      // Cek apakah user pernah menutup banner instalasi dalam 7 hari terakhir
      const dismissedUntil = localStorage.getItem('samsara_pwa_install_dismissed');
      if (dismissedUntil && Date.now() < parseInt(dismissedUntil, 10)) {
        return;
      }

      // Tampilkan tombol & banner instalasi di UI
      this.showInstallPrompts(true);
    }

    /**
     * Eksekusi instalasi aplikasi oleh pengguna
     */
    async promptInstall() {
      if (!this.deferredInstallPrompt) {
        console.warn('[PWA] Prompt instalasi belum siap atau aplikasi sudah terpasang.');
        this.showToast('Aplikasi sudah terpasang atau gunakan opsi "Install" di menu peramban.', 'info');
        return;
      }

      const promptEvent = this.deferredInstallPrompt;
      this.deferredInstallPrompt = null;

      try {
        await promptEvent.prompt();
        const choice = await promptEvent.userChoice;
        console.info('[PWA] Pilihan instalasi pengguna:', choice.outcome);

        if (choice.outcome === 'accepted') {
          this.showInstallPrompts(false);
          this.showToast('Memasang SAMSARA OPS ke perangkat...', 'success');
        } else {
          // Pengguna membatalkan: tunda tawaran selama 3 hari
          localStorage.setItem('samsara_pwa_install_dismissed', (Date.now() + 3 * 86400000).toString());
          this.showInstallPrompts(false);
        }
      } catch (err) {
        console.error('[PWA] Error saat memicu instalasi:', err);
      }
    }

    /**
     * Handler saat aplikasi sukses diinstal
     */
    handleAppInstalled(event) {
      console.info('[PWA] Sukses diinstal!');
      this.deferredInstallPrompt = null;
      this.showInstallPrompts(false);
      this.showToast('SAMSARA OPS berhasil dipasang di perangkat Anda!', 'success');
    }

    /**
     * Tampilkan atau sembunyikan elemen pemicu instalasi
     */
    showInstallPrompts(visible) {
      document.querySelectorAll('.pwa-install-trigger').forEach((el) => {
        if (visible) {
          el.classList.remove('hidden');
          el.classList.add('flex');
        } else {
          el.classList.add('hidden');
          el.classList.remove('flex');
        }
      });
    }

    /**
     * Tutup banner instalasi untuk sementara
     */
    dismissInstallPrompt() {
      localStorage.setItem('samsara_pwa_install_dismissed', (Date.now() + 7 * 86400000).toString());
      this.showInstallPrompts(false);
    }

    /**
     * Notifikasi pembaruan aplikasi tersedia
     */
    notifyUpdateAvailable() {
      const existingToast = document.getElementById('pwaUpdateToast');
      if (existingToast) return;

      const toast = document.createElement('div');
      toast.id = 'pwaUpdateToast';
      toast.className = 'fixed bottom-20 lg:bottom-6 right-4 lg:right-6 z-50 max-w-sm p-4 rounded-2xl bg-surface border border-brand/40 shadow-2xl flex items-start gap-3.5 transition-all transform duration-300';
      toast.innerHTML = `
        <div class="w-9 h-9 rounded-xl bg-brand/15 text-brand flex items-center justify-center shrink-0 mt-0.5">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v6h6M20 20v-6h-6M5.5 15A7 7 0 0 0 18 17.5M18.5 9A7 7 0 0 0 6 6.5"/>
          </svg>
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-display font-bold text-sm text-ink">Versi Baru SAMSARA OPS</p>
          <p class="text-xs text-muted mt-0.5">Pembaruan sistem operasional siap dipasang.</p>
          <div class="mt-2.5 flex items-center gap-2">
            <button type="button" id="pwaBtnApplyUpdate" class="h-8 px-3 rounded-lg bg-brand text-white font-semibold text-xs shadow-md shadow-brand/25 active:scale-95 transition">
              Perbarui Sekarang
            </button>
            <button type="button" onclick="this.closest('#pwaUpdateToast').remove()" class="h-8 px-2 text-xs text-subtle hover:text-ink">
              Nanti
            </button>
          </div>
        </div>
      `;

      document.body.appendChild(toast);

      document.getElementById('pwaBtnApplyUpdate')?.addEventListener('click', () => {
        this.applyUpdate();
      });
    }

    /**
     * Terapkan update baru (Kirim sinyal SKIP_WAITING ke SW)
     */
    applyUpdate() {
      if (this.waitingWorker) {
        this.waitingWorker.postMessage({ type: 'SKIP_WAITING' });
      } else {
        window.location.reload();
      }
    }

    /**
     * Monitor perubahan konektivitas jaringan
     */
    handleNetworkChange(isOnline) {
      this.isOnlineState = isOnline;
      this.updateNetworkUI(isOnline);

      // Trigger custom event untuk listener modul lain (misal scanner offline)
      window.dispatchEvent(
        new CustomEvent('samsara:connectivity', {
          detail: { online: isOnline, timestamp: Date.now() },
        })
      );

      if (isOnline) {
        this.showToast('Koneksi internet terhubung kembali.', 'success');
      } else {
        this.showToast('Koneksi lapangan terputus. Mode offline aktif.', 'warning');
      }
    }

    /**
     * Update tampilan elemen status koneksi di Header
     */
    updateNetworkUI(isOnline) {
      document.querySelectorAll('.pwa-net-badge').forEach((el) => {
        const dot = el.querySelector('.pwa-net-dot');
        const label = el.querySelector('.pwa-net-label');

        if (isOnline) {
          el.classList.remove('bg-rose-500/10', 'text-rose-400', 'border-rose-500/30');
          el.classList.add('bg-emerald-500/10', 'text-emerald-400', 'border-emerald-500/30');
          if (dot) {
            dot.classList.remove('bg-rose-500', 'animate-pulse');
            dot.classList.add('bg-emerald-400');
          }
          if (label) label.textContent = 'Online';
        } else {
          el.classList.remove('bg-emerald-500/10', 'text-emerald-400', 'border-emerald-500/30');
          el.classList.add('bg-rose-500/10', 'text-rose-400', 'border-rose-500/30');
          if (dot) {
            dot.classList.remove('bg-emerald-400');
            dot.classList.add('bg-rose-500', 'animate-pulse');
          }
          if (label) label.textContent = 'Offline';
        }
      });
    }

    /**
     * Cek apakah berjalan di PWA Standalone Mode
     */
    checkStandaloneMode() {
      const isStandalone =
        window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true;

      if (isStandalone) {
        document.documentElement.classList.add('pwa-standalone');
        console.info('[PWA] Berjalan di mode Standalone.');
      }
    }

    /**
     * Pasang listener pada tombol install yang ada di DOM
     */
    bindActionButtons() {
      document.querySelectorAll('.pwa-btn-install').forEach((btn) => {
        btn.addEventListener('click', () => this.promptInstall());
      });
      document.querySelectorAll('.pwa-btn-dismiss').forEach((btn) => {
        btn.addEventListener('click', () => this.dismissInstallPrompt());
      });
    }

    /**
     * Utilitas toast UI SAMSARA
     */
    showToast(message, type = 'info') {
      const existing = document.getElementById('pwaNoticeToast');
      if (existing) existing.remove();

      const tones = {
        success: { bg: 'bg-emerald-500/15', border: 'border-emerald-500/30', text: 'text-emerald-300', dot: 'bg-emerald-400' },
        warning: { bg: 'bg-amber-500/15', border: 'border-amber-500/30', text: 'text-amber-300', dot: 'bg-amber-400' },
        info: { bg: 'bg-raised', border: 'border-line', text: 'text-ink', dot: 'bg-brand' },
      };
      const tone = tones[type] || tones.info;

      const toast = document.createElement('div');
      toast.id = 'pwaNoticeToast';
      toast.className = `fixed bottom-20 lg:bottom-6 left-1/2 -translate-x-1/2 z-50 w-max max-w-[calc(100vw-2rem)] px-4 py-2.5 rounded-full ${tone.bg} border ${tone.border} shadow-xl flex items-center gap-2.5 text-xs font-semibold ${tone.text} transition-all duration-300 pointer-events-none`;
      toast.innerHTML = `<span class="w-2 h-2 shrink-0 rounded-full ${tone.dot}"></span><span class="truncate">${message}</span>`;

      document.body.appendChild(toast);
      setTimeout(() => {
        if (toast.parentNode) {
          toast.style.opacity = '0';
          toast.style.transform = 'translate(-50%, 10px)';
          setTimeout(() => toast.remove(), 300);
        }
      }, 3500);
    }
  }

  // Inisialisasi Singleton Global
  window.samsaraPwa = new SamsaraPWA();

})(window, document);
