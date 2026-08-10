# How to put the Redcliffe Advisory website online

This guide takes you from "the website files are on GitHub" to "the website is
live at www.redcliffeadvisory.com".

You do **not** need to be a developer. You do **not** need to install anything.
Everything happens in a web browser.

Follow the parts in order. Each part ends with a check, so you always know it
worked before moving on.

---

## What you need first

Three things:

1. **A GitHub account** that can see the `wiqram/radcliffe` project.
2. **A free account on Vercel or Netlify.** You will create this in Part 1.
   Pick just one. They both do the same job. If you have no preference, pick
   **Vercel**.
3. **About 15 minutes.**

Later on you will also need the password for wherever redcliffeadvisory.com is
managed, but not until Part 4.

---

## How long each part takes

| Part | What it does | Time | Do you have to? |
| --- | --- | --- | --- |
| **Part 1** | Puts the website online | 10 min | Yes |
| **Part 2** | Lets you edit the website's words and pictures | 10 min | No, but you'll want it |
| **Part 3** | Emails you when someone uses the contact form | 10 min | No |
| **Part 4** | Moves redcliffeadvisory.com to the new website | 15 min | Only when you're ready |
| **Part 5** | Final check that everything works | 5 min | Yes |

---

## A few words explained

You will see these words. Here is what they mean.

**Deploy** — to publish the website. When you "deploy", the platform copies the
files and makes them visible on the internet.

**Repository (or repo)** — the folder on GitHub where the website files live.

**Environment variable** — a setting you type into the website's control panel.
It is just a **name** and a **value**, like a label on a box. Passwords and
addresses are stored this way, so they are never written into the files
themselves.

**Database** — where the website keeps things people type in: edited text,
uploaded photos, and messages from the contact form.

**DNS** — the internet's address book. It is what makes
`www.redcliffeadvisory.com` point at your website and not somebody else's.

---

## How the website is built (worth 30 seconds)

The website has **two parts that switch on separately**.

**Part one: the pages.** The homepage, the Summit page, the photos, the
styling. This works straight away with nothing set up. All the words and
pictures are already inside the files.

**Part two: the editing tools.** The `/admin` area where you change the words,
plus the contact form. This needs a database and two passwords, which you set
up in Part 2.

This is why the guide is in this order. Part 1 gets the website online. Part 2
lets you edit it. Until you do Part 2:

- The public website works perfectly and looks completely normal
- `/admin` shows a dark page saying "The CMS is not configured"
- The contact form tells visitors to send an email instead

Nothing is broken. It is just waiting for you.

---

# Part 1 — Put the website online

## If you chose Vercel

