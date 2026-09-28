#!/usr/bin/env node
/**
 * The homepage quotations as a visitor sees them, in a real browser.
 *
 *   WP_URL=http://localhost:8092 node scripts/test/testimonials.js
 *
 * scripts/test/testimonials.sh checks the words the server sends. This checks what is left on
 * the screen once the page's own JavaScript has run — the thing that went wrong before theme
 * 1.4.7, when the rotator wrote the quotations itself and replaced the published ones on load.
 */
const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');
const puppeteer = require('puppeteer-core');

const base = (process.env.WP_URL || 'http://localhost:8092').replace(/\/$/, '');
const chrome = process.env.CHROME_BIN || ['/usr/bin/google-chrome', '/usr/bin/google-chrome-stable', '/usr/bin/chromium', '/usr/bin/chromium-browser'].find((p) => fs.existsSync(p));
const wp = (...args) => execFileSync('bash', [path.join(__dirname, 'wp-env.sh'), 'wp', ...args], { encoding: 'utf8' }).trim();

const results = [];
const check = (name, ok, detail = '') => { results.push(ok); console.log(`${ok ? 'PASS' : 'FAIL'}  ${name}${ok || !detail ? '' : `\n        ${detail}`}`); };

// What the owner publishes in Appearance > Customize > Redcliffe Advisory > Homepage.
const published = [
  { quote: 'A room where the hard questions get asked politely.', attribution: 'A pension fund trustee · Chatham House rule' },
  { quote: 'Physicists and treasurers, in one room, without an interpreter.', attribution: 'A NATO advisor · Quantum Strategy' },
];

(async () => {
  published.forEach((t, i) => {
    wp('theme', 'mod', 'set', `rad_home_testimonial_${i + 1}_quote`, t.quote);
    wp('theme', 'mod', 'set', `rad_home_testimonial_${i + 1}_attribution`, t.attribution);
  });

  const browser = await puppeteer.launch({ executablePath: chrome, headless: 'new', args: ['--no-sandbox'] });
  const page = await browser.newPage();
  const noise = [];
  page.on('console', (m) => ['error', 'warning'].includes(m.type()) && noise.push(`${m.type()}: ${m.text()}`));
  page.on('pageerror', (e) => noise.push(`pageerror: ${e.message}`));

  try {
    await page.setViewport({ width: 1440, height: 900 });
    await page.goto(`${base}/`, { waitUntil: 'networkidle2', timeout: 60000 });
    // A theme that writes the quotations from its own list has no slots to show.
    const slots = await page.$$eval('.test-item', (els) => els.length).catch(() => 0);
    if (!slots) {
      check('the homepage has a quotation slot for each published quotation', false, 'no .test-item on the page — this theme builds the quotations in JavaScript');
      throw new Error('no testimonial slots');
    }
    await page.waitForSelector('.test-item:not([hidden])', { timeout: 10000 });

    const onScreen = () => page.evaluate(() => {
      const shown = [...document.querySelectorAll('.test-item')].filter((el) => !el.hidden);
      const counter = document.getElementById('t-count');
      return {
        showing: shown.length,
        quote: shown.length ? shown[0].querySelector('.test-quote').textContent.trim() : '',
        words: shown.length ? shown[0].querySelector('.test-quote-text').textContent.trim() : '',
        attribution: shown.length ? shown[0].querySelector('.test-attrib').textContent.trim() : '',
        counter: counter ? counter.textContent.trim() : '',
      };
    });
    const next = async () => { await page.click('#t-next'); await new Promise((r) => setTimeout(r, 700)); };
    const prev = async () => { await page.click('#t-prev'); await new Promise((r) => setTimeout(r, 700)); };

    let seen = await onScreen();
    check('one quotation is on screen at a time', seen.showing === 1, `${seen.showing} showing`);
    check('the words on screen are the ones the owner published', seen.words === published[0].quote, `saw: ${seen.words}`);
    check('with the gold quote marks around them, once', seen.quote === `“${published[0].quote}”`, `saw: ${seen.quote}`);
    check('and the attribution the owner published', seen.attribution === published[0].attribution, `saw: ${seen.attribution}`);
    check('the counter says which of the four is showing', seen.counter === '01 / 04', `saw: ${seen.counter}`);

    await next();
    seen = await onScreen();
    check('the arrow moves to the second quotation, as published', seen.showing === 1 && seen.words === published[1].quote, `saw: ${seen.words}`);
    check('the attribution moves with it', seen.attribution === published[1].attribution, `saw: ${seen.attribution}`);
    check('and the counter follows', seen.counter === '02 / 04', `saw: ${seen.counter}`);

    await next();
    seen = await onScreen();
    check('a quotation left as designed still reads as designed', seen.words.startsWith('Clear language. Gender balance.'), `saw: ${seen.words}`);

    await prev();
    await prev();
    await prev();
    seen = await onScreen();
    check('the arrows go round the four', seen.counter === '04 / 04' && seen.words.startsWith('Redcliffe Advisory connects worlds'), `${seen.counter} — ${seen.words}`);

    check('the page reported no errors', noise.length === 0, noise.slice(0, 3).join('\n        '));
  } finally {
    await browser.close();
    published.forEach((t, i) => {
      wp('theme', 'mod', 'remove', `rad_home_testimonial_${i + 1}_quote`);
      wp('theme', 'mod', 'remove', `rad_home_testimonial_${i + 1}_attribution`);
    });
  }

  const failed = results.filter((ok) => !ok).length;
  console.log(`\n${results.length - failed} passed, ${failed} failed`);
  process.exit(failed ? 1 : 0);
})().catch((e) => { console.error(e); process.exit(1); });
