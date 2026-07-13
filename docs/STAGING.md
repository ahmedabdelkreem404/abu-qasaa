# Staging Deployment Architecture

Phase 15 prepares repository-side staging readiness. No external deployment was performed because this repository does not contain server credentials, SSH access, hosting credentials, or production secrets.

## Release Baseline

- Baseline commit: `81e87b8d617fd281b5b9bf782cbac2042ef62283`
- Baseline tag: `v1.0.0`
- Staging branch: `codex/staging-readiness`
- Production launch status: not approved until a real staging deployment and smoke test run are completed.

## Backend Runtime

Source of truth: `backend/composer.json`, `backend/config`, `backend/.env.example`.

- PHP: `^8.2`
- Framework: Laravel 12
- Composer: Composer 2 is expected for modern Laravel dependency resolution.
- Required PHP extensions: common Laravel production extensions plus `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `curl`, and `bcmath` when payment/decimal handling needs it.
- Web document root: `backend/public`
- Deployment migration command: `php artisan migrate --force`
- Do not run `php artisan migrate:fresh` outside a local/test database.
- Do not run development seeders automatically in staging or production.

Writable backend paths:

- `backend/storage`
- `backend/storage/app`
- `backend/storage/app/public`
- `backend/storage/framework`
- `backend/storage/logs`
- `backend/bootstrap/cache`

Storage setup:

- Run `php artisan storage:link` once per deployment target.
- Public uploads are served from `public/storage`.
- Private files should stay under the configured private/local disk and must not be symlinked into the public web root.

Cache, session, and queue:

- Recommended staging defaults: `CACHE_STORE=database`, `SESSION_DRIVER=database`, `QUEUE_CONNECTION=database`.
- If a long-running queue worker is enabled, restart it after each deployment using the target server process manager.
- No scheduler tasks are currently registered in `backend/routes/console.php`.

Environment:

- `APP_ENV=staging`
- `APP_DEBUG=false`
- `LOG_LEVEL=info`
- `PAYMOB_FAKE_MODE=true`
- `APP_URL=https://api.staging.example.com`
- `FRONTEND_URL=https://staging.example.com`

CORS and cookies:

- `CORS_ALLOWED_ORIGINS` must list the frontend staging origin only.
- `SANCTUM_STATEFUL_DOMAINS` must list the frontend host when cookie-based flows are introduced.
- `SESSION_SECURE_COOKIE=true` is required behind HTTPS.
- Configure trusted proxies at the server/load balancer layer if TLS terminates before PHP.

## Frontend Runtime

Source of truth: `frontend/package.json`, `frontend/next.config.ts`, `frontend/.env.example`.

- Node.js: use the active LTS version supported by Next.js 16.
- Package manager: npm.
- Framework: Next.js `16.2.10`, React `19.2.4`.
- Install command: `npm ci`.
- Lint command: `npm run lint`.
- Build command: `npm run build`.
- Start command: `npm run start`.

Public frontend variables:

- `NEXT_PUBLIC_APP_URL`
- `NEXT_PUBLIC_SITE_URL`
- `NEXT_PUBLIC_API_URL`
- `NEXT_PUBLIC_API_BASE_URL` as an alias for operators that use that naming.
- `NEXT_PUBLIC_DEFAULT_LOCALE`
- `NEXT_PUBLIC_PAYMOB_RETURN_URL`

Frontend variables must never contain Paymob API keys, HMAC secrets, database credentials, mail credentials, or private storage credentials.

The current `next.config.ts` does not enable standalone output. Do not deploy as standalone unless that output mode is explicitly configured and verified.

## Database Runtime

- Database: MySQL or MariaDB compatible with Laravel 12.
- Charset: `utf8mb4`.
- Collation: `utf8mb4_unicode_ci`.
- Required user permissions: `SELECT`, `INSERT`, `UPDATE`, `DELETE`, `CREATE`, `ALTER`, `INDEX`, and `DROP` for Laravel migration changes. Avoid granting global admin privileges.
- Staging must use a separate database from production.
- Staging must not use production customer data unless properly anonymized.

Migration command:

```bash
cd backend
php artisan migrate --force
```

Backup command examples are documented in `docs/BACKUP_AND_RECOVERY.md`.

## Payment And External Services

- Keep `PAYMOB_FAKE_MODE=true` in staging unless official test credentials are supplied through environment variables.
- Do not enable real payment processing with guessed or placeholder values.
- Use sandbox mail or a mail catcher in staging.
- Do not enable real SMS sending in staging.

## Debug And Secret Exposure Checks

Before opening staging to testers:

- Confirm public API errors do not show stack traces.
- Confirm `/api/v1/health` does not expose credentials or filesystem paths.
- Confirm `APP_DEBUG=false`.
- Confirm CORS is restricted to the staging frontend origin.
- Confirm logs are written outside the public web root.

## Issue Classification

P0 blockers:

- Data loss.
- Payment incorrectly marked paid.
- Authorization bypass.
- Cross-business-unit leakage.
- Secret exposure.
- Production completely unavailable.

P1 blockers:

- Core checkout broken.
- Inventory corruption.
- Real-estate reservation conflict.
- RFQ submission broken.
- Admin cannot operate core workflows.

P2 findings:

- Non-critical workflow issue.
- Layout issue affecting usability.
- Missing validation message.

P3 findings:

- Cosmetic or documentation issue.

Fix every P0/P1 before production launch. P2 may be fixed before launch or explicitly accepted. P3 may move to post-launch backlog.
