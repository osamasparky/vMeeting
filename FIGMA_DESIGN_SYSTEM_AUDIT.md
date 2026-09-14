# FIGMA DESIGN SYSTEM AUDIT & ARCHITECTURAL BLUEPRINT
**System**: UlaSpace — Design System
**Figma File Key**: `FGzWcAAHW0jnskIgxsCW9l`
**Node**: `0:1`
**Author**: Lead Frontend Architect & Design System Specialist
**Date**: September 2026

---

## 1. FIGMA FILES & METADATA ANALYZED
- **File Name**: `UlaSpace — Design System`
- **File ID**: `FGzWcAAHW0jnskIgxsCW9l`
- **Total Pages Analyzed**: 27 Pages
- **Total Document Styles**: 61 (Fills, Text Styles, Effects/Shadows)
- **Total Components**: 205 Components
- **Component Sets**: 30 Sets

---

## 2. FIGMA PAGES ANALYZED
1. `Cover` (id: `10:2`) — Brand arch, cover identity
2. `Getting Started` (id: `11:2`) — File map, bilingual structure, RTL rules, accessibility, decision logs
3. `——— FOUNDATIONS ———`
4. `Brand` (id: `27:2`) — Adopted mark, proportions, clear space, photography brief, tagline
5. `Color` (id: `14:2`) — Brand colors, full ramps (Palm, Sand, Gold, Terracotta, Stone), semantic tokens, contrast ratios, Light/Dark
6. `Typography` (id: `17:2`) — Typefaces (`IBM Plex Sans Arabic`, `IBM Plex Sans`, `IBM Plex Mono`), two-line rule, text styles hierarchy
7. `Layout` (id: `19:2`) — Spacing scale, 12-column grid, layout tokens, container widths
8. `Shape & Elevation` (id: `22:2`) — Corner radius scale, borders, elevation/shadows, focus rings, backdrop blur
9. `Motion & States` (id: `24:2`) — Duration tokens, easing curves, interaction states, reduced motion
10. `Icons` (id: `25:6`) — Material Symbols Rounded icon set, sizes (16, 18, 20, 24, 32), RTL directional rules
11. `——— COMPONENTS ———`
12. `Button` (id: `35:11`) — Primary, Secondary, Ghost, Danger, Outline across Sm (36px), Md (44px), Lg (52px)
13. `Icon Button` (id: `39:10`) — Standalone, grouped, toolbar, round vs square
14. `Chip & Tag` (id: `42:10`) — Status tags (Live, Scheduled, Attention, Cancelled), filter chips, removable chips
15. `Inputs` (id: `43:11`) — Text input, search field with shortcut badge, text area, floating/standard labels, helper text
16. `Selection Controls` (id: `46:9`) — Checkbox (20x20), Radio (20x20), Switch (44x26 pill), Segmented control
17. `Avatar` (id: `47:25`) — Avatar sizes (24, 32, 40, 48, 64), presence indicator dot, avatar stacks, overflow counter
18. `Cards & Data` (id: `48:10`) — KPI stat cards, meeting rows, quick action tiles, donut percentage charts, tables
19. `Empty State & Dialog` (id: `49:9`) — Modal dialogs, confirmation popups, empty state illustrations & copy
20. `Navigation & Bars` (id: `52:10`) — App shell top bar, sidebar navigation, dark floor map toolbar, language selector pill
21. `Page Blocks` (id: `54:11`) — Page headers, stats band, filter bars, split panels
22. `——— SCREENS ———`
23. `Website` (id: `56:14`) — Public landing / marketing page with arch identity
24. `Dashboard` (id: `57:9`) — Admin / user central workspace dashboard with KPI cards, meeting lists, donut stats, quick tiles
25. `Floor Map` (id: `58:10`) — Dark immersive virtual office map with room capsules, presence pills, floating controls
26. `——— INTERNAL ———`
27. `Utilities` (id: `69:9`) — Color cards, measurement rulers, testing helpers

---

## 3. DESIGN TOKENS & SYSTEM FOUNDATIONS

### A. Color Architecture & Palettes
The UlaSpace design system uses an organic, premium heritage palette inspired by desert sands, palms, and warm gold accents:

