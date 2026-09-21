repo: osamasparky/vMeeting
branch: main
path: virtual-workplace

## Last sync
date: 2026-09-16T02:20:00Z

### Updated in this project
- Rewrote `resources/css/ulaspace-tokens.css` from the Figma source: 62 primitives, 104 semantic tokens with Light/Dark, spacing, shape, typography, motion, elevation
- Regenerated `resources/design-system/tokens.json` from the Figma variable collections (226 variables, 47 text styles, 9 effect styles)
- Corrected all 16 Blade components in `resources/views/components/` to the Figma component sets (props and behaviour unchanged)
- Swept 67 views + 3 stylesheets: 5,679 token references rewritten to `--ula-*`, 335 retired declarations removed, 67 stale hex literals fixed; the blue/violet palette in `app.css` retired

## Screen map
| Project screen / artifact | Repo files |
| :--- | :--- |
| Token layer | virtual-workplace/resources/css/ulaspace-tokens.css, virtual-workplace/resources/css/app.css, virtual-workplace/resources/design-system/tokens.json |
| Blade components (16) | virtual-workplace/resources/views/components/*.blade.php |
| Website / landing | virtual-workplace/resources/views/landing/home.blade.php, landing/layout.blade.php |
| Dashboard + 16 tabs | virtual-workplace/resources/views/dashboard.blade.php, dashboard/partials/*.blade.php |
| Floor map / office | virtual-workplace/resources/views/office.blade.php, office/partials/modals.blade.php, resources/css/ulaspace-office.css |
| Projects hub | virtual-workplace/resources/views/projects/hub.blade.php, projects/partials/modals.blade.php |
| Superadmin (13 views) | virtual-workplace/resources/views/superadmin/*.blade.php, superadmin/cms/*.blade.php |
| Auth / billing / editor / guest join | virtual-workplace/resources/views/auth/*, layouts/auth.blade.php, billing/payment.blade.php, editor.blade.php, guest_join.blade.php |
| Figma token + component reference | uploads/UlaSpace - Design System.fig, extract/ULASPACE-DS.md, extract/component-specs.md |
| Change list for the PR | CHANGES.md |

## Sync history
- 2026-09-16T01:56:03Z — read the repo design-system layer and docs; recorded the drift against the Figma file; no repo files changed
