# Refactor Roadmap — vMeeting / UlaSpace

**Date:** 2026-10-14 · Based on `docs/refactor/AUDIT.md` (same date). Phases are ordered by risk and by what later phases depend on. Each phase is one reviewable diff, verified and committed before the next starts. Pushing and deploying stay separate decisions for the owner.

## Goals
- Remove the security exposures found in the audit (secrets, uploads in git).
- Make the checks trustworthy: CI that runs on the working branch, against MySQL as well as SQLite, and passes.
- Make "which organisation am I acting in" one decision in one place.
- Reduce the cost of changing the largest files without changing what they do.

## Non-goals
- No rewrite of the office engine, no framework or frontend-stack change, no visual redesign.
- No change to business rules: owner-only approval of timesheets and leave, plan limits, attendance rules, CMS behaviour.
- No mobile app work.

## Invariants (every phase)
- Routes, route names, request/response shapes, permissions and tenant boundaries unchanged unless a phase says otherwise.
- RTL/LTR, light/dark, Arabic-first copy and design tokens unchanged.
- The full suite passes on the commit itself, proven in an isolated worktree with a **real** `vendor` copy (a junction silently tests the working tree).
- Only the phase's files are committed (`git diff --cached --name-only` checked; the owner's `CLAUDE.md` edit stays out).

---

