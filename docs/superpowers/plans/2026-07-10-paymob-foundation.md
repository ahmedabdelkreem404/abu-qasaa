# Paymob Payment Foundation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add Paymob online payment initiation and callback-driven confirmation to the existing Payments module.

**Architecture:** Payment logic stays in the Laravel Payments module behind provider/client/verifier services. Public frontend initiation redirects to Paymob URLs, while callback verification alone updates paid/failed status.

**Tech Stack:** Laravel 12, Laravel HTTP client, MySQL, Next.js, TypeScript, Tailwind CSS.

## Global Constraints

- Real Paymob credentials come only from `.env`.
- Fake mode is used for automated tests.
- Return URL never marks an order paid.
- No card data collection or frontend secrets.
- No inventory, shipping, real estate, RFQ, microservices, or DevOps complexity.

---

### Task 1: Backend Provider Foundation

**Files:** config, enums, migration, models, Paymob client/config/verifier/mapper.

- [ ] Add provider/status fields to `payments` and `payment_transactions`.
- [ ] Add `PaymentProvider`, Paymob method types, and transaction types.
- [ ] Add `PaymobConfig`, `PaymobClient`, `PaymobSignatureVerifier`, and `PaymobPayloadMapper`.
- [ ] Add fake mode that returns deterministic checkout/session references.

### Task 2: Backend Actions And Routes

**Files:** Paymob actions, controller, resources, routes.

- [ ] Add initiation action with business-unit/module/setting checks.
- [ ] Add callback action with HMAC validation and idempotent updates.
- [ ] Add return/status actions that never mark paid.
- [ ] Add public and dashboard routes.

### Task 3: Tests And Seeders

**Files:** `PaymentSeeder`, `.env.example`, feature tests.

- [ ] Seed Paymob Card for product business units in fake mode.
- [ ] Add public initiation, forbidden, callback, idempotency, invalid signature, and dashboard tests.

### Task 4: Frontend And Docs

**Files:** API client, public payment page, return page, tracking/dashboard pages, docs.

- [ ] Add Paymob initiation/status methods and types.
- [ ] Add online payment button and redirect behavior.
- [ ] Add return processing page and payment status messaging.
- [ ] Update docs and run full backend/frontend checks.
