// Logs in the QA test account and saves Playwright storage state so shot.mjs
// can reuse the session for authenticated pages.
import { chromium } from 'playwright';

const EMAIL = 'redesign-qa@example.test';
const PASSWORD = 'RedesignQA12345!';

const browser = await chromium.launch();
const page = await browser.newPage();
await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
await page.fill('#email', EMAIL);
await page.fill('#password', PASSWORD);
await page.click('#loginBtn');
await page.waitForLoadState('networkidle');
console.log('URL after login:', page.url());

await page.context().storageState({ path: 'scripts/.auth-state.json' });
console.log('saved scripts/.auth-state.json');

await browser.close();