#### Brand Color Ramps:
1. **Palm (Primary Green)**:
   - `--nx-palm-950`: `#0b1410` (Deepest canvas / dark background)
   - `--nx-palm-900`: `#142b24` (Primary dark chrome / sidebar / dark navbar)
   - `--nx-palm-800`: `#17221f`
   - `--nx-palm-700`: `#1b3223` (Dark surface cards / elevated dark)
   - `--nx-palm-500`: `#1e412f` (Primary brand interactive)
   - `--nx-palm-300`: `#3c6b4c` (Live state / active highlight)
   - `--nx-palm-100`: `#e8f2ec` (Success soft background)
2. **Sand & Ivory (Light Surfaces & Page Backgrounds)**:
   - `--nx-sand-50`: `#ffffff` (Card surface light)
   - `--nx-sand-100`: `#f9f4ee` (Default page background)
   - `--nx-sand-200`: `#f4ede1` (Secondary light background)
   - `--nx-sand-300`: `#ede6d9` (Control fill on dark / muted container)
   - `--nx-sand-400`: `#e3d2bb` (Borders subtle / secondary text on dark)
   - `--nx-sand-500`: `#c1b6a6` (Divider / border default)
3. **Gold & Ochre (Accent & Scheduled)**:
   - `--nx-gold-200`: `#f6ebda` (Gold soft background)
   - `--nx-gold-400`: `#d3a553` (Primary Accent / Focus Ring / Highlights)
   - `--nx-gold-600`: `#b46c34` (Scheduled state / active gold)
4. **Terracotta (Attention / Danger / Live Action)**:
   - `--nx-terracotta-100`: `#faeae6`
   - `--nx-terracotta-500`: `#9a5827` (Attention / Warning / Danger)
5. **Stone (Neutral / Cancelled / Muted)**:
   - Tone Stone: `#e8e4dc` track, `#8e877c` text

#### Semantic Token Mapping:
| Semantic Token | Light Mode Value | Dark Mode Value |
| :--- | :--- | :--- |
| `--nx-bg-page` | `#f9f4ee` | `#0b1410` |
| `--nx-bg-surface` | `#ffffff` | `#142b24` |
| `--nx-bg-surface-elevated` | `#ffffff` | `#1b3223` |
| `--nx-bg-surface-hover` | `#f4ede1` | `#1e382f` |
| `--nx-text-primary` | `#142b24` | `#f9f4ee` |
| `--nx-text-secondary` | `#5a6b63` | `#e3d2bb` |
| `--nx-text-muted` | `#8e9d95` | `#a4b5ad` |
| `--nx-text-on-dark` | `#ffffff` | `#ffffff` |
| `--nx-text-on-dark-muted` | `#e3d2bb` | `#c1b6a6` |
| `--nx-border-subtle` | `rgba(27, 50, 35, 0.08)` | `rgba(237, 230, 217, 0.12)` |
| `--nx-border-default` | `rgba(27, 50, 35, 0.16)` | `rgba(237, 230, 217, 0.22)` |
| `--nx-border-strong` | `rgba(27, 50, 35, 0.28)` | `rgba(237, 230, 217, 0.38)` |
| `--nx-accent` | `#d3a553` | `#d3a553` |
| `--nx-accent-hover` | `#b46c34` | `#e5b765` |
| `--nx-status-live` | `#3c6b4c` | `#4ea66f` |
| `--nx-status-scheduled` | `#d3a553` | `#e5b765` |
| `--nx-status-attention` | `#9a5827` | `#c9743a` |
| `--nx-status-cancelled` | `#8e877c` | `#8e877c` |

---

## 4. TYPOGRAPHY HIERARCHY
- **Arabic Typeface (Primary)**: `'IBM Plex Sans Arabic', sans-serif`
- **Latin Typeface (Secondary / Companion)**: `'IBM Plex Sans', sans-serif`
- **Monospace (Data / Times / Codes)**: `'IBM Plex Mono', monospace`

### The Two-Line Rule:
Major headlines display the prominent Arabic heading on line 1 and a lighter companion English line on line 2 (e.g. `English/Display` or `English/H1` in 300 weight).

