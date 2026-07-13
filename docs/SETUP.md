# Setup

## Phase 9 Seed Notes

`php artisan migrate:fresh --seed` creates Oils wholesale seed data, including a demo approved wholesale customer with phone `01011111111` and a pending wholesale application. Dates remains wholesale-disabled by default.

## Backend

Safe helper from the repository root:

```bash
scripts/setup-local.sh
```

This copies environment examples only when local env files do not exist, installs dependencies, generates a Laravel app key when needed, creates the public storage link, and clears caches.

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Configure MySQL in `backend/.env` before running migrations. Local defaults are documented in `backend/.env.example`.

## Frontend

```bash
cd frontend
npm install
cp .env.example .env.local
npm run dev
```

The frontend reads `NEXT_PUBLIC_API_BASE_URL` and `NEXT_PUBLIC_API_URL`, which default to `http://localhost:8000/api/v1`.

## Local URLs

```text
Backend API: http://localhost:8000
Frontend: http://localhost:3000
Health: http://localhost:8000/api/v1/health
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

The setup helper also supports a guarded local reset:

```bash
scripts/setup-local.sh --fresh-database
```

## Checks

```bash
cd backend && php artisan test
cd frontend && npm run lint && npm run build
```

The root `scripts/check.ps1` runs the basic backend and frontend checks.

## Development Login

After seeding, use the local-only Super Admin:

```text
Email: admin@abuqasaa.test
Password: password
```

Business-unit demo admins use the same `DEMO_USER_PASSWORD` default:

- `oils.admin@abuqasaa.test`
- `dates.admin@abuqasaa.test`
- `realestate.admin@abuqasaa.test`
- `importexport.admin@abuqasaa.test`

Override these values in `.env` with `SUPER_ADMIN_*` and `DEMO_USER_PASSWORD`. Do not use these defaults outside local development.

## Seeded CMS Content

Use a fresh seed when working on the public website or CMS dashboard:

```bash
cd backend
php artisan migrate:fresh --seed
```

The seed creates company pages, business-unit landing pages, a main menu, demo roles, permissions, and demo users. The public site expects `NEXT_PUBLIC_API_URL` to point at the Laravel `/api/v1` base URL.

## Seeded Catalog Data

The default seed also creates product catalog data for:

- `oils`: categories, brands, retail/wholesale/distributor price lists, sample lubricants, variants, and prices.
- `dates`: categories, the Ghosoun brand, retail price list, sample dates products, variants, and prices.

Use `oils.admin@abuqasaa.test` or `dates.admin@abuqasaa.test` for scoped dashboard catalog testing.

## Commerce Local Testing

Dates supports guest checkout in seeded data. Oils supports carts and product browsing, but seeded `allow_guest_checkout` is disabled for Oils to reflect wholesale-style ordering rules.

Public cart session tokens are stored by the frontend per business unit using `abu_qasaa_cart_{businessSlug}`.

## Seeded Manual Payments

The default seed creates placeholder manual payment methods. They intentionally use safe demo destination accounts only; replace them through dashboard configuration for real deployments and do not commit real payment account data.

## Paymob Local Testing

`PAYMOB_FAKE_MODE=true` lets local and automated tests initiate Paymob payments without real credentials. Real Paymob credentials must be configured only in `.env`, never committed.
