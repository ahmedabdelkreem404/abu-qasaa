# Setup

## Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Configure MySQL in `backend/.env` before running migrations.

## Frontend

```bash
cd frontend
npm install
cp .env.example .env.local
npm run dev
```

The frontend reads `NEXT_PUBLIC_API_URL`, which defaults to `http://localhost:8000/api/v1`.

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
