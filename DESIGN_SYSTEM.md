# UlaSpace — Design System

Bilingual (Arabic / English) design system for UlaSpace, a virtual-workplace product: a floor map you move around, meeting rooms you walk into, and a dashboard that reports on the space.

This document is generated from the Figma source file **UlaSpace — Design System** and is the reference for the implementation in `virtual-workplace/resources/`. Where this document and any other doc in the repo disagree, this one is correct — it was read from the file itself.

| | |
| :--- | :--- |
| Source | `uploads/UlaSpace - Design System.fig` |
| Read | 2026-09-16 |
| Variables | 226 across 6 collections |
| Text styles | 47 |
| Effect styles | 9 |
| Component sets | 45 (≈210 variants) |
| Pages in file | 28 |
| Implementation | `resources/css/ulaspace-tokens.css`, `resources/design-system/tokens.json` |

---

## 1. Design principles

**Arabic first.** Arabic is the primary language, not a translation layer. Type is set in Cairo; English sits underneath as a lighter companion line in IBM Plex Sans, never the other way round. Every layout works mirrored — direction is a `[dir]` attribute, not a separate design.

**Warm, not clinical.** The palette is sand and palm — warm paper, deep green — rather than the blue-grey default of workplace software. A page is `#fbf8f2`, not white.

**Green carries action, gold carries attention.** `accent/*` (palm green) is for what the user does: primary buttons, active states, checked controls. `highlight/*` (gold) is for what the user should notice: focus rings, quote rules, selected map elements. They are not interchangeable.

**Light chrome, dark canvas.** Dashboards and marketing surfaces are light. The floor map is dark — it is a space you look into, and glass capsules float above it. Both share the same tokens; only the semantic layer swaps.

**Restraint in elevation.** Five shadow steps exist, and the first two do nearly all the work. A card is a hairline border plus `Shadow/XS`. Reach for LG or XL only when something genuinely floats over the page.

---

## 2. Typography

Three families, each with one job.

| Family | Role | Weights |
| :--- | :--- | :--- |
| **Cairo** | Arabic — all headings, body, UI labels | 300, 400, 500, 600, 700 |
| **IBM Plex Sans** | English companion lines, brand wordmark | 300, 400, 500, 600 |
| **IBM Plex Mono** | Data: times, counts, IDs, token names | 400, 500 |

Cairo is self-hosted (`public/fonts/cairo/`) with Arabic and Latin subsets split by `unicode-range`. IBM Plex loads from the Google CDN.

### The two-line rule

Arabic headline, English companion beneath it, lighter and smaller:

```html
<div class="ula-headline-group">
  <span class="ula-headline-ar" style="font-size: 30px">صباح الخير، سعود</span>
  <span class="ula-headline-en" style="font-size: 18px">Good to see you again</span>
</div>
```

The English line is `text/secondary`, weight 300. It never competes with the Arabic.

### Text styles

| Style | Family | Weight | Size | Line height | Tracking |
| :--- | :--- | :--- | ---: | ---: | ---: |
| Display/Large | Cairo | SemiBold | 56px | 1.25 | 0em |
| Display/Default | Cairo | SemiBold | 44px | 1.25 | 0em |
| Heading/H1 | Cairo | SemiBold | 34px | 1.25 | 0em |
| Heading/H2 | Cairo | SemiBold | 26px | 1.4 | 0em |
| Heading/H3 | Cairo | SemiBold | 21px | 1.4 | 0em |
| Heading/H4 | Cairo | SemiBold | 18px | 1.4 | 0em |
| Title/Large | Cairo | SemiBold | 17px | 1.6 | 0em |
| Title/Default | Cairo | SemiBold | 15px | 1.6 | 0em |
| Body/Large | Cairo | Regular | 17px | 1.6 | 0em |
| Body/Default | Cairo | Regular | 15px | 1.6 | 0em |
| Body/Compact | Cairo | Regular | 14px | 1.6 | 0em |
| Body/Small | Cairo | Regular | 13px | 1.6 | 0em |
| Body/XSmall | Cairo | Regular | 12px | 1.6 | 0em |
| Body/Tiny | Cairo | Regular | 11px | 1.6 | 0em |
| Body/Micro | Cairo | Regular | 10px | 1.6 | 0em |
| Label/Default | Cairo | Medium | 15px | 1.6 | 0em |
| Label/Compact | Cairo | Medium | 14px | 1.6 | 0em |
| Label/Small | Cairo | Medium | 13px | 1.6 | 0em |
| Label/XSmall | Cairo | Medium | 12px | 1.6 | 0em |
| Button/Large | Cairo | SemiBold | 17px | 22px | 0em |
| Button/Medium | Cairo | SemiBold | 15px | 20px | 0em |
| Button/Small | Cairo | SemiBold | 13px | 20px | 0em |
| Button/Large Soft | Cairo | Medium | 17px | 22px | 0em |
| Button/Medium Soft | Cairo | Medium | 15px | 20px | 0em |
| Button/Small Soft | Cairo | Medium | 13px | 20px | 0em |
| Metric/Large | Cairo | Light | 40px | 1.1 | 0em |
| Metric/Default | Cairo | Light | 34px | 1.15 | 0em |
| Metric/Medium | Cairo | SemiBold | 26px | 1.1 | 0em |
| Metric/Small | Cairo | SemiBold | 23px | 1 | 0em |
| English/Display | IBM Plex Sans | Light | 24px | 1.4 | 0em |
| English/H1 | IBM Plex Sans | Light | 18px | 1.4 | 0em |
| English/H2 | IBM Plex Sans | Regular | 15px | 1.5 | 0em |
| English/Body | IBM Plex Sans | Regular | 14px | 1.5 | 0em |
| English/Small | IBM Plex Sans | Regular | 13px | 1.5 | 0em |
| English/XSmall | IBM Plex Sans | Regular | 12px | 1.5 | 0em |
| English/Caption | IBM Plex Sans | Regular | 11px | 1.5 | 0em |
| Label/Brand | IBM Plex Sans | Medium | 11px | 1.5 | 0.14em |
| Mono/Default | IBM Plex Mono | Regular | 12px | 1.5 | 0em |
| Mono/Small | IBM Plex Mono | Regular | 11px | 1.5 | 0em |
| Mono/Tiny | IBM Plex Mono | Regular | 10px | 1.4 | 0em |
| Label/XSmall Strong | Cairo | SemiBold | 12px | 1.6 | 0em |
| English/Label | IBM Plex Sans | Medium | 13px | 1.5 | 0em |
| Title/Small | Cairo | SemiBold | 14px | 1.6 | 0em |
| Heading/Hero | Cairo | SemiBold | 30px | 1.3 | 0em |
| Heading/Quote | Cairo | SemiBold | 23px | 1.4 | 0em |
| Brand/Wordmark Small | IBM Plex Sans | Medium | 16px | 1.1 | 0em |
| Brand/Wordmark Large | IBM Plex Sans | SemiBold | 28px | 1.1 | -0.01em |

