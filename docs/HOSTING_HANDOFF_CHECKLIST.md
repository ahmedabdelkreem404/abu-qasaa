# Hosting Handoff Checklist

## Before Deployment

| Item | Result | Notes |
| --- | --- | --- |
| Hosting provider selected | TODO | Fill in `docs/HOSTING_INFORMATION_TEMPLATE.md`. |
| Server OS confirmed | TODO | Must support PHP `^8.2` and Node.js compatible with Next.js `16.2.10`. |
| SSH access confirmed | TODO | Do not commit credentials. |
| Database created | TODO | Dedicated database and user required. |
| Domains configured | TODO | Recommended `www.example.com` and `api.example.com`. |
| SSL certificates available | TODO | HTTPS required. |
| Mail provider available | TODO | Real mail validation is deferred until hosting exists. |
| Paymob mode and credentials available | TODO | Use fake mode until real test/production credentials are intentionally configured. |
| Backup destination available | TODO | Remote backup validation is deferred until hosting exists. |

## Backend Deployment

| Item | Result | Notes |
| --- | --- | --- |
| Backend source uploaded | TODO | Follow `docs/RELEASE_RUNBOOK.md`. |
| Composer install completed | TODO | Use production flags from the runbook. |
| Backend env configured | TODO | Use names from `docs/HOSTING_REQUIREMENTS.md`; never commit secrets. |
| Laravel app key configured | TODO | Generate on the server if missing. |
| Storage permissions set | TODO | PHP process must write storage and bootstrap cache. |
| Public storage link created | TODO | Run `php artisan storage:link`. |
| Migrations run | TODO | Back up first in non-local environments. |
| Caches built | TODO | Config, route, and view cache. |
| Queue process configured | TODO | Required only for non-sync queue drivers. |
| Scheduler configured | TODO | Required only when scheduled commands are enabled. |

## Frontend Deployment

| Item | Result | Notes |
| --- | --- | --- |
| Frontend source uploaded | TODO | Use lockfile install. |
| Frontend env configured | TODO | Public variables only. |
| `npm ci` completed | TODO | Do not use `npm audit fix --force`. |
| `npm run build` completed | TODO | Build must point at the deployed API base URL. |
| Process manager configured | TODO | Keep `npm run start` running. |
| Reverse proxy configured | TODO | Nginx should proxy to the Next.js process. |

## Verification

| Item | Result | Notes |
| --- | --- | --- |
| Backend health URL works | TODO | `https://api.example.com/api/v1/health`. |
| Frontend URL works | TODO | `https://www.example.com`. |
| CORS/Sanctum origins verified | TODO | Browser login must work from the frontend domain. |
| Public storage URL works | TODO | Verify `/storage/...` public files only. |
| Private documents not public | TODO | Private disk files must not be directly browsable. |
| Mail delivery verified | TODO | Real provider only. |
| Paymob callback verified | TODO | Test/production mode only when credentials are supplied. |
| Backup and restore path verified | TODO | Remote backup validation is required before launch. |

## Release Decision

Use this wording until hosting is actually available and verified:

```text
Local release candidate approved.
External deployment and production launch are deferred until hosting infrastructure is available.
```
