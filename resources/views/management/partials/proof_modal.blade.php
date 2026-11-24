{{-- Modal pratinjau bukti pembayaran (dipanggil via showProofModal(...)) --}}
<div id="paymentProofModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-end sm:items-center justify-center sm:p-4" onclick="if(event.target === this) closeProofModal()">
  <div class="bg-surface ring-1 ring-line-strong rounded-t-3xl sm:rounded-2xl w-full sm:max-w-xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden">
    <div class="px-4 py-3 border-b border-line flex items-center justify-between shrink-0">
      <div>
        <p class="text-[11px] font-semibold uppercase tracking-wider text-sky-400">Verifikasi kasir</p>
        <h3 class="font-display font-semibold text-base text-ink">Bukti transfer / pembayaran</h3>
      </div>
      <button type="button" onclick="closeProofModal()" class="w-9 h-9 rounded-full bg-raised ring-1 ring-line text-muted hover:text-ink flex items-center justify-center" aria-label="Tutup">
        <x-ui.icon name="x" class="w-4 h-4" />
      </button>
    </div>

    <dl class="grid grid-cols-2 sm:grid-cols-4 gap-3 px-4 py-3 bg-canvas/60 border-b border-line text-xs">
      <div><dt class="text-subtle">Kode</dt><dd id="proofModalCode" class="font-code text-ink font-semibold truncate">-</dd></div>
      <div><dt class="text-subtle">Ketua</dt><dd id="proofModalLeader" class="text-ink font-semibold truncate">-</dd></div>
      <div><dt class="text-subtle">Nominal</dt><dd id="proofModalAmount" class="text-brand font-bold tabular-nums">-</dd></div>
      <div><dt class="text-subtle">Metode</dt><dd id="proofModalMethod" class="text-emerald-400 font-semibold uppercase">-</dd></div>
    </dl>

    <div class="flex-1 overflow-auto p-4 flex items-center justify-center bg-canvas">
      <img id="proofModalImage" src="" alt="Bukti pembayaran" class="max-h-[60vh] w-auto max-w-full object-contain rounded-lg ring-1 ring-line">
    </div>

    <div class="p-3 border-t border-line flex items-center justify-between gap-3 shrink-0 safe-bottom">
      <a id="proofModalRawLink" href="#" target="_blank" class="inline-flex items-center gap-1.5 h-10 px-3 rounded-xl bg-raised ring-1 ring-line text-sky-400 text-sm font-semibold">
        <x-ui.icon name="external" class="w-4 h-4" /> Resolusi penuh
      </a>
      <x-ui.button variant="secondary" onclick="closeProofModal()">Tutup</x-ui.button>
    </div>
  </div>
</div>

<script>
  function showProofModal(imageUrl, code, leader, amount, method) {
    const set = (id, v) => { const el = document.getElementById(id); if (el) el.innerText = v; };
    const img = document.getElementById('proofModalImage');
    if (img) img.src = imageUrl;
    set('proofModalCode', code || '-');
    set('proofModalLeader', leader || '-');
    set('proofModalAmount', amount ? 'Rp ' + amount : '-');
    set('proofModalMethod', method || 'TRANSFER');
    const raw = document.getElementById('proofModalRawLink');
    if (raw) raw.href = imageUrl;
    document.getElementById('paymentProofModal')?.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }
  function closeProofModal() {
    document.getElementById('paymentProofModal')?.classList.add('hidden');
    document.body.style.overflow = '';
  }
</script>
