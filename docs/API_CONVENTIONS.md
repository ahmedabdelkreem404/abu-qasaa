# API Conventions

## Versioning

All public API routes are versioned under `/api/v1`.

## Success Response

```json
{
  "message": "OK",
  "data": {},
  "meta": {}
}
```

## Error Response

```json
{
  "message": "Validation failed",
  "errors": {}
}
```

## Pagination

Paginated responses should return records in `data` and pagination details in `meta`, including `current_page`, `per_page`, `total`, and `last_page`.

## Auth Notes

The backend is prepared for API authentication suitable for a Next.js frontend. Laravel Sanctum is the preferred first implementation for cookie/session or token-based auth, depending on deployment needs.

## Business Unit Scoping

Business-specific endpoints must resolve the current business unit from route parameters, headers, authenticated access, or dashboard context before querying scoped data.
