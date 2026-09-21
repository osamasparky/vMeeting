# vMeeting — project instructions

## Design system

This project uses the **UlaSpace design system**. Read `DESIGN_SYSTEM.md` at the repo root before writing any UI. It is generated from the Figma source file and is authoritative — if any other doc in the repo disagrees with it, that doc is stale.

Two supporting files:

- `virtual-workplace/resources/css/ulaspace-tokens.css` — every token as a CSS custom property, both themes, plus type and surface utilities
- `virtual-workplace/resources/design-system/tokens.json` — the same tokens machine-readable (226 variables, 47 text styles, 9 effect styles)

### Rules — these are not suggestions

**Tokens only.** Never write a raw hex, px radius, or duration in a view. Reference a token: `var(--ula-surface-card)`, not `#f9f6ef`. If the value you need has no token, stop and ask — do not invent one.

**Semantic, not primitive.** Use `--ula-text-primary`, not `--ula-palm-900`. Primitives exist so the ramps can be checked; referencing them directly breaks dark mode.

**Never use the retired names.** `--nx-*`, `--brand-*`, `--bg-base`, `--text-main` and the old blue/violet palette are gone. A compatibility block at the bottom of `ulaspace-tokens.css` keeps old references alive during migration; do not add new ones, and delete the block once `grep -rn -- "--nx-\|--brand-" resources/` is empty.

**Green acts, gold notices.** `accent/*` is palm green and carries action — primary buttons, active states, checked controls, links. `highlight/*` is gold and carries attention only — focus rings, quote rules, tile icons. Gold is never a button fill.

**Arabic first.** Arabic is the primary language, not a translation layer. Headings pair an Arabic line with a smaller, lighter English companion beneath it (`.ula-headline-group` / `.ula-headline-ar` / `.ula-headline-en`) — never English alone, never English on top.

**Isolate numbers.** Every number, time, price, count and ID rendered inside Arabic text needs `direction: ltr; unicode-bidi: isolate`, in `var(--ula-font-mono)`. Without it they read backwards.

**Logical properties.** `margin-inline-start`, `inset-inline-end`, `padding-inline` — never `left`/`right`. Every layout must work mirrored.

**Use the components.** 16 Blade components live in `resources/views/components/`. Use `<x-btn>`, `<x-badge>`, `<x-input>`, `<x-kpi-card>` and the rest instead of hand-rolling markup. Their geometry already matches Figma. Do not change their props.

### Before committing UI work

```bash
grep -rn -- "--nx-\|--brand-\|--bg-base\|--text-main" resources/   # must be empty
grep -rnE "#[0-9a-fA-F]{6}" resources/views/                       # must be empty
grep -rn 'href="#"' resources/views/                               # must be empty
npm run build
```

Then check the screen in all four combinations: light+RTL, light+LTR, dark+RTL, dark+LTR. Confirm the gold focus ring is visible on every interactive element, nothing interactive is under 44px, and the brand mark is legible against whatever surface it sits on.

### Known outstanding — do not "fix" these

- Cairo webfonts are not in the repo yet. `ulaspace-tokens.css` declares `@font-face` for `public/fonts/cairo/cairo-arabic.woff2` and `cairo-latin.woff2`; until those files exist Cairo falls back to IBM Plex Sans Arabic. **Do not remove the `@font-face` rules.**
- Icons are Material Symbols Rounded standing in for the 42-icon Figma set.
- Photography is placeholder stripes (`media/stripe-*`) wherever a real image is called for.
- Brand marks in `public/images/` are ivory-only and invisible on light surfaces — see `FIX_BRIEF.md` §1.

### Outstanding fixes

`FIX_BRIEF.md` at the repo root lists the current compliance gaps found against the live site, with the exact files and the verification commands. Work it top to bottom.

## Stack

Laravel 12 · Blade · Tailwind 4 · Vite. App root is `virtual-workplace/`.