| Style Name | Size / Weight / Line-Height | CSS Token | Usage |
| :--- | :--- | :--- | :--- |
| `Display/Large` | 56px / 600 / 1.25 | `--nx-text-display-lg` | Landing hero title |
| `Display/Default` | 44px / 600 / 1.25 | `--nx-text-display` | Large section headings |
| `Metric/Large` | 40px / 300 / 1.1 | `--nx-text-metric-lg` | Hero KPI numbers (weight 300) |
| `Metric/Default` | 34px / 300 / 1.1 | `--nx-text-metric` | Dashboard stat values |
| `Heading/H1` | 34px / 600 / 1.25 | `--nx-text-h1` | Screen title / Page main heading |
| `Heading/Hero` | 30px / 600 / 1.30 | `--nx-text-hero` | Hero arch block headline |
| `Heading/H2` | 26px / 600 / 1.40 | `--nx-text-h2` | Major card / section heading |
| `Metric/Medium` | 26px / 600 / 1.1 | `--nx-text-metric-md` | Large donut percentage |
| `Metric/Small` | 23px / 600 / 1.1 | `--nx-text-metric-sm` | Donut percentage |
| `Heading/H3` | 21px / 600 / 1.40 | `--nx-text-h3` | Card headers |
| `Heading/H4` | 18px / 600 / 1.40 | `--nx-text-h4` | Sub-headings, dialog titles |
| `English/H1` | 18px / 300 / 1.40 | `--nx-text-h1-en` | Latin companion under H1 |
| `Title/Large` | 17px / 600 / 1.60 | `--nx-text-title-lg` | Dashboard panel titles |
| `Body/Large` | 17px / 400 / 1.60 | `--nx-text-body-lg` | Intro copy |
| `Button/Large` | 17px / 600 / 1.0 | `--nx-btn-lg` | Large action buttons |
| `Title/Default` | 15px / 600 / 1.60 | `--nx-text-title` | Standard card titles |
| `Body/Default` | 15px / 400 / 1.60 | `--nx-text-body` | Standard paragraph body |
| `Label/Default` | 15px / 500 / 1.60 | `--nx-text-label` | Prominent input labels |
| `Label/Compact` | 14px / 500 / 1.60 | `--nx-text-label-compact` | Meeting titles, tile labels |
| `Body/Compact` | 14px / 400 / 1.60 | `--nx-text-body-compact` | Nav links, select values |
| `Label/Small` | 13px / 500 / 1.60 | `--nx-text-label-sm` | Field labels, map toolbar |
| `Body/Small` | 13px / 400 / 1.60 | `--nx-text-sm` | List items, helper copy |
| `Label/XSmall` | 12px / 600 / 1.40 | `--nx-text-tag` | Status badges, tags |
| `Mono/Default` | 12px / 400 / 1.40 | `--nx-text-mono` | Timestamps, coordinates, counts |
| `English/Caption` | 11px / 400 / 1.40 | `--nx-text-caption-en` | Sub-label English companion |
| `Mono/Small` | 11px / 400 / 1.0 | `--nx-text-mono-sm` | Keyboard shortcut badges |

---

## 5. SPACING, RADII, SHADOWS & ELEVATION

### Spacing Scale (4px Base):
- Scale: `4px` (`--nx-space-1`), `8px` (`--nx-space-2`), `12px` (`--nx-space-3`), `14px` (`--nx-space-3.5`), `16px` (`--nx-space-4`), `20px` (`--nx-space-5`), `24px` (`--nx-space-6`), `32px` (`--nx-space-8`), `40px` (`--nx-space-10`), `48px` (`--nx-space-12`), `64px` (`--nx-space-16`), `80px` (`--nx-space-20`), `96px` (`--nx-space-24`), `120px` (`--nx-space-30`)
- Layout Max: `1440px` (`--nx-container-max`), Narrow: `1120px` (`--nx-container-narrow`), Card Padding: `24px`, Panel Padding: `32px`.

