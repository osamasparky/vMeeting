// Pages load the design-system stylesheets straight from public/css (asset('css/…')),
// while resources/css is where they are edited (CLAUDE.md) and what Vite bundles.
// This copies resources → public so the two can no longer drift.
//
//   node scripts/sync-css.mjs          copy (run by `npm run build`)
//   node scripts/sync-css.mjs --check  exit 1 if a public copy differs (CI)
import { readFileSync, writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const files = ['ulaspace-tokens.css', 'ulaspace-dashboard.css', 'ulaspace-office.css'];
const check = process.argv.includes('--check');
let stale = 0;

for (const file of files) {
    const source = readFileSync(join(root, 'resources/css', file));
    const target = join(root, 'public/css', file);
    let current = null;
    try {
        current = readFileSync(target);
    } catch {
        // missing copy counts as stale
    }
    if (current && source.equals(current)) {
        continue;
    }
    stale++;
    if (check) {
        console.error(`public/css/${file} differs from resources/css/${file} — run: node scripts/sync-css.mjs`);
    } else {
        writeFileSync(target, source);
        console.log(`synced public/css/${file}`);
    }
}

if (check && stale) {
    process.exit(1);
}
