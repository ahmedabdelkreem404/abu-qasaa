# Local Release Readiness

Test date: 2026-07-13

Release statement:

```text
Local release candidate approved.
External deployment and production launch are deferred until hosting infrastructure is available.
```

## Scope

Completed now:

- V1 application development
- Local automated verification
- Local release candidate
- Hosting handoff package

Deferred until hosting exists:

- Real staging deployment
- Public HTTPS/domain validation
- Remote backup validation
- Real mail validation
- Paymob test/production credentials
- Staging smoke tests against public URLs
- Production launch decision

Deferred hosting work is not an application code defect.

## Local URLs

```text
Backend API: http://localhost:8000
Frontend: http://localhost:3000
Health: http://localhost:8000/api/v1/health
```

## Backend Requirements

- PHP: `^8.2` from `backend/composer.json`; local audit used PHP `8.2.12`.
- Laravel: `12.63.0`.
- Composer: local audit used Composer `2.8.11`.
- Required PHP extensions observed locally: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `gd`, `intl`, `json`, `mbstring`, `mysqli`, `openssl`, `PDO`, `pdo_mysql`, `pdo_sqlite`, `session`, `tokenizer`, `xml`, `xmlreader`, `xmlwriter`, `zip`.
- Database: MySQL/MariaDB using `DB_CONNECTION=mysql`, `utf8mb4_unicode_ci`, and a dedicated `abu_qasaa` database for local work.
- Writable directories: `backend/storage`, `backend/bootstrap/cache`, `backend/storage/app/public`, `backend/storage/app/private`, `backend/storage/logs`, `backend/storage/framework`.
- Public storage link: `php artisan storage:link`.
- Queue behavior: local `.env.example` uses `QUEUE_CONNECTION=sync`. A queue worker is optional only when a non-sync queue is configured.
- Mail behavior: local `.env.example` uses `MAIL_MAILER=log`.
- Paymob behavior: local `.env.example` uses `PAYMOB_FAKE_MODE=true`.

## Frontend Requirements

- Node.js: local audit used `v22.14.0`.
- npm: local audit used `10.9.2`.
- Next.js: `16.2.10` from `frontend/package.json`.
- React: `19.2.4`.
- Backend API variable: `NEXT_PUBLIC_API_BASE_URL=http://localhost:8000/api/v1`.
- Browser assumption: current Chromium, Firefox, Safari, and Edge versions supported by Next.js/React runtime output.
- Font dependency: no remote Google Font dependency; local tests assert that `next/font/google`, `fonts.googleapis.com`, and `fonts.gstatic.com` are absent from frontend source.

## Local Setup

Safe helper:

```bash
scripts/setup-local.sh
```

The helper verifies required commands, copies env examples only when local env files are missing, installs dependencies, generates a Laravel app key when needed, creates the storage link, clears caches, and prints start commands.

Optional destructive local reset:

```bash
scripts/setup-local.sh --fresh-database
```

`--fresh-database` runs `php artisan migrate:fresh --seed` only after an explicit typed confirmation. Use it only for local development or automated verification, never for staging or production.

## Start Commands

Backend:

```bash
cd backend
php artisan serve
```

Frontend:

```bash
cd frontend
npm run dev
```

Optional queue worker, only when `QUEUE_CONNECTION` is not `sync`:

```bash
cd backend
php artisan queue:work
```

## Database Lifecycle

Initial setup:

```bash
cd backend
php artisan migrate --seed
```

Full local/test reset:

```bash
cd backend
php artisan migrate:fresh --seed
```

`migrate:fresh` deletes local database data. Do not run it against staging or production.

Inspection:

```bash
cd backend
php artisan migrate:status
php artisan db:show
```

## Upload Verification

- CMS image fields and product image fields currently store validated URL/path strings, not multipart upload endpoints.
- Manual payment proof currently stores a validated `proof_image` path string.
- RFQ documents have database support for private document metadata; the public RFQ submission endpoint currently does not accept multipart documents.
- Real-estate project/unit image support is represented by stored media/path metadata, not a multipart upload endpoint in the current controller.
- Shared upload storage hardening is covered by `SafeUploadService` regression tests: allowed image/PDF files succeed, unsafe executable extensions fail, oversized files fail, generated names are UUID-based, private disk writes are not exposed through the public disk, and public disk writes work with the storage link.

## Paymob Verification

Local mode keeps:

```env
PAYMOB_FAKE_MODE=true
```

Automated coverage verifies fake initiation, invalid signature rejection, valid signed callback payment confirmation, failed callback behavior, dashboard scoping, and callback idempotency. The public return URL is a browser return path only and must not mark a payment paid.

## Automated Verification Commands

Backend:

```bash
cd backend
composer dump-autoload -o
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan test
php vendor/bin/pint --test
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize:clear
composer audit
```

Frontend:

```bash
cd frontend
npm ci
npm run lint
npm run build
npm audit --omit=dev
npm ls next postcss
npm outdated
```

Do not run `npm audit fix --force`.

## Dependency Advisory Review

Composer audit: no security vulnerability advisories found.

Frontend production audit: `npm audit --omit=dev` reports two moderate advisories from the same dependency path:

```text
next@16.2.10 -> postcss@8.4.31
GHSA-qx2v-qp2m-jg93
PostCSS has XSS via Unescaped </style> in its CSS Stringify Output
```

Production relevance: Next.js bundles this vulnerable PostCSS version internally for build/runtime tooling. The application does not expose user-controlled CSS stringify functionality as a public feature in the V1 scope.

Safe upgrade option: none currently available on the stable Next.js 16 line. Registry metadata shows `16.2.10` is the latest stable Next.js version, and `next@16.2.10` depends on `postcss@8.4.31`.

Blocked status: P2, non-blocking for local V1 completion. Do not apply the audit suggestion because it requires `npm audit fix --force` and would install `next@9.3.3`, a breaking downgrade.
