#!/usr/bin/env node
/**
 * Does the site still hold together after the owner has "done things"? Loads every page at four screen widths
 * and checks what an owner can break by editing but should never have to think about:
 *   - the page scrolls sideways, or text runs off the right of the screen where it is silently cut off
 *   - a picture is stretched (drawn at a different shape from the file, without object-fit)
 *   - a picture is broken
 *   - the header's logo and menu run into each other, or the header grows very tall
 * and reports, without failing, pictures cropped down to a small part of themselves.
 *
 *   WP_URL=http://localhost:8092 node scripts/test/stress.js [--widths=1440,1024,768,390] [--pages=/,/summit/] [--shots=outdir] [--expect-menu=expanded]
 * Run after `wp-env.sh stress-seed`. Also runs against a real site: it only reads.
 */
const fs = require('fs');
const path = require('path');
const puppeteer = require('puppeteer-core');

const base = (process.env.WP_URL || 'http://localhost:8092').replace(/\/$/, '');
const flag = (name, dflt) => { const f = process.argv.find((a) => a.startsWith(`--${name}=`)); return f ? f.slice(name.length + 3) : dflt; };
const widths = flag('widths', '1440,1024,768,390').split(',').map(Number);
const pages = flag('pages', '/,/practice/,/who/,/city/,/summit/,/agenda/,/articles/,/ethics/,/contact/,/stress-article/,/stress-short/').split(',');
const shotsDir = flag('shots', '');
const expectMenu = flag('expect-menu', 'any'); // 'expanded': on a normal site the designed menu must stay a single line at 1024px and up
const chrome = process.env.CHROME_BIN || ['/usr/bin/google-chrome', '/usr/bin/chromium'].find((p) => fs.existsSync(p));
let failed = 0;
let warned = 0;

