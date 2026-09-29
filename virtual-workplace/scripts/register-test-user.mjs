// One-off: create a fresh test account via the real /register form so we have
// known credentials for authenticated screenshot verification, without
// touching any existing user's data.
import { chromium } from 'playwright';

const EMAIL = 'redesign-qa@example.test';
const PASSWORD = 'RedesignQA12345!';

const browser = await chromium.launch();
const page = await browser.newPage();
await page.goto('http://127.0.0.1:8000/register', { waitUntil: 'networkidle' });

await page.fill('#name', 'Redesign QA');
await page.fill('#email', EMAIL);
await page.fill('#password', PASSWORD);
await page.fill('#password_confirmation', PASSWORD);
await page.fill('#organization_name', 'Redesign QA Org');

await page.click('#registerBtn');
await page.waitForLoadState('networkidle');

console.log('URL after submit:', page.url());
console.log('EMAIL:', EMAIL);
console.log('PASSWORD:', PASSWORD);

const bodyText = await page.evaluate(() => document.body.innerText.slice(0, 500));
console.log('--- page text sample ---');
console.log(bodyText);

await browser.close();
