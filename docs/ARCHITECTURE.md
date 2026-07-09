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