### Typography tokens

| Figma | CSS | Value |
| :--- | :--- | :--- |
| `font/ar` | `--ula-font-ar` | Cairo |
| `font/en` | `--ula-font-en` | IBM Plex Sans |
| `font/mono` | `--ula-font-mono` | IBM Plex Mono |
| `size/display-lg` | `--ula-size-display-lg` | 56 |
| `size/display` | `--ula-size-display` | 44 |
| `size/h1` | `--ula-size-h1` | 34 |
| `size/h2` | `--ula-size-h2` | 26 |
| `size/h3` | `--ula-size-h3` | 21 |
| `size/h4` | `--ula-size-h4` | 18 |
| `size/body-lg` | `--ula-size-body-lg` | 17 |
| `size/body` | `--ula-size-body` | 15 |
| `size/sm` | `--ula-size-sm` | 13 |
| `size/xs` | `--ula-size-xs` | 12 |
| `size/label` | `--ula-size-label` | 11 |
| `size/display-en` | `--ula-size-display-en` | 24 |
| `size/h1-en` | `--ula-size-h1-en` | 18 |
| `size/h2-en` | `--ula-size-h2-en` | 15 |
| `size/body-en` | `--ula-size-body-en` | 14 |
| `weight/light` | `--ula-weight-light` | 300 |
| `weight/regular` | `--ula-weight-regular` | 400 |
| `weight/medium` | `--ula-weight-medium` | 500 |
| `weight/semibold` | `--ula-weight-semibold` | 600 |
| `weight/bold` | `--ula-weight-bold` | 700 |

---

## 3. Color

### 3.1 Primitives

Raw ramps. **Never reference these in a component** — always use a semantic token so dark mode follows.

#### Palm — brand green, the action color
| Figma | CSS | Hex |
| :--- | :--- | :--- |
| `palm/50` | `--ula-palm-50` | #f0f4f0 |
| `palm/100` | `--ula-palm-100` | #dbe6dd |
| `palm/200` | `--ula-palm-200` | #b6c9bb |
| `palm/300` | `--ula-palm-300` | #8baa94 |
| `palm/400` | `--ula-palm-400` | #5e8a6c |
| `palm/500` | `--ula-palm-500` | #3c6b4c |
| `palm/600` | `--ula-palm-600` | #2a5439 |
| `palm/700` | `--ula-palm-700` | #1e412f |
| `palm/800` | `--ula-palm-800` | #1b3223 |
| `palm/900` | `--ula-palm-900` | #142b24 |
| `palm/950` | `--ula-palm-950` | #0e1c17 |
| `palm/chrome` | `--ula-palm-chrome` | #17221f |

#### Sand — warm paper
| Figma | CSS | Hex |
| :--- | :--- | :--- |
| `sand/50` | `--ula-sand-50` | #fbf8f2 |
| `sand/100` | `--ula-sand-100` | #f9f6ef |
| `sand/200` | `--ula-sand-200` | #f4ede1 |
| `sand/300` | `--ula-sand-300` | #ede6d9 |
| `sand/400` | `--ula-sand-400` | #e3d2bb |
| `sand/500` | `--ula-sand-500` | #d6c0a2 |
| `sand/600` | `--ula-sand-600` | #c3a882 |

#### Stone — neutral text and borders
| Figma | CSS | Hex |
| :--- | :--- | :--- |
| `stone/100` | `--ula-stone-100` | #e8e4dc |
| `stone/200` | `--ula-stone-200` | #d9d3c8 |
| `stone/300` | `--ula-stone-300` | #c1b6a6 |
| `stone/400` | `--ula-stone-400` | #a49889 |
| `stone/500` | `--ula-stone-500` | #857a6c |
| `stone/600` | `--ula-stone-600` | #665d52 |
| `stone/700` | `--ula-stone-700` | #4a443c |

