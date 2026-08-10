/**
 * Vercel serverless entry point.
 *
 * vercel.json routes /api/* and /admin* here; everything else is served from
 * the static dist/ output by Vercel's CDN. An Express app is a valid
 * (req, res) handler, so it can be exported directly.
 */

const app = require('../app');

module.exports = app;
