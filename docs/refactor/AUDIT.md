# Refactor Audit — vMeeting / UlaSpace

**Date:** 2026-10-14 · **Branch:** `ulaspace-rebuild` @ `336ed99` · **Scope:** the Laravel app in `virtual-workplace/`, its realtime service (`virtual-workplace/realtime/`), CI and the deployment path to the live server. The Flutter app in `mobile_app/` is out of scope (the owner has deferred mobile work).

This is a discovery-only assessment. No source code, configuration, schema or data was changed while producing it. An earlier architecture audit and ADRs are referenced from code comments ("Architecture Audit §7/§15, ADR-002" in `config/tenancy.php`, `app/Domains/Tenancy/Scopes/TenantScope.php`) but those documents were removed from the repository; this file replaces them as the reference.

---

## 1. Repository and stack inventory

| Area | What is there (evidence) |
| :--- | :--- |
| Backend | Laravel **12.66** on PHP ^8.2 (`composer.json`, `composer.lock`); Sanctum 4 for API tokens |
| Frontend | Blade views + inline JavaScript; Tailwind 4 and Vite 7 (`package.json`); LiveKit client for audio/video |
| Styles | Design tokens in `resources/css/ulaspace-tokens.css`; page CSS loaded **directly from `public/css/*`** (`resources/views/dashboard.blade.php:24`), with a hand-kept copy in `resources/css/*` |
| Realtime | Separate Node/TypeScript WebSocket service (`realtime/package.json`: `ws`, `dotenv`), token-authenticated (`realtime/src/server.ts:102-116`), run under pm2 as `nextspace-realtime` |
| Databases | Local and CI: **SQLite** (`.env`, `phpunit.xml`, `.github/workflows/ci.yml`). Live: **MySQL/MariaDB** with `explicit_defaults_for_timestamp=0` |
| Queue / schedule | `QUEUE_CONNECTION=sync` on live; scheduler (`routes/console.php`): recurring tasks and due reminders hourly, notification pruning daily |
| Mail | `MAIL_MAILER=log` on live |
| Dev tooling | PHPUnit 11, Pint, Larastan 3 (level 2, `phpstan.neon`), Playwright (used ad hoc from scratch scripts, not wired into a suite) |
| Root of repo | ~50 untracked one-off scripts (`check_*.py`, `deploy_*.py`, `test_*.js`), design docs (`DESIGN_SYSTEM.md`, `REBUILD.md`, `FIX_BRIEF.md`) |

**Size hot spots.**

| File | Lines |
| :--- | ---: |
| `resources/views/office.blade.php` | 7,995 |
| `resources/views/editor.blade.php` | 4,135 |
| `resources/views/projects/hub.blade.php` | 4,115 |
| `resources/views/dashboard/partials/scripts.blade.php` | 3,714 |
| `app/Http/Controllers/SuperAdminController.php` | 1,880 |
| `app/Http/Controllers/Web/OfficeController.php` | 1,185 |
| `app/Http/Controllers/Web/OrganizationSettingsController.php` | 1,079 |
| `app/Domains/People/Services/AttendanceService.php` | 892 |

## 2. Architecture map

```
Browser ──Blade pages (dashboard, office, editor, project hub, superadmin)
   │        └─ inline JS: office engine, WebRTC/LiveKit, chat, timers
   ├─ routes/web.php (164 routes, session auth)  → app/Http/Controllers/Web/*  → app/Domains/*/{Actions,Services,Models}
   ├─ routes/api.php (192 routes, Sanctum)       → app/Domains/*/Controllers/* → same domain layer
   └─ WebSocket ──► realtime service (Node)  ◄── Laravel pushes via shared secret
```

- **Domain layer** (`app/Domains`, 199 PHP files): Projects 84, Workspace 39, People 20, Tenancy 18, CMS 7, Identity 7, Meetings 6, Administration 6, Guests 4, Chat 3, Notifications 3, Collaboration 2.
- **Two controller styles.** The API side is domain-organised (`app/Domains/*/Controllers`, organisation bound from `/api/v1/organizations/{organization}` + `EnsureOrganizationMember`). The web side lives in `app/Http/Controllers/Web/*` and `SuperAdminController`, and resolves the organisation itself (see §4.3).
- **Middleware** (`app/Http/Middleware`): `EnsureOrganizationMember`, `EnsurePermission` (`permission:` alias), `SuperAdminMiddleware`, `SetLocaleMiddleware`, `SecurityHeadersMiddleware`.
- **Policies:** one (`app/Policies/TaskPolicy.php`); everything else checks roles/permissions inline.
- **Office templates and seeding** run on first dashboard load (`BuildOrganizationDashboardAction::ensureDefaultWorkspace`, `database/seeders/BlueprintOfficeSeeder.php`) — a seeder class used at runtime.

