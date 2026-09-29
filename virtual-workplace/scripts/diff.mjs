// Pixel-diff a reference PNG against a live screenshot.
// Usage: node scripts/diff.mjs <referencePng> <livePng> <diffOutPng>
import { PNG } from 'pngjs';
import pixelmatch from 'pixelmatch';
import { readFileSync, writeFileSync } from 'fs';

const [, , refPath, livePath, diffPath] = process.argv;
if (!refPath || !livePath || !diffPath) {
  console.error('Usage: node scripts/diff.mjs <referencePng> <livePng> <diffOutPng>');
  process.exit(1);
}

const ref = PNG.sync.read(readFileSync(refPath));
const live = PNG.sync.read(readFileSync(livePath));

const width = Math.min(ref.width, live.width);
const height = Math.min(ref.height, live.height);

function crop(img, w, h) {
  const out = new PNG({ width: w, height: h });
  PNG.bitblt(img, out, 0, 0, w, h, 0, 0);
  return out;
}

const refC = crop(ref, width, height);
const liveC = crop(live, width, height);
const diff = new PNG({ width, height });

const mismatched = pixelmatch(refC.data, liveC.data, diff.data, width, height, {
  threshold: 0.1,
});

writeFileSync(diffPath, PNG.sync.write(diff));

const total = width * height;
const pct = (mismatched / total) * 100;
console.log(`ref: ${ref.width}x${ref.height}  live: ${live.width}x${live.height}  compared: ${width}x${height}`);
console.log(`mismatched pixels: ${mismatched} / ${total} = ${pct.toFixed(2)}%`);
