#!/usr/bin/env node
/**
 * Converts the static HTML site into the derived parts of the WordPress theme.
 *
 * Generated (never edit by hand — re-run `npm run build:wp` instead):
 *   header.php, footer.php, front-page.php, template-*.php
 *   inc/content-defaults.php, inc/pages.php, inc/customizer-fields.php,
 *   inc/guide-content.php, style.css, assets/js/site.js, images/
 *
 * Hand-written and left untouched by this script:
 *   functions.php, index.php, page.php, single.php, 404.php,
 *   inc/content.php, inc/customizer.php, inc/contact.php, inc/setup.php,
 *   inc/blocks.php, inc/guide.php, assets/css/blocks.css, template-parts/*.php
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

/**
 * The old CMS injected extra sections here. In WordPress the same slot shows
 * whatever the owner adds to the page in the block editor (see inc/blocks.php),
 * so every page can grow new sections without touching the design.
 */
function rewriteDynamicSectionMount(html, label) {
  const mount = /\s*<div\b[^>]*\bdata-cms-sections\b[^>]*>\s*<\/div>/g;
  const found = html.match(mount) || [];
  if (found.length !== 1) fail(`${label} should have exactly one data-cms-sections mount, found ${found.length}`);
  return html.replace(mount, '\n\n<?php rad_extra_sections(); ?>\n');
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

function convert(html, label = '') {
  let out = html;
  out = rewriteCmsImages(out);
  out = rewriteStaticAssets(out);
  out = rewriteInternalLinks(out);
  out = rewriteContactForm(out);
  out = rewriteCmsText(out);
  out = rewriteCmsSections(out);
  if (label) out = rewriteDynamicSectionMount(out, label);
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
Description: The Redcliffe Advisory website — an editorial theme covering the practice, the City Quantum & AI Summit, and the contact form. The words and photographs of the designed pages are edited under Appearance › Customize › Redcliffe Advisory; new sections and pictures are added to any page with the page editor.
Version: 1.1.0
Requires at least: 6.0
Tested up to: 7.1
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

/* ------------------------------------------------------------ owner's guide */

/**
 * A small Markdown-to-HTML converter, enough for docs/WEBSITE-GUIDE.md:
 * headings, paragraphs, lists, tables, block quotes, rules, bold, italic,
 * inline code and links. Anything the guide starts using beyond that should be
 * added here rather than worked around in the guide.
 */
function inlineMarkdown(text) {
  return text
    .replace(/&(?!(amp|lt|gt|quot|#\d+);)/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/`([^`]+)`/g, (_m, code) => `<code>${code}</code>`)
    .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
    .replace(/(^|[^*\w])\*([^*\n]+)\*(?!\w)/g, '$1<em>$2</em>')
    .replace(/\[([^\]]+)\]\(([^)\s]+)\)/g, '<a href="$2">$1</a>')
    .replace(/(^|[\s(])(https?:\/\/[^\s)<]+)/g, '$1<a href="$2">$2</a>');
}

function slugify(text) {
  return text
    .toLowerCase()
    .replace(/<[^>]+>/g, '')
    .replace(/[^a-z0-9\s-]/g, '')
    .trim()
    .replace(/\s+/g, '-');
}

function markdownToHtml(markdown) {
  const lines = markdown.replace(/\r/g, '').split('\n');
  const out = [];
  let i = 0;

  const isTableRow = (line) => /^\s*\|.*\|\s*$/.test(line);
  const isSeparator = (line) => /^\s*\|?\s*:?-{3,}/.test(line);

  while (i < lines.length) {
    const line = lines[i];

    if (!line.trim()) { i += 1; continue; }

    if (/^---+\s*$/.test(line)) { out.push('<hr />'); i += 1; continue; }

    const heading = /^(#{1,6})\s+(.*)$/.exec(line);
    if (heading) {
      const level = heading[1].length;
      const text = inlineMarkdown(heading[2].trim());
      out.push(`<h${level} id="${slugify(heading[2])}">${text}</h${level}>`);
      i += 1;
      continue;
    }

    if (isTableRow(line) && i + 1 < lines.length && isSeparator(lines[i + 1])) {
      const cells = (row) => row.trim().replace(/^\||\|$/g, '').split('|').map((c) => inlineMarkdown(c.trim()));
      const head = cells(line);
      i += 2;
      const rows = [];
      while (i < lines.length && isTableRow(lines[i])) { rows.push(cells(lines[i])); i += 1; }
      out.push('<table>');
      out.push(`<thead><tr>${head.map((c) => `<th>${c}</th>`).join('')}</tr></thead>`);
      out.push(`<tbody>${rows.map((r) => `<tr>${r.map((c) => `<td>${c}</td>`).join('')}</tr>`).join('')}</tbody>`);
      out.push('</table>');
      continue;
    }

    if (/^\s*>/.test(line)) {
      const quote = [];
      while (i < lines.length && /^\s*>/.test(lines[i])) { quote.push(lines[i].replace(/^\s*>\s?/, '')); i += 1; }
      out.push(`<blockquote>${markdownToHtml(quote.join('\n'))}</blockquote>`);
      continue;
    }

    const listItem = /^(\s*)([-*]|\d+\.)\s+(.*)$/.exec(line);
    if (listItem) {
      const indent = listItem[1].length;
      const ordered = /\d/.test(listItem[2]);
      const items = [];
      while (i < lines.length) {
        const m = /^(\s*)([-*]|\d+\.)\s+(.*)$/.exec(lines[i]);
        if (m && m[1].length === indent && /\d/.test(m[2]) === ordered) {
          items.push([m[3]]);
          i += 1;
          // Continuation lines and nested blocks belong to this item.
          while (i < lines.length && lines[i].trim() && !(/^(\s*)([-*]|\d+\.)\s+/.exec(lines[i]) && /^(\s*)/.exec(lines[i])[1].length <= indent)) {
            items[items.length - 1].push(lines[i]);
            i += 1;
          }
          // A blank line followed by an indented block is still part of the item.
          while (i + 1 < lines.length && !lines[i].trim() && /^\s{2,}/.test(lines[i + 1]) && !(/^(\s*)([-*]|\d+\.)\s+/.exec(lines[i + 1]) && /^(\s*)/.exec(lines[i + 1])[1].length <= indent)) {
            items[items.length - 1].push('');
            i += 1;
            while (i < lines.length && lines[i].trim() && /^\s{2,}/.test(lines[i])) { items[items.length - 1].push(lines[i]); i += 1; }
          }
          // A blank line between items keeps the same list going.
          let next = i;
          while (next < lines.length && !lines[next].trim()) next += 1;
          const following = next < lines.length && /^(\s*)([-*]|\d+\.)\s+/.exec(lines[next]);
          if (following && following[1].length === indent && /\d/.test(following[2]) === ordered) i = next;
        } else {
          break;
        }
      }
      const tag = ordered ? 'ol' : 'ul';
      const rendered = items.map(([first, ...rest]) => {
        const nested = rest.map((l) => l.replace(new RegExp(`^\\s{0,${indent + 3}}`), '')).join('\n');
        const inner = nested.trim() ? markdownToHtml(`${first}\n${nested}`).replace(/^<p>([\s\S]*?)<\/p>/, '$1') : inlineMarkdown(first);
        return `<li>${inner}</li>`;
      });
      out.push(`<${tag}>${rendered.join('')}</${tag}>`);
      continue;
    }

    // Paragraph: consecutive non-blank, non-special lines.
    const para = [];
    while (i < lines.length && lines[i].trim() && !/^(#{1,6}\s|---+\s*$|\s*>|\s*([-*]|\d+\.)\s+)/.test(lines[i]) && !(isTableRow(lines[i]) && isSeparator(lines[i + 1] || ''))) {
      para.push(lines[i].trim());
      i += 1;
    }
    out.push(`<p>${inlineMarkdown(para.join(' '))}</p>`);
  }

  return out.join('\n');
}

function buildGuide() {
  const markdown = fs.readFileSync(path.join(ROOT, 'docs', 'WEBSITE-GUIDE.md'), 'utf8');
  const html = markdownToHtml(markdown);
  return `<?php
/**
 * GENERATED FILE — do not edit.
 *
 * The owner's guide, converted from docs/WEBSITE-GUIDE.md. Shown under
 * "Website guide" in the WordPress admin menu (see inc/guide.php).
 *
 * Run \`npm run build:wp\` to regenerate.
 */

defined( 'ABSPATH' ) || exit;

return ${phpString(html)};
`;
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
    const template = buildTemplate(page, convert(parts.main, page.template));
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
  written.push(write('inc/guide-content.php', buildGuide()));
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
