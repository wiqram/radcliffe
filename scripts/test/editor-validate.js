#!/usr/bin/env node
/**
 * Opens the real WordPress block editor in headless Chrome and checks that
 * everything the theme hands the owner is valid block markup:
 *   - every pattern under "Redcliffe Advisory" (what "Attempt recovery" errors come from)
 *   - the content of any page passed with --page=<slug> (e.g. the seeded Agenda)
 * A block is invalid when its saved HTML differs from what the editor would
 * regenerate — the owner sees "Block contains unexpected or invalid content".
 *
 *   WP_URL=http://localhost:8092 node scripts/test/editor-validate.js [--page=agenda ...]
 */
const puppeteer = require('puppeteer-core');

const base = process.env.WP_URL || 'http://localhost:8092';
const pages = process.argv.filter((a) => a.startsWith('--page=')).map((a) => a.slice(7));

(async () => {
  const browser = await puppeteer.launch({
    executablePath: process.env.CHROME || '/usr/bin/google-chrome',
    headless: 'new',
    args: ['--no-sandbox', '--disable-gpu'],
  });
  const page = await browser.newPage();
  await page.setViewport({ width: 1400, height: 900 });
  const validation = [];
  page.on('console', (msg) => {
    const text = msg.text();
    if (/Block validation|Expected|Content generated|Block successfully updated/i.test(text)) validation.push(text.slice(0, 600));
  });

  await page.goto(`${base}/wp-admin/post-new.php?post_type=page&rad_test_login=1`, { waitUntil: 'networkidle2', timeout: 120000 });
  await page.waitForFunction(() => window.wp && wp.data && wp.data.select('core/block-editor') && wp.blocks.getBlockTypes().length > 20, { timeout: 60000 });

  const report = await page.evaluate(async (pageSlugs) => {
    const invalid = [];
    const walk = (blocks, label) => {
      for (const b of blocks) {
        if (!b.isValid) invalid.push({ label, block: b.name, snippet: (b.originalContent || '').slice(0, 220) });
        walk(b.innerBlocks || [], label);
      }
    };
    const checked = [];

    const patterns = await wp.apiFetch({ path: '/wp/v2/block-patterns/patterns' });
    for (const p of patterns.filter((x) => (x.categories || []).includes('redcliffe-advisory'))) {
      const blocks = wp.blocks.parse(p.content.raw || p.content);
      checked.push(`pattern:${p.name}`);
      walk(blocks, `pattern:${p.name}`);
    }
    for (const slug of pageSlugs) {
      const found = await wp.apiFetch({ path: `/wp/v2/pages?slug=${slug}&context=edit` });
      if (!found.length) { invalid.push({ label: `page:${slug}`, block: '(page not found)', snippet: '' }); continue; }
      checked.push(`page:${slug}`);
      walk(wp.blocks.parse(found[0].content.raw), `page:${slug}`);
    }
    return { checked, invalid };
  }, pages);

  await browser.close();
  console.log(`Checked ${report.checked.length}: ${report.checked.join(', ')}`);
  if (report.invalid.length) {
    console.log(`\nINVALID BLOCKS: ${report.invalid.length}`);
    for (const i of report.invalid) console.log(` - ${i.label} → ${i.block}\n     ${i.snippet.replace(/\s+/g, ' ')}`);
    if (validation.length) console.log('\nEditor said:\n' + validation.slice(0, 6).join('\n---\n'));
    process.exit(1);
  }
  console.log('All blocks valid in the real editor.');
})().catch((e) => { console.error(e); process.exit(2); });
