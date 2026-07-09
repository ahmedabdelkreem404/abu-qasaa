# Abnaa Abu Qasaa Trading Platform

Abnaa Abu Qasaa Trading is an umbrella business platform for managing multiple current and future business units from one modular monolith.

## Tech Stack

- Backend: Laravel 12
- Frontend: Next.js, TypeScript, Tailwind CSS
- Database: MySQL
- Architecture: API-first modular monolith with Clean Architecture-inspired module boundaries

## Folder Structure

```text
backend/   Laravel API application
frontend/  Next.js public site and dashboard application
docs/      Architecture, database, API, setup, and roadmap notes
scripts/   Local helper scripts
```

## Local Setup

Backend:

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Frontend:

```bash
cd frontend
npm install
cp .env.example .env.local
npm run dev
```

## Development Workflow

Build backend features inside `backend/app/Modules/{ModuleName}` using Domain, Application, Infrastructure, and Presentation layers where useful. Build frontend features inside matching `frontend/src/{feature}` folders and expose screens through `frontend/src/app`.

## Architecture Summary

The platform uses one backend, one frontend, and one MySQL database. Business units are represented as data through `business_units`, `activity_templates`, and enabled modules. The system is prepared for oils and lubricants, import/export RFQs, dates ecommerce, real estate, CMS, payments, inventory, reports, and audit logs without splitting into microservices.

## Phase 1 Foundation

Implemented in this phase:

- Business Units API and dashboard management pages.
- Activity Templates and Activity Modules.
- Business Unit Modules for enabling/disabling capabilities.
- Business Unit Settings and Feature Flags.
- Seed data for the four current business units.
- Public business unit lookup pages.

Seed the foundation data:

```bash
cd backend
php artisan migrate:fresh --seed
```

Phase 2 should focus on ecommerce/catalog workflows for oils and dates. Auth, permissions, and richer CMS work remain follow-up work.

## Git Workflow

Use focused branches and small commits. Keep generated secrets out of Git. Commit foundation changes with clear messages and open pull requests for review once a remote is configured.
