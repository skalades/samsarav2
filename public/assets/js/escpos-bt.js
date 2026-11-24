/**
 * SAMSARA — ESC/POS Bluetooth thermal printing (tested target: RP20N 58mm).
 *
 * Takes a receipt "document" (array of ops built by App\Services\ThermalReceiptService),
 * encodes it to ESC/POS bytes and sends it through one of three transports:
 *   - ble    : Web Bluetooth (Chrome / Edge on Android, Windows, macOS, ChromeOS). Needs HTTPS or localhost.
 *   - serial : Web Serial — desktop Chrome/Edge, for printers paired as Bluetooth SPP (shows as a COM port).
 *   - rawbt  : Android "RawBT" app (handles classic Bluetooth SPP printers that BLE cannot reach).
 *
 * Settings are per browser/device (localStorage), since the paired printer belongs to the device.
 */
(function (global) {
  'use strict';

  const STORAGE_KEY = 'samsara.escpos.settings';
  const DEFAULTS = {
    transport: 'ble',  // ble | serial | rawbt
    paper: 58,         // 58 -> 32 col / 384 dot, 80 -> 48 col / 576 dot
    logo: true,
    qr: true,
    cut: true,
    copies: 1,
    chunk: 100,        // BLE write size (bytes)
    baud: 9600,
  };

  // Known BLE service / characteristic UUIDs used by cheap 58mm printers (RP/RPP/MTP/Goojprt/Xprinter...).
  const BLE_SERVICES = [
    '000018f0-0000-1000-8000-00805f9b34fb',
    '0000ff00-0000-1000-8000-00805f9b34fb',
    '0000ffe0-0000-1000-8000-00805f9b34fb',
    '0000fee7-0000-1000-8000-00805f9b34fb',
    '0000ae30-0000-1000-8000-00805f9b34fb',
    '0000ae3a-0000-1000-8000-00805f9b34fb',
    '0000ff12-0000-1000-8000-00805f9b34fb',
    'e7810a71-73ae-499d-8c15-faa9aef0c3f2',
    '49535343-fe7d-4ae5-8fa9-9fafd205e455',
    '000020ff-0000-1000-8000-00805f9b34fb',
  ];

  // ------------------------------------------------------------------ settings
  function loadSettings() {
    try {
      return Object.assign({}, DEFAULTS, JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}'));
    } catch (e) {
      return Object.assign({}, DEFAULTS);
    }
  }
  function saveSettings(s) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(s));
  }

  // ------------------------------------------------------------------ text helpers
  const CHAR_MAP = {
    '•': '-', '·': '-', '—': '-', '–': '-', '→': '>', '←': '<', '✓': 'v', '✔': 'v',
    '“': '"', '”': '"', '‘': "'", '’': "'", '…': '...', '×': 'x', '°': 'o',
  };
  function toAscii(str) {
    return String(str == null ? '' : str)
      .replace(/[•·—–→←✓✔“”‘’…×°]/g, (c) => CHAR_MAP[c])
      .normalize('NFD').replace(/[̀-ͯ]/g, '')
      .replace(/[^\x20-\x7E\n]/g, '?');
  }

  function wrap(text, width) {
    const out = [];
    toAscii(text).split('\n').forEach((para) => {
      let line = '';
      para.split(/\s+/).filter(Boolean).forEach((word) => {
        while (word.length > width) {
          if (line) { out.push(line); line = ''; }
          out.push(word.slice(0, width));
          word = word.slice(width);
        }
        if (!word) return;
        if (!line) line = word;
        else if (line.length + 1 + word.length <= width) line += ' ' + word;
        else { out.push(line); line = word; }
      });
      out.push(line);
    });
    return out;
  }

  function rowLines(left, right, width) {
    left = toAscii(left); right = toAscii(right);
    if (left.length + right.length + 1 <= width) {
      return [left + ' '.repeat(width - left.length - right.length) + right];
    }
    // Too long for one line: left wraps, right goes right-aligned underneath.
    const lines = wrap(left, width);
    wrap(right, width).forEach((r) => lines.push(' '.repeat(width - r.length) + r));
    return lines;
  }

  // ------------------------------------------------------------------ ESC/POS encoder
  class EscPos {
    constructor() { this.parts = []; }
    raw(bytes) { this.parts.push(Uint8Array.from(bytes)); return this; }
    str(s) {
      const a = toAscii(s);
      const b = new Uint8Array(a.length);
      for (let i = 0; i < a.length; i++) b[i] = a.charCodeAt(i);
      this.parts.push(b);
      return this;
    }
    init() { return this.raw([0x1b, 0x40, 0x1b, 0x74, 0x00]); } // reset + codepage PC437
    align(a) { return this.raw([0x1b, 0x61, { left: 0, center: 1, right: 2 }[a] || 0]); }
    bold(on) { return this.raw([0x1b, 0x45, on ? 1 : 0]); }
    size(s) { return this.raw([0x1d, 0x21, { wide: 0x10, tall: 0x01, big: 0x11 }[s] || 0x00]); }
    line(s) { return this.str(s).raw([0x0a]); }
    feed(n) { return this.raw([0x1b, 0x64, Math.max(0, Math.min(255, n | 0))]); }
    cut() { return this.raw([0x1d, 0x56, 0x42, 0x00]); }
    qr(data, size) {
      const d = toAscii(data);
      const len = d.length + 3;
      this.raw([0x1d, 0x28, 0x6b, 0x04, 0x00, 0x31, 0x41, 0x32, 0x00]);           // model 2
      this.raw([0x1d, 0x28, 0x6b, 0x03, 0x00, 0x31, 0x43, size || 6]);              // module size
      this.raw([0x1d, 0x28, 0x6b, 0x03, 0x00, 0x31, 0x45, 0x31]);                   // ECC M
      this.raw([0x1d, 0x28, 0x6b, len & 0xff, (len >> 8) & 0xff, 0x31, 0x50, 0x30]).str(d); // store
      return this.raw([0x1d, 0x28, 0x6b, 0x03, 0x00, 0x31, 0x51, 0x30]);            // print
    }
    /** mono: Uint8Array of 0/1 pixels, widthPx multiple of 8 */
    raster(mono, widthPx, heightPx) {
      const bytesPerRow = widthPx / 8;
      const BAND = 48; // rows per GS v 0 command — keeps printer buffer happy
      for (let y0 = 0; y0 < heightPx; y0 += BAND) {
        const h = Math.min(BAND, heightPx - y0);
        const buf = new Uint8Array(8 + bytesPerRow * h);
        buf.set([0x1d, 0x76, 0x30, 0x00, bytesPerRow & 0xff, bytesPerRow >> 8, h & 0xff, h >> 8]);
        for (let y = 0; y < h; y++) {
          for (let x = 0; x < widthPx; x++) {
            if (mono[(y0 + y) * widthPx + x]) buf[8 + y * bytesPerRow + (x >> 3)] |= 0x80 >> (x & 7);
          }
        }
        this.parts.push(buf);
      }
      return this;
    }
    bytes() {
      const total = this.parts.reduce((n, p) => n + p.length, 0);
      const out = new Uint8Array(total);
      let o = 0;
      this.parts.forEach((p) => { out.set(p, o); o += p.length; });
      return out;
    }
  }

  // Load logo, scale, grayscale + Floyd–Steinberg dither → 1-bit.
  async function logoToMono(url, maxWidth) {
    const img = await new Promise((resolve, reject) => {
      const i = new Image();
      i.crossOrigin = 'anonymous';
      i.onload = () => resolve(i);
      i.onerror = reject;
      i.src = url;
    });
    let w = Math.min(maxWidth, img.naturalWidth);
    w -= w % 8;
    const h = Math.max(1, Math.round(img.naturalHeight * (w / img.naturalWidth)));
    const c = document.createElement('canvas');
    c.width = w; c.height = h;
    const ctx = c.getContext('2d');
    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, w, h);
    ctx.drawImage(img, 0, 0, w, h);
    const px = ctx.getImageData(0, 0, w, h).data;
    const g = new Float32Array(w * h);
    for (let i = 0; i < w * h; i++) {
      const a = px[i * 4 + 3] / 255;
      const lum = 0.299 * px[i * 4] + 0.587 * px[i * 4 + 1] + 0.114 * px[i * 4 + 2];
      g[i] = lum * a + 255 * (1 - a);
    }
    const mono = new Uint8Array(w * h);
    for (let y = 0; y < h; y++) {
      for (let x = 0; x < w; x++) {
        const i = y * w + x;
        const v = g[i] < 128 ? 0 : 255;
        mono[i] = v === 0 ? 1 : 0;
        const err = g[i] - v;
        if (x + 1 < w) g[i + 1] += err * 7 / 16;
        if (y + 1 < h) {
          if (x > 0) g[i + w - 1] += err * 3 / 16;
          g[i + w] += err * 5 / 16;
          if (x + 1 < w) g[i + w + 1] += err * 1 / 16;
        }
      }
    }
    return { mono, w, h };
  }

  async function encode(doc, settings, logoUrl) {
    const cols = settings.paper === 80 ? 48 : 32;
    const dots = settings.paper === 80 ? 576 : 384;
    const p = new EscPos().init();

    for (const op of doc) {
      switch (op.op) {
        case 'logo':
          if (settings.logo && logoUrl) {
            try {
              const { mono, w, h } = await logoToMono(logoUrl, Math.round(dots * 0.6));
              const pad = ((dots - w) / 2) | 0; // center manually (alignment is unreliable for rasters)
              const padded = pad - (pad % 8);
              const fullW = padded + w;
              const out = new Uint8Array(fullW * h);
              for (let y = 0; y < h; y++) out.set(mono.subarray(y * w, (y + 1) * w), y * fullW + padded);
              p.align('left').raster(out, fullW, h).feed(1);
            } catch (e) { console.warn('[escpos] logo skipped', e); }
          }
          break;
        case 'text': {
          const width = op.size === 'wide' || op.size === 'big' ? cols / 2 : cols;
          p.align(op.align || 'left').bold(!!op.bold).size(op.size);
          wrap(op.text, width).forEach((l) => p.line(l));
          p.size('normal').bold(false);
          break;
        }
        case 'row': {
          const width = op.size === 'wide' || op.size === 'big' ? cols / 2 : cols;
          p.align('left').bold(!!op.bold).size(op.size);
          rowLines(op.left, op.right, width).forEach((l) => p.line(l));
          p.size('normal').bold(false);
          break;
        }
        case 'hr':
          p.align('left').line((op.char || '-').repeat(cols));
          break;
        case 'qr':
          if (settings.qr) p.align('center').qr(op.data, op.size).raw([0x0a]).align('left');
          break;
        case 'feed':
          p.feed(op.lines || 1);
          break;
        case 'cut':
          if (settings.cut) p.cut();
          break;
      }
    }
    return p.bytes();
  }

  // ------------------------------------------------------------------ transports
  const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
  let bleDevice = null;
  let bleChar = null;
  let serialPort = null;

  async function findWritable(server) {
    const services = await server.getPrimaryServices();
    for (const svc of services) {
      const chars = await svc.getCharacteristics();
      for (const ch of chars) {
        if (ch.properties.write || ch.properties.writeWithoutResponse) return ch;
      }
    }
    throw new Error('Karakteristik tulis printer tidak ditemukan. Coba mode RawBT / Serial.');
  }

  async function bleConnect(forcePicker) {
    if (!navigator.bluetooth) {
      throw new Error('Browser ini tidak mendukung Web Bluetooth. Gunakan Chrome (Android/Windows) via HTTPS, atau mode RawBT.');
    }
    if (bleChar && bleDevice && bleDevice.gatt.connected && !forcePicker) return bleChar;

    if (!bleDevice || forcePicker) {
      // Try silently reusing a previously granted printer.
      if (!forcePicker && navigator.bluetooth.getDevices) {
        try {
          const known = await navigator.bluetooth.getDevices();
          const lastId = localStorage.getItem(STORAGE_KEY + '.bleId');
          bleDevice = known.find((d) => d.id === lastId) || null;
        } catch (e) { /* ignore */ }
      }
      if (!bleDevice || forcePicker) {
        bleDevice = await navigator.bluetooth.requestDevice({ acceptAllDevices: true, optionalServices: BLE_SERVICES });
      }
      localStorage.setItem(STORAGE_KEY + '.bleId', bleDevice.id);
      bleDevice.addEventListener('gattserverdisconnected', () => { bleChar = null; });
    }
    const server = await bleDevice.gatt.connect();
    bleChar = await findWritable(server);
    return bleChar;
  }

  async function bleSend(bytes, settings) {
    const ch = await bleConnect(false);
    const size = Math.max(20, settings.chunk | 0);
    const withResponse = ch.properties.write && ch.writeValueWithResponse;
    for (let i = 0; i < bytes.length; i += size) {
      const part = bytes.slice(i, i + size);
      if (withResponse) await ch.writeValueWithResponse(part);
      else if (ch.writeValueWithoutResponse) { await ch.writeValueWithoutResponse(part); await sleep(25); }
      else await ch.writeValue(part);
    }
  }

  async function serialSend(bytes, settings, forcePicker) {
    if (!navigator.serial) throw new Error('Web Serial hanya tersedia di Chrome/Edge desktop.');
    if (!serialPort || forcePicker) {
      if (serialPort) { try { await serialPort.close(); } catch (e) { /* ignore */ } }
      const granted = forcePicker ? [] : await navigator.serial.getPorts();
      serialPort = granted[0] || await navigator.serial.requestPort();
    }
    if (!serialPort.writable) await serialPort.open({ baudRate: settings.baud | 0 || 9600 });
    const writer = serialPort.writable.getWriter();
    try { await writer.write(bytes); } finally { writer.releaseLock(); }
  }

  function rawbtSend(bytes) {
    let bin = '';
    for (let i = 0; i < bytes.length; i++) bin += String.fromCharCode(bytes[i]);
    const b64 = btoa(bin);
    window.location.href = 'intent:base64,' + b64 + '#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;';
  }

  // ------------------------------------------------------------------ public API
  async function print(doc, opts) {
    opts = opts || {};
    const settings = loadSettings();
    // Connect first: the device picker needs the click's user activation, which
    // can expire while the logo is being loaded/dithered.
    if (settings.transport === 'ble') await bleConnect(false);
    const single = await encode(doc, settings, opts.logoUrl);
    const copies = Math.max(1, Math.min(5, settings.copies | 0));
    const bytes = new Uint8Array(single.length * copies);
    for (let c = 0; c < copies; c++) bytes.set(single, c * single.length);

    if (settings.transport === 'rawbt') return rawbtSend(bytes);
    if (settings.transport === 'serial') return serialSend(bytes, settings, false);
    return bleSend(bytes, settings);
  }

  async function pair() {
    const s = loadSettings();
    if (s.transport === 'serial') {
      await serialSend(new EscPos().init().bytes(), s, true);
      return 'Port serial siap';
    }
    if (s.transport === 'rawbt') return 'Mode RawBT: pairing dilakukan di aplikasi RawBT';
    await bleConnect(true);
    return 'Terhubung: ' + (bleDevice.name || 'Printer BLE');
  }

  async function testPage() {
    return print([
      { op: 'text', text: 'TES PRINTER', align: 'center', bold: true, size: 'big' },
      { op: 'text', text: 'SAMSARA ESC/POS BLUETOOTH', align: 'center' },
      { op: 'hr', char: '=' },
      { op: 'row', left: 'Kertas', right: loadSettings().paper + 'mm' },
      { op: 'row', left: 'Mode', right: loadSettings().transport.toUpperCase() },
      { op: 'text', text: '0123456789 ABCDEFGHIJKLMNOPQRSTUVWXYZ abcdefghijklmnopqrstuvwxyz', align: 'left' },
      { op: 'qr', data: 'SAMSARA-TEST', size: 5 },
      { op: 'feed', lines: 3 },
      { op: 'cut' },
    ]);
  }

  function currentDevice() {
    const s = loadSettings();
    if (s.transport === 'ble') return bleDevice && bleDevice.gatt.connected ? (bleDevice.name || 'Printer BLE') : null;
    if (s.transport === 'serial') return serialPort ? 'Port Serial' : null;
    return 'RawBT';
  }

  global.SamsaraEscPos = {
    print, pair, testPage, encode, loadSettings, saveSettings, currentDevice,
    _internals: { wrap, rowLines, toAscii, EscPos },
  };
})(window);
