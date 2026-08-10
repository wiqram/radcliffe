#!/usr/bin/env node
/**
 * Converts the static HTML site into the derived parts of the WordPress theme.
 *
 * Generated (never edit by hand — re-run `npm run build:wp` instead):
 *   header.php, footer.php, front-page.php, template-*.php
 *   inc/content-defaults.php, style.css, assets/js/site.js, images/
 *
 * Hand-written and left untouched by this script:
 *   functions.php, index.php, page.php, single.php, 404.php,
 *   inc/content.php, inc/customizer.php, inc/contact.php, inc/setup.php,
 *   template-parts/*.php
 *
 * The conversion is mechanical and deliberately strict: anything it does not
 * recognise raises an error rather than being silently dropped, so the theme
 * cannot quietly drift away from the design.
 */

const fs = require('fs');
const path = require('path');

const { PAGES, NAV, PANELS } = require('./wordpress-fields');

const ROOT = path.join(__dirname, '..');
const THEME = path.join(ROOT, 'wordpress', 'redcliffe-advisory');

const SLUG_BY_FILE = Object.fromEntries(PAGES.map((page) => [page.file, page.slug]));

const defaults = { text: {}, images: {}, imageAlt: {}, titles: {} };

/* ------------------------------------------------------------------ helpers */

function phpString(value) {
  return `'${String(value).replace(/\\/g, '\\\\').replace(/'/g, "\\'")}'`;
}

function fail(message) {
  throw new Error(message);
}

/** Index just past the close tag matching the element opened before `from`. */
function findMatchingClose(html, tag, from) {
  const token = new RegExp(`<${tag}\\b|</${tag}>`, 'gi');
  token.lastIndex = from;
  let depth = 1;
  let match;
  while ((match = token.exec(html))) {
    depth += match[0][1] === '/' ? -1 : 1;
    if (depth === 0) return { innerEnd: match.index, end: token.lastIndex };
  }
  return fail(`Unbalanced <${tag}> near offset ${from}`);
}

/* ------------------------------------------------------------- transformers */

/** `<img data-cms-image="key" src="images/x" alt="y">` -> Customizer-backed. */
function rewriteCmsImages(html) {
  return html.replace(/<img\b[^>]*\bdata-cms-image="([^"]+)"[^>]*>/g, (tag, key) => {
    const src = /\ssrc="([^"]*)"/.exec(tag);
    const alt = /\salt="([^"]*)"/.exec(tag);
    if (!src) fail(`data-cms-image="${key}" has no src to use as its default`);

    // Stored as a plain value, not as HTML — WordPress escapes it on output.
    defaults.images[key] = src[1].replace(/&amp;/g, '&');
    defaults.imageAlt[key] = alt ? alt[1] : '';

    return tag
      .replace(/\sdata-cms-image="[^"]*"/, '')
      .replace(
        /\ssrc="[^"]*"/,
        ` src="<?php echo esc_url( rad_image_url( '${key}' ) ); ?>"`
      )
      .replace(
        /\salt="[^"]*"/,
        ` alt="<?php echo esc_attr( rad_image_alt( '${key}' ) ); ?>"`
      );
  });
}

/** Theme-relative URLs for the images that are part of the design. */
function rewriteStaticAssets(html) {
  return html.replace(
    /(src|href)="(images\/[^"]+)"/g,
    (_all, attr, file) =>
      `${attr}="<?php echo esc_url( get_theme_file_uri( ${phpString(file)} ) ); ?>"`
  );
}

/** `Contact.html` / `Summit.html#collaborators` -> real WordPress permalinks. */
function rewriteInternalLinks(html) {
  return html.replace(/href="([^"#]+\.html)(#[^"]*)?"/g, (all, file, fragment) => {
    const slug = SLUG_BY_FILE[file];
    if (!slug) return all; // e.g. the legacy Summit capture, which is not imported
    return `href="<?php echo esc_url( rad_url( '${slug}' ) ); ?>${fragment || ''}"`;
  });
}

