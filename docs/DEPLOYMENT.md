# Deploying to Vercel or Netlify

The repository is configured for both platforms. Pick one — connecting the
GitHub repository is enough to get the public website live; the extra
environment variables below switch on the CMS and the contact form.

- **Vercel** reads `vercel.json`
- **Netlify** reads `netlify.toml`

Neither needs any build settings entered by hand.

---

## What gets deployed

| Part of the site | Where it runs |
| --- | --- |
| The nine public pages, CSS, JS, images | Static files on the CDN, built into `dist/` by `npm run build` |
| `/api/*` (CMS content, images, contact form) | One serverless function running the Express app |
| `/admin` (the CMS portal) | The same serverless function, behind the login |

`dist/` is generated at deploy time, so server code, Dockerfiles, Kubernetes
manifests and `.env` files are never published to the website.

### Zero configuration

With no environment variables at all, the deploy still succeeds and the website
is fully live. The pages carry their text and images inline; `cms-client.js`
simply keeps them when `/api/content` reports the CMS is unavailable. What is
switched off in that state:

- `/admin` returns a "CMS is not configured" page listing what is missing
- the contact form replies "not configured yet — please email us directly"

---

## Step 1 — Create the database (only needed for the CMS)

Any Postgres reachable over the internet works. The schema and the seed content
are created automatically on the first request.

- **Neon** — <https://neon.tech>, free tier, works well with serverless
- **Supabase** — <https://supabase.com>, free tier
- **Vercel Postgres** — provisioned from the Vercel dashboard (Storage tab)

Copy the connection string. It must be reachable with TLS; managed providers
give you one ending in `?sslmode=require`.

> The current Kubernetes deployment runs its own `radcliffe-db` Postgres inside
> the cluster. That database is not reachable from Vercel or Netlify — either
> point the new deploy at a managed database, or dump and restore the existing
> one into it (`pg_dump` / `pg_restore`, see the Backup section of `README.md`).

## Step 2 — Connect the repository

### Vercel

1. <https://vercel.com/new> → **Import Git Repository** → pick this repo.
2. Leave every build setting untouched — `vercel.json` supplies the build
   command (`npm run build`), the output directory (`dist`) and the routing.
3. Add the environment variables from Step 3 (Settings → Environment Variables),
   scoped to **Production** (and Preview if you want the CMS on previews).
4. **Deploy**.

### Netlify

1. <https://app.netlify.com/start> → **Import an existing project** → pick this
   repo.
2. Leave the build settings untouched — `netlify.toml` supplies the build
   command, the publish directory (`dist`), the functions directory and the
   routing.
3. Add the environment variables from Step 3 (Site configuration → Environment
   variables).
4. **Deploy site**.

## Step 3 — Environment variables

Set these on the platform, then redeploy (both platforms redeploy on save or on
the next push).

| Variable | Required for | Notes |
| --- | --- | --- |
| `DATABASE_URL` | CMS, admin, storing contact messages | Postgres connection string with `sslmode=require` |
| `ADMIN_USERNAME` | admin portal | Defaults to `admin` |
| `ADMIN_PASSWORD` | admin portal | **No default in production** — the portal stays disabled until this is set |
| `SESSION_SECRET` | admin portal | Long random string, e.g. `openssl rand -hex 32` |
| `SMTP_HOST` | emailing contact submissions | Without it, messages are only stored in the database |
| `SMTP_PORT` | emailing | Default `587` |
| `SMTP_SECURE` | emailing | `true` for port 465 |
| `SMTP_USER`, `SMTP_PASSWORD` | emailing | Omit for an unauthenticated relay |
| `CONTACT_FROM_EMAIL` | emailing | Envelope sender; must be one your SMTP provider allows |
| `SITE_URL` | build | Defaults to `https://www.redcliffeadvisory.com`; only affects `robots.txt` and `sitemap.xml` |

`.env.example` lists the same set with the optional tuning flags.

## Step 4 — Point the domain at the deploy

`redcliffeadvisory.com` is live today from the existing Kubernetes deployment,
so verify the new deploy on its platform URL first
(`<project>.vercel.app` / `<site>.netlify.app`) before moving DNS.

**Vercel** — Project → Settings → Domains → add `www.redcliffeadvisory.com` and
`redcliffeadvisory.com`, then follow the DNS records it prints (a `CNAME` for
`www`, an `A` record for the apex). Set `www` as the primary so the apex
redirects to it, matching the current canonical URL.

**Netlify** — Site configuration → Domain management → add the same two names
and follow the printed records.

TLS certificates are issued automatically once DNS resolves. Do not change DNS
until the platform URL is verified — that is the rollback path.

## Step 5 — Verify

Against the platform URL first, then again after the DNS switch:

```bash
BASE=https://<your-deploy-url>

curl -s $BASE/api/health                       # {"ok":true,"cms":"configured",...}
curl -s -o /dev/null -w '%{http_code}\n' $BASE/          # 200 — homepage
curl -s -o /dev/null -w '%{http_code}\n' $BASE/Summit.html
curl -s -o /dev/null -w '%{http_code}\n' $BASE/images/redcliffe-logo.webp
curl -s $BASE/api/content | head -c 120        # CMS content, or 503 if not configured
```

Then in a browser:

- every page in the top navigation loads and is styled
- `/admin` → login → edit a piece of text → the public page shows it after reload
- submit the contact form; the message appears under Messages in the portal

`/api/health` reports what is switched on:

```json
{"ok":true,"cms":"configured","admin":"enabled","contactEmail":"smtp"}
```

---

## Notes and limitations

**Serverless cold starts.** The function opens one Postgres connection per
instance and checks the schema once per cold start (two cheap queries when the
database is already seeded). Use a pooled connection string if your provider
offers one — Neon's pooled endpoint, Supabase's port 6543.

**CMS images live in Postgres.** `/api/media/:key` streams them through the
function rather than the CDN, with a 5-minute cache header. That is fine at this
volume; if the CMS ever holds many large images, move them to object storage.

**Uploads are not persisted to disk.** Serverless filesystems are ephemeral, but
nothing in the app writes to disk — uploads go straight into Postgres — so there
is nothing to migrate.

**The `uploads/` folder** in the repository is historical source material. It is
not served by the site and is not copied into `dist/`.

## Running it the old way

Nothing here replaces the existing pipeline. `npm start`, the Dockerfiles, the
Jenkinsfile and `deployment.yaml` still build and run the same app — `server.js`
now imports `app.js` rather than defining the app itself, and the site is served
from one process exactly as before.

Local development is unchanged:

```bash
npm install
npm run dev          # http://localhost:3007, uses the local Postgres if present
npm run build        # optional: preview exactly what the CDN will serve, in dist/
```
