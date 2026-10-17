---
trigger: always_on
---
# UlaSpace Design System Rules

`DESIGN_SYSTEM.md` at the repo root is authoritative, and `CLAUDE.md` summarises its rules. If this file ever disagrees with them, they win. Read `DESIGN_SYSTEM.md` before writing any UI.

Whenever you modify or build frontend views, Blade templates, styles or components in this repository:

1. **Tokens only — the `--ula-*` set**
   - Every colour, radius, shadow, spacing and duration comes from a token in `virtual-workplace/resources/css/ulaspace-tokens.css`, e.g. `var(--ula-surface-card)`, `var(--ula-radius-lg)`, `var(--ula-duration-fast)`.
   - Never write a raw hex, px radius or duration in a view. If the value you need has no token, stop and ask; don't invent one.
   - Use **semantic** tokens (`--ula-text-primary`, `--ula-surface-raised`, `--ula-accent-default`, `--ula-border-subtle`, `--ula-status-danger`), not primitives (`--ula-palm-900`, `--ula-sand-200`). Primitives break dark mode.
   - The old names are retired: `--nx-*`, `--brand-*`, `--bg-base`, `--text-main`. Never add new references to them.

2. **Green acts, gold notices**
   - `accent/*` (palm green) carries action: primary buttons, active states, checked controls, links.
   - `highlight/*` (gold) carries attention only: focus rings, quote rules, tile icons. Gold is never a button fill.

3. **Four-way theme and RTL**
   - Use logical properties only: `margin-inline-start`, `padding-inline-end`, `inset-inline-start`, `inset-block-end`, `text-align: start`. Never `left` / `right`.
   - Every screen must work in light+RTL, light+LTR, dark+RTL and dark+LTR.
   - Every number, time, price, count and ID inside Arabic text needs `direction: ltr; unicode-bidi: isolate` in `var(--ula-font-mono)`.

4. **Typography**
   - **Cairo** for Arabic (headings, body, UI labels), **IBM Plex Sans** for English companion lines and the wordmark, **IBM Plex Mono** for data (times, counts, IDs).
   - Arabic is the primary language. Headings pair an Arabic line with a smaller, lighter English companion beneath it (`.ula-headline-group` / `.ula-headline-ar` / `.ula-headline-en`). Never English alone, never English on top.

5. **Use the Blade components** in `virtual-workplace/resources/views/components/` instead of hand-rolled markup. Their geometry matches Figma; don't change their props.
   - `<x-btn variant="primary|secondary|outline|ghost|danger" size="sm|md|lg">`, `<x-icon-btn>`, `<x-badge>`, `<x-input>`, `<x-checkbox>`, `<x-switch>`, `<x-tabs>`, `<x-table>`, `<x-modal>`, `<x-card>`, `<x-kpi-card>`, `<x-kpi-chip>`, `<x-stat-card>`, `<x-meeting-row>`, `<x-task-card>`, `<x-quick-tile>`, `<x-donut-chart>`, `<x-empty-state>`.

6. **Accessibility**
   - Nothing interactive is under 44px.
   - The gold focus ring (`var(--ula-focus-ring)`) is visible on every interactive element.
   - Respect `prefers-reduced-motion`.

Before committing UI work, run the checks listed in `CLAUDE.md` (retired-name grep, raw-hex grep, `href="#"` grep, `npm run build`).
