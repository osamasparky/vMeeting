const fs = require('fs');
const content = fs.readFileSync('resources/views/office.blade.php', 'utf8');
const scriptMatch = content.match(/<script>([\s\S]*?)<\/script>/);

if (!scriptMatch) {
    console.error('No <script> tag found');
    process.exit(1);
}

let js = scriptMatch[1];
// Mock blade variables
js = js.replace(/\{\{\s*.*?\}\}/g, '"mock"');
js = js.replace(/@json\(.*?\)/g, '{}');
js = js.replace(/@if\([\s\S]*?@endif/g, '');

try {
    new Function(js);
    console.log('✅ JavaScript syntax in office.blade.php is 100% VALID!');
} catch (e) {
    console.error('❌ JS Syntax Error:', e);
}