/** Editable text: the element's current content becomes the Customizer default. */
function rewriteCmsText(html) {
  const opening = /<([a-zA-Z0-9]+)\b([^>]*?)\sdata-cms-key="([^"]+)"([^>]*)>/;
  let out = html;
  let match;

  while ((match = opening.exec(out))) {
    const [openTag, tag, before, key, after] = match;
    const openEnd = match.index + openTag.length;
    const { innerEnd, end } = findMatchingClose(out, tag, openEnd);

    defaults.text[key] = out.slice(openEnd, innerEnd).trim();

    const attrs = `${before}${after}`
      .replace(/\sdata-cms-mode="[^"]*"/g, '')
      .replace(/\s+/g, ' ')
      .trimEnd();

    out =
      out.slice(0, match.index) +
      `<${tag}${attrs}><?php rad_html( '${key}' ); ?></${tag}>` +
      out.slice(end);
  }

  return out;
}

/** Sections gain a show/hide switch, matching the CMS this theme replaces. */
function rewriteCmsSections(html) {
  const opening = /<section\b([^>]*?)\sdata-cms-section="([^"]+)"([^>]*)>/;
  let out = html;
  let match;

  while ((match = opening.exec(out))) {
    const [openTag, before, key, after] = match;
    const openEnd = match.index + openTag.length;
    const { end } = findMatchingClose(out, 'section', openEnd);

    const attrs = `${before}${after}`.replace(/\s+/g, ' ').trimEnd();
    const body = `<section${attrs}>${out.slice(openEnd, end)}`;

    out =
      out.slice(0, match.index) +
      `<?php if ( rad_section_enabled( '${key}' ) ) : ?>\n${body}\n<?php endif; ?>` +
      out.slice(end);
  }

  return out;
}

/** The old CMS could inject extra sections here; WordPress uses pages instead. */
function removeDynamicSectionMount(html) {
  return html.replace(/\s*<div\b[^>]*\bdata-cms-sections\b[^>]*>\s*<\/div>/g, '');
}

/** The contact form becomes a real WordPress form with a nonce and handler. */
function rewriteContactForm(html) {
  const start = html.indexOf('<form class="contact-form"');
  if (start === -1) return html;
  const closing = html.indexOf('</form>', start);
  if (closing === -1) fail('Contact form is not closed');
  return (
    html.slice(0, start) +
    `<?php get_template_part( 'template-parts/contact-form' ); ?>` +
    html.slice(closing + '</form>'.length)
  );
}

function convert(html) {
  let out = html;
  out = rewriteCmsImages(out);
  out = rewriteStaticAssets(out);
  out = rewriteInternalLinks(out);
  out = rewriteContactForm(out);
  out = rewriteCmsText(out);
  out = rewriteCmsSections(out);
  out = removeDynamicSectionMount(out);
  return out;
}

