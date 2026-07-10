# Business Units

## Wholesale Business-Unit Rules

Wholesale is available only when the `wholesale` module is enabled and the `wholesale_enabled` setting is true for that business unit. Oils is enabled in seeds; Dates is intentionally disabled unless changed by an admin.

Abnaa Abu Qasaa Trading is the umbrella brand. It owns and manages several business units through one shared platform.

## Current Business Units

- Abnaa Abu Qasaa Oils & Lubricants
- Abnaa Abu Qasaa Import & Export
- Ghosoun Dates
- Abnaa Abu Qasaa Real Estate

## Future Business Units

Future business units should be created from the dashboard by selecting an activity template, enabling modules, and filling business settings. The platform must not create a new table or app for every new business.

Super Admins can select existing templates/modules only. New module behavior is added by developers in code, then exposed through the activity module catalog.

## Activity Templates

- Product Store
- Wholesale Store
- Services/RFQ
- Real Estate
- Content Only
- Hybrid

## Seeding

Run the foundation seed from `backend/`:

```bash
php artisan migrate:fresh --seed
```

This seeds the six templates, the activity module catalog, global feature flags, and the four current business units with their default modules and settings.

## Public Business Unit Pages

Each active business unit can have a published CMS page with `page_type = business_unit_landing`. The public route `/[businessSlug]` first loads the business unit, then renders its CMS landing sections when available.

Dashboard CMS permissions are business-unit scoped. A business-unit admin can manage pages and inquiries only for assigned units; company-level CMS pages remain Super Admin-only.

## Product Catalog Units

Product catalog management is available only when the `products` module is enabled for the business unit. Oils and Dates are seeded as product-based units; Import/Export and Real Estate do not expose product catalog management.

Future product-based business units can use the same generic catalog tables and APIs after Super Admin enables the `products`, `categories`, `brands`, and optional `price_lists` modules.

## Commerce Rules

Commerce checkout requires the business unit to be active, have `products` and `orders` enabled, and have `checkout_enabled`, `allow_guest_checkout`, and `show_prices` enabled. Carts cannot mix products across business units.

## Manual Payment Rules

Manual payments require an active business unit, enabled `orders`, `payments`, and `manual_payments` modules, and the `manual_payment_enabled` setting. Dashboard users can manage or review payments only for assigned business units unless they are Super Admin.

Paymob initiation requires an active business unit, enabled `orders`, `payments`, and `paymob` modules, and the `paymob_enabled` setting. Seed data enables Paymob Card for product business units in fake/local mode.
