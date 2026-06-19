const crypto = require('crypto');
const path = require('path');

const express = require('express');
const multer = require('multer');
const nodemailer = require('nodemailer');
const { Pool } = require('pg');

const app = express();
app.set('trust proxy', 1);
const upload = multer({
  storage: multer.memoryStorage(),
  limits: { fileSize: Number(process.env.MAX_UPLOAD_BYTES || 8 * 1024 * 1024) },
});

const ROOT = __dirname;
const PORT = Number(process.env.PORT || 3007);
const ADMIN_USERNAME = process.env.ADMIN_USERNAME || 'admin';
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'radcliffe';
const SESSION_SECRET = process.env.SESSION_SECRET || 'replace-this-session-secret';
const COOKIE_NAME = 'radcliffe_admin';

const pool = new Pool({
  connectionString:
    process.env.DATABASE_URL || 'postgres://radcliffe:radcliffe@localhost:5432/radcliffe',
});

const contentSeeds = [
  {
    key: 'global.announcement.text',
    page: 'Global',
    label: 'Announcement bar text',
    type: 'html',
    value:
      '<span class="hide-sm">The City Quantum &amp; AI Summit · Sixth Anniversary — </span><em>7 October 2026</em><span class="hide-sm">, Mansion House</span>',
  },
  {
    key: 'global.announcement.cta',
    page: 'Global',
    label: 'Announcement CTA',
    type: 'html',
    value: 'See the agenda <span class="arr">→</span>',
  },
  {
    key: 'home.hero.eyebrow',
    page: 'Homepage',
    label: 'Hero eyebrow',
    type: 'html',
    value: '<span class="eb-mark" aria-hidden="true"></span>A Strategic Influence Platform',
  },
  {
    key: 'home.hero.title',
    page: 'Homepage',
    label: 'Hero title',
    type: 'html',
    value: 'Connecting the world&rsquo;s most <em>independent</em> minds',
  },
  {
    key: 'home.hero.lede',
    page: 'Homepage',
    label: 'Hero lede',
    type: 'html',
    value: 'Advising companies with a global outlook — bridging <em>Deep&nbsp;Tech, Finance and Defence</em>.',
  },
  {
    key: 'home.testimonial.1.quote',
    page: 'Homepage',
    label: 'Featured testimonial quote',
    type: 'html',
    value:
      '<span class="open-q">“</span>Karina Robinson is, in the best sense, a benign disruptor.<span class="close-q">”</span>',
  },
  {
    key: 'home.testimonial.1.attribution',
    page: 'Homepage',
    label: 'Featured testimonial attribution',
    type: 'html',
    value: '<span class="name">A City Chair</span> · Under Chatham House rule',
  },
  {
    key: 'practice.hero.title',
    page: 'Practice',
    label: 'Practice hero title',
    type: 'html',
    value: 'Chair &amp; CEO Counsel',
  },
  {
    key: 'who.hero.title',
    page: 'Who',
    label: 'Who hero title',
    type: 'html',
    value: 'Karina Robinson',
  },
  {
    key: 'city.hero.title',
    page: 'The City',
    label: 'City hero title',
    type: 'html',
    value: 'The City of London',
  },
  {
    key: 'summit.hero.title',
    page: 'Summit',
    label: 'Summit hero title',
    type: 'html',
    value: 'The City<br />Quantum &amp; <em>AI</em> Summit',
  },
  {
    key: 'summit.hero.sub',
    page: 'Summit',
    label: 'Summit hero subtitle',
    type: 'html',
    value: 'Connections in Chaos — the Summit where world-changing technology meets the City of London.',
  },
  {
    key: 'agenda.hero.title',
    page: 'Agenda',
    label: 'Agenda hero title',
    type: 'html',
    value: 'The agenda',
  },
  {
    key: 'articles.hero.title',
    page: 'Articles',
    label: 'Articles hero title',
    type: 'html',
    value: 'Articles &amp;<br /><em>long reads</em>',
  },
  {
    key: 'ethics.hero.title',
    page: 'Ethics',
    label: 'Ethics hero title',
    type: 'html',
    value: 'Ethics &amp;<br /><em>independence</em>',
  },
  {
    key: 'contact.recipient.email',
    page: 'Contact',
    label: 'Contact form recipient email',
    type: 'text',
    value: 'karina.robinson@redcliffeadvisory.com',
  },
  {
    key: 'contact.hero.title',
    page: 'Contact',
    label: 'Contact page title',
    type: 'html',
    value: 'Contact <em>Redcliffe</em>',
  },
  {
    key: 'contact.hero.lede',
    page: 'Contact',
    label: 'Contact page lede',
    type: 'html',
    value:
      'For advisory enquiries, Summit attendance, media requests, or private introductions, send a note through the form below.',
  },
  {
    key: 'contact.details.email',
    page: 'Contact',
    label: 'Displayed email address',
    type: 'text',
    value: 'karina.robinson@redcliffeadvisory.com',
  },
  {
    key: 'contact.details.location',
    page: 'Contact',
    label: 'Displayed location',
    type: 'text',
    value: 'London & Madrid',
  },
  {
    key: 'contact.details.response',
    page: 'Contact',
    label: 'Response note',
    type: 'text',
    value: 'Messages are reviewed by Redcliffe Advisory. We respond where the enquiry is relevant to the practice.',
  },
];

