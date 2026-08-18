# Information Architecture — Digital Workplace OS

## 1. Global Navigation Architecture

```
                    ┌──────────────────────────────────────────────┐
                    │               APP HEADER BAR                 │
                    │  [Org Logo] [World Switcher]  [Search / Cmd] │
                    │   [Lang EN/AR] [Theme] [Status] [Profile]    │
                    └──────────────────────┬───────────────────────┘
                                           │
             ┌─────────────────────────────┴─────────────────────────────┐
             ▼                                                           ▼
   ┌───────────────────────────┐                               ┌───────────────────────────┐
   │ WORLD A: VIRTUAL WORKSPACE│                               │ WORLD B: MANAGEMENT SUITE │
   ├───────────────────────────┤                               ├───────────────────────────┤
   │ 🏠 Workplace Dashboard    │                               │ 🏢 Organization & Plan    │
   │ 🗺️ Virtual Office (Canvas)│                               │ 👥 Employees & Invites    │
   │ 👥 People Directory       │                               │ 🏢 Floors & Maps          │
   │ 💬 Spatial & Team Chat    │                               │ 🚪 Rooms & Zones Matrix   │
   │ 📅 Meetings & Stages      │                               │ 🛡️ Roles & Permissions    │
   │                           │                               │ 📊 Real-time Analytics    │
   │ [Quick Dock at Bottom]    │                               │ ⚙️ System Settings        │
   └───────────────────────────┘                               └───────────────────────────┘
```

---

## 2. Core Views & User Navigation Flow

1. **Workplace Dashboard (`/dashboard`)**: The living nerve center showing who is in the office, floor capacity, active conversation clusters, and teleport shortcuts.
2. **Virtual Office (`/office/{map}`)**: Fullscreen spatial canvas with floating minimal HUD, proximity audio triggers, audio/video dock, and room knock notifications.
3. **Map Studio Editor (`/editor/{map}`)**: Visual floor builder with component catalog, drag & drop canvas, properties inspector, and 4-way rotation controls.
4. **SuperAdmin Portal (`/superadmin/*`)**: Tenant management, SaaS subscription plans, global furniture catalog, and matrix controls.
