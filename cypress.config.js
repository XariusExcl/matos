const { defineConfig } = require('cypress');
const { execFileSync } = require('child_process');

const sql = (query) => execFileSync('php', ['bin/console', 'dbal:run-sql', query, '--no-interaction'], { cwd: __dirname, encoding: 'utf8' });

module.exports = defineConfig({
  e2e: {
    baseUrl: process.env.CYPRESS_BASE_URL || 'http://127.0.0.1:8000',
    viewportWidth: 1440,
    viewportHeight: 900,
    video: false,
    defaultCommandTimeout: 10000,
    setupNodeEvents(on) {
      on('task', {
        resetStudent({ email, hash }) {
          sql(`INSERT INTO "user" (id, email, roles, password, name, active) SELECT nextval('user_id_seq'), '${email}', '["ROLE_USER"]', '${hash}', 'Cypress Etudiant', true WHERE NOT EXISTS (SELECT 1 FROM "user" WHERE email = '${email}')`);
          sql(`UPDATE loan SET status = 4 WHERE loaner_id = (SELECT id FROM "user" WHERE email = '${email}') AND status IN (0, 1)`);
          return null;
        },
      });
    },
  },
});
