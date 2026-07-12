# V1 Acceptance Checklist

## Architecture

- [x] Laravel 12 backend.
- [x] Next.js frontend.
- [x] MySQL-compatible migrations.
- [x] One backend, one frontend, one database.
- [x] Modular monolith with module-scoped routes, controllers, resources, models, seeders, and tests.
- [x] Business-unit scoped data access for dashboard and public flows.

## Business Scope

- [x] Oils and lubricants catalog, commerce, payments, inventory, and wholesale foundations.
- [x] Ghosoun Dates merchandising, bundles, collections, seasonal store, and corporate gifting.
- [x] Real estate foundation for projects, properties, units, leads, appointments, reservations, and installment plans.
- [x] Import/export services and RFQ foundation.
- [x] Reports, order CSV exports, and audit logs.
- [x] Production readiness polish and final documentation.

## Verification

- [x] Phase 10 regression tests passed before commit.
- [x] Phase 11 regression tests passed before commit.
- [x] Phase 12 regression tests passed before commit.
- [x] Phase 13 regression tests passed before commit.
- [x] Phase 14 readiness test added and passed.
- [ ] Final full backend suite run on release candidate.
- [ ] Final frontend lint run on release candidate.
- [ ] Final frontend production build run on release candidate.
- [ ] Final migration refresh and seed run on release candidate.
- [ ] `master` merged, pushed, and verified equal to `origin/master`.

## Release Gate

Do not mark V1 released until every unchecked item above is complete in the target environment.
