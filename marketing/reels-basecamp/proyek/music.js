// Musik latar sintetis sederhana yang ceria: pad + arpeggio pluck + kick + hihat. WAV 48 kHz stereo.
// Pakai: node music.js out.wav <durasi-detik>
const fs = require('fs');
const out = process.argv[2] || 'musik.wav', DUR = +(process.argv[3] || 56);
const SR = 48000, N = Math.floor(SR * DUR), BPM = 112, beat = 60 / BPM, bar = beat * 4;
const L = new Float32Array(N), Rch = new Float32Array(N);
const mtof = m => 440 * Math.pow(2, (m - 69) / 12);
// C - G - Am - F
const chords = [[60, 64, 67], [55, 59, 62], [57, 60, 64], [53, 57, 60]];
const bass = [36, 43, 45, 41];
let rs = 1; const rnd = () => (rs = (rs * 16807) % 2147483647, rs / 2147483647 * 2 - 1);
function add(i, v, pan = 0) { if (i < 0 || i >= N) return; L[i] += v * (1 - pan) ; Rch[i] += v * (1 + pan); }
const nBars = Math.ceil(DUR / bar);
for (let b = 0; b < nBars; b++) {
  const ch = chords[b % 4], t0 = b * bar;
  // pad
  for (const m of ch) {
    const f = mtof(m), s0 = Math.floor(t0 * SR), len = Math.floor(bar * SR);
    for (let k = 0; k < len; k++) {
      const t = k / SR, env = Math.min(1, t / .3) * Math.min(1, (bar - t) / .3);
      const v = (Math.sin(2 * Math.PI * f * t) + .3 * Math.sin(2 * Math.PI * f * 2.003 * t)) * .045 * env;
      add(s0 + k, v, m % 2 ? .3 : -.3);
    }
  }
  // bass
  for (let q = 0; q < 4; q++) {
    const f = mtof(bass[b % 4]), s0 = Math.floor((t0 + q * beat) * SR), len = Math.floor(beat * .9 * SR);
    for (let k = 0; k < len; k++) { const t = k / SR; add(s0 + k, Math.sin(2 * Math.PI * f * t) * .16 * Math.exp(-t * 3.5)); }
  }
  // arpeggio 8th notes
  const arp = [ch[0] + 12, ch[1] + 12, ch[2] + 12, ch[1] + 12, ch[0] + 24, ch[2] + 12, ch[1] + 12, ch[2] + 12];
  arp.forEach((m, j) => {
    const f = mtof(m), s0 = Math.floor((t0 + j * beat / 2) * SR), len = Math.floor(.35 * SR);
    for (let k = 0; k < len; k++) { const t = k / SR; const tri = 2 / Math.PI * Math.asin(Math.sin(2 * Math.PI * f * t)); add(s0 + k, tri * .07 * Math.exp(-t * 9), j % 2 ? .45 : -.45); }
  });
  // drums (start after first bar)
  if (b >= 1) for (let q = 0; q < 4; q++) {
    const s0 = Math.floor((t0 + q * beat) * SR);
    for (let k = 0; k < SR * .25; k++) { const t = k / SR; const f = 50 + 90 * Math.exp(-t * 30); add(s0 + k, Math.sin(2 * Math.PI * f * t) * .32 * Math.exp(-t * 14)); }
    const h0 = Math.floor((t0 + q * beat + beat / 2) * SR);
    for (let k = 0; k < SR * .05; k++) { const t = k / SR; add(h0 + k, rnd() * .05 * Math.exp(-t * 80), .2); }
    if (q % 2 === 1) for (let k = 0; k < SR * .15; k++) { const t = k / SR; add(s0 + k, rnd() * .09 * Math.exp(-t * 25), -.1); }
  }
}
// fade in/out + normalisasi
let peak = 0;
for (let i = 0; i < N; i++) {
  const t = i / SR, g = Math.min(1, t / 1.0) * Math.min(1, (DUR - t) / 2.5);
  L[i] *= g; Rch[i] *= g; peak = Math.max(peak, Math.abs(L[i]), Math.abs(Rch[i]));
}
const norm = .85 / peak;
const buf = Buffer.alloc(44 + N * 4);
buf.write('RIFF', 0); buf.writeUInt32LE(36 + N * 4, 4); buf.write('WAVE', 8); buf.write('fmt ', 12);
buf.writeUInt32LE(16, 16); buf.writeUInt16LE(1, 20); buf.writeUInt16LE(2, 22); buf.writeUInt32LE(SR, 24);
buf.writeUInt32LE(SR * 4, 28); buf.writeUInt16LE(4, 32); buf.writeUInt16LE(16, 34); buf.write('data', 36); buf.writeUInt32LE(N * 4, 40);
for (let i = 0; i < N; i++) {
  buf.writeInt16LE(Math.round(Math.max(-1, Math.min(1, L[i] * norm)) * 32767), 44 + i * 4);
  buf.writeInt16LE(Math.round(Math.max(-1, Math.min(1, Rch[i] * norm)) * 32767), 46 + i * 4);
}
fs.writeFileSync(out, buf);
console.log('musik', out, DUR + 's');
