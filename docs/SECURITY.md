# Security Notes

## Authentication

Dashboard APIs use Laravel Sanctum bearer tokens. Inactive users cannot log in, invalid credentials return a generic message, and login attempts are throttled by email and IP.

## Authorization

Protected APIs use permission middleware and business-unit scoping. Super admin users can bypass business-unit scope intentionally; assigned admins see only their allowed units.

## Audit Logs

V1 audit logging records actor, business unit, action, subject, IP, user agent, and structured metadata. Sensitive metadata keys such as passwords, tokens, secrets, authorization headers, API keys, HMAC values, and card fields are redacted before persistence.

## Uploads

Use `SafeUploadService` for media/document storage. V1 allows only JPEG, PNG, WebP, and PDF uploads, generates UUID filenames, and strips unsafe directory traversal patterns from storage paths.

## Public Surface

Public APIs are limited to business-unit discovery, published CMS/catalog/commerce flows, payments status flows, wholesale applications, real estate lead intake, and RFQ intake. Dashboard management remains authenticated.

## Staging And Production Headers

Phase 15 expects deployment targets to send safe baseline headers:

- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `X-Frame-Options: SAMEORIGIN` or an equivalent `frame-ancestors` policy
- `Permissions-Policy` with unnecessary browser features disabled

Do not enable a strict Content Security Policy until Paymob redirect and iframe origins are confirmed for the target environment. Paymob origins must be explicitly reviewed before production card payments are enabled.

## CORS And Cookies

Restrict `CORS_ALLOWED_ORIGINS` to the frontend domain for staging and production. Use HTTPS and `SESSION_SECURE_COOKIE=true` for production cookies. Frontend public environment variables must not contain backend secrets.

## Release Checks

Before production release:

- Set `APP_DEBUG=false`.
- Rotate seeded/demo passwords.
- Configure HTTPS and secure cookies.
- Re-run backend tests, frontend lint, frontend build, and migrations.
- Review Paymob/live payment credentials outside Git.
- Run the staging smoke checklist in `docs/STAGING_ACCEPTANCE_CHECKLIST.md`.
- Confirm no P0/P1 findings remain open.
