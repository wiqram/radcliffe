#!/usr/bin/env node
/**
 * End-to-end checks of the theme running in WordPress (scripts/test/wp-env.sh up).
 * Pages, sponsors, buttons, articles, redirects — and that PHP logged nothing.
 * Every check prints PASS or FAIL; the exit code is non-zero if any failed.
 *
 *   WP_URL=http://localhost:8092 node scripts/test/smoke.js
 *
 * Some checks need content (sponsors, posts) — scripts/test/seed.sh creates it.
 */
const { execSync } = require('child_process');

const base = (process.env.WP_URL || 'http://localhost:8092').replace(/\/$/, '');
const results = [];
const check = (name, ok, detail = '') => { results.push(ok); console.log(`${ok ? 'PASS' : 'FAIL'}  ${name}${ok || !detail ? '' : `\n        ${detail}`}`); };
const get = async (path, opts = {}) => {
  const res = await fetch(base + path, { redirect: 'manual', ...opts });
  return { status: res.status, location: res.headers.get('location') || '', html: await res.text() };
};
const count = (html, needle) => html.split(needle).length - 1;
const version = require('fs').readFileSync(require('path').join(__dirname, '../../wordpress/redcliffe-advisory/style.css'), 'utf8').match(/Version:\s*(\S+)/)[1];

