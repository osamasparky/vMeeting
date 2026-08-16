# Virtual Workplace SaaS Platform — Project Implementation Blueprint

**Based on:** BRD v1.0, SRS v1.0, Technical Architecture v1.0 (all dated 16 Aug 2026)
**Role assumed:** Principal Architect / CTO / Full-Stack, Realtime, DevOps, Security, QA lead
**Status:** Pre-development — for approval before any code is written

---

## A. Document Analysis

### A.1 BRD — what it establishes
- **Product framing:** a *Virtual Workplace Operating System*, not a meeting-clone. Core value is persistent presence + spontaneous spatial communication, reducing scheduled-meeting overhead.
- **Personas:** Platform Owner, Company Admin, Manager, Employee, Guest/Client, Event Organizer — six distinct permission surfaces.
- **MVP scope (§6.1):** auth/onboarding, tenant mgmt, employee profiles/presence, office/floor/map, avatar+movement, spatial audio, video, private/meeting rooms, chat (DM+room), screen share, admin+roles, basic analytics, S3 storage foundation. **Notably absent from MVP scope: billing/monetization enforcement, recording, events, guests are not explicitly in 6.1** even though BR-008 (Guest Access) and BR-010 (Billing) are listed as core *business* requirements in §7, and guest journeys/roles appear elsewhere. This is the first conflict — resolved in §B.
- **Roadmap:** 4 phases — MVP → Collaboration workspace (calendar, whiteboard, guests, events, recording, broadcast, mobile) → AI/analytics/SSO → ERP ecosystem.
- **Monetization:** four-tier plan model (Free/Starter/Business/Enterprise) plus usage add-ons (recording, storage, AI minutes, events).

### A.2 SRS — what it establishes
- Confirms the layered system (client / API / realtime / media / world engine / storage / admin / billing).
- Leaves the frontend and media technology **nominally open** ("React/Next.js **or** Vue3/Nuxt", "Phaser **or** PixiJS", "LiveKit / mediasoup / **equivalent** SFU") — i.e. it states a *class* of technology, not a locked decision.
- Gives concrete functional requirement IDs (FR-*), a WebSocket event table, an MVP acceptance-criteria table (AC-01…AC-10), and a **recommended MVP delivery order** (10 steps) that is realtime-and-media-first, admin-last.
- Data model includes a `user_positions` (current location) entity in the Presence domain — in tension with the "don't persist every movement tick" rule. Resolved in §B.
- FR-AUTH-004 (2FA) is phrased as "should support optional" with no phase assigned — ambiguous whether it's MVP-blocking.

### A.3 Technical Architecture — what it establishes
- This is the **decision document**: it takes the SRS's open technology *classes* and locks them to specific choices (React+Next.js+TS, Phaser, Laravel 8.3/8.4, Node.js+TS realtime, LiveKit, PostgreSQL, Redis, S3, CloudFront). Per the governing master prompt, **this document is the baseline** wherever it conflicts with the more permissive SRS language.
- Defines strict responsibility boundaries per component (§5) and an explicit "what NOT to build initially" list (§24) — custom SFU, custom codec, full 3D engine, microservice sprawl, per-tenant DB, native apps, in-core ERP.
- Gives a 4-stage scaling path and names the top 7 architecture risks with mitigations.
- Ends with an explicit Phase-0 proof-of-concept checklist (map → 2 avatars → WS sync → proximity → LiveKit room → mic → camera/screen-share → private-room isolation → load measurement) — this is treated as a **gate**, not an optional nicety.

### A.4 Cross-document read
All three documents are internally consistent on the big-picture architecture (modular monolith + dedicated realtime + managed SFU, strict tenant isolation, Redis for transient state, Postgres for truth). The friction is concentrated in a small number of **scope boundary questions** (is billing/guests/2FA in MVP?) and **one data-modeling question** (how is position data persisted, if at all?). None of these require a different architecture — they're scoping/assumption calls, handled in §B.

---

## B. Architecture Validation

**Overall: I agree with the Technical Architecture baseline.** It correctly avoids over-engineering (no k8s/microservices at MVP, no custom SFU) while still drawing hard boundaries that prevent the common failure mode of this kind of product — media traffic or raw position ticks leaking into the business database/API layer. I'm adopting it as-is with the resolutions below.

### B.1 Conflicts between documents (resolved)