#### Gold — attention and focus
| Figma | CSS | Hex |
| :--- | :--- | :--- |
| `gold/200` | `--ula-gold-200` | #f0dfbc |
| `gold/300` | `--ula-gold-300` | #e6c88b |
| `gold/400` | `--ula-gold-400` | #d3a553 |
| `gold/500` | `--ula-gold-500` | #b98a37 |
| `gold/600` | `--ula-gold-600` | #946c27 |

#### Terracotta — danger and warm accent
| Figma | CSS | Hex |
| :--- | :--- | :--- |
| `terracotta/200` | `--ula-terracotta-200` | #ecc9ae |
| `terracotta/300` | `--ula-terracotta-300` | #d99a6c |
| `terracotta/400` | `--ula-terracotta-400` | #b46c34 |
| `terracotta/500` | `--ula-terracotta-500` | #9a5827 |
| `terracotta/600` | `--ula-terracotta-600` | #7d451d |

#### Base and alphas
| Figma | CSS | Hex |
| :--- | :--- | :--- |
| `white` | `--ula-white` | #ffffff |
| `black` | `--ula-black` | #0b1410 |
| `alpha/ivory-8` | `--ula-alpha-ivory-8` | #ede6d914 |
| `alpha/ivory-10` | `--ula-alpha-ivory-10` | #ede6d91a |
| `alpha/ivory-16` | `--ula-alpha-ivory-16` | #ede6d929 |
| `alpha/ivory-18` | `--ula-alpha-ivory-18` | #ede6d92e |
| `alpha/ivory-30` | `--ula-alpha-ivory-30` | #ede6d94d |
| `alpha/page-35` | `--ula-alpha-page-35` | #fbf8f259 |
| `alpha/page-72` | `--ula-alpha-page-72` | #fbf8f2b8 |
| `alpha/palm-950-48` | `--ula-alpha-palm-950-48` | #0e1c177a |
| `alpha/palm-950-62` | `--ula-alpha-palm-950-62` | #0e1c179e |
| `alpha/palm-950-72` | `--ula-alpha-palm-950-72` | #0e1c17b8 |
| `alpha/palm-400-14` | `--ula-alpha-palm-400-14` | #5e8a6c24 |
| `alpha/gold-400-18` | `--ula-alpha-gold-400-18` | #d3a5532e |
| `alpha/terracotta-400-20` | `--ula-alpha-terracotta-400-20` | #b46c3433 |
| `alpha/shadow-6` | `--ula-alpha-shadow-6` | #1b32230f |
| `alpha/shadow-10` | `--ula-alpha-shadow-10` | #1b32231a |
| `alpha/shadow-14` | `--ula-alpha-shadow-14` | #1b322324 |
| `alpha/shadow-18` | `--ula-alpha-shadow-18` | #1b32232e |
| `alpha/shadow-22` | `--ula-alpha-shadow-22` | #1b322338 |
| `alpha/black-50` | `--ula-alpha-black-50` | #00000080 |
| `alpha/black-55` | `--ula-alpha-black-55` | #0000008c |
| `alpha/black-60` | `--ula-alpha-black-60` | #00000099 |
| `alpha/ivory-14` | `--ula-alpha-ivory-14` | #ede6d924 |

### 3.2 Semantic tokens

104 tokens, each with a Light and a Dark value. These are what components use.

#### Surface
| Figma | CSS | Light | Dark |
| :--- | :--- | :--- | :--- |
| `surface/page` | `--ula-surface-page` | sand/50 · #fbf8f2 | palm/950 · #0e1c17 |
| `surface/page-alt` | `--ula-surface-page-alt` | sand/200 · #f4ede1 | palm/900 · #142b24 |
| `surface/card` | `--ula-surface-card` | sand/100 · #f9f6ef | palm/900 · #142b24 |
| `surface/raised` | `--ula-surface-raised` | white · #ffffff | palm/800 · #1b3223 |
| `surface/sunken` | `--ula-surface-sunken` | sand/200 · #f4ede1 | palm/950 · #0e1c17 |
| `surface/dark` | `--ula-surface-dark` | palm/900 · #142b24 | palm/950 · #0e1c17 |
| `surface/dark-alt` | `--ula-surface-dark-alt` | palm/800 · #1b3223 | palm/900 · #142b24 |
| `surface/accent-soft` | `--ula-surface-accent-soft` | palm/50 · #f0f4f0 | alpha/palm-400-14 · #5e8a6c24 |
| `surface/gold-soft` | `--ula-surface-gold-soft` | gold/200 · #f0dfbc | alpha/gold-400-18 · #d3a5532e |
| `surface/danger-soft` | `--ula-surface-danger-soft` | terracotta/200 · #ecc9ae | alpha/terracotta-400-20 · #b46c3433 |
| `surface/warm` | `--ula-surface-warm` | sand/400 · #e3d2bb | palm/800 · #1b3223 |
| `surface/overlay` | `--ula-surface-overlay` | alpha/palm-950-48 · #0e1c177a | alpha/palm-950-48 · #0e1c177a |
| `surface/capsule` | `--ula-surface-capsule` | alpha/palm-950-62 · #0e1c179e | alpha/palm-950-62 · #0e1c179e |
| `surface/map-chrome` | `--ula-surface-map-chrome` | palm/chrome · #17221f | palm/chrome · #17221f |
| `surface/hover` | `--ula-surface-hover` | sand/200 · #f4ede1 | palm/800 · #1b3223 |
| `surface/pressed` | `--ula-surface-pressed` | sand/300 · #ede6d9 | palm/950 · #0e1c17 |
| `surface/map-canvas` | `--ula-surface-map-canvas` | palm/950 · #0e1c17 | palm/950 · #0e1c17 |
| `surface/capsule-strong` | `--ula-surface-capsule-strong` | alpha/palm-950-72 · #0e1c17b8 | alpha/palm-950-72 · #0e1c17b8 |

