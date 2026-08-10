/**
 * Single source of truth for the files that make up the public website.
 *
 * Used by:
 *  - server/app.js  -> to decide what the Node server may serve directly
 *  - scripts/build-static.js -> to decide what gets copied into dist/ for
 *    Vercel / Netlify static hosting
 */

const SITE_URL = process.env.SITE_URL || 'https://www.redcliffeadvisory.com';

// Public pages, in sitemap order. `home: true` marks the page that is also
// published as dist/index.html so the bare domain resolves to it.
const PAGES = [
  { file: 'Homepage.html', home: true, priority: '1.0' },
  { file: 'Practice.html', priority: '0.8' },
  { file: 'Whos-Who.html', priority: '0.8' },
  { file: 'The-City.html', priority: '0.8' },
  { file: 'Summit.html', priority: '0.9' },
  { file: 'Agenda.html', priority: '0.8' },
  { file: 'Articles.html', priority: '0.7' },
  { file: 'Ethics.html', priority: '0.6' },
  { file: 'Contact.html', priority: '0.7' },
  // Legacy single-file capture of the old Summit site: still reachable, but
  // deliberately kept out of the sitemap.
  { file: 'The City Quantum & AI Summit.html', priority: '0.3', noindex: true },
];

// Root-level assets referenced by the pages.
const ASSETS = ['styles.css', 'site.js', 'cms-client.js', 'contact.js'];

// Directories copied verbatim into the published output.
const ASSET_DIRS = ['images'];

const PUBLIC_FILES = new Set([...PAGES.map((page) => page.file), ...ASSETS]);

module.exports = { SITE_URL, PAGES, ASSETS, ASSET_DIRS, PUBLIC_FILES };
