# UI Visual Acceptance

Date: 2026-07-13
Branch: `codex/ui-visual-remediation`
Result: Passed

## Scope

Phase 17 remediated the rendered UI/UX defects found after `v1.1.0`:

- Header no longer relies on a cramped mobile horizontal nav strip.
- Hero text now renders white on a dark branded surface across desktop and mobile.
- Logo usage is constrained to a small circular mark or intentional brand panel, not a large raw white square.
- Arabic public pages no longer fall back to English CMS text when Arabic content is missing.
- Business-unit cards use localized labels, descriptions, accents, and consistent spacing.
- Dashboard shell uses a mobile navigation disclosure and localized overview labels.

## Screenshot Evidence

| Surface | Locale | Viewport | Screenshot | Overflow | Translation | Direction | Result |
| --- | --- | ---: | --- | --- | --- | --- | --- |
| Home | Arabic | 1440x900 | `runtime-logs/ui-remediation/home-ar-desktop.png` | Pass | Pass | `rtl` | Pass |
| Home | English | 1440x900 | `runtime-logs/ui-remediation/home-en-desktop.png` | Pass | Pass | `ltr` | Pass |
| Home | Arabic | 390x844 | `runtime-logs/ui-remediation/home-ar-mobile.png` | Pass | Pass | `rtl` | Pass |
| Business units | Arabic | 1440x900 | `runtime-logs/ui-remediation/business-units-ar-desktop.png` | Pass | Pass | `rtl` | Pass |
| Business units | English | 1440x900 | `runtime-logs/ui-remediation/business-units-en-desktop.png` | Pass | Pass | `ltr` | Pass |
| Oils | Arabic | 1440x900 | `runtime-logs/ui-remediation/oils-ar-desktop.png` | Pass | Pass | `rtl` | Pass |
| Dates | Arabic | 1440x900 | `runtime-logs/ui-remediation/dates-ar-desktop.png` | Pass | Pass | `rtl` | Pass |
| Real estate | Arabic | 1440x900 | `runtime-logs/ui-remediation/real-estate-ar-desktop.png` | Pass | Pass | `rtl` | Pass |
| Import/export | Arabic | 1440x900 | `runtime-logs/ui-remediation/import-export-ar-desktop.png` | Pass | Pass | `rtl` | Pass |
| Dashboard | Arabic | 1440x900 | `runtime-logs/ui-remediation/dashboard-ar-desktop.png` | Pass | Pass | `rtl` | Pass |
| Dashboard | Arabic | 390x844 | `runtime-logs/ui-remediation/dashboard-ar-mobile.png` | Pass | Pass | `rtl` | Pass |

## Viewport Matrix

Routes checked at every viewport: `/`, `/business-units`, `/dashboard`.

| Viewport | Overflow | RTL/lang | Translation | Result |
| ---: | --- | --- | --- | --- |
| 300x500 | Pass | Pass | Pass | Pass |
| 320x568 | Pass | Pass | Pass | Pass |
| 360x800 | Pass | Pass | Pass | Pass |
| 375x812 | Pass | Pass | Pass | Pass |
| 390x844 | Pass | Pass | Pass | Pass |
| 414x896 | Pass | Pass | Pass | Pass |
| 768x1024 | Pass | Pass | Pass | Pass |
| 1024x768 | Pass | Pass | Pass | Pass |
| 1280x720 | Pass | Pass | Pass | Pass |
| 1366x768 | Pass | Pass | Pass | Pass |
| 1440x900 | Pass | Pass | Pass | Pass |
| 1920x1080 | Pass | Pass | Pass | Pass |
| 2560x1440 | Pass | Pass | Pass | Pass |
| 3840x2160 | Pass | Pass | Pass | Pass |

Matrix artifact: `runtime-logs/ui-remediation/viewport-matrix.json`

## Verification

| Check | Result |
| --- | --- |
| `npm.cmd run lint` | Passed |
| `npm.cmd run build` | Passed |
| `C:\xampp\php\php.exe artisan test` | Passed, 118 tests / 804 assertions |
| `C:\xampp\php\php.exe vendor/bin/pint --test` | Passed |
| `node runtime-logs\ui-remediation\live-smoke-current.mjs` | Passed, 19 live checks |

## Notes

- `runtime-logs/live-smoke.mjs` is a prior untracked local smoke artifact with a stale hardcoded local password; the current remediation smoke uses a temporary Sanctum token via `AQ_VISUAL_QA_TOKEN`, writes `runtime-logs/ui-remediation/live-smoke-current.json`, and the token was deleted after the run.
- Product sample records whose Arabic fields contained English seed text are hidden on Arabic business-unit pages rather than machine-translated.
- `_external/Cashiry` was not modified.