#### Text
| Figma | CSS | Light | Dark |
| :--- | :--- | :--- | :--- |
| `text/primary` | `--ula-text-primary` | palm/900 · #142b24 | sand/100 · #f9f6ef |
| `text/strong` | `--ula-text-strong` | palm/800 · #1b3223 | sand/100 · #f9f6ef |
| `text/body` | `--ula-text-body` | stone/700 · #4a443c | sand/300 · #ede6d9 |
| `text/secondary` | `--ula-text-secondary` | stone/600 · #665d52 | sand/400 · #e3d2bb |
| `text/muted` | `--ula-text-muted` | stone/500 · #857a6c | stone/300 · #c1b6a6 |
| `text/on-dark` | `--ula-text-on-dark` | sand/100 · #f9f6ef | sand/100 · #f9f6ef |
| `text/on-dark-muted` | `--ula-text-on-dark-muted` | sand/400 · #e3d2bb | sand/400 · #e3d2bb |
| `text/on-accent` | `--ula-text-on-accent` | sand/50 · #fbf8f2 | sand/50 · #fbf8f2 |
| `text/on-gold` | `--ula-text-on-gold` | palm/900 · #142b24 | palm/900 · #142b24 |
| `text/link` | `--ula-text-link` | palm/600 · #2a5439 | gold/300 · #e6c88b |
| `text/link-hover` | `--ula-text-link-hover` | palm/800 · #1b3223 | gold/200 · #f0dfbc |
| `text/danger` | `--ula-text-danger` | terracotta/600 · #7d451d | terracotta/300 · #d99a6c |
| `text/disabled` | `--ula-text-disabled` | stone/400 · #a49889 | stone/500 · #857a6c |
| `text/on-dark-subtle` | `--ula-text-on-dark-subtle` | stone/300 · #c1b6a6 | stone/300 · #c1b6a6 |
| `text/on-cta` | `--ula-text-on-cta` | palm/900 · #142b24 | palm/900 · #142b24 |

#### Border
| Figma | CSS | Light | Dark |
| :--- | :--- | :--- | :--- |
| `border/subtle` | `--ula-border-subtle` | stone/100 · #e8e4dc | alpha/ivory-10 · #ede6d91a |
| `border/default` | `--ula-border-default` | stone/200 · #d9d3c8 | alpha/ivory-18 · #ede6d92e |
| `border/strong` | `--ula-border-strong` | stone/300 · #c1b6a6 | alpha/ivory-30 · #ede6d94d |
| `border/hover` | `--ula-border-hover` | stone/500 · #857a6c | stone/300 · #c1b6a6 |
| `border/focus` | `--ula-border-focus` | palm/500 · #3c6b4c | palm/400 · #5e8a6c |
| `border/danger` | `--ula-border-danger` | terracotta/400 · #b46c34 | terracotta/300 · #d99a6c |
| `border/on-dark` | `--ula-border-on-dark` | alpha/ivory-18 · #ede6d92e | alpha/ivory-18 · #ede6d92e |
| `border/on-dark-subtle` | `--ula-border-on-dark-subtle` | alpha/ivory-10 · #ede6d91a | alpha/ivory-10 · #ede6d91a |

#### Accent and highlight
| Figma | CSS | Light | Dark |
| :--- | :--- | :--- | :--- |
| `accent/default` | `--ula-accent-default` | palm/700 · #1e412f | palm/400 · #5e8a6c |
| `accent/hover` | `--ula-accent-hover` | palm/600 · #2a5439 | palm/300 · #8baa94 |
| `accent/press` | `--ula-accent-press` | palm/800 · #1b3223 | palm/500 · #3c6b4c |
| `accent/fg` | `--ula-accent-fg` | sand/50 · #fbf8f2 | palm/950 · #0e1c17 |
| `highlight/default` | `--ula-highlight-default` | gold/400 · #d3a553 | gold/400 · #d3a553 |
| `highlight/ink` | `--ula-highlight-ink` | gold/600 · #946c27 | gold/600 · #946c27 |

#### Status
| Figma | CSS | Light | Dark |
| :--- | :--- | :--- | :--- |
| `status/success` | `--ula-status-success` | palm/500 · #3c6b4c | palm/500 · #3c6b4c |
| `status/warning` | `--ula-status-warning` | gold/500 · #b98a37 | gold/500 · #b98a37 |
| `status/danger` | `--ula-status-danger` | terracotta/500 · #9a5827 | terracotta/500 · #9a5827 |
| `status/danger-hover` | `--ula-status-danger-hover` | terracotta/600 · #7d451d | terracotta/600 · #7d451d |
| `status/info` | `--ula-status-info` | palm/400 · #5e8a6c | palm/400 · #5e8a6c |
| `status/success-on-dark` | `--ula-status-success-on-dark` | palm/300 · #8baa94 | palm/300 · #8baa94 |

