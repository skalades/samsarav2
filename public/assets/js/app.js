/**
 * SAMSARA EXPEDITION — Mobile Application Controller
 * Scalable, Modular, and Maintainable UI Logic
 */

const App = {
  state: {
    climbers: 4,
    ojekCount: 2,
    hasVehicle: false,
    motorcycleCount: 0,
    carCount: 0,
    date: (new Date()).toISOString().split('T')[0],
    returnDate: (new Date(Date.now() + 86400000)).toISOString().split('T')[0],
    activeNav: 'sec-booking'
  },

  init() {
    this.bindEvents();
    this.renderTelemetry();
    this.renderWaypoints();
    
    // Set initial stepper boxes
    const bookOjekBox = document.getElementById('bookOjekCountBox');
    if (bookOjekBox) bookOjekBox.innerText = this.state.ojekCount;

    this.booking.recalc();

    // Check for saved local draft (anti-terclose recovery)
    this.draft.checkDraft();

    // Check initial hash for deep-link tab navigation
    const initialHash = window.location.hash.replace('#', '');
    const validTabs = ['sec-beranda', 'sec-booking', 'sec-telemetry', 'sec-waypoints', 'sec-logistik'];
    if (validTabs.includes(initialHash)) {
      this.nav.switchTab(initialHash);
    } else {
      this.nav.switchTab('sec-beranda');
    }

    // Deep-link check: #cek-tiket or ?ticket=SMK-...
    const urlParams = new URLSearchParams(window.location.search);
    const ticketParam = urlParams.get('ticket');
    if (ticketParam || window.location.hash === '#cek-tiket') {
      setTimeout(() => {
        if (ticketParam) {
          const input = document.getElementById('searchTicketInput');
          if (input) input.value = ticketParam;
        }
        this.ticket.openModal();
      }, 200);
    }
  },

  bindEvents() {
    // Dynamic date picker default & live quota sync
    const dateInput = document.getElementById('bookDate');
    if (dateInput && !dateInput.value) {
      dateInput.value = this.state.date;
    }

    const handleDateChange = async (e) => {
      const selectedDate = e.target.value;
      if (!selectedDate) return;
      try {
        const resp = await fetch('/api/simaksi/quota?date=' + encodeURIComponent(selectedDate));
        const data = await resp.json();
        if (data && data.success) {
          const heroQuota = document.getElementById('heroRemainingQuota');
          if (heroQuota) {
            heroQuota.innerText = `${data.remaining_quota} TERSISA`;
            if (data.is_closed || data.remaining_quota === 0) {
              heroQuota.className = 'text-rose-400 font-bold text-sm';
              heroQuota.innerText = data.is_closed ? 'JALUR DITUTUP' : 'KUOTA PENUH (0 TERSISA)';
            } else if (data.remaining_quota < 30) {
              heroQuota.className = 'text-amber-400 font-bold text-sm';
            } else {
              heroQuota.className = 'text-[#FF5722] font-bold text-sm';
            }
          }
        }
      } catch (err) {
        console.warn('Live quota check warning:', err);
      }
    };

    if (dateInput) dateInput.addEventListener('change', handleDateChange);
    const wzDateEl = document.getElementById('wzHikingDate');
    if (wzDateEl) wzDateEl.addEventListener('change', handleDateChange);

    // Reactive Auto-Save for Multi-step Form Inputs
    const formFields = [
      'wzHikingDate', 'wzReturnDate', 'wzLeaderName', 'wzLeaderNik',
      'wzLeaderPhone', 'wzLeaderCity', 'wzLeaderAddress', 'wzLeaderMedical', 'wzEmergName', 'wzEmergPhone', 'wzEmergRel'
    ];
    formFields.forEach(id => {
      const el = document.getElementById(id);
      if (el) {
        el.addEventListener('input', () => this.draft.save());
        el.addEventListener('change', () => this.draft.save());
      }
    });

    const membersListContainer = document.getElementById('wzMembersList');
    if (membersListContainer) {
      membersListContainer.addEventListener('input', () => this.draft.save());
      membersListContainer.addEventListener('change', () => this.draft.save());
    }
  },

  /**
   * Client-side Image Compression Engine (Pure HTML5 Canvas)
   * Scalable & zero server CPU footprint: resizes & compresses high-res camera photos (3-8MB) to ~150-250KB JPEG
   * keeping sharp text legibility for NIK KTP & proof of transfer receipts.
   */
  compressImage(file, options = {}) {
    const maxWidth = options.maxWidth || 1280;
    const maxHeight = options.maxHeight || 1280;
    const quality = options.quality || 0.78;

    return new Promise((resolve) => {
      // If not an image (e.g. PDF), resolve original file
      if (!file || !file.type || !file.type.startsWith('image/')) {
        resolve({
          file: file,
          compressed: false,
          originalSize: file ? file.size : 0,
          compressedSize: file ? file.size : 0,
          ratio: 0,
          dataUrl: null
        });
        return;
      }

      const reader = new FileReader();
      reader.onerror = () => {
        resolve({ file, compressed: false, originalSize: file.size, compressedSize: file.size, ratio: 0 });
      };
      reader.onload = (e) => {
        const img = new Image();
        img.onerror = () => {
          resolve({ file, compressed: false, originalSize: file.size, compressedSize: file.size, ratio: 0 });
        };
        img.onload = () => {
          let width = img.width;
          let height = img.height;

          if (width > height) {
            if (width > maxWidth) {
              height = Math.round((height * maxWidth) / width);
              width = maxWidth;
            }
          } else {
            if (height > maxHeight) {
              width = Math.round((width * maxHeight) / height);
              height = maxHeight;
            }
          }

          const canvas = document.createElement('canvas');
          canvas.width = width;
          canvas.height = height;

          const ctx = canvas.getContext('2d');
          ctx.fillStyle = '#FFFFFF';
          ctx.fillRect(0, 0, width, height);
          ctx.drawImage(img, 0, 0, width, height);

          canvas.toBlob(
            (blob) => {
              if (!blob) {
                resolve({ file, compressed: false, originalSize: file.size, compressedSize: file.size, ratio: 0 });
                return;
              }

              const ext = 'jpg';
              const baseName = file.name ? file.name.replace(/\.[^/.]+$/, '') : 'photo';
              const compressedFile = new File([blob], `${baseName}_compressed.${ext}`, {
                type: 'image/jpeg',
                lastModified: Date.now()
              });

              const isSmaller = compressedFile.size < file.size;
              const finalFile = isSmaller ? compressedFile : file;
              const savedBytes = file.size - finalFile.size;
              const ratio = Math.max(0, Math.round((savedBytes / file.size) * 100));

              resolve({
                file: finalFile,
                compressed: isSmaller,
                originalSize: file.size,
                compressedSize: finalFile.size,
                ratio: ratio,
                dataUrl: canvas.toDataURL('image/jpeg', quality)
              });
            },
            'image/jpeg',
            quality
          );
        };
        img.src = e.target.result;
      };
      reader.readAsDataURL(file);
    });
  },

  renderTelemetry() {
    const t = SAMSARA_CONFIG.telemetry;
    const elStatus = document.getElementById('telemetryTrailStatus');
    const elWater = document.getElementById('telemetryWaterStatus');
    const elTemp = document.getElementById('telemetryNightTemp');
    const elSoil = document.getElementById('telemetrySoilStatus');

    // Preserve live server Blade telemetry from DB; only fallback if element is empty
    if (elStatus && !elStatus.innerText.trim()) elStatus.innerText = t.trailStatus;
    if (elWater && !elWater.innerText.trim()) elWater.innerText = t.pos2WaterSpring;
    if (elTemp && !elTemp.innerText.trim()) elTemp.innerText = t.nightTemperature;
    if (elSoil && !elSoil.innerText.trim()) elSoil.innerText = t.soilCondition;
  },

  renderWaypoints() {
    const container = document.getElementById('waypointContainer');
    if (!container || container.dataset.static) return; // timeline sudah dirender server dari pengaturan jalur

    const wps = SAMSARA_CONFIG.waypoints;
    const last = wps.length - 1;
    const fmt = (n) => Number(n).toLocaleString('id-ID');

    // Ringkasan jarak & jumlah pos dihitung dari data rute itu sendiri
    const totalKm = wps.reduce((sum, wp) => sum + (parseFloat(String(wp.distance).replace(/[^0-9.]/g, '')) || 0), 0);
    const summary = document.getElementById('wpSummary');
    if (summary) summary.textContent = `${totalKm.toFixed(1).replace('.', ',')} km · ${wps.length} pos`;

    container.innerHTML = '<ol class="relative">' + wps.map((wp, idx) => {
      const isLast = idx === last;
      const badges = [
        wp.waterSource ? '<span class="inline-flex items-center h-6 px-2 rounded-full bg-sky-500/10 text-sky-300 ring-1 ring-inset ring-sky-500/25 text-[11px] font-semibold">Sumber air</span>' : '',
        wp.campArea ? '<span class="inline-flex items-center h-6 px-2 rounded-full bg-emerald-500/10 text-emerald-300 ring-1 ring-inset ring-emerald-500/25 text-[11px] font-semibold">Area camp</span>' : '',
      ].join('');

      return `
      <li class="relative flex gap-4 ${isLast ? '' : 'pb-5'}">
        ${isLast ? '' : '<span class="absolute left-[15px] top-8 bottom-0 w-0.5 bg-line-strong" aria-hidden="true"></span>'}
        <span class="relative z-10 w-8 h-8 shrink-0 rounded-full flex items-center justify-center ${isLast ? 'bg-brand text-white' : 'bg-raised ring-2 ring-line-strong text-muted'}">
          ${isLast ? '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m3 20 6.5-11 4 6.5 2.5-4L21 20H3Z"/></svg>' : `<span class="text-[11px] font-bold tabular-nums">${idx}</span>`}
        </span>
        <div class="flex-1 min-w-0 rounded-2xl bg-surface ring-1 ${isLast ? 'ring-brand/30' : 'ring-line'} p-4">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="font-code text-[11px] font-semibold ${isLast ? 'text-brand-soft' : 'text-subtle'}">${wp.code}</p>
              <h4 class="font-display font-bold text-base text-ink mt-0.5 leading-snug">${wp.name}</h4>
            </div>
            <span class="shrink-0 font-display font-bold text-ink tabular-nums">${fmt(wp.elevation)} <span class="text-xs font-medium text-muted">mdpl</span></span>
          </div>
          <div class="flex flex-wrap items-center gap-1.5 mt-2.5">
            <span class="inline-flex items-center h-6 px-2 rounded-full bg-raised text-muted ring-1 ring-inset ring-line text-[11px] font-medium">${wp.distance}</span>
            <span class="inline-flex items-center h-6 px-2 rounded-full bg-raised text-muted ring-1 ring-inset ring-line text-[11px] font-medium">${wp.duration}</span>
            ${badges}
          </div>
          <p class="text-sm text-muted mt-2.5 leading-relaxed">${wp.description}</p>
          ${wp.criticalWarning ? `
            <div class="mt-3 flex gap-2.5 p-3 rounded-xl bg-amber-500/10 ring-1 ring-amber-500/25 text-sm text-amber-200">
              <svg class="w-5 h-5 shrink-0 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 9v4m0 4h.01M10.3 4.2 2.6 17.5A2 2 0 0 0 4.3 20.5h15.4a2 2 0 0 0 1.7-3L13.7 4.2a2 2 0 0 0-3.4 0Z"/></svg>
              <span>${wp.criticalWarning}</span>
            </div>` : ''}
        </div>
      </li>`;
    }).join('') + '</ol>';
  },

  nav: {
    currentTab: 'sec-beranda',

    switchTab(tabId) {
      if (!tabId) tabId = 'sec-beranda';
      this.currentTab = tabId;

      const tabs = ['sec-beranda', 'sec-booking', 'sec-telemetry', 'sec-waypoints', 'sec-logistik'];
      tabs.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
          if (id === tabId) {
            el.classList.remove('hidden');
          } else {
            el.classList.add('hidden');
          }
        }
      });

      // Update Top Nav Tab Buttons
      document.querySelectorAll('.tab-btn').forEach(btn => {
        const active = btn.getAttribute('data-tab') === tabId;
        btn.classList.toggle('bg-raised', active);
        btn.classList.toggle('text-ink', active);
        btn.classList.toggle('ring-1', active);
        btn.classList.toggle('ring-line-strong', active);
        btn.classList.toggle('text-muted', !active);
      });

      // Update Mobile Bottom Nav Items
      document.querySelectorAll('.nav-item').forEach(item => {
        const target = item.getAttribute('data-tab') || item.getAttribute('href')?.replace('#', '');
        if (!target || !target.startsWith('sec-')) return;
        const active = target === tabId;
        item.classList.toggle('text-brand', active && !item.classList.contains('nav-cta'));
        item.classList.toggle('text-ink', active && item.classList.contains('nav-cta'));
        item.classList.toggle('text-muted', !active);
      });

      // Smooth scroll to top of content container if scrolled down
      if (window.scrollY > 80) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    },

    setActive(element) {
      const target = element.getAttribute('data-tab') || element.getAttribute('href')?.replace('#', '');
      if (target) {
        this.switchTab(target);
      }
    }
  },

  booking: {
    adjustClimbers(delta) {
      const min = SAMSARA_CONFIG.expedition.minClimbersPerGroup;
      const max = SAMSARA_CONFIG.expedition.maxClimbersPerGroup;
      App.state.climbers = Math.min(max, Math.max(min, App.state.climbers + delta));

      const countBox = document.getElementById('climberCountBox');
      const countLabel = document.getElementById('climberCountLabel');

      const climberText = App.state.climbers === 1 ? '1 ORANG (SOLO)' : `${App.state.climbers} ORANG`;
      if (countBox) countBox.innerText = App.state.climbers;
      if (countLabel) countLabel.innerText = climberText;

      this.recalc();
    },

    adjustOjek(delta) {
      App.state.ojekCount = Math.max(0, Math.min(30, App.state.ojekCount + delta));
      const box = document.getElementById('bookOjekCountBox');
      if (box) box.innerText = App.state.ojekCount;
      this.recalc();
    },

    toggleVehicle(checked) {
      App.state.hasVehicle = checked;
      const opts = document.getElementById('bookVehicleOptions');
      if (opts) {
        if (checked) {
          opts.classList.remove('hidden');
          if (App.state.motorcycleCount === 0 && App.state.carCount === 0) {
            App.state.motorcycleCount = 1;
            const motorBox = document.getElementById('bookMotorCountBox');
            if (motorBox) motorBox.innerText = '1';
          }
        } else {
          opts.classList.add('hidden');
        }
      }
      this.recalc();
    },

    adjustMotorcycle(delta) {
      App.state.motorcycleCount = Math.max(0, Math.min(30, App.state.motorcycleCount + delta));
      const box = document.getElementById('bookMotorCountBox');
      if (box) box.innerText = App.state.motorcycleCount;
      this.recalc();
    },

    adjustCar(delta) {
      App.state.carCount = Math.max(0, Math.min(15, App.state.carCount + delta));
      const box = document.getElementById('bookCarCountBox');
      if (box) box.innerText = App.state.carCount;
      this.recalc();
    },

    recalc() {
      const count = App.state.climbers;
      const ojekCount = App.state.ojekCount;
      const hasVehicle = App.state.hasVehicle;
      const motorCount = hasVehicle ? App.state.motorcycleCount : 0;
      const carCount = hasVehicle ? App.state.carCount : 0;

      const simaksiCost = count * SAMSARA_CONFIG.pricing.simaksiPerPerson;
      const ojekCost = ojekCount * SAMSARA_CONFIG.pricing.ojekPerTrip;
      const parkingCost = (motorCount * (SAMSARA_CONFIG.pricing.parkingMotor || 10000)) + (carCount * (SAMSARA_CONFIG.pricing.parkingCar || 25000));
      const total = simaksiCost + ojekCost + parkingCost;

      const setEl = (id, text) => {
        const el = document.getElementById(id);
        if (el) el.innerText = text;
      };

      setEl('txtSimaksiSub', `SIMAKSI (${count} Org):`);
      setEl('valSimaksiSub', `Rp ${simaksiCost.toLocaleString('id-ID')}`);
      setEl('bookOjekTotalLabel', `Rp ${ojekCost.toLocaleString('id-ID')}`);
      setEl('txtOjekSub', `Ojek (${ojekCount} Armada):`);
      setEl('valOjekSub', `Rp ${ojekCost.toLocaleString('id-ID')}`);

      const parkSubRow = document.getElementById('rowParkirSub');
      if (parkSubRow) {
        if (hasVehicle && (motorCount > 0 || carCount > 0)) {
          parkSubRow.classList.remove('hidden');
          const details = [];
          if (motorCount > 0) details.push(`${motorCount} Motor`);
          if (carCount > 0) details.push(`${carCount} Mobil`);
          setEl('txtParkirSub', `Parkir (${details.join(', ')}):`);
          setEl('valParkirSub', `Rp ${parkingCost.toLocaleString('id-ID')}`);
        } else {
          parkSubRow.classList.add('hidden');
        }
      }
      setEl('bookParkingTotalLabel', `Rp ${parkingCost.toLocaleString('id-ID')}`);
      setEl('valTotalSub', `Rp ${total.toLocaleString('id-ID')}`);
    },

    openRegistrationModal() {
      App.wizard.open();
    }
  },

  /**
   * MULTI-STEP WIZARD CONTROLLER
   * Handles touch-friendly 4-step wizard modal for self-service SIMAKSI
   */
  wizard: {
    currentStep: 1,
    selectedFile: null,
    selectedKtpFile: null,
    selectedMethod: 'qris',
    lastSavedCode: null,
    ojekCount: 2,
    uniqueCode: 0,
    lastCalculatedTotal: 0,

    stepConfig: {
      1: { label: 'LANGKAH 1/4: JADWAL & LOGISTIK', percent: '25%' },
      2: { label: 'LANGKAH 2/4: DATA KETUA & KONTAK DARURAT', percent: '50%' },
      3: { label: 'LANGKAH 3/4: MANIFEST ANGGOTA ROMBONGAN', percent: '75%' },
      4: { label: 'LANGKAH 4/4: PEMBAYARAN & BUKTI BAYAR', percent: '100%' },
      5: { label: 'TIKET RESMI BERHASIL DITERBITKAN', percent: '100%' }
    },

    open() {
      const date = document.getElementById('bookDate')?.value || App.state.date;
      this.ojekCount = App.state.ojekCount;
      
      // Generate a persistent 3-digit unique code for this booking session (e.g. 101 - 899)
      if (!this.uniqueCode) {
        this.uniqueCode = Math.floor(101 + Math.random() * 790);
      }

      // Sync wizard Step 1 inputs from home booking widget
      const wzDate = document.getElementById('wzHikingDate');
      const wzReturn = document.getElementById('wzReturnDate');
      const wzCountBox = document.getElementById('wzClimberCountBox');
      const wzCountLbl = document.getElementById('wzClimberCountLabel');
      const wzOjekBox = document.getElementById('wzOjekCountBox');

      if (wzDate) wzDate.value = date;
      if (wzReturn) {
        const nextDay = new Date(date);
        nextDay.setDate(nextDay.getDate() + 1);
        wzReturn.value = nextDay.toISOString().split('T')[0];
      }
      if (wzCountBox) wzCountBox.innerText = App.state.climbers;
      if (wzCountLbl) wzCountLbl.innerText = `${App.state.climbers} ORANG`;
      if (wzOjekBox) wzOjekBox.innerText = this.ojekCount;

      // Sync vehicle inputs
      const wzHasVehicle = document.getElementById('wzHasVehicle');
      const wzVehicleOpts = document.getElementById('wzVehicleOptions');
      const wzMotorBox = document.getElementById('wzMotorCountBox');
      const wzCarBox = document.getElementById('wzCarCountBox');
      if (wzHasVehicle) wzHasVehicle.checked = App.state.hasVehicle;
      if (wzVehicleOpts) {
        if (App.state.hasVehicle) wzVehicleOpts.classList.remove('hidden');
        else wzVehicleOpts.classList.add('hidden');
      }
      if (wzMotorBox) wzMotorBox.innerText = App.state.motorcycleCount;
      if (wzCarBox) wzCarBox.innerText = App.state.carCount;

      this.hideError();
      this.recalc();
      this.goTo(1);
      App.modal.open('regModal');
    },

    goTo(step) {
      this.currentStep = step;

      // Toggle step sections
      for (let s = 1; s <= 5; s++) {
        const stepEl = document.getElementById(`wizardStep${s}`);
        if (stepEl) {
          if (s === step) {
            stepEl.classList.remove('hidden');
          } else {
            stepEl.classList.add('hidden');
          }
        }
      }

      // Update progress label & bar
      const info = this.stepConfig[step] || this.stepConfig[1];
      const lbl = document.getElementById('wizardStepLabel');
      const pct = document.getElementById('wizardStepPercent');
      const bar = document.getElementById('wizardProgressBar');

      if (lbl) lbl.innerText = info.label;
      if (pct) pct.innerText = info.percent;
      if (bar) bar.style.width = info.percent;

      // Update step pill tabs
      for (let p = 1; p <= 4; p++) {
        const pill = document.getElementById(`stepPill${p}`);
        if (pill) {
          if (p === step) {
            pill.className = 'py-1 rounded bg-[#FF5722]/15 text-[#FF5722] border border-[#FF5722]/40 font-bold transition';
          } else if (p < step) {
            pill.className = 'py-1 rounded bg-[#181B22] text-stone-300 border border-emerald-500/30 font-medium transition';
          } else {
            pill.className = 'py-1 rounded bg-[#181B22] text-stone-500 border border-[#222731] font-medium transition';
          }
        }
      }

      // Scroll modal body smoothly to top
      const modalScrollBody = document.querySelector('#regModal .overflow-y-auto');
      if (modalScrollBody) modalScrollBody.scrollTop = 0;

      // Auto-save progress to localStorage
      if (step >= 1 && step <= 4) {
        App.draft?.save();
      }
    },

    adjustClimbers(delta) {
      App.booking.adjustClimbers(delta);
      const wzBox = document.getElementById('wzClimberCountBox');
      const wzLbl = document.getElementById('wzClimberCountLabel');
      const climberText = App.state.climbers === 1 ? '1 ORANG (SOLO)' : `${App.state.climbers} ORANG`;
      if (wzBox) wzBox.innerText = App.state.climbers;
      if (wzLbl) wzLbl.innerText = climberText;
      this.recalc();
    },

    adjustOjek(delta) {
      this.ojekCount = Math.max(0, Math.min(30, this.ojekCount + delta));
      App.state.ojekCount = this.ojekCount;
      const wzBox = document.getElementById('wzOjekCountBox');
      const bookBox = document.getElementById('bookOjekCountBox');
      if (wzBox) wzBox.innerText = this.ojekCount;
      if (bookBox) bookBox.innerText = this.ojekCount;
      this.recalc();
      App.booking.recalc();
    },

    toggleVehicle(checked) {
      App.booking.toggleVehicle(checked);
      const wzHasVehicle = document.getElementById('wzHasVehicle');
      const bookHasVehicle = document.getElementById('bookHasVehicle');
      const wzVehicleOpts = document.getElementById('wzVehicleOptions');
      if (wzHasVehicle) wzHasVehicle.checked = checked;
      if (bookHasVehicle) bookHasVehicle.checked = checked;
      if (wzVehicleOpts) {
        if (checked) wzVehicleOpts.classList.remove('hidden');
        else wzVehicleOpts.classList.add('hidden');
      }
      const wzMotorBox = document.getElementById('wzMotorCountBox');
      if (wzMotorBox) wzMotorBox.innerText = App.state.motorcycleCount;
      const wzCarBox = document.getElementById('wzCarCountBox');
      if (wzCarBox) wzCarBox.innerText = App.state.carCount;
      this.recalc();
      App.booking.recalc();
    },

    adjustMotorcycle(delta) {
      App.booking.adjustMotorcycle(delta);
      const wzMotorBox = document.getElementById('wzMotorCountBox');
      if (wzMotorBox) wzMotorBox.innerText = App.state.motorcycleCount;
      this.recalc();
      App.booking.recalc();
    },

    adjustCar(delta) {
      App.booking.adjustCar(delta);
      const wzCarBox = document.getElementById('wzCarCountBox');
      if (wzCarBox) wzCarBox.innerText = App.state.carCount;
      this.recalc();
      App.booking.recalc();
    },

    category: 'camp',

    setCategory(cat) {
      this.category = cat;
      const tektokLabel = document.getElementById('catTektokLabel');
      const campLabel = document.getElementById('catCampLabel');
      const campsiteBox = document.getElementById('wzCampsiteBox');
      const returnDate = document.getElementById('wzReturnDate');
      const returnDateLabel = document.getElementById('wzReturnDateLabel');
      const hikingDate = document.getElementById('wzHikingDate');

      if (cat === 'tektok') {
        if (tektokLabel) {
          tektokLabel.className = 'relative flex flex-col p-2.5 rounded-xl border border-cyan-400 bg-cyan-500/15 cursor-pointer transition';
        }
        if (campLabel) {
          campLabel.className = 'relative flex flex-col p-2.5 rounded-xl border border-[#2A303C] bg-[#16191F] cursor-pointer hover:border-emerald-400/50 transition';
        }
        if (campsiteBox) campsiteBox.classList.add('hidden');
        if (returnDate && hikingDate) {
          returnDate.value = hikingDate.value;
          returnDate.disabled = true;
          returnDate.classList.add('opacity-50', 'cursor-not-allowed');
        }
        if (returnDateLabel) returnDateLabel.innerText = 'TURUN HARI INI (TEKTOK)';
      } else {
        if (campLabel) {
          campLabel.className = 'relative flex flex-col p-2.5 rounded-xl border border-emerald-500/50 bg-emerald-500/10 cursor-pointer hover:border-emerald-400 transition';
        }
        if (tektokLabel) {
          tektokLabel.className = 'relative flex flex-col p-2.5 rounded-xl border border-[#2A303C] bg-[#16191F] cursor-pointer hover:border-cyan-400/50 transition';
        }
        if (campsiteBox) campsiteBox.classList.remove('hidden');
        if (returnDate) {
          returnDate.disabled = false;
          returnDate.classList.remove('opacity-50', 'cursor-not-allowed');
          if (hikingDate && hikingDate.value) {
            const nextDay = new Date(hikingDate.value);
            nextDay.setDate(nextDay.getDate() + 1);
            returnDate.value = nextDay.toISOString().split('T')[0];
          }
        }
        if (returnDateLabel) returnDateLabel.innerText = 'ESTIMASI TURUN';
      }
      this.recalc();
    },

    onDateChange() {
      const hikingDate = document.getElementById('wzHikingDate');
      const returnDate = document.getElementById('wzReturnDate');
      if (!hikingDate || !returnDate) return;

      if (this.category === 'tektok') {
        returnDate.value = hikingDate.value;
      } else {
        const nextDay = new Date(hikingDate.value);
        nextDay.setDate(nextDay.getDate() + 1);
        returnDate.value = nextDay.toISOString().split('T')[0];
      }
    },

    recalc() {
      if (!this.uniqueCode) {
        this.uniqueCode = Math.floor(101 + Math.random() * 790);
      }

      const count = App.state.climbers;
      const simaksiCost = count * SAMSARA_CONFIG.pricing.simaksiPerPerson;
      const ojekCost = this.ojekCount * SAMSARA_CONFIG.pricing.ojekPerTrip;
      const hasVehicle = App.state.hasVehicle;
      const motorCount = hasVehicle ? App.state.motorcycleCount : 0;
      const carCount = hasVehicle ? App.state.carCount : 0;
      const parkingCost = (motorCount * (SAMSARA_CONFIG.pricing.parkingMotor || 10000)) + (carCount * (SAMSARA_CONFIG.pricing.parkingCar || 25000));
      const subtotal = simaksiCost + ojekCost + parkingCost;
      
      const effectiveUnique = (this.selectedMethod === 'cash_pos') ? 0 : this.uniqueCode;
      const total = subtotal + effectiveUnique;
      this.lastCalculatedTotal = total;

      const setEl = (id, text) => {
        const el = document.getElementById(id);
        if (el) el.innerText = text;
      };

      setEl('wzTxtSimaksiSub', `SIMAKSI (${count} Org @ Rp ${SAMSARA_CONFIG.pricing.simaksiPerPerson.toLocaleString('id-ID')}):`);
      setEl('wzValSimaksiSub', `Rp ${simaksiCost.toLocaleString('id-ID')}`);
      setEl('wzOjekTotalLabel', `Rp ${ojekCost.toLocaleString('id-ID')}`);
      setEl('wzTxtOjekSub', `Shuttle Ojek (${this.ojekCount} Armada):`);
      setEl('wzValOjekSub', `Rp ${ojekCost.toLocaleString('id-ID')}`);

      // Parking breakdown in wizard Step 1 & Step 4
      const wzParkRow = document.getElementById('wzRowParkirSub');
      if (wzParkRow) {
        if (hasVehicle && (motorCount > 0 || carCount > 0)) {
          wzParkRow.classList.remove('hidden');
          const details = [];
          if (motorCount > 0) details.push(`${motorCount} Motor`);
          if (carCount > 0) details.push(`${carCount} Mobil`);
          setEl('wzTxtParkirSub', `Parkir (${details.join(', ')}):`);
          setEl('wzValParkirSub', `Rp ${parkingCost.toLocaleString('id-ID')}`);
        } else {
          wzParkRow.classList.add('hidden');
        }
      }
      setEl('wzParkingTotalLabel', `Rp ${parkingCost.toLocaleString('id-ID')}`);
      setEl('wzValTotalSub', `Rp ${subtotal.toLocaleString('id-ID')}`);
      
      // Step 4 Payment breakdown
      setEl('wzSubtotalPay', `Rp ${subtotal.toLocaleString('id-ID')}`);
      const payParkRow = document.getElementById('wzParkingPayRow');
      if (payParkRow) {
        if (hasVehicle && parkingCost > 0) {
          payParkRow.classList.remove('hidden');
          setEl('wzParkingPay', `Rp ${parkingCost.toLocaleString('id-ID')}`);
        } else {
          payParkRow.classList.add('hidden');
        }
      }
      setEl('wzUniqueCodePay', `+Rp ${Number(effectiveUnique).toLocaleString('id-ID')}`);
      setEl('wzFinalTotal', `Rp ${total.toLocaleString('id-ID')}`);

      const uniqueRow = document.getElementById('wzUniqueCodeRow');
      const uniqueNote = document.getElementById('wzUniqueNote');
      const totalLbl = document.getElementById('wzTotalLabel');
      const copyBtn = document.getElementById('btnCopyNominal');

      if (this.selectedMethod === 'cash_pos') {
        if (uniqueRow) uniqueRow.classList.add('hidden');
        if (uniqueNote) uniqueNote.classList.add('hidden');
        if (totalLbl) totalLbl.innerText = 'TOTAL BAYAR LOKET';
        if (copyBtn) copyBtn.classList.add('hidden');
      } else {
        if (uniqueRow) uniqueRow.classList.remove('hidden');
        if (uniqueNote) uniqueNote.classList.remove('hidden');
        if (totalLbl) totalLbl.innerText = 'TOTAL TRANSFER';
        if (copyBtn) copyBtn.classList.remove('hidden');
      }
    },

    copyTransferAmount() {
      if (this.lastCalculatedTotal) {
        navigator.clipboard?.writeText(String(this.lastCalculatedTotal));
        alert(`Nominal transfer Rp ${this.lastCalculatedTotal.toLocaleString('id-ID')} (tepat hingga 3 digit kode unik) berhasil disalin.`);
      }
    },

    validateStep1() {
      const hikingDate = document.getElementById('wzHikingDate')?.value;
      if (!hikingDate) {
        alert('Silakan tentukan tanggal naik pendakian.');
        return;
      }
      this.goTo(2);
    },

    validateStep2() {
      const name = document.getElementById('wzLeaderName')?.value.trim();
      const nik = document.getElementById('wzLeaderNik')?.value.trim();
      const phone = document.getElementById('wzLeaderPhone')?.value.trim();
      const emergName = document.getElementById('wzEmergName')?.value.trim();
      const emergPhone = document.getElementById('wzEmergPhone')?.value.trim();

      if (!name) {
        alert('Mohon masukkan Nama Lengkap Ketua Tim sesuai KTP.');
        document.getElementById('wzLeaderName')?.focus();
        return;
      }

      if (!nik || nik.length < 10) {
        alert('Mohon masukkan Nomor KTP/NIK Ketua Tim yang valid (16 digit).');
        document.getElementById('wzLeaderNik')?.focus();
        return;
      }

      if (!phone) {
        alert('Mohon masukkan Nomor WhatsApp aktif Ketua Tim.');
        document.getElementById('wzLeaderPhone')?.focus();
        return;
      }

      const address = document.getElementById('wzLeaderAddress')?.value.trim() || document.getElementById('wzLeaderCity')?.value.trim();
      if (!address || address.length < 5) {
        alert('Mohon masukkan Alamat Lengkap domisili Ketua Tim sesuai KTP.');
        document.getElementById('wzLeaderAddress')?.focus();
        return;
      }

      if (!emergName || !emergPhone) {
        alert('Mohon lengkapi Nama & Nomor WhatsApp Kontak Darurat Keluarga.');
        document.getElementById('wzEmergName')?.focus();
        return;
      }

      if (!this.selectedKtpFile) {
        alert('Mohon lampirkan foto / scan KTP asli Ketua Tim untuk verifikasi petugas basecamp.');
        return;
      }

      // Update Member #1 summary in Step 3
      const sumName = document.getElementById('wzLeaderSummaryName');
      const sumNik = document.getElementById('wzLeaderSummaryNik');
      const roleTag = document.getElementById('wzLeaderRoleTag');
      const roleBadge = document.getElementById('wzLeaderRoleBadge');
      if (sumName) sumName.innerText = name;
      const addrDisplay = document.getElementById('wzLeaderAddress')?.value.trim() || document.getElementById('wzLeaderCity')?.value || 'Garut';
      if (sumNik) sumNik.innerText = `NIK: ${nik} • ${addrDisplay}`;
      if (roleTag) roleTag.innerText = App.state.climbers === 1 ? 'PENDAKI TUNGGAL' : 'ANGGOTA #1';
      if (roleBadge) roleBadge.innerText = App.state.climbers === 1 ? '[SOLO HIKER]' : '[KETUA TIM]';

      this.renderMembers();
      this.goTo(3);
    },

    renderMembers() {
      const container = document.getElementById('wzMembersList');
      if (!container) return;

      const totalClimbers = App.state.climbers;
      const roleTag = document.getElementById('wzLeaderRoleTag');
      const roleBadge = document.getElementById('wzLeaderRoleBadge');
      if (roleTag) roleTag.innerText = totalClimbers === 1 ? 'PENDAKI TUNGGAL' : 'ANGGOTA #1';
      if (roleBadge) roleBadge.innerText = totalClimbers === 1 ? '[SOLO HIKER]' : '[KETUA TIM]';

      if (totalClimbers === 1) {
        container.innerHTML = `
          <div class="p-4 bg-[#121418] border border-emerald-500/30 rounded-xl space-y-1.5 font-mono-tech text-center">
            <span class="text-xs font-bold text-emerald-400 block">[✓] PENDAKIAN SOLO (1 ORANG)</span>
            <p class="text-[10px] text-stone-400 leading-relaxed">
              Data identitas Anda pada Langkah 2 otomatis menjadi manifest pendaki tunggal. Tidak ada data anggota rombongan tambahan yang perlu diisi.
            </p>
          </div>
        `;
        return;
      }

      // Preserve already typed member values
      const existingData = [];
      for (let i = 2; i <= 15; i++) {
        const elName = document.getElementById(`wzMemName_${i}`);
        const elNik = document.getElementById(`wzMemNik_${i}`);
        const elGen = document.getElementById(`wzMemGen_${i}`);
        const elMed = document.getElementById(`wzMemMed_${i}`);
        if (elName && elName.value) {
          existingData[i] = {
            name: elName.value,
            nik: elNik ? elNik.value : '',
            gender: elGen ? elGen.value : 'L',
            medical: elMed ? elMed.value : 'Tidak Ada'
          };
        }
      }

      let html = '';
      for (let i = 2; i <= totalClimbers; i++) {
        const prev = existingData[i] || { name: '', nik: '', gender: 'L', medical: 'Tidak Ada' };
        html += `
          <div class="p-3 bg-[#111317] border border-[#222731] rounded-xl space-y-2">
            <div class="flex items-center justify-between pb-1.5 border-b border-[#1E2229]">
              <span class="px-2 py-0.5 rounded text-[9px] bg-stone-800 text-stone-300 font-bold tracking-wider">
                ANGGOTA #${i}
              </span>
              <span class="text-[9px] text-stone-500">WAJIB KTP</span>
            </div>

            <div>
              <label class="block text-[9px] text-stone-400 uppercase mb-0.5">Nama Lengkap Anggota #${i} <span class="text-[#FF5722]">*</span></label>
              <input type="text" id="wzMemName_${i}" value="${prev.name}" placeholder="Nama sesuai KTP" class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-[#FF5722]">
            </div>

            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block text-[9px] text-stone-400 uppercase mb-0.5">NIK (16 Digit) <span class="text-[#FF5722]">*</span></label>
                <input type="text" id="wzMemNik_${i}" value="${prev.nik}" maxlength="16" placeholder="NIK KTP Anggota #${i}" class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-[#FF5722]">
              </div>
              <div>
                <label class="block text-[9px] text-stone-400 uppercase mb-0.5">Jenis Kelamin</label>
                <select id="wzMemGen_${i}" class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-[#FF5722]">
                  <option value="L" ${prev.gender === 'L' ? 'selected' : ''}>Laki-laki</option>
                  <option value="P" ${prev.gender === 'P' ? 'selected' : ''}>Perempuan</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-[9px] text-stone-400 uppercase mb-0.5">Riwayat Penyakit Bawaan / Alergi</label>
              <input type="text" id="wzMemMed_${i}" value="${prev.medical || 'Tidak Ada'}" placeholder="Contoh: Tidak Ada / Asma, Alergi Dingin, dsb." class="w-full bg-[#181B22] border border-[#2A303C] rounded-lg px-2.5 py-1.5 text-xs text-white focus:outline-none focus:border-[#FF5722]">
            </div>
          </div>
        `;
      }

      container.innerHTML = html;
    },

    validateStep3() {
      const totalClimbers = App.state.climbers;
      for (let i = 2; i <= totalClimbers; i++) {
        const elName = document.getElementById(`wzMemName_${i}`);
        const elNik = document.getElementById(`wzMemNik_${i}`);
        if (!elName || !elName.value.trim()) {
          alert(`Mohon lengkapi Nama Anggota #${i}`);
          elName?.focus();
          return;
        }
        if (!elNik || !elNik.value.trim() || elNik.value.trim().length < 8) {
          alert(`Mohon lengkapi NIK Anggota #${i}`);
          elNik?.focus();
          return;
        }
      }

      this.recalc();
      this.goTo(4);
    },

    onMethodChange(method) {
      this.selectedMethod = method;

      // Toggle radio border styling
      const cards = ['lblMethodQris', 'lblMethodTransfer', 'lblMethodCash'];
      cards.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
          el.classList.remove('border-[#FF5722]');
          el.classList.add('border-[#222731]');
        }
      });

      const activeCardMap = {
        qris: 'lblMethodQris',
        transfer: 'lblMethodTransfer',
        cash_pos: 'lblMethodCash'
      };
      const activeCard = document.getElementById(activeCardMap[method]);
      if (activeCard) {
        activeCard.classList.remove('border-[#222731]');
        activeCard.classList.add('border-[#FF5722]');
      }

      // Toggle info panels
      const pQris = document.getElementById('methodDetailQris');
      const pTransfer = document.getElementById('methodDetailTransfer');
      const pCash = document.getElementById('methodDetailCash');
      const reqBadge = document.getElementById('wzProofRequiredBadge');

      if (pQris) pQris.classList.toggle('hidden', method !== 'qris');
      if (pTransfer) pTransfer.classList.toggle('hidden', method !== 'transfer');
      if (pCash) pCash.classList.toggle('hidden', method !== 'cash_pos');

      if (reqBadge) {
        reqBadge.innerText = (method === 'cash_pos') ? '(OPSIONAL DI KASIR)' : '* (WAJIB UNTUK VERIFIKASI)';
      }

      this.recalc();
    },

    async handleKtpSelect(event) {
      const file = event.target.files?.[0];
      if (!file) return;

      const badge = document.getElementById('wzKtpCompressBadge');
      const placeholder = document.getElementById('wzKtpPlaceholder');
      const previewBox = document.getElementById('wzKtpPreviewBox');
      const previewImg = document.getElementById('wzKtpPreviewImg');
      const fileName = document.getElementById('wzKtpFileName');
      const fileSize = document.getElementById('wzKtpFileSize');

      if (badge) {
        badge.innerText = '// MENGOMPRES FOTO KTP...';
        badge.classList.remove('hidden');
      }

      try {
        const res = await App.compressImage(file, { maxWidth: 1280, maxHeight: 1280, quality: 0.8 });
        this.selectedKtpFile = res.file;

        if (fileName) fileName.innerText = res.file.name;
        if (fileSize) {
          const compKb = (res.compressedSize / 1024).toFixed(0);
          fileSize.innerText = res.compressed 
            ? `${compKb} KB (Hemat ${res.ratio}%)` 
            : `${(file.size / 1024).toFixed(0)} KB`;
        }

        if (badge) {
          badge.innerText = res.compressed ? `// KOMPRESI SUKSES (-${res.ratio}%)` : '// FORMAT ASLI SIAP';
        }

        if (res.dataUrl) {
          if (previewImg) previewImg.src = res.dataUrl;
        } else {
          if (previewImg) previewImg.src = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="%23FF5722" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>';
        }

        if (placeholder) placeholder.classList.add('hidden');
        if (previewBox) previewBox.classList.remove('hidden');
      } catch (err) {
        console.error('KTP compression error:', err);
        this.selectedKtpFile = file;
        if (placeholder) placeholder.classList.add('hidden');
        if (previewBox) previewBox.classList.remove('hidden');
      }
    },

    removeKtpFile() {
      this.selectedKtpFile = null;
      const input = document.getElementById('wzLeaderKtpInput');
      if (input) input.value = '';

      const badge = document.getElementById('wzKtpCompressBadge');
      const placeholder = document.getElementById('wzKtpPlaceholder');
      const previewBox = document.getElementById('wzKtpPreviewBox');

      if (badge) badge.classList.add('hidden');
      if (placeholder) placeholder.classList.remove('hidden');
      if (previewBox) previewBox.classList.add('hidden');
    },

    async handleFileSelect(event) {
      const file = event.target.files?.[0];
      if (!file) return;

      const placeholder = document.getElementById('wzUploadPlaceholder');
      const previewBox = document.getElementById('wzUploadPreviewBox');
      const previewImg = document.getElementById('wzUploadPreviewImg');
      const fileName = document.getElementById('wzUploadFileName');
      const fileSize = document.getElementById('wzUploadFileSize');

      if (fileSize) fileSize.innerText = 'Mengompres bukti...';

      try {
        const res = await App.compressImage(file, { maxWidth: 1280, maxHeight: 1280, quality: 0.8 });
        this.selectedFile = res.file;

        if (fileName) fileName.innerText = res.file.name;
        if (fileSize) {
          const compKb = (res.compressedSize / 1024).toFixed(0);
          fileSize.innerText = res.compressed 
            ? `${compKb} KB (Hemat ${res.ratio}%)` 
            : `${(file.size / 1024).toFixed(0)} KB`;
        }

        if (res.dataUrl) {
          if (previewImg) previewImg.src = res.dataUrl;
        } else {
          if (previewImg) previewImg.src = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="%23FF5722" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>';
        }

        if (placeholder) placeholder.classList.add('hidden');
        if (previewBox) previewBox.classList.remove('hidden');
      } catch (err) {
        console.error('Proof compression error:', err);
        this.selectedFile = file;
        if (placeholder) placeholder.classList.add('hidden');
        if (previewBox) previewBox.classList.remove('hidden');
      }
    },

    removeFile() {
      this.selectedFile = null;
      const input = document.getElementById('wzPaymentProofInput');
      if (input) input.value = '';

      const placeholder = document.getElementById('wzUploadPlaceholder');
      const previewBox = document.getElementById('wzUploadPreviewBox');
      if (placeholder) placeholder.classList.remove('hidden');
      if (previewBox) previewBox.classList.add('hidden');
    },

    copyAccount(num, bankName = '') {
      const bank = bankName || SAMSARA_CONFIG.expedition.bankName || 'Bank Resmi';
      const acc = num || SAMSARA_CONFIG.expedition.bankAccountRaw || '8100982111';
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(acc).then(() => {
          alert(`Nomor Rekening ${bank} (${acc}) berhasil disalin ke clipboard.`);
        }).catch(() => {
          prompt(`Salin Nomor Rekening ${bank}:`, acc);
        });
      } else {
        prompt(`Salin Nomor Rekening ${bank}:`, acc);
      }
    },

    copyResultCode() {
      if (this.lastSavedCode) {
        navigator.clipboard?.writeText(this.lastSavedCode);
        alert(`Kode Registrasi ${this.lastSavedCode} berhasil disalin ke clipboard.`);
      }
    },

    showError(msg) {
      const alertEl = document.getElementById('wzErrorAlert');
      if (alertEl) {
        alertEl.innerText = msg;
        alertEl.classList.remove('hidden');
      }
    },

    hideError() {
      const alertEl = document.getElementById('wzErrorAlert');
      if (alertEl) alertEl.classList.add('hidden');
    },

    async submit() {
      this.hideError();

      const agree = document.getElementById('wzAgreeCheck')?.checked;
      if (!agree) {
        this.showError('Anda harus menyetujui SOP Keselamatan SAMSARA & Deposit Sampah.');
        return;
      }

      if (this.selectedMethod !== 'cash_pos' && !this.selectedFile) {
        if (!confirm('Anda belum mengunggah bukti bayar transfer/QRIS. Lanjutkan pendaftaran dengan status belum bayar?')) {
          return;
        }
      }

      // UI Loading state
      const btn = document.getElementById('btnSubmitRegistration');
      const txt = document.getElementById('txtSubmitBtn');
      const spinner = document.getElementById('spinnerSubmitBtn');

      if (btn) btn.disabled = true;
      if (txt) txt.innerText = 'MEMPROSES...';
      if (spinner) spinner.classList.remove('hidden');

      try {
        // Collect Member Manifest Array
        const totalClimbers = App.state.climbers;
        const leaderName = document.getElementById('wzLeaderName')?.value.trim();
        const leaderNik = document.getElementById('wzLeaderNik')?.value.trim();
        const leaderPhone = document.getElementById('wzLeaderPhone')?.value.trim();
        const leaderAddress = document.getElementById('wzLeaderAddress')?.value.trim() || document.getElementById('wzLeaderCity')?.value.trim() || 'Garut';
        const leaderCity = leaderAddress;
        const leaderMedical = document.getElementById('wzLeaderMedical')?.value.trim() || 'Tidak Ada';

        const members = [
          {
            full_name: leaderName,
            nik: leaderNik,
            phone: leaderPhone,
            gender: 'L',
            city_origin: leaderCity,
            medical_conditions: leaderMedical,
            is_leader: true
          }
        ];

        for (let i = 2; i <= totalClimbers; i++) {
          const elName = document.getElementById(`wzMemName_${i}`);
          const elNik = document.getElementById(`wzMemNik_${i}`);
          const elGen = document.getElementById(`wzMemGen_${i}`);
          const elMed = document.getElementById(`wzMemMed_${i}`);
          members.push({
            full_name: elName?.value.trim() || `Anggota #${i}`,
            nik: elNik?.value.trim() || `320501${i}0000000`,
            phone: null,
            gender: elGen?.value || 'L',
            city_origin: leaderCity,
            medical_conditions: elMed?.value.trim() || 'Tidak Ada',
            is_leader: false
          });
        }

        // Build FormData
        const formData = new FormData();
        const plannedCampsite = document.querySelector('input[name="wzCampsite"]:checked')?.value || 'POS_2';
        formData.append('hiking_category', this.category || 'camp');
        formData.append('planned_campsite', this.category === 'camp' ? plannedCampsite : '');
        formData.append('hiking_date', document.getElementById('wzHikingDate')?.value);
        formData.append('return_date', document.getElementById('wzReturnDate')?.value || '');
        formData.append('leader_name', leaderName);
        formData.append('leader_nik', leaderNik);
        formData.append('leader_phone', leaderPhone);
        formData.append('leader_address', leaderAddress);
        formData.append('leader_city', leaderCity);
        formData.append('medical_conditions', leaderMedical);
        formData.append('emergency_contact_name', document.getElementById('wzEmergName')?.value.trim());
        formData.append('emergency_relationship', document.getElementById('wzEmergRel')?.value);
        formData.append('emergency_contact_phone', document.getElementById('wzEmergPhone')?.value.trim());
        formData.append('book_ojek', this.ojekCount > 0 ? '1' : '0');
        formData.append('ojek_count', String(this.ojekCount));
        formData.append('has_vehicle', App.state.hasVehicle ? '1' : '0');
        formData.append('motorcycle_count', String(App.state.motorcycleCount));
        formData.append('car_count', String(App.state.carCount));
        formData.append('unique_code', this.selectedMethod === 'cash_pos' ? '0' : String(this.uniqueCode));
        formData.append('payment_method', this.selectedMethod);
        formData.append('members', JSON.stringify(members));

        if (this.selectedKtpFile) {
          formData.append('leader_ktp', this.selectedKtpFile);
        }

        if (this.selectedFile) {
          formData.append('payment_proof', this.selectedFile);
        }

        const response = await fetch('/api/simaksi/register', {
          method: 'POST',
          headers: {
            'Accept': 'application/json'
          },
          body: formData
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
          throw new Error(result.message || 'Terjadi kesalahan sistem saat menyimpan registrasi SIMAKSI.');
        }

        // Success! Populate Step 5 View
        this.lastSavedCode = result.registration_code;
        const codeEl = document.getElementById('wzResultCode');
        const leaderEl = document.getElementById('wzResultLeader');
        const climbersEl = document.getElementById('wzResultClimbers');
        const dateEl = document.getElementById('wzResultDate');
        const totalEl = document.getElementById('wzResultTotal');
        const methodEl = document.getElementById('wzResultMethod');
        const statusEl = document.getElementById('wzResultStatusBadge');

        if (codeEl) codeEl.innerText = result.registration_code;
        if (leaderEl) leaderEl.innerText = result.leader_name;
        if (climbersEl) climbersEl.innerText = `${result.total_climbers} ORANG`;
        if (dateEl) dateEl.innerText = result.hiking_date;
        if (totalEl) totalEl.innerText = `Rp ${Number(result.total_amount).toLocaleString('id-ID')}`;
        if (methodEl) {
          const uniqueNote = result.unique_code > 0 ? ` (+KODE UNIK ${result.unique_code})` : '';
          methodEl.innerText = `${result.payment_method}${uniqueNote} ${result.has_proof ? '(BUKTI TERUNGGAH)' : '(BELUM BAYAR)'}`;
        }
        if (statusEl) {
          statusEl.innerText = (result.has_proof) ? 'MENUNGGU VERIFIKASI KASIR' : 'BELUM BAYAR (MENUNGGU PEMBAYARAN)';
          statusEl.className = result.has_proof
            ? 'text-[10px] text-amber-400 font-bold px-2.5 py-0.5 bg-amber-500/10 border border-amber-500/30 rounded uppercase tracking-wider'
            : 'text-[10px] text-rose-400 font-bold px-2.5 py-0.5 bg-rose-500/10 border border-rose-500/30 rounded uppercase tracking-wider';
        }

        const btnDigital = document.getElementById('btnViewDigitalTicket');
        const btnPdf = document.getElementById('btnDownloadTicketPdf');
        if (btnDigital) btnDigital.href = `/simaksi/ticket/${result.registration_code}`;
        if (btnPdf) btnPdf.href = `/simaksi/ticket/${result.registration_code}/pdf`;

        // Cache into SAMSARA_CONFIG mockTickets for instant ticket lookup
        SAMSARA_CONFIG.mockTickets[result.registration_code] = {
          id: result.registration_code,
          leader: result.leader_name,
          phone: leaderPhone,
          members: result.total_climbers,
          date: result.hiking_date,
          ojek: this.ojekCount > 0 ? `${this.ojekCount} Armada Ojek Trail` : 'Tidak Menggunakan Ojek',
          status: result.has_proof ? 'MENUNGGU VERIFIKASI KASIR' : 'MENUNGGU PEMBAYARAN',
          statusClass: result.has_proof ? 'text-amber-400 border-amber-500/30 bg-amber-500/10' : 'text-rose-400 border-rose-500/30 bg-rose-500/10',
          totalPaid: `Rp ${Number(result.total_amount).toLocaleString('id-ID')} (${result.payment_method})`,
          checkInTime: '06:00 - 10:00 WIB'
        };

        // Go to Step 5 (Ticket Ready)
        this.goTo(5);

        // Clean up completed draft & store last ticket code
        localStorage.removeItem(App.draft.STORAGE_KEY);
        localStorage.setItem('samsara_last_ticket', result.registration_code);
        App.draft.dismiss();

        // Automatically open WhatsApp to send full details to Admin
        this.openWhatsAppAdmin({
          code: result.registration_code,
          leaderName: result.leader_name,
          leaderPhone: leaderPhone,
          leaderAddress: leaderAddress,
          leaderCity: leaderCity,
          leaderNik: leaderNik,
          leaderMedical: leaderMedical,
          emergencyName: document.getElementById('wzEmergName')?.value.trim(),
          emergencyRel: document.getElementById('wzEmergRel')?.value,
          emergencyPhone: document.getElementById('wzEmergPhone')?.value.trim(),
          hikingDate: result.hiking_date,
          returnDate: document.getElementById('wzReturnDate')?.value || '-',
          totalClimbers: result.total_climbers,
          ojekCount: this.ojekCount,
          hasVehicle: App.state.hasVehicle,
          motorcycleCount: App.state.motorcycleCount,
          carCount: App.state.carCount,
          subtotal: result.subtotal_amount,
          uniqueCode: result.unique_code,
          totalAmount: result.total_amount,
          paymentMethod: result.payment_method,
          hasProof: result.has_proof,
          members: members
        });

      } catch (err) {
        console.error('SIMAKSI Registration Error:', err);
        this.showError(err.message || 'Gagal memproses pendaftaran. Periksa koneksi internet Anda.');
      } finally {
        if (btn) btn.disabled = false;
        if (txt) txt.innerText = 'KIRIM & BUAT TIKET';
        if (spinner) spinner.classList.add('hidden');
      }
    },

    openWhatsAppAdmin(data) {
      const adminPhone = SAMSARA_CONFIG.expedition.adminWhatsApp || '6282122334455';
      
      let memberListText = '';
      if (Array.isArray(data.members)) {
        memberListText = data.members.map((m, idx) => {
          const role = data.totalClimbers === 1 ? 'Solo' : (m.is_leader ? 'Ketua' : 'Anggota');
          return `${idx + 1}. ${m.full_name} (${m.gender}) - ${role}`;
        }).join('\n');
      }

      const ojekText = data.ojekCount > 0 ? `${data.ojekCount} Armada (BC -> Pos 1)` : 'Tidak Pesan Ojek';
      
      let vehicleText = 'Tidak Bawa Kendaraan';
      if (data.hasVehicle) {
        const parts = [];
        if (data.motorcycleCount > 0) parts.push(`${data.motorcycleCount} Motor`);
        if (data.carCount > 0) parts.push(`${data.carCount} Mobil`);
        vehicleText = parts.length > 0 ? parts.join(', ') : 'Bawa Kendaraan';
      }

      const uniqueText = data.uniqueCode > 0 ? `+Rp ${Number(data.uniqueCode).toLocaleString('id-ID')}` : 'Rp 0';
      const proofText = data.hasProof ? 'Sudah Diunggah di Portal' : 'Belum Diunggah';
      const domisiliText = (data.leaderAddress || data.leaderCity || '-').replace(/[\r\n]+/g, ', ');

      const expName = (window.SAMSARA_EXPEDITION_PROFILE && window.SAMSARA_EXPEDITION_PROFILE.expedition_name) || (typeof SAMSARA_CONFIG !== 'undefined' ? SAMSARA_CONFIG.expedition.name : 'SAMSARA');
      const mtnName = (window.SAMSARA_EXPEDITION_PROFILE && window.SAMSARA_EXPEDITION_PROFILE.mountain_name) || (typeof SAMSARA_CONFIG !== 'undefined' ? SAMSARA_CONFIG.expedition.mountain : 'Gunung Cikuray');

      const message = 
`*KONFIRMASI RESERVASI SIMAKSI ${expName.toUpperCase()} ${mtnName.toUpperCase()}*
------------------------------
*KODE REGISTRASI :* *${data.code}*
*STATUS :* *Menunggu Verifikasi Admin*

*1. DATA KETUA & KONTAK*
- Nama Lengkap : ${data.leaderName}
- NIK KTP : ${data.leaderNik}
- No. WhatsApp : ${data.leaderPhone}
- Alamat Domisili : ${domisiliText}
- Riwayat Medis : ${data.leaderMedical || 'Tidak Ada'}
- Kontak Darurat : ${data.emergencyName} (${data.emergencyRel}) - ${data.emergencyPhone}

*2. JADWAL & ANGGOTA*
- Tanggal Naik : ${data.hikingDate}
- Estimasi Turun : ${data.returnDate}
- Total Pendaki : ${data.totalClimbers === 1 ? '1 Orang (Solo)' : data.totalClimbers + ' Orang'}

*3. LAYANAN & LOGISTIK*
- Shuttle Ojek : ${ojekText}
- Parkir Kendaraan : ${vehicleText}

*4. RINCIAN BIAYA & METODE*
- Subtotal : Rp ${Number(data.subtotal || (data.totalAmount - (data.uniqueCode || 0))).toLocaleString('id-ID')}
- Kode Unik : ${uniqueText}
- *TOTAL TRANSFER :* *Rp ${Number(data.totalAmount).toLocaleString('id-ID')}*
- Metode Bayar : ${data.paymentMethod}
- Bukti Transfer : ${proofText}

*5. MANIFEST PENDAKI*
${memberListText.trim()}
------------------------------
Halo Admin ${SAMSARA_CONFIG.expedition.basecampName || 'Basecamp SAMSARA'}, saya telah menyelesaikan pendaftaran mandiri di portal SIMAKSI. Mohon diverifikasi berkas dan pembayaran kami. Terima kasih!`;

      this.lastWhatsAppMessage = message;

      const encoded = encodeURIComponent(message);
      const waUrl = `https://api.whatsapp.com/send?phone=${adminPhone}&text=${encoded}`;
      
      const btnWa = document.getElementById('btnOpenWhatsApp');
      if (btnWa) {
        btnWa.href = waUrl;
      }

      // Automatically launch WhatsApp window/app
      try {
        window.open(waUrl, '_blank');
      } catch (e) {
        console.warn('Auto open WhatsApp popup was blocked:', e);
      }
    },

    copyWhatsAppText() {
      if (!this.lastWhatsAppMessage) {
        return;
      }

      const handleSuccess = () => {
        const txt = document.getElementById('txtCopyWhatsApp');
        if (txt) {
          const original = txt.innerText;
          txt.innerText = '✓ FORMAT PESAN TELAH DISALIN!';
          txt.classList.add('text-[#25D366]');
          setTimeout(() => {
            txt.innerText = original;
            txt.classList.remove('text-[#25D366]');
          }, 3500);
        }
      };

      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(this.lastWhatsAppMessage).then(handleSuccess).catch(() => {
          this.fallbackCopyText(this.lastWhatsAppMessage, handleSuccess);
        });
      } else {
        this.fallbackCopyText(this.lastWhatsAppMessage, handleSuccess);
      }
    },

    fallbackCopyText(text, callback) {
      const ta = document.createElement('textarea');
      ta.value = text;
      ta.style.position = 'fixed';
      ta.style.opacity = '0';
      document.body.appendChild(ta);
      ta.focus();
      ta.select();
      try {
        document.execCommand('copy');
        if (callback) callback();
      } catch (err) {
        console.error('Fallback copy failed:', err);
      }
      document.body.removeChild(ta);
    }
  },

  modal: {
    open(id) {
      const modal = document.getElementById(id);
      if (modal) {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
      }
    },
    close(id) {
      const modal = document.getElementById(id);
      if (modal) {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
      }
    }
  },

  previewImage(src, title) {
    const img = document.getElementById('imagePreviewImg');
    const titleEl = document.getElementById('imagePreviewTitle');
    const rawLink = document.getElementById('imagePreviewRawLink');
    if (img) img.src = src;
    if (titleEl) titleEl.innerText = title || 'PREVIEW DOKUMEN';
    if (rawLink) rawLink.href = src;
    this.modal.open('imagePreviewModal');
  },

  /**
   * ANTI-TERCLOSE AUTO-DRAFT CONTROLLER
   * Persists form progress to localStorage and provides instant recovery
   */
  draft: {
    STORAGE_KEY: 'samsara_simaksi_draft',

    save() {
      try {
        const totalClimbers = App.state.climbers;
        const members = [];
        for (let i = 2; i <= totalClimbers; i++) {
          const elName = document.getElementById(`wzMemName_${i}`);
          const elNik = document.getElementById(`wzMemNik_${i}`);
          const elGen = document.getElementById(`wzMemGen_${i}`);
          const elMed = document.getElementById(`wzMemMed_${i}`);
          if (elName) {
            members.push({
              index: i,
              name: elName.value || '',
              nik: elNik ? elNik.value : '',
              gender: elGen ? elGen.value : 'L',
              medical: elMed ? elMed.value : 'Tidak Ada'
            });
          }
        }

        const draft = {
          step: App.wizard.currentStep || 1,
          date: document.getElementById('wzHikingDate')?.value || App.state.date,
          returnDate: document.getElementById('wzReturnDate')?.value || App.state.returnDate,
          climbers: App.state.climbers,
          ojekCount: App.wizard.ojekCount,
          leaderName: document.getElementById('wzLeaderName')?.value || '',
          leaderNik: document.getElementById('wzLeaderNik')?.value || '',
          leaderPhone: document.getElementById('wzLeaderPhone')?.value || '',
          leaderAddress: document.getElementById('wzLeaderAddress')?.value || document.getElementById('wzLeaderCity')?.value || '',
          leaderCity: document.getElementById('wzLeaderAddress')?.value || document.getElementById('wzLeaderCity')?.value || '',
          leaderMedical: document.getElementById('wzLeaderMedical')?.value || 'Tidak Ada',
          emergName: document.getElementById('wzEmergName')?.value || '',
          emergPhone: document.getElementById('wzEmergPhone')?.value || '',
          emergRel: document.getElementById('wzEmergRel')?.value || 'Orang Tua',
          members: members,
          uniqueCode: App.wizard.uniqueCode,
          method: App.wizard.selectedMethod || 'qris',
          savedAt: new Date().toISOString()
        };

        if (draft.leaderName || draft.members.some(m => m.name) || draft.step > 1) {
          localStorage.setItem(this.STORAGE_KEY, JSON.stringify(draft));
        }
      } catch (e) {
        console.warn('Auto-save draft error:', e);
      }
    },

    checkDraft() {
      try {
        const raw = localStorage.getItem(this.STORAGE_KEY);
        if (!raw) return;
        const draft = JSON.parse(raw);
        if (!draft || (!draft.leaderName && draft.step <= 1)) return;

        const banner = document.getElementById('draftRecoveryBanner');
        const info = document.getElementById('draftRecoveryInfo');
        if (banner && info) {
          const stepNames = { 1: 'Jadwal & Logistik', 2: 'Data Ketua', 3: 'Manifest Anggota', 4: 'Pembayaran Transfer' };
          const stepText = stepNames[draft.step] || `Langkah ${draft.step}`;
          info.innerHTML = `Terakhir tersimpan: <strong class="text-white">${draft.leaderName || 'Rombongan'}</strong> (${draft.climbers || 4} Orang) • Jadwal: <strong class="text-white">${draft.date}</strong> • Tahap: <span class="text-[#FF5722] font-bold">${stepText}</span>.`;
          banner.classList.remove('hidden');
        }
      } catch (e) {
        console.warn('Check draft error:', e);
      }
    },

    resume() {
      try {
        const raw = localStorage.getItem(this.STORAGE_KEY);
        if (!raw) return;
        const draft = JSON.parse(raw);

        App.state.climbers = draft.climbers || 4;
        App.state.date = draft.date || new Date().toISOString().split('T')[0];
        App.wizard.ojekCount = draft.ojekCount || 0;
        App.wizard.uniqueCode = draft.uniqueCode || Math.floor(101 + Math.random() * 790);

        const setVal = (id, val) => {
          const el = document.getElementById(id);
          if (el && val !== undefined) el.value = val;
        };

        setVal('wzHikingDate', draft.date);
        setVal('wzReturnDate', draft.returnDate);
        setVal('wzLeaderName', draft.leaderName);
        setVal('wzLeaderNik', draft.leaderNik);
        setVal('wzLeaderPhone', draft.leaderPhone);
        setVal('wzLeaderAddress', draft.leaderAddress || draft.leaderCity);
        setVal('wzLeaderCity', draft.leaderAddress || draft.leaderCity);
        setVal('wzLeaderMedical', draft.leaderMedical || 'Tidak Ada');
        setVal('wzEmergName', draft.emergName);
        setVal('wzEmergPhone', draft.emergPhone);
        setVal('wzEmergRel', draft.emergRel);

        const wzCountBox = document.getElementById('wzClimberCountBox');
        const wzCountLbl = document.getElementById('wzClimberCountLabel');
        const wzOjekBox = document.getElementById('wzOjekCountBox');

        if (wzCountBox) wzCountBox.innerText = App.state.climbers;
        if (wzCountLbl) wzCountLbl.innerText = `${App.state.climbers} ORANG`;
        if (wzOjekBox) wzOjekBox.innerText = App.wizard.ojekCount;

        App.wizard.renderMembers();
        if (Array.isArray(draft.members)) {
          draft.members.forEach(m => {
            const elName = document.getElementById(`wzMemName_${m.index}`);
            const elNik = document.getElementById(`wzMemNik_${m.index}`);
            const elGen = document.getElementById(`wzMemGen_${m.index}`);
            const elMed = document.getElementById(`wzMemMed_${m.index}`);
            if (elName && m.name) elName.value = m.name;
            if (elNik && m.nik) elNik.value = m.nik;
            if (elGen && m.gender) elGen.value = m.gender;
            if (elMed && m.medical) elMed.value = m.medical;
          });
        }

        if (draft.method) {
          App.wizard.onMethodChange(draft.method);
        }

        App.wizard.recalc();
        App.modal.open('regModal');
        App.wizard.goTo(draft.step || 1);

        this.dismiss();
      } catch (e) {
        console.error('Failed to resume draft:', e);
        alert('Gagal memulihkan draf pemesanan.');
      }
    },

    discard() {
      if (confirm('Hapus draf pendaftaran yang tersimpan?')) {
        localStorage.removeItem(this.STORAGE_KEY);
        this.dismiss();
      }
    },

    dismiss() {
      const banner = document.getElementById('draftRecoveryBanner');
      if (banner) banner.classList.add('hidden');
    }
  },

  /**
   * DEDICATED TICKET TRACKING & SUSULAN PROOF CONTROLLER
   */
  ticket: {
    selectedProofFile: null,
    selectedKtpFile: null,

    openModal() {
      App.modal.open('ticketModal');
      this.updateRecentChips();
      
      const last = localStorage.getItem('samsara_last_ticket');
      const input = document.getElementById('searchTicketInput');
      if (input && last && (!input.value || input.value === 'SMK-202611-001')) {
        input.value = last;
      }
      this.search();
    },

    updateRecentChips() {
      const container = document.getElementById('recentTicketChipContainer');
      if (!container) return;
      const last = localStorage.getItem('samsara_last_ticket');
      if (last) {
        container.innerHTML = `
          <button type="button" onclick="App.ticket.quickSearch('${last}')" class="px-2 py-0.5 rounded bg-[#FF5722]/15 border border-[#FF5722]/40 text-[#FF5722] font-bold">
            TIKET ANDA (${last})
          </button>
        `;
      } else {
        container.innerHTML = '';
      }
    },

    quickSearch(code) {
      const input = document.getElementById('searchTicketInput');
      if (input) input.value = code;
      this.search();
    },

    async search() {
      const input = document.getElementById('searchTicketInput');
      const query = input?.value.trim().toUpperCase();
      const res = document.getElementById('ticketSearchResult');
      if (!res) return;

      if (!query) {
        res.innerHTML = `
          <div class="text-center py-6 text-stone-500 text-xs font-mono-tech space-y-1">
            <p>Ketik Kode SIMAKSI (contoh: <strong class="text-stone-300">SMK-202611-001</strong>) atau No. WhatsApp Ketua Tim.</p>
          </div>
        `;
        return;
      }

      // Tactical Loading HUD
      res.innerHTML = `
        <div class="p-6 text-center text-xs font-mono-tech space-y-2">
          <div class="inline-block animate-spin w-6 h-6 border-2 border-[#FF5722] border-t-transparent rounded-full"></div>
          <span class="block text-stone-400">MENGAMBIL TELEMETRI TIKET DARI SERVER...</span>
        </div>
      `;

      try {
        const response = await fetch('/api/simaksi/lookup?query=' + encodeURIComponent(query), {
          headers: { 'Accept': 'application/json' }
        });

        const json = await response.json();

        if (response.ok && json.success && json.data) {
          const reg = json.data;
          this.renderTicketDetail(reg);
        } else {
          // Fallback to SAMSARA_CONFIG.mockTickets if available
          const match = SAMSARA_CONFIG.mockTickets[query];
          if (match) {
            this.renderMockTicket(match);
          } else {
            res.innerHTML = `
              <div class="p-4 bg-[#121418] border border-rose-500/30 rounded-xl space-y-2 text-xs font-mono-tech text-center">
                <span class="text-rose-400 font-bold block">[!] DATA TIKET TIDAK DITEMUKAN</span>
                <p class="text-stone-400 text-[11px] leading-relaxed">
                  Tidak ditemukan pendaftaran dengan kode atau nomor <strong>"${query}"</strong>.<br>
                  Pastikan kode registrasi atau nomor WhatsApp sesuai dengan saat pendaftaran.
                </p>
                <div class="pt-2">
                  <a href="https://wa.me/${SAMSARA_CONFIG.expedition.adminWhatsApp}?text=${encodeURIComponent('Halo Admin SAMSARA, saya ingin cek status tiket dengan query: ' + query)}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded bg-[#16191F] border border-[#282D37] text-stone-300 hover:text-white text-[10px]">
                    <span>BANTUAN VIA WHATSAPP ADMIN →</span>
                  </a>
                </div>
              </div>
            `;
          }
        }
      } catch (err) {
        console.error('Ticket search error:', err);
        res.innerHTML = `
          <div class="p-4 bg-[#121418] border border-rose-500/30 rounded-xl space-y-1 text-xs font-mono-tech text-center">
            <span class="text-rose-400 font-bold block">[!] GAGAL MENGHUBUNGI SERVER</span>
            <p class="text-stone-400 text-[10px]">Periksa koneksi jaringan Anda atau coba sesaat lagi.</p>
          </div>
        `;
      }
    },

    renderTicketDetail(reg) {
      const res = document.getElementById('ticketSearchResult');
      if (!res) return;

      // Status pill styling
      let statusBg = 'bg-amber-500/10 border-amber-500/30 text-amber-400';
      if (reg.status === 'paid' || reg.status === 'approved') {
        statusBg = 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400';
      } else if (reg.status === 'checked_in' || reg.status === 'active_hiking') {
        statusBg = 'bg-[#FF5722]/10 border-[#FF5722]/30 text-[#FF5722]';
      } else if (reg.status === 'needs_revision' || reg.status === 'revision_needed' || reg.status === 'rejected') {
        statusBg = 'bg-rose-500/10 border-rose-500/30 text-rose-400';
      } else if (reg.status === 'completed') {
        statusBg = 'bg-stone-500/10 border-stone-500/30 text-stone-300';
      }

      // Step progress state
      const step1 = true;
      const step2 = reg.has_proof || reg.payment_status === 'paid';
      const step3 = ['approved', 'paid', 'checked_in', 'active_hiking', 'completed'].includes(reg.status);
      const step4 = ['checked_in', 'active_hiking', 'completed'].includes(reg.status);

      // Manifest members list
      let membersHtml = '';
      if (Array.isArray(reg.members)) {
        membersHtml = reg.members.map((m, idx) => `
          <div class="flex items-center justify-between py-1 border-b border-[#1E2229] last:border-0 text-[10px]">
            <div>
              <span class="text-stone-300 font-medium">${idx + 1}. ${m.full_name} (${m.gender})</span>
              <span class="text-stone-500 block text-[9px]">NIK: ${m.nik} • Medis: <span class="${m.medical_conditions && m.medical_conditions !== 'Tidak Ada' ? 'text-amber-400 font-bold' : 'text-stone-400'}">${m.medical_conditions || 'Tidak Ada'}</span></span>
            </div>
            <span class="text-[9px] ${m.is_leader ? 'text-[#FF5722] font-bold' : 'text-stone-400'}">
              ${m.is_leader ? '[KETUA]' : '[ANGGOTA]'}
            </span>
          </div>
        `).join('');
      }

      // Render proof & upload susulan container
      let proofSectionHtml = '';
      if (reg.has_proof) {
        proofSectionHtml = `
          <div class="p-3 bg-[#101318] border border-emerald-500/30 rounded-xl space-y-2">
            <div class="flex items-center justify-between">
              <span class="text-[10px] text-emerald-400 font-bold uppercase flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span>BUKTI TRANSFER TERUNGGAH</span>
              </span>
              <span class="text-[9px] text-stone-400">Verifikasi Kasir</span>
            </div>
            <div class="flex items-center gap-3 pt-1">
              ${reg.payment_proof_url ? `
                <button type="button" onclick="App.previewImage('${reg.payment_proof_url}', 'BUKTI PEMBAYARAN: ${reg.registration_code}')" class="block w-16 h-16 rounded border border-[#2E3544] overflow-hidden bg-black shrink-0 hover:border-[#FF5722] transition text-left cursor-pointer">
                  <img src="${reg.payment_proof_url}" alt="Bukti" class="w-full h-full object-cover">
                </button>
              ` : ''}
              <div class="flex-1 min-w-0 text-[10px] text-stone-300 space-y-0.5">
                <span class="text-white font-bold block">Bukti Pembayaran Diterima</span>
                <span class="text-[9px] text-stone-400 block">Metode: ${reg.payment_method}</span>
                ${reg.payment_proof_url ? `
                  <button type="button" onclick="App.previewImage('${reg.payment_proof_url}', 'BUKTI PEMBAYARAN: ${reg.registration_code}')" class="inline-flex items-center gap-1 text-[#FF5722] hover:underline text-[9px] font-bold cursor-pointer">
                    <svg class="w-3 h-3 text-[#FF5722]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>[LIHAT POPUP BUKTI]</span>
                  </button>
                ` : ''}
              </div>
            </div>
          </div>
        `;
      } else {
        proofSectionHtml = `
          <!-- Dropzone Upload Bukti Bayar Susulan -->
          <div class="p-3.5 bg-[#141820] border-2 border-dashed border-amber-500/40 rounded-xl space-y-3">
            <div class="flex items-start gap-2">
              <div class="w-2 h-2 rounded-full bg-amber-400 mt-1 shrink-0"></div>
              <div>
                <span class="text-[11px] font-bold text-amber-300 block uppercase">// UNGGAH BUKTI BAYAR SUSULAN:</span>
                <p class="text-[10px] text-stone-300 leading-tight mt-0.5">
                  Jika Anda terclose saat transfer di m-Banking/ATM, Anda dapat mengunggah struk transfer di bawah ini tanpa perlu daftar ulang.
                </p>
              </div>
            </div>

            <!-- Transfer Account Info -->
            <div class="p-2.5 bg-[#0D1015] border border-[#222731] rounded-lg text-[10px] space-y-1">
              <div class="flex justify-between items-center text-stone-400">
                <span>Rekening Resmi ${SAMSARA_CONFIG.expedition.basecampName || 'SAMSARA'}:</span>
                <span class="text-white font-bold">${SAMSARA_CONFIG.expedition.bankName}: ${SAMSARA_CONFIG.expedition.bankAccount}</span>
              </div>
              <div class="flex justify-between items-center text-stone-400">
                <span>Nominal Transfer Tepat:</span>
                <span class="text-[#FF5722] font-bold text-xs">Rp ${Number(reg.total_amount).toLocaleString('id-ID')}</span>
              </div>
            </div>

            <!-- File Upload Dropzone -->
            <div onclick="document.getElementById('ticketProofFileInput').click()" class="p-3 bg-[#181C24] hover:bg-[#1E232E] border border-[#2E3544] hover:border-[#FF5722] rounded-lg text-center cursor-pointer transition">
              <input type="file" id="ticketProofFileInput" accept="image/*,application/pdf" class="hidden" onchange="App.ticket.handleProofFileSelect(event)">
              <div id="ticketProofPlaceholder" class="space-y-1">
                <svg class="w-5 h-5 mx-auto text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span class="text-[11px] font-bold text-stone-300 block">Pilih Foto Struk / Screenshot Transfer</span>
                <span class="text-[9px] text-stone-500 block">Maks. 5 MB (JPG, PNG, PDF)</span>
              </div>
              <div id="ticketProofSelected" class="hidden text-left flex items-center justify-between">
                <span id="ticketProofFileName" class="text-xs font-bold text-emerald-400 truncate max-w-[200px]">-</span>
                <span class="text-[9px] text-stone-400">Siap Kirim</span>
              </div>
            </div>

            <!-- Submit Upload Button -->
            <button type="button" id="btnUploadProofSusulan" onclick="App.ticket.submitProof('${reg.registration_code}')" class="tap-active w-full py-2.5 bg-[#FF5722] hover:bg-[#F4511E] text-black font-bold text-xs uppercase tracking-wider rounded-lg transition flex items-center justify-center gap-1.5 shadow-lg shadow-[#FF5722]/20">
              <span id="txtUploadProofBtn">UNGGAH BUKTI BAYAR SEKARANG</span>
              <svg id="spinnerUploadProofBtn" class="hidden animate-spin w-3.5 h-3.5 text-black" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
            </button>
          </div>
        `;
      }

      const expName = (window.SAMSARA_EXPEDITION_PROFILE && window.SAMSARA_EXPEDITION_PROFILE.expedition_name) || (typeof SAMSARA_CONFIG !== 'undefined' ? SAMSARA_CONFIG.expedition.name : 'SAMSARA');
      const mtnName = (window.SAMSARA_EXPEDITION_PROFILE && window.SAMSARA_EXPEDITION_PROFILE.mountain_name) || (typeof SAMSARA_CONFIG !== 'undefined' ? SAMSARA_CONFIG.expedition.mountain : 'Gunung Cikuray');
      const baseName = (window.SAMSARA_EXPEDITION_PROFILE && window.SAMSARA_EXPEDITION_PROFILE.basecamp_name) || (typeof SAMSARA_CONFIG !== 'undefined' ? SAMSARA_CONFIG.expedition.basecampName : 'Basecamp SAMSARA');

      // WhatsApp Message Generator
      const waMsg = encodeURIComponent(
`*CEK STATUS TIKET SIMAKSI ${expName.toUpperCase()} ${mtnName.toUpperCase()}*
------------------------------
*KODE REGISTRASI :* *${reg.registration_code}*
- Ketua Tim : ${reg.leader_name} (${reg.leader_phone})
- Jadwal : ${reg.hiking_date} (${reg.total_climbers} Orang)
- Total Biaya : Rp ${Number(reg.total_amount).toLocaleString('id-ID')}
------------------------------
Halo Admin ${baseName}, mohon bantuan konfirmasi verifikasi status tiket permohonan SIMAKSI kami. Terima kasih!`
      );
      const waUrl = `https://api.whatsapp.com/send?phone=${SAMSARA_CONFIG.expedition.adminWhatsApp}&text=${waMsg}`;

      res.innerHTML = `
        <div class="space-y-4">
          
          <!-- Top Ticket Header Banner -->
          <div class="p-3.5 bg-[#16191F] border border-[#2A303C] rounded-xl space-y-2">
            <div class="flex items-center justify-between border-b border-[#222731] pb-2">
              <div>
                <span class="text-[9px] text-stone-500 uppercase block">KODE REGISTRASI RESMI</span>
                <span class="text-base font-bold text-[#FF5722] tracking-wider">${reg.registration_code}</span>
              </div>
              <span class="px-2.5 py-1 rounded text-[9px] font-bold border uppercase tracking-wider ${statusBg}">
                ${reg.status_label || reg.status}
              </span>
            </div>

            <!-- Pipeline Visual HUD -->
            <div class="pt-1">
              <span class="text-[9px] text-stone-500 block mb-1 uppercase">TAHAP VERIFIKASI EXPEDITION:</span>
              <div class="grid grid-cols-4 gap-1 text-center text-[8px] font-bold">
                <div class="py-1 rounded ${step1 ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-[#181B22] text-stone-600'}">
                  1. DRAFT
                </div>
                <div class="py-1 rounded ${step2 ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30'}">
                  2. BUKTI
                </div>
                <div class="py-1 rounded ${step3 ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-[#181B22] text-stone-600'}">
                  3. VERIFIKASI
                </div>
                <div class="py-1 rounded ${step4 ? 'bg-[#FF5722]/20 text-[#FF5722] border border-[#FF5722]/40' : 'bg-[#181B22] text-stone-600'}">
                  4. DI JALUR
                </div>
              </div>
            </div>

            <!-- Interactive QR Code for Basecamp Gate Check-in -->
            ${(reg.status === 'paid' || reg.status === 'approved' || reg.status === 'checked_in' || reg.status === 'completed') ? `
              <div class="mt-2 p-2.5 bg-[#0E1014] border border-emerald-500/30 rounded-xl flex items-center justify-between gap-3">
                <div class="space-y-0.5 min-w-0">
                  <span class="text-[10px] text-emerald-400 font-bold uppercase flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>BARCODE / QR CODE TIKET RESMI</span>
                  </span>
                  <p class="text-[9px] text-stone-400 leading-tight">
                    Tunjukkan barcode QR ini kepada ranger di Gerbang Basecamp untuk proses check-in otomatis 1 detik.
                  </p>
                </div>
                <div class="p-1 bg-white rounded-lg shrink-0 shadow-lg">
                  <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&margin=2&data=${encodeURIComponent(reg.registration_code)}" alt="QR Code Tiket" class="w-16 h-16 block" loading="lazy">
                </div>
              </div>
            ` : ''}

            <!-- Action Buttons: Download PDF & Open Digital Ticket -->
            <div class="mt-2.5 flex gap-2">
              <a href="${reg.pdf_download_url || ('/simaksi/ticket/' + encodeURIComponent(reg.registration_code) + '/pdf')}" target="_blank" class="flex-1 py-2.5 px-3 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-black font-bold text-[11px] uppercase tracking-wider text-center flex items-center justify-center gap-1.5 shadow-md shadow-emerald-500/20 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>UNDUH E-TIKET PDF</span>
              </a>
              <a href="${reg.ticket_url || ('/simaksi/ticket/' + encodeURIComponent(reg.registration_code))}" target="_blank" class="py-2.5 px-3 rounded-lg bg-[#181B22] hover:bg-[#202530] text-stone-200 border border-[#2D3340] font-bold text-[11px] uppercase tracking-wider text-center flex items-center justify-center gap-1 transition">
                <span>E-TIKET ↗</span>
              </a>
            </div>
          </div>

          <!-- Rincian Identitas & Jadwal -->
          <div class="p-3 bg-[#111317] border border-[#202530] rounded-xl space-y-2 text-[11px] text-stone-300">
            <div class="grid grid-cols-2 gap-2 pb-2 border-b border-[#1E2229]">
              <div>
                <span class="text-[9px] text-stone-500 block uppercase">KETUA TIM:</span>
                <span class="font-bold text-white">${reg.leader_name}</span>
                <span class="text-[9px] text-stone-400 block">${reg.leader_address || reg.leader_city || 'Garut'}</span>
                <span class="text-[9px] ${reg.medical_conditions && reg.medical_conditions !== 'Tidak Ada' ? 'text-amber-400 font-bold' : 'text-stone-400'} block">Medis: ${reg.medical_conditions || 'Tidak Ada'}</span>
              </div>
              <div>
                <span class="text-[9px] text-stone-500 block uppercase">WHATSAPP:</span>
                <span class="font-bold text-white">${reg.leader_phone}</span>
                <span class="text-[9px] text-stone-400 block">NIK: ${reg.leader_nik || '-'}</span>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-2 pb-2 border-b border-[#1E2229]">
              <div>
                <span class="text-[9px] text-stone-500 block uppercase">TANGGAL PENDAKIAN:</span>
                <span class="font-bold text-white">${reg.hiking_date}</span>
                <span class="text-[9px] text-stone-400 block">Turun: ${reg.return_date || '-'}</span>
              </div>
              <div>
                <span class="text-[9px] text-stone-500 block uppercase">TOTAL ROMBONGAN:</span>
                <span class="font-bold text-[#FF5722] text-xs">${reg.total_climbers} ORANG</span>
                <span class="text-[9px] text-stone-400 block">Termasuk Ketua</span>
              </div>
            </div>

            <!-- Logistik Services -->
            <div class="flex justify-between items-center text-[10px] text-stone-400 pb-1 border-b border-[#1E2229]">
              <span>Layanan Shuttle Ojek:</span>
              <span class="text-stone-200 font-bold">${reg.ojek_count > 0 ? reg.ojek_count + ' Armada Trail' : 'Tidak Menggunakan'}</span>
            </div>
            <div class="flex justify-between items-center text-[10px] text-stone-400 pb-2 border-b border-[#1E2229]">
              <span>Parkir Kendaraan:</span>
              <span class="text-stone-200 font-bold">${reg.has_vehicle ? `${reg.motorcycle_count || 0} Motor, ${reg.car_count || 0} Mobil (Rp ${Number(reg.parking_fee || 0).toLocaleString('id-ID')})` : 'Tanpa Kendaraan'}</span>
            </div>

            <!-- Fee & Payment Details -->
            <div class="pt-1 space-y-1">
              <div class="flex justify-between text-stone-400 text-[10px]">
                <span>Total Biaya Permit, Parkir & Logistik:</span>
                <span class="text-stone-200">Rp ${Number(Number(reg.simaksi_fee || 0) + Number(reg.ojek_fee || 0) + Number(reg.parking_fee || 0)).toLocaleString('id-ID')}</span>
              </div>
              ${reg.unique_code > 0 ? `
                <div class="flex justify-between text-amber-300 text-[10px]">
                  <span>Kode Unik Verifikasi:</span>
                  <span class="font-bold">+Rp ${reg.unique_code}</span>
                </div>
              ` : ''}
              <div class="flex justify-between items-center text-sm pt-1 border-t border-[#1E2229]">
                <span class="font-bold text-white">TOTAL TRANSFER:</span>
                <span class="font-bold text-[#FF5722] text-base">Rp ${Number(reg.total_amount).toLocaleString('id-ID')}</span>
              </div>
              <div class="text-[9px] text-stone-500 text-right">
                Metode: ${reg.payment_method} • Status: ${reg.payment_status ? reg.payment_status.toUpperCase() : 'BELUM BAYAR'}
              </div>
            </div>
          </div>

          <!-- Manifest Anggota Accordion -->
          <details class="group bg-[#111317] border border-[#202530] rounded-xl overflow-hidden">
            <summary class="px-3.5 py-2.5 cursor-pointer flex items-center justify-between text-xs text-stone-300 font-bold hover:bg-[#16191F] transition">
              <span>MANIFEST LENGKAP ANGGOTA (${reg.total_climbers} ORANG)</span>
              <span class="text-[10px] text-stone-500 group-open:rotate-180 transition-transform">▼</span>
            </summary>
            <div class="p-3 pt-1 border-t border-[#1E2229] space-y-1 bg-[#0E1013]">
              ${membersHtml}
            </div>
          </details>

          <!-- Status Dokumen KTP Ketua -->
          <div class="p-3 bg-[#111317] border border-[#202530] rounded-xl space-y-1.5 font-mono-tech text-xs">
            <div class="flex items-center justify-between">
              <span class="text-[10px] text-stone-400 uppercase">FOTO / SCAN KTP KETUA:</span>
              ${reg.has_ktp 
                ? '<span class="text-emerald-400 font-bold text-[9px] bg-emerald-500/10 border border-emerald-500/30 px-2 py-0.5 rounded">[✓] TERLAMPIR</span>' 
                : (reg.status === 'completed'
                  ? '<span class="text-stone-400 font-bold text-[9px] bg-stone-500/10 border border-stone-500/30 px-2 py-0.5 rounded">[✓] DIMUSNAHKAN OTOMATIS (PDP PRIVASI)</span>'
                  : '<span class="text-amber-400 font-bold text-[9px] bg-amber-500/10 border border-amber-500/30 px-2 py-0.5 rounded">[!] BELUM DILAMPIRKAN</span>')}
            </div>
            ${reg.leader_ktp_url ? `
              <div class="pt-1 flex items-center gap-2.5">
                <img src="${reg.leader_ktp_url}" class="w-12 h-8 object-cover rounded border border-[#2A303C]" alt="KTP">
                <a href="${reg.leader_ktp_url}" target="_blank" class="text-[#FF5722] hover:underline text-[10px] font-bold">[LIHAT FOTO KTP ASLI ↗]</a>
              </div>
            ` : (reg.status === 'completed' ? `
              <p class="text-[10px] text-stone-400 pt-1">
                Berkas fisik KTP Anda telah dihapus secara permanen dari server pos SAMSARA demi kepatuhan Perlindungan Data Pribadi (PDP) pasca-pendakian.
              </p>
            ` : '')}
          </div>

          <!-- Permintaan Perbaikan Berkas / KTP (Jika Status needs_revision) -->
          ${(reg.status === 'needs_revision' || reg.status === 'revision_needed') ? `
            <div class="p-3.5 bg-amber-500/10 border border-amber-500/40 rounded-xl space-y-2.5 font-mono-tech text-xs">
              <div class="flex items-center gap-2 text-amber-400 font-bold">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span class="uppercase tracking-wider">// PERMINTAAN PERBAIKAN BERKAS PETUGAS:</span>
              </div>
              <div class="p-2.5 bg-[#0F1116] border border-amber-500/30 rounded-lg text-[11px] text-amber-200 leading-relaxed">
                "${reg.revision_notes || 'Silakan unggah foto identitas KTP yang lebih jelas untuk verifikasi.'}"
              </div>
              
              <!-- Dropzone Perbaikan KTP -->
              <div onclick="document.getElementById('ticketKtpFileInput').click()" class="p-3 bg-[#181C24] hover:bg-[#1E232E] border-2 border-dashed border-amber-500/50 rounded-lg text-center cursor-pointer transition">
                <input type="file" id="ticketKtpFileInput" accept="image/*,application/pdf" class="hidden" onchange="App.ticket.handleKtpFileSelect(event)">
                <div id="ticketKtpPlaceholder" class="space-y-1">
                  <span class="text-xs font-bold text-amber-300 block">Ambil Foto / Unggah Ulang KTP Ketua</span>
                  <span class="text-[9px] text-stone-400 block">Auto-compress aktif • Cepat & ringan</span>
                </div>
                <div id="ticketKtpSelected" class="hidden text-left flex items-center justify-between">
                  <span id="ticketKtpFileName" class="text-xs font-bold text-emerald-400 truncate max-w-[200px]">-</span>
                  <span class="text-[9px] text-stone-400 font-mono-tech">Siap Kirim</span>
                </div>
              </div>
              <p class="text-[9px] text-stone-400 leading-tight">
                *Sesuai prinsip Perlindungan Data Pribadi (PDP), berkas fisik KTP Anda akan otomatis dihapus permanen dari server segera setelah pendakian selesai.
              </p>
              <button type="button" id="btnUploadKtpPerbaikan" onclick="App.ticket.submitKtpRevision('${reg.registration_code}')" class="tap-active w-full py-2.5 bg-amber-500 hover:bg-amber-400 text-black font-bold text-xs uppercase tracking-wider rounded-lg transition flex items-center justify-center gap-1.5 shadow-lg shadow-amber-500/20">
                <span id="txtUploadKtpBtn">KIRIM PERBAIKAN KTP KE VERIFIKATOR</span>
                <svg id="spinnerUploadKtpBtn" class="hidden animate-spin w-3.5 h-3.5 text-black" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
              </button>
            </div>
          ` : ''}

          <!-- Bukti Bayar / Upload Susulan Section -->
          ${proofSectionHtml}

          <!-- WhatsApp Direct Action Button -->
          <div class="pt-1">
            <a href="${waUrl}" target="_blank" class="tap-active w-full py-2.5 bg-[#25D366] hover:bg-[#20BD5A] text-black font-bold text-xs uppercase tracking-wider rounded-lg transition flex items-center justify-center gap-2 shadow-lg shadow-[#25D366]/20">
              <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z"/></svg>
              <span>KONFIRMASI TIKET KE ADMIN WHATSAPP</span>
            </a>
          </div>

        </div>
      `;
    },

    renderMockTicket(match) {
      const res = document.getElementById('ticketSearchResult');
      if (!res) return;
      res.innerHTML = `
        <div class="p-3 bg-[#121418] border border-emerald-500/30 rounded-lg space-y-2 text-xs font-mono-tech">
          <div class="flex items-center justify-between pb-1.5 border-b border-[#20242C]">
            <span class="text-[#FF5722] font-bold">${match.id}</span>
            <span class="px-2 py-0.5 text-[9px] font-bold rounded ${match.statusClass}">${match.status}</span>
          </div>
          <div class="grid grid-cols-2 gap-2 text-[11px] text-stone-300">
            <div><span class="text-stone-500 text-[10px] block">KETUA TIM:</span>${match.leader}</div>
            <div><span class="text-stone-500 text-[10px] block">ROMBONGAN:</span>${match.members} ORANG</div>
            <div><span class="text-stone-500 text-[10px] block">TANGGAL MASUK:</span>${match.date}</div>
            <div><span class="text-stone-500 text-[10px] block">JAM CHECK-IN:</span>${match.checkInTime}</div>
          </div>
          <div class="pt-2 border-t border-[#20242C] flex justify-between text-[11px]">
            <span class="text-stone-400">BIAYA EXPEDITION:</span>
            <span class="text-emerald-400 font-bold">${match.totalPaid}</span>
          </div>
          <div class="pt-1 text-center text-emerald-400 text-[9px] font-bold border-t border-[#20242C]">
            [VERIFIED PASSENGER ENTRY]
          </div>
        </div>
      `;
    },

    async handleProofFileSelect(event) {
      const file = event.target.files?.[0];
      if (!file) return;

      const ph = document.getElementById('ticketProofPlaceholder');
      const sel = document.getElementById('ticketProofSelected');
      const fn = document.getElementById('ticketProofFileName');

      try {
        const res = await App.compressImage(file, { maxWidth: 1280, maxHeight: 1280, quality: 0.8 });
        this.selectedProofFile = res.file;
        const compKb = (res.compressedSize / 1024).toFixed(0);

        if (ph) ph.classList.add('hidden');
        if (sel) sel.classList.remove('hidden');
        if (fn) fn.innerText = res.compressed ? `${res.file.name} (${compKb} KB, -${res.ratio}%)` : `${file.name} (${compKb} KB)`;
      } catch (err) {
        this.selectedProofFile = file;
        if (ph) ph.classList.add('hidden');
        if (sel) sel.classList.remove('hidden');
        if (fn) fn.innerText = file.name;
      }
    },

    async handleKtpFileSelect(event) {
      const file = event.target.files?.[0];
      if (!file) return;

      const ph = document.getElementById('ticketKtpPlaceholder');
      const sel = document.getElementById('ticketKtpSelected');
      const fn = document.getElementById('ticketKtpFileName');

      try {
        const res = await App.compressImage(file, { maxWidth: 1280, maxHeight: 1280, quality: 0.8 });
        this.selectedKtpFile = res.file;
        const compKb = (res.compressedSize / 1024).toFixed(0);

        if (ph) ph.classList.add('hidden');
        if (sel) sel.classList.remove('hidden');
        if (fn) fn.innerText = res.compressed ? `${res.file.name} (${compKb} KB, -${res.ratio}%)` : `${file.name} (${compKb} KB)`;
      } catch (err) {
        this.selectedKtpFile = file;
        if (ph) ph.classList.add('hidden');
        if (sel) sel.classList.remove('hidden');
        if (fn) fn.innerText = file.name;
      }
    },

    async submitKtpRevision(code) {
      if (!this.selectedKtpFile) {
        alert('Silakan pilih file foto KTP perbaikan terlebih dahulu.');
        return;
      }

      const btn = document.getElementById('btnUploadKtpPerbaikan');
      const txt = document.getElementById('txtUploadKtpBtn');
      const spinner = document.getElementById('spinnerUploadKtpBtn');

      if (btn) btn.disabled = true;
      if (txt) txt.innerText = 'MENGIRIM PERBAIKAN KTP...';
      if (spinner) spinner.classList.remove('hidden');

      try {
        const formData = new FormData();
        formData.append('registration_code', code);
        formData.append('leader_ktp', this.selectedKtpFile);

        const response = await fetch('/api/simaksi/upload-ktp', {
          method: 'POST',
          headers: { 'Accept': 'application/json' },
          body: formData
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
          throw new Error(result.message || 'Gagal mengirimkan foto KTP perbaikan.');
        }

        alert('Foto KTP perbaikan berhasil dikirim ke verifikator! Status permohonan kembali ke antrean review.');
        this.selectedKtpFile = null;
        this.search();

      } catch (err) {
        console.error('Upload KTP error:', err);
        alert(err.message || 'Terjadi kesalahan saat mengunggah KTP.');
      } finally {
        if (btn) btn.disabled = false;
        if (txt) txt.innerText = 'KIRIM PERBAIKAN KTP KE VERIFIKATOR';
        if (spinner) spinner.classList.add('hidden');
      }
    },

    async submitProof(code) {
      if (!this.selectedProofFile) {
        alert('Silakan pilih file foto / screenshot bukti transfer terlebih dahulu.');
        return;
      }

      const btn = document.getElementById('btnUploadProofSusulan');
      const txt = document.getElementById('txtUploadProofBtn');
      const spinner = document.getElementById('spinnerUploadProofBtn');

      if (btn) btn.disabled = true;
      if (txt) txt.innerText = 'MENGUNGGAH BUKTI...';
      if (spinner) spinner.classList.remove('hidden');

      try {
        const formData = new FormData();
        formData.append('registration_code', code);
        formData.append('payment_proof', this.selectedProofFile);

        const response = await fetch('/api/simaksi/upload-proof', {
          method: 'POST',
          headers: { 'Accept': 'application/json' },
          body: formData
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
          throw new Error(result.message || 'Gagal mengunggah bukti bayar.');
        }

        alert('Bukti pembayaran susulan berhasil diunggah! Petugas kasir akan segera memverifikasi.');
        this.selectedProofFile = null;
        
        // Refresh ticket details
        this.search();

      } catch (err) {
        console.error('Upload proof error:', err);
        alert(err.message || 'Terjadi kesalahan saat mengunggah bukti.');
      } finally {
        if (btn) btn.disabled = false;
        if (txt) txt.innerText = 'UNGGAH BUKTI BAYAR SEKARANG';
        if (spinner) spinner.classList.add('hidden');
      }
    }
  }
};

document.addEventListener('DOMContentLoaded', () => App.init());
