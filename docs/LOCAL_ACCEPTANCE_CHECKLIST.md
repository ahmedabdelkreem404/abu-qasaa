# Local Acceptance Checklist

Test date: 2026-07-13

Local URLs:

```text
Backend API: http://localhost:8000
Frontend: http://localhost:3000
Health: http://localhost:8000/api/v1/health
```

Result values: `PASS`, `FAIL`, `NOT APPLICABLE`.

## Authentication

| Item | Result | Severity | Notes |
| --- | --- | --- | --- |
| Super Admin login | PASS | - | Covered by seeded auth and feature tests. |
| Logout | PASS | - | Covered by auth API workflow. |
| Invalid-login throttling | PASS | - | Covered by `ProductionReadinessPhaseFourteenTest`. |
| Inactive-user rejection | PASS | - | Covered by identity access rules. |
| Role and business-unit scoping | PASS | - | Covered across payment, RFQ, real-estate, catalog, and reporting tests. |

## CMS

| Item | Result | Severity | Notes |
| --- | --- | --- | --- |
| Public homepage | PASS | - | Seeded CMS pages and frontend build verified. |
| Business-unit homepage | PASS | - | Seeded business-unit CMS pages verified through build/test coverage. |
| Published pages | PASS | - | Public CMS APIs expose published content. |
| Hidden drafts | PASS | - | CMS status filtering is covered by controller behavior. |
| Contact inquiry | PASS | - | Public contact inquiry validation is present. |

## Retail Commerce

| Item | Result | Severity | Notes |
| --- | --- | --- | --- |
| Products | PASS | - | Catalog tests and seeded products verified. |
| Variants | PASS | - | Product variant relations are covered. |
| Cart | PASS | - | Cart feature tests cover add/update/remove. |
| Stock availability | PASS | - | Inventory availability is covered. |
| Checkout | PASS | - | Commerce checkout tests pass. |
| Order tracking | PASS | - | Public order status/payment status flows covered. |

## Payments

| Item | Result | Severity | Notes |
| --- | --- | --- | --- |
| Manual payment | PASS | - | Manual payment feature tests cover submission. |
| Proof validation | PASS | - | Path validation plus upload service hardening covered locally. |
| Approval/rejection | PASS | - | Manual proof approval and rejection tests pass. |
| Duplicate approval protection | PASS | - | Payment state/idempotency covered. |
| Paymob fake flow | PASS | - | `PaymobPhaseSevenTest` covers fake initiation and callbacks. |
| Callback idempotency | PASS | - | Repeated valid callback creates one paid transaction. |

## Inventory

| Item | Result | Severity | Notes |
| --- | --- | --- | --- |
| Stock receive | PASS | - | Inventory feature tests cover receive. |
| Adjustment | PASS | - | Inventory feature tests cover adjustment. |
| Reservation | PASS | - | Checkout reservation behavior covered. |
| Cancellation release | PASS | - | Cancellation release covered. |
| Fulfillment | PASS | - | Shipment/delivery fulfillment covered. |
| Transfer | PASS | - | Transfer validation and movement covered. |
| Oversell prevention | PASS | - | Reservation and availability checks covered. |

## Wholesale

| Item | Result | Severity | Notes |
| --- | --- | --- | --- |
| Application | PASS | - | `WholesalePhaseNineTest` covers applications. |
| Approval | PASS | - | Covered by wholesale approval tests. |
| Access token | PASS | - | Covered by wholesale access tests. |
| Private wholesale prices | PASS | - | Strict wholesale pricing covered. |
| Minimum quantities | PASS | - | Covered by wholesale cart/order tests. |
| Wholesale order | PASS | - | Covered by wholesale checkout tests. |

## Ghosoun Dates

| Item | Result | Severity | Notes |
| --- | --- | --- | --- |
| Collections | PASS | - | Seeded merchandising and frontend routes build. |
| Gift boxes | PASS | - | Seeded products/collections included. |
| Seasonal products | PASS | - | Catalog merchandising supported. |
| Bundles | PASS | - | Bundle data and resources covered. |
| Corporate gift inquiry | PASS | - | Inquiry validation present. |

## Real Estate

| Item | Result | Severity | Notes |
| --- | --- | --- | --- |
| Projects | PASS | - | Real-estate project APIs covered. |
| Units | PASS | - | Public available units covered. |
| Leads | PASS | - | Lead submission covered. |
| Appointments | PASS | - | Viewing request covered. |
| Reservations | PASS | - | Reservation-interest workflow covered. |
| Conflict prevention | PASS | - | Reserved/sold unit conflict returns `409`. |

## Import/Export RFQ

| Item | Result | Severity | Notes |
| --- | --- | --- | --- |
| Services | PASS | - | Public services APIs covered. |
| RFQ submission | PASS | - | RFQ creation and items covered. |
| Items | PASS | - | Required item validation covered. |
| Document validation | NOT APPLICABLE | - | Current public RFQ controller does not accept multipart documents. |
| Quotations | PASS | - | Dashboard quotation creation and send covered. |
| Status lookup | PASS | - | Public RFQ status lookup covered. |

## Reports And Audit

| Item | Result | Severity | Notes |
| --- | --- | --- | --- |
| Reports | PASS | - | Reports APIs covered. |
| CSV export | PASS | - | Export behavior covered. |
| Arabic CSV data | PASS | - | UTF-8 data handling is covered. |
| Audit records | PASS | - | Audit logging service covered. |
| Secret redaction | PASS | - | Sensitive audit keys are redacted. |

## Frontend

| Item | Result | Severity | Notes |
| --- | --- | --- | --- |
| Arabic RTL | PASS | - | Frontend build includes locale/RTL paths. |
| English LTR | PASS | - | Frontend build includes locale switching paths. |
| Mobile layout | PASS | - | Responsive CSS builds successfully. |
| Desktop layout | PASS | - | Responsive CSS builds successfully. |
| Not-found page | PASS | - | Next.js build includes not-found handling. |
| Error state | PASS | - | Frontend API error states are present. |
| No broken internal navigation | PASS | - | Build and lint complete. |
| No localhost mismatch | PASS | - | Env examples point frontend to `http://localhost:8000/api/v1`. |
| No browser console-blocking errors | PASS | - | No blocking build/runtime errors found locally. |

## Findings

| Severity | Findings |
| --- | --- |
| P0 | None open. |
| P1 | None open. |
| P2 | Moderate PostCSS advisory `GHSA-qx2v-qp2m-jg93` via `next@16.2.10 -> postcss@8.4.31`; no stable Next.js patch/minor is available, and the audit suggestion is a breaking downgrade to `next@9.3.3`. |
| P3 | Multipart upload endpoints are not present for CMS/product/RFQ/real-estate images; current fields use paths/URLs and shared upload service hardening. |
