{{-- Shared compact thermal print styles (80mm roll, no browser/content header & footer) --}}
<style>
  @page {
    size: 80mm auto;
    margin: 0;
  }

  * { box-sizing: border-box; }

  body {
    font-family: 'JetBrains Mono', ui-monospace, 'Courier New', monospace;
    background: #F4F4F5;
    color: #000;
    margin: 0;
    padding: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .actions {
    width: 100%;
    max-width: 360px;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px;
  }

  button, a.btn-back {
    font-family: inherit;
    font-size: 12px;
    font-weight: 700;
    padding: 8px 14px;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    border: none;
  }

  .btn-print { background: #FF5722; color: #000; }
  .btn-back { background: #333; color: #FFF; }

  .thermal-sheet {
    width: 80mm;
    max-width: 100%;
    margin-top: 12px;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .thermal-card {
    background: #FFF;
    padding: 8px 10px;
    border: 1px dashed #000;
    font-size: 10px;
    line-height: 1.3;
    page-break-inside: avoid;
    break-inside: avoid;
  }

  .text-center { text-align: center; }
  .font-bold { font-weight: 700; }
  .font-black { font-weight: 800; }
  .sep { border-bottom: 1px dashed #000; padding-bottom: 4px; margin-bottom: 4px; }
  .row { display: flex; justify-content: space-between; gap: 6px; }
  .row > span:last-child { text-align: right; }

  .code-line {
    text-align: center;
    font-weight: 800;
    font-size: 11px;
    letter-spacing: 1px;
    padding: 3px 4px;
    margin-bottom: 4px;
    border: 1px solid #000;
  }

  .highlight-box {
    border: 1px solid #000;
    padding: 4px 6px;
    margin: 4px 0;
    text-align: center;
  }

  @media print {
    html, body {
      width: 80mm;
      background: #FFF;
      margin: 0;
      padding: 0;
    }
    .actions { display: none !important; }
    .thermal-sheet {
      width: 100%;
      margin-top: 0;
      gap: 2mm;
    }
    .thermal-card {
      padding: 2mm 2.5mm;
      border: 1px dashed #000;
      box-shadow: none;
    }
  }
</style>
