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

## Catalog Routes

Protected catalog endpoints:

- `GET /api/v1/catalog/categories`
- `POST /api/v1/catalog/categories`
- `GET /api/v1/catalog/categories/{category}`
- `PATCH /api/v1/catalog/categories/{category}`
- `DELETE /api/v1/catalog/categories/{category}`
- `GET /api/v1/catalog/brands`
- `POST /api/v1/catalog/brands`
- `GET /api/v1/catalog/brands/{brand}`
- `PATCH /api/v1/catalog/brands/{brand}`
- `DELETE /api/v1/catalog/brands/{brand}`
- `GET /api/v1/catalog/products`
- `POST /api/v1/catalog/products`
- `GET /api/v1/catalog/products/{product}`
- `PATCH /api/v1/catalog/products/{product}`
- `DELETE /api/v1/catalog/products/{product}`
- `POST /api/v1/catalog/products/{product}/publish`
- `PUT /api/v1/catalog/products/{product}/variants`
- `PUT /api/v1/catalog/products/{product}/images`
- `PUT /api/v1/catalog/products/{product}/prices`
- `GET /api/v1/catalog/price-lists`
- `POST /api/v1/catalog/price-lists`
- `GET /api/v1/catalog/price-lists/{priceList}`
- `PATCH /api/v1/catalog/price-lists/{priceList}`
- `DELETE /api/v1/catalog/price-lists/{priceList}`

Public catalog endpoints:

- `GET /api/v1/public/{businessSlug}/products`
- `GET /api/v1/public/{businessSlug}/products/{productSlug}`
- `GET /api/v1/public/{businessSlug}/categories`
- `GET /api/v1/public/{businessSlug}/brands`

Public product responses do not expose `cost_price`.

## Commerce Routes

Public commerce endpoints:

- `POST /api/v1/public/{businessSlug}/cart`
- `GET /api/v1/public/{businessSlug}/cart/{sessionToken}`
- `POST /api/v1/public/{businessSlug}/cart/{sessionToken}/items`
- `PUT /api/v1/public/{businessSlug}/cart/{sessionToken}/items/{cartItem}`
- `DELETE /api/v1/public/{businessSlug}/cart/{sessionToken}/items/{cartItem}`
- `DELETE /api/v1/public/{businessSlug}/cart/{sessionToken}/clear`
- `POST /api/v1/public/{businessSlug}/checkout`
- `GET /api/v1/public/{businessSlug}/orders/{orderNumber}?phone=...`

Protected commerce endpoints:

- `GET /api/v1/commerce/customers`
- `POST /api/v1/commerce/customers`
- `GET /api/v1/commerce/customers/{customer}`
- `PATCH /api/v1/commerce/customers/{customer}`
- `GET /api/v1/commerce/orders`
- `GET /api/v1/commerce/orders/{order}`
- `PUT /api/v1/commerce/orders/{order}/status`
- `POST /api/v1/commerce/orders/{order}/cancel`

Public order responses do not expose internal notes.

## Payment Routes

Public payment endpoints:

- `GET /api/v1/public/{businessSlug}/payment-methods`
- `GET /api/v1/public/{businessSlug}/orders/{orderNumber}/payment-options?phone=...`
- `POST /api/v1/public/{businessSlug}/orders/{orderNumber}/manual-payment-proofs`
- `POST /api/v1/public/{businessSlug}/orders/{orderNumber}/cash-on-delivery`

Protected payment endpoints:

- `GET /api/v1/payments/methods`
- `POST /api/v1/payments/methods`
- `GET /api/v1/payments/methods/{paymentMethod}`
- `PATCH /api/v1/payments/methods/{paymentMethod}`
- `POST /api/v1/payments/methods/{paymentMethod}/toggle`
- `GET /api/v1/payments`
- `GET /api/v1/payments/{payment}`
- `GET /api/v1/payments/manual-proofs`
- `GET /api/v1/payments/manual-proofs/{manualPaymentProof}`
- `POST /api/v1/payments/manual-proofs/{manualPaymentProof}/approve`
- `POST /api/v1/payments/manual-proofs/{manualPaymentProof}/reject`
- `POST /api/v1/payments/orders/{order}/mark-paid-manually`
- `POST /api/v1/payments/orders/{order}/cash-on-delivery`

Public payment responses do not expose admin notes, sensitive method config, or raw transaction payloads.

Paymob public endpoints:

- `GET /api/v1/public/{businessSlug}/orders/{orderNumber}/payment-status?phone=...`
- `POST /api/v1/public/{businessSlug}/orders/{orderNumber}/paymob/initiate`
- `GET|POST /api/v1/public/paymob/return`
- `GET|POST /api/v1/payments/paymob/callback`

Paymob dashboard endpoint:

- `GET /api/v1/payments/paymob/transactions`
