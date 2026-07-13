# Abu Qasaa UI/UX System

This document describes the V1.1.0 frontend design system and localization structure for Abu Qasaa.

## Brand System

The visual identity is based on the Abu Qasaa Oils logo:

- Graphite gray for industrial trust.
- Deep green for growth, operations, and approval states.
- Warm oil gold for premium highlights and important accents.
- Soft neutral backgrounds for a corporate umbrella brand that can host Oils, Dates, Real Estate, and Import/Export.

The logo is stored locally at:

`frontend/public/brand/abu-qasaa-oils-logo.jpg`

No remote font or image dependency is required for the core brand shell.

## Color Tokens

Tokens live in `frontend/src/app/globals.css`.

| Token | Purpose |
| --- | --- |
| `--aq-primary` | Main brand green for CTAs and navigation emphasis. |
| `--aq-primary-2` | Dark green for text and high-contrast states. |
| `--aq-green` | Active/positive accent. |
| `--aq-gold` | Oil-inspired premium highlight. |
| `--aq-graphite` | Industrial neutral from the logo. |
| `--aq-ink` | Primary readable text. |
| `--aq-muted` | Secondary text. |
| `--aq-line` | Borders and dividers. |
| `--aq-surface` | Main card surface. |
| `--aq-soft` | Quiet background surface. |
| `--aq-success`, `--aq-warning`, `--aq-error`, `--aq-info` | Semantic states. |

## Typography

The app uses local/system font stacks only:

- Arabic: `Segoe UI`, `Tahoma`, `Arial`, `sans-serif`.
- English: `Inter`, `Segoe UI`, `Arial`, `sans-serif`.
- Monospace: `Cascadia Code`, `Courier New`, `Consolas`, `monospace`.

Remote Google Fonts are intentionally avoided to keep local and offline builds stable.

## Core CSS Utilities

Reusable classes in `globals.css`:

- `aq-container`: fluid responsive container up to wide desktop.
- `aq-shell-bg`: subtle branded page background.
- `aq-brand-mark`: logo + text lockup.
- `aq-logo`: consistent local logo rendering.
- `aq-eyebrow`: small section label.
- `aq-display`: large hero headline.
- `aq-title`: page and section title scale.
- `aq-subtitle`: readable supporting copy.
- `aq-card`: primary card surface.
- `aq-card-muted`: quiet supporting card.
- `aq-btn`, `aq-btn-secondary`, `aq-btn-ghost`: action hierarchy.
- `aq-chip`: status/category pill.
- `aq-grid-auto`: responsive cards from mobile to wide desktop.
- `aq-hero`: premium branded hero surface.
- `aq-form-grid`: responsive form layout.
- `aq-table-wrap`: horizontal table safety for dashboard data views.

## Localization

Translation files:

- `frontend/src/i18n/messages/ar.json`
- `frontend/src/i18n/messages/en.json`

Helpers:

- `frontend/src/i18n/index.ts`
- `frontend/src/i18n/server.ts`
- `frontend/src/components/shared/language-switcher.tsx`

Arabic is the default locale. The selected locale is stored in the `abu_qasaa_locale` cookie. Server-rendered shells read the cookie and set:

- `<html lang="ar" dir="rtl">`
- `<html lang="en" dir="ltr">`

When adding user-facing UI, add shared strings to the JSON dictionaries first and import the dictionary through `getDictionary()` for server components or `dictionaries[pickLocale(...)]` for client components.

## Public Experience

The public site uses the umbrella brand shell with:

- Sticky responsive navigation.
- Local logo lockup.
- Language switcher.
- Premium hero and business-sector cards.
- Consistent cards/forms/buttons across products, checkout, wholesale, real estate, services, RFQ, CMS, and contact.

Each business unit keeps its own content and API integration while inheriting the umbrella visual system.

## Dashboard Experience

The dashboard uses:

- Dark industrial sidebar.
- Sticky top context bar.
- Responsive navigation.
- Bilingual navigation labels.
- Branded dashboard hero.
- Unified cards and stats.
- Existing protected-route and permission logic unchanged.

## Responsive Strategy

The design system is built mobile-first and avoids viewport-based font scaling. It uses:

- `clamp()` only for bounded display scales.
- `aq-container` for fluid widths from 300px through 4K.
- `aq-grid-auto` for card grids that collapse naturally.
- `aq-form-grid` for mobile-first forms.
- `aq-table-wrap` for dashboard tables that need horizontal scroll.
- Tap targets at or above 44px for primary actions.

Verification targets:

- 300x500
- 320px
- 375px
- 768px
- 1024px
- 1440px
- 1920px
- 2560px / 4K-like widths

Both Arabic RTL and English LTR should be checked after major UI changes.