const mediaSeeds = [
  { key: 'home.hero.portrait', page: 'Homepage', label: 'Homepage portrait' },
  { key: 'home.entry.summit', page: 'Homepage', label: 'Homepage summit image' },
  { key: 'home.entry.who', page: 'Homepage', label: 'Homepage profile image' },
  { key: 'summit.hero.medallion', page: 'Summit', label: 'Summit medallion' },
  { key: 'summit.feature.photo', page: 'Summit', label: 'Summit feature photo' },
  { key: 'city.feature.photo', page: 'The City', label: 'City feature photo' },
  { key: 'who.hero.photo', page: 'Who', label: 'Who hero photo' },
  { key: 'articles.hero.photo', page: 'Articles', label: 'Articles hero photo' },
];

const sectionSeeds = [
  { key: 'home.hero', pageSlug: 'home', page: 'Homepage', label: 'Hero', sortOrder: 10, isStatic: true },
  { key: 'home.rooms', pageSlug: 'home', page: 'Homepage', label: 'Six rooms index', sortOrder: 20, isStatic: true },
  { key: 'home.testimonials', pageSlug: 'home', page: 'Homepage', label: 'Testimonials', sortOrder: 30, isStatic: true },
  { key: 'practice.hero', pageSlug: 'practice', page: 'Practice', label: 'Hero', sortOrder: 10, isStatic: true },
  { key: 'practice.intro', pageSlug: 'practice', page: 'Practice', label: 'Intro prose', sortOrder: 20, isStatic: true },
  { key: 'practice.areas', pageSlug: 'practice', page: 'Practice', label: 'Practice areas', sortOrder: 30, isStatic: true },
  { key: 'practice.other', pageSlug: 'practice', page: 'Practice', label: 'Other rooms', sortOrder: 40, isStatic: true },
  { key: 'who.hero', pageSlug: 'who', page: 'Who', label: 'Hero', sortOrder: 10, isStatic: true },
  { key: 'who.profile', pageSlug: 'who', page: 'Who', label: 'Profile', sortOrder: 20, isStatic: true },
  { key: 'who.other', pageSlug: 'who', page: 'Who', label: 'Other rooms', sortOrder: 30, isStatic: true },
  { key: 'city.hero', pageSlug: 'city', page: 'The City', label: 'Hero', sortOrder: 10, isStatic: true },
  { key: 'city.intro', pageSlug: 'city', page: 'The City', label: 'Intro prose', sortOrder: 20, isStatic: true },
  { key: 'city.quantum', pageSlug: 'city', page: 'The City', label: 'Quantum future', sortOrder: 30, isStatic: true },
  { key: 'city.other', pageSlug: 'city', page: 'The City', label: 'Other rooms', sortOrder: 40, isStatic: true },
  { key: 'summit.hero', pageSlug: 'summit', page: 'Summit', label: 'Hero', sortOrder: 10, isStatic: true },
  { key: 'summit.feature', pageSlug: 'summit', page: 'Summit', label: 'Feature', sortOrder: 20, isStatic: true },
  { key: 'summit.audience', pageSlug: 'summit', page: 'Summit', label: 'Audience', sortOrder: 30, isStatic: true },
  { key: 'summit.collaborators', pageSlug: 'summit', page: 'Summit', label: 'Collaborators', sortOrder: 40, isStatic: true },
  { key: 'summit.gallery', pageSlug: 'summit', page: 'Summit', label: 'Gallery', sortOrder: 50, isStatic: true },
  { key: 'summit.other', pageSlug: 'summit', page: 'Summit', label: 'Other rooms', sortOrder: 60, isStatic: true },
  { key: 'agenda.hero', pageSlug: 'agenda', page: 'Agenda', label: 'Hero', sortOrder: 10, isStatic: true },
  { key: 'agenda.programme', pageSlug: 'agenda', page: 'Agenda', label: 'Programme', sortOrder: 20, isStatic: true },
  { key: 'articles.hero', pageSlug: 'articles', page: 'Articles', label: 'Hero', sortOrder: 10, isStatic: true },
  { key: 'articles.journal', pageSlug: 'articles', page: 'Articles', label: 'Journal', sortOrder: 20, isStatic: true },
  { key: 'articles.other', pageSlug: 'articles', page: 'Articles', label: 'Other rooms', sortOrder: 30, isStatic: true },
  { key: 'ethics.hero', pageSlug: 'ethics', page: 'Ethics', label: 'Hero', sortOrder: 10, isStatic: true },
  { key: 'ethics.principles', pageSlug: 'ethics', page: 'Ethics', label: 'Principles', sortOrder: 20, isStatic: true },
  { key: 'ethics.commitments', pageSlug: 'ethics', page: 'Ethics', label: 'Commitments', sortOrder: 30, isStatic: true },
  { key: 'ethics.other', pageSlug: 'ethics', page: 'Ethics', label: 'Other rooms', sortOrder: 40, isStatic: true },
  { key: 'contact.hero', pageSlug: 'contact', page: 'Contact', label: 'Hero', sortOrder: 10, isStatic: true },
  { key: 'contact.form', pageSlug: 'contact', page: 'Contact', label: 'Contact form', sortOrder: 20, isStatic: true },
];