#### Focus
| Figma | CSS | Light | Dark |
| :--- | :--- | :--- | :--- |
| `focus/ring` | `--ula-focus-ring` | gold/400 · #d3a553 | gold/400 · #d3a553 |
| `focus/gap` | `--ula-focus-gap` | sand/50 · #fbf8f2 | palm/950 · #0e1c17 |
| `focus/gap-on-dark` | `--ula-focus-gap-on-dark` | palm/900 · #142b24 | palm/900 · #142b24 |

#### Icon
| Figma | CSS | Light | Dark |
| :--- | :--- | :--- | :--- |
| `icon/primary` | `--ula-icon-primary` | palm/800 · #1b3223 | sand/100 · #f9f6ef |
| `icon/secondary` | `--ula-icon-secondary` | stone/600 · #665d52 | sand/400 · #e3d2bb |
| `icon/muted` | `--ula-icon-muted` | stone/500 · #857a6c | stone/300 · #c1b6a6 |
| `icon/subtle` | `--ula-icon-subtle` | stone/300 · #c1b6a6 | stone/500 · #857a6c |
| `icon/disabled` | `--ula-icon-disabled` | stone/400 · #a49889 | stone/500 · #857a6c |
| `icon/accent` | `--ula-icon-accent` | palm/600 · #2a5439 | palm/300 · #8baa94 |
| `icon/highlight` | `--ula-icon-highlight` | gold/500 · #b98a37 | gold/400 · #d3a553 |
| `icon/danger` | `--ula-icon-danger` | terracotta/400 · #b46c34 | terracotta/300 · #d99a6c |
| `icon/on-dark` | `--ula-icon-on-dark` | sand/100 · #f9f6ef | sand/100 · #f9f6ef |
| `icon/on-accent` | `--ula-icon-on-accent` | sand/50 · #fbf8f2 | sand/50 · #fbf8f2 |
| `icon/on-dark-subtle` | `--ula-icon-on-dark-subtle` | stone/300 · #c1b6a6 | stone/300 · #c1b6a6 |

#### Tone — paired background / foreground / dot sets for badges and avatars
| Figma | CSS | Light | Dark |
| :--- | :--- | :--- | :--- |
| `tone/palm/bg` | `--ula-tone-palm-bg` | palm/100 · #dbe6dd | alpha/palm-400-14 · #5e8a6c24 |
| `tone/palm/fg` | `--ula-tone-palm-fg` | palm/700 · #1e412f | palm/300 · #8baa94 |
| `tone/palm/dot` | `--ula-tone-palm-dot` | palm/500 · #3c6b4c | palm/400 · #5e8a6c |
| `tone/gold/bg` | `--ula-tone-gold-bg` | gold/200 · #f0dfbc | alpha/gold-400-18 · #d3a5532e |
| `tone/gold/fg` | `--ula-tone-gold-fg` | gold/600 · #946c27 | gold/300 · #e6c88b |
| `tone/gold/dot` | `--ula-tone-gold-dot` | gold/400 · #d3a553 | gold/400 · #d3a553 |
| `tone/terracotta/bg` | `--ula-tone-terracotta-bg` | terracotta/200 · #ecc9ae | alpha/terracotta-400-20 · #b46c3433 |
| `tone/terracotta/fg` | `--ula-tone-terracotta-fg` | terracotta/600 · #7d451d | terracotta/300 · #d99a6c |
| `tone/terracotta/dot` | `--ula-tone-terracotta-dot` | terracotta/400 · #b46c34 | terracotta/300 · #d99a6c |
| `tone/stone/bg` | `--ula-tone-stone-bg` | stone/100 · #e8e4dc | alpha/ivory-10 · #ede6d91a |
| `tone/stone/fg` | `--ula-tone-stone-fg` | stone/700 · #4a443c | stone/200 · #d9d3c8 |
| `tone/stone/dot` | `--ula-tone-stone-dot` | stone/400 · #a49889 | stone/400 · #a49889 |
| `tone/sand/bg` | `--ula-tone-sand-bg` | sand/400 · #e3d2bb | alpha/ivory-16 · #ede6d929 |
| `tone/sand/fg` | `--ula-tone-sand-fg` | terracotta/600 · #7d451d | sand/400 · #e3d2bb |

#### Control — form and dark-chrome controls
| Figma | CSS | Light | Dark |
| :--- | :--- | :--- | :--- |
| `control/dark-fill` | `--ula-control-dark-fill` | alpha/ivory-8 · #ede6d914 | alpha/ivory-8 · #ede6d914 |
| `control/dark-fill-strong` | `--ula-control-dark-fill-strong` | alpha/ivory-10 · #ede6d91a | alpha/ivory-10 · #ede6d91a |
| `control/dark-fill-hover` | `--ula-control-dark-fill-hover` | alpha/ivory-16 · #ede6d929 | alpha/ivory-16 · #ede6d929 |
| `control/dark-border` | `--ula-control-dark-border` | alpha/ivory-16 · #ede6d929 | alpha/ivory-16 · #ede6d929 |
| `control/dark-border-subtle` | `--ula-control-dark-border-subtle` | alpha/ivory-14 · #ede6d924 | alpha/ivory-14 · #ede6d924 |
| `control/dark-fill-strong-hover` | `--ula-control-dark-fill-strong-hover` | alpha/ivory-18 · #ede6d92e | alpha/ivory-18 · #ede6d92e |
| `control/cta-on-dark` | `--ula-control-cta-on-dark` | sand/300 · #ede6d9 | sand/300 · #ede6d9 |
| `control/cta-on-dark-hover` | `--ula-control-cta-on-dark-hover` | sand/100 · #f9f6ef | sand/100 · #f9f6ef |
| `control/track-off` | `--ula-control-track-off` | stone/300 · #c1b6a6 | alpha/ivory-30 · #ede6d94d |

