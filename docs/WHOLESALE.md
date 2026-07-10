# Wholesale Foundation

Phase 9 adds business-unit-scoped wholesale capabilities for product business units, with Oils enabled by default and Dates left disabled.

## Scope

- Public wholesale applications are accepted only when the `wholesale` module is enabled and `wholesale_enabled` is true.
- Admins can list, review, approve, reject, update wholesale customers, and assign wholesale-capable price lists.
- Approved customers can request a simple phone-based access token for this foundation phase.
- Wholesale catalog and cart pricing require an approved customer context.
- Advanced CRM, credit accounting, sales commissions, procurement, shipping integrations, and SMS OTP are outside this phase.

## Application Flow

1. Public visitor submits `POST /api/v1/public/{businessSlug}/wholesale/apply`.
2. The application starts as `pending`.
3. Dashboard users with `wholesale.review_applications` approve or reject it.
4. Approval creates or updates a customer, marks `wholesale_status=approved`, and optionally assigns a price list.
5. Rejection records an internal reason; public status responses expose only safe status text.

## Access Flow

Approved customers call `POST /api/v1/public/{businessSlug}/wholesale/access` with their phone number. The API returns a token once, stores only a hash, and the frontend stores `{ phone, token }` in `localStorage` under `abu_qasaa_wholesale_{businessSlug}`.

This is a foundation access method. Real OTP/SMS or a full customer portal belongs in a later phase.

## Price Resolution

Wholesale pricing resolves in this order:

1. Customer assigned active price list if it is `wholesale`, `distributor`, or `special`.
2. Active wholesale price list for the same business unit.
3. Reject the wholesale price request if no matching price exists.

The fallback behavior is intentionally strict: wholesale orders do not silently fall back to retail prices. Retail cart behavior still uses the default retail price list or product base price.

## Minimum Quantity

`product_prices.min_quantity` is enforced for wholesale cart additions and cart quantity updates. Cart and order item metadata snapshots include `price_list_id`, `price_list_type`, `price_audience`, `min_quantity_applied`, and `price_source`.

## Dashboard

Dashboard wholesale routes live under `/api/v1/wholesale/*` and require `wholesale.view`, `wholesale.manage`, `wholesale.review_applications`, or `wholesale.assign_price_lists`.

Super Admin can manage all business units. Business Unit Admins can manage only assigned business units with wholesale enabled.

## Seed Data

Oils has Retail, Wholesale, and Distributor price lists, wholesale and distributor product price examples, one approved demo wholesale customer (`01011111111`), and one pending application. Dates remains wholesale-disabled by default.

## Phase 10 Candidates

- Real OTP/SMS or customer portal auth.
- Historical price-list assignment records.
- B2B account users and buyer roles.
- Credit policies and invoicing/accounting workflows.
- More advanced dashboard analytics.