## Phase 1 — Secrets out of tracked code
**Rationale:** audit §4.1. Smallest, highest-value change.
**Scope:** `deploy_direct_sftp.py` reads host/user/password from environment variables and refuses to run without them; `MeetingController` stops carrying a default TURN credential and host (config only, no literal fallback); `.env.example` documents the variables.
**Excludes:** rotating the password and purging history (owner decisions, §6 Q1–Q2); untracked root scripts.
**Touch points:** `deploy_direct_sftp.py`, `app/Domains/Meetings/Controllers/MeetingController.php`, `virtual-workplace/.env.example`, `config/services.php` (only if the TURN keys are missing there).
**Acceptance:** `git grep` finds no password literal in tracked files; TURN config still resolves from `config('services.turn.*')`; meeting tests pass.
**Verify:** `git grep -nE "password\s*=\s*['\"][^'\"]{6,}"`; `php artisan test --filter=Meeting`; full suite in worktree.
**Rollback:** revert the commit. **Live note:** if the live `.env` lacks `TURN_*`, set them before deploying (a missing TURN config degrades calls behind strict NAT).
**Status (2026-10-14): done.** Changed from the plan: `deploy_direct_sftp.py` was **deleted** rather than moved to environment variables — it was obsolete (local path `d:\Vwork`, SFTP file copy instead of git, `chmod -R 777` on storage). The root scripts that hold credentials (`deploy_*.py`, `reset_*.py`, `fix_permissions.py`, `reload_fpm.py`) are now in `.gitignore`. The TURN literals were dead code (`config()` returns the existing key's `null`, never the `env()` default); with nothing configured the endpoint now returns an empty `ice_servers` list instead of entries with blank URLs. Live has no `TURN_*` set. **Still open for the owner:** rotate the root password (it remains in git history) and decide on purging history.

## Phase 2 — CI that runs and passes
**Rationale:** §4.5. Every later phase needs it.
**Scope:** run CI on `ulaspace-rebuild` and on pull requests; fix the 3 files Pint flags (formatting only, its own commit); add a second test job against a MySQL service (`explicit_defaults_for_timestamp=0`, like live).
**Excludes:** Larastan in CI (Phase 6); new tests beyond what the MySQL job needs to boot.
**Touch points:** `.github/workflows/ci.yml` (and the duplicate under `virtual-workplace/.github` — decide which one is real and delete the other), `phpunit.xml` or a `phpunit.mysql.xml`, the 3 Pint files.
**Acceptance:** CI green on both jobs for the branch; a deliberately reintroduced `ON UPDATE` timestamp column fails the MySQL job (checked once, not committed).
**Verify:** `vendor/bin/pint --test`; a local MySQL run if available; CI run link.
**Rollback:** revert the workflow commit; formatting commit is independent.
**Status (2026-10-14): done locally; CI run pending a push.** Pint passes repo-wide (51edc1d, route:list identical). The workflow runs on `ulaspace-rebuild` and every pull request, with a `mariadb:10.6` job (live is MariaDB 10.6.23, `explicit_defaults_for_timestamp=0`). Locally the suite was run against MariaDB 10.4, the only engine available: 359/371 pass. The 12 failures are window-function queries Laravel generates for eager-load limits, rejected by 10.4 under ONLY_FULL_GROUP_BY; the same query runs on live 10.6, and live logs show no such error. Acceptance check done: with migration 2026_10_14_000005 removed, the MariaDB run fails the attendance heartbeat test; restored, it passes. The duplicate `virtual-workplace/.github/workflows/ci.yml` (ignored by GitHub) was removed.

## Phase 3 — Uploads out of git, safely
**Rationale:** §4.2.
**Scope:** add `public/uploads/` to `.gitignore` and `git rm --cached` the 97 files. **Deployment procedure in the same phase:** before the live checkout, copy `public/uploads` aside, check out, restore, verify a sample of upload URLs return 200.
**Excludes:** moving uploads to `storage/` or object storage (a later decision).
**Touch points:** `.gitignore`, the index; a deploy runbook note.
**Acceptance:** `git ls-files public/uploads` empty; files still on disk locally and live; uploaded images still served.
**Rollback:** restore from the copied folder; revert the commit.
**Status (2026-10-14): not started — waiting for the owner.** Untracking the files means the next `git checkout` on live deletes them unless the deploy copies them aside first; that is a destructive step to agree on before doing.

## Phase 4 — One resolver for the current organisation (web side)
**Rationale:** §4.3, and a prerequisite for Phase 5.
**Scope:** a single `CurrentMembership` service (request-scoped, resolves the user's membership once); replace the 28 `OrganizationMember::where('user_id', …)->first()` lookups with it. Behaviour for single-organisation users is identical.
**Excludes:** letting users switch organisation (needs the owner's answer to §6 Q4); API routes, which already bind the organisation from the URL.
**Touch points:** `AttendanceController`, `HrController`, `OfficeController`, `OrganizationSettingsController`, `DashboardController`, `GuestAccessController`, `ProjectHubController`, `AuthController`, `ChatController`, `TaskPolicy`; a new class under `app/Domains/Tenancy`.
**Acceptance:** `git grep "OrganizationMember::where('user_id'"` returns only the resolver; a new test proves a user in two organisations gets a deterministic membership; the suite passes.
**Rollback:** revert; no data change.
**Status (2026-10-14): done.** Implemented as a model scope rather than a service: `OrganizationMember::forUser($id)` = `where('user_id')` + oldest membership first. All 57 lookups switched mechanically, so every query keeps its own eager loads and filters (54 end in `first()`, one `exists()`, one `value()`, one org-filtered target lookup). `CurrentMembershipTest` proves the fixed order (with a precondition showing the unordered query returns the other organisation). Suite: 373/373 on SQLite; on MariaDB 10.4 only the 12 known 10.4-only failures.

## Phase 5 — Tenant scope on in the test suite
**Rationale:** §4.4.
**Scope:** run the suite once with `TENANCY_ENFORCE_GLOBAL_SCOPE=true`; fix leaks it reveals (queries missing an organisation filter); add a CI matrix leg with the flag on. Production flag stays off in this phase.
**Excludes:** turning it on in production (separate decision once the leg is green for a while).
**Acceptance:** suite green with the flag on and off.
**Rollback:** drop the matrix leg.
**Status (2026-10-14): done.** With the flag on, 105 tests failed at first. 104 had one cause, not a data leak: `Role` is tenant-scoped but the system roles have `organization_id` NULL, so every permission check lost its role — the scope could never have been switched on. Models can now declare `$tenantScopeIncludesShared` (own rows plus NULL rows); only `Role` does. The last failure was the Gantt endpoint returning tasks in no defined order; it now orders by `task_number`. Suite 374/374 with the flag off and on; CI runs both. Production flag still off — turning it on is a separate decision once CI has been green for a while.

## Phase 6 — Static analysis baseline
**Rationale:** §4.5 (Larastan unused).
**Scope:** add relation return types / `@property` annotations to the models behind the 425 `property`/`method.notFound` reports; generate a baseline for the rest; run Larastan in CI so new code cannot add errors.
**Excludes:** raising the level.
**Acceptance:** `phpstan analyse` passes with the baseline; baseline shrinks by the annotated models.
**Rollback:** remove the CI step.
**Status (2026-10-14): done.** Generic `@return` PHPDoc on 217 relations in 64 model files (comments only) took Larastan from 445 to 49 findings. Two of the remaining were real runtime errors, fixed and tested in their own commit (1f390d4): the member profile/edit form returned 500 for any member with time entries (`TimeEntry::formattedDuration()` did not exist), and saving workspace settings for a company without a settings row crashed on a missing import. The rest (selectRaw aggregates, `env()` fallbacks, a dead `Room::$floor_id` branch) are in `phpstan-baseline.neon`; Larastan runs in CI and was checked to still fail on a new undefined-method call.

## Phase 7 — Split `SuperAdminController`
**Rationale:** §4.7. Pure move.
**Scope:** one controller per area (Companies, Plans & subscriptions, Website content, Furniture & templates, Settings & translations). Routes and route names unchanged; methods moved verbatim.
**Acceptance:** `php artisan route:list --path=superadmin` identical before/after (diffed); suite passes; every superadmin screen renders (in-process render check as used on live).
**Rollback:** revert.
**Status (2026-10-14): done.** `App\Http\Controllers\SuperAdmin\{Company,Plan,PlatformSettings,OfficeCatalog,WebsiteContent}Controller`; `SuperAdminController` keeps the dashboard, permission matrix, feature flags and health (1,880 → 142 lines). All 57 methods moved verbatim (compared body by body, whitespace ignored); the full route list (356) is identical apart from the controller class on 49 actions; all 16 superadmin GET pages render 200 in-process; suite 376/376; Larastan clean (baseline path updated).

## Phase 8 — First module out of `office.blade.php`
**Rationale:** §4.7. Start with the attendance clock and heartbeat (self-contained, recently changed, well understood).
**Scope:** move that block into `resources/js/office/attendance.js` loaded through Vite; keep every element id and global function the page calls.
**Excludes:** the office engine, WebRTC, chat.
**Acceptance:** the same browser checks used in the attendance work pass (clock counts, heartbeat posts with `floor_id`, idle pause/resume); no console errors in light/dark, RTL/LTR.
**Rollback:** revert.
**Status (2026-10-14): done.** Changed from the plan: the module is a classic script, `public/js/office/attendance.js`, loaded right after the page script with the CSP nonce and a filemtime cache-buster — the existing convention (`public/js/office/room-doors.js`), and the only way to keep the shared top-level scope without rewriting call sites. 203 lines moved verbatim; the two translated toasts come from an inline `attendanceText` (Blade `@json`). Browser checks in Arabic/light and English/dark, 20/20: script loaded, clock counts, `enter` with `floor_id`, today's summary, idle prompt and confirm, `idle_pause` with idle seconds, paused overlay, resume, no page errors.

## Phase 9 — One source for page CSS
**Rationale:** §4.8.
**Scope:** either serve `ulaspace-dashboard.css` through Vite from `resources/css`, or keep `public/css` as the only copy and delete the other; add a CI check that fails if two copies differ in the meantime.
**Acceptance:** one copy; pages render identically (screenshot diff on dashboard, office, project hub).
**Rollback:** revert.
**Status (2026-10-14): done, with the "sync + check" option.** `resources/css` stays the source (as CLAUDE.md says) and `public/css` stays the served path; `scripts/sync-css.mjs` copies tokens/dashboard/office on every `npm run build`, and CI runs `--check` before the build. The check found `public/css/ulaspace-tokens.css` already stale: two committed fixes (dark-mode outline/secondary buttons, wrapping project tabs; 8bc94a2, a3935e1) had only gone into `resources/css`. Synced. Pages that also load the Vite bundle (dashboard, office, hub) already had them — computed styles identical before/after on the dashboard in dark mode; pages that load only `public/css` now get them.

## Phase 10 — Deploy script and runbook
**Rationale:** §4.6.
**Scope:** replace the stale `deploy_live_server.py` flow with a committed, secret-free script (credentials from environment): DB backup, fetch + checkout of the release branch, migrate, build, caches, ownership fix inside `virtual-workplace/` only, smoke checks (`/`, `/login`), uploads preserved.
**Acceptance:** a dry-run mode prints the steps; one real deploy performed with it, with the owner's approval.
**Rollback:** keep the manual procedure documented in the runbook.
**Status (2026-10-14): script and runbook done; the real deploy with it waits for the owner.** `ops/deploy.py` (credentials only from `DEPLOY_*` environment variables; named outside the ignored `/deploy_*.py` pattern) and `docs/DEPLOY.md` (procedure, rules learned, rollback). The read-only steps were run against live: start state, vhost-root check (`psaserv 750`), app-dir owner, smoke checks. That caught a broken realtime check (a `pm2 jlist` text match that never matched) before it could fail a deploy; it now uses `pm2 describe`. Uploads are copied aside before the checkout and restored with `cp -an` after, which is what phase 3 needs.

---

**Dependencies:** 2 before 5 and 6 (needs CI); 4 before 5; 3 before 10 (deploy must handle uploads). Phases 1, 7, 8 and 9 are independent.