### Corner Radii:
- `--nx-radius-xs`: `6px` (Checkboxes, small badges)
- `--nx-radius-sm`: `10px` (Status squares, avatar presence, chips)
- `--nx-radius-md`: `14px` (Meeting rows, dropdown menus, inputs)
- `--nx-radius-lg`: `16px` (Standard cards, quick action tiles, modals)
- `--nx-radius-xl`: `20px` (Feature cards, page panels)
- `--nx-radius-arch`: `48px` (Hero arch top corners)
- `--nx-radius-pill`: `9999px` (Buttons, status capsules, avatars)

### Shadows & Effects:
- `--nx-shadow-sm`: `0 2px 8px -2px rgba(27, 50, 35, 0.10)`
- `--nx-shadow-md`: `0 8px 24px -8px rgba(27, 50, 35, 0.14)` (Card hover / elevation)
- `--nx-shadow-lg`: `0 20px 48px -12px rgba(27, 50, 35, 0.18)` (Dialogs & hero cards)
- `--nx-shadow-xl`: `0 32px 80px -16px rgba(27, 50, 35, 0.22)` (Modals & drawers)
- `--nx-shadow-on-map`: `0 8px 24px -8px rgba(0, 0, 0, 0.55)` (Floor map overlays)
- `--nx-focus-ring`: `0 0 0 2px var(--nx-bg-surface), 0 0 0 4px #d3a553`
- `--nx-focus-ring-on-dark`: `0 0 0 2px #142b24, 0 0 0 4px #d3a553`
- `--nx-backdrop-blur`: `blur(14px)` (Glassmorphic capsules & toolbars)

---

## 6. COMPONENT INVENTORY & COMPONENT MAPPING (30 Sets / 205 Components)

| Component / Set | Figma Set Name | Existing Status | Action Plan |
| :--- | :--- | :--- | :--- |
| **Buttons** | `Button` (Primary, Secondary, Ghost, Danger, Outline) | `EXISTS_BUT_NEEDS_REDESIGN` (`components/tactile-btn.blade.php`) | Upgrade with Figma pill radii, lift motion, gold focus rings |
| **Nav CTA** | `Nav CTA` | `MISSING` | Create pill CTA with `#ede6d9` fill and `#142b24` text |
| **Status Capsule** | `Status Capsule` (Occupancy, Presence) | `MISSING` | Implement glassmorphic pill status capsules with blur |
| **Donut KPI Chart** | `Donut` (Default 112px, Large 126px) | `PARTIAL` (Inline SVGs) | Create reusable `x-donut-chart` component |
| **Meeting Row** | `Meeting Row` (Live, Scheduled, Attention, Cancelled) | `EXISTS_BUT_NEEDS_REDESIGN` | Create reusable `x-meeting-row` component with status square |
| **Quick Action Tile** | `Quick Action Tile` (2x2 / 4x1 grid) | `EXISTS_BUT_NEEDS_REDESIGN` | Create reusable `x-quick-tile` component with hover elevation |
| **Checkboxes & Radios** | `Checkbox`, `Radio` | `PARTIAL` | Create unified `x-checkbox` and `x-radio` with gold checked state |
| **Switches** | `Switch` (44x26 pill) | `PARTIAL` | Create unified `x-switch` with RTL support |
| **Avatar & Stack** | `Avatar`, `Avatar Overflow` | `PARTIAL` | Create unified `x-avatar` with status presence and ring overflow |
| **Language Select** | `Language Select` | `PARTIAL` | Create standard pill language switcher |
| **Dark Toolbar Controls** | `Toolbar Button`, `Select Dark` | `EXISTS_BUT_NEEDS_REDESIGN` (`office.blade.php`) | Upgrade floor map controls to exact Figma specs |
| **Inputs & Textarea** | `Inputs`, `Search Field` | `EXISTS_BUT_NEEDS_REDESIGN` | Create `x-input`, `x-textarea`, `x-search` with `⌘K` badge |
| **Modals & Dialogs** | `Empty State & Dialog` | `EXISTS_BUT_NEEDS_REDESIGN` (`components/modal.blade.php`) | Upgrade modal dialogs with 20px radius, shadow XL, scrim |
| **Chips & Tags** | `Chip & Tag` | `EXISTS_BUT_NEEDS_REDESIGN` (`components/badge.blade.php`) | Upgrade badges with Live, Scheduled, Attention, Cancelled styles |
| **Tables** | `Cards & Data / Table` | `MISSING` (Ad-hoc tables) | Create unified `x-table` with sorting, search, bulk actions |
| **KPI Stat Cards** | `Cards & Data / Stat Card` | `EXISTS_BUT_NEEDS_REDESIGN` (`components/kpi-card.blade.php`) | Upgrade with 300 weight numbers, donut integration |
| **Empty States** | `Empty State` | `MISSING` | Create `x-empty-state` with bilingual headings and CTAs |

