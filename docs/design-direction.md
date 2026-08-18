# Design Direction — Digital Workplace OS

## 1. Product Identity & Philosophy

The Virtual Workplace platform is defined as a **Digital Workplace OS** — the online home for distributed enterprises where spatial presence, spontaneous watercooler conversations, private boardrooms, and structured team administration coexist in harmony.

### Key Tenets:
1. **Calm by Default, Alive in Context**: Deep focus without intrusive noise; UI awakens smoothly when teammates approach or events occur.
2. **Human & Spatial**: Reflects natural human intuitions of distance, eye contact, and room boundaries without game-like gimmickry.
3. **Enterprise Elegance**: Modern precision typography, balanced data density, and crisp micro-interactions.

---

## 2. Two Distinct Worlds

```
┌────────────────────────────────────────────────────────────────────────┐
│                      DIGITAL WORKPLACE OS                              │
├──────────────────────────────────┬─────────────────────────────────────┤
│   WORLD A: VIRTUAL WORKPLACE     │      WORLD B: MANAGEMENT SUITE      │
├──────────────────────────────────┼─────────────────────────────────────┤
│ • 2D Spatial Office Canvas       │ • Enterprise Overview & Plans       │
│ • Floating, Minimal HUD          │ • Department & RBAC Access Matrix   │
│ • Proximity Acoustic Halos       │ • Floor & Map Infrastructure        │
│ • Floating Bottom Media Dock     │ • Real-time Occupancy Analytics     │
│ • Spontaneous Corridor Huddles   │ • Settings & LiveKit Credentials    │
│ • Organic Motion & Smooth Easing │ • Professional Floor Studio Editor  │
└──────────────────────────────────┴─────────────────────────────────────┘
```

Both worlds share the exact same foundational tokens (color values, typography scales, border radius rules, spacing systems), yet their density and layout paradigms reflect their unique purpose.

---

## 3. Visual Language & Aesthetics

- **Base Surfaces (`--bg-base`)**: Deep obsidian dark / crisp porcelain light.
- **Card Surfaces (`--bg-surface`)**: Subtle elevation with 1px luminous borders (`rgba(255,255,255,0.08)` in dark, `rgba(0,0,0,0.06)` in light).
- **Floating HUD (`--bg-glass`)**: Glassmorphism with `backdrop-filter: blur(20px)`.
- **Primary Brand**: Digital Cobalt `hsl(228, 89%, 60%)`.
- **Accent**: Electric Violet `hsl(265, 85%, 64%)` & Cyan `hsl(190, 95%, 48%)`.
- **Semantic Statuses**:
  - Available: Emerald `hsl(152, 69%, 45%)`
  - Busy / In Meeting: Crimson `hsl(356, 75%, 58%)`
  - Focus Mode: Amethyst `hsl(270, 70%, 60%)`
  - Away: Amber `hsl(38, 92%, 50%)`
  - Offline: Slate Neutral `hsl(220, 10%, 45%)`
