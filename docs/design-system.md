# Design System Specification — Digital Workplace OS

## 1. Design Tokens Architecture

### Color Palette (HSL Architecture)

```css
:root {
  /* Brand Tokens */
  --brand-500: hsl(228, 89%, 60%);
  --brand-600: hsl(228, 85%, 52%);
  --brand-400: hsl(228, 89%, 68%);
  --violet-500: hsl(265, 85%, 64%);
  --cyan-500:   hsl(190, 95%, 48%);

  /* Dark Theme (Default) */
  --bg-base:        hsl(224, 25%, 8%);
  --bg-surface:     hsl(224, 20%, 12%);
  --bg-elevated:    hsl(224, 18%, 16%);
  --bg-glass:       hsla(224, 20%, 12%, 0.82);
  --border-subtle:  hsla(220, 20%, 90%, 0.07);
  --border-default: hsla(220, 20%, 90%, 0.12);
  --border-brand:   hsla(228, 89%, 60%, 0.45);
  --text-primary:   hsl(220, 25%, 96%);
  --text-secondary: hsl(220, 15%, 72%);
  --text-muted:     hsl(220, 10%, 48%);

  /* Semantic Feedback */
  --status-available: hsl(152, 69%, 45%);
  --status-busy:      hsl(356, 75%, 58%);
  --status-away:      hsl(38, 92%, 50%);
  --status-focus:     hsl(270, 70%, 60%);
  --status-offline:   hsl(220, 10%, 45%);

  /* Spacing Scale (4px Base) */
  --space-1: 4px;  --space-2: 8px;   --space-3: 12px;
  --space-4: 16px; --space-5: 20px;  --space-6: 24px;
  --space-8: 32px; --space-10: 40px; --space-12: 48px;

  /* Typography */
  --font-sans: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;
  --font-arabic: 'IBM Plex Sans Arabic', 'Cairo', sans-serif;

  /* Border Radius */
  --radius-sm: 6px;  --radius-md: 10px;
  --radius-lg: 16px; --radius-xl: 24px;
  --radius-full: 9999px;

  /* Shadows */
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.25);
  --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.35);
  --shadow-lg: 0 12px 36px rgba(0, 0, 0, 0.45);
  --shadow-brand: 0 0 28px hsla(228, 89%, 60%, 0.32);
}

[data-theme="light"] {
  --bg-base:        hsl(220, 25%, 97%);
  --bg-surface:     hsl(0, 0%, 100%);
  --bg-elevated:    hsl(220, 20%, 94%);
  --bg-glass:       hsla(0, 0%, 100%, 0.88);
  --border-subtle:  hsla(224, 25%, 15%, 0.06);
  --border-default: hsla(224, 25%, 15%, 0.10);
  --border-brand:   hsla(228, 89%, 60%, 0.35);
  --text-primary:   hsl(224, 30%, 12%);
  --text-secondary: hsl(224, 15%, 38%);
  --text-muted:     hsl(224, 10%, 55%);
}
```
