# Fix brief — UlaSpace design-system compliance

Paste this whole file to Claude Code (or any coding agent) in the `vMeeting` repo. Work top to bottom; every item is independent and safe to commit on its own.

## Ground rules

- The design system is `DESIGN_SYSTEM.md` at the repo root. It is generated from the Figma source and is authoritative. If any other doc disagrees with it, it is stale.
- All tokens live in `virtual-workplace/resources/css/ulaspace-tokens.css` as `--ula-*` custom properties. **Never** write a raw hex, px radius, or duration in a view — reference a token. If a value you need has no token, stop and say so rather than inventing one.
- Use semantic tokens (`--ula-surface-card`, `--ula-text-primary`), never primitives (`--ula-palm-700`) and never the retired `--nx-*` / `--brand-*` names.
- `accent/*` is palm green and carries action. `highlight/*` is gold and carries attention only. Gold is never a button fill.
- Do not change layout, routes, controller logic, JS behaviour, or component props. These are presentation and content fixes.
- Every change must work in all four combinations: light+RTL, light+LTR, dark+RTL, dark+LTR.

---

## 1. Brand marks are invisible on light backgrounds — BLOCKING

All four brand assets in `virtual-workplace/public/images/` are rendered in sand (`#f9f6ef`), so they only work on dark chrome:

- `ulaspace-logo.png`
- `ulaspace-icon.png`
- `brand/brand-lockup.png`
- `brand/mark.png`

On `login.blade.php` and `register.blade.php` the icon sits on a light panel and is effectively invisible.

**Fix.** Produce a palm-green variant of each (fill `--ula-brand-mark-green`, which is `palm/700` = `#1e412f`) and save alongside the existing files with a `-green` suffix:

```
public/images/ulaspace-icon-green.png
public/images/ulaspace-logo-green.png
public/images/brand/mark-green.png
public/images/brand/brand-lockup-green.png
```

Then pick by surface, not by page:
- on `surface/page`, `surface/card`, `surface/raised` → the `-green` asset
- on `surface/dark`, `surface/map-chrome`, or any photo scrim → the existing ivory asset

Files to update: `resources/views/auth/login.blade.php`, `resources/views/auth/register.blade.php`, `resources/views/layouts/auth.blade.php`, and the app bar / marketing nav partials. Add `alt="UlaSpace"` wherever it is missing.

Ask me before generating the assets if you cannot recolour a PNG cleanly — a hand-built SVG of the mark is preferable to a badly keyed PNG.

---

## 2. Remove emoji from the marketing page

`resources/views/landing/home.blade.php` contains 🎙️, ⚡ and 🌿. The system has no emoji — it uses a 42-icon set delivered as Material Symbols Rounded.

**Fix.** Replace each with a Material Symbols glyph at `--ula-icon-accent` or `--ula-icon-highlight`:

| Emoji | Context | Use |
| :--- | :--- | :--- |
| 🎙️ | "Spatial Audio Mesh" chip | `graphic_eq` |
| ⚡ | "Ultra-low latency WebRTC" chip | `bolt` |
| 🌿 | footer tagline | `eco` |

Markup pattern already used elsewhere in the repo:

```html
<span class="material-symbols-rounded" style="font-size: 18px; color: var(--ula-icon-accent)">graphic_eq</span>
```

---

## 3. English-only strings in an Arabic-first product

The system's two-line rule: Arabic is primary, English is a smaller, lighter companion line beneath it — never English alone, never English on top.

**Offending strings** (all in `resources/views/landing/home.blade.php`, hero floor-map card):
- `Floor 1 • Main Headquarters`
- `18 Active Members`
- `Palm Boardroom • 4 In Call`
- `Innovation Lounge • 2 Desks`

**Fix.** Give each an Arabic primary line and demote the English, using the existing utility classes from `ulaspace-tokens.css`:

```html
<div class="ula-headline-group">
  <span class="ula-headline-ar" style="font-size: 15px">الطابق الأول · المقر الرئيسي</span>
  <span class="ula-headline-en" style="font-size: 11px">Floor 1 · Main Headquarters</span>
</div>
```

Suggested Arabic (change if the product team has better wording):
- `الطابق الأول · المقر الرئيسي`
- `18 عضواً متصلاً`
- `قاعة النخيل · 4 في مكالمة`
- `مساحة الابتكار · مكتبان متاحان`

