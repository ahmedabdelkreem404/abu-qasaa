# Deployment Notes

V1 is a conventional two-app deployment: Laravel API plus Next.js frontend, backed by one MySQL database.

## Backend

Required environment:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY` generated per environment
- MySQL `DB_*` credentials
- Sanctum-compatible frontend domain settings
- Paymob/manual payment settings only when enabling live payment flows

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

## Frontend

Required environment:

- `NEXT_PUBLIC_API_URL=https://api.example.com/api/v1`
- `NEXT_PUBLIC_SITE_URL=https://www.example.com`

Release commands:

```bash
npm ci
npm run build
npm run start
```

The frontend uses local/system fonts only and does not depend on Google Fonts at runtime.

## Database

Run migrations once per release. Seeders are safe for local/dev verification; production seeding should be limited to required bootstrap records and reviewed before execution.
