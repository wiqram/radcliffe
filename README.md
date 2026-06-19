# Redcliffe Advisory Website Handover

This package contains the Redcliffe Advisory website, a lightweight Node/Express CMS, PostgreSQL-backed content storage, a public contact form, and deployment manifests for Docker Compose and Kubernetes.

## What Is Included

- Static public pages: `Homepage.html`, `Practice.html`, `Whos-Who.html`, `The-City.html`, `Summit.html`, `Agenda.html`, `Articles.html`, `Ethics.html`, `Contact.html`
- Public assets: `styles.css`, `site.js`, `cms-client.js`, `contact.js`, `images/`
- Backend/CMS: `server.js`
- Admin portal: `admin/`
- Docker build files: `Dockerfile.production`, `docker-compose-prod.yml`
- Kubernetes manifests: `namespace.yaml`, `deployment.yaml`
- Technical reference: `docs/PROJECT_REFERENCE.md`

## Runtime Overview

The app runs as a Node.js service on port `3007`.

It serves the public website and exposes:

- `GET /api/content` for public CMS content.
- `GET /api/media/:key` for CMS-managed images.
- `POST /api/contact` for public contact form submissions.
- `/admin` for the CMS admin portal.

PostgreSQL stores:

- editable page content
- CMS-managed image uploads
- dynamic/hidden page sections
- contact form submissions

The site still has static HTML fallback content. If the database is unavailable, the public pages remain readable, but CMS edits, uploaded images, and contact form storage will not work.

## Prerequisites

The receiving team needs one of the following deployment paths.

For Docker Compose:

- Docker Engine
- Docker Compose v2
- Outbound access to pull `node:20-alpine` and `postgres:16-alpine`
- A container registry if the image will be pushed elsewhere

For Kubernetes:

- Kubernetes cluster access
- `kubectl` configured for the target cluster
- An ingress controller compatible with `networking.k8s.io/v1` Ingress, currently configured for `ingressClassName: nginx`
- A default StorageClass or an explicit StorageClass added to the PostgreSQL PVC template
- A container registry accessible by the cluster
- TLS certificate/secret for the production hostname, or cert-manager/another TLS automation process

For local non-container development:

- Node.js `>=20`
- npm
- PostgreSQL `16` or compatible

## Required Production Decisions

Before deploying to production, replace the defaults below:

- `ADMIN_USERNAME`
- `ADMIN_PASSWORD`
- `SESSION_SECRET`
- `POSTGRES_PASSWORD`
- `DATABASE_URL`
- Ingress hostname in `deployment.yaml`
- Kubernetes TLS secret name or certificate automation
- Container image registry path

The default admin credentials in the current manifests are:

- Username: `admin`
- Password: `radcliffe`

These are useful only for first-run validation. Change them before public production use.

## Environment Variables

Required:

- `NODE_ENV=production`
- `PORT=3007`
- `DATABASE_URL=postgres://USER:PASSWORD@HOST:5432/DB`
- `ADMIN_USERNAME`
- `ADMIN_PASSWORD`
- `SESSION_SECRET`

Database container variables:

- `POSTGRES_DB`
- `POSTGRES_USER`
- `POSTGRES_PASSWORD`
- `PGDATA=/var/lib/postgresql/data/pgdata`

Optional SMTP variables for sending contact form emails:

- `SMTP_HOST`
- `SMTP_PORT`
- `SMTP_SECURE`
- `SMTP_USER`
- `SMTP_PASSWORD`
- `CONTACT_FROM_EMAIL`

If SMTP is not configured, contact form submissions are still stored in PostgreSQL with delivery status `stored`.

## Docker Compose Deployment

1. Unzip the package.

2. Create a local `.env` file from the example:

   ```bash
   cp .env.example .env
   ```

3. Edit `.env`:

   ```env
   REGISTRY_HOST=your-container-registry.example.com
   SESSION_SECRET=replace-with-a-long-random-secret
   ```

4. Review `docker-compose-prod.yml` and update these values if needed:

   - `DATABASE_URL`
   - `ADMIN_USERNAME`
   - `ADMIN_PASSWORD`
   - PostgreSQL database/user/password
   - published port `3007:3007`

5. Build and start:

   ```bash
   docker compose -f docker-compose-prod.yml up --build -d
   ```

6. Verify:

   ```bash
   docker compose -f docker-compose-prod.yml ps
   curl -f http://localhost:3007/api/content
   ```

7. Open:

   - Public site: `http://localhost:3007/Homepage.html`
   - Admin portal: `http://localhost:3007/admin`

8. Stop:

   ```bash
   docker compose -f docker-compose-prod.yml down
   ```

To remove the database volume as well:

```bash
docker compose -f docker-compose-prod.yml down -v
```

Only run `down -v` when data loss is acceptable.

## Kubernetes Deployment

1. Build and push the application image.

   ```bash
   cp .env.example .env
   # Edit REGISTRY_HOST in .env first.
   docker compose -f docker-compose-prod.yml build radcliffe-web
   docker compose -f docker-compose-prod.yml push radcliffe-web
   ```