#### Brand, shadow, media, spinner
| Figma | CSS | Light | Dark |
| :--- | :--- | :--- | :--- |
| `brand/mark-green` | `--ula-brand-mark-green` | palm/700 · #1e412f | palm/700 · #1e412f |
| `brand/mark-ivory` | `--ula-brand-mark-ivory` | sand/100 · #f9f6ef | sand/100 · #f9f6ef |
| `shadow/xs` | `--ula-shadow-xs` | alpha/shadow-6 · #1b32230f | alpha/shadow-6 · #1b32230f |
| `shadow/sm` | `--ula-shadow-sm` | alpha/shadow-10 · #1b32231a | alpha/black-50 · #00000080 |
| `shadow/md` | `--ula-shadow-md` | alpha/shadow-14 · #1b322324 | alpha/black-55 · #0000008c |
| `shadow/lg` | `--ula-shadow-lg` | alpha/shadow-18 · #1b32232e | alpha/black-60 · #00000099 |
| `shadow/xl` | `--ula-shadow-xl` | alpha/shadow-22 · #1b322338 | alpha/shadow-22 · #1b322338 |
| `shadow/on-map` | `--ula-shadow-on-map` | alpha/black-55 · #0000008c | alpha/black-55 · #0000008c |
| `media/stripe-a` | `--ula-media-stripe-a` | sand/400 · #e3d2bb | sand/400 · #e3d2bb |
| `media/stripe-b` | `--ula-media-stripe-b` | sand/500 · #d6c0a2 | sand/500 · #d6c0a2 |
| `media/label-bg` | `--ula-media-label-bg` | alpha/page-72 · #fbf8f2b8 | alpha/page-72 · #fbf8f2b8 |
| `media/label-fg` | `--ula-media-label-fg` | stone/700 · #4a443c | stone/700 · #4a443c |
| `spinner/track` | `--ula-spinner-track` | alpha/page-35 · #fbf8f259 | alpha/page-35 · #fbf8f259 |
| `spinner/head` | `--ula-spinner-head` | sand/50 · #fbf8f2 | palm/950 · #0e1c17 |

### 3.3 Theming

`prefers-color-scheme` is the default. `[data-theme="dark"]` and `.dark` force dark; `[data-theme="light"]` forces light and wins over the media query.

```html
<html dir="rtl" data-theme="dark">
```

---

## 4. Spacing and layout

A 4px-based scale with a 2px half-step at the bottom.

| Figma | CSS | Value |
| :--- | :--- | :--- |
| `space/0` | `--ula-space-0` | 0 |
| `space/1` | `--ula-space-1` | 2 |
| `space/2` | `--ula-space-2` | 4 |
| `space/3` | `--ula-space-3` | 8 |
| `space/4` | `--ula-space-4` | 12 |
| `space/5` | `--ula-space-5` | 16 |
| `space/6` | `--ula-space-6` | 20 |
| `space/7` | `--ula-space-7` | 24 |
| `space/8` | `--ula-space-8` | 32 |
| `space/9` | `--ula-space-9` | 40 |
| `space/10` | `--ula-space-10` | 48 |
| `space/11` | `--ula-space-11` | 64 |
| `space/12` | `--ula-space-12` | 80 |
| `space/13` | `--ula-space-13` | 96 |
| `space/14` | `--ula-space-14` | 128 |
| `layout/gutter` | `--ula-layout-gutter` | null |
| `layout/card-pad` | `--ula-layout-card-pad` | null |
| `layout/panel-pad` | `--ula-layout-panel-pad` | null |
| `layout/section-y` | `--ula-layout-section-y` | null |
| `layout/container-max` | `--ula-layout-container-max` | 1280 |
| `layout/container-narrow` | `--ula-layout-container-narrow` | 880 |
| `size/touch-target` | `--ula-size-touch-target` | 44 |

**Grid:** 12 columns, 24px gutter, 24px margin, stretch. Container `1280px`; narrow (reading) container `880px`. Minimum touch target `44px` — every interactive control meets it.

---

## 5. Shape

| Figma | CSS | Value |
| :--- | :--- | :--- |
| `radius/xs` | `--ula-radius-xs` | 6px |
| `radius/sm` | `--ula-radius-sm` | 10px |
| `radius/md` | `--ula-radius-md` | 14px |
| `radius/lg` | `--ula-radius-lg` | 20px |
| `radius/xl` | `--ula-radius-xl` | 28px |
| `radius/pill` | `--ula-radius-pill` | 999px |
| `border-width/hairline` | `--ula-border-width-hairline` | 1px |
| `border-width/thick` | `--ula-border-width-thick` | 1.5px |
| `blur/panel` | `--ula-blur-panel` | 14px |

Radius by scale: inputs and small controls `sm` (10) to `md` (14); cards `lg` (20); dialogs and screen shells `xl` (28); tags, avatars and capsules `pill`. Borders are `1px` hairline; `1.5px` only for a checkbox box or an error field.

---

## 6. Elevation

