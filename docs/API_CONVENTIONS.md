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

Phase 1 adds `/api/v1/business-units`, `/api/v1/activity-templates`, `/api/v1/activity-modules`, `/api/v1/feature-flags`, and `/api/v1/public/business-units` endpoints. Admin routes include TODO comments for Sanctum and authorization middleware in the auth phase.

## Auth Notes

The backend is prepared for API authentication suitable for a Next.js frontend. Laravel Sanctum is the preferred first implementation for cookie/session or token-based auth, depending on deployment needs.

## Business Unit Scoping

Business-specific endpoints must resolve the current business unit from route parameters, headers, authenticated access, or dashboard context before querying scoped data.
