# Release Runbook

This runbook assumes a conventional Laravel backend, Next.js frontend, and MySQL database. Replace paths and service names with the target server configuration.

## 1. Pre-Deployment Backup

```bash
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_DATABASE=abu_qasaa
export DB_USERNAME=abu_qasaa
export DB_PASSWORD="set-outside-git"
export BACKUP_DIR=/secure/backups/abu-qasaa

scripts/backup-database.sh
```

## 2. Maintenance Mode

```bash
cd /var/www/abu-qasaa/backend
php artisan down --retry=60
```

## 3. Backend Update

```bash
cd /var/www/abu-qasaa
git fetch origin
git checkout master
git pull --ff-only origin master

cd backend
composer install --no-dev --prefer-dist --optimize-autoloader
```

## 4. Database Migration

```bash
php artisan migrate --force
```

Never run `php artisan migrate:fresh` on staging or production.

## 5. Cache Rebuild

```bash
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache || true
```

## 6. Frontend Update

```bash
cd /var/www/abu-qasaa/frontend
npm ci
npm run lint
npm run build
```

## 7. Process Restart

Use the configured process manager for the target server. Examples:

```bash
sudo systemctl reload php8.2-fpm
sudo systemctl restart abu-qasaa-frontend
php /var/www/abu-qasaa/backend/artisan queue:restart
```

Only run commands that match the actual server configuration.

## 8. Bring Backend Online

```bash
cd /var/www/abu-qasaa/backend
php artisan up
```

## 9. Health Verification

```bash
scripts/verify-staging.sh https://api.example.com https://www.example.com dates
```

Expected:

- Backend `/api/v1/health` returns HTTP 200.
- Frontend home page returns HTTP 200.
- Public business-unit page returns HTTP 200.
- Missing API route does not expose debug markers.

## 10. Smoke Tests

Run `docs/STAGING_ACCEPTANCE_CHECKLIST.md` against the target staging URL. Record actual results, notes, and blocker severity.

## 11. Rollback

Code rollback:

```bash
cd /var/www/abu-qasaa/backend
php artisan down --retry=60

cd /var/www/abu-qasaa
git checkout <previous-known-good-commit-or-tag>

cd backend
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan optimize:clear
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up

cd ../frontend
npm ci
npm run build
```

Database rollback:

- Do not run automatic migration rollback in production.
- Restore only a verified backup after incident approval.
- See `docs/BACKUP_AND_RECOVERY.md`.

## Launch Decision

Production launch is approved only when:

- Staging deployment completed successfully.
- All P0/P1 findings are fixed.
- Backend tests, Pint, frontend lint/build, and dependency audits are reviewed.
- Backup and restore procedures are verified.