| Style | CSS box-shadow |
| :--- | :--- |
| Shadow/XS | `0px 1px 2px 0px #1b32230f` |
| Shadow/SM | `0px 2px 8px -2px #1b32231a` |
| Shadow/MD | `0px 8px 24px -8px #1b322324` |
| Shadow/LG | `0px 20px 48px -12px #1b32232e` |
| Shadow/XL | `0px 32px 80px -16px #1b322338` |
| Shadow/On Map | `0px 8px 24px -8px #0000008c` |
| Focus/Ring | `0px 0px 0px 4px #d3a553, 0px 0px 0px 2px #fbf8f2` |
| Focus/Ring On Dark | `0px 0px 0px 4px #d3a553, 0px 0px 0px 2px #142b24` |
| Blur/Panel | `0px 0px 14px 0px #000000` |

`Focus/Ring` renders as a 2px surface-colored gap plus a 4px gold ring:

```css
box-shadow: 0 0 0 2px var(--ula-focus-gap), 0 0 0 4px var(--ula-focus-ring-color);
```

Glass panels over the map use `surface/capsule` with a 14px backdrop blur and a `border/on-dark` hairline.

---

## 7. Motion

| Figma | CSS | Value |
| :--- | :--- | :--- |
| `duration/instant` | `--ula-duration-instant` | 90ms |
| `duration/fast` | `--ula-duration-fast` | 160ms |
| `duration/base` | `--ula-duration-base` | 240ms |
| `duration/slow` | `--ula-duration-slow` | 360ms |
| `duration/ambient` | `--ula-duration-ambient` | 700ms |
| `ease/out` | `--ula-ease-out` | cubic-bezier(0.2, 0.8, 0.2, 1) |
| `ease/in-out` | `--ula-ease-in-out` | cubic-bezier(0.4, 0, 0.2, 1) |
| `ease/entrance` | `--ula-ease-entrance` | cubic-bezier(0.16, 1, 0.3, 1) |

`fast` for hover and color changes, `base` for anything that moves or resizes, `slow` for panels and dialogs entering, `ambient` for presence pulses and donut sweeps. `ease/entrance` only for things arriving on screen.

---

## 8. Components

45 sets. Props are documented in the Blade files; geometry below is the Figma spec as implemented.

| Component | Geometry | Notes |
| :--- | :--- | :--- |
| **Button** | h 36 / 44 / 52 · pad-x 16 / 22 / 26 · radius 10 / 14 / 18 · gap 8 | Primary `accent/*`; Secondary card + `border/strong`; Ghost `text/link` on `surface/accent-soft`; Danger `status/danger`; Nav-CTA `control/cta-on-dark`. States: default, hover, press, focus, disabled, loading |
| **Icon Button** | 40×40 · radius 12 · icon 20 | Outline, Accent, dark-toolbar, danger, ghost. Optional 8px notification dot with 1.5px surface ring |
| **Tag** | h 29 · pad-x 12 · gap 6 · pill · dot 7 | Tonal `tone/*` pairs, no border. Online / In meeting / Busy / Away |
| **Chip** | h 36 · pad-x 16 · pill | `surface/card` + `border/default`, hover `border/hover` |
| **Text Field** | h 46 · radius 14 · pad-x 16 | `surface/page` fill, `border/default`; focus `border/focus` + gold ring; error `border/danger` 1.5px |
| **Select** | h 46 · radius 14 | Light and dark variants; chevron `icon/secondary` |
| **Checkbox** | box 20 · radius 6 · border 1.5 · gap 11 | Checked `accent/default` |
| **Switch** | track 44×26 · pad 3 · knob 20 | Off `control/track-off`, on `accent/default` |
| **Avatar** | 32 / 36 / 44 · pill | Initials or photo; stack overlaps −12 with a 2px surface ring, overflow count in mono |
| **Stat Card** | radius 20 · pad 20/18 · gap 14 | Metric in Cairo Light 34/1.1; 32px tonal icon chip; optional donut and delta row |
| **Meeting Row** | radius 14 · pad 14/12 | `surface/page` fill; 36px status square radius 10 in `tone/*` |
| **Quick Action Tile** | radius 16 · pad 16 · icon 26 | `surface/page`; hover `border/strong` |
| **Donut** | stroke 10 / 16 · track `control/track-off` | Value in the center, legend items beneath |
| **Empty State** | radius 20 · dashed `border/default` | 64px circular icon, Arabic + English line, one primary action |
| **Dialog** | radius 28 · `Shadow/XL` | Overlay `surface/overlay` + 14px blur; 40px circular icon chip in the header |
| **App Bar** | h 68 | Brand lockup, nav pills, search, icon buttons, language select, avatar + name/role |
| **Marketing Nav / Footer** | — | Dark chrome, `control/cta-on-dark` call to action |
| **Map Toolbar** | h 67 · `surface/map-chrome` | Dark; `control/dark-*` fills, floor select, presence count |
| **Map Room Label** | radius 12 · `surface/capsule` + blur | Arabic name, English beneath in `text/on-dark-subtle` |
| **Status Capsule** | h 33 · pill · `surface/capsule-strong` | Dot + occupancy text over the map |
| **Meeting Control Bar** | button 72×65 · radius 16 | Leave is `status/danger`; the rest are `control/dark-fill` |
| **Page Blocks** | — | Hero, Quote, Welcome, Feature |

### Component sets by page

### Utilities
- _Media/Stripes (single)
- _Spinner (single)
- _Presence Dot — 3 variants

