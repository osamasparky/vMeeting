# UlaSpace Design System — Architecture & Developer Guide

This guide is the permanent reference manual for building, styling, and extending any screen or component within the UlaSpace virtual workplace platform.

---

## 1. Design Philosophy
- **Desert Modern & Heritage Luxury**: Organic sands, deep palm greens, warm ivory papers, and refined gold accents.
- **Arabic-First Bilingual Foundation**: Built for native right-to-left (RTL) Arabic with balanced English companion typography.
- **Tactile Micro-Interactions**: Gentle +2px lift on hover, organic shadows tinted with palm greens, and crisp gold focus rings.

---

## 2. Core Token Cheatsheet

### Colors
```css
/* Palm (Dark Chrome & Primary Interactive) */
--nx-palm-950: #0b1410; /* Dark canvas */
--nx-palm-900: #142b24; /* Sidebar & Nav */
--nx-palm-700: #1b3223; /* Dark card surface */
--nx-palm-500: #1e412f; /* Primary interactive */
--nx-palm-300: #3c6b4c; /* Live status */
--nx-palm-100: #e8f2ec; /* Soft success */

/* Sand & Ivory (Light Surfaces) */
--nx-sand-50:  #ffffff; /* Card surface */
--nx-sand-100: #f9f4ee; /* Page background */
--nx-sand-200: #f4ede1; /* Light surface hover */
--nx-sand-300: #ede6d9; /* Nav CTA fill */
--nx-sand-400: #e3d2bb; /* Subtle on-dark text */
--nx-sand-500: #c1b6a6; /* Border default */

/* Gold & Accent */
--nx-gold-400: #d3a553; /* Focus ring & Accent */
--nx-gold-600: #b46c34; /* Scheduled status */

/* Terracotta (Attention / Danger) */
--nx-terracotta-500: #9a5827;
```

### Typography
- `font-family: var(--nx-font-ar)`: `'IBM Plex Sans Arabic', sans-serif`
- `font-family: var(--nx-font-en)`: `'IBM Plex Sans', sans-serif`
- `font-family: var(--nx-font-mono)`: `'IBM Plex Mono', monospace`

### Radii
- Small (Badges, Checkboxes): `6px` (`--nx-radius-xs`)
- Medium (Inputs, Rows): `14px` (`--nx-radius-md`)
- Large (Cards, Modals): `16px` (`--nx-radius-lg`)
- Arch (Hero container top): `48px` (`--nx-radius-arch`)
- Pill (Buttons, Capsules, Avatars): `9999px` (`--nx-radius-pill`)

---

## 3. Blade Component Usage Guide

### Buttons (`<x-btn>`)
```html
<x-btn variant="primary" size="md" icon="add">
    <span>إنشاء اجتماع جديد</span>
</x-btn>

<x-btn variant="nav-cta" size="md">
    <span>احجز عرضاً</span>
</x-btn>
```

### KPI Cards with Donut (`<x-kpi-card>`)
```html
<x-kpi-card 
    title="نسبة الحضور اليومي" 
    subtitle="Daily Attendance"
    value="84%" 
    metric="42 / 50 حاضر الآن"
    :donut="84"
    trend="+12%"
/>
```

### Meeting Rows (`<x-meeting-row>`)
```html
<x-meeting-row 
    time="10:00 ص"
    title="اجتماع مجلس الإدارة الأسبوعي"
    room="قاعة النخيل • Floor 2"
    status="live"
/>
```

### Quick Action Tiles (`<x-quick-tile>`)
```html
<x-quick-tile 
    icon="video_camera_front" 
    title="غرفة اجتماعات سريعة" 
    subtitle="Quick Meet"
    href="/office"
/>
```