Also replace the `•` separator with `·` (U+00B7) throughout — the bullet renders badly in RTL runs.

Same treatment for the subscription plan names in `register.blade.php`: `Free` / `Starter` / `Business` / `Enterprise` become `مجاني` / `مبتدئ` / `أعمال` / `مؤسسات` with the English as the companion line. Keep prices in `--ula-font-mono` and wrap them so they read correctly in RTL:

```html
<span style="font-family: var(--ula-font-mono); direction: ltr; unicode-bidi: isolate">$30/mo</span>
```

**Apply that `direction: ltr; unicode-bidi: isolate` pair to every number, time, price, count and ID rendered inside Arabic text**, across all views. This is a systemic bug, not a one-off — audit for it.

---

## 4. Status labels must use the Tag component

The hero map cards show `LIVE` and `OPEN` as bare English capitals. The system's Tag component is pill, 29px tall, 12px horizontal padding, a 7px dot, tonal `tone/*` fill, no border, Arabic label.

**Fix.** Use the existing Blade component instead of hand-rolled markup:

```blade
<x-badge variant="live" dot>مباشر</x-badge>
<x-badge variant="scheduled" dot>متاح</x-badge>
```

Then grep the whole view layer for hand-rolled status pills and replace them with `<x-badge>`:

```
grep -rn "LIVE\|OPEN\|BUSY\|AWAY" resources/views/
```

---

## 5. Dead links

- `نسيت كلمة المرور؟` in `login.blade.php` points at `#`.
- All three legal links in the marketing footer point at `#`: `سياسة الخصوصية`, `شروط الخدمة`, `الأمن وحماية البيانات`.

**Fix.** Wire them to real routes if they exist. If they do not, remove the anchor and render plain text — a link that goes nowhere is worse than no link. Do not leave `href="#"`.

---

## 6. Missing pricing section

Both the marketing nav and the footer link to `#pricing`, and the nav item is labelled `الباقات والأسعار`, but no element with `id="pricing"` exists in `home.blade.php`. Both links are broken.

**Fix.** Either add the pricing section (the four plans already exist in `register.blade.php` — reuse those plan names and prices, built from `<x-card>` and `<x-btn>`), or remove both links and the nav item. Ask me which; do not guess.

---

## 7. Verification before you commit

```bash
# 1. No retired token names anywhere
grep -rn -- "--nx-\|--brand-\|--bg-base\|--text-main\|--accent-gradient" resources/

# 2. No raw hex in views (tokens only)
grep -rnE "#[0-9a-fA-F]{6}" resources/views/ | grep -v "material-symbols"

# 3. No emoji
grep -rnP "[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]" resources/views/

# 4. No dead links
grep -rn 'href="#"' resources/views/

# 5. Build
npm run build
```

All five must come back clean (or, for #1, only the clearly-marked legacy bridge block at the bottom of `ulaspace-tokens.css`).

Then check the three primary screens — landing, dashboard, office — in light+RTL, light+LTR, dark+RTL, dark+LTR, and confirm:
- focus ring is visible on every interactive element (gold, 2px gap + 4px ring)
- no interactive control is under 44px
- brand mark is legible on whatever surface it sits on
- numbers and times read left-to-right inside Arabic sentences

---

## 8. Known outstanding, do not treat as bugs

- **Cairo webfonts are missing.** `ulaspace-tokens.css` declares `@font-face` for `public/fonts/cairo/cairo-arabic.woff2` and `cairo-latin.woff2`; neither file exists, so Cairo currently falls back to IBM Plex Sans Arabic. Add them (weights 300–700, from fonts.google.com/specimen/Cairo) — do not remove the `@font-face` rules.
- **Icons are Material Symbols stand-ins** for the 42-icon Figma set. Fine for now.
- **Photography is placeholder** wherever `media/stripe-*` is used.
- Three older docs (`ULASPACE_DESIGN_SYSTEM_GUIDE.md`, `DESIGN_SYSTEM_IMPLEMENTATION.md`, `FIGMA_DESIGN_SYSTEM_AUDIT.md`) describe pre-migration values. Delete them or replace with a pointer to `DESIGN_SYSTEM.md`.

---

## 9. What I could not check

This review covered the three public pages (`/`, `/login`, `/register`) plus the repo source. The authenticated screens — dashboard, floor map, projects hub, editor, superadmin — were not visually reviewed. After the above is done, take screenshots of those in all four theme/direction combinations so they can be audited against the system.
