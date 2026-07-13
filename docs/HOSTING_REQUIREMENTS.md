# Hosting Requirements

This document is for a future hosting operator. The repository is currently verified locally only; no staging server, production server, domain, SSL certificate, remote database, or hosting access is available yet.

## Backend Server

- Linux server supported by PHP `^8.2` and Laravel 12.
- PHP extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `gd`, `intl`, `json`, `mbstring`, `mysqli`, `openssl`, `PDO`, `pdo_mysql`, `session`, `tokenizer`, `xml`, `xmlreader`, `xmlwriter`, `zip`.
- Composer 2.
- Nginx or Apache configured to serve `backend/public`.
- Writable directories: `backend/storage`, `backend/bootstrap/cache`, public uploads under `storage/app/public`, private files under `storage/app/private`, logs, cache, sessions, and views.
- Public storage link: `php artisan storage:link`.
- Cron is required only if scheduled Laravel commands are enabled in the target environment.
- A process manager is required only if `QUEUE_CONNECTION` uses a driver other than `sync`.
- HTTPS is required for all public traffic.

## Frontend Server

- Node.js compatible with Next.js `16.2.10`; Node.js `22.14.0` was used locally.
- npm compatible with lockfile version 3; npm `10.9.2` was used locally.
- Persistent Node process for `npm run start` after `npm run build`.
- Nginx reverse proxy in front of the Next.js process.
- Recommended build memory: 2 GB minimum, 4 GB preferred.

## Database

- MySQL or MariaDB compatible with Laravel 12.
- `utf8mb4` charset and `utf8mb4_unicode_ci` collation.
- Dedicated database and dedicated database user.
- Backup and restore access for the operator.

## Domains

Recommended structure:

```text
www.example.com
api.example.com
```

## Storage

- Public uploads: `backend/storage/app/public` exposed through `/storage`.
- Private documents: `backend/storage/app/private`, not directly served by the web server.
- Backup directory outside the public web root.
- File ownership must allow the PHP process to write Laravel storage and cache paths.

## Required Environment Names

Values must be provided through server env files, the hosting control panel, or a secure secret manager. Never commit real values.

```text
APP_NAME
APP_ENV
APP_KEY
APP_DEBUG
APP_URL
FRONTEND_URL
CORS_ALLOWED_ORIGINS
SANCTUM_STATEFUL_DOMAINS
DB_CONNECTION
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD
DB_COLLATION
CACHE_STORE
SESSION_DRIVER
SESSION_DOMAIN
SESSION_SECURE_COOKIE
QUEUE_CONNECTION
FILESYSTEM_DISK
PRIVATE_FILESYSTEM_DISK
MAIL_MAILER
MAIL_HOST
MAIL_PORT
MAIL_USERNAME
MAIL_PASSWORD
MAIL_FROM_ADDRESS
MAIL_FROM_NAME
PAYMOB_FAKE_MODE
PAYMOB_API_KEY
PAYMOB_INTEGRATION_ID
PAYMOB_IFRAME_ID
PAYMOB_HMAC_SECRET
PAYMOB_CALLBACK_URL
PAYMOB_RETURN_URL
PAYMOB_CURRENCY
NEXT_PUBLIC_API_BASE_URL
NEXT_PUBLIC_APP_URL
NEXT_PUBLIC_DEFAULT_LOCALE
NEXT_PUBLIC_PAYMOB_RETURN_URL
```

## Deployment Sequence

Use the existing release assets:

- `docs/RELEASE_RUNBOOK.md`
- `scripts/deploy-backend.sh`
- `scripts/deploy-frontend.sh`
- `scripts/verify-staging.sh`
- `scripts/backup-database.sh`

Do not mark staging or production complete until public HTTPS, domain routing, remote database access, storage permissions, backups, mail, and Paymob credentials have been verified in that external environment.
