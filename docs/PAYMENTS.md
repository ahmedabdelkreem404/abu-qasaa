# Payments

Phase 6 implements the manual payments foundation for existing Commerce orders.

## Scope

- Manual methods: Vodafone Cash, Instapay, Bank Transfer, and Cash on Delivery.
- Dashboard method management, payment listing, and manual proof review.
- Public order payment options and proof submission by order number plus matching phone.
- Business-unit scoping through enabled `payments` and `manual_payments` modules plus `manual_payment_enabled`.

## Data Model

- `payment_methods` stores business-unit methods, instructions, placeholder destination accounts, active status, and sort order.
- `payments` stores the selected method, amount, status, order/customer links, references, and paid/failed timestamps.
- `payment_transactions` records manual proof submission, approval, rejection, COD selection, and admin mark-paid events.
- `manual_payment_proofs` stores submitted proof details and admin review state.

Public resources hide `config_json`, internal metadata, admin notes, and rejected reasons.

## Public Flow

1. Customer creates an order through checkout.
2. Customer opens `/{businessSlug}/orders/{orderNumber}/payment`.
3. The page requires the phone used on the order.
4. Vodafone Cash, Instapay, and Bank Transfer show instructions and accept proof details.
5. Submitted proof sets payment and order `payment_status` to `pending`.
6. Cash on Delivery creates a pending payment record and does not mark the order paid.

## Dashboard Review Flow

- Users with `payments.review_manual` can review proofs for assigned business units.
- Approval marks the proof approved, payment paid, order `payment_status` paid, and moves pending orders to confirmed.
- Rejection marks the proof rejected, payment failed, and order payment status unpaid.
- Proofs are retained after rejection.

## Seed Data

Seeded placeholder accounts only:

- Vodafone Cash: `01000000000`
- Instapay: `abuqasaa@instapay`
- Bank Transfer: `Bank account details placeholder`

Oils and Dates get Vodafone Cash, Instapay, Bank Transfer, and COD. Import/Export and Real Estate get Bank Transfer and Instapay. Paymob is seeded only as an inactive placeholder.

## Boundaries

- No Paymob integration.
- No online card payments.
- No wallet callbacks or automatic verification.
- No inventory deduction or stock movements.
- No shipping provider integration.

## Phase 7 Notes

Phase 7 adds a Paymob provider foundation with backend initiation, callback HMAC verification, fake/local mode, and public redirect UX. The return URL never marks payment paid; only verified callbacks update order/payment status.

Future phases can add richer Paymob reconciliation, refunds, production Paymob field confirmation once docs are directly accessible, inventory deduction after payment/fulfillment decisions, and payment reports.
