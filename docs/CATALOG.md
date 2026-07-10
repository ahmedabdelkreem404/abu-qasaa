# Catalog

## Wholesale Price Lists

Catalog price lists support `retail`, `wholesale`, `distributor`, and `special`. Wholesale price resolution uses assigned customer price lists first, then the business unit wholesale list. Missing wholesale prices reject wholesale requests instead of falling back to retail.

## Scope

Phase 4 implements the Product Catalog foundation for product-based business units. It supports catalog management and public product browsing only.

Out of scope: cart, checkout, orders workflow, Paymob, manual payment processing, stock deduction, inventory movements, real estate workflows, and RFQ workflows.

## Tables

- `categories`: business-unit scoped, nested through `parent_id`, SEO fields, status, soft deletes.
- `brands`: business-unit scoped brand records, status, soft deletes.
- `products`: business-unit scoped products with category, brand, SKU, type, status, visibility, prices, specs JSON, SEO fields, and publishing fields.
- `product_variants`: product-level variants using flexible `option_values_json`.
- `product_images`: product image metadata and primary image flag.
- `price_lists`: business-unit scoped retail, wholesale, distributor, and special price lists.
- `product_prices`: product or variant prices tied to a price list and min quantity.

## Rules

- Catalog records must belong to a business unit.
- Dashboard writes require the relevant `products.*` permission.
- Users can manage only assigned business units unless they are Super Admin.
- The target business unit must have the `products` module enabled.
- Category parents, product category, product brand, price lists, and variants must belong to the same business-unit/product scope.
- Public APIs expose only published products with `visibility = public`.
- Public product responses do not expose `cost_price`.

## Public APIs

- `GET /api/v1/public/{businessSlug}/products`
- `GET /api/v1/public/{businessSlug}/products/{productSlug}`
- `GET /api/v1/public/{businessSlug}/categories`
- `GET /api/v1/public/{businessSlug}/brands`

Product listing supports filters for category, brand, featured flag, search, and price range.

## Dashboard APIs

Protected endpoints live under `/api/v1/catalog/*` and cover categories, brands, products, product publishing, variants, images, prices, and price lists.

## Seed Data

The local seed creates:

- Oils: Engine Oils, Gear Oils, Greases; Abu Qasaa Select and Partner Lubricants; retail, wholesale, and distributor price lists; three sample products with variants and prices.
- Dates: Premium Dates, Gift Boxes, Bulk Dates; Ghosoun brand; retail price list; three sample products with variants and prices.

## Frontend

Public pages:

- `/{businessSlug}/products`
- `/{businessSlug}/products/{productSlug}`

Dashboard pages:

- `/dashboard/catalog`
- `/dashboard/catalog/categories`
- `/dashboard/catalog/brands`
- `/dashboard/catalog/products`
- `/dashboard/catalog/price-lists`

## Phase 5

Next work should build ecommerce flows on top of the catalog: cart, checkout, order creation, customer-facing order states, and payment integration.

Phase 5 now builds the first commerce layer on top of catalog data. Product prices are resolved into cart/order snapshots; catalog `cost_price` remains admin-only and is never exposed in public commerce responses.
