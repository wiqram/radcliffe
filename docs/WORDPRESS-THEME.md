# The WordPress theme

Developer notes. The deployment team needs only the guide at the top of
`README.md`; the owner needs only `docs/WEBSITE-GUIDE.md`, which is also
built into the WordPress dashboard.

## What it is

`wordpress/redcliffe-advisory/` is a classic (non-block) WordPress theme that
reproduces the nine designed pages exactly. It is self-contained: no plugins, no
external services, no build step on the server. That matters because the site is
destined for WordPress.com Business, where a theme is uploaded as a single zip.

Editing works two ways. The designed parts of the pages are a fixed design with
a known set of editable fields, exposed under **Appearance → Customize →
Redcliffe Advisory**, as in the old Node CMS. New material is added with the
ordinary block editor: each template has a slot (where the old CMS's dynamic
section mount sat) that renders the page's own block content as an extra
section, so the owner can add sections, photographs, galleries and quotes to
any page without a developer. See "Extra sections" below.

## Generated vs hand-written

Most of the theme is generated from the original HTML, so the design cannot drift
away from the source files.

```
npm run build:wp      regenerate the theme from the HTML
npm run package:wp    regenerate, then build the handover archives
```

| Generated — do not edit | Hand-written |
| --- | --- |
| `header.php`, `footer.php` | `functions.php` |
| `front-page.php`, `template-*.php` | `index.php`, `singular.php`, `404.php` |
| `style.css` (theme header + `styles.css`) | `inc/content.php`, `inc/customizer.php` |
| `assets/js/site.js` | `inc/contact.php`, `inc/setup.php` |
| `images/` | `template-parts/*.php` |
| `inc/content-defaults.php`, `inc/pages.php`, `inc/customizer-fields.php` | `inc/blocks.php`, `inc/guide.php` |
| `inc/guide-content.php` (from `docs/WEBSITE-GUIDE.md`) | `assets/css/blocks.css` |

`scripts/build-wordpress-theme.js` does the conversion; the field metadata it
works from lives in `scripts/wordpress-fields.js`.

## What the conversion does

Per page, it splits the HTML into chrome, main content and footer, checks all
nine pages share byte-identical chrome, and then rewrites:

| In the HTML | In the theme |
| --- | --- |
| `data-cms-key="home.hero.title"` | `<?php rad_html( 'home.hero.title' ); ?>`, with the element's current content captured as the Customizer default |
| `data-cms-image="who.hero.photo"` | `rad_image_url()` / `rad_image_alt()`, defaulting to the original file |
| `data-cms-section="summit.gallery"` | wrapped in `if ( rad_section_enabled( … ) )` |
| `href="Contact.html#x"` | `<?php echo esc_url( rad_url( 'contact' ) ); ?>#x` |
| `src="images/…"` | `get_theme_file_uri()` |
| the contact `<form>` | `get_template_part( 'template-parts/contact-form' )` |
| `<div data-cms-sections>` | `rad_extra_sections()` — the page's block content |

The script is strict on purpose: unbalanced tags, a changed header, or any
leftover `data-cms-*` attribute, `.html` link or flat-file asset path aborts the
build rather than shipping a half-converted page.

## How editing works

Every field is a `theme_mod` named after its key — `home.hero.title` becomes
`rad_home_hero_title`, section switches become `rad_section_<key>`.

Defaults come from `inc/content-defaults.php`, which is lifted from the HTML at
build time. `rad_get_html()` falls back to the default whenever a field is empty,
so clearing a box in the Customizer restores the original wording and the site
can never render an empty hero.

Text fields pass through `wp_kses_post()` on both save and output, which keeps
the `<em>`, `<span>` and `<br />` the design relies on and drops anything else.

Images store an attachment ID and fall back to the file shipped in the theme.
Every image the site uses is shipped with it — the five stock photographs that
the original pages hotlinked from Unsplash were downloaded into `images/` at the
dimensions the design requests, so the site makes no third-party image requests
and cannot be broken by someone else's CDN. `rad_image_url()` still returns
absolute URLs untouched, so an external address remains a valid default if one
is ever wanted.

## Extra sections

`inc/blocks.php` owns everything the block editor touches:

- `rad_extra_sections()` prints the queried page's `post_content` through
  `the_content` filters inside `<section class="section rad-extra">`, and
  prints nothing when the content is empty or only empty paragraphs, so a page
  the owner has not touched renders exactly as before.