app.disable('x-powered-by');
app.use(express.json({ limit: '1mb' }));
app.use(express.urlencoded({ extended: true }));

function parseCookies(req) {
  return Object.fromEntries(
    (req.headers.cookie || '')
      .split(';')
      .map((part) => part.trim())
      .filter(Boolean)
      .map((part) => {
        const idx = part.indexOf('=');
        if (idx === -1) return [decodeURIComponent(part), ''];
        return [decodeURIComponent(part.slice(0, idx)), decodeURIComponent(part.slice(idx + 1))];
      })
  );
}

function signPayload(payload) {
  const body = Buffer.from(JSON.stringify(payload)).toString('base64url');
  const signature = crypto.createHmac('sha256', SESSION_SECRET).update(body).digest('base64url');
  return `${body}.${signature}`;
}

function verifyToken(token) {
  if (!token || !token.includes('.')) return false;
  const [body, signature] = token.split('.');
  const expected = crypto.createHmac('sha256', SESSION_SECRET).update(body).digest('base64url');
  if (Buffer.byteLength(signature) !== Buffer.byteLength(expected)) return false;
  if (!crypto.timingSafeEqual(Buffer.from(signature), Buffer.from(expected))) return false;
  const payload = JSON.parse(Buffer.from(body, 'base64url').toString('utf8'));
  return payload.u === ADMIN_USERNAME && payload.exp > Date.now();
}

function requireAdmin(req, res, next) {
  try {
    if (verifyToken(parseCookies(req)[COOKIE_NAME])) return next();
  } catch {
    // Fall through to the login response.
  }
  if (req.path.startsWith('/api/')) return res.status(401).json({ error: 'Authentication required' });
  return res.redirect('/admin/login');
}

function isSecureRequest(req) {
  return req.secure || req.get('x-forwarded-proto') === 'https';
}