**Step 1.** Go to **[vercel.com/signup](https://vercel.com/signup)**.

**Step 2.** Click **Continue with GitHub**. Log in with GitHub and allow it.

> Signing up with GitHub is important. It is what lets Vercel see the website
> files.

**Step 3.** Go to **[vercel.com/new](https://vercel.com/new)**.

**Step 4.** Find **radcliffe** in the list and click **Import** next to it.

> Can't see it? Click **Adjust GitHub App Permissions**, tick the `radcliffe`
> project, and save. Then come back to this page.

**Step 5.** A screen appears called **Configure Project**.

**Change nothing on this screen.** Do not type anything. Do not pick anything
from the menus. The correct settings are already inside the project, and Vercel
reads them by itself.

**Step 6.** Click the black **Deploy** button.

**Step 7.** Wait about a minute. You will see text scrolling by. That is normal.

**Step 8.** When it finishes you get a web address like
`radcliffe-abc123.vercel.app`. **Click it.**

**Your website is online.**

## If you chose Netlify

**Step 1.** Go to **[netlify.com/signup](https://app.netlify.com/signup)**.

**Step 2.** Click **GitHub**. Log in with GitHub and allow it.

**Step 3.** Go to **[app.netlify.com/start](https://app.netlify.com/start)**.

**Step 4.** Click **Deploy with GitHub**, then find and click **radcliffe** in
the list.

> Can't see it? Click **Configure the Netlify app on GitHub**, tick the
> `radcliffe` project, and save. Then come back to this page.

**Step 5.** A screen appears with build settings on it.

**Change nothing on this screen.** The correct settings are already inside the
project, and Netlify reads them by itself.

**Step 6.** Click **Deploy radcliffe**.

**Step 7.** Wait about a minute.

**Step 8.** When it finishes you get a web address like
`radcliffe-abc123.netlify.app`. **Click it.**

**Your website is online.**

## Check Part 1 worked

Open your new web address and look for three things:

- [ ] The homepage loads, with photos and proper styling
- [ ] Every link along the top opens a page that works
- [ ] Add `/admin` to the end of the address. You should see a **dark page
      saying "The CMS is not configured"**

**That dark page is the correct answer right now.** It means everything
deployed properly and is waiting for Part 2.

If anything looks wrong, see [If something goes wrong](#if-something-goes-wrong)
near the end.

---

# Part 2 — Turn on editing

This gives you the `/admin` area, where you change the website's words and
pictures without needing a developer.

## Step 2.1 — Make a database

The website needs somewhere to keep what you type. That is the database.

Go to **[neon.tech](https://neon.tech)** and sign up. It is free.

> Neon is the easiest. [Supabase](https://supabase.com) works too, as does
> **Vercel Postgres** if you are on Vercel (look under **Storage**). Any
> PostgreSQL database works.

Create a project. Neon will show you something called a **connection string**.
It looks like this:

```
postgres://username:password@ep-cool-name.neon.tech/dbname?sslmode=require
```

**Copy the whole thing**, including the `?sslmode=require` on the end. Paste it
somewhere safe for a moment. You will need it in Step 2.3.

> **Treat it like a password.** Anyone with this line can read the database.
> Don't email it or put it in a document that gets shared around.

## Step 2.2 — Make up two passwords

You need to invent two values. Write them down somewhere safe.

**One: your admin password.** This is what you will type to log into `/admin`.
Make it a strong one. It protects the live website.

**Two: a long random string.** The website uses this behind the scenes to
remember that you are logged in. Nobody ever types it. Just mash the keyboard
for 40 or more characters, or use a password generator.

> **Why can't I skip these?** An older version of this website had a built-in
> password that anyone could look up. That is fine on a laptop and dangerous on
> the internet. The `/admin` area now stays switched off until you set your own.

## Step 2.3 — Type three settings into your platform

Now you tell the website about the database and the passwords.

**On Vercel:** open your project, click **Settings** at the top, then
**Environment Variables** in the left menu.

**On Netlify:** open your site, click **Site configuration**, then
**Environment variables** in the left menu.

Add these three, one at a time. The **Name** must be typed exactly as shown,
in capitals, with underscores:

| Name (type exactly) | Value (what to paste in) |
| --- | --- |
| `DATABASE_URL` | The connection string you copied in Step 2.1 |
| `ADMIN_PASSWORD` | Your admin password from Step 2.2 |
| `SESSION_SECRET` | Your long random string from Step 2.2 |

On Vercel, leave all the environment tick-boxes ticked. Click **Save** after
each one.

> Optional: you can also add `ADMIN_USERNAME` if you want to log in as
> something other than `admin`.

## Step 2.4 — Deploy again

**Settings only reach the website the next time it deploys.** So do that now.

**On Vercel:** click the **Deployments** tab, find the newest one, click the
**⋯** menu on the right, and choose **Redeploy**.

**On Netlify:** click the **Deploys** tab, then **Trigger deploy**, then
**Deploy site**.

Wait for it to finish.

## Step 2.5 — Log in

Go to your website address and add `/admin` to the end.

Instead of the dark "not configured" page, you should now see a **login form**.

Log in with:

- Username: `admin`
- Password: the admin password you chose in Step 2.2

The database fills itself in automatically the first time you use it. There is
nothing to import or set up.

## Check Part 2 worked

- [ ] `/admin` shows a login form
- [ ] Your password gets you in
- [ ] Change some text in the admin area and click save
- [ ] Open that page on the public website, refresh, and see your change

---

# Part 3 — Turn on emails

Without this, contact form messages are still saved. You just have to remember
to check the admin area for them. With this, they land in an inbox as well.

> **Do Part 2 first.** The email settings need the database.

## What the website emails

Just one thing: **when someone fills in the contact form, that message gets
emailed to you.**

The website never emails the visitor. There is no newsletter and no marketing.

The email arrives from your own address, and when you press **Reply**, it
replies **to the visitor**. So it behaves like a normal email from them.

## Messages are never lost

Whatever happens with email, the message is always saved in the admin area
under **Messages**, with a small label on it:

| Label you see | What it means |
| --- | --- |
| `stored` | Saved. Email is not switched on yet |
| `sent` (green) | Saved **and** emailed to you |
| `stored_email_failed` | Saved, but the email did not go out. Something needs fixing |

So you can never lose an enquiry, even if the email part breaks.

## Step 3.1 — Pick how the emails get sent

The website cannot send email by itself. It hands each message to an email
service. You have two choices.

**Choice A — use the email account you already have.** Good if the practice
already uses Google Workspace or Microsoft 365.

**Choice B — use a free email-sending service**, such as
[Resend](https://resend.com) or [Postmark](https://postmarkapp.com). These
exist to make sure automated emails actually arrive instead of landing in spam.
The free plans are far more than a contact form needs.

**If you are not sure, pick Choice B with Resend.** It takes about five minutes
and the emails are more likely to arrive.

## Step 3.2 — Collect five pieces of information

Whichever service you use, you need the same five things. Find your service in
this table:

| Your service | Host | Port | Username | Password |
| --- | --- | --- | --- | --- |
| Gmail / Google Workspace | `smtp.gmail.com` | `587` | your full email address | an **App Password** (see below) |
| Microsoft 365 / Outlook | `smtp.office365.com` | `587` | your full email address | your mailbox password |
| Resend | `smtp.resend.com` | `587` | the word `resend` | your API key |
| Postmark | `smtp.postmarkapp.com` | `587` | your Server API token | the same token again |
| SendGrid | `smtp.sendgrid.net` | `587` | the word `apikey` | your API key |
| Mailgun | `smtp.mailgun.org` | `587` | `postmaster@your-domain` | the password Mailgun shows you |
| Brevo | `smtp-relay.brevo.com` | `587` | your Brevo login email | your SMTP key |

**Two things that trip people up:**

**Gmail will refuse your normal password.** You must switch on 2-Step
Verification for that Google account, then create an **App Password** at
[myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords).
Use that 16-character code as the password.

**Microsoft 365 often blocks this by default,** and Microsoft is gradually
turning it off for everyone. If it refuses to log in, either ask whoever
manages your Microsoft account to switch on "SMTP AUTH" for that mailbox, or
use Choice B instead.

## Step 3.3 — Type the email settings in

Go back to the same **Environment Variables** screen you used in Step 2.3, and
add these:

| Name (type exactly) | Value | Needed? |
| --- | --- | --- |
| `SMTP_HOST` | The **Host** from the table above | **Yes.** This is the switch that turns emailing on |
| `SMTP_PORT` | `587` | No, `587` is assumed |
| `SMTP_SECURE` | `false` | No, `false` is assumed. Use `true` only if you chose port `465` |
| `SMTP_USER` | The **Username** from the table | Yes |
| `SMTP_PASSWORD` | The **Password** from the table | Yes |
| `CONTACT_FROM_EMAIL` | The address the emails come **from**, like `website@redcliffeadvisory.com` | Yes, in practice |

> **About `CONTACT_FROM_EMAIL`:** it has to be an address your email service is
> allowed to send from. If you picked Choice B, that usually means proving you
> own redcliffeadvisory.com in that service first. It will show you how. Getting
> this wrong is the most common reason emails get rejected.

Then **deploy again**, exactly as in Step 2.4. Settings only take effect on the
next deploy.

## Step 3.4 — Say who gets the emails

This one is not a setting. It lives in the admin area, so you can change it
whenever you like:

1. Log into `/admin`
2. Find the **Contact** section
3. Edit **Contact form recipient email**
4. Save

It starts out as `karina.robinson@redcliffeadvisory.com`.

## Step 3.5 — Send yourself a test

1. Go to the **Contact** page on your live website and fill in the form.
2. Check the inbox. **Also check the spam folder.**
3. In `/admin` → **Messages**, check your test shows the green **`sent`**
   label.
4. Press **Reply** on the email. It should be addressed to whoever filled in
   the form.

## Step 3.6 — Stop your emails going to spam

Only if you chose **Choice B** and are sending from your own domain.

Your email service will give you some **DNS records** to add. They usually have
names like SPF and DKIM. Add them wherever redcliffeadvisory.com is managed
(the same place you will use in Part 4). The service shows a green tick once
they are right.

**Don't skip this.** Without it, email pretending to come from your domain
looks fake to other mail systems, so it goes to spam or gets thrown away.

## Check Part 3 worked

- [ ] Your test message arrived in the inbox
- [ ] It shows the green `sent` label in the admin area
- [ ] Pressing Reply writes back to the person who filled in the form

---

# Part 4 — Use your own web address

Until now the website has been at an address ending in `.vercel.app` or
`.netlify.app`. This part moves `www.redcliffeadvisory.com` to it.

> **Do this last, and only when you are happy with everything else.**
>
> Right now, redcliffeadvisory.com still shows the **old** website. That is a
> good thing. It means visitors see a working site the whole time you are
> setting this up, and if anything goes wrong you can put the old address back
> and nothing is lost.

**Step 1.** Open the domain screen:

- **Vercel:** your project → **Settings** → **Domains**
- **Netlify:** your site → **Site configuration** → **Domain management**

**Step 2.** Add **both** of these:

- `www.redcliffeadvisory.com`
- `redcliffeadvisory.com`

**Step 3.** Set **`www.redcliffeadvisory.com` as the primary one**. This makes
the short version send people to the `www` version, which is how the site works
today.

**Step 4.** The platform now shows you some **DNS records**. There will be a
`CNAME` record for `www` and an `A` record for the plain domain.

Copy these into wherever redcliffeadvisory.com's DNS is managed. That is usually
the company you bought the domain from. If you don't know who that is, ask
whoever set up the practice's email.

**Step 5.** Wait. DNS changes take anywhere from a few minutes to a few hours to
spread around the world. Your platform shows a green tick next to each domain
once it can see the change.

**Step 6.** The padlock (HTTPS) turns on by itself. You do not need to buy a
certificate or do anything else.

---

# Part 5 — Final check

Go to **www.redcliffeadvisory.com** and check each of these:

- [ ] The homepage loads with photos and proper styling
- [ ] Every page in the top menu works
- [ ] There is a padlock in the address bar
- [ ] Typing `redcliffeadvisory.com` without the `www` takes you to the `www`
      version
- [ ] `/admin` shows the login page and your password works
- [ ] Changing text in the admin area changes the public page after a refresh
- [ ] The contact form sends, and the message appears under **Messages**
- [ ] (If you did Part 3) The message arrived by email with a green `sent`
      label

## The quick status check

The website can tell you what is switched on. Add `/api/health` to the end of
your address:

**www.redcliffeadvisory.com/api/health**

Everything working looks like this:

```json
{"ok":true,"cms":"configured","admin":"enabled","contactEmail":"smtp"}
```

If something says the wrong thing, here is what it means:

| What it says | What it means |
| --- | --- |
| `"cms":"disabled"` | `DATABASE_URL` is missing or wrong. Redo Step 2.3 |
| `"admin":"disabled"` | `ADMIN_PASSWORD` or `SESSION_SECRET` is missing. Redo Step 2.3 |
| `"contactEmail":"stored"` | Messages are saved but not emailed. Do Part 3 |
| `"contactEmail":"disabled"` | No database and no email. Do Part 2 |

---

# Using the website from now on

**To change words or pictures:** log into `/admin`. Changes show up on the
website straight away. Nothing else to do.

**To change the actual pages or code:** a developer pushes to the `main` branch
on GitHub, and the website updates itself within a minute or two.

**If something breaks after a change:** every past version is kept. In the
platform dashboard, find an older deployment and click **Redeploy** (Vercel) or
**Publish deploy** (Netlify) to put it back.

**Cost:** the free plans cover a website this size comfortably. You would need
a very large amount of traffic before paying anything.

---

# If something goes wrong

| What you see | What it means | What to do |
| --- | --- | --- |
| `/admin` says "The CMS is not configured" | A setting is missing. The page lists which ones | Add them (Step 2.3), then deploy again (Step 2.4) |
| The right password is refused | The setting was saved but the site hasn't deployed since | Deploy again (Step 2.4) |
| Pages load but look plain and unstyled | The deploy did not finish properly | Open the deploy log in the dashboard and look for red text |
| Contact form says "not configured yet" | No database and no email service | Do Part 2 |
| Contact form says "unable to send" | Email failed and there is no database to fall back on | Do Part 2 |
| Messages stay on `stored` instead of `sent` | `SMTP_HOST` is missing, or you haven't deployed since adding it | Check the spelling, then deploy again |
| Messages say `stored_email_failed` | The email service rejected it | Check the logs (below). The reason is written there |
| Gmail says "Username and Password not accepted" | You used your normal password | Create an App Password (Step 3.2) |
| Microsoft says "535 Authentication unsuccessful" | SMTP is switched off for that mailbox | Ask your Microsoft admin, or use Resend instead |
| Emails go to spam | Missing DNS records | Do Step 3.6 |
| Your edits don't show on the website | Your browser is showing an old copy | Hold Shift and refresh |
| Uploaded pictures don't appear | The file was bigger than 8 MB | Save a smaller version and upload again |
| The domain still shows the old website | DNS hasn't caught up yet | Wait, then check the records in Part 4 |

## Where to find the logs

When something fails, the website writes down why.

- **Vercel:** your project → the **Logs** tab
- **Netlify:** your site → **Logs** → **Functions** → `server`

Failed emails appear as `Contact email delivery failed:` followed by the exact
reason the mail server gave.

---
---

# For developers

Everything above is the whole job. This section is background detail, and you
do not need it to deploy the site.

**Two-part deployment.** `npm run build` (`scripts/build-static.js`) copies the
public pages, `styles.css`, the client scripts and `images/` into `dist/`,
along with a generated `index.html`, `robots.txt`, `sitemap.xml` and `404.html`.
Both platforms publish `dist/` to their CDN. Building into a clean folder rather
than publishing the repository root is deliberate: it keeps `server.js`, the
Dockerfiles, the Kubernetes manifests and any `.env` files off the public web.

`/api/*` and `/admin*` are routed to a single serverless function running the
same Express application — `api/index.js` on Vercel, and
`netlify/functions/server.js` (via `serverless-http`) on Netlify. The routing
lives in `vercel.json` and `netlify.toml`, so neither platform needs build
settings entered by hand.

**Application structure.** `app.js` builds and exports the Express app;
`server.js` is only the long-running process entry point used by `npm start`,
Docker and Kubernetes. Both hosting models run identical application code.

**Graceful degradation.** With no `DATABASE_URL` the app never opens a
connection: `/api/content` answers `503`, and `cms-client.js` keeps the content
already inline in the pages. That is why the public site is unaffected by CMS
configuration.

**Production credentials.** With `NODE_ENV=production`, `ADMIN_PASSWORD` and
`SESSION_SECRET` have no fallback values, and the admin portal stays disabled
until both are set. The Kubernetes deployment already injects them from
`radcliffe-app-secret`, so that path is unaffected.

**Database migrations** run lazily on first use and are idempotent. A seeded
database short-circuits after two cheap queries, which matters because this runs
once per serverless cold start rather than once per deploy. Override with
`SKIP_DB_MIGRATION=true` or `FORCE_DB_MIGRATION=true`.

**Connection pooling.** The function opens at most one Postgres connection per
instance. Prefer a pooled connection string where the provider offers one —
Neon's pooled endpoint, or Supabase on port 6543.

**Email delivery** is handled by nodemailer, configured entirely from
`SMTP_*` environment variables. `SMTP_HOST` is the on/off switch; auth is
omitted when `SMTP_USER` is unset. Delivery status is recorded per message in
`contact_messages.delivery_status` as `stored`, `sent`, or `stored_email_failed`.
Failures never block the response.

**CMS images** are stored as `BYTEA` in Postgres and streamed through the
function by `/api/media/:key` with a five-minute cache header. Fine at this
volume; move to object storage if the library grows large.

**Existing database.** The in-cluster `radcliffe-db` Postgres is unreachable
from Vercel and Netlify. To carry existing CMS content across, `pg_dump` it and
restore into the managed database (see the Backup section of `README.md`).
Otherwise the new database seeds itself from the content built into the pages.

**Full environment variable reference** is in `.env.example`, including optional
tuning flags (`SITE_URL`, `DATABASE_SSL`, `MAX_UPLOAD_BYTES`).

**The existing pipeline is untouched.** `npm start`, `Dockerfile.production`,
`docker-compose-prod.yml`, the `Jenkinsfile` and `deployment.yaml` all still
build and run the same application, serving the whole site from one process.

**Local development:**

```bash
npm install
npm run dev      # http://localhost:3007 — uses a local Postgres if one is running
npm run build    # optional: produces dist/, exactly what the CDN will serve
```