/** Nothing from the old CMS or the flat-file layout may survive conversion. */
function assertClean(label, php) {
  for (const [pattern, description] of [
    [/data-cms-[a-z]+=/, 'a leftover data-cms-* attribute'],
    [/href="[^"]*\.html"/, 'a leftover .html link'],
    [/src="(images|site\.js|cms-client\.js)/, 'a leftover flat-file asset path'],
    [/href="\/admin"/, 'a leftover link to the old admin portal'],
  ]) {
    const found = pattern.exec(php);
    if (found) fail(`${label} still contains ${description}: ${found[0]}`);
  }
}

/* ------------------------------------------------------------------ writing */

function write(relativePath, contents) {
  const target = path.join(THEME, relativePath);
  fs.mkdirSync(path.dirname(target), { recursive: true });
  fs.writeFileSync(target, contents);
  return relativePath;
}

const GENERATED_NOTICE = `<?php
/**
 * GENERATED FILE — do not edit.
 * Produced from the original HTML by scripts/build-wordpress-theme.js.
 * Run \`npm run build:wp\` to regenerate.
 */
?>
`;

function splitPage(html, file) {
  const bodyOpen = /<body[^>]*>/.exec(html);
  if (!bodyOpen) fail(`${file} has no <body>`);

  const chromeStart = html.indexOf('<div class="mobile-bar">');
  const headerEnd = html.indexOf('</header>');
  const footerStart = html.indexOf('<footer class="footer">');
  const footerEnd = html.indexOf('</footer>');
  if (chromeStart === -1 || headerEnd === -1 || footerStart === -1) {
    fail(`${file} does not have the expected page chrome`);
  }

  const title = /<title>([\s\S]*?)<\/title>/.exec(html);

  return {
    title: title ? title[1].trim() : '',
    chrome: html.slice(chromeStart, headerEnd + '</header>'.length),
    main: html.slice(headerEnd + '</header>'.length, footerStart).trim(),
    footer: html.slice(footerStart, footerEnd + '</footer>'.length),
  };
}

function buildHeader(chrome) {
  // The <nav> becomes a real WordPress menu, with this markup as its fallback.
  const navStart = chrome.indexOf('<nav class="nav"');
  const navEnd = chrome.indexOf('</nav>') + '</nav>'.length;
  if (navStart === -1 || navEnd === -1) fail('Could not find the primary <nav>');
  const withMenu =
    chrome.slice(0, navStart) +
    `<?php get_template_part( 'template-parts/navigation' ); ?>` +
    chrome.slice(navEnd);

  return `${GENERATED_NOTICE}<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?> data-page="<?php echo esc_attr( rad_page_slug() ); ?>">
<?php wp_body_open(); ?>
${convert(withMenu)}
`;
}

function buildFooter(footer) {
  return `${GENERATED_NOTICE}${convert(footer)}

<?php wp_footer(); ?>
</body>
</html>
`;
}

function buildTemplate(page, main) {
  const header =
    page.template === 'front-page.php'
      ? `${GENERATED_NOTICE}`
      : `${GENERATED_NOTICE}<?php
/**
 * Template Name: ${page.title}
 */
?>
`;

  return `${header}<?php get_header(); ?>

${main}

<?php get_footer(); ?>
`;
}

function buildDefaults() {
  const entries = (record) =>
    Object.entries(record)
      .map(([key, value]) => `\t\t${phpString(key)} => ${phpString(value)},`)
      .join('\n');

  return `<?php
/**
 * GENERATED FILE — do not edit.
 *
 * The website's own words and pictures, lifted straight out of the original
 * design. These are the values the Customizer starts from, and what it falls
 * back to whenever a field is cleared.
 *
 * Run \`npm run build:wp\` to regenerate.
 */

defined( 'ABSPATH' ) || exit;

return array(
\t'text' => array(
${entries(defaults.text)}
\t),
\t'images' => array(
${entries(defaults.images)}
\t),
\t'image_alt' => array(
${entries(defaults.imageAlt)}
\t),
\t'titles' => array(
${entries(defaults.titles)}
\t),
);
`;
}

/** JavaScript value -> PHP literal, so field metadata is defined in one place. */
function phpValue(value, depth = 1) {
  const pad = '\t'.repeat(depth);
  const closePad = '\t'.repeat(depth - 1);

  if (typeof value === 'boolean') return value ? 'true' : 'false';
  if (typeof value === 'number') return String(value);
  if (typeof value !== 'object' || value === null) return phpString(value);

  const items = Array.isArray(value)
    ? value.map((item) => `${pad}${phpValue(item, depth + 1)},`)
    : Object.entries(value).map(
        ([key, item]) => `${pad}${phpString(key)} => ${phpValue(item, depth + 1)},`
      );

  if (!items.length) return 'array()';

  return `array(\n${items.join('\n')}\n${closePad})`;
}

function buildGeneratedPhp(description, value) {
  return `<?php
/**
 * GENERATED FILE — do not edit.
 *
 * ${description}
 *
 * Run \`npm run build:wp\` to regenerate.
 */

defined( 'ABSPATH' ) || exit;

return ${phpValue(value)};
`;
}

function buildPagesFile() {
  return buildGeneratedPhp(
    'The pages the theme builds on activation, and the primary navigation.',
    {
      pages: PAGES.map(({ slug, title, template }) => ({ slug, title, template })),
      nav: NAV,
    }
  );
}

function buildCustomizerFields() {
  return buildGeneratedPhp(
    'The layout of Appearance > Customize > Redcliffe Advisory.',
    PANELS.map((panel) => ({
      id: panel.id,
      title: panel.title,
      description: panel.description || '',
      text: panel.text,
      images: panel.images,
      toggles: panel.toggles,
      extra: panel.extra || [],
    }))
  );
}

function buildStylesheet() {
  const css = fs.readFileSync(path.join(ROOT, 'styles.css'), 'utf8');
  return `/*
Theme Name: Redcliffe Advisory
Theme URI: https://www.redcliffeadvisory.com
Author: Redcliffe Advisory
Description: The Redcliffe Advisory website — an editorial theme covering the practice, the City Quantum & AI Summit, and the contact form. Page content is edited under Appearance › Customize › Redcliffe Advisory.
Version: 1.0.0
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
License: GNU General Public License v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: redcliffe-advisory
Tags: business, portfolio, one-column, custom-menu, custom-logo, editor-style, full-width-template
*/

${css}`;
}

/** The nav highlight is done in PHP now, so remove the client-side version. */
function buildSiteScript() {
  const js = fs.readFileSync(path.join(ROOT, 'site.js'), 'utf8');
  const from = js.indexOf('  // 4) Active-page nav highlight');
  const to = js.indexOf('  // 5) Testimonial rotator');
  if (from === -1 || to === -1) fail('site.js no longer has the expected section markers');

  const trimmed =
    js.slice(0, from) +
    '  // 4) Active-page nav highlight is applied server-side by the theme.\n\n' +
    js.slice(to);

  new Function(trimmed); // parse check — a broken site.js would break the site
  return trimmed;
}

function copyImages() {
  const source = path.join(ROOT, 'images');
  const target = path.join(THEME, 'images');
  fs.rmSync(target, { recursive: true, force: true });
  fs.cpSync(source, target, { recursive: true });
  return fs.readdirSync(target).length;
}

/* --------------------------------------------------------------------- main */

function main() {
  const written = [];

  const pages = PAGES.map((page) => {
    const html = fs.readFileSync(path.join(ROOT, page.file), 'utf8');
    const parts = splitPage(html, page.file);
    defaults.titles[page.slug] = parts.title;
    return { page, parts };
  });

  // Every page carries identical chrome, so the first one defines it for all.
  const [first] = pages;
  for (const { page, parts } of pages) {
    if (parts.chrome !== first.parts.chrome) fail(`${page.file} has a different header`);
    if (parts.footer !== first.parts.footer) fail(`${page.file} has a different footer`);
  }

  for (const { page, parts } of pages) {
    const template = buildTemplate(page, convert(parts.main));
    assertClean(page.template, template);
    written.push(write(page.template, template));
  }

  const header = buildHeader(first.parts.chrome);
  const footer = buildFooter(first.parts.footer);
  assertClean('header.php', header);
  assertClean('footer.php', footer);
  written.push(write('header.php', header));
  written.push(write('footer.php', footer));

  written.push(write('inc/content-defaults.php', buildDefaults()));
  written.push(write('inc/pages.php', buildPagesFile()));
  written.push(write('inc/customizer-fields.php', buildCustomizerFields()));
  written.push(write('style.css', buildStylesheet()));
  written.push(write('assets/js/site.js', buildSiteScript()));

  const imageCount = copyImages();

  console.log(`WordPress theme written to wordpress/redcliffe-advisory/`);
  for (const file of written) console.log(`  · ${file}`);
  console.log(`  · images/ (${imageCount} files)`);
  console.log(
    `\n${Object.keys(defaults.text).length} text fields, ` +
      `${Object.keys(defaults.images).length} images, ` +
      `${pages.length} page templates.`
  );
}

main();
