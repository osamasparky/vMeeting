// Screenshot a live page for reference-vs-live comparison.
// Usage: node scripts/shot.mjs <url> <outPath> [dir] [theme] [auth]
//   dir:   rtl (default) | ltr   -- also drives the real server-side locale
//          (ar for rtl, en for ltr) via /lang/{locale}, not just the
//          HTML dir attribute, so nav/labels actually render in that
//          language instead of just flipping text direction cosmetically.
//   theme: light (default) | dark
//   auth:  guest (default) | qa  -- "qa" reuses the saved QA login session
//          (scripts/.auth-state.json, created by scripts/login.mjs). Only
//          pass "qa" for pages that require auth (dashboard/office/editor/
//          projects) -- guest-facing pages (landing/auth/guest-join) must
//          stay logged out to match the reference's guest-state header.
import { chromium } from 'playwright';
import { existsSync } from 'fs';

const [, , url, outPath, dir = 'rtl', theme = 'light', auth = 'guest'] = process.argv;
if (!url || !outPath) {
  console.error('Usage: node scripts/shot.mjs <url> <outPath> [rtl|ltr] [light|dark] [guest|qa]');
  process.exit(1);
}

const AUTH_STATE = 'scripts/.auth-state.json';
const locale = dir === 'ltr' ? 'en' : 'ar';
const origin = new URL(url).origin;

const browser = await chromium.launch();
const context = await browser.newContext({
  viewport: { width: 1440, height: 900 },
  deviceScaleFactor: 2,
  storageState: auth === 'qa' && existsSync(AUTH_STATE) ? AUTH_STATE : undefined,
});
const page = await context.newPage();

// Set the real server-side locale first (session-driven), not just the
// HTML dir attribute, so nav/labels/copy actually render in that language.
await page.goto(`${origin}/lang/${locale}`, { waitUntil: 'domcontentloaded' });

await page.goto(url, { waitUntil: 'networkidle' });
await page.evaluate((theme) => {
  document.documentElement.setAttribute('data-theme', theme);
  if (theme === 'dark') {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
  try { localStorage.setItem('vw_theme', theme); } catch {}
}, theme);
await page.reload({ waitUntil: 'networkidle' });
await page.evaluate((theme) => {
  document.documentElement.setAttribute('data-theme', theme);
}, theme);
await page.waitForTimeout(800);

await page.screenshot({ path: outPath, fullPage: true });
await browser.close();
console.log(`saved ${outPath} (${dir}/${locale}, ${theme}, ${auth})`);
