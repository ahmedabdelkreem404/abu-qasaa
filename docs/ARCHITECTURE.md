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

## API First

The Laravel backend exposes `/api/v1/*` routes for the Next.js frontend. Sanctum is the preferred authentication path once auth work begins.