## 3. Critical flows and contracts

| Flow | Where | Contract worth preserving |
| :--- | :--- | :--- |
| Office presence / attendance | `AttendanceController@logRoomAttendance` → `AttendanceService`; client in `office.blade.php` (~7480, ~7830) | enter / leave / heartbeat (45 s) / idle_pause / idle_resume; stale after 10 min; times counted in the office's timezone |
| Task time | `StartTimerAction`, `StopTimerAction`, `LogManualTimeAction`, `TimeTrackingController` | typed times are office-local unless offset given; stored UTC |
| Timesheets | `SubmitTimesheetAction`, approve/reject in `TimesheetController` | **only the company owner (company_admin) approves** — owner decision |
| Leave / holidays / monthly report | `HrController`, `LeaveService`, `MonthlyAttendanceReport` | leave decided by owner only; days count office working days minus holidays |
| Office templates | `OfficeTemplateDesigner`, `office-templates:rebuild`, `BlueprintOfficeSeeder` | plan room limits; empty office heals on next dashboard load |
| Website content | `CmsSchema`, `Cms::section()`, `SuperAdminController` CMS actions | required sections fall back to defaults; admin-edited text is never overwritten by `CmsSchema::sync()` |
| Auth | web session + `/api/v1/auth/login` (Sanctum token, throttle 10/min); impersonation `AuthController@impersonateMember` | — |
| Plans | Free (5 seats, 1 office, 3 rooms) and Business (100/50/50) exist on live; Starter/Enterprise rows exist only in seeders |

UI conventions that must survive any refactor: Arabic-first, RTL/LTR mirroring with logical properties, light/dark themes via tokens (`CLAUDE.md`, `DESIGN_SYSTEM.md`), 44 px touch targets, gold focus ring, numbers isolated LTR in mono.

## 4. Observed risks (facts, with evidence)

Ordered by severity.

### 4.1 Secrets committed to git — **high**
- `deploy_direct_sftp.py` (tracked since `22a23f5`) contains the live server's **root SSH password** in plain text (`host`, `username = "root"`, `password = ...`). It is in history even if deleted now.
- `MeetingController.php:206-208` hard-codes a TURN server host and a default TURN credential.
- The untracked `deploy_live_server.py` holds the same root password; it is not committed but sits in the working tree next to tracked files.
- Repository visibility on GitHub (`osamasparky/vMeeting`) could not be checked from here (`gh` not installed). **If the repository is or was public, the root password must be treated as leaked.**

### 4.2 Customer uploads are tracked in git — **high**
`git ls-files public/uploads` → 97 files. User-uploaded content lives in version control and is deployed by `git checkout`. Untracking them naïvely would make the next `git checkout` on the live server **delete those files** — any fix must move them aside first.

### 4.3 Organisation resolution on the web side — **medium/high**
28 places resolve "the current organisation" as `OrganizationMember::where('user_id', …)->first()` (e.g. `AttendanceController::currentMembership`, `HrController::currentMembership`, `OfficeController`, `OrganizationSettingsController`, `ChatController`, `TaskPolicy`). For a user who belongs to two organisations the result is arbitrary, and every authorization check downstream inherits that. Today no user is in two organisations (local DB check), but `POST /api/v1/organizations` lets any user create a second one.

### 4.4 Tenant isolation is manual — **medium**
`TenantScope` exists but is **off by default** (`config/tenancy.php: enforce_global_scope = env('TENANCY_ENFORCE_GLOBAL_SCOPE', false)`). Isolation depends on every query adding `organization_id` by hand. Several new reads use `withoutGlobalScopes()` deliberately; there is no test that runs the suite with the scope on.

