# API Conventions

## Versioning

All public API routes are versioned under `/api/v1`.

## Success Response

```json
{
  "success": true,
  "message": "OK",
  "data": {}
}
```

## Error Response

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {}
}
```

## Pagination

Paginated responses should return records in `data` and pagination details in `meta`, including `current_page`, `per_page`, `total`, and `last_page`.

## Phase 1 Routes

Phase 1 adds `/api/v1/business-units`, `/api/v1/activity-templates`, `/api/v1/activity-modules`, `/api/v1/feature-flags`, and `/api/v1/public/business-units` endpoints.

Phase 2 protects dashboard/admin endpoints with Sanctum bearer tokens and permission middleware.

Public endpoints:

- `GET /api/v1/public/business-units`
- `GET /api/v1/public/business-units/{slug}`

Auth endpoints:

- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `GET /api/v1/auth/me`

Unauthorized response:

```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

Forbidden response:

```json
{
  "success": false,
  "message": "Forbidden.",
  "errors": {}
}
```

## Auth Notes

The backend uses Laravel Sanctum personal access tokens for the dashboard. Send `Authorization: Bearer <token>` for protected endpoints.

## Business Unit Scoping

Business-specific endpoints must resolve the current business unit from route parameters, headers, authenticated access, or dashboard context before querying scoped data.

## CMS Routes

Public CMS endpoints:

- `GET /api/v1/public/cms/pages`
- `GET /api/v1/public/cms/pages/{slug}`
- `GET /api/v1/public/cms/business-units/{businessSlug}/page`
- `GET /api/v1/public/cms/menus/{location}`
- `POST /api/v1/public/contact-inquiries`

Protected CMS endpoints:

- `GET /api/v1/cms/pages`
- `POST /api/v1/cms/pages`
- `GET /api/v1/cms/pages/{cmsPage}`
- `PATCH /api/v1/cms/pages/{cmsPage}`
- `DELETE /api/v1/cms/pages/{cmsPage}`
- `POST /api/v1/cms/pages/{cmsPage}/publish`
- `PUT /api/v1/cms/pages/{cmsPage}/sections`
- `GET /api/v1/cms/contact-inquiries`
- `PUT /api/v1/cms/contact-inquiries/{contactInquiry}/status`

Dashboard CMS endpoints return `Forbidden.` when the authenticated user has the permission but not the required business-unit scope.
