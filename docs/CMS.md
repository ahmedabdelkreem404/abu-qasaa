# CMS

## Scope

The CMS foundation supports company pages, business-unit landing pages, reusable page sections, menus, and contact inquiries. It is intentionally limited to public website content and dashboard management; ecommerce, payments, inventory, RFQ, and real estate workflows remain separate roadmap phases.

## Backend

The CMS backend lives in `backend/app/Modules/CMS`:

- `Domain`: enums for page status, page type, section type, and inquiry status.
- `Application`: actions for page listing, publishing, section updates, public lookup, and inquiry workflow.
- `Infrastructure`: Eloquent models for pages, sections, menus, menu items, and inquiries.
- `Presentation`: API routes, controller, form requests, and resources.

Protected dashboard routes use Sanctum and permission middleware. Super Admin can manage company and business-unit content. Other users must have an active assignment for the target business unit.

## Frontend

The public website renders CMS content for:

- `/`
- `/about`
- `/business-units`
- `/[businessSlug]`
- `/contact`

The dashboard CMS area supports page listing, creation, editing, publishing, JSON section editing, and contact inquiry status updates.

## Seeding

Run:

```bash
cd backend
php artisan migrate:fresh --seed
```

Seeded CMS content includes company pages, business-unit landing pages for active units, and a main navigation menu.

## Limitations

- Section editing is JSON-based in the dashboard.
- Menu management APIs exist, but a full visual menu editor is not yet implemented.
- Media uploads, revisions, preview mode, and localization workflows are not yet implemented.
