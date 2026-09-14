---
trigger: always_on
---
# UlaSpace Design System Rules

Whenever modifying or building frontend views, Blade templates, styles, or components in this repository:

1. **Strict Design Tokens**:
   - Always use `--nx-*` CSS custom properties for all colors, fonts, spacing, shadows, and radii.
   - Never use arbitrary hard-coded hex colors (like `#ffffff`, `#1e293b`, `#000000`) or ad-hoc margins/paddings.
   - Use semantic tokens: `--nx-bg-page`, `--nx-bg-surface`, `--nx-text-primary`, `--nx-text-secondary`, `--nx-border-subtle`, `--nx-accent`, etc.

2. **4-Way Theme & RTL Compliance**:
   - Use CSS logical properties exclusively: `margin-inline-start`, `margin-inline-end`, `padding-inline-start`, `padding-inline-end`, `inset-inline-start`, `text-align: start`.
   - The UI must work identically in Light+LTR, Light+RTL, Dark+LTR, and Dark+RTL.

3. **Typography Standards**:
   - Arabic typeface is `'IBM Plex Sans Arabic', sans-serif`.
   - English typeface is `'IBM Plex Sans', sans-serif`.
   - Data/numbers typeface is `'IBM Plex Mono', monospace`.
   - Apply the two-line rule for headings: primary Arabic headline on line 1, lighter English companion on line 2.

4. **Reusable Blade Components**:
   - Buttons: `<x-btn variant="primary|secondary|ghost|danger|outline|nav-cta" size="sm|md|lg">`
   - KPI Cards: `<x-kpi-card title="..." value="..." :donut="78">`
   - Meeting Rows: `<x-meeting-row :status="'live'|'scheduled'|'attention'|'cancelled'" ...>`
   - Quick Tiles: `<x-quick-tile icon="..." label="...">`
   - Donut Charts: `<x-donut-chart :percent="84" :size="'lg'">`
   - Form Inputs: `<x-input>`, `<x-checkbox>`, `<x-switch>`
   - Tables: `<x-table>`
   - Modals: `<x-modal>`