async function initDatabase() {
  await pool.query(`
    CREATE TABLE IF NOT EXISTS cms_content (
      key TEXT PRIMARY KEY,
      page TEXT NOT NULL DEFAULT 'Global',
      label TEXT NOT NULL,
      type TEXT NOT NULL DEFAULT 'text',
      value TEXT NOT NULL DEFAULT '',
      created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
      updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
    );

    CREATE TABLE IF NOT EXISTS cms_media (
      key TEXT PRIMARY KEY,
      page TEXT NOT NULL DEFAULT 'Global',
      label TEXT NOT NULL,
      filename TEXT,
      mime_type TEXT,
      data BYTEA,
      alt_text TEXT NOT NULL DEFAULT '',
      created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
      updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
    );

    CREATE TABLE IF NOT EXISTS cms_sections (
      key TEXT PRIMARY KEY,
      page_slug TEXT NOT NULL,
      page TEXT NOT NULL,
      label TEXT NOT NULL,
      eyebrow TEXT NOT NULL DEFAULT '',
      title TEXT NOT NULL DEFAULT '',
      body_html TEXT NOT NULL DEFAULT '',
      image_key TEXT NOT NULL DEFAULT '',
      layout TEXT NOT NULL DEFAULT 'text',
      sort_order INTEGER NOT NULL DEFAULT 100,
      enabled BOOLEAN NOT NULL DEFAULT true,
      is_static BOOLEAN NOT NULL DEFAULT false,
      created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
      updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
    );

    CREATE TABLE IF NOT EXISTS contact_messages (
      id BIGSERIAL PRIMARY KEY,
      name TEXT NOT NULL,
      email TEXT NOT NULL,
      organisation TEXT NOT NULL DEFAULT '',
      topic TEXT NOT NULL DEFAULT '',
      message TEXT NOT NULL,
      recipient_email TEXT NOT NULL DEFAULT '',
      delivery_status TEXT NOT NULL DEFAULT 'stored',
      created_at TIMESTAMPTZ NOT NULL DEFAULT now()
    );
  `);

  for (const item of contentSeeds) {
    await pool.query(
      `
      INSERT INTO cms_content (key, page, label, type, value)
      VALUES ($1, $2, $3, $4, $5)
      ON CONFLICT (key) DO NOTHING
    `,
      [item.key, item.page, item.label, item.type, item.value]
    );
  }

  for (const item of mediaSeeds) {
    await pool.query(
      `
      INSERT INTO cms_media (key, page, label)
      VALUES ($1, $2, $3)
      ON CONFLICT (key) DO NOTHING
    `,
      [item.key, item.page, item.label]
    );
  }

  for (const item of sectionSeeds) {
    await pool.query(
      `
      INSERT INTO cms_sections (key, page_slug, page, label, sort_order, is_static, enabled)
      VALUES ($1, $2, $3, $4, $5, $6, true)
      ON CONFLICT (key) DO UPDATE
        SET page_slug = EXCLUDED.page_slug,
            page = EXCLUDED.page,
            label = EXCLUDED.label,
            sort_order = EXCLUDED.sort_order,
            is_static = EXCLUDED.is_static
    `,
      [item.key, item.pageSlug, item.page, item.label, item.sortOrder, item.isStatic]
    );
  }
}

async function queryContent() {
  const content = await pool.query(
    'SELECT key, page, label, type, value, updated_at FROM cms_content ORDER BY page, label'
  );
  const media = await pool.query(
    `SELECT key, page, label, filename, mime_type, alt_text, updated_at, data IS NOT NULL AS has_data
     FROM cms_media ORDER BY page, label`
  );
  const sections = await pool.query(
    `SELECT key, page_slug, page, label, eyebrow, title, body_html, image_key, layout,
            sort_order, enabled, is_static, updated_at
     FROM cms_sections
     ORDER BY page_slug, sort_order, label`
  );
  return { content: content.rows, media: media.rows, sections: sections.rows };
}

async function getContentValue(key, fallback = '') {
  const result = await pool.query('SELECT value FROM cms_content WHERE key = $1', [key]);
  return result.rows[0]?.value || fallback;
}