### 4.5 Tests and CI do not cover production — **medium/high**
- CI (`.github/workflows/ci.yml`) triggers only on `main`/`master`; all current work is on `ulaspace-rebuild`, so CI does not run on it.
- Tests run on SQLite only. Two production-only bugs reached live this week and were invisible to the suite: first-TIMESTAMP-column `ON UPDATE CURRENT_TIMESTAMP` on MySQL (zeroed every office session; fixed by `2026_10_14_000005_…`) and a query on a non-existent `map_objects.image_url` that SQLite treats as a string literal (empty office for every new company; fixed in `ea8a50d`).
- `pint --test` currently **fails** on 3 files (`SuperAdminController.php`, `routes/web.php`, `tests/Feature/Phase6ConsistencyTest.php`), so CI would fail even if it ran.
- Larastan is installed but not in CI; level 2 reports **445** issues: 338 `property.notFound` and 87 `method.notFound` (mostly untyped relations returning `Model`), 16 `env()` outside config, 1 `class.notFound`.
- No JavaScript tests; the ~20k lines of inline JS in Blade are untested except through ad-hoc Playwright scripts outside the repo. The realtime service has one test file.

### 4.6 Deployment is manual and partly stale — **medium**
- `deploy_live_server.py` still runs `git pull origin main`; the live checkout is on `ulaspace-rebuild`. Deploys this week were done by hand over SSH.
- Artisan runs as root on the server; `storage` and `bootstrap/cache` must be `chown`ed back each time, and the Plesk vhost root must stay `psaserv 750` (a past recursive chown caused an outage).
- `public/build` is not committed, so every deploy needs `npm ci && npm run build` on the server.

### 4.7 Monolithic views and controllers — **medium (maintainability)**
`office.blade.php` (8k lines) mixes markup, the office engine, attendance, WebRTC and modals; `SuperAdminController` (1.9k) covers companies, plans, subscriptions, CMS, furniture, templates, settings and translations. Changes to these files are hard to review and impossible to unit-test.

### 4.8 Duplicated CSS by hand — **low/medium**
`public/css/ulaspace-dashboard.css` (served) and `resources/css/ulaspace-dashboard.css` must be kept identical by hand. A commit (`f2a0e0f`) already fixed a stale copy once; this audit's own session hit it again.

### 4.9 Smaller items
- `users.manage` grants impersonation (`AuthController.php:199`) but is not a seeded permission — dead today, an escalation path if a custom role ever gets it (no check that the target outranks nobody).
- Runtime use of a seeder class (`BlueprintOfficeSeeder`) from application code.
- `deploy_*`/`check_*` scripts at the repo root are untracked one-offs; some contain credentials.

## 5. Test and CI inventory (what was actually run)

| Check | Run during this audit? | Result |
| :--- | :--- | :--- |
| `php artisan test` (62 test files) | Yes, earlier the same day on HEAD in an isolated worktree with a real `vendor` copy | 369 passed |
| `vendor/bin/pint --test` | Yes (read-only) | **fail**, 3 files |
| `vendor/bin/phpstan analyse` (level 2) | Yes (read-only) | 445 errors |
| `npm run build` | Yes | passes |
| Realtime `npm test` | **Not run** | — |
| CI on GitHub | **Not observed** (no access to Actions from here) | — |
| MySQL-backed test run | **Does not exist** | — |

## 6. Open questions

1. Is the GitHub repository public, or has it ever been? Has the root password in `deploy_direct_sftp.py` been rotated?
2. Should the git history be purged of the password (needs a force-push and every clone re-cloned), or is rotation enough?
3. Is `main` still meant to be the release branch, or should `ulaspace-rebuild` become it?
4. Can a user legitimately belong to more than one organisation? If yes, how should they choose the current one (session switch, subdomain, URL)?
5. Are Starter and Enterprise plans coming back? Their templates and seeder rows exist, the live plans table does not have them.
6. Is a staging environment available, or should a MySQL service in CI stand in for it?

## 7. Limitations

- Inspection was static plus read-only commands; no load, browser-wide or security testing was done for this document.
- Live-server facts (MySQL behaviour, plans, permissions) come from read-only queries and logs gathered earlier the same day.
- The inline JavaScript was surveyed by size and entry points, not read in full.
- `mobile_app/` was not inspected.
