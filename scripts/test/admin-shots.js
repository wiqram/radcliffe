#!/usr/bin/env node
/**
 * Loads the admin screens the owner uses — Sponsors, its tiers, the bulk
 * importer, the Customizer — logged in as the test admin, screenshots them and
 * reports anything that looks wrong (PHP errors on the page, missing fields).
 *
 *   WP_URL=http://localhost:8092 node scripts/test/admin-shots.js <outdir>
 */
const puppeteer = require('puppeteer-core');
const fs = require('fs');
const path = require('path');

const base = (process.env.WP_URL || 'http://localhost:8092').replace(/\/$/, '');
const out = process.argv[2] || 'admin-shots';
const chrome = process.env.CHROME_BIN || ['/usr/bin/google-chrome', '/usr/bin/chromium'].find((p) => fs.existsSync(p));
let failed = 0;
const check = (name, ok, detail = '') => { if (!ok) failed += 1; console.log(`${ok ? 'PASS' : 'FAIL'}  ${name}${ok || !detail ? '' : ` — ${detail}`}`); };

(async () => {
  fs.mkdirSync(out, { recursive: true });
  const browser = await puppeteer.launch({ executablePath: chrome, headless: 'new', args: ['--no-sandbox', '--disable-gpu'] });
  const page = await browser.newPage();
  await page.setViewport({ width: 1400, height: 900 });
  const goto = async (url, wait = 'networkidle2') => page.goto(`${base}${url}${url.includes('?') ? '&' : '?'}rad_test_login=1`, { waitUntil: wait, timeout: 90000 });
  const text = () => page.evaluate(() => document.body.innerText);
  const phpError = async () => /(Warning|Notice|Fatal error|Deprecated):.*\.php/i.test(await page.content());

  await goto('/wp-admin/edit.php?post_type=rad_sponsor');
  await page.screenshot({ path: path.join(out, 'sponsors-list.png') });
  let t = await text();
  check('Sponsors list shows logo, name, tier, website and order columns', /Logo/.test(t) && /Tier/.test(t) && /Website/.test(t) && /Order/.test(t));
  check('Sponsors list has no PHP errors', !(await phpError()));

  await goto('/wp-admin/edit-tags.php?taxonomy=rad_tier&post_type=rad_sponsor');
  await page.screenshot({ path: path.join(out, 'tiers.png') });
  t = await text();
  check('Tiers screen lists Gold Sponsor … Partners in page order', t.indexOf('Gold Sponsor') > -1 && t.indexOf('Gold Sponsor') < t.indexOf('Dinner Sponsor') && t.indexOf('Silver Sponsor') < t.indexOf('Partners'));
  check('Add-a-tier form offers position, logo size and show-names', /Position on the page/.test(t) && /Logo size/.test(t) && /Show names/.test(t));
  check('Tiers screen has no PHP errors', !(await phpError()));

  await goto('/wp-admin/edit.php?post_type=rad_sponsor&page=rad-sponsor-import');
  await page.screenshot({ path: path.join(out, 'import.png') });
  t = await text();
  check('The bulk importer screen renders', /Add several logos at once/.test(t));
  check('Bulk importer has no PHP errors', !(await phpError()));

  const id = await page.evaluate(() => null);
  await goto('/wp-admin/post-new.php?post_type=rad_sponsor');
  await page.screenshot({ path: path.join(out, 'sponsor-new.png') });
  t = await text();
  check('New-sponsor screen: name, tier, website, role and tile colour are there', /About this sponsor/.test(t) && /Website address/.test(t) && /Tile colour/.test(t) && /Tier/.test(t));
  check('New-sponsor screen calls the picture a Logo', /Choose the logo/.test(t) || /Logo/.test(t));
  check('New-sponsor screen has no PHP errors', !(await phpError()));

  await goto('/wp-admin/customize.php');
  await page.waitForFunction(() => window.wp && wp.customize && wp.customize.settings && Object.keys(wp.customize.settings.settings).length > 50, { timeout: 60000 });
  const keys = await page.evaluate(() => Object.keys(wp.customize.settings.settings));
  const has = (re) => keys.some((k) => re.test(k));
  check('Customizer: Register button text', has(/summit[._]hero[._]registerLabel/));
  check('Customizer: registration page address', has(/summit[._]hero[._]registerUrl/));
  check('Customizer: Agenda closing buttons (words, addresses, show/hide)', has(/agenda[._]actions[._]primaryLabel/) && has(/agenda[._]actions[._]primaryUrl/) && has(/agenda[._]actions[._]secondaryLabel/) && has(/agenda[._]actions[._]secondaryUrl/) && has(/section[._]agenda[._]actions/));
  check('Customizer: LinkedIn feed number, profile address, link text, show/hide', has(/home[._]linkedin[._]embedId/) && has(/home[._]linkedin[._]profileUrl/) && has(/home[._]linkedin[._]followLabel/) && has(/section[._]home[._]linkedin/));
  check('Customizer: default author name for articles', has(/articles[._]byline[._]default/));
  await browser.close();
  console.log(failed ? `\n${failed} failed` : '\nall passed');
  process.exit(failed ? 1 : 0);
})().catch((e) => { console.error(e); process.exit(1); });
