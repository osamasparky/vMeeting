---
name: ui-ux-design
description: >-
  World-class UI/UX design and frontend aesthetics engineering skill. Use whenever designing,
  building, refactoring, or polishing user interfaces, dashboards, design systems, color palettes,
  tactile 3D components, micro-animations, typography, and bilingual RTL/LTR experiences.
---

# UI/UX Design & Spatial Aesthetics Engineering Masterclass

This skill defines the definitive principles, patterns, and code blueprints for building state-of-the-art, WOW-inducing web applications and dashboards that combine visual luxury, tactile responsiveness, and flawless user experience.

---

## 1. Core Visual Aesthetics & Design Philosophy

### The "Anti-Generic" Rule
- **Never use plain/raw colors** (e.g., `#FF0000`, `#0000FF`, standard grayscale `#808080`).
- **Use Curated Organic / Modern Color Systems**:
  - Primary / Brand: Rich Forest (`#1C4D30`, `#245C3A`), Deep Slate Indigo (`#1E293B`, `#4F46E5`), or Obsidian Emerald.
  - Secondary / Accents: Warm Gold (`#D6A23A`), Sage Leaf (`#4F9B5F`), Muted Coral (`#D96B5F`).
  - Warm Spatial Neutrals (Light Mode): Warm Ivory Canvas (`#F5F3E8`), Off-white Porcelain (`#FFFDF6`), Cream Muted (`#F7F5EC`).
  - Deep Spatial Neutrals (Dark Mode): Deep Obsidian Emerald (`#0C1711`, `#13241B`), Cyber Charcoal (`#0F172A`, `#1E293B`).

### Spatial 3D Lighting & Tactile Depth
Apply multi-layered diffuse ambient lighting and physical tactile feedback:
```css
:root {
  /* Ambient Diffuse Shadow */
  --shadow-soft-3d: 0 4px 12px rgba(36, 92, 58, 0.07), 0 1px 3px rgba(36, 92, 58, 0.05);
  --shadow-card: 0 6px 18px rgba(36, 92, 58, 0.08), 0 2px 6px rgba(36, 92, 58, 0.04);
  --shadow-elevated: 0 16px 36px rgba(36, 92, 58, 0.14), 0 4px 12px rgba(36, 92, 58, 0.08);
  --shadow-inset-3d: inset 0 2px 4px rgba(36, 92, 58, 0.06), inset 0 1px 2px rgba(36, 92, 58, 0.04);

  /* Tactile Physical Button Press */
  --btn-shadow-primary: 0 3px 0 #183F27, 0 6px 14px rgba(36, 92, 58, 0.25);
  --btn-shadow-hover: 0 4px 0 #183F27, 0 8px 18px rgba(36, 92, 58, 0.3);
  --btn-shadow-active: 0 1px 0 #183F27;
}

.tactile-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  border-radius: 12px;
  font-weight: 800;
  font-size: 13px;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.tactile-btn:hover {
  transform: translateY(-1.5px);
  box-shadow: var(--btn-shadow-hover);
}

.tactile-btn:active {
  transform: translateY(2px);
  box-shadow: var(--btn-shadow-active);
}
```

---

## 2. Typography & Bilingual RTL/LTR Architecture

### Dual-Font Pairing Hierarchy
- **Arabic**: `Cairo`, `Alexandria`, or `Tajawal` (Weights: 300, 400, 600, 700, 800, 900).
- **English / Numbers**: `Inter`, `Plus Jakarta Sans`, or `Outfit` (Weights: 400, 600, 700, 800, 900).
- **Code / Identifiers**: `JetBrains Mono` or `Fira Code`.

```css
body {
  font-family: 'Cairo', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  letter-spacing: -0.01em;
  text-rendering: optimizeLegibility;
  -webkit-font-smoothing: antialiased;
}
```

### Flawless Bidirectional (RTL/LTR) Design Rules
1. **Always use CSS Logical Properties**:
   - `margin-inline-start`, `margin-inline-end` instead of `margin-left`, `margin-right`.
   - `padding-inline-start`, `padding-inline-end` instead of `padding-left`, `padding-right`.
   - `inset-inline-start: 0`, `inset-inline-end: 0` instead of `left: 0`, `right: 0`.
   - `text-align: start`, `text-align: end` instead of `text-align: left`, `text-align: right`.
2. **Mirroring Directional Elements**:
   - Arrows, chevrons, and step indicators must flip automatically when `[dir="rtl"]`.
   - Brand logos, time clocks, and number badges retain global alignment.

---

## 3. High-Conversion Component Patterns

### A. WOW Dashboard KPI Metric Card
```html
<div class="kpi-card">
  <div class="kpi-header">
    <span class="kpi-title">Gross Revenue</span>
    <div class="kpi-icon-box">💰</div>
  </div>
  <div class="kpi-value">$128,450</div>
  <div class="kpi-footer">
    <span class="delta-pill delta-positive">↑ 14.2%</span>
    <span class="kpi-subtext">vs previous 30 days</span>
  </div>
</div>
```

### B. Dynamic Collapsing Mini Sidebar (Icon-Rail)
- **Expanded Width**: `260px` with full labels, badges, and user profile pill.
- **Collapsed Width**: `76px` with centered icons, floating animated tooltips on hover, and active glowing indicators.
- **Persistence**: Remembers user choice in `localStorage.setItem('sidebar_collapsed', true)`.

### C. Kanban Board Columns
- 5 lanes: `Backlog`, `Ready`, `In Progress`, `Review/QA`, `Done`.
- Drag-over highlight with dashed subtle glowing border.
- Quick status select pill on card for keyboard / 1-click quick transitions.
- Task priority badges: 🔥 Urgent (Crimson), ⚡ High (Amber Gold), 🟢 Normal (Leaf Green), ⚪ Low (Muted Slate).

### D. Harmonic Sound Feedback (Web Audio API)
Enhance key moments (meeting alarms, timer completions, successful saves) with local synthesized chord chimes without external audio assets:
```javascript
function playHarmonicChime() {
  const AudioCtx = window.AudioContext || window.webkitAudioContext;
  if (!AudioCtx) return;
  const ctx = new AudioCtx();
  if (ctx.state === 'suspended') ctx.resume();
  const notes = [523.25, 659.25, 783.99, 1046.50]; // C5-E5-G5-C6 Bell Chord
  notes.forEach((freq, idx) => {
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.type = 'sine';
    osc.frequency.setValueAtTime(freq, ctx.currentTime + idx * 0.08);
    gain.gain.setValueAtTime(0.0001, ctx.currentTime + idx * 0.08);
    gain.gain.exponentialRampToValueAtTime(0.2, ctx.currentTime + idx * 0.08 + 0.03);
    gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + idx * 0.08 + 1.1);
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.start(ctx.currentTime + idx * 0.08);
    osc.stop(ctx.currentTime + idx * 0.08 + 1.2);
  });
}
```

---

## 4. UX Psychology & Cognitive Ergonomics
1. **Progressive Disclosure**: Show high-level summary upfront; reveal deep configurations via sub-tabs and context inspectors.
2. **Fitts' Law**: Place primary CTAs in reachable hotspots with large click targets (minimum 42px height).
3. **Instant Feedback**: Every button click, status toggle, and input submission must immediately render an optimistic UI state or toast notification.
4. **Empty States with Character**: Replace empty lists with motivating illustrations, friendly descriptions, and direct "+ Create New" action buttons.
5. **Zero Layout Shifts (CLS)**: Pre-allocate heights and widths for charts, tables, and images.
