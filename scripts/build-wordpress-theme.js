#!/usr/bin/env node
/**
 * Converts the static HTML site into the derived parts of the WordPress theme.
 *
 * Generated (never edit by hand — re-run `npm run build:wp` instead):
 *   header.php, footer.php, front-page.php, template-*.php
 *   inc/content-defaults.php, inc/pages.php, inc/customizer-fields.php,
 *   inc/guide-content.php, inc/content-seeds.php, style.css, assets/js/site.js,
 *   images/
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

const { PAGES, NAV, PANELS, REGISTER_URL } = require('./wordpress-fields');

const ROOT = path.join(__dirname, '..');
const THEME = path.join(ROOT, 'wordpress', 'redcliffe-advisory');

const SLUG_BY_FILE = Object.fromEntries(PAGES.map((page) => [page.file, page.slug]));

const defaults = { text: {}, images: {}, imageAlt: {}, titles: {}, links: { register: REGISTER_URL } };

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

/* ------------------------------------------------------------ auto-tagging */

/**
 * Everything on the site is editable. The design tags a handful of elements by
 * hand (data-cms-key / data-cms-image); this pre-pass tags the rest, so every
 * piece of text and every photograph becomes a Customizer field without the
 * HTML having to be annotated by hand.
 *
 * Rules:
 *   - The innermost element that carries text directly becomes a field, with
 *     its inline formatting (<em>, <span>, <br />) kept. If an element's own
 *     text is only whitespace, its children are considered instead.
 *   - Fewer than three letters or digits ("02", "→") is decoration, not text.
 *   - Scripts, forms, SVG, the primary nav and anything already tagged are
 *     left alone, as is the section a page edits as blocks.
 *   - Keys are <group>.<section>.<hash of the text>, so they survive the HTML
 *     being reordered; if the wording in the HTML changes, the default changes
 *     with it and a fresh key is right.
 */
