# User Flows — Digital Workplace OS

## 1. Flow 1: Morning Arrival & Office Entry

```
[User logs into Dashboard]
        │
        ▼
[Living Dashboard View]
  • Sees active pulse: "14 colleagues online, 2 active huddles"
  • Clicks "Enter Virtual Office" or selects specific floor
        │
        ▼
[Virtual Office Spatial Canvas]
  • WebSocket connects with HMAC Token
  • Avatar spawns smoothly at personal desk / entrance point
  • Presence broadcasted to floor occupants
  • Media dock initializes in muted/standby state
```

---

## 2. Flow 2: Spatial Proximity Conversation

```
[User moves with WASD / Arrow Keys]
        │
        ▼
[Approaches Colleague (Distance <= 160px)]
        │
        ▼
[Contextual Proximity Bubble triggers in Floating HUD]
  • Soft acoustic radar halo pulses around avatars
  • Live audio stream connects seamlessly via LiveKit / WebRTC
  • Quick actions: [Unmute Mic] [Turn on Video] [Share Screen]
        │
        ▼
[Users finish conversation & walk away]
  • Distance > 160px: Audio volume fades smoothly (distance attenuation)
  • Bubble dissolves gracefully; Dock returns to minimal standby
```

---

## 3. Flow 3: Private Room Entry & Knock Approval

```
[User approaches locked Boardroom door]
        │
        ▼
[Room Boundary Collision Detected]
  • Door status: "LOCKED / IN SESSION"
  • Contextual prompt: "Boardroom is in a private session"
        │
        ▼
[User clicks "Knock on Door ✊"]
  • WebSocket emits `room.knock` to room host
  • Host gets elegant floating notification: "Sara is knocking to enter"
        │
        ▼
[Host Clicks "Allow In"]
  • Requester receives approval
  • Avatar teleports past doorway threshold
  • User joins private audio/video room boundary
```