async function queryMessages() {
  const result = await pool.query(
    `SELECT id, name, email, organisation, topic, message, recipient_email, delivery_status, created_at
     FROM contact_messages
     ORDER BY created_at DESC
     LIMIT 100`
  );
  return result.rows;
}

function isEmail(value) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
}

async function maybeSendContactEmail(message) {
  if (!process.env.SMTP_HOST) return 'stored';

  const transporter = nodemailer.createTransport({
    host: process.env.SMTP_HOST,
    port: Number(process.env.SMTP_PORT || 587),
    secure: process.env.SMTP_SECURE === 'true',
    auth: process.env.SMTP_USER
      ? {
          user: process.env.SMTP_USER,
          pass: process.env.SMTP_PASSWORD || '',
        }
      : undefined,
  });

  await transporter.sendMail({
    from: process.env.CONTACT_FROM_EMAIL || message.recipientEmail,
    to: message.recipientEmail,
    replyTo: message.email,
    subject: `Redcliffe website enquiry: ${message.topic || message.name}`,
    text: [
      `Name: ${message.name}`,
      `Email: ${message.email}`,
      `Organisation: ${message.organisation || '-'}`,
      `Topic: ${message.topic || '-'}`,
      '',
      message.message,
    ].join('\n'),
  });

  return 'sent';
}

app.get('/api/content', async (_req, res) => {
  try {
    const rows = await queryContent();
    res.json({
      content: Object.fromEntries(rows.content.map((row) => [row.key, row])),
      media: Object.fromEntries(rows.media.filter((row) => row.has_data).map((row) => [row.key, row])),
      sections: rows.sections,
    });
  } catch (error) {
    res.status(503).json({ error: 'CMS content is unavailable' });
  }
});

app.get('/api/media/:key', async (req, res) => {
  const result = await pool.query('SELECT mime_type, data, updated_at FROM cms_media WHERE key = $1', [
    req.params.key,
  ]);
  const row = result.rows[0];
  if (!row || !row.data) return res.sendStatus(404);
  res.setHeader('Content-Type', row.mime_type || 'application/octet-stream');
  res.setHeader('Cache-Control', 'public, max-age=300');
  res.send(row.data);
});

app.post('/api/contact', async (req, res) => {
  const name = String(req.body.name || '').trim();
  const email = String(req.body.email || '').trim();
  const organisation = String(req.body.organisation || '').trim();
  const topic = String(req.body.topic || '').trim();
  const message = String(req.body.message || '').trim();

  if (!name || !email || !message) {
    return res.status(400).json({ error: 'Name, email, and message are required.' });
  }
  if (!isEmail(email)) {
    return res.status(400).json({ error: 'Enter a valid email address.' });
  }

  const recipientEmail = await getContentValue(
    'contact.recipient.email',
    'karina.robinson@redcliffeadvisory.com'
  );
  let deliveryStatus = 'stored';
  try {
    deliveryStatus = await maybeSendContactEmail({
      name,
      email,
      organisation,
      topic,
      message,
      recipientEmail,
    });
  } catch (error) {
    deliveryStatus = 'stored_email_failed';
  }

  await pool.query(
    `
    INSERT INTO contact_messages
      (name, email, organisation, topic, message, recipient_email, delivery_status)
    VALUES ($1, $2, $3, $4, $5, $6, $7)
  `,
    [name, email, organisation, topic, message, recipientEmail, deliveryStatus]
  );

  res.json({ ok: true, deliveryStatus });
});

app.get('/admin/login', (req, res) => {
  if (verifyToken(parseCookies(req)[COOKIE_NAME])) return res.redirect('/admin');
  return res.sendFile(path.join(ROOT, 'admin', 'login.html'));
});
app.get('/admin/assets/admin.css', (_req, res) => {
  res.setHeader('Cache-Control', 'no-cache');
  return res.sendFile(path.join(ROOT, 'admin', 'assets', 'admin.css'));
});

