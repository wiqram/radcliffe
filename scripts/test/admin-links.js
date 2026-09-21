#!/usr/bin/env node
/**
 * The way from a page that looks empty in the page editor to where its words and photographs are edited.
 *
 *   WP_URL=http://localhost:8092 node scripts/test/admin-links.js
 *
 * The designed pages (Who's Who, Contact ...) keep their words in the Customizer, so their page editor is nearly
 * empty. The Pages list has an "Edit words and photos" link, and the editor has a button, that open the Customizer
 * on that page with its own section already open. Needs the test site's login helper (?rad_test_login=1).
 */
const puppeteer = require('puppeteer-core');
const fs = require('fs');
const { execSync } = require('child_process');

const base = (process.env.WP_URL || 'http://localhost:8092').replace(/\/$/, '');
const chrome = process.env.CHROME_BIN || ['/usr/bin/google-chrome', '/usr/bin/chromium'].find((p) => fs.existsSync(p));
let failed = 0;
const check = (name, ok, detail = '') => { if (!ok) failed += 1; console.log(`${ok ? 'PASS' : 'FAIL'}  ${name}${ok || !detail ? '' : ` — ${detail}`}`); };
const wp = (args) => execSync(`bash ${__dirname}/wp-env.sh wp ${args}`, { encoding: 'utf8' }).trim();
const SLUGS = ['home', 'practice', 'who', 'city', 'summit', 'agenda', 'articles', 'ethics', 'contact'];

(async () => {
  const extra = wp("post create --post_type=page --post_title='A page the owner added' --post_status=publish --porcelain");
  const browser = await puppeteer.launch({ executablePath: chrome, headless: 'new', args: ['--no-sandbox', '--disable-gpu'] });
  const page = await browser.newPage();
  await page.setViewport({ width: 1400, height: 900 });
  const goto = (url) => page.goto(`${base}${url}${url.includes('?') ? '&' : '?'}rad_test_login=1`, { waitUntil: 'networkidle2', timeout: 90000 });

  try {
    // The Pages list.
    await goto('/wp-admin/edit.php?post_type=page');
    const rows = await page.evaluate(() => [...document.querySelectorAll('#the-list tr')].map((tr) => ({
      id: Number((tr.id || '').replace('post-', '')),
      title: tr.querySelector('.row-title')?.textContent.trim(),
      actions: [...tr.querySelectorAll('.row-actions a')].map((a) => ({ text: a.textContent.trim(), href: a.getAttribute('href') })),
    })));
    const linkFor = (r) => r.actions.find((a) => a.text === 'Edit words and photos');
    const designed = rows.filter((r) => Number(r.id) !== Number(extra) && linkFor(r));
    check('nine designed pages carry an "Edit words and photos" link', designed.length === 9, `${designed.length}: ${designed.map((r) => r.title).join(', ')}`);
    const ownRow = rows.find((r) => Number(r.id) === Number(extra));
    check('a page the owner added has no such link (nothing designed to edit)', ownRow && !linkFor(ownRow));
    const who = rows.find((r) => /Who/.test(r.title || ''));
    const order = who ? who.actions.map((a) => a.text) : [];
    check('the link sits beside Edit', order[0] === 'Edit' && order[1] === 'Edit words and photos', order.join(' | '));

    // Following the link from every designed page opens the Customizer on that page's own section.
    const ids = {};
    for (const r of designed) {
      const href = linkFor(r).href.replace(/&amp;/g, '&');
      const wanted = new URL(href, base).searchParams.get('url') || '';
      await page.goto(`${base}${href.startsWith('http') ? href.replace(/^https?:\/\/[^/]+/, '') : href}`, { waitUntil: 'networkidle2', timeout: 90000 });
      const seen = await page.evaluate(async () => {
        for (let i = 0; i < 60; i += 1) { // the Customizer takes a moment to build its sections
          if (window.wp && wp.customize && wp.customize.state && wp.customize.section) {
            const open = [];
            wp.customize.section.each((s) => { if (s.expanded && s.expanded()) open.push(s.id); });
            if (open.length) return { open, preview: wp.customize.previewer && wp.customize.previewer.previewUrl ? String(wp.customize.previewer.previewUrl()) : '' };
          }
          await new Promise((res) => setTimeout(res, 250));
        }
        return { open: [], preview: '' };
      });
      const slug = SLUGS.find((s) => seen.open.includes(`rad_section_${s}`));
      ids[r.title] = slug;
      check(`"${r.title}": the Customizer opens on its own section, showing that page`, Boolean(slug) && wanted !== '' && seen.preview.replace(/\/$/, '') === wanted.replace(/\/$/, ''), `open: ${seen.open.join(',') || 'none'}; preview: ${seen.preview}; wanted: ${wanted}`);
    }
    check('nine different sections were reached', new Set(Object.values(ids)).size === 9, JSON.stringify(ids));

    // The page editor: a button in the notice, for a designed page but not for the owner's own page.
    const whoId = who && who.id;
    await goto(`/wp-admin/post.php?post=${whoId}&action=edit`);
    await page.waitForSelector('.components-notice', { timeout: 30000 }).catch(() => null);
    const notice = await page.evaluate(() => {
      const n = document.querySelector('.components-notice');
      const a = n && [...n.querySelectorAll('a')].find((x) => /Edit words and photos/.test(x.textContent));
      return { text: n ? n.textContent : '', href: a ? a.getAttribute('href') : '' };
    });
    check("Who's Who's editor explains the empty page and has an \"Edit words and photos\" button", /appears as a new section/.test(notice.text) && /customize\.php/.test(notice.href) && /rad_section_who/.test(decodeURIComponent(notice.href)), JSON.stringify(notice).slice(0, 200));
    await goto(`/wp-admin/post.php?post=${extra}&action=edit`);
    await new Promise((res) => setTimeout(res, 2500));
    const ownNotice = await page.evaluate(() => [...document.querySelectorAll('.components-notice a')].some((x) => /Edit words and photos/.test(x.textContent)));
    check("a page the owner added gets no button", !ownNotice);
  } finally {
    wp(`post delete ${extra} --force`);
    await browser.close();
  }
  console.log(failed ? `\n${failed} failed` : '\nall passed');
  process.exit(failed ? 1 : 0);
})().catch((e) => { console.error(e); process.exit(1); });