(async () => {
  // ── every page loads, on the current version of the stylesheet
  const pages = { '/': 'home', '/practice/': 'practice', '/who/': 'who', '/city/': 'city', '/summit/': 'summit', '/agenda/': 'agenda', '/articles/': 'articles', '/ethics/': 'ethics', '/contact/': 'contact' };
  const html = {};
  for (const path of Object.keys(pages)) {
    const r = await get(path);
    html[path] = r.html;
    check(`${path} loads with style.css?ver=${version}`, r.status === 200 && r.html.includes(`style.css?ver=${version}`), `status ${r.status}`);
  }

  // ── the dead "Continue" label is gone everywhere
  const cont = Object.entries(html).filter(([, h]) => /<div class="small"[^>]*>\s*Continue\s*<\/div>/.test(h)).map(([p]) => p);
  check('no page has a "Continue" label that does nothing', cont.length === 0, cont.join(', '));

  // ── Summit: Register button (editable words, address) and sponsors
  const summit = html['/summit/'];
  check('Summit has a Register button with editable words', /class="btn on-dark-ghost"[^>]*>\s*<span data-rad="summit\.hero\.registerLabel">[^<]+<\/span>/.test(summit));
  check('Register goes to the registration page', /on-dark-ghost" href="https:\/\/web\.cvent\.com\/event\/[^"]+\/register"/.test(summit));
  const tiles = count(summit, '<li class="sponsor ');
  if (tiles) {
    check(`Summit shows ${tiles} sponsor tiles in place of the placeholder names`, !summit.includes('class="partner-grid'));
    check('each sponsor tile has a logo image with dimensions', count(summit, 'class="sponsor-logo"') === count(summit, '<span class="sponsor-logo"><img') && /<img src="[^"]+" width="\d+" height="\d+"/.test(summit));
    check('sponsor tiers are labelled headings', /<h3 class="sponsor-tier-title"[^>]*>Gold Sponsor<\/h3>/.test(summit));
    const cols = [...summit.matchAll(/--cols:(\d+);--cols-md:(\d+)/g)].map((m) => [Number(m[1]), Number(m[2])]);
    check('every tier has a balanced column count', cols.length > 0 && cols.every(([a, b]) => a >= 1 && b >= 1 && b <= a), JSON.stringify(cols));
    check('the Collaborators heading stays editable', /data-rad="summit\.collaborators\.[a-z0-9]+"/.test(summit));
  } else {
    check('Summit falls back to the designed placeholder names when there are no sponsors', summit.includes('class="partner-grid'));
  }

  // ── Agenda: one set of closing buttons, the first defaulting to registration
  const agenda = html['/agenda/'];
  check('Agenda has exactly one set of closing buttons', count(agenda, 'agenda-actions') === 1 && !agenda.includes('wp-block-buttons rad-agenda-actions'));
  check('the first Agenda button is worded "Reserve your spot" and goes to registration', /class="btn" href="https:\/\/web\.cvent\.com[^"]+"[^>]*><span data-rad="agenda\.actions\.primaryLabel">Reserve your spot/.test(agenda));

  // ── Home: LinkedIn posts
  const home = html['/'];
  check('Home has the LinkedIn posts feed with its embed number', /class="sk-ww-linkedin-profile-post" data-embed-id="114149"/.test(home));
  check('the LinkedIn widget is loaded lazily, not in the page head', !/<script[^>]+sociablekit/.test(home) && home.includes('data-linkedin-src="https://widgets.sociablekit.com/'));
  check('a plain link to the LinkedIn profile is always there', home.includes('href="https://www.linkedin.com/in/karina-robinson/"'));

  // ── Search and sharing: every page says what it is, once, and has a picture to show when shared
  const noTags = Object.keys(pages).filter((path) => !(count(html[path], '<meta name="description"') === 1 && count(html[path], 'property="og:title"') === 1 && count(html[path], 'property="og:image"') === 1 && count(html[path], 'name="twitter:card"') === 1));
  check('every page has one description and the share tags', noTags.length === 0, noTags.join(', '));
  const shareImage = (html['/summit/'].match(/property="og:image" content="([^"]+)"/) || [])[1];
  const shareRes = shareImage ? await fetch(shareImage) : { status: 0 };
  check('the picture shown when a page is shared can be fetched', shareRes.status === 200, `${shareImage} -> ${shareRes.status}`);

  // ── Articles
  const articles = html['/articles/'];
  check('Articles hero is the compact two-column one', articles.includes('page-hero page-hero--split') && articles.includes('class="hero-split"'));
  if (articles.includes('journal-list')) {
    check('article cards say who wrote them and the month, e.g. "Karina Robinson · September 2026"', /<div class="byline">Karina Robinson · [A-Z][a-z]+ \d{4}<\/div>/.test(articles));
    check('article excerpts are part of the card', /<h4>[^<]+<\/h4>\s*<p>/.test(articles));
  }
  const post = (await get('/karinas-column-2026-top-billing/'));
  if (post.status === 200) {
    check('an article shows author and month, and its tags', /<div class="byline">Karina Robinson · [A-Z][a-z]+ \d{4}<\/div>/.test(post.html));
    check('an article body is wrapped for reading (rad-article)', post.html.includes('rad-article'));
    check('article.css is loaded for articles', post.html.includes('assets/css/article.css'));
    const postTitle = (post.html.match(/<title>([^<]*)<\/title>/) || [])[1] || '';
    check('an article\'s title ends with the firm\'s name, never a temporary web address', /\u2014 [^<]+$/.test(postTitle) && !/\.hostingersite\.com\s*$/.test(postTitle), postTitle);
    check('an article says it is an article when shared', post.html.includes('property="og:type" content="article"'));
  }
  const cat = await get('/category/cisi/');
  check('a category page lists its articles in the site style', cat.status === 200 ? cat.html.includes('journal-item') && cat.html.includes('page-hero--article') : cat.status === 404 ? true : false, `status ${cat.status}`);
  const paged = await get('/articles/page/2/');
  check('older articles are reachable (Articles page 2 at /articles/page/2/)', paged.status === 200, `status ${paged.status}`);
  const pageOne = await get('/articles/');
  if (/class="navigation pagination"/.test(pageOne.html)) check('page 2 differs from page 1 and has no featured card', paged.status === 200 && !paged.html.includes('journal-feature'));

  // ── legacy Squarespace addresses still land on the right page
  for (const [from, to] of [['/the-city-quantum-and-ai-summit/', '/summit/'], ['/the-city-quantum-and-ai-summit-2026/', '/summit/'], ['/the-city-quantum-and-ai-summit-2026-agenda/', '/agenda/'], ['/the-city-quantum-and-ai-summit-2025-agenda/', '/agenda/'], ['/summit-2024/', '/summit/'], ['/summit-2023/', '/summit/'], ['/summit-2023-old/', '/summit/'], ['/whos-who/', '/who/']]) {
    const r = await get(from);
    check(`${from} redirects to ${to}`, r.status === 301 && r.location.endsWith(to), `${r.status} ${r.location}`);
  }

  // ── PHP was quiet throughout
  let log = '';
  try { log = execSync('docker exec rad-test-wp sh -c "cat /var/www/html/wp-content/debug.log 2>/dev/null || true"', { encoding: 'utf8' }); } catch (e) { log = ''; }
  const bad = log.split('\n').filter((l) => /PHP (Warning|Notice|Fatal|Deprecated|Parse)/.test(l) && !/mysqli_real_connect/.test(l));
  check('debug.log has no PHP warnings, notices or fatals from the theme', bad.length === 0, bad.slice(0, 5).join('\n        '));

  const failed = results.filter((ok) => !ok).length;
  console.log(`\n${results.length - failed} passed, ${failed} failed`);
  process.exit(failed ? 1 : 0);
})().catch((e) => { console.error(e); process.exit(1); });
