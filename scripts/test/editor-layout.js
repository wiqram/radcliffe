#!/usr/bin/env node
/**
 * Opens a post or page in the real block editor and reports where its title
 * and blocks sit — left edge, width, type size, font — so "headings line up
 * with the text" is measured rather than eyeballed. Also saves a screenshot.
 *
 *   WP_URL=http://localhost:8092 node scripts/test/editor-layout.js <post-id> [outfile.png]
 */
const puppeteer = require('puppeteer-core');
const fs = require('fs');

const base = process.env.WP_URL || 'http://localhost:8092';
const id = process.argv[2];
const out = process.argv[3] || 'editor.png';
if (!id) { console.error('usage: editor-layout.js <post-id> [outfile.png]'); process.exit(1); }
const chrome = process.env.CHROME_BIN || ['/usr/bin/google-chrome', '/usr/bin/chromium'].find((p) => fs.existsSync(p));

(async () => {
  const browser = await puppeteer.launch({ executablePath: chrome, headless: 'new', args: ['--no-sandbox', '--disable-gpu'] });
  const page = await browser.newPage();
  await page.setViewport({ width: 1500, height: 1000 });
  await page.goto(`${base}/wp-admin/post.php?post=${id}&action=edit&rad_test_login=1`, { waitUntil: 'networkidle2', timeout: 120000 });
  await page.waitForFunction(() => window.wp && wp.data && wp.data.select('core/block-editor') && wp.data.select('core/block-editor').getBlocks().length > 0, { timeout: 60000 });
  // No welcome guide in the way.
  await page.evaluate(() => { try { wp.data.dispatch('core/preferences').set('core/edit-post', 'welcomeGuide', false); wp.data.dispatch('core/preferences').set('core/edit-post', 'welcomeGuideTemplate', false); } catch (e) {} });
  await new Promise((r) => setTimeout(r, 2500));

  const report = await page.evaluate(() => {
    const frame = document.querySelector('iframe[name="editor-canvas"]');
    const doc = frame ? frame.contentDocument : document;
    const win = frame ? frame.contentWindow : window;
    const root = doc.querySelector('.editor-styles-wrapper');
    const rows = [];
    const pick = (label, sel) => doc.querySelectorAll(sel).forEach((el, i) => {
      if (i > 1 && label !== 'p') return;
      const r = el.getBoundingClientRect(); const cs = win.getComputedStyle(el);
      rows.push({ label, left: Math.round(r.left), width: Math.round(r.width), font: cs.fontFamily.split(',')[0].replace(/"/g, ''), size: cs.fontSize, lh: cs.lineHeight, align: cs.textAlign, text: (el.textContent || '').trim().slice(0, 34) });
    });
    pick('title', '.wp-block-post-title, .editor-post-title__input');
    pick('p', '.is-root-container > .wp-block p, .is-root-container p');
    ['h1', 'h2', 'h3', 'h4'].forEach((h) => pick(h, `.is-root-container ${h}.wp-block-heading`));
    pick('quote', '.is-root-container .wp-block-quote');
    pick('list', '.is-root-container ul');
    return { iframe: !!frame, bodyClass: doc.body.className.slice(0, 80), rootIsBody: root === doc.body, rows };
  });
  console.log(`iframe: ${report.iframe}; body: ${report.bodyClass}`);
  console.table(report.rows);
  await page.screenshot({ path: out });
  console.log('screenshot ->', out);
  await browser.close();
})().catch((e) => { console.error(e); process.exit(1); });
