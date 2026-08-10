/**
 * Long-lived server entry point (`npm start`, Docker, Kubernetes).
 *
 * Serverless platforms do not use this file — Vercel loads api/index.js and
 * Netlify loads netlify/functions/server.js, both of which import ./app.
 */

const app = require('./app');

const PORT = Number(process.env.PORT || 3007);

/**
 * The database usually starts alongside this process (compose / K8s rollout),
 * so keep trying in the background. The website itself never waits for it: the
 * pages carry their content inline and CMS routes retry on the next request.
 */
async function warmDatabase() {
  for (let attempt = 1; attempt <= 20; attempt += 1) {
    try {
      await app.ensureSchema();
      console.log('Database ready.');
      return;
    } catch (error) {
      console.log(`Waiting for database (${attempt}/20): ${error.message}`);
      await new Promise((resolve) => setTimeout(resolve, 1500));
    }
  }
  console.error('Database still unreachable — serving the website without the CMS.');
}

app.listen(PORT, '0.0.0.0', () => {
  console.log(`Radcliffe site listening on ${PORT}`);
  if (app.CMS_ENABLED) {
    warmDatabase();
  } else {
    console.log('DATABASE_URL is not set — serving the website without the CMS.');
  }
});
