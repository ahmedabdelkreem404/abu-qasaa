# Abu Qasaa V1 Completion Design

## Approved Scope

This design implements the approved V1 completion prompt for Phases 10 through 14. The source of truth is the V1 completion prompt approved on 2026-07-12, including its exclusions, acceptance criteria, verification commands, documentation requirements, and final Git workflow.

## Architecture

Abu Qasaa remains a modular monolith: one Laravel 12 backend, one Next.js frontend, one MySQL database, and business-unit scoped modules. Backend work follows the existing module structure under `backend/app/Modules` with Domain enums, Application actions, Infrastructure Eloquent models, Presentation controllers/requests/resources/routes, additive migrations, seeders, and focused tests. Frontend work follows the existing Next.js App Router route groups and shared dashboard/public API clients.

## Phase 10: Dates Store Merchandising

Catalog gains generic merchandising capabilities used by Ghosoun Dates: badges, collections, bundles, gift metadata, and corporate gift inquiries. Existing untracked Phase 10 migration/enums are preserved and reconciled with the approved V1 contract: collection statuses are `active`, `draft`, and `archived`; bundle types are `fixed_box`, `corporate_box`, `seasonal_box`, and `simple_bundle`; pricing modes are `use_parent_product_price` and `fixed_bundle_price`.

Public APIs expose collections, featured products, gift products, seasonal products, corporate gift products, and inquiry submission. Dashboard APIs manage badges, collections, bundles, and inquiries with products/leads permissions, products module enforcement, and business-unit scoping. Cart and order snapshots include bundle metadata while inventory continues to reserve the parent bundle product only.

## Phase 11: Real Estate Foundation

The RealEstate module becomes a complete V1 foundation for projects, properties, units, leads, appointments, reservations, and installment plans. Public APIs expose active project/unit browsing and lead/viewing/reservation-interest submission without leaking private lead or dashboard notes. Dashboard APIs provide scoped CRUD, status changes, assignment, appointments, reservations, and installment plan management protected by real-estate, leads, and appointment permissions.

Unit reservations prevent conflicts for already reserved or sold units, update unit status through controlled transitions, and reject cross-business-unit IDs.

## Phase 12: Import/Export Services and RFQ

The ServicesRfq module becomes a complete services and request-for-quotation workflow. It includes services, RFQ requests, RFQ items, private/public RFQ documents, quotations, quotation items, and activity history. Public APIs support service browsing, multi-item RFQ submission, safe document upload, and status lookup by reference plus contact verification. Dashboard APIs support service management, RFQ review, status transitions, assignment, document handling, quotation creation, quotation totals, and activity timelines.

Uploads use Laravel storage, generated safe names, MIME and size validation, and private access rules for non-public documents.

## Phase 13: Reports, Exports, and Audit Logs

The Audit module records important operational changes while redacting passwords, tokens, API keys, secrets, card data, and authorization headers. Audit failures do not corrupt the main transaction. The Reports module exposes executive, commerce, inventory, and leads reports with business-unit scoping, permission checks, date filters, pagination, and UTF-8 Arabic-safe CSV exports.

Dashboard pages expose report filters, export actions, and audit log browsing with loading, empty, error, and forbidden states.

## Phase 14: Production Readiness

Production readiness closes auth hardening, user management, secure upload/media foundation, Google Fonts independence, standardized API errors, health endpoint, public/dashboard error pages, rate limits, security hardening, database indexes, responsive RTL/LTR UI, feature flag enforcement, environment documentation, deployment documentation, security documentation, operations documentation, V1 audit, and V1 acceptance checklist.

The existing local Google Fonts removal in `frontend/src/app/layout.tsx` and local/system font stack in `frontend/src/app/globals.css` are preserved and committed as a focused production build fix.

## Verification

Each phase must include migrations, seed data where required, backend tests for scoping and privacy, route verification, frontend lint/build checks where affected, and a focused commit. The final verification suite is the backend and frontend command set from the approved prompt. The project may only be reported as `V1 agreed scope is 100% complete` if all definition-of-done criteria pass and local `HEAD` equals `origin/master`.

## V1 Exclusions

The following remain out of V1: microservices, Kubernetes, complex CI/CD, native mobile apps, multi-vendor marketplace, loyalty, subscriptions, advanced coupons, full ERP accounting, procurement accounting, manufacturing/BOM, batch/lot/expiry tracking, barcode hardware, commissions, advanced CRM automation, SMS OTP, application-owned card handling, advanced shipping integration, advanced real-estate legal/accounting workflows, automatic bank reconciliation, and AI features.
