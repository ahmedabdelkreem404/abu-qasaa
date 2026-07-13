# Abu Qasaa Local Acceptance Report

Test date: 2026-07-13

Scope: local XAMPP runtime verification for the Abu Qasaa repository only. `_external/Cashiry` was not touched. No deployment was performed.

## Environment

| Item | Value |
| --- | --- |
| Git base before fixes | `6dfbf0615a4e8be819b40e542336022169a1c52b` / `v1.0.2` |
| Branch used for fixes | `codex/local-runtime-fixes` |
| XAMPP path | `C:\xampp` |
| PHP | `8.2.12` |
| MariaDB | `10.4.32-MariaDB` |
| Node.js | `v22.14.0` |
| npm | `10.9.2` |
| Database | `abu_qasaa` |
| Backend URL | `http://127.0.0.1:8000` |
| Frontend URL | `http://localhost:3000` |
| Health URL | `http://127.0.0.1:8000/api/v1/health` |
| Paymob mode | Fake/local mode |

Local env files were configured for XAMPP/MariaDB and local frontend API access. Secrets and local passwords are intentionally not recorded in this committed report.

## Runtime Status

| Service | Result | Evidence |
| --- | --- | --- |
| Apache | PASS | Listening on port `80`, PID `30468`. |
| MySQL/MariaDB | PASS | Listening on port `3306`, PID `10360`. |
| Laravel API | PASS | Listening on port `8000`, health returned `200`. |
| Next frontend | PASS | Listening on port `3000`, public routes returned `200`. |
| Super Admin login | PASS | API login succeeded; browser login landed on `/dashboard`. |
| Permissions | PASS | Super Admin returned `52` permissions. |

Runtime logs/screenshots are local-only under `runtime-logs/`.

## Database And Seed

`php artisan migrate:fresh --seed --force` completed successfully against MariaDB after the migration-order fix.

| Seed area | Result |
| --- | ---: |
| Business units | 4 |
| Activity modules | 25 |
| Users | 5 |
| Roles | 9 |
| Permissions | 52 |
| Products | 9 |
| Price lists | 4 |
| Warehouses | 2 |
| Payment methods | 18 |

Seeded business units:

| Slug | Type | Status |
| --- | --- | --- |
| `dates` | `product_store` | `active` |
| `import-export` | `services_rfq` | `active` |
| `oils` | `wholesale_store` | `active` |
| `real-estate` | `real_estate` | `active` |

## Live Workflow Smoke

Final live smoke after the final seed completed `66` HTTP checks with no warnings.

Created local references:

| Workflow | Reference |
| --- | --- |
| Contact inquiry | `1` |
| Dates retail order | `DAT-202607-000001` |
| Manual proof | `1` |
| Oils wholesale order | `OIL-202607-000001` |
| Real-estate lead | `1` |
| Real-estate viewing request | `1` |
| Real-estate reservation interest | `1` |
| Import/export RFQ | `RFQ-IMP-20260713021259-979` |
| RFQ quotation | `1` |

Live activities verified:

| Area | Result | Notes |
| --- | --- | --- |
| Health and routing | PASS | Health endpoint and frontend public/dashboard routes returned `200`. |
| Auth | PASS | Login, `/auth/me`, logout, and login-after-logout verified. |
| CMS | PASS | Public pages, main menu, contact inquiry, dashboard inquiry list. |
| Dates retail | PASS | Product list, cart, checkout, order lookup. |
| Payments | PASS | Public methods, Paymob fake initiation, invalid callback rejection, manual proof submission and approval. |
| Inventory | PASS | Summary, warehouses, stock list, receive, adjustment, order cancellation. |
| Oils wholesale | PASS | Unauthenticated wholesale product denial, access token, private product list, minimum quantity rejection, wholesale checkout, application, dashboard list. |
| Real estate | PASS | Projects, units, lead, viewing request, reservation, duplicate reservation conflict. |
| Import/export RFQ | PASS | Services, RFQ submit, verified status lookup, wrong-contact privacy, dashboard RFQ list, quotation creation, quotation send. |
| Reports/audit | PASS | Executive summary, orders CSV export, audit logs. |
| Permission guards | PASS | Unauthenticated inventory and wholesale dashboard requests returned `401`; scoped RFQ dashboard access verified. |

Browser verification:

| Item | Result |
| --- | --- |
| `/login` renders | PASS |
| Super Admin login through Chrome | PASS |
| Dashboard render | PASS |
| Final URL | `http://localhost:3000/dashboard` |

## Automated Checks

| Command | Result |
| --- | --- |
| `composer install` | PASS |
| `composer dump-autoload -o` | PASS |
| `php artisan migrate:fresh --seed --force` | PASS |
| `php artisan test` | PASS, `118 passed (795 assertions)` |
| `php vendor/bin/pint --test` | PASS |
| `composer audit` | PASS, no advisories |
| `npm ci` | PASS |
| `npm run lint` | PASS |
| `npm run build` | PASS |

## Fixes Applied

| Severity | Finding | Status |
| --- | --- | --- |
| P0 | MariaDB failed `migrate:fresh --seed` because `customers.price_list_id` referenced `price_lists` before `price_lists` existed. | Fixed by creating the column first and adding the foreign key after `price_lists` is created. Regression test added. |
| P1 | Auth tests assumed hardcoded default seed password and failed under configured local runtime password. | Fixed tests to use the configured seed password. |
| P1 | Paymob test assumed fake reference always equals `fake-ref-1`, which is brittle after real local runtime records and auto-increment drift. | Fixed test to assert fake reference format. |
| P2 | Manual proof approval is idempotent: duplicate approval returns `200` with the approved proof. | Verified behavior; not blocking. |
| P3 | Public RFQ document upload is not exposed as multipart in current V1 endpoints. | Not applicable to current implemented API surface. |

No P0/P1 blockers remain open.
