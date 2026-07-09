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
