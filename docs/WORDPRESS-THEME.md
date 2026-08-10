# The WordPress theme

Developer notes. The deployment team needs only the guide at the top of
`README.md`.

## What it is

`wordpress/redcliffe-advisory/` is a classic (non-block) WordPress theme that
reproduces the nine designed pages exactly. It is self-contained: no plugins, no
external services, no build step on the server. That matters because the site is
destined for WordPress.com Business, where a theme is uploaded as a single zip.

Editing works the way the old Node CMS worked: a fixed design with a known set of
editable fields, exposed under **Appearance → Customize → Redcliffe Advisory**.

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
| `inc/content-defaults.php`, `inc/pages.php`, `inc/customizer-fields.php` | |

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
One default (`articles.hero.photo`) is an external Unsplash URL, as on the
current site — `rad_image_url()` detects absolute URLs and returns them
untouched. Four further Unsplash images are hardcoded in the templates exactly as
they are today; consider uploading them to the Media Library at some point so the
site does not depend on hotlinking.

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

## WordPress.com notes

Uploading a theme requires the **Business plan or higher**. Nothing in the theme
needs a plugin, and it uses only core APIs, so it runs unmodified on
WordPress.com and on self-hosted WordPress alike.
