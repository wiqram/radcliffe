# Redcliffe Advisory Website Handover

This package contains the Redcliffe Advisory website, a lightweight Node/Express CMS, PostgreSQL-backed content storage, a public contact form, and deployment manifests for Docker Compose and Kubernetes.

## What Is Included

- Static public pages: `Homepage.html`, `Practice.html`, `Whos-Who.html`, `The-City.html`, `Summit.html`, `Agenda.html`, `Articles.html`, `Ethics.html`, `Contact.html`, `The City Quantum & AI Summit.html`
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

## Deployment Scenarios

Choose the path that matches the receiving team's situation.

### Scenario A: Existing Cloud Environment

Use this path when the team already has cloud infrastructure, a container registry, and either Kubernetes or a managed container service.

Recommended production shape:

- Build the app from `Dockerfile.production`.
- Push the image to the team's registry.
- Run PostgreSQL as a managed database where possible.
- Store secrets in the cloud provider's secret manager, not in Git.
- Put HTTPS, custom domain routing, and redirects at the cloud load balancer, ingress, or CDN layer.

Container image build:

```bash
cp .env.example .env
# Edit REGISTRY_HOST in .env first.
docker compose -f docker-compose-prod.yml build radcliffe-web
docker compose -f docker-compose-prod.yml push radcliffe-web
```

Managed database setup:

1. Create a PostgreSQL 16 compatible database.
2. Create a dedicated database user and strong password.
3. Configure the app with:

   ```env
   DATABASE_URL=postgres://USER:PASSWORD@HOST:5432/DB
   NODE_ENV=production
   PORT=3007
   ADMIN_USERNAME=<new-admin-user>
   ADMIN_PASSWORD=<new-admin-password>
   SESSION_SECRET=<long-random-secret>
   ```

4. Allow network access from the app runtime to PostgreSQL.
5. Deploy the image and confirm `/api/content` returns JSON.

For Kubernetes in an existing cloud:

1. Edit `deployment.yaml`.

   Replace:

   - `container-registry.traderyolo.com/radcliffe-web` with the team's image path.
   - `radcliffe.example.com` with the production hostname.
   - `radcliffe-web-tls` with the real TLS secret, unless cert-manager or cloud TLS automation is used.
   - all default values in `radcliffe-app-secret`.

2. If using managed PostgreSQL, remove the `radcliffe-db` `StatefulSet` and `radcliffe-db` service from `deployment.yaml`, then point `DATABASE_URL` at the managed database.

3. If using the included in-cluster PostgreSQL, confirm the cluster has a default StorageClass. Otherwise add:

   ```yaml
   volumeClaimTemplates:
     - metadata:
         name: postgres-data
       spec:
         storageClassName: your-storage-class
   ```

4. Apply and verify:

   ```bash
   kubectl apply -f namespace.yaml
   kubectl apply -f deployment.yaml
   kubectl -n radcliffe rollout status deployment/radcliffe-web
   kubectl -n radcliffe get pods,svc,ingress
   ```

5. Smoke test:

   ```bash
   kubectl -n radcliffe port-forward svc/radcliffe-web 3007:3007
   curl -f http://localhost:3007/api/content
   ```

Kubernetes notes:

- `deployment.yaml` currently uses namespace `radcliffe`.
- Web service type is `NodePort` with `nodePort: 30070`. If the team only uses Ingress, change it to `ClusterIP`.
- HPA is configured for `radcliffe-web` with `minReplicas: 2` and `maxReplicas: 6`.
- The app creates its required database tables and seed rows on startup.

### Scenario B: Own Server Or Existing VM

Use this path when the team has its own Linux server, VM, or hosting environment and wants a straightforward deployment without Kubernetes.

Recommended minimum server:

- Ubuntu 22.04/24.04 LTS or equivalent Linux distribution.
- 1 vCPU and 1 GB RAM minimum for light traffic; 2 GB RAM is more comfortable.
- Docker Engine and Docker Compose v2.
- Ports `80` and `443` open for the public website if using a reverse proxy.

Deploy with Docker Compose:

1. Unzip the handover package on the server.

2. Create the runtime env file:

   ```bash
   cp .env.example .env
   ```

3. Edit `.env`:

   ```env
   REGISTRY_HOST=local
   SESSION_SECRET=replace-with-a-long-random-secret
   ```

4. Review `docker-compose-prod.yml` and change defaults before production:

   - `ADMIN_USERNAME`
   - `ADMIN_PASSWORD`
   - `POSTGRES_PASSWORD`
   - `DATABASE_URL` password to match `POSTGRES_PASSWORD`
   - published port if `3007:3007` conflicts with another service