const VOID_TAGS = new Set(['img', 'br', 'hr', 'input', 'meta', 'link', 'source', 'wbr', 'area', 'col', 'embed', 'track', 'param']);
const SKIP_TAGS = new Set(['script', 'style', 'svg', 'form', 'nav', 'noscript', 'template', 'select', 'textarea', 'button']);
const BLOCK_TAGS = new Set(['div', 'p', 'section', 'article', 'aside', 'header', 'footer', 'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'figure', 'figcaption', 'table', 'thead', 'tbody', 'tr', 'td', 'th', 'blockquote', 'form', 'nav', 'main', 'dl', 'dt', 'dd']);

const autoFields = {}; // group -> [{ key, label, kind: 'text' | 'image' }]

function hashText(text) {
  let h = 5381;
  for (let i = 0; i < text.length; i += 1) h = ((h * 33) ^ text.charCodeAt(i)) >>> 0;
  return h.toString(36).padStart(7, '0').slice(-7);
}

function plainText(html) {
  return html
    .replace(/<[^>]+>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/&amp;/g, '&')
    .replace(/&rsquo;/g, '’')
    .replace(/&ndash;/g, '–')
    .replace(/&mdash;/g, '—')
    .replace(/&times;/g, '×')
    .replace(/&copy;/g, '©')
    .replace(/&#(\d+);/g, (_all, code) => String.fromCodePoint(Number(code)))
    .replace(/&#x([0-9a-f]+);/gi, (_all, code) => String.fromCodePoint(parseInt(code, 16)))
    .replace(/\s+/g, ' ')
    .trim();
}

function labelFor(text) {
  return text.length > 60 ? `${text.slice(0, 57).trimEnd()}…` : text;
}

/** Top-level child elements of an HTML fragment. */
function childElements(html) {
  const out = [];
  const open = /<([a-zA-Z][a-zA-Z0-9]*)\b[^>]*>|<!--[\s\S]*?-->/g;
  let match;
  while ((match = open.exec(html))) {
    if (match[0].startsWith('<!--')) continue;
    const tag = match[1].toLowerCase();
    const start = match.index;
    const openEnd = start + match[0].length;
    if (VOID_TAGS.has(tag) || match[0].endsWith('/>')) {
      out.push({ tag, start, openEnd, innerEnd: openEnd, end: openEnd, openTag: match[0] });
      open.lastIndex = openEnd;
      continue;
    }
    const { innerEnd, end } = findMatchingClose(html, tag, openEnd);
    out.push({ tag, start, openEnd, innerEnd, end, openTag: match[0] });
    open.lastIndex = end;
  }
  return out;
}

function addAutoField(group, section, kind, seed, label) {
  const key = `${group}.${section}.${hashText(`${kind}:${seed}`)}`;
  autoFields[group] = autoFields[group] || [];
  if (!autoFields[group].some((f) => f.key === key)) autoFields[group].push({ key, label, kind });
  return key;
}

/** Insert an attribute into an opening tag. */
function withAttribute(openTag, attribute) {
  return openTag.endsWith('/>') ? `${openTag.slice(0, -2).trimEnd()} ${attribute} />` : `${openTag.slice(0, -1)} ${attribute}>`;
}

function autoTag(html, group, section, skipSection) {
  const children = childElements(html);
  if (!children.length) return html;

  let out = '';
  let cursor = 0;

  for (const child of children) {
    out += html.slice(cursor, child.start);
    cursor = child.end;

    const { tag, openTag } = child;
    const inner = html.slice(child.openEnd, child.innerEnd);
    const closing = html.slice(child.innerEnd, child.end);

    if (tag === 'img') {
      if (/\sdata-cms-image=/.test(openTag)) {
        out += openTag;
      } else {
        const alt = /\salt="([^"]*)"/.exec(openTag);
        const src = /\ssrc="([^"]*)"/.exec(openTag);
        const label = alt && alt[1] ? labelFor(plainText(alt[1])) : `Image (${src ? src[1].split('/').pop() : 'unknown'})`;
        const key = addAutoField(group, section, 'image', src ? src[1] : openTag, label);
        out += withAttribute(openTag, `data-cms-image="${key}"`);
      }
      continue;
    }

    if (VOID_TAGS.has(tag) || SKIP_TAGS.has(tag) || /\sdata-cms-key=/.test(openTag)) {
      out += html.slice(child.start, child.end);
      continue;
    }

    const sectionAttr = /\sdata-cms-section="([^"]+)"/.exec(openTag);
    if (sectionAttr) {
      const key = sectionAttr[1];
      if (key === skipSection) {
        out += html.slice(child.start, child.end);
      } else {
        out += openTag + autoTag(inner, group, key.split('.').slice(1).join('_') || key, skipSection) + closing;
      }
      continue;
    }

    const grandchildren = childElements(inner);
    let direct = inner;
    for (let i = grandchildren.length - 1; i >= 0; i -= 1) {
      direct = direct.slice(0, grandchildren[i].start) + ' ' + direct.slice(grandchildren[i].end);
    }
    const directText = plainText(direct);
    const hasBlockChild = grandchildren.some((g) => BLOCK_TAGS.has(g.tag) || SKIP_TAGS.has(g.tag) || g.tag === 'img');
    const alnum = (directText.match(/[\p{L}\p{N}]/gu) || []).length;

    if (alnum >= 3 && !hasBlockChild && !grandchildren.some((g) => /\sdata-cms-/.test(g.openTag))) {
      const key = addAutoField(group, section, 'text', inner.trim(), labelFor(plainText(inner)));
      out += withAttribute(openTag, `data-cms-key="${key}"`) + inner + closing;
    } else {
      out += openTag + autoTag(inner, group, section, skipSection) + closing;
    }
  }

  return out + html.slice(cursor);
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
      .replace(/\sdata-cms-image="[^"]*"/, ` data-rad-img="${key}"`)
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
  // Attributes may already hold PHP (a rewritten href), hence the alternation.
  const attr = String.raw`(?:[^>]|<\?php[\s\S]*?\?>)`;
  const opening = new RegExp(`<([a-zA-Z0-9]+)\\b(${attr}*?)\\sdata-cms-key="([^"]+)"(${attr}*)>`);
  let out = html;
  let match;

  while ((match = opening.exec(out))) {
    const [openTag, tag, before, key, after] = match;
    const openEnd = match.index + openTag.length;
    const { innerEnd, end } = findMatchingClose(out, tag, openEnd);

    // A link inside editable text keeps a {{url:slug}} token, which the theme
    // turns into the page's real address when it prints the text.
    defaults.text[key] = out
      .slice(openEnd, innerEnd)
      .trim()
      .replace(/<\?php echo esc_url\( rad_url\( '([a-z-]+)' \) \); \?>/g, '{{url:$1}}');

    const attrs = `${before}${after}`
      .replace(/\sdata-cms-mode="[^"]*"/g, '')
      .replace(/\s+/g, ' ')
      .trimEnd();

    out =
      out.slice(0, match.index) +
      `<${tag}${attrs} data-rad="${key}"><?php rad_html( '${key}' ); ?></${tag}>` +
      out.slice(end);
  }

  return out;
}

