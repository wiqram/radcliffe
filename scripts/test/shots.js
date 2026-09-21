#!/usr/bin/env node
/**
 * Screenshots of one part of a page at several screen widths, so layout can be
 * checked by eye and not just by markup.
 *
 *   WP_URL=http://localhost:8092 node scripts/test/shots.js <path> <selector> <outdir> [--widths=1440,1024,768,390] [--max-height=N] [--from=N]
 *
 * Example: node scripts/test/shots.js /summit/ '#collaborators' /tmp/shots
 */
const path = require('path');
const fs = require('fs');
const puppeteer = require('puppeteer-core');

const [, , pagePath = '/', selector = 'body', outDir = 'shots', ...flags] = process.argv;
const base = (process.env.WP_URL || 'http://localhost:8092').replace(/\/$/, '');
const widthsFlag = flags.find((f) => f.startsWith('--widths='));
const widths = (widthsFlag ? widthsFlag.slice(9) : '1440,1024,768,390').split(',').map(Number);
const chrome = process.env.CHROME_BIN || ['/usr/bin/google-chrome', '/usr/bin/google-chrome-stable', '/usr/bin/chromium', '/usr/bin/chromium-browser'].find((p) => fs.existsSync(p));

(async () => {
  fs.mkdirSync(outDir, { recursive: true });
  const browser = await puppeteer.launch({ executablePath: chrome, headless: 'new', args: ['--no-sandbox', '--force-color-profile=srgb'] });
  const page = await browser.newPage();
  const messages = [];
  page.on('console', (m) => ['error', 'warning'].includes(m.type()) && messages.push(`${m.type()}: ${m.text()}`));
  page.on('pageerror', (e) => messages.push(`pageerror: ${e.message}`));

  for (const width of widths) {
    await page.setViewport({ width, height: 900, deviceScaleFactor: width < 600 ? 2 : 1 });
    await page.goto(base + pagePath, { waitUntil: 'networkidle2', timeout: 60000 });
    // Reveal-on-scroll content is hidden until scrolled to.
    await page.evaluate(async () => {
      for (let y = 0; y < document.body.scrollHeight; y += 500) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 40)); }
      window.scrollTo(0, 0);
      document.querySelectorAll('.reveal').forEach((el) => el.classList.add('in'));
    });
    await new Promise((r) => setTimeout(r, Number(process.env.SHOT_WAIT || 1400)));
    // Lazy images below the fold are not loaded by a screenshot of a region: load them all, and say if any is broken.
    const broken = await page.evaluate(async () => {
      const images = [...document.images];
      images.forEach((img) => { img.loading = 'eager'; });
      await Promise.all(images.map((img) => (img.complete ? null : new Promise((r) => { img.onload = r; img.onerror = r; setTimeout(r, 8000); }))));
      // Images use decoding="async": a tall screenshot can be taken before they are painted, leaving blank tiles.
      await Promise.all(images.map((img) => img.decode().catch(() => null)));
      return images.filter((img) => img.complete && img.naturalWidth === 0).map((img) => img.currentSrc || img.src);
    });
    if (broken.length) console.log(`  ! ${broken.length} broken image(s) at ${width}px: ${broken.slice(0, 3).join(', ')}`);
    // The fixed header and announcement bar would be photographed on top of the section.
    await page.addStyleTag({ content: '.header, .announce, .mobile-bar { display: none !important; }' });
    const el = await page.$(selector);
    if (!el) { console.error(`no element matches ${selector} at ${width}px`); continue; }
    const file = path.join(outDir, `${(pagePath.replace(/\W+/g, '_') || 'home')}-${width}.png`);
    const box = await el.boundingBox();
    const heightFlag = flags.find((f) => f.startsWith('--max-height='));
    const startFlag = flags.find((f) => f.startsWith('--from='));
    const from = startFlag ? Number(startFlag.slice(7)) : 0;
    const maxHeight = heightFlag ? Number(heightFlag.slice(13)) : box.height;
    await page.screenshot({ path: file, captureBeyondViewport: true, clip: { x: box.x, y: box.y + from, width: box.width, height: Math.min(maxHeight, box.height - from) } });
    console.log(`${width}px -> ${file} (${Math.round(box.width)}x${Math.round(box.height)})`);
  }
  if (messages.length) console.log('\nbrowser messages:\n' + messages.join('\n'));
  await browser.close();
})().catch((e) => { console.error(e); process.exit(1); });