5. Start:

   ```bash
   docker compose -f docker-compose-prod.yml up --build -d
   ```

6. Verify:

   ```bash
   docker compose -f docker-compose-prod.yml ps
   curl -f http://localhost:3007/api/content
   ```

7. Put a reverse proxy in front of the app. Example Nginx server block:

   ```nginx
   server {
       listen 80;
       server_name example.com www.example.com;

       location / {
           proxy_pass http://127.0.0.1:3007;
           proxy_set_header Host $host;
           proxy_set_header X-Real-IP $remote_addr;
           proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
           proxy_set_header X-Forwarded-Proto $scheme;
       }
   }
   ```

8. Add HTTPS using the team's standard certificate process, for example Certbot:

   ```bash
   sudo certbot --nginx -d example.com -d www.example.com
   ```

9. Open:

   - Public site: `https://example.com/Homepage.html`
   - Admin portal: `https://example.com/admin`

Stop without deleting data:

```bash
docker compose -f docker-compose-prod.yml down
```

Remove the database volume only when data loss is acceptable:

```bash
docker compose -f docker-compose-prod.yml down -v
```

### Scenario C: From Scratch On A Public Cloud

Use this path when the team has nothing installed and wants the lowest-hassle public cloud setup.

Fastest practical route:

- Create one small VM on AWS Lightsail, DigitalOcean Droplet, Azure VM, Google Compute Engine, Hetzner Cloud, or similar.
- Install Docker and Docker Compose.
- Run the included `docker-compose-prod.yml`.
- Point DNS at the VM.
- Use Nginx plus Certbot for HTTPS.

Bootstrap checklist:

1. Create a Linux VM.

   Suggested starting size:

   - Ubuntu 24.04 LTS.
   - 1-2 vCPU.
   - 2 GB RAM.
   - 20 GB disk.
   - Firewall allowing SSH, HTTP, and HTTPS.

2. Point DNS records at the VM public IP:

   ```text
   A     example.com       <vm-public-ip>
   CNAME www.example.com   example.com
   ```

3. Install Docker:

   ```bash
   sudo apt-get update
   sudo apt-get install -y ca-certificates curl gnupg
   sudo install -m 0755 -d /etc/apt/keyrings
   curl -fsSL https://download.docker.com/linux/ubuntu/gpg \
     | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
   sudo chmod a+r /etc/apt/keyrings/docker.gpg
   echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo $VERSION_CODENAME) stable" \
     | sudo tee /etc/apt/sources.list.d/docker.list >/dev/null
   sudo apt-get update
   sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
   sudo usermod -aG docker "$USER"
   ```

   Log out and back in after adding the user to the Docker group.

4. Install Nginx and Certbot:

   ```bash
   sudo apt-get install -y nginx certbot python3-certbot-nginx unzip
   ```

5. Upload and unzip the handover package:

   ```bash
   unzip radcliffe-handover-YYYY-MM-DD.zip -d radcliffe
   cd radcliffe
   cp .env.example .env
   ```

6. Edit credentials:

   ```bash
   nano .env
   nano docker-compose-prod.yml
   ```

   Replace the default admin and database passwords before the site is public.

7. Start the app:

   ```bash
   docker compose -f docker-compose-prod.yml up --build -d
   curl -f http://localhost:3007/api/content
   ```

8. Configure Nginx reverse proxy as shown in Scenario B, replacing `example.com` with the real domain.

9. Enable HTTPS:

   ```bash
   sudo certbot --nginx -d example.com -d www.example.com
   ```

10. Verify the public site and admin portal:

   - `https://example.com/Homepage.html`
   - `https://example.com/admin`

For a more cloud-native setup later, move PostgreSQL to a managed database and deploy the same Docker image to ECS, Cloud Run, Azure Container Apps, App Service, or Kubernetes.

## Admin Portal

The admin portal is available at `/admin`.

Admin can manage:

- Pages - choose a website page, then see its sections and editable text in one place.
- Media Library - replace images, upload images for new sections, or restore the original website image.
- Messages - view recent contact form enquiries.
- Settings - edit global announcement and contact routing values.

Editors do not need to know internal keys, page slugs, sort numbers, or image keys for normal use. Technical identifiers remain available only inside collapsed advanced details.

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
- local editor/cache artifacts such as `.thumbnail`
- legacy archive or upload-staging folders not required at runtime

Use `.env.example` as the starting point for environment configuration.