app.post('/admin/login', (req, res) => {
  const { username, password } = req.body;
  if (username !== ADMIN_USERNAME || password !== ADMIN_PASSWORD) {
    return res.status(401).sendFile(path.join(ROOT, 'admin', 'login.html'));
  }
  const token = signPayload({ u: ADMIN_USERNAME, exp: Date.now() + 1000 * 60 * 60 * 12 });
  res.cookie(COOKIE_NAME, token, {
    httpOnly: true,
    sameSite: 'lax',
    secure: isSecureRequest(req),
    maxAge: 1000 * 60 * 60 * 12,
  });
  return res.redirect('/admin');
});

app.post('/admin/logout', requireAdmin, (_req, res) => {
  res.clearCookie(COOKIE_NAME);
  res.redirect('/admin/login');
});

app.get('/admin', requireAdmin, (_req, res) => {
  res.sendFile(path.join(ROOT, 'admin', 'index.html'));
});
app.use('/admin/assets', requireAdmin, express.static(path.join(ROOT, 'admin', 'assets')));

app.get('/api/admin/content', requireAdmin, async (_req, res) => {
  const rows = await queryContent();
  rows.messages = await queryMessages();
  res.json(rows);
});

app.get('/api/admin/messages', requireAdmin, async (_req, res) => {
  res.json(await queryMessages());
});

app.put('/api/admin/content/:key', requireAdmin, async (req, res) => {
  const { page = 'Global', label = req.params.key, type = 'text', value = '' } = req.body;
  const result = await pool.query(
    `
    INSERT INTO cms_content (key, page, label, type, value)
    VALUES ($1, $2, $3, $4, $5)
    ON CONFLICT (key) DO UPDATE
      SET page = EXCLUDED.page,
          label = EXCLUDED.label,
          type = EXCLUDED.type,
          value = EXCLUDED.value,
          updated_at = now()
    RETURNING key, page, label, type, value, updated_at
  `,
    [req.params.key, page, label, type, value]
  );
  res.json(result.rows[0]);
});

app.get('/api/admin/media', requireAdmin, async (_req, res) => {
  const result = await pool.query(
    `SELECT key, page, label, filename, mime_type, alt_text, updated_at, data IS NOT NULL AS has_data
     FROM cms_media ORDER BY page, label`
  );
  res.json(result.rows);
});

app.post('/api/admin/media/:key', requireAdmin, upload.single('image'), async (req, res) => {
  const { page = 'Global', label = req.params.key, altText = '' } = req.body;
  if (!req.file) {
    const result = await pool.query(
      `
      INSERT INTO cms_media (key, page, label, alt_text)
      VALUES ($1, $2, $3, $4)
      ON CONFLICT (key) DO UPDATE
        SET page = EXCLUDED.page,
            label = EXCLUDED.label,
            alt_text = EXCLUDED.alt_text,
            updated_at = now()
      RETURNING key, page, label, filename, mime_type, alt_text, updated_at, data IS NOT NULL AS has_data
    `,
      [req.params.key, page, label, altText]
    );
    return res.json(result.rows[0]);
  }

  const result = await pool.query(
    `
    INSERT INTO cms_media (key, page, label, filename, mime_type, data, alt_text)
    VALUES ($1, $2, $3, $4, $5, $6, $7)
    ON CONFLICT (key) DO UPDATE
      SET page = EXCLUDED.page,
          label = EXCLUDED.label,
          filename = EXCLUDED.filename,
          mime_type = EXCLUDED.mime_type,
          data = EXCLUDED.data,
          alt_text = EXCLUDED.alt_text,
          updated_at = now()
    RETURNING key, page, label, filename, mime_type, alt_text, updated_at, data IS NOT NULL AS has_data
  `,
    [req.params.key, page, label, req.file.originalname, req.file.mimetype, req.file.buffer, altText]
  );
  return res.json(result.rows[0]);
});

app.delete('/api/admin/media/:key', requireAdmin, async (req, res) => {
  const result = await pool.query(
    `
    UPDATE cms_media
    SET filename = NULL,
        mime_type = NULL,
        data = NULL,
        updated_at = now()
    WHERE key = $1
    RETURNING key, page, label, filename, mime_type, alt_text, updated_at, data IS NOT NULL AS has_data
  `,
    [req.params.key]
  );
  if (!result.rows[0]) return res.sendStatus(404);
  return res.json(result.rows[0]);
});

