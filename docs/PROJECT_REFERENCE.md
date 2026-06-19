# Redcliffe Project Reference

## Current Shape

This project began as a static Redcliffe Advisory website:

- Public pages are plain HTML files in the repository root.
- Shared styling lives in `styles.css`.
- Shared public behaviour lives in `site.js`.
- Production previously used `nginxinc/nginx-unprivileged` to serve the static files on port `3007`.
- Kubernetes resources deploy into the `radcliffe` namespace.

## Key Public Pages

- `Homepage.html` - main landing page.
- `Practice.html` - Chair and CEO counsel page.
- `Whos-Who.html` - Karina Robinson profile page.
- `The-City.html` - City of London and quantum future page.
- `Summit.html` - City Quantum and AI Summit page.
- `Agenda.html` - Summit agenda page.
- `Articles.html` - articles and long reads page.
- `Ethics.html` - ethics and independence page.

`The City Quantum & AI Summit.html` appears to be an archived/generated standalone export. It is not part of the main navigation and should be treated carefully before editing.

## CMS Architecture

The upgraded application uses a small Node/Express server instead of nginx. It still serves the same static HTML, CSS, JavaScript, and image assets, but also provides:

- Public CMS API:
  - `GET /api/content`
  - `GET /api/media/:key`
- Admin portal:
  - `GET /admin`
  - `POST /admin/login`
  - `POST /admin/logout`
  - `GET /api/admin/content`
  - `PUT /api/admin/content/:key`
  - `GET /api/admin/media`
  - `POST /api/admin/media/:key`

Content is stored in PostgreSQL:

- `cms_content` stores editable text/HTML values.
- `cms_media` stores uploaded image bytes, MIME type, filename, alt text, and labels.
- `cms_sections` stores page section controls:
  - Existing static sections are seeded with stable keys and can be enabled/disabled.
  - CMS-authored sections can be added, edited, reordered, enabled/disabled, and deleted.
  - Dynamic sections support text, text plus image, and dark band layouts.

The frontend enhancement script `cms-client.js` fetches CMS values after page load and applies them to elements marked with:

- `data-cms-key="some.key"` for text or HTML.
- `data-cms-mode="html"` when the value should be applied with `innerHTML`.
- `data-cms-image="some.image.key"` for image overrides from the database.
- `data-cms-section="page.section"` for existing sections that can be hidden from the CMS.
- `data-cms-sections` for the insertion point where CMS-authored sections are rendered.

If the CMS API or database is unavailable, the static hard-coded HTML remains visible.

## Admin Portal

The public top navigation includes an `Admin` link to `/admin`.

The admin portal has three management areas:

- `Content` - edit or add arbitrary text/HTML keys.
- `Images` - upload or add arbitrary image keys stored in PostgreSQL.
- `Sections` - configure all seeded page sections and add new CMS-authored sections.

Deleting a static section hides it for visitors by setting `enabled=false`; the HTML remains in the file as fallback. Deleting a CMS-authored section removes it from the database.

## Admin Credentials

Default admin credentials are configured to match the request:

- Username: `admin`
- Password: `radcliffe`

They can be overridden with:

- `ADMIN_USERNAME`
- `ADMIN_PASSWORD`
- `SESSION_SECRET`

For a public production deployment, replace these defaults through Kubernetes secrets or environment variables.

## Local Runtime

The app listens on port `3007`.

Docker Compose includes:

- `qcx-web` - Node/Express application image.
- `radcliffe-db` - PostgreSQL database image.

Important environment variables:

- `PORT=3007`
- `DATABASE_URL=postgres://radcliffe:radcliffe@radcliffe-db:5432/radcliffe`
- `POSTGRES_DB=radcliffe`
- `POSTGRES_USER=radcliffe`
- `POSTGRES_PASSWORD=radcliffe`

## Kubernetes Runtime

`deployment.yaml` now contains:

- Web `Deployment`
- Web `Service`
- PostgreSQL `StatefulSet`
- PostgreSQL `Service`
- PostgreSQL `PersistentVolumeClaim` template
- App `Secret`
- Ingress
- HPA

All resources use namespace `radcliffe`.

## Future Editing Notes

To make a new static element editable:

1. Add a stable key to the HTML element, for example:
   `<h1 data-cms-key="practice.hero.title" data-cms-mode="html">Chair &amp; CEO Counsel</h1>`
2. Add a seed row for the key in `server.js` so it appears in the admin portal by default.
3. For images, add `data-cms-image="some.image.key"` to the `img` element and add a media seed in `server.js`.
4. For whole static sections, add `data-cms-section="page.section"` to the section and add a section seed in `server.js`.

Keep CMS keys stable. Changing keys disconnects existing database content from the page.