### Icons
- Icon/menu (single)
- Icon/search (single)
- Icon/notifications (single)
- Icon/language (single)
- Icon/expand_more (single)
- Icon/chevron_left (single)
- Icon/arrow_back (single)
- Icon/grid_view (single)
- Icon/layers (single)
- Icon/apartment (single)
- Icon/meeting_room (single)
- Icon/chair (single)
- Icon/group (single)
- Icon/person_add (single)
- Icon/place (single)
- Icon/space_dashboard (single)
- Icon/workspaces (single)
- Icon/videocam (single)
- Icon/videocam_off (single)
- Icon/mic (single)
- Icon/mic_off (single)
- Icon/screen_share (single)
- Icon/chat_bubble (single)
- Icon/call_end (single)
- Icon/front_hand (single)
- Icon/calendar_month (single)
- Icon/event_available (single)
- Icon/schedule (single)
- Icon/check_circle (single)
- Icon/error (single)
- Icon/mail (single)
- Icon/bolt (single)
- Icon/folder (single)
- Icon/upload_file (single)
- Icon/attach_file (single)
- Icon/download (single)
- Icon/settings (single)
- Icon/more_horiz (single)
- Icon/palette (single)
- Icon/door_front (single)
- Icon/add (single)
- Icon/check (single)

### Brand
- Brand Mark — 4 variants
- Brand Lockup — 4 variants

### Button
- Button Primary — 15 variants
- Button Secondary — 12 variants
- Button Ghost — 12 variants
- Button Danger — 12 variants

### Icon Button
- Icon Button — 9 variants
- Icon Button Dark — 5 variants

### Chip & Tag
- Chip — 3 variants
- Tag — 5 variants

### Inputs
- Text Field — 8 variants
- Search Field — 4 variants
- Select — 3 variants
- Select Dark — 2 variants
- Language Select — 2 variants
- Language Select Dark (single)

### Selection Controls
- Checkbox — 2 variants
- Switch — 2 variants

### Avatar
- Avatar — 6 variants
- Avatar Overflow — 2 variants
- Avatar Stack (single)
- Avatar Stack Map (single)

### Cards & Data
- Stat Card — 4 variants
- Quick Action Tile — 4 variants
- Meeting Row — 8 variants
- Donut — 2 variants
- Legend Item — 4 variants

### Empty State & Dialog
- Empty State (single)
- Dialog (single)

### Navigation & Bars
- Meeting Control — 4 variants
- Meeting Control Bar — 2 variants
- Map Room Label — 2 variants
- Status Capsule — 2 variants
- Toolbar Button — 2 variants
- Nav CTA — 2 variants
- Marketing Nav (single)
- App Bar (single)
- Footer (single)
- Map Toolbar (single)

### Page Blocks
- Media Placeholder (single)
- Date Pill (single)
- Feature Item (single)
- Quote Banner (single)
- Welcome Card (single)
- Hero Block (single)

### Icons

42 icons in the Figma set, drawn on a 24px grid at 2px stroke. The implementation uses **Material Symbols Rounded** (weight 400, optical size 24) as the delivery mechanism; names map 1:1 where they exist. Icons that must flip in RTL carry `.ula-mirror-rtl`.

### Brand

The wordmark is set in IBM Plex Sans Medium, uppercase, `0.14em` tracking (`Label/Brand`). The mark and lockup exist in 8 variants (green on light, ivory on dark, mark only, lockup with Arabic descriptor). They are **not** exported as assets yet — see Open items.

---

## 9. Accessibility

- Body text is `text/body` or darker on `surface/page` / `surface/card`. `text/muted` is for metadata only, never body copy.
- Focus is always visible: the gold `Focus/Ring` on every interactive element, with `Focus/Ring on Dark` over map chrome.
- Minimum touch target 44px (`size/touch-target`).
- Status is never color alone — every status tag pairs its dot with a text label.
- Direction is real: `[dir]` switches the font stack and all spacing uses logical properties (`margin-inline-start`, `inset-inline-end`).

---

## 10. Implementation

```
virtual-workplace/resources/
├── css/
│   ├── ulaspace-tokens.css      ← all tokens, both themes, type + surface utilities
│   ├── app.css                  ← Tailwind entry + @theme bridge
│   ├── ulaspace-dashboard.css
│   └── ulaspace-office.css
├── design-system/
│   └── tokens.json              ← machine-readable, generated from Figma
└── views/components/            ← 16 Blade components
```

Naming: Figma `surface/page` → CSS `--ula-surface-page`. Mechanical, in both directions.

**Rules.** Components reference semantic tokens, never primitives and never raw hex. New color needs a semantic token with both modes before it can be used. Tailwind utilities resolve through the `@theme` bridge in `app.css`, so `bg-card` and `var(--ula-surface-card)` are the same color.

---

## 11. Open items

1. **Cairo webfonts** are not in the repo. Add `public/fonts/cairo/cairo-arabic.woff2` and `cairo-latin.woff2` (weights 300–700). Until then Cairo falls back to IBM Plex Sans Arabic.
2. **Brand mark and icon assets** are not exported from Figma — the icons are Material Symbols stand-ins and the wordmark is live text.
3. **Photography** is placeholder stripes (`media/stripe-*`) wherever the design calls for a real image.
4. **Legacy bridge block** at the bottom of `ulaspace-tokens.css` can be deleted once `grep -rn -- "--nx-\|--brand-\|--bg-base" resources/` is empty.
5. The three older repo docs describe the pre-migration values and should be retired in favour of this file.