app.get('/api/admin/sections', requireAdmin, async (_req, res) => {
  const result = await pool.query(
    `SELECT key, page_slug, page, label, eyebrow, title, body_html, image_key, layout,
            sort_order, enabled, is_static, updated_at
     FROM cms_sections
     ORDER BY page_slug, sort_order, label`
  );
  res.json(result.rows);
});

app.put('/api/admin/sections/:key', requireAdmin, async (req, res) => {
  const {
    pageSlug = 'home',
    page = 'Homepage',
    label = req.params.key,
    eyebrow = '',
    title = '',
    bodyHtml = '',
    imageKey = '',
    layout = 'text',
    sortOrder = 100,
    enabled = true,
  } = req.body;

  const result = await pool.query(
    `
    INSERT INTO cms_sections (
      key, page_slug, page, label, eyebrow, title, body_html, image_key, layout,
      sort_order, enabled, is_static
    )
    VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, false)
    ON CONFLICT (key) DO UPDATE
      SET page_slug = EXCLUDED.page_slug,
          page = EXCLUDED.page,
          label = EXCLUDED.label,
          eyebrow = EXCLUDED.eyebrow,
          title = EXCLUDED.title,
          body_html = EXCLUDED.body_html,
          image_key = EXCLUDED.image_key,
          layout = EXCLUDED.layout,
          sort_order = EXCLUDED.sort_order,
          enabled = EXCLUDED.enabled,
          updated_at = now()
    RETURNING key, page_slug, page, label, eyebrow, title, body_html, image_key, layout,
              sort_order, enabled, is_static, updated_at
  `,
    [
      req.params.key,
      pageSlug,
      page,
      label,
      eyebrow,
      title,
      bodyHtml,
      imageKey,
      layout,
      Number(sortOrder) || 100,
      Boolean(enabled),
    ]
  );
  res.json(result.rows[0]);
});

app.delete('/api/admin/sections/:key', requireAdmin, async (req, res) => {
  const existing = await pool.query('SELECT is_static FROM cms_sections WHERE key = $1', [req.params.key]);
  if (!existing.rows[0]) return res.sendStatus(404);

  if (existing.rows[0].is_static) {
    const result = await pool.query(
      `UPDATE cms_sections
       SET enabled = false, updated_at = now()
       WHERE key = $1
       RETURNING key, page_slug, page, label, eyebrow, title, body_html, image_key, layout,
                 sort_order, enabled, is_static, updated_at`,
      [req.params.key]
    );
    return res.json(result.rows[0]);
  }

  await pool.query('DELETE FROM cms_sections WHERE key = $1', [req.params.key]);
  return res.json({ deleted: true, key: req.params.key });
});

const publicFiles = new Set([
  'Agenda.html',
  'Articles.html',
  'Contact.html',
  'Ethics.html',
  'Homepage.html',
  'Practice.html',
  'Summit.html',
  'The-City.html',
  'The City Quantum & AI Summit.html',
  'Whos-Who.html',
  'styles.css',
  'site.js',
  'cms-client.js',
  'contact.js',
]);

app.use('/images', express.static(path.join(ROOT, 'images'), { index: false }));
app.get('/:file', (req, res, next) => {
  if (!publicFiles.has(req.params.file)) return next();
  return res.sendFile(path.join(ROOT, req.params.file));
});
app.get('/', (_req, res) => res.sendFile(path.join(ROOT, 'Homepage.html')));
app.get('*', (req, res, next) => {
  if (req.path.includes('.')) return next();
  return res.sendFile(path.join(ROOT, 'Homepage.html'));
});

async function start() {
  let lastError;
  for (let attempt = 1; attempt <= 20; attempt += 1) {
    try {
      await initDatabase();
      app.listen(PORT, '0.0.0.0', () => {
        console.log(`Radcliffe site listening on ${PORT}`);
      });
      return;
    } catch (error) {
      lastError = error;
      console.log(`Waiting for database (${attempt}/20): ${error.message}`);
      await new Promise((resolve) => setTimeout(resolve, 1500));
    }
  }
  throw lastError;
}

start().catch((error) => {
  console.error(error);
  process.exit(1);
});
