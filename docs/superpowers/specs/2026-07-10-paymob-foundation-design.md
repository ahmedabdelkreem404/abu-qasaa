# Phase 7 Paymob Foundation Design

Paymob is added as a provider inside the existing Payments module. The backend owns initiation, callback verification, status mapping, and order/payment updates. The frontend only asks the backend to initiate payment and redirects to the returned external checkout/iframe URL.

Official Paymob pages were discoverable but blocked direct content access behind JavaScript/anti-bot verification. Search snippets from official Paymob docs confirmed the current flow uses backend-created payment intentions and callback HMAC verification. Any exact field that could not be confirmed is isolated behind `PaymobPayloadMapper` and `PaymobSignatureVerifier`.

Implementation uses `PAYMOB_FAKE_MODE=true` for tests/local development. Real credentials come only from `.env`; no secrets are exposed through public APIs or committed.

Boundaries: no card collection, no frontend-paid trust, no inventory deduction, no shipping integration, no real estate/RFQ logic, no microservices.
