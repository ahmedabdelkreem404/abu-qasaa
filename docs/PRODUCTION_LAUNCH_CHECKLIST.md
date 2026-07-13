# Production Launch Checklist

Production launch status: not approved.

Reason: external staging deployment and staging smoke tests have not yet been executed with real staging URLs and credentials.

## Infrastructure

- [ ] Production domain and DNS configured.
- [ ] HTTPS configured and tested.
- [ ] Server time and timezone verified.
- [ ] Production database created.
- [ ] Database backup verified.
- [ ] Storage directories writable.
- [ ] Queue worker configured if `QUEUE_CONNECTION` requires it.
- [ ] Scheduler configured if future scheduled commands are added.
- [ ] Logs monitored.
- [ ] Disk space checked.
- [ ] Separate staging database and storage confirmed.

## Backend

- [ ] `APP_ENV=production`.
- [ ] `APP_DEBUG=false`.
- [ ] Strong `APP_KEY` generated outside Git.
- [ ] `APP_URL` configured.
- [ ] `FRONTEND_URL` configured.
- [ ] `CORS_ALLOWED_ORIGINS` restricted.
- [ ] `SESSION_SECURE_COOKIE=true`.
- [ ] Database backup taken before migration.
- [ ] `php artisan migrate --force` applied.
- [ ] `php artisan storage:link` verified.
- [ ] `php artisan config:cache` run.
- [ ] `php artisan route:cache` run.
- [ ] `php artisan view:cache` run.
- [ ] `/api/v1/health` checked.
- [ ] No stack traces or secret values in API responses.

## Frontend

- [ ] `NEXT_PUBLIC_API_URL` uses production API URL.
- [ ] `NEXT_PUBLIC_API_BASE_URL` matches production API URL if used by operators.
- [ ] `NEXT_PUBLIC_APP_URL` uses production frontend URL.
- [ ] Production build completed.
- [ ] Sitemap route checked.
- [ ] Robots route checked.
- [ ] Metadata checked.
- [ ] No external Google Fonts dependency.
- [ ] No staging URLs in the production build configuration.

## Payment

- [ ] Paymob production credentials entered only in backend server environment.
- [ ] Paymob API key configured.
- [ ] Paymob integration ID configured.
- [ ] Paymob iframe ID configured.
- [ ] Paymob HMAC secret configured.
- [ ] Callback URL verified.
- [ ] Return URL verified.
- [ ] Small controlled production transaction tested.
- [ ] Refund/cancellation operating procedure documented.
- [ ] Manual payment account values verified.

## Operations

- [ ] Administrator accounts verified.
- [ ] Default/demo passwords changed.
- [ ] Backup schedule enabled.
- [ ] Recovery process tested.
- [ ] Rollback commit/tag identified.
- [ ] Contact person assigned.
- [ ] Launch monitoring window defined.
- [ ] P0 findings closed.
- [ ] P1 findings closed.
- [ ] P2 findings accepted or scheduled.
- [ ] P3 findings placed in post-launch backlog.

## Current Blockers

| ID | Severity | Blocker | Required Action |
| --- | --- | --- | --- |
| PROD-001 | P1 | Staging deployment has not been executed. | Deploy to staging using the runbook. |
| PROD-002 | P1 | Staging smoke checklist is pending. | Complete `docs/STAGING_ACCEPTANCE_CHECKLIST.md`. |
| PROD-003 | P1 | Production environment credentials and server access are not present in this repository environment. | Operator must provide credentials outside Git and execute deployment. |
| PROD-004 | P2 | `npm audit --omit=dev` reports moderate PostCSS advisories through Next.js. | Review and upgrade through a safe Next.js patch path before or after launch according to risk acceptance. |

## Launch Decision

Production launch not yet approved.
