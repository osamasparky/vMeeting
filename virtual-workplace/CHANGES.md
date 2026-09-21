# UlaSpace Figma → vMeeting implementation

Source of truth: the Figma file **UlaSpace — Design System** (`uploads/UlaSpace - Design System.fig`, exported 2026-09-16).
Every value below was read out of that file's variable collections, text styles, effect styles and component sets — not from the previous docs, which had drifted.

## Decisions applied
- Token names mirror Figma 1:1 with an `--ula-` prefix: Figma `surface/page` → `--ula-surface-page`.
- Arabic typeface is **Cairo** (Figma) with `IBM Plex Sans Arabic` as fallback; Cairo is **self-hosted**.
- Dark mode: `prefers-color-scheme` is the default, `[data-theme="dark"]` / `.dark` override it, `[data-theme="light"]` forces light.
- `[dir]` rules kept for RTL/LTR font switching.
- The second (blue/violet HSL) palette in `app.css` is retired; all of its references were rewritten onto UlaSpace tokens.
- Layout, markup and behaviour were left intact — this is a retheme, not a rebuild. No component props, routes or JS behaviour were removed.

## Corrections against the old token layer
| Thing | Was | Now (Figma) |
| :--- | :--- | :--- |
| Arabic font | IBM Plex Sans Arabic | Cairo |
| Palm ramp | 7 steps, mislabelled (500 = #1e412f) | 11 steps + `palm/chrome`; #1e412f is `palm/700` |
| Palm 950 | #0b1410 | #0e1c17 |
| Sand 50 / page bg | #ffffff / #f9f4ee | #fbf8f2 / card #f9f6ef |
| Sand 500 | #c1b6a6 (actually stone/300) | #d6c0a2 |
| Stone | 2 ad-hoc values | 7-step ramp #e8e4dc → #4a443c |
| Gold 200 / 600 | #f6ebda / #b46c34 | #f0dfbc / #946c27 |
| Terracotta | 2 values | 5-step ramp |
| Radii | lg 16, xl 20, arch 48 | lg 20, xl 28 (no arch) |
| Container | max 1440, narrow 1120 | max 1280, narrow 880 |
| Spacing | 4→120 | 0,2,4,8,12,16,20,24,32,40,48,64,80,96,128 |
| Motion | 120/200/320ms, 2 eases | 90/160/240/360/700ms, 3 Figma cubic-beziers |
| Semantic tokens | ~30 | 104 (incl. `tone/*`, `control/*`, `icon/*`, `media/*`, `spinner/*`) |
| Primary button | palm-900 fill, pill | `accent/default` (#1e412f), radius 10/14/18 |
| Accent | gold was "accent" | `accent/*` is palm green; gold is `highlight/*` |
| Checkbox / switch "on" | gold | `accent/default` |

## Files changed

### 1. Token layer (rewritten)
- `resources/css/ulaspace-tokens.css` — regenerated: 62 primitives, 104 semantic tokens × Light/Dark, spacing, shape, typography, motion, elevation, scrims, type + surface utilities, `@font-face` for self-hosted Cairo, and a clearly-marked legacy bridge block (deletable once nothing references the old names).
- `resources/design-system/tokens.json` — regenerated straight from the Figma variables: 226 variables across 6 collections, 47 text styles, 9 effect styles, grid.
- `resources/css/app.css` — legacy HSL palette removed; now imports the token layer, adds a Tailwind `@theme` bridge so utilities resolve to UlaSpace tokens, keeps both keyframe animations (recoloured).

### 2. Components (16 files in `resources/views/components/`)
Props and slots unchanged; geometry and colour corrected to the Figma component sets.
- `btn` — sizes 36/44/52, pad-x 16/22/26, radius 10/14/18, gap 8; primary = `accent/*`; secondary = card + `border/strong`; ghost = `text/link` on `surface/accent-soft`; danger = `status/danger`; nav-cta = `control/cta-on-dark`; gold focus ring; **new** `loading` prop (Figma has a Loading state); `pill` now defaults to false.
- `icon-btn` — 40×40, radius 12, icon 20; Outline/Accent/dark-toolbar variants; **new** `dot` prop (8px terracotta notification dot with 1.5px surface ring).
- `badge` — Figma Tag: pill 29h, pad-x 12, 7px dot, tonal `tone/*` pairs, no border; `filter` renders the Chip (36h, pad-x 16, bordered).
- `input` — Text Field: 46h, radius 14, `surface/page` fill, `border/default`, focus `border/focus` + gold ring, error `border/danger` 1.5px, label 15 medium, helper 13.
- `checkbox` — 20px box, radius 6, 1.5px `border/strong`, checked `accent/default`, gap 11.
- `switch` — track 44×26, pad 3, off `control/track-off`, on `accent/default`, knob 20.
- `kpi-card` — Stat Card: radius 20, pad 20/18, shadow XS, tonal icon chips, metric in Cairo Light.
- `meeting-row` — radius 14, `surface/page` fill, pad 14/12, 36px status square radius 10 with `tone/*` fills.
- `quick-tile` — radius 16, pad 16, icon 26, `surface/page`, hover `border/strong`.
- `card`, `modal`, `table`, `tabs`, `empty-state`, `donut-chart` — surfaces, radii, shadows, overlay (`surface/overlay` + 14px blur) and active states moved onto the real tokens.
- `tactile-btn` — untouched (class-based legacy wrapper).

### 3. Token sweep (67 files)
5679 `var()` references rewritten, 335 retired declarations removed, 67 stale hex literals corrected. Composite locals kept their meaning and were renamed `--ula-gradient-accent` / `--ula-transition-smooth`. Utility classes `nx-card`, `nx-glass`, `nx-headline-*`, `nx-mirror-rtl` renamed to `ula-*`.

| File | refs rewritten | decls removed | hex fixed |
| :--- | ---: | ---: | ---: |
| `resources/views/dashboard/partials/modals.blade.php` | 536 | 0 | 0 |
| `resources/views/projects/hub.blade.php` | 435 | 66 | 0 |
| `resources/views/dashboard.blade.php` | 292 | 64 | 14 |
| `resources/views/dashboard/partials/tab-timesheets.blade.php` | 310 | 0 | 0 |
| `resources/views/dashboard/partials/tab-all-tasks.blade.php` | 261 | 0 | 0 |
| `resources/views/superadmin/company_show.blade.php` | 244 | 0 | 0 |
| `resources/views/superadmin/layout.blade.php` | 162 | 61 | 8 |
| `resources/views/editor.blade.php` | 174 | 38 | 4 |
| `resources/views/dashboard/partials/tab-profile.blade.php` | 169 | 0 | 19 |
| `resources/views/office.blade.php` | 139 | 34 | 9 |
| `resources/views/superadmin/subscriptions.blade.php` | 160 | 0 | 0 |
| `resources/views/dashboard/partials/scripts.blade.php` | 156 | 0 | 0 |
| `resources/views/superadmin/furniture.blade.php` | 150 | 0 | 0 |
| `resources/views/superadmin/settings.blade.php` | 144 | 0 | 0 |
| `resources/views/dashboard/partials/tab-settings.blade.php` | 125 | 0 | 0 |
| `resources/views/dashboard/partials/tab-rooms.blade.php` | 107 | 0 | 0 |
| `resources/views/billing/payment.blade.php` | 101 | 0 | 0 |
| `resources/views/dashboard/partials/tab-departments.blade.php` | 97 | 0 | 0 |
| `resources/views/dashboard/partials/tab-chat.blade.php` | 95 | 0 | 0 |
| `resources/views/superadmin/default_template.blade.php` | 92 | 0 | 0 |
| `resources/views/dashboard/partials/tab-offices.blade.php` | 91 | 0 | 0 |
| `resources/views/superadmin/dashboard.blade.php` | 89 | 0 | 0 |
| `resources/views/layouts/auth.blade.php` | 57 | 26 | 3 |
| `resources/views/dashboard/partials/tab-workload.blade.php` | 80 | 0 | 0 |
| `resources/views/projects/partials/modals.blade.php` | 79 | 0 | 0 |
| `resources/css/ulaspace-dashboard.css` | 51 | 22 | 4 |
| `resources/views/dashboard/partials/tab-guests.blade.php` | 77 | 0 | 0 |
| `resources/views/dashboard/partials/tab-billing.blade.php` | 74 | 0 | 0 |
| `resources/views/office/partials/modals.blade.php` | 74 | 0 | 0 |
| `resources/views/superadmin/plans.blade.php` | 72 | 0 | 0 |
| `resources/views/dashboard/partials/tab-audit.blade.php` | 68 | 0 | 0 |
| `resources/views/superadmin/health.blade.php` | 65 | 0 | 0 |
| `resources/css/ulaspace-office.css` | 54 | 10 | 0 |
| `resources/views/landing/home.blade.php` | 64 | 0 | 0 |
| `resources/views/superadmin/translations.blade.php` | 64 | 0 | 0 |
| `resources/views/superadmin/companies.blade.php` | 58 | 0 | 0 |
| `resources/views/guest_join.blade.php` | 50 | 0 | 0 |
| `resources/views/dashboard/partials/tab-my-tasks.blade.php` | 47 | 0 | 0 |
| `resources/views/welcome.blade.php` | 28 | 14 | 1 |
| `resources/views/components/kpi-card.blade.php` | 35 | 0 | 0 |
| `resources/views/superadmin/matrix.blade.php` | 30 | 0 | 0 |
| `resources/views/superadmin/cms/theme.blade.php` | 27 | 0 | 0 |
| `resources/views/superadmin/features.blade.php` | 27 | 0 | 0 |
| `resources/views/dashboard/partials/tab-meetings.blade.php` | 26 | 0 | 0 |
| `resources/views/landing/layout.blade.php` | 26 | 0 | 0 |
| `resources/views/dashboard/partials/tab-projects.blade.php` | 25 | 0 | 0 |
| `resources/views/components/meeting-row.blade.php` | 23 | 0 | 0 |
| `resources/views/components/badge.blade.php` | 22 | 0 | 0 |
| `resources/views/components/icon-btn.blade.php` | 22 | 0 | 0 |
| `resources/views/components/tabs.blade.php` | 22 | 0 | 0 |
| `resources/views/superadmin/cms/page_edit.blade.php` | 22 | 0 | 0 |
| `resources/views/components/card.blade.php` | 21 | 0 | 0 |
| `resources/views/components/btn.blade.php` | 20 | 0 | 0 |
| `resources/views/components/input.blade.php` | 18 | 0 | 0 |
| `resources/views/components/quick-tile.blade.php` | 18 | 0 | 0 |
| `resources/views/dashboard/partials/tab-members.blade.php` | 17 | 0 | 0 |
| `resources/views/superadmin/cms/assets.blade.php` | 16 | 0 | 0 |
| `resources/views/auth/register.blade.php` | 14 | 0 | 0 |
| `resources/views/components/modal.blade.php` | 12 | 0 | 0 |
| `resources/views/components/table.blade.php` | 9 | 0 | 0 |
| `resources/views/superadmin/cms/pages.blade.php` | 8 | 0 | 0 |
| `resources/views/components/checkbox.blade.php` | 7 | 0 | 0 |
| `resources/views/components/empty-state.blade.php` | 7 | 0 | 0 |
| `resources/views/dashboard/partials/tab-overview.blade.php` | 2 | 0 | 5 |
| `resources/views/components/switch.blade.php` | 5 | 0 | 0 |
| `resources/views/components/donut-chart.blade.php` | 4 | 0 | 0 |
| `resources/views/auth/login.blade.php` | 3 | 0 | 0 |

## Before you commit
1. Add the Cairo webfonts: `public/fonts/cairo/cairo-arabic.woff2` and `cairo-latin.woff2` (weights 300–700, from fonts.google.com/specimen/Cairo). Until they exist, Cairo falls back to IBM Plex Sans Arabic.
2. `npm run build` — Tailwind 4 picks up the new `@theme` bridge.
3. Sanity-check the three primary screens in Light+RTL, Light+LTR, Dark+RTL, Dark+LTR.
4. Delete the "LEGACY BRIDGE" block at the bottom of `ulaspace-tokens.css` once `grep -rn -- "--nx-\|--brand-\|--bg-base" resources/` is empty.
5. The three repo docs (`ULASPACE_DESIGN_SYSTEM_GUIDE.md`, `DESIGN_SYSTEM_IMPLEMENTATION.md`, `FIGMA_DESIGN_SYSTEM_AUDIT.md`) still describe the old values — they should be updated or pointed at `tokens.json`.

## Not done (say the word)
- Hard-coded hex values that never went through a token still exist in the largest views (`office.blade.php`, `projects/hub.blade.php`, `editor.blade.php`). Only the retired-palette hexes were corrected.
- The 42-icon Figma set and the Brand mark/lockup components were not exported as assets.
- Live reference page (queued).