(async () => {
  if (shotsDir) fs.mkdirSync(shotsDir, { recursive: true });
  const browser = await puppeteer.launch({ executablePath: chrome, headless: 'new', args: ['--no-sandbox', '--force-color-profile=srgb'] });
  const page = await browser.newPage();
  for (const width of widths) {
    await page.setViewport({ width, height: 900, deviceScaleFactor: 1, isMobile: width < 600 });
    for (const url of pages) {
      const res = await page.goto(base + url, { waitUntil: 'networkidle2', timeout: 90000 }).catch((e) => ({ status: () => `error ${e.message}` }));
      if (res.status() !== 200) { failed += 1; console.log(`FAIL ${url} @${width}: HTTP ${res.status()}`); continue; }
      await page.evaluate(async () => {
        for (let y = 0; y < document.body.scrollHeight; y += 500) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 30)); }
        window.scrollTo(0, 0);
        document.querySelectorAll('.reveal').forEach((el) => el.classList.add('in'));
        const imgs = [...document.images];
        imgs.forEach((i) => { i.loading = 'eager'; });
        await Promise.all(imgs.map((i) => (i.complete ? null : new Promise((r) => { i.onload = r; i.onerror = r; setTimeout(r, 6000); }))));
        await Promise.all(imgs.map((i) => i.decode().catch(() => null)));
      });
      const m = await page.evaluate(() => {
        const vw = document.documentElement.clientWidth;
        const label = (e) => `${e.tagName.toLowerCase()}${e.id ? '#' + e.id : ''}${e.className && typeof e.className === 'string' ? '.' + e.className.trim().split(/\s+/).slice(0, 2).join('.') : ''}`;
        const out = { overflow: document.documentElement.scrollWidth - vw, offenders: [], stretched: [], broken: [], cropped: [], header: {} };
        // The page hides sideways overflow (overflow-x: hidden), so scrollWidth cannot be trusted: look at where things actually end.
        // Something that runs past the screen is fine inside a box that scrolls or clips on its own (a table, a ticker).
        const contained = (e) => {
          for (let p = e.parentElement; p && p !== document.body && p !== document.documentElement; p = p.parentElement) {
            const s = getComputedStyle(p);
            if (['auto', 'scroll', 'hidden', 'clip'].includes(s.overflowX) && p.getBoundingClientRect().right <= vw + 1) return true;
          }
          return false;
        };
        for (const e of document.querySelectorAll('body *')) {
          const r = e.getBoundingClientRect();
          const cs = getComputedStyle(e);
          if (r.width > 0 && r.height > 0 && r.right > vw + 1 && cs.position !== 'fixed' && cs.visibility !== 'hidden' && !e.closest('.reveal:not(.in)') && !contained(e)) out.offenders.push(`${label(e)} right=${Math.round(r.right)}`);
          if (out.offenders.length >= 6) break;
        }
        if (out.overflow > 1 && !out.offenders.length) out.offenders.push('nothing identifiable');
        for (const img of document.images) {
          const r = img.getBoundingClientRect();
          if (!r.width || !r.height || getComputedStyle(img).visibility === 'hidden' || getComputedStyle(img).display === 'none') continue;
          const src = (img.currentSrc || img.src).split('/').pop().slice(0, 40);
          if (img.complete && img.naturalWidth === 0) { out.broken.push(src); continue; }
          const nat = img.naturalWidth / img.naturalHeight;
          const shown = r.width / r.height;
          const fit = getComputedStyle(img).objectFit;
          if ((fit === 'fill' || fit === 'none') && Math.abs(shown / nat - 1) > 0.04 && img.naturalWidth > 1) out.stretched.push(`${src} file ${img.naturalWidth}x${img.naturalHeight} drawn ${Math.round(r.width)}x${Math.round(r.height)}`);
          if (fit === 'cover') {
            const scale = Math.max(r.width / img.naturalWidth, r.height / img.naturalHeight);
            const visible = (r.width * r.height) / (img.naturalWidth * img.naturalHeight * scale * scale);
            if (visible < 0.2 && img.naturalWidth > 100) out.cropped.push(`${src} shows ${Math.round(visible * 100)}% of the file`);
          }
        }
        const header = document.querySelector('.header');
        if (header) {
          const hb = header.getBoundingClientRect();
          out.header.height = Math.round(hb.height);
          out.header.collapsed = document.documentElement.classList.contains('nav-collapsed');
          const brand = header.querySelector('.brand, .brand-logo');
          const nav = header.querySelector('.nav');
          if (brand && nav && getComputedStyle(nav).display !== 'none') {
            const a = brand.getBoundingClientRect(); const b = nav.getBoundingClientRect();
            out.header.overlap = a.width && b.width && a.right > b.left + 1 && a.left < b.right - 1 && a.bottom > b.top + 1 && a.top < b.bottom - 1;
            out.header.navWidth = Math.round(b.width);
          }
          const links = [...header.querySelectorAll('.nav a')];
          if (links.length && getComputedStyle(nav).display !== 'none') {
            // Rows are told apart by the middle of each link: the Enquire button is taller than the others.
            const mids = links.map((l) => { const r = l.getBoundingClientRect(); return r.top + r.height / 2; });
            out.header.navRows = Math.max(...mids) - Math.min(...mids) > 12 ? 2 : 1;
          }
        }
        return out;
      });
      const problems = [];
      if (m.overflow > 1 || m.offenders.length) problems.push(`${m.overflow > 1 ? `page scrolls sideways by ${m.overflow}px` : 'something runs off the right of the screen and is cut off'} (${m.offenders.join('; ')})`);
      if (m.stretched.length) problems.push(`stretched: ${m.stretched.slice(0, 3).join(' | ')}`);
      if (m.broken.length) problems.push(`broken images: ${m.broken.slice(0, 3).join(', ')}`);
      if (m.header.overlap) problems.push('header logo and menu overlap');
      if (m.header.height > (width < 600 ? 140 : 130)) problems.push(`header is ${m.header.height}px tall`);
      if (width >= 1000 && m.header.navRows > 1) problems.push(`menu wraps onto ${m.header.navRows} rows`);
      if (expectMenu === 'expanded' && width >= 1024 && m.header.collapsed) problems.push('the menu collapsed to the menu button although it fits');
      if (problems.length) { failed += 1; console.log(`FAIL ${url} @${width}: ${problems.join(' ; ')}`); }
      if (m.cropped.length) { warned += 1; console.log(`note ${url} @${width}: cropped to a sliver: ${m.cropped.slice(0, 2).join(' | ')}`); }
      if (shotsDir) await page.screenshot({ path: path.join(shotsDir, `${(url.replace(/\W+/g, '_') || 'home')}-${width}.png`), fullPage: true });
    }
  }
  await browser.close();
  console.log(`\n${pages.length * widths.length} page views checked, ${failed} with problems, ${warned} with notes`);
  process.exit(failed ? 1 : 0);
})();
