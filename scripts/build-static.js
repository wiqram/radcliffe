#!/usr/bin/env node
/**
 * Builds dist/ — the folder Vercel and Netlify publish to their CDN.
 *
 * Copying into a clean output folder (instead of publishing the repository
 * root) keeps server code, Dockerfiles, Kubernetes manifests and .env files off
 * the public website.
 *
 * Run with `npm run build`. No dependencies, no toolchain.
 */

const fs = require('fs');
const path = require('path');

const { SITE_URL, PAGES, ASSETS, ASSET_DIRS } = require('../site-manifest');

const ROOT = path.join(__dirname, '..');
const DIST = path.join(ROOT, 'dist');

const BUILD_ID = new Date().toISOString();

function copyFile(relativePath, destinationName = relativePath) {
  const source = path.join(ROOT, relativePath);
  if (!fs.existsSync(source)) {
    throw new Error(`Missing site file: ${relativePath}`);
  }
  const target = path.join(DIST, destinationName);
  fs.mkdirSync(path.dirname(target), { recursive: true });
  fs.copyFileSync(source, target);
  return fs.statSync(target).size;
}

function copyDir(relativePath) {
  const source = path.join(ROOT, relativePath);
  if (!fs.existsSync(source)) return 0;
  fs.cpSync(source, path.join(DIST, relativePath), { recursive: true });
  return fs.readdirSync(source).length;
}

function buildSitemap() {
  const base = SITE_URL.replace(/\/$/, '');
  const today = BUILD_ID.slice(0, 10);
  const urls = PAGES.filter((page) => !page.noindex)
    .map((page) => {
      const location = page.home ? `${base}/` : `${base}/${encodeURIComponent(page.file)}`;
      return [
        '  <url>',
        `    <loc>${location}</loc>`,
        `    <lastmod>${today}</lastmod>`,
        `    <priority>${page.priority}</priority>`,
        '  </url>',
      ].join('\n');
    })
    .join('\n');

  return `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${urls}
</urlset>
`;
}

function buildRobots() {
  const base = SITE_URL.replace(/\/$/, '');
  return `User-agent: *
Allow: /
Disallow: /admin
Disallow: /api/

Sitemap: ${base}/sitemap.xml
`;
}

function buildNotFoundPage() {
  return `<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Page not found — Redcliffe Advisory</title>
<link rel="icon" type="image/png" href="/images/favicon.png" />
<style>
  :root { --cream:#F6F8FB; --ink:#0F2A4A; --slate:#61748D; --accent:#2E6CA6; }
  body { margin:0; min-height:100vh; display:grid; place-items:center; padding:2rem;
         background:var(--cream); color:var(--ink);
         font-family:"Hanken Grotesk", system-ui, sans-serif; }
  main { max-width:34rem; text-align:center; }
  h1 { font-family:"EB Garamond", Georgia, serif; font-weight:500; font-size:clamp(2rem,5vw,3rem);
       margin:0 0 1rem; }
  p { color:var(--slate); line-height:1.7; margin:0 0 2rem; }
  a { color:var(--accent); text-decoration:none; border-bottom:1px solid currentColor;
      padding-bottom:2px; }
</style>
</head>
<body>
  <main>
    <h1>That page has moved on</h1>
    <p>The page you were looking for is not here. The rooms of Redcliffe Advisory
       are all reachable from the homepage.</p>
    <p><a href="/">Return to Redcliffe Advisory</a></p>
  </main>
</body>
</html>
`;
}

function write(name, contents) {
  fs.writeFileSync(path.join(DIST, name), contents);
}

function main() {
  fs.rmSync(DIST, { recursive: true, force: true });
  fs.mkdirSync(DIST, { recursive: true });

  const copied = [];

  for (const page of PAGES) {
    copyFile(page.file);
    copied.push(page.file);
    if (page.home) {
      // The bare domain must resolve without relying on a host-specific rewrite.
      copyFile(page.file, 'index.html');
      copied.push('index.html');
    }
  }

  for (const asset of ASSETS) {
    copyFile(asset);
    copied.push(asset);
  }

  for (const dir of ASSET_DIRS) {
    const count = copyDir(dir);
    copied.push(`${dir}/ (${count} files)`);
  }

  write('robots.txt', buildRobots());
  write('sitemap.xml', buildSitemap());
  write('404.html', buildNotFoundPage());
  copied.push('robots.txt', 'sitemap.xml', '404.html');

  console.log(`Built dist/ for ${SITE_URL}`);
  for (const entry of copied) console.log(`  · ${entry}`);
}

main();
