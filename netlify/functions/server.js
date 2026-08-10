/**
 * Netlify Functions entry point.
 *
 * netlify.toml rewrites /api/* and /admin* to this function; everything else is
 * served from the static dist/ output.
 */

const serverless = require('serverless-http');

const app = require('../../app');

const FUNCTION_PREFIX = '/.netlify/functions/server';

const handler = serverless(app, {
  // CMS images come out of Postgres as binary; without this they would be
  // mangled into UTF-8 text on the way back through the function response.
  binary: ['image/*', 'font/*', 'application/octet-stream', 'application/pdf'],
});

exports.handler = async (event, context) => {
  context.callbackWaitsForEmptyEventLoop = false;

  // Depending on how the rewrite is applied, Netlify may hand us either the
  // original path (/api/content) or the function path
  // (/.netlify/functions/server/api/content). Express only understands the
  // former, so normalise before delegating.
  if (event.path && event.path.startsWith(FUNCTION_PREFIX)) {
    event.path = event.path.slice(FUNCTION_PREFIX.length) || '/';
  }

  return handler(event, context);
};