2. Edit `deployment.yaml`.

   Replace:

   - `container-registry.traderyolo.com/radcliffe-web` with the receiving team's image path.
   - `radcliffe.example.com` with the production hostname.
   - `radcliffe-web-tls` with the actual TLS secret, unless TLS is provisioned another way.
   - all default values in `radcliffe-app-secret`.

3. Confirm storage.

   `deployment.yaml` creates a PostgreSQL `StatefulSet` with a `2Gi` PVC. If the cluster does not have a default StorageClass, add `storageClassName` under:

   ```yaml
   volumeClaimTemplates:
     - metadata:
         name: postgres-data
       spec:
         storageClassName: your-storage-class
   ```

4. Apply the namespace and deployment:

   ```bash
   kubectl apply -f namespace.yaml
   kubectl apply -f deployment.yaml
   ```

5. Verify rollout:

   ```bash
   kubectl -n radcliffe get pods
   kubectl -n radcliffe rollout status deployment/radcliffe-web
   kubectl -n radcliffe get svc
   kubectl -n radcliffe get ingress
   ```

6. Check logs if needed:

   ```bash
   kubectl -n radcliffe logs deployment/radcliffe-web
   kubectl -n radcliffe logs statefulset/radcliffe-db
   ```

7. Smoke test through port-forwarding:

   ```bash
   kubectl -n radcliffe port-forward svc/radcliffe-web 3007:3007
   curl -f http://localhost:3007/api/content
   ```

8. Open:

   - Public site: `https://<production-hostname>/Homepage.html`
   - Admin portal: `https://<production-hostname>/admin`

## Kubernetes Notes

- `deployment.yaml` currently uses namespace `radcliffe`.
- Web service type is `NodePort` with `nodePort: 30070`. If the receiving team only uses Ingress, they may change this to `ClusterIP`.
- HPA is configured for `radcliffe-web` with `minReplicas: 2` and `maxReplicas: 6`.
- PostgreSQL is deployed in-cluster for this package. If the receiving team uses managed PostgreSQL, remove the `radcliffe-db` `StatefulSet` and service, then point `DATABASE_URL` to the managed database.

## Admin Portal

The admin portal is available at `/admin`.

Admin can manage:

- page text and HTML content
- images
- page sections
- contact form messages

The public top navigation includes an `Admin` link.

## Contact Form

All enquiry/contact links route to `Contact.html`.

Submissions go to `POST /api/contact` and are stored in `contact_messages`.

The recipient email and displayed contact details are editable through the CMS using these keys:

- `contact.recipient.email`
- `contact.details.email`
- `contact.details.location`
- `contact.details.response`
- `contact.hero.title`
- `contact.hero.lede`

## Database Initialization

The app creates required tables automatically on startup:

- `cms_content`
- `cms_media`
- `cms_sections`
- `contact_messages`

It also seeds the initial CMS keys and section records. Existing rows are not overwritten except for stable section metadata needed by the app.

## Backup And Restore

For Docker Compose:

```bash
docker exec radcliffe-db pg_dump -U radcliffe radcliffe > radcliffe-backup.sql
cat radcliffe-backup.sql | docker exec -i radcliffe-db psql -U radcliffe radcliffe
```

For Kubernetes:

```bash
kubectl -n radcliffe exec statefulset/radcliffe-db -- pg_dump -U radcliffe radcliffe > radcliffe-backup.sql
cat radcliffe-backup.sql | kubectl -n radcliffe exec -i statefulset/radcliffe-db -- psql -U radcliffe radcliffe
```

Adjust database/user names if production secrets differ.

## Verification Checklist

After deployment:

1. `GET /api/content` returns JSON.
2. `Homepage.html` loads.
3. `Summit.html` loads with logo, announcement bar, and top navigation aligned.
4. `/admin` redirects to login when unauthenticated.
5. Admin login succeeds with configured credentials.
6. Content edits in admin are reflected on the public site.
7. Image uploads in admin are visible on public pages.
8. `Contact.html` form submission stores a message.
9. Admin `Messages` view shows the submitted contact form message.
10. PostgreSQL data survives web pod restarts and database pod restarts.

## Common Troubleshooting

If `/api/content` returns `503`:

- Check `DATABASE_URL`.
- Check PostgreSQL pod/container health.
- Check app logs for connection errors.

If admin login loops:

- Confirm `SESSION_SECRET` is stable across pod restarts.
- Confirm HTTPS/proxy headers are correct. The app trusts one proxy hop and sets secure cookies when the request is HTTPS or `x-forwarded-proto=https`.

If uploaded CMS images disappear:

- Confirm PostgreSQL persistence is working.
- Confirm the database volume/PVC was not recreated.

If contact form stores but does not send email:

- Configure SMTP environment variables.
- Check `delivery_status` in admin Messages.

## Files Not Included In The Handover Zip

The generated handover zip intentionally excludes:

- `.git/`
- `.idea/`
- `node_modules/`
- local `.env`
- generated handover zip files

Use `.env.example` as the starting point for environment configuration.

