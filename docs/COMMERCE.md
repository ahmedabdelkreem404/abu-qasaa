# Commerce

## Scope

Phase 5 implements cart, customers, checkout request, and orders foundation. It does not implement Paymob, manual payment proof review, Vodafone Cash, Instapay, inventory deduction, stock movements, or shipping provider integrations.

## Cart Model

Carts belong to one business unit and are identified publicly by a `session_token`. Cart items snapshot product name, SKU, variant label, unit price, quantity, and subtotal. A cart can be `active`, `converted`, `abandoned`, or `expired`.

## Customer Model

Customers are business-unit scoped. Phone is required for checkout, email is optional, and customer types include `individual`, `shop`, `company`, and `distributor`. Wholesale approval remains a placeholder through customer fields.

## Order Model

Orders preserve totals and item snapshots at checkout time. Initial public checkout creates orders as:

- `status`: `pending_review`
- `payment_status`: `unpaid`
- `fulfillment_status`: `unfulfilled`

Order status changes create `order_status_histories` records.

## Public APIs

- `POST /api/v1/public/{businessSlug}/cart`
- `GET /api/v1/public/{businessSlug}/cart/{sessionToken}`
- `POST /api/v1/public/{businessSlug}/cart/{sessionToken}/items`
- `PUT /api/v1/public/{businessSlug}/cart/{sessionToken}/items/{cartItem}`
- `DELETE /api/v1/public/{businessSlug}/cart/{sessionToken}/items/{cartItem}`
- `DELETE /api/v1/public/{businessSlug}/cart/{sessionToken}/clear`
- `POST /api/v1/public/{businessSlug}/checkout`
- `GET /api/v1/public/{businessSlug}/orders/{orderNumber}?phone=...`

## Dashboard APIs

- `GET /api/v1/commerce/customers`
- `POST /api/v1/commerce/customers`
- `GET /api/v1/commerce/customers/{customer}`
- `PATCH /api/v1/commerce/customers/{customer}`
- `GET /api/v1/commerce/orders`
- `GET /api/v1/commerce/orders/{order}`
- `PUT /api/v1/commerce/orders/{order}/status`
- `POST /api/v1/commerce/orders/{order}/cancel`

## Business Rules

- Business unit must be active.
- `products` and `orders` modules must be enabled.
- Checkout requires `checkout_enabled`, `allow_guest_checkout`, and `show_prices`.
- Public carts cannot mix products across business units.
- Public checkout only accepts published/public products with resolvable prices.
- Public order lookup requires order number and matching phone.

## Phase 6 Manual Payments

Manual payment records now integrate with Commerce orders. Manual proof submission sets order `payment_status` to `pending`; admin approval marks the payment and order paid and can confirm a pending order; rejection keeps the proof and returns the order payment state to unpaid. Cash on Delivery records the selected method but does not mark the order paid until later manual confirmation.

Paymob, card payments, inventory deduction, and shipping provider integrations are still deferred.

## Phase 7 Paymob

Paymob Card can now be initiated from the public order payment page. Initiation sets order `payment_status` to `pending`; only a verified backend callback can mark it `paid` and confirm the order. Return URLs show processing status only.

## Phase 8 Inventory

Checkout now reserves available stock when the business unit has inventory enabled. Cancelling an order releases reservations, and moving an order to `shipped` or `delivered` fulfills reserved stock idempotently. The return URL and payment redirects do not fulfill stock.
