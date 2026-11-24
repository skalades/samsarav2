// Render frame PNG dari scene.html (Three.js) lewat Chrome headless.
// Pakai: node render.js <start> <end>          -> frames/00000.png ... (skip yang sudah ada)
//        node render.js sample t1 t2 ...        -> samples/<t>.png
const puppeteer = require('puppeteer-core');
const http = require('http');
const fs = require('fs');
const path = require('path');

const root = __dirname;
const mime = { '.html': 'text/html', '.js': 'text/javascript', '.css': 'text/css', '.woff2': 'font/woff2', '.woff': 'font/woff', '.mp3': 'audio/mpeg' };
const server = http.createServer((req, res) => {
  const p = path.join(root, decodeURIComponent(req.url.split('?')[0]));
  fs.readFile(p, (e, d) => {
    if (e) { res.writeHead(404); res.end(); return; }
    res.writeHead(200, { 'Content-Type': mime[path.extname(p)] || 'application/octet-stream' });
    res.end(d);
  });
});

(async () => {
  await new Promise(r => server.listen(0, r));
  const port = server.address().port;
  const browser = await puppeteer.launch({
    executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe',
    headless: true,
    args: ['--ignore-gpu-blocklist', '--enable-gpu', '--use-angle=d3d11', '--enable-unsafe-swiftshader', '--hide-scrollbars', '--force-color-profile=srgb'],
  });
  const page = await browser.newPage();
  page.on('console', m => console.log('[page]', m.text()));
  page.on('pageerror', e => console.log('[pageerror]', e.message));
  await page.setViewport({ width: 1080, height: 1920, deviceScaleFactor: 1 });
  await page.goto(`http://127.0.0.1:${port}/scene.html`, { waitUntil: 'load' });
  await page.waitForFunction('window.READY === true', { timeout: 60000 });

  const args = process.argv.slice(2);
  if (args[0] === 'sample') {
    fs.mkdirSync(path.join(root, process.env.SD || 'samples'), { recursive: true });
    for (const ts of args.slice(1)) {
      const t = parseFloat(ts);
      await page.evaluate(t => window.renderFrame(t), t);
      await page.screenshot({ path: path.join(root, process.env.SD || 'samples', `${t.toFixed(2)}.png`) });
      console.log('sample', t);
    }
  } else {
    const start = +args[0], end = +args[1];
    fs.mkdirSync(path.join(root, 'frames'), { recursive: true });
    const t0 = Date.now();
    for (let i = start; i < end; i++) {
      const p = path.join(root, 'frames', String(i).padStart(5, '0') + '.png');
      if (fs.existsSync(p)) continue;
      await page.evaluate(t => window.renderFrame(t), i / 30);
      const tmp = p.replace('.png', '.tmp.png');
      await page.screenshot({ path: tmp, type: 'png' });
      fs.renameSync(tmp, p);
      if (i % 30 === 0) console.log('frame', i, ((Date.now() - t0) / 1000).toFixed(1) + 's');
    }
    console.log('selesai sampai frame', end - 1);
  }
  await browser.close();
  server.close();
})().catch(e => { console.error(e); process.exit(1); });
