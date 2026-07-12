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

## Release Checks

Before production release:

- Set `APP_DEBUG=false`.
- Rotate seeded/demo passwords.
- Configure HTTPS and secure cookies.
- Re-run backend tests, frontend lint, frontend build, and migrations.
- Review Paymob/live payment credentials outside Git.