/** Sections gain a show/hide switch, matching the CMS this theme replaces. */
function rewriteCmsSections(html, blockSection = '', postSection = '', sponsorSection = '') {
  const opening = /<section\b([^>]*?)\sdata-cms-section="([^"]+)"([^>]*)>/;
  let out = html;
  let match;

  while ((match = opening.exec(out))) {
    const [openTag, before, key, after] = match;
    const openEnd = match.index + openTag.length;
    const { end } = findMatchingClose(out, 'section', openEnd);

    const attrs = `${before}${after}`.replace(/\s+/g, ' ').trimEnd();
    let body = `<section${attrs}>${out.slice(openEnd, end)}`;

    // The page's own block content stands in for this section once it has
    // any, so the owner edits the section in the page editor.
    if (key === blockSection) {
      // The buttons closing the Agenda are theme settings, not page content, so
      // they look the same whether the programme is blocks or the design.
      const actions = key === 'agenda.programme' ? `<?php rad_agenda_actions(); ?>\n` : '';
      body =
        `<?php if ( rad_page_has_content() ) : ?>\n` +
        `<section class="section rad-page-blocks">\n<div class="container">\n` +
        `<div class="rad-blocks entry-content rad-${key.split('.')[0]}">\n<?php rad_page_content(); ?>\n</div>\n${actions}</div>\n</section>\n` +
        `<?php else : ?>\n${body}\n<?php endif; ?>`;
    }

    // Sponsors added under Sponsors in the admin menu replace the placeholder
    // names in this section (its heading stays editable in the Customizer).
    if (key === sponsorSection) {
      const gridStart = body.indexOf('<div class="partner-grid');
      const containerEnd = body.lastIndexOf('</div>', body.lastIndexOf('</section>'));
      if (gridStart === -1 || containerEnd === -1 || gridStart > containerEnd) fail(`${key} has no .partner-grid to replace with sponsors`);
      body =
        body.slice(0, gridStart) +
        `<?php if ( rad_has_sponsors() ) : ?>\n<?php rad_render_sponsors(); ?>\n<?php else : ?>\n` +
        body.slice(gridStart, containerEnd) +
        `<?php endif; ?>\n` +
        body.slice(containerEnd);
    }

    // Real WordPress Posts stand in for this section once there are any, so
    // the owner's "Add Post" workflow drives the page instead of fixed text.
    if (key === postSection) {
      body =
        `<?php if ( rad_has_articles() ) : ?>\n` +
        `<section class="section rad-articles-dynamic">\n<div class="container">\n` +
        `<?php get_template_part( 'template-parts/journal' ); ?>\n</div>\n</section>\n` +
        `<?php else : ?>\n${body}\n<?php endif; ?>`;
    }

    out =
      out.slice(0, match.index) +
      `<?php if ( rad_section_enabled( '${key}' ) ) : ?>\n${body}\n<?php endif; ?>` +
      out.slice(end);
  }

  return out;
}

/**
 * A "Register" button beside "See the 2026 agenda" in the Summit hero. Its
 * words are an ordinary editable field (with the pencil in the Customizer
 * preview) and its address is a Customizer setting; clearing the address hides
 * the button. The same address is what the Agenda's closing button uses.
 *
 * Runs on the *converted* template (after auto-tagging), not the raw design
 * HTML: its href carries PHP of its own, and auto-tagging's tag scanner does
 * not expect a `>` inside an attribute (the one in `?>`), which corrupts the
 * markup if this button is present when auto-tagging runs.
 */
function injectSummitRegisterButton(html) {
  const ctaOpen = '<div class="cine-cta">';
  const start = html.indexOf(ctaOpen);
  if (start === -1) fail('template-summit.php has no .cine-cta to add the Register button to');
  const openEnd = start + ctaOpen.length;
  const { innerEnd } = findMatchingClose(html, 'div', openEnd);
  defaults.text['summit.hero.registerLabel'] = 'Register';
  const button =
    `\n<?php $rad_register_url = rad_registration_url(); if ( $rad_register_url ) : ?>\n` +
    `<a class="btn on-dark-ghost" href="<?php echo esc_url( $rad_register_url ); ?>" target="_blank" rel="noopener"><span data-rad="summit.hero.registerLabel"><?php rad_html( 'summit.hero.registerLabel' ); ?></span><span class="arr">→</span></a>\n` +
    `<?php endif; ?>\n`;

  return html.slice(0, innerEnd) + button + html.slice(innerEnd);
}

