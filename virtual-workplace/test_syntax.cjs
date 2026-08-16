const fs = require('fs');
const content = fs.readFileSync('resources/views/office.blade.php', 'utf8');
const scriptMatch = content.match(/<script>([\s\S]*?)<\/script>/);

let js = scriptMatch[1];
// Replace quotes with blade tags properly
js = js.replace(/\"\{\{\s*.*?\}\}\"/g, '"mock"');
js = js.replace(/\{\{\s*.*?\}\}/g, '"mock"');
js = js.replace(/@json\(.*?\)/g, '{}');
js = js.replace(/@if\([\s\S]*?@endif/g, '');

const lines = js.split('\n');
let hadError = false;
try {
    new Function(js);
    console.log('🎉 WHOLE JAVASCRIPT IS 100% VALID!');
} catch (e) {
    console.log('Error in whole script:', e);
    hadError = true;
}