- Editor support: `editor-styles` with `assets/css/blocks.css`, the site's five
  colours as the palette (custom colours, gradients and font sizes disabled so
  the owner cannot drift off-brand), `align-wide`.
- Seven block patterns under a "Redcliffe Advisory" category, built from core
  blocks with the theme's class names (`rad-section-head`, `rad-label`,
  `rad-lede`), so they need no custom blocks and survive core updates.
- A one-time notice in the page editor explaining that content appears at the
  bottom of the page and that the designed parts are edited in the Customizer.

`assets/css/blocks.css` styles core blocks to match `styles.css` and is loaded
both on the front end (after `style.css`) and in the editor. Every selector is
doubled — `.rad-blocks …` and `.editor-styles-wrapper …` — for that reason.

## The owner's guide

`docs/WEBSITE-GUIDE.md` is the single source. The build script converts it to
HTML (a small Markdown converter lives in `build-wordpress-theme.js`; extend it
there rather than working around it in the guide) into
`inc/guide-content.php`, and `inc/guide.php` shows it under **Website guide**
in the admin menu with a quick-links box on the dashboard. The packager also
copies the Markdown into the handover pack.

## Contact form

`template-parts/contact-form.php` posts to `admin-post.php` with a nonce and a
honeypot field. No JavaScript is involved, so it works even if scripts fail.

`inc/contact.php` validates, saves the enquiry as a `rad_enquiry` post — listed
under **Enquiries** in the admin, with sender columns and a details panel — and
emails it via `wp_mail()` with `Reply-To` set to the enquirer. Delivery failure
is recorded on the enquiry rather than shown to the visitor, so a message is
never lost to a mail problem. A validation error bounces back with the submitted
values restored from a ten-minute transient.

## Activation

`inc/setup.php` runs on `after_switch_theme`: it creates the nine pages, assigns
their templates, sets the static front page, builds the primary menu, and shows a
one-time notice pointing at the site and the Customizer. It is idempotent —
existing pages are reused by slug, and an already-assigned menu is left alone.

## Testing it

The theme was verified against a real WordPress (6.x, PHP 8.2) in Docker:

```bash
npm run package:wp

# a throwaway WordPress with the packaged zip mounted at /package
docker run -d --name wp-db -e MARIADB_ROOT_PASSWORD=root -e MARIADB_DATABASE=wp \
  -e MARIADB_USER=wp -e MARIADB_PASSWORD=wp mariadb:11
docker run -d --name wp --link wp-db:db -p 8091:80 \
  -e WORDPRESS_DB_HOST=db -e WORDPRESS_DB_USER=wp -e WORDPRESS_DB_PASSWORD=wp \
  -e WORDPRESS_DB_NAME=wp -v "$PWD/dist-wordpress":/package:ro wordpress:6-php8.2-apache

# then, with the wp-cli image sharing the same volume:
wp core install --url=http://localhost:8091 --title="Redcliffe Advisory" …
wp theme install /package/redcliffe-advisory.zip --activate
```

What was checked: the packaged zip installs and activates on a clean install;
all nine pages plus a 404 render; every internal link and image returns 200; the
original `<title>` of each page is preserved; editing, clearing and image
replacement all work through the Customizer; section switches hide and restore
sections; and the contact form handles valid, incomplete, honeypot and bad-nonce
submissions correctly. `WP_DEBUG` produced no notices.

Fidelity is verified by normalising URLs, HTML entities and inter-tag whitespace
and then diffing the rendered output against the original files. All nine pages
come out identical.

## Deliberate differences from the flat site

- The `/admin` link is gone from the navigation; WordPress has its own dashboard.
- The current-page underline is applied in PHP rather than JavaScript, and is
  suppressed on the **Enquire** button, which the original script also never
  marked.
- The old CMS's ability to invent extra page sections is not carried over —
  WordPress pages cover that need. The empty mount point it used is dropped.
- `cms-client.js` and `contact.js` are not shipped; both are now server-side.

## Where it is deployed

The live site is on **Hostinger** managed WordPress (hPanel account shared with
the Redcliffe Advisory owner), currently at its temporary
`*.hostingersite.com` address until `redcliffeadvisory.com` is connected in
hPanel. Deployment is the wp-admin upload described at the top of `README.md`;
a newer theme zip is uploaded the same way and WordPress offers **Replace
active with uploaded**. Hostinger ships LiteSpeed Cache, so purge it after a
theme update before judging the result.

Nothing in the theme needs a plugin, and it uses only core APIs, so it runs
unmodified on Hostinger, WordPress.com (Business plan or higher) and
self-hosted WordPress alike.
