# Operations Notes

## Health

Use `GET /api/v1/health` for uptime checks. The endpoint returns `status`, `application`, `environment`, and `timestamp` without secrets.

## Routine Verification

Backend:

```bash
php artisan migrate:fresh --seed
php artisan test
```

Frontend:

```bash
npm run lint
npm run build
```

## Business Unit Operations

Business-unit modules control whether public and dashboard features are visible for each unit. Keep feature enablement aligned with seeded modules and permissions.

## Reports And Exports

Executive reports are scoped by business unit where provided. Order exports produce CSV snapshots for operational review and should be treated as business data.

## Incident Handling

For authentication, authorization, payment, RFQ, real estate, or merchandising incidents:

1. Check audit logs for actor/action/subject context.
2. Verify business-unit module enablement.
3. Confirm permissions and assigned business units.
4. Reproduce with the focused feature test where possible.
5. Add a regression test before applying fixes.