| # | Conflict | Resolution | Rationale |
|---|---|---|---|
| 1 | SRS leaves frontend framework/rendering engine/SFU as "or" choices; TA locks them | **TA wins.** React+Next.js+TS, Phaser, LiveKit are final. | Master prompt explicitly designates TA as baseline; SRS predates the lock-in decision. |
| 2 | SRS models a persisted `user_positions` ("current location") entity; TA/master-prompt rule says don't write every movement tick to Postgres | **No `user_positions` table.** Live position lives only in Redis (`presence:{org}:{map}` hash), pushed via WebSocket. An optional `position_samples` table (sparse, e.g. every 30–60s or on room-transition) is added *only* for analytics, not for real-time state. | Reconciles "current location" need (served from Redis, not DB) with the explicit anti-pattern warning in TA §12/§13 and the master prompt's Node.js rules. |
| 3 | BRD §6.1 MVP scope list omits billing/guests; BRD §7 (BR-008, BR-010) and the data model in SRS/master-prompt include them | **Split the requirement.** Guest *access* (link-based, host-approved, expiring) ships in MVP — it's small, security-relevant, and explicitly detailed as a BRD user journey. Billing/subscriptions/plans ship as **schema + enforcement of plan limits (seat counts, room counts)** in MVP, but **payment processing integration (Stripe/etc.) is deferred to post-MVP** — orgs are provisioned on a plan manually/via admin until a payment provider is wired in. | Keeps the tenant model plan-aware from day one (avoids a schema migration later) without pulling payments — a genuinely separate, non-blocking workstream — into the MVP critical path. |
| 4 | FR-AUTH-004 (2FA) has no phase assignment | **Assumption:** optional TOTP 2FA ships in MVP as an opt-in account setting (cheap schema addition — one `two_factor_secret`/`recovery_codes` pair on `users` — and it's a security-positive default). Not required to *use* it; required to *support* it. | Low cost, no architectural impact, closes a real security gap for admin/owner accounts specifically. Flagging for your approval rather than silently deciding, since it does touch auth flow. |
| 5 | BRD fixed 5-role model (Super Admin/Company Admin/Manager/Employee/Guest) vs. SRS/master-prompt implied generic `roles`+`permissions` RBAC tables | **Both.** Ship the 5 BRD roles as seeded rows in a generic RBAC schema (roles ↔ permissions ↔ organization_members), so the UI/API only exposes the 5 fixed roles in MVP, but custom-role creation (post-MVP) requires no schema change, only a UI/API surface. | Matches BRD's product simplicity requirement while not painting the schema into a corner. |

### B.2 Missing requirements (gaps I'm flagging, not silently filling)

- **Map versioning/recovery.** SRS §10 requires map changes to be "recoverable/versioned"; no document specifies *how*. **Assumption:** maps have a `status` (draft/published) and a `version` integer; publishing snapshots the previous published version's `map_objects` into a lightweight history table. Full branching/diff history is out of scope for MVP.
- **Guest lobby/approval mechanics.** BRD's guest journey specifies "guest lobby → host approval," but no FR-GUEST-* requirement or data entity covers it. **Assumption:** add a `status` (pending/approved/denied/expired) to guest sessions and a WebSocket event (`guest.requested`) notifying the host — small addition, not a new subsystem.
- **Rate-limit / plan-limit values.** Neither document gives concrete numbers (max seats per plan, max concurrent room size, max maps per org). These are **business decisions, not engineering ones** — I need these from you before finalizing `plans`/`plan_limits` schema defaults. Not blocking for schema design (values are just rows), but blocking for actual plan enforcement logic.
- **Departments vs. Teams relationship.** Both appear in scope, with no defined hierarchy. **Assumption:** a Team is an optional sub-grouping *within* a Department (nullable `department_id` on `teams`); Department is the primary org unit used for room/permission defaults.
- **Data residency / AWS region.** TA §27 explicitly calls this out as undecided. Needed before infrastructure provisioning (Phase 0), not before schema design.

### B.3 Risks I'd add beyond TA's risk register
See consolidated **Risk Register (§J)** — I've merged TA's 7 risks with 4 additional ones surfaced by cross-referencing the BRD's guest/billing scope against the MVP engineering plan.

### B.4 Recommended changes to the baseline
None to the *technology* baseline. One process recommendation: **treat the Phase-0 PoC (TA §28) as a hard gate**, not a formality — specifically the "measure CPU, memory, WebSocket traffic, and media behavior" step. Spatial audio + avatar sync is the highest-novelty, highest-risk part of this system; everything else (CRUD, admin, chat) is comparatively conventional Laravel/Next.js work. I want real numbers from 10–20 simulated concurrent avatars before committing the realtime architecture to production shape.

---

## C. Final Stack

| Technology | Version | Purpose | Reason |
|---|---|---|---|
| React | 18.x | UI library | Ecosystem maturity, concurrent rendering for realtime UI |
| Next.js | 14.x (App Router) | App shell, SSR/routing, dashboards | Auth-aware routing, SaaS page patterns, per TA §6 |
| TypeScript | 5.x | Frontend + realtime language | Shared types between Next.js client and Node realtime service |
| Phaser | 3.8x | Virtual office rendering | 2D map/avatar/collision engine, avoids building a game engine (TA §7) |
| Zustand | 4.x | Frontend state | Lightweight, keeps Phaser state separate from React UI state per TA §6 |
| Laravel | PHP 8.3/8.4 | Business/API layer | Fast CRUD/auth/tenancy development, team productivity (TA §3) |
| Laravel Sanctum | latest | API/session auth | First-party, works for SPA + token auth |
| Node.js | 20 LTS | Realtime service runtime | Efficient event-loop for high-frequency presence/movement |
| TypeScript (Node) | 5.x | Realtime service language | Shared contracts with frontend |
| ws / uWebSockets.js | latest | WebSocket transport | High-throughput WS server |
| Redis | 7.x | Transient state, pub/sub, queues, rate limiting | Required per TA §14; also Laravel queue backend |
| LiveKit | latest stable (self-hosted or LiveKit Cloud) | Audio/video/screen-share SFU | Avoids building a custom SFU (explicit prohibition in master prompt & TA) |
| PostgreSQL | 16.x | System of record | Relational integrity, JSONB where needed, future PostGIS optionality |
| AWS S3 | — | File/asset/recording storage | Object storage, signed URLs |
| CloudFront | — | CDN | Static/media delivery |
| Docker / Docker Compose | latest | Dev environment containers | Per TA §22, container-friendly from day one |
| GitHub Actions | — | CI/CD baseline | Aligns with GitHub-hosted repo assumption |
| OpenTelemetry + centralized logs | — | Observability | Per SRS §3 |
| Python + FastAPI | future | AI gateway | Explicitly future-phase, isolated from core (TA §3, master prompt §30) |
| Flutter | future | Mobile | Explicitly future-phase |

**Not adopted / explicitly rejected for MVP** (per TA §24 and master prompt): custom WebRTC SFU, custom codec/media transport, 3D engine, Kubernetes/microservices estate, per-tenant database, native desktop/mobile apps, in-core ERP modules.

---

## D. Repository Structure

```
virtual-workplace/
├── frontend/                     # Next.js + React + TS + Phaser
│   ├── src/
│   │   ├── app/                  # Next.js App Router pages (dashboard, office, admin)
│   │   ├── components/           # Shared UI components
│   │   ├── office/               # Phaser engine integration (isolated from React state)
│   │   │   ├── scenes/
│   │   │   ├── entities/         # Avatar, Room, Zone renderers
│   │   │   └── engine-bridge.ts  # Typed bridge between Phaser and React/Zustand
│   │   ├── stores/               # Zustand stores
│   │   ├── lib/
│   │   │   ├── api-client/       # Typed REST client (generated or hand-typed)
│   │   │   ├── ws-client/        # Typed WebSocket client
│   │   │   └── livekit-client/   # LiveKit room connection helpers
│   │   └── types/                # Shared TS types (mirrors /contracts)
│   └── tests/
│
├── backend/                      # Laravel (PHP 8.3/8.4)
│   ├── app/
│   │   ├── Domains/              # Domain-organized, NOT giant generic folders
│   │   │   ├── Identity/         # Auth, users, sessions
│   │   │   ├── Tenancy/          # Organizations, memberships, plans
│   │   │   ├── People/           # Departments, teams, profiles, avatars
│   │   │   ├── Workspace/        # Floors, maps, rooms, zones, objects
│   │   │   ├── Meetings/
│   │   │   ├── Chat/
│   │   │   ├── Guests/
│   │   │   ├── Billing/
│   │   │   ├── Analytics/
│   │   │   └── Administration/   # Roles, permissions, audit logs
│   │   │       └── each domain contains: Controllers/ Requests/ Actions/ Policies/ Models/
│   │   ├── Services/             # Cross-domain services (e.g. LiveKitTokenService)
│   │   └── Support/              # Shared helpers
│   ├── database/
│   │   ├── migrations/
│   │   ├── factories/
│   │   └── seeders/
│   ├── routes/
│   │   └── api.php               # grouped by domain, versioned (/api/v1/...)
│   └── tests/
│       ├── Unit/
│       └── Feature/               # incl. TenantIsolationTest, PermissionBypassTest
│
├── realtime/                     # Node.js + TypeScript realtime service
│   ├── src/
│   │   ├── ws/                   # Connection handling, auth handshake
│   │   ├── presence/             # Presence state management
│   │   ├── movement/             # Position sync, boundary validation
│   │   ├── proximity/            # Spatial/zone proximity calculation
│   │   ├── rooms/                # Room membership, interest management
│   │   ├── redis/                # Redis client, pub/sub channel definitions
│   │   ├── events/                # Typed event definitions (shared w/ frontend via /contracts)
│   │   └── livekit-bridge/       # Requests LiveKit room/participant state changes
│   └── tests/
│
├── contracts/                    # Shared, versioned type contracts (source of truth)
│   ├── api/                      # OpenAPI spec or TS types generated from Laravel
│   ├── websocket/                # Event payload types, shared by frontend + realtime
│   └── README.md
│
├── infrastructure/
│   ├── docker/
│   │   ├── frontend.Dockerfile
│   │   ├── backend.Dockerfile
│   │   ├── realtime.Dockerfile
│   │   └── docker-compose.yml    # Local dev: postgres, redis, backend, realtime, frontend
│   ├── terraform/ (or CDK)       # AWS provisioning — Phase 6, not Phase 0
│   └── ci/                       # GitHub Actions workflows
│
├── docs/
│   ├── architecture.md
│   ├── database.md
│   ├── api.md
│   ├── websocket.md
│   ├── realtime.md
│   ├── webrtc.md
│   ├── security.md
│   ├── deployment.md
│   ├── development.md
│   └── testing.md
│
├── tests/                        # Cross-service E2E (Playwright), load tests (k6)
│   ├── e2e/
│   └── load/
│
├── CLAUDE.md                     # Project-specific development rules
├── .env.example
└── docker-compose.yml            # Root convenience compose (dev)
```

---

## E. Database Design (Conceptual ERD)

Organized by domain per TA §13, merged with SRS §11 and reconciled per §B.1/§B.2 above. `org` = `organization_id` (UUID, FK, indexed) present on every tenant-owned table unless noted.

### Tenant & Identity
- **organizations**: id (UUID, PK), name, slug (unique), logo_url, timezone, status, plan_id (FK→plans), created_at
- **organization_settings**: organization_id (PK/FK), branding JSONB, policies JSONB
- **plans**: id, name, seat_limit, room_limit, storage_limit_gb, features JSONB, price
- **subscriptions**: id, organization_id (FK), plan_id (FK), status, current_period_end
- **users**: id (UUID, PK), email (unique), password_hash, name, two_factor_secret (nullable), email_verified_at, created_at
- **organization_members**: id, organization_id (FK), user_id (FK), role_id (FK), status (invited/active/suspended), joined_at — *unique(org, user)*
- **roles**: id, organization_id (nullable FK — null = system role), name, is_system
- **permissions**: id, key (e.g. `rooms.manage`), description
- **role_permissions**: role_id (FK), permission_id (FK) — composite PK

### People
- **departments**: id, organization_id (FK), name, parent_department_id (nullable, self-FK)
- **teams**: id, organization_id (FK), department_id (nullable FK), name
- **user_profiles**: user_id (PK/FK), organization_id (FK), job_title, department_id (nullable FK), team_id (nullable FK), bio
- **avatars**: id, user_id (FK), organization_id (FK), sprite_config JSONB, updated_at

### Virtual World / Workspace
- **floors**: id, organization_id (FK), name, order
- **maps**: id, floor_id (FK), organization_id (FK), status (draft/published), version (int), layout_data JSONB (tile/coordinate grid ref), published_at
- **map_versions**: id, map_id (FK), version, layout_snapshot JSONB, created_at — *satisfies "recoverable" requirement, §B.2*
- **rooms**: id, map_id (FK), organization_id (FK), name, type (meeting/private/manager/support/client/reception), access_mode (public/private/role/invite), capacity
- **zones**: id, map_id (FK), organization_id (FK), type (movement/audio/interaction), shape_data JSONB (rect/polygon), audible_radius (nullable, for audio zones)
- **map_objects**: id, map_id (FK), organization_id (FK), type (desk/chair/wall/door/furniture), position JSONB, collision boolean, interaction_config JSONB

*Indexes: `(organization_id)` on all of the above; `(map_id)` on rooms/zones/map_objects; `(floor_id)` on maps.*

### Presence (deliberately thin — §B.1 resolution #2)
- **presence_sessions**: id, user_id (FK), organization_id (FK), started_at, ended_at (nullable), status (available/busy/away/focus/offline) — durable log, one row per session, **not** per movement tick
- **position_samples** *(optional, analytics-only)*: id, user_id (FK), organization_id (FK), map_id (FK), x, y, sampled_at — sparse writes (30–60s interval or on zone-transition), never on every tick
- Live position/status/zone-membership itself → **Redis only**: `presence:{org_id}` hash of `user_id → {x,y,status,map_id,updated_at}`, TTL-refreshed on heartbeat, no Postgres write.

### Communication
- **channels**: id, organization_id (FK), type (dm/room/broadcast), room_id (nullable FK)
- **channel_members**: channel_id (FK), user_id (FK)
- **messages**: id, channel_id (FK), organization_id (FK), sender_id (FK), body, created_at — *index (channel_id, created_at)* for pagination
- **message_reads**: message_id (FK), user_id (FK), read_at

### Meetings
- **meetings**: id, organization_id (FK), room_id (nullable FK), title, started_at, ended_at, created_by (FK→users)
- **meeting_participants**: meeting_id (FK), user_id (FK), joined_at, left_at

### Files
- **files**: id, organization_id (FK), owner_id (FK→users), s3_key, filename, size, mime_type, created_at
- **file_permissions**: file_id (FK), organization_id (FK), scope (user/room/channel), scope_id

### Guests (§B.2 addition)
- **guest_invitations**: id, organization_id (FK), room_id (FK), invited_by (FK→users), token (unique, random), status (pending/approved/denied/expired), expires_at

### Events (schema present, feature Post-MVP)
- **events**: id, organization_id (FK), title, starts_at, ends_at
- **event_rooms**: event_id (FK), room_id (FK)
- **event_participants**: event_id (FK), user_id (FK)

### Security & Analytics
- **audit_logs**: id, organization_id (FK), actor_id (FK→users, nullable for system), action, target_type, target_id, metadata JSONB, created_at — *index (organization_id, created_at)*
- **access_logs**: id, organization_id (FK), user_id (FK), resource, ip, created_at
- **usage_events** *(basic analytics, MVP)*: id, organization_id (FK), event_type, metadata JSONB, created_at

**Cross-cutting rules:** UUID PKs on externally-referenced entities; every tenant-owned table indexed on `organization_id`; foreign keys enforced at DB level (no soft-reference-only tenancy); JSONB reserved for genuinely variable-shape data (layout_data, sprite_config, metadata) — never used to avoid modeling a real relationship.

---

## F. API Specification (by domain)

All routes versioned under `/api/v1`. Auth via Sanctum session/token; every non-public route resolves `organization_id` server-side from the authenticated membership — **never** from a client-supplied parameter (per TA §17).

| Domain | Endpoints |
|---|---|
| **Auth** | `POST /auth/register` · `POST /auth/login` · `POST /auth/logout` · `POST /auth/forgot-password` · `POST /auth/reset-password` · `POST /auth/verify-email` · `POST /auth/2fa/enable` · `POST /auth/2fa/verify` |
| **Organizations** | `POST /organizations` · `GET /organizations/{org}` · `PATCH /organizations/{org}` · `GET /organizations/{org}/settings` · `PATCH /organizations/{org}/settings` |
| **Members/Invites** | `GET /organizations/{org}/members` · `POST /organizations/{org}/members/invite` · `PATCH /organizations/{org}/members/{member}` · `DELETE /organizations/{org}/members/{member}` |
| **Departments/Teams** | `GET/POST /organizations/{org}/departments` · `GET/POST /organizations/{org}/teams` |
| **Roles/Permissions** | `GET /organizations/{org}/roles` · `PATCH /organizations/{org}/members/{member}/role` |
| **Profile/Avatar** | `GET/PATCH /users/me` · `PATCH /users/me/avatar` · `PATCH /users/me/status` |
| **Floors/Maps** | `GET/POST /organizations/{org}/floors` · `GET /floors/{floor}/map` · `PUT /maps/{map}` · `POST /maps/{map}/publish` · `GET /maps/{map}/versions` |
| **Rooms** | `GET/POST /maps/{map}/rooms` · `GET/PATCH/DELETE /rooms/{room}` |
| **Zones/Objects** | `GET/POST /maps/{map}/zones` · `GET/POST /maps/{map}/objects` |
| **Meetings** | `POST /meetings` · `GET /meetings/{meeting}` · `POST /meetings/{meeting}/end` |
| **Chat** | `GET/POST /channels/{channel}/messages` · `GET /users/{user}/dm-channel` |
| **Files** | `POST /files/presign` · `GET /files/{file}` · `DELETE /files/{file}` |
| **Guests** | `POST /rooms/{room}/guest-invitations` · `GET /guest-invitations/{token}` · `POST /guest-invitations/{token}/respond` |
| **LiveKit** | `POST /rooms/{room}/livekit-token` (issues short-lived join token *after* server-side room-access authorization) |
| **Billing** | `GET /organizations/{org}/subscription` · `GET /plans` |
| **Analytics** | `GET /organizations/{org}/analytics/summary` |
| **Audit** | `GET /organizations/{org}/audit-logs` |

Convention: `Controllers` stay thin → `FormRequest` validates → `Action`/`Service` executes business logic → `Policy` authorizes → `Model`/repository persists. No business logic in controllers (per master prompt §15/§18).

---

## G. WebSocket Specification

Transport: single authenticated WS connection per client to the Node realtime service, scoped to one organization + one active map. Auth handshake: client presents a short-lived token issued by Laravel (`POST /realtime/token`) after normal session auth; Node verifies signature + org/map membership before accepting the connection — Node never re-derives authorization from client-claimed IDs.

| Event | Direction | Payload (sketch) | Auth/Authorization note |
|---|---|---|---|
| `presence.updated` | Server→Client | `{userId, status}` | Broadcast to org-scoped subscribers only |
| `user.joined` | Server→Client | `{userId, mapId, position, status}` | Sent to clients on same map |
| `user.left` | Server→Client | `{userId, mapId}` | " |
| `position.updated` | Client→Server→Client | `{x, y}` in / `{userId, x, y}` out | Server clamps/validates against map boundaries before rebroadcast; throttled (e.g. max ~10Hz client emit, server-debounced) |
| `room.entered` | Client→Server→Client | `{roomId}` in / `{userId, roomId}` out | Server checks room `access_mode` before confirming; denial returns `room.access_denied` |
| `room.left` | Client→Server→Client | `{roomId}` | |
| `audio.zone.changed` | Server→Client | `{userId, zoneId, participants[]}` | Drives LiveKit room/group membership changes (via livekit-bridge) |
| `chat.message.created` | Client→Server→Client | `{channelId, body}` in / persisted message out | Server persists via internal Laravel call or shared DB access pattern (TBD in Phase-0, see risk register) before rebroadcast |
| `meeting.started` / `meeting.ended` | Server→Client | `{meetingId, roomId}` | Triggered by Laravel event → Redis pub/sub → Node |
| `guest.requested` | Server→Client | `{guestInvitationId, roomId}` | Sent to room host only (§B.2 addition) |

All events documented and versioned in `/contracts/websocket`; no ad-hoc event names introduced outside this contract (per master prompt §16).

---

## H. WebRTC / LiveKit Architecture

- **Connection flow:** client requests `POST /rooms/{room}/livekit-token` from Laravel → Laravel checks tenant membership + room `access_mode` (public/private/role/invite) via Policy → Laravel calls LiveKit server SDK to mint a scoped, short-lived JWT (room name + participant identity + permissions) → client uses that token to connect **directly** to LiveKit, bypassing Laravel and the Node realtime service entirely for media.
- **Room mapping:** each *private room* (meeting room, CEO office, etc.) maps 1:1 to a LiveKit room. Each *open-area audio zone* maps to a dynamically-created/torn-down LiveKit room scoped to current zone occupants — membership changes are driven by the Node realtime service's proximity calculation (`audio.zone.changed` → livekit-bridge updates participant grants), not by client-side LiveKit calls.
- **Spatial control:** proximity/zone math happens in the Node service (position data it already holds in Redis); LiveKit itself has no concept of "distance" — it only manages whoever the app layer tells it is in a given room. Client-side volume attenuation (optional, per SRS §7) can be layered on top using relative avatar distance even within a shared LiveKit room, without app-layer room-switching for minor distance changes.
- **Private rooms:** isolated LiveKit rooms; only participants who passed the Laravel authorization check for that room ever receive a token for it — audio/video isolation is enforced by "you were never issued a token," not by client-side filtering.
- **Reconnection:** LiveKit client SDK handles ICE/transport reconnection; the app shows "Connection lost. Reconnecting…" (per master prompt §28) rather than a generic error, and re-requests a token if the original expires mid-session.
- **Recording:** explicitly out of MVP (BRD §6.2, TA §10) — architecture leaves room for LiveKit's Egress service later without redesign.

---

## I. MVP Development Plan

Following SRS §19's realtime-first delivery order, mapped onto the master prompt's 7 phases:

### Phase 0 — Architecture Validation & PoC
- **Goal:** de-risk the realtime+media core before building the full product.
- **Features:** repo scaffold, Docker Compose dev env, CI baseline, one map, two test avatars, WS position sync, proximity detection, LiveKit room join, mic test, camera/screen-share test, private-room audio isolation test.
- **Modules:** `realtime/`, `infrastructure/docker`, minimal `frontend/office`.
- **Dependencies:** none (first phase).
- **Tests:** manual PoC validation + load measurement (CPU/mem/WS traffic under ~10-20 simulated avatars).
- **Acceptance criteria:** TA §28 checklist fully passes; go/no-go decision recorded before Phase 1 starts.

### Phase 1 — Backend Foundation
- **Goal:** tenant-safe backend core.
- **Features:** auth (register/login/logout/reset/verify/2FA), organizations, members/invites, roles/permissions (5 seeded roles), departments/teams.
- **Modules:** `backend/app/Domains/{Identity,Tenancy,People,Administration}`.
- **Dependencies:** Phase 0 infra.
- **Tests:** unit (auth, RBAC), feature (org CRUD, invite flow), **tenant-isolation test suite starts here and runs in every subsequent phase.**
- **Acceptance criteria:** AC-01 (admin creates org, invites employees), AC-10 partial (server-side tenant/role enforcement demonstrated).

### Phase 2 — Virtual Office
- **Goal:** working, navigable office.
- **Features:** map data model + editor API (not full admin UI yet), Phaser rendering, avatar movement (keyboard/WASD/arrows), collision, WS position sync, presence.
- **Modules:** `backend/app/Domains/Workspace`, `frontend/src/office`, `realtime/src/{presence,movement}`.
- **Dependencies:** Phase 1 (auth/org context).
- **Tests:** WS position/presence tests, boundary-collision tests, browser E2E for office entry+movement.
- **Acceptance criteria:** AC-02, AC-03, AC-04.

### Phase 3 — Real-Time Communication
- **Goal:** spatial audio + private rooms + video.
- **Features:** LiveKit integration, proximity→audio-zone binding, private room isolation, camera, mic, screen share.
- **Modules:** `realtime/src/{proximity,rooms,livekit-bridge}`, `backend` LiveKit token service, `frontend/src/lib/livekit-client`.
- **Dependencies:** Phase 2 (positions/zones exist).
- **Tests:** audio/video/screen-share tests, room-isolation tests, cross-browser WebRTC tests.
- **Acceptance criteria:** AC-05, AC-06, AC-07.

### Phase 4 — Collaboration
- **Goal:** persistent communication.
- **Features:** DM + room chat, message persistence/pagination, guest invitations (link + lobby + host approval), file upload (S3 presign).
- **Modules:** `backend/app/Domains/{Chat,Guests}`, `frontend` chat UI.
- **Dependencies:** Phase 1 (channels need org/user context), Phase 3 (guest rooms need media).
- **Tests:** chat feature tests, guest-token tests, file permission tests.
- **Acceptance criteria:** AC-08.

### Phase 5 — Admin
- **Goal:** operable by non-engineers.
- **Features:** admin dashboard (online employees, active rooms, meetings, recent activity), full map editor UI, org/user/role management UI, basic analytics.
- **Modules:** `frontend/src/app/(admin)`, `backend/app/Domains/Analytics`.
- **Dependencies:** Phases 1–4 (surfaces existing data).
- **Tests:** admin permission tests, map-editor E2E.
- **Acceptance criteria:** AC-09.

### Phase 6 — Production Hardening
- **Goal:** ready for real tenants.
- **Features:** audit logs, rate limiting, monitoring/observability, backups, load testing, cross-tenant security testing, cost-per-user baseline.
- **Modules:** cross-cutting.
- **Dependencies:** all prior phases.
- **Tests:** load tests (WS fan-out, media), penetration-style tenant-isolation tests, permission-bypass tests.
- **Acceptance criteria:** AC-10 (full), all AC-01…09 re-verified under hardened config.

---

## J. Risk Register

| Risk | Source | Impact | Mitigation |
|---|---|---|---|
| WebRTC/media complexity | TA | High | LiveKit (no custom SFU); media isolated from business services |
| Realtime scaling (WS fan-out) | TA | High | Redis pub/sub, zone-level interest management, horizontal scaling from Phase 6 |
| Large maps/asset load time | TA | Medium | Lazy loading, CDN, optimized bundles |
| **Tenant data leakage** | TA | **Critical** | Server-side tenant policies on every layer (API, WS, LiveKit token issuance), automated cross-tenant tests every phase |
| Over-engineering early | TA | High | Modular monolith; defer k8s/microservices/per-tenant DB |
| Browser/WebRTC compatibility | TA | Medium | Early cross-browser testing in Phase 3 |
| Infra cost scaling with concurrent users | TA | Medium | Cost-per-active-user tracked from Phase 6 |
| **Chat persistence path (Node↔Laravel)** *(new)* | This analysis | Medium | `chat.message.created` requires the realtime Node service to get a message durably into Postgres before rebroadcast. Needs an explicit decision in Phase 0/1: Node calls a Laravel internal API, or both services share write access via a message queue. **Architectural decision pending — flagged per master prompt §32, not yet resolved by any source document.** |
| **Plan/billing scope creep** *(new)* | This analysis | Medium | Keep payment provider integration explicitly out of MVP; ship schema + manual plan assignment only, to avoid a Stripe/webhook integration expanding Phase 6 scope |
| **Guest lobby UX undefined** *(new)* | This analysis | Low-Medium | Minimal implementation (approve/deny via existing WS notification) rather than a full lobby "waiting room" UI in MVP |
| **2FA scope ambiguity** *(new)* | This analysis | Low | Resolved as optional/opt-in in §B.1 #4 — pending your confirmation |
| **Map versioning depth** *(new)* | This analysis | Low | Draft/publish + single-level snapshot only, not full history/diffing, for MVP |

---

## K. Estimated Development Complexity (by module)

| Module | Complexity |
|---|---|
| Auth, org, RBAC (Phase 1) | Medium |
| Map data model + editor API | Medium |
| Phaser rendering/movement/collision | Medium-High |
| WebSocket presence + position sync | High |
| Proximity/spatial-audio calculation | **Critical** |
| LiveKit integration (audio/video/screen-share) | High |
| Private room isolation | High |
| Chat (DM + room) | Medium |
| Guest access/lobby | Medium |
| Admin dashboard | Medium |
| Map editor UI | Medium-High |
| Billing schema (no payment integration) | Low-Medium |
| Analytics (basic) | Low-Medium |
| Multi-tenant isolation testing (ongoing) | High (effort is continuous, not a one-time module) |
| Observability/monitoring | Medium |
| CI/CD, Docker, deployment | Medium |

I'm not attaching calendar-time estimates (per master prompt §34.K instruction against unrealistic time promises) — complexity should inform your team-sizing/sprint-planning conversation, not stand in for one.

---

## L. Phase 0 Proof of Concept — Exact Definition

Before any full-application development starts, this must be demonstrated end-to-end, matching TA §28:

1. One virtual office map (hand-authored, not via editor UI yet).
2. Two test avatars/browser sessions.
3. Position sync between them over Node.js/WebSocket.
4. Proximity detection between the two avatars (distance-based, using the actual zone/radius model, not a stub).
5. Automatic creation/joining of the correct LiveKit room/group based on that proximity.
6. Live microphone test confirming audio only flows when avatars are within radius.
7. Camera + screen-share added to the same PoC.
8. Move both avatars into a private room and verify audio is isolated from the open-area participants (and vice versa).
9. Measure: CPU, memory, WebSocket message rate, and LiveKit connection behavior under this minimal load, then repeat with a scripted 10–20 simulated avatar load.
10. Written go/no-go decision based on those numbers before Phase 1 begins.

**This response is intentionally the blueprint only** — no application code has been generated. Per your process rules (§21/§34), I'm stopping here for your review and approval, specifically on:

- The 5 resolved conflicts in §B.1 (particularly #3 billing/guest scope and #4 2FA)
- The new architectural decision needed on the chat-persistence write path (§J)
- Concrete plan-limit numbers and AWS region/data-residency choice (§B.2)

Once you confirm or amend these, I'll proceed to Phase 0 scaffolding.
