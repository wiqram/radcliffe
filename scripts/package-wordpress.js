#!/usr/bin/env node
/**
 * Packages the WordPress theme for handover.
 *
 * Produces, in dist-wordpress/:
 *   redcliffe-advisory.zip                    the theme on its own, ready to
 *                                             upload in Appearance > Themes
 *   redcliffe-advisory-wordpress.zip          the full handover pack
 *   redcliffe-advisory-wordpress.tar.gz       the same, for anyone who prefers tar
 *
 * The pack carries two guides: START-HERE.md (installing the theme, from the
 * top of README.md) and WEBSITE-GUIDE.md (looking after the site afterwards,
 * from docs/), so there is only ever one copy of each to keep up to date.
 *
 * Run `npm run package:wp` (which regenerates the theme first).
 */

const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');

const ROOT = path.join(__dirname, '..');
const THEME_DIR = path.join(ROOT, 'wordpress');
const THEME_NAME = 'redcliffe-advisory';
const OUT = path.join(ROOT, 'dist-wordpress');
const STAGING = path.join(OUT, 'handover');

const GUIDE_START = '<!-- wp-guide:start -->';
const GUIDE_END = '<!-- wp-guide:end -->';

function run(command, args, cwd) {
  execFileSync(command, args, { cwd, stdio: 'pipe' });
}

/** The deployment guide, taken from the top of README.md. */
function extractGuide() {
  const readme = fs.readFileSync(path.join(ROOT, 'README.md'), 'utf8');
  const start = readme.indexOf(GUIDE_START);
  const end = readme.indexOf(GUIDE_END);

  if (start === -1 || end === -1) {
    throw new Error(`README.md is missing the ${GUIDE_START} / ${GUIDE_END} markers`);
  }

  return readme.slice(start + GUIDE_START.length, end).trim();
}

function bytes(file) {
  return `${(fs.statSync(file).size / 1024 / 1024).toFixed(2)} MB`;
}

function main() {
  const themePath = path.join(THEME_DIR, THEME_NAME);

  for (const required of ['style.css', 'functions.php', 'index.php', 'header.php', 'footer.php']) {
    if (!fs.existsSync(path.join(themePath, required))) {
      throw new Error(`The theme is missing ${required} — run \`npm run build:wp\` first`);
    }
  }

  fs.rmSync(OUT, { recursive: true, force: true });
  fs.mkdirSync(STAGING, { recursive: true });

  // 1. The theme zip. WordPress expects exactly one folder at the top level.
  const themeZip = path.join(OUT, `${THEME_NAME}.zip`);
  run('zip', ['-r', '-q', '-X', themeZip, THEME_NAME, '-x', '*.DS_Store'], THEME_DIR);

  const listing = execFileSync('unzip', ['-Z1', themeZip]).toString().split('\n');
  for (const required of [`${THEME_NAME}/style.css`, `${THEME_NAME}/functions.php`]) {
    if (!listing.includes(required)) {
      throw new Error(`${required} is missing from the theme zip`);
    }
  }

  // 2. The handover pack.
  fs.mkdirSync(path.join(STAGING, '1-theme-to-upload'), { recursive: true });
  fs.copyFileSync(themeZip, path.join(STAGING, '1-theme-to-upload', `${THEME_NAME}.zip`));

  fs.mkdirSync(path.join(STAGING, '2-theme-files'), { recursive: true });
  fs.cpSync(themePath, path.join(STAGING, '2-theme-files', THEME_NAME), { recursive: true });

  fs.writeFileSync(path.join(STAGING, 'START-HERE.md'), `${extractGuide()}\n`);
  fs.copyFileSync(path.join(ROOT, 'docs', 'WEBSITE-GUIDE.md'), path.join(STAGING, 'WEBSITE-GUIDE.md'));
  const guidePdf = path.join(ROOT, 'docs', 'WEBSITE-GUIDE.pdf');
  if (fs.existsSync(guidePdf)) fs.copyFileSync(guidePdf, path.join(STAGING, 'WEBSITE-GUIDE.pdf'));

  const packZip = path.join(OUT, `${THEME_NAME}-wordpress.zip`);
  const packTar = path.join(OUT, `${THEME_NAME}-wordpress.tar.gz`);

  run('zip', ['-r', '-q', '-X', packZip, '.', '-x', '*.DS_Store'], STAGING);
  run('tar', ['-czf', packTar, '-C', STAGING, '.'], ROOT);

  fs.rmSync(STAGING, { recursive: true, force: true });

  console.log('Handover package built in dist-wordpress/\n');
  console.log(`  ${THEME_NAME}.zip                 ${bytes(themeZip)}   the theme alone`);
  console.log(`  ${THEME_NAME}-wordpress.zip       ${bytes(packZip)}   give this to the deployment team`);
  console.log(`  ${THEME_NAME}-wordpress.tar.gz    ${bytes(packTar)}   the same, as a tarball`);
  console.log('\nThe pack contains:');
  console.log('  START-HERE.md              how to install the theme');
  console.log('  WEBSITE-GUIDE.md           how to look after the site afterwards');
  console.log('  1-theme-to-upload/         the zip to upload in WordPress');
  console.log('  2-theme-files/             the same theme unzipped, for FTP installs');
}

main();