---

## 7. EXISTING APPLICATION MODULE AUDIT (55 Blade Views)
1. **Authentication** (`auth/login.blade.php`, `auth/register.blade.php`, `layouts/auth.blade.php`)
2. **Landing Page** (`landing/home.blade.php`, `landing/layout.blade.php`, `welcome.blade.php`)
3. **Dashboard (Main Shell + 17 Tabs)**:
   - `dashboard.blade.php` (App shell, top navigation, search, notifications, profile)
   - `tab-overview.blade.php` (KPI grid, Donut stats, Quick Action tiles, Recent Meetings, Activity)
   - `tab-meetings.blade.php` (Meeting scheduler, meeting list, filter pills, room badges)
   - `tab-all-tasks.blade.php` & `tab-my-tasks.blade.php` (Task board, task table, filters, status chips)
   - `tab-projects.blade.php` & `projects/hub.blade.php` (Project cards, milestone trackers, modals)
   - `tab-members.blade.php` & `tab-departments.blade.php` (Team grid, member cards, avatar stacks)
   - `tab-timesheets.blade.php` & `tab-workload.blade.php` (Timesheet tables, workload capacity charts)
   - `tab-rooms.blade.php` & `tab-offices.blade.php` (Room management, office floor spaces)
   - `tab-guests.blade.php`, `tab-billing.blade.php`, `tab-chat.blade.php`, `tab-audit.blade.php`, `tab-profile.blade.php`, `tab-settings.blade.php`
4. **Virtual Workplace / Floor Map**:
   - `office.blade.php` (Interactive 2D/3D floor plan, dark floating toolbar, room labels, audio/video)
   - `editor.blade.php` (Floor map editor, furniture catalog, zone tools)
   - `guest_join.blade.php` (Guest onboarding, permission prompts)
5. **Superadmin System**:
   - `superadmin/layout.blade.php`, `superadmin/dashboard.blade.php`, `superadmin/companies.blade.php`, `superadmin/plans.blade.php`, `superadmin/subscriptions.blade.php`, `superadmin/settings.blade.php`, `superadmin/health.blade.php`, `superadmin/furniture.blade.php`, `superadmin/translations.blade.php`, `superadmin/cms/*`

---

## 8. 4-WAY THEME & RTL ARCHITECTURE
- **Modes**:
  1. Light + LTR
  2. Light + RTL (`dir="rtl"` with Arabic type priority)
  3. Dark + LTR (`data-theme="dark"`)
  4. Dark + RTL (`data-theme="dark"` & `dir="rtl"`)
- **Logical CSS Rules**:
  - `margin-inline-start` / `margin-inline-end`
  - `padding-inline-start` / `padding-inline-end`
  - `inset-inline-start` / `inset-inline-end`
  - `text-align: start` / `text-align: end`
  - Donut arcs and numeric meters maintain clockwise direction.
  - Switches, chevron icons, and navigation arrows mirror in RTL.

---

## 9. QA & RISK MITIGATION CHECKLIST
- [ ] Preserve all Laravel controller bindings, Blade directives, live WebRTC connections, and form POST actions.
- [ ] Ensure WCAG AA contrast compliance across all text styles on Light and Dark backgrounds.
- [ ] Ensure all hover, focus-visible, and active states trigger smoothly without layout shift.
- [ ] Ensure responsive breakpoint scaling across mobile (<768px), tablet (768px-1024px), laptop (1200px), and desktop (1440px+).