/**
 * Defaults for the settings behind the closing buttons of the Agenda and the
 * LinkedIn feed on the homepage (their markup lives in PHP; see
 * inc/buttons.php and inc/linkedin.php).
 */
function registerHandWrittenDefaults() {
  defaults.text['agenda.actions.primaryLabel'] = 'Reserve your spot';
  defaults.text['agenda.actions.secondaryLabel'] = 'Back to the Summit';
  defaults.text['home.linkedin.followLabel'] = 'Follow Karina on LinkedIn';
  defaults.text['home.linkedin.moreLabel'] = 'Show more posts';
}

/** Marks where a hand-written PHP function takes over from the design. */
function injectMarker(html, marker, php, label) {
  const token = `<!--${marker}-->`;
  if (!html.includes(token)) fail(`${label} lost its ${marker} marker`);
  return html.replace(token, php);
}

/**
 * The old CMS injected extra sections here. In WordPress the same slot shows
 * whatever the owner adds to the page in the block editor (see inc/blocks.php),
 * so every page can grow new sections without touching the design.
 */
function rewriteDynamicSectionMount(html, label, blockSection = '') {
  const mount = /\s*<div\b[^>]*\bdata-cms-sections\b[^>]*>\s*<\/div>/g;
  const found = html.match(mount) || [];
  if (found.length !== 1) fail(`${label} should have exactly one data-cms-sections mount, found ${found.length}`);
  // A page whose content replaces a designed section shows it there, not here.
  return html.replace(mount, blockSection ? '\n' : '\n\n<?php rad_extra_sections(); ?>\n');
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

function convert(html, label = '', blockSection = '', group = '', postSection = '', sponsorSection = '') {
  let out = html;
  if (group) out = autoTag(out, group, 'top', blockSection || postSection);
  out = rewriteCmsImages(out);
  out = rewriteStaticAssets(out);
  out = rewriteInternalLinks(out);
  out = rewriteContactForm(out);
  out = rewriteCmsText(out);
  out = rewriteCmsSections(out, blockSection, postSection, sponsorSection);
  if (label) out = rewriteDynamicSectionMount(out, label, blockSection);
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
  // The announcement strip gets a show/hide switch like any page section.
  const announceStart = chrome.indexOf('<a class="announce"');
  let announceEnd = chrome.indexOf('</a>', announceStart) + '</a>'.length;
  if (announceStart === -1) fail('Could not find the announcement strip');
  // Its link can be pointed anywhere from the Customizer; the Agenda is the default.
  const announceTag = chrome.slice(announceStart, chrome.indexOf('>', announceStart) + 1);
  if (!/href="Agenda\.html"/.test(announceTag)) fail('The announcement strip should link to Agenda.html');
  chrome = // eslint-disable-line no-param-reassign
    chrome.slice(0, announceStart) +
    announceTag.replace(/href="Agenda\.html"/, `href="<?php echo esc_url( rad_setting_url( 'global.announcement.url', rad_url( 'agenda' ) ) ); ?>"`) +
    chrome.slice(announceStart + announceTag.length);
  announceEnd = chrome.indexOf('</a>', announceStart) + '</a>'.length; // eslint-disable-line no-param-reassign
  chrome = // eslint-disable-line no-param-reassign
    chrome.slice(0, announceStart) +
    `<?php if ( rad_section_enabled( 'global.announcement' ) ) : ?>\n` +
    chrome.slice(announceStart, announceEnd) +
    `\n<?php endif; ?>` +
    chrome.slice(announceEnd);

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
${convert(withMenu, '', '', 'global')}
`;
}

function buildFooter(footer) {
  return `${GENERATED_NOTICE}${convert(footer, '', '', 'footer')}

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
\t'links' => array(
${entries(defaults.links)}
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
  const panels = PANELS.map((panel) => {
    const auto = autoFields[panel.id] || [];
    return {
      id: panel.id,
      title: panel.title,
      description: panel.description || '',
      // Hand-picked fields first, then everything else on the page in page
      // order. The third element marks an automatically found field.
      text: [...panel.text, ...auto.filter((f) => f.kind === 'text').map((f) => [f.key, f.label, true])],
      images: [...panel.images, ...auto.filter((f) => f.kind === 'image').map((f) => [f.key, f.label, true])],
      toggles: panel.toggles,
      extra: panel.extra || [],
    };
  });

  const unplaced = Object.keys(autoFields).filter((group) => !PANELS.some((panel) => panel.id === group));
  if (unplaced.length) fail(`Auto-tagged fields have no Customizer section: ${unplaced.join(', ')}`);

  return buildGeneratedPhp('The layout of Appearance > Customize > Redcliffe Advisory.', panels);
}

function buildStylesheet() {
  const css = fs.readFileSync(path.join(ROOT, 'styles.css'), 'utf8');
  return `/*
Theme Name: Redcliffe Advisory
Theme URI: https://www.redcliffeadvisory.com
Author: Redcliffe Advisory
Description: The Redcliffe Advisory website — an editorial theme covering the practice, the City Quantum & AI Summit, and the contact form. The words and photographs of the designed pages are edited under Appearance › Customize › Redcliffe Advisory; new sections and pictures are added to any page with the page editor.
Version: 1.4.5
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

/* ------------------------------------------------------------- block seeds */

/**
 * Block markup for a section the owner edits in the page editor. Built from
 * the HTML so the editor opens with the real programme, not a blank page.
 *
 * Only the agenda programme is converted today. The markup uses core blocks
 * only (paragraphs, headings, columns, buttons) carrying the theme's class
 * names, so it needs no custom blocks and survives WordPress updates.
 */
function blockText(html) {
  // Keep the inline <em> the design uses; everything else becomes plain text.
  return html
    .replace(/<(?!\/?em\b)[^>]+>/g, '')
    .replace(/\s+/g, ' ')
    .trim();
}

function paragraph(className, text) {
  return `<!-- wp:paragraph {"className":"${className}"} -->\n<p class="${className}">${text}</p>\n<!-- /wp:paragraph -->`;
}

function heading(level, className, text) {
  return `<!-- wp:heading {"level":${level},"className":"${className}"} -->\n<h${level} class="wp-block-heading ${className}">${text}</h${level}>\n<!-- /wp:heading -->`;
}

function column(inner, width = '') {
  if (!width) return `<!-- wp:column -->\n<div class="wp-block-column">${inner}</div>\n<!-- /wp:column -->`;
  return `<!-- wp:column {"width":"${width}"} -->\n<div class="wp-block-column" style="flex-basis:${width}">${inner}</div>\n<!-- /wp:column -->`;
}

function columns(className, cols) {
  return `<!-- wp:columns {"className":"${className}"} -->\n<div class="wp-block-columns ${className}">${cols.join('\n\n')}</div>\n<!-- /wp:columns -->`;
}

function agendaRow(time, title, desc, feature) {
  const className = feature ? 'rad-agenda-row is-feature' : 'rad-agenda-row';
  const right = [paragraph('rad-agenda-title', title)];
  if (desc) right.push(paragraph('rad-agenda-desc', desc));
  return columns(className, [column(paragraph('rad-agenda-time', time), '132px'), column(right.join('\n\n'))]);
}

function buildAgendaSeed(sectionHtml) {
  const blocks = [];

  // Date / venue / theme
  const facts = [...sectionHtml.matchAll(/<div class="ai"><div class="k">([\s\S]*?)<\/div><div class="v">([\s\S]*?)<\/div><\/div>/g)];
  if (facts.length !== 3) fail(`Agenda intro should have three facts, found ${facts.length}`);
  blocks.push(
    columns(
      'rad-agenda-intro',
      facts.map(([, k, v]) => column(`${paragraph('rad-label', blockText(k))}\n\n${paragraph('rad-agenda-value', blockText(v))}`))
    )
  );

  // Parts of the day
  const parts = [...sectionHtml.matchAll(/<div class="agenda-block reveal">([\s\S]*?)<\/div>\s*(?=<div class="agenda-block reveal">|<div class="agenda-note)/g)];
  if (!parts.length) fail('Agenda has no programme blocks');
  for (const [, part] of parts) {
    const name = /<span class="ph">([\s\S]*?)<\/span>/.exec(part);
    if (!name) fail('Agenda block has no heading');
    blocks.push(heading(3, 'rad-agenda-part', blockText(name[1])));

    const rows = [...part.matchAll(/<div class="agenda-row( feature)?">\s*<div class="at">([\s\S]*?)<\/div>\s*<div><div class="as-title">([\s\S]*?)<\/div>(?:<div class="as-desc">([\s\S]*?)<\/div>)?<\/div>\s*<\/div>/g)];
    if (!rows.length) fail(`Agenda block "${blockText(name[1])}" has no rows`);
    for (const [, feature, time, title, desc] of rows) {
      blocks.push(agendaRow(blockText(time), blockText(title), desc ? blockText(desc) : '', Boolean(feature)));
    }
  }

  const note = /<div class="agenda-note reveal">([\s\S]*?)<\/div>/.exec(sectionHtml);
  if (note) blocks.push(paragraph('rad-agenda-note', blockText(note[1])));

  // The buttons that close the Agenda are theme settings (inc/buttons.php), not page content.

  return blocks.join('\n\n');
}

function buildSeed(page, mainHtml) {
  const start = mainHtml.indexOf(`data-cms-section="${page.blockSection}"`);
  if (start === -1) fail(`${page.file} has no section ${page.blockSection}`);
  const sectionStart = mainHtml.lastIndexOf('<section', start);
  const { end } = findMatchingClose(mainHtml, 'section', mainHtml.indexOf('>', start) + 1);
  const sectionHtml = mainHtml.slice(sectionStart, end);

  if (page.slug === 'agenda') return { markup: buildAgendaSeed(sectionHtml), links: [] };
  return fail(`No block seed builder for ${page.slug}`);
}

function buildSeedsFile(seeds) {
  return buildGeneratedPhp(
    'Starting content for the pages the owner edits as blocks: the section from the design, as core blocks. `markup` is a sprintf() template whose %n$s placeholders are the permalinks of the pages listed in `links`, in order.',
    seeds
  );
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
  const seeds = {};

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
    // The Agenda's closing buttons are theme settings rather than page text.
    if (page.slug === 'agenda') {
      const actions = /\s*<div class="cta-row"[^>]*>[\s\S]*?<\/div>/;
      if (!actions.test(parts.main)) fail('Agenda.html no longer has its closing .cta-row');
      parts.main = parts.main.replace(actions, '\n<!--rad-agenda-actions-->');
    }

    let converted = convert(parts.main, page.template, page.blockSection || '', page.slug, page.postSection || '', page.sponsorSection || '');
    if (page.slug === 'summit') converted = injectSummitRegisterButton(converted);
    if (page.slug === 'agenda') converted = injectMarker(converted, 'rad-agenda-actions', '<?php rad_agenda_actions(); ?>', page.template);
    if (page.slug === 'home') converted = injectMarker(converted, 'rad-linkedin-feed', '<?php rad_linkedin_feed(); ?>', page.template);
    const template = buildTemplate(page, converted);
    if (page.blockSection) seeds[page.slug] = buildSeed(page, parts.main);
    assertClean(page.template, template);
    written.push(write(page.template, template));
  }

  const header = buildHeader(first.parts.chrome);
  const footer = buildFooter(first.parts.footer);
  assertClean('header.php', header);
  assertClean('footer.php', footer);
  written.push(write('header.php', header));
  written.push(write('footer.php', footer));

  registerHandWrittenDefaults();
  written.push(write('inc/content-defaults.php', buildDefaults()));
  written.push(write('inc/pages.php', buildPagesFile()));
  written.push(write('inc/customizer-fields.php', buildCustomizerFields()));
  written.push(write('inc/guide-content.php', buildGuide()));
  written.push(write('inc/content-seeds.php', buildSeedsFile(seeds)));
  written.push(write('style.css', buildStylesheet()));
  written.push(write('assets/js/site.js', buildSiteScript()));

  const imageCount = copyImages();

  console.log(`WordPress theme written to wordpress/redcliffe-advisory/`);
  for (const file of written) console.log(`  · ${file}`);
  console.log(`  · images/ (${imageCount} files)`);
  const autoCount = Object.values(autoFields).reduce((n, list) => n + list.length, 0);
  console.log(
    `\n${Object.keys(defaults.text).length} text fields, ` +
      `${Object.keys(defaults.images).length} images ` +
      `(${autoCount} found automatically), ${pages.length} page templates.`
  );
}

if (require.main === module) {
  main();
}

module.exports = { markdownToHtml };
