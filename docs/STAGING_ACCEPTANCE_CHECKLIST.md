# Staging Acceptance Checklist

Status legend:

- `PASS`: tested successfully on staging.
- `FAIL`: tested and failed.
- `PENDING`: not tested yet because external staging deployment/access is unavailable.
- `N/A`: not applicable to the deployment.

Current Phase 15 status: all external staging smoke tests are `PENDING`. Repository-side scripts, docs, and automated local verification were prepared, but production launch is not approved until these tests are executed on a real staging deployment.

Environment under test:

- Backend URL: `TBD`
- Frontend URL: `TBD`
- Database: separate staging database required
- Storage: separate staging storage required
- Paymob: fake mode or official test credentials only

## Acceptance Matrix

| Area | Test | Status | Tested URL/Environment | Expected Result | Actual Result | Notes | Severity If Failed |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Authentication | Super Admin login | PENDING | Staging dashboard | Valid admin receives token/session | Not run | Requires staging credentials outside Git | P1 |
| Authentication | Invalid login rate limiting | PENDING | `/api/v1/auth/login` | Repeated invalid attempts return 429 | Not run | Backend regression exists locally | P1 |
| Authentication | Logout | PENDING | Staging dashboard | Token/session invalidated | Not run | Requires staging login | P2 |
| Authentication | Inactive user denied | PENDING | `/api/v1/auth/login` | Inactive user cannot log in | Not run | Backend regression exists locally | P1 |
| Authentication | Business-unit permissions enforced | PENDING | Dashboard APIs | User sees assigned units only | Not run | Must verify with staging users | P0 |
| CMS | Public homepage loads | PENDING | Frontend home | HTTP 200 and published content | Not run |  | P1 |
| CMS | Published pages load | PENDING | Public CMS page | Published page visible | Not run |  | P2 |
| CMS | Draft pages remain hidden | PENDING | Public CMS page | Draft content not visible | Not run |  | P1 |
| CMS | Contact inquiry submission works | PENDING | Public contact form | Inquiry stored safely | Not run | Verify rate limiting | P2 |
| Retail commerce | Browse products | PENDING | Public products page | Published products visible | Not run |  | P1 |
| Retail commerce | Product detail loads | PENDING | Product detail page | Product data loads | Not run |  | P1 |
| Retail commerce | Add item to cart | PENDING | Cart API/page | Cart totals update | Not run |  | P1 |
| Retail commerce | Inventory availability enforced | PENDING | Checkout/cart | Unavailable stock blocked | Not run |  | P0 |
| Retail commerce | Checkout creates order | PENDING | Checkout | Order created once | Not run |  | P1 |
| Retail commerce | Order tracking works | PENDING | Order status page | Correct phone required | Not run |  | P2 |
| Manual payments | Manual payment method appears | PENDING | Payment options | Enabled methods visible | Not run |  | P2 |
| Manual payments | Proof upload validation works | PENDING | Proof submission | Unsafe files rejected | Not run |  | P1 |
| Manual payments | Admin approval works | PENDING | Dashboard payments | Payment marked paid once | Not run |  | P1 |
| Manual payments | Duplicate approval does not double-pay | PENDING | Dashboard payments | Idempotent approval | Not run |  | P0 |
| Paymob | Fake-mode initiation works | PENDING | Payment initiation | Fake checkout URL returned | Not run | Staging must not use real credentials unless official test values provided | P1 |
| Paymob | Return URL does not mark paid | PENDING | Paymob return route | Return alone does not pay order | Not run |  | P0 |
| Paymob | Invalid callback signature rejected | PENDING | Paymob callback | Invalid HMAC rejected | Not run |  | P0 |
| Paymob | Valid signed callback marks paid | PENDING | Paymob callback | Payment/order marked paid once | Not run | Test credentials only | P0 |
| Paymob | Duplicate callback remains idempotent | PENDING | Paymob callback | No duplicate payment mutation | Not run |  | P0 |
| Inventory | Stock reserved at checkout | PENDING | Checkout | Reservation created | Not run |  | P0 |
| Inventory | Cancellation releases reservation | PENDING | Order cancellation | Stock reservation released | Not run |  | P0 |
| Inventory | Fulfillment deducts stock once | PENDING | Inventory dashboard | Stock decremented once | Not run |  | P0 |
| Inventory | Overselling blocked | PENDING | Cart/checkout | Quantity beyond stock blocked | Not run |  | P0 |
| Inventory | Cross-business-unit stock access blocked | PENDING | Inventory APIs | Scoped data only | Not run |  | P0 |
| Wholesale | Application submission works | PENDING | Wholesale application | Application stored | Not run |  | P2 |
| Wholesale | Admin approval works | PENDING | Dashboard wholesale | Customer approved | Not run |  | P1 |
| Wholesale | Approved access token works | PENDING | Wholesale public page | Approved customer can access | Not run |  | P1 |
| Wholesale | Wholesale prices remain private | PENDING | Public retail pages | Wholesale prices hidden | Not run |  | P0 |
| Wholesale | Minimum quantities enforced | PENDING | Cart/checkout | Below-min quantity rejected | Not run |  | P1 |
| Ghosoun Dates | Collections load | PENDING | `/dates/collections` | Active collections visible | Not run |  | P2 |
| Ghosoun Dates | Gift boxes load | PENDING | `/dates/gift-boxes` | Gift products visible | Not run |  | P2 |
| Ghosoun Dates | Seasonal products load | PENDING | `/dates/seasonal` | Seasonal products visible | Not run |  | P2 |
| Ghosoun Dates | Bundle added as parent product | PENDING | Cart | Bundle snapshot preserved | Not run |  | P1 |
| Ghosoun Dates | Corporate inquiry works | PENDING | Corporate gifts form | Inquiry stored | Not run |  | P2 |
| Real estate | Projects and units load | PENDING | Real estate pages | Active projects/units visible | Not run |  | P2 |
| Real estate | Lead submission works | PENDING | Lead form | Lead stored | Not run |  | P1 |
| Real estate | Appointment creation works | PENDING | Viewing request form | Appointment stored | Not run |  | P1 |
| Real estate | Reservation conflict blocked | PENDING | Reservation interest | Conflicting unit rejected | Not run |  | P1 |
| Import/Export RFQ | Services load | PENDING | Services pages | Published services visible | Not run |  | P2 |
| Import/Export RFQ | RFQ submission works | PENDING | RFQ form | RFQ and items stored | Not run |  | P1 |
| Import/Export RFQ | Documents reject unsafe file types | PENDING | RFQ documents | Unsafe files rejected | Not run |  | P1 |
| Import/Export RFQ | Dashboard quotation flow works | PENDING | Dashboard RFQ | Quotation can be created/sent | Not run |  | P1 |
| Import/Export RFQ | RFQ status lookup is safe | PENDING | RFQ status page | Contact verification required | Not run |  | P1 |
| Reports and audit | Dashboard reports load | PENDING | Dashboard reports | Scoped data shown | Not run |  | P1 |
| Reports and audit | CSV export Arabic UTF-8 works | PENDING | Orders export | CSV opens correctly | Not run |  | P2 |
| Reports and audit | Audit entries are created | PENDING | Audit logs | Actions logged | Not run |  | P1 |
| Reports and audit | Sensitive values are redacted | PENDING | Audit logs | Secrets redacted | Not run | Backend regression exists locally | P0 |

## Findings

| ID | Severity | Status | Finding | Required Action |
| --- | --- | --- | --- | --- |
| STG-001 | P1 | OPEN | External staging deployment has not been executed from this environment. | Deploy to staging with real server credentials and run this checklist. |
| STG-002 | P1 | OPEN | Staging smoke tests have not been executed against real URLs. | Record PASS/FAIL results with tested URLs before production launch. |

## Launch Decision

Production launch not yet approved.
