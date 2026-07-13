# Deployment Notes

V1 is a conventional two-app deployment: Laravel API plus Next.js frontend, backed by one MySQL database. Phase 15 adds staging-readiness guidance, safe deployment scripts, backup/rollback notes, and launch checklists without introducing extra DevOps complexity.

For the complete staging architecture, read `docs/STAGING.md`. For exact release commands, read `docs/RELEASE_RUNBOOK.md`.

## Backend

Required environment:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY` generated per environment
- MySQL `DB_*` credentials
- Sanctum-compatible frontend domain settings
- Paymob/manual payment settings only when enabling live payment flows
- `APP_DEBUG=false`
- `CORS_ALLOWED_ORIGINS` restricted to the frontend origin
- `PAYMOB_FAKE_MODE=true` on staging unless official test credentials are supplied

Release commands:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

Use `/api/v1/health` as the external health probe. It returns safe operational metadata only.

Do not run `php artisan migrate:fresh` or development seeders against staging or production.

## Frontend

Required environment:

- `NEXT_PUBLIC_API_URL=https://api.example.com/api/v1`
- `NEXT_PUBLIC_API_BASE_URL=https://api.example.com/api/v1`
- `NEXT_PUBLIC_SITE_URL=https://www.example.com`
- `NEXT_PUBLIC_APP_URL=https://www.example.com`

Release commands:

```bash
npm ci
npm run build
npm run start
```

The frontend uses local/system fonts only and does not depend on Google Fonts at runtime.

## Database

Run migrations once per release with `php artisan migrate --force`. Seeders are safe for local/dev verification; production seeding should be limited to required bootstrap records and reviewed before execution.

Create a verified database backup before each staging or production deployment. See `docs/BACKUP_AND_RECOVERY.md`.
