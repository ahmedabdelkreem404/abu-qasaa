# Paymob

Phase 7 adds Paymob as an online payment provider inside the existing Payments module.

## Documentation Access

Official Paymob developer pages were discoverable, but direct page reads were blocked by JavaScript/anti-bot verification during implementation. Official search snippets confirmed the backend-created Intention API flow and HMAC callback verification. Exact callback field ordering is isolated behind `PaymobSignatureVerifier` and marked with TODO comments so it can be replaced after direct docs access.

## Environment

Configure real credentials only in `.env`:

```text
PAYMOB_API_KEY=
PAYMOB_INTEGRATION_ID=
PAYMOB_IFRAME_ID=
PAYMOB_HMAC_SECRET=
PAYMOB_BASE_URL=https://accept.paymob.com/api
PAYMOB_CALLBACK_URL=
PAYMOB_RETURN_URL=
PAYMOB_CURRENCY=EGP
PAYMOB_FAKE_MODE=false
```

For automated tests and local demos, `PAYMOB_FAKE_MODE=true` returns deterministic fake checkout URLs and validates fake HMAC payloads.

## Flow

The frontend calls the backend initiation endpoint, receives a Paymob URL, and redirects the user. The frontend never marks an order paid. Paymob callbacks are verified by HMAC before changing payment or order state.

Success callbacks mark the payment paid, mark the order paid, confirm pending orders, and create idempotent transaction records. Failed or cancelled callbacks mark the payment failed/cancelled without confirming the order.

## Boundaries

- No card data is collected or stored.
- No Paymob secrets are exposed to frontend responses.
- No inventory deduction.
- No shipping provider integration.
- No real estate or RFQ logic.
- No microservices or DevOps-heavy setup.
