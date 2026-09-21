#!/usr/bin/env node
/**
 * The owner's path from logos placed by hand on the Summit page to the Sponsors
 * screen, clicked through in a real browser. Run after `wp-env.sh up` and
 * `wp-env.sh seed-legacy`:
 *   WP_URL=http://localhost:8092 node scripts/test/import-flow.js [outdir]
 */
const puppeteer = require('puppeteer-core');
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const base = (process.env.WP_URL || 'http://localhost:8092').replace(/\/$/, '');
const out = process.argv[2] || 'import-shots';
const chrome = process.env.CHROME_BIN || ['/usr/bin/google-chrome', '/usr/bin/chromium'].find((p) => fs.existsSync(p));
let failed = 0;
const check = (name, ok, detail = '') => { if (!ok) failed += 1; console.log(`${ok ? 'PASS' : 'FAIL'}  ${name}${ok || !detail ? '' : ` — ${detail}`}`); };
const wp = (cmd) => execSync(`bash ${path.join(__dirname, 'wp-env.sh')} wp ${cmd}`, { encoding: 'utf8' }).trim();

(async () => {
  // The flow counts sponsors before and after the move, so it needs a site with none:
  // `wp-env.sh up`, then `seed-legacy`, and not `seed`.
  const existing = wp('post list --post_type=rad_sponsor --format=count');
  if (existing !== '0') {
    console.error(`This site already has ${existing} sponsors. Run \`wp-env.sh up\` then \`wp-env.sh seed-legacy\` (without \`seed\`) first.`);
    process.exit(2);
  }
  fs.mkdirSync(out, { recursive: true });
  const browser = await puppeteer.launch({ executablePath: chrome, headless: 'new', args: ['--no-sandbox', '--disable-gpu'] });
  const page = await browser.newPage();
  await page.setViewport({ width: 1300, height: 1100 });
  const go = (url) => page.goto(`${base}${url}${url.includes('?') ? '&' : '?'}rad_test_login=1`, { waitUntil: 'networkidle2', timeout: 90000 });

  await go('/wp-admin/');
  check('the Dashboard points out logos placed by hand on the Summit page', /Your Summit page has logos that were placed by hand/.test(await page.evaluate(() => document.body.innerText)));

  const summitBefore = await (await fetch(`${base}/summit/`)).text();
  check('before the move the Summit page still shows the placeholder names', summitBefore.includes('class="partner-grid'));

  await go('/wp-admin/edit.php?post_type=rad_sponsor&page=rad-sponsor-import');
  await page.screenshot({ path: path.join(out, 'import-page-logos.png') });
  const rows = await page.$$eval('.rad-import-page tbody tr', (trs) => trs.map((tr) => ({ name: tr.querySelector('input[type=text]').value, tier: tr.querySelector('select').selectedOptions[0].textContent })));
  check('eight organisations found, each logo once although some appear twice', rows.length === 8, JSON.stringify(rows.map((r) => r.name)));
  const tierOf = (needle) => (rows.find((r) => r.name.toLowerCase().includes(needle)) || {}).tier;
  check('tiers are read from the headings above each logo', tierOf('multiverse') === 'Gold Sponsor' && tierOf('clifford') === 'Dinner Sponsor' && tierOf('oqc') === 'Silver Sponsor' && tierOf('delta') === 'Bronze Sponsors' && tierOf('cpd') === 'Bronze Sponsors' && ['iop', 'mbda', 'farnborough'].every((n) => tierOf(n) === 'Collaborators'), JSON.stringify(rows));
  check('the photo that sits above every heading is not mistaken for a logo', !rows.some((r) => /photo|hall/i.test(r.name)));

  await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }), page.click('.rad-import-page ~ p ~ p input[type=submit], form:has(.rad-import-page) input[type=submit]')]);
  const t = await page.evaluate(() => document.body.innerText);
  check('a confirmation says 8 sponsors were added and taken off the page', /8 sponsors added/.test(t) && /taken off the Summit page/.test(t), t.slice(0, 200));
  check('there are now 8 sponsors', wp('post list --post_type=rad_sponsor --format=count') === '8');

  const summit = await (await fetch(`${base}/summit/`)).text();
  check('the Summit page now shows the sponsor wall', (summit.match(/<li class="sponsor /g) || []).length === 8 && !summit.includes('class="partner-grid'));
  check('the hand-made headings and pictures are no longer on the page', !/Gold Sponsor:/.test(summit) && !/wp-block-gallery[^"]*rad-logos/.test(summit) && !/<h2[^>]*>\s*(Gold|Dinner|Silver|Bronze) Sponsors?:/.test(summit));
  const summitId = wp('post list --post_type=page --name=summit --field=ID');
  const content = wp(`post get ${summitId} --field=post_content`);
  check('unrelated text and the photo on the page were left alone', content.includes('With thanks to everyone who makes the Summit possible.') && content.includes('a-photo-of-the-hall'));
  const revisions = wp(`post list --post_type=revision --post_parent=${summitId} --format=count`);
  check('WordPress kept the previous version of the page as a revision', Number(revisions) >= 1, `revisions: ${revisions}`);

  await go('/wp-admin/');
  check('the Dashboard notice is gone once the logos are moved', !/placed by hand/.test(await page.evaluate(() => document.body.innerText)));
  await go('/wp-admin/edit.php?post_type=rad_sponsor');
  await page.screenshot({ path: path.join(out, 'sponsors-after-move.png') });

  await browser.close();
  console.log(failed ? `\n${failed} failed` : '\nall passed');
  process.exit(failed ? 1 : 0);
})().catch((e) => { console.error(e); process.exit(1); });
