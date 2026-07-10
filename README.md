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

Auth, permissions, and richer CMS work remain follow-up work from the broader foundation track.

## Phase 2 Auth Foundation

The platform uses Laravel Sanctum bearer tokens for dashboard authentication. Seeded local development credentials are controlled by safe `.env` defaults:

```text
SUPER_ADMIN_EMAIL=admin@abuqasaa.test
SUPER_ADMIN_PASSWORD=password
DEMO_USER_PASSWORD=password
```

Local demo users:

- `admin@abuqasaa.test`
- `oils.admin@abuqasaa.test`
- `dates.admin@abuqasaa.test`
- `realestate.admin@abuqasaa.test`
- `importexport.admin@abuqasaa.test`

Protected dashboard APIs require `Authorization: Bearer <token>`. Public business unit lookup endpoints remain unauthenticated.

## Phase 3 CMS And Public Website Foundation

The CMS module now powers the public website pages and business-unit landing pages from seeded content instead of frontend hardcoding. It includes:

- Company pages for `/`, `/about`, and `/contact`.
- Business-unit landing content for `/[businessSlug]`.
- CMS sections, menus, and contact inquiries.
- Dashboard CMS pages, section editing, publishing, and inquiry status workflow.
- Public APIs for published CMS pages, menus, business-unit pages, and contact inquiry submission.

Run `php artisan migrate:fresh --seed` from `backend/` to create the CMS seed content.

## Phase 4 Product Catalog Foundation

The Catalog module now supports product-based business units with business-unit scoped categories, brands, products, variants, product images, price lists, and product prices.

Implemented catalog scope:

- Dashboard APIs and screens for products, categories, brands, and price lists.
- Public product listing/detail pages under `/{businessSlug}/products`.
- Featured products on business-unit landing pages when the `products` module is enabled.
- Seed catalog data for Oils & Lubricants and Ghosoun Dates.

This phase does not include cart, checkout, orders, payments, or inventory stock movements.

## Phase 5 Cart And Orders Foundation

Commerce foundation now supports public carts, checkout request submission, customer snapshots, and dashboard order management.

- Public users can create a business-unit cart, add/update/remove items, and submit checkout details.
- Orders preserve item names, SKUs, variant labels, unit prices, quantities, and totals at order time.
- Dashboard users can view scoped orders/customers and update order status.
- No online payment, Paymob, manual proof review, shipping provider, or inventory deduction is implemented yet.

## Phase 6 Manual Payments Foundation

Payments now support business-unit scoped manual methods for existing orders:

- Seeded Vodafone Cash, Instapay, Bank Transfer, Cash on Delivery, and inactive Paymob placeholders.
- Public payment options and manual proof submission by order number plus matching phone.
- Dashboard payment methods, payment records, manual proof review, approval, and rejection.
- COD selection remains pending/unpaid until an admin marks collection paid.

Paymob, card payments, wallet callbacks, automatic verification, inventory deduction, and shipping integrations remain outside this phase.

## Git Workflow

Use focused branches and small commits. Keep generated secrets out of Git. Commit foundation changes with clear messages and open pull requests for review once a remote is configured.
