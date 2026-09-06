#!/usr/bin/env node
/**
 * docs/WEBSITE-GUIDE.md → docs/WEBSITE-GUIDE.pdf
 *
 * The owner's guide as a PDF to email or print. Uses the same Markdown
 * converter the theme uses for its in-dashboard copy, wrapped in print styles,
 * and printed by headless Chrome (google-chrome, chromium or chrome on PATH,
 * or the path in $CHROME).
 *
 *   npm run guide:pdf
 */
'use strict';

const fs = require('fs');
const os = require('os');
const path = require('path');
const { execFileSync } = require('child_process');
const { markdownToHtml } = require('./build-wordpress-theme');

const ROOT = path.resolve(__dirname, '..');
const SOURCE = path.join(ROOT, 'docs', 'WEBSITE-GUIDE.md');
const TARGET = path.join(ROOT, 'docs', 'WEBSITE-GUIDE.pdf');

const STYLE = `
@page { size: A4; margin: 20mm 18mm; }
body { font-family: "Hanken Grotesk", "Segoe UI", Helvetica, Arial, sans-serif; font-size: 11.5pt; line-height: 1.55; color: #1d2327; }
h1 { font-size: 24pt; line-height: 1.2; margin: 0 0 12pt; color: #0F2A4A; }
h2 { font-size: 16pt; margin: 28pt 0 8pt; padding-top: 14pt; border-top: 1px solid #dcdcde; color: #0F2A4A; page-break-after: avoid; }
h3 { font-size: 12.5pt; margin: 16pt 0 6pt; color: #2E6CA6; page-break-after: avoid; }
p, li { margin: 0 0 6pt; }
ul, ol { padding-left: 20pt; margin: 4pt 0 8pt; }
li { margin-bottom: 4pt; }
table { border-collapse: collapse; width: 100%; margin: 8pt 0 14pt; font-size: 10.5pt; }
th, td { border: 1px solid #dcdcde; padding: 5pt 7pt; text-align: left; vertical-align: top; }
th { background: #f6f7f7; }
tr { page-break-inside: avoid; }
code { background: #f0f0f1; padding: 1px 4px; font-size: 10pt; font-family: Menlo, Consolas, monospace; }
hr { border: 0; border-top: 1px solid #dcdcde; margin: 14pt 0; }
blockquote { border-left: 3px solid #2E6CA6; margin: 8pt 0; padding: 2pt 12pt; background: #f6f9fc; }
a { color: #2E6CA6; text-decoration: none; }
`;

function findChrome() {
  const candidates = [process.env.CHROME, 'google-chrome', 'google-chrome-stable', 'chromium', 'chromium-browser', 'chrome'].filter(Boolean);
  for (const name of candidates) {
    try {
      execFileSync(name, ['--version'], { stdio: 'ignore' });
      return name;
    } catch (error) {
      // try the next one
    }
  }
  throw new Error('Chrome or Chromium is needed to print the PDF. Install it, or set CHROME=/path/to/chrome.');
}

function main() {
  const markdown = fs.readFileSync(SOURCE, 'utf8');
  const html =
    `<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">` +
    `<title>Looking after the Redcliffe Advisory website</title><style>${STYLE}</style></head>` +
    `<body>${markdownToHtml(markdown)}</body></html>`;

  const tmp = fs.mkdtempSync(path.join(os.tmpdir(), 'rad-guide-'));
  const page = path.join(tmp, 'guide.html');
  fs.writeFileSync(page, html);

  execFileSync(
    findChrome(),
    ['--headless=new', '--disable-gpu', '--no-sandbox', '--no-pdf-header-footer', `--print-to-pdf=${TARGET}`, `file://${page}`],
    { stdio: 'ignore' }
  );
  fs.rmSync(tmp, { recursive: true, force: true });

  const kb = Math.round(fs.statSync(TARGET).size / 1024);
  console.log(`Wrote ${path.relative(ROOT, TARGET)} (${kb} KB)`);
}

main();
