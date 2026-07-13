# Business Storefronts

Abu Qasaa is one umbrella platform with multiple business activities. Each activity should feel like its own website while sharing the same backend, admin foundation, and operational data model.

## Public Experience

- Each activity has a distinct visual identity: logo, palette, hero media, gallery, product imagery, policies, and navigation.
- Activity pages keep a fixed activity navbar and footer across home, products, product details, collections, gifts, gallery, policies, wholesale, cart, and checkout.
- The public route shape stays unified under `/{businessSlug}` so the platform can link, search, and administer all activities consistently.
- Shared commerce and content components are allowed, but copy, imagery, and layout emphasis must come from the activity storefront profile.

## Admin Ownership

- Super admins manage the umbrella platform and all activities.
- Activity admins manage one activity at a time.
- Employee permissions should be scoped by activity and capability, not by disconnected websites.

## Current Storefront Profiles

- `dates`: Ghosoun for Dates, using the supplied brand logo and date product imagery.
- `oils`: Abu Qasaa oils, separate industrial/trade identity.
- `real-estate`: Abu Qasaa real estate, separate property identity.
- `import-export`: Abu Qasaa import/export, separate logistics and trade identity.

## Implementation Notes

- Storefront identity is defined in `frontend/src/storefront/profiles.ts`.
- Shared activity shell is implemented in `frontend/src/components/layout/business-storefront-shell.tsx`.
- Activity routes are wrapped by `frontend/src/app/(business)/[businessSlug]/layout.tsx`.
- Public Ghosoun assets live under `frontend/public/brand/ghosoun/`.
