# Architecture

Abnaa Abu Qasaa Trading is structured as a modular monolith: one Laravel backend, one Next.js frontend, and one MySQL database. The codebase is intentionally prepared for expansion without introducing microservices, Kubernetes, or heavy DevOps concerns.

## Why Modular Monolith

The platform needs shared identity, reporting, payments, inventory, catalog, CMS, and business unit configuration. A modular monolith keeps these capabilities in one deployable system while still separating ownership through module folders, contracts, DTOs, actions, routes, and migrations.

## Why Not Microservices

Microservices would add distributed transactions, duplicated auth, network failure modes, and deployment overhead before the business workflows are mature. The current foundation favors fast iteration and clear module boundaries inside one Laravel application.

## Backend Modules

Backend modules live under `backend/app/Modules`: `Core`, `Identity`, `BusinessUnits`, `Catalog`, `Commerce`, `Inventory`, `Payments`, `CMS`, `ServicesRfq`, `RealEstate`, `Notifications`, `Reports`, and `Audit`.

Each module follows Clean Architecture layers where useful: `Domain`, `Application`, `Infrastructure`, and `Presentation`.

## Frontend Structure

The frontend uses the Next.js App Router with route groups for public pages, business unit pages, and dashboard pages. Feature folders under `src/` mirror the backend module language so frontend work can grow around business capabilities instead of page-only code.

## Business Unit Extensibility

Business units are data, not separate applications or tables. A future Super Admin workflow will create a business unit, select an activity template, enable modules, and configure settings or feature flags.

Phase 1 now implements this control plane in the `BusinessUnits` module: business units, activity templates, activity modules, module assignments, settings, feature flags, seeders, public lookups, and dashboard management screens. Controllers remain thin and delegate writes to application actions.

## Authentication And Authorization

Phase 2 adds Laravel Sanctum token authentication and a custom role/permission layer in the `Identity` module. Global roles attach through `user_roles`; business-unit scoped roles attach through `user_business_units`.

Super Admin bypasses business-unit scope. Other users must have an active business unit assignment and the required permission for protected actions. Future modules should enforce permissions in backend middleware or policies; frontend permission checks are for navigation and user experience only.

## API First

The Laravel backend exposes `/api/v1/*` routes for the Next.js frontend. Protected dashboard APIs use Sanctum bearer tokens.

## CMS And Public Website

Phase 3 adds a `CMS` module with Clean Architecture-inspired boundaries: enums in `Domain`, actions in `Application`, Eloquent models in `Infrastructure`, and requests/resources/controllers/routes in `Presentation`.

CMS pages may be company-level or scoped to a business unit. Public routes only expose published pages and active sections. Dashboard CMS routes require Sanctum plus CMS or lead permissions, and non-super-admin users are limited to their assigned business units.

The Next.js public routes render CMS data for `/`, `/about`, `/contact`, and business-unit pages. The dashboard CMS screens call the same API client used by the rest of the admin app.

## Catalog Foundation

Phase 4 adds a real `Catalog` module around the existing modular monolith boundaries. Catalog records are scoped by `business_unit_id`, and dashboard writes require both permission checks and an enabled `products` module for the target business unit.

The Catalog module owns categories, brands, products, variants, images, price lists, and product prices. Flexible product specs remain JSON-based through `specs_json` and variant `option_values_json`; there is no attribute-builder subsystem yet.

Public product APIs resolve the business-unit slug, require an active business unit with the `products` module enabled, expose only published/public products, and omit admin-only cost fields.

## Commerce Foundation

Phase 5 adds public cart and order request flows inside the `Commerce` module. Carts and orders are scoped to one business unit, and checkout requires active product and order modules plus enabled checkout settings.

Orders store pricing snapshots from cart items so later catalog price changes do not alter historical totals. Payment and fulfillment fields exist for future phases, but no payment processing, inventory deduction, or shipping integration is performed.

## Payments Foundation

Phase 6 adds a real `Payments` module inside the same modular monolith. Payment methods, payments, payment transactions, and manual proofs are business-unit scoped and exposed through API-first public and dashboard routes.

Controllers remain thin and delegate manual proof submission, review, COD selection, and admin mark-paid flows to application actions. No Paymob provider, card payment callback flow, microservice, or DevOps-heavy setup is introduced in this phase.

Phase 7 extends the same module boundary with a Paymob provider interface, client, signature verifier, and payload mapper. The provider supports fake mode for local/test execution and keeps exact Paymob field assumptions isolated for later replacement when direct official docs access is available.

## Inventory Foundation

Phase 8 adds the `Inventory` module for business-unit scoped branches, warehouses, stock items, stock movements, reservations, and transfers. Commerce calls Inventory for reservation, release, and fulfillment, while Inventory does not own pricing, payment confirmation, shipping, RFQ, real estate, or supplier procurement.
