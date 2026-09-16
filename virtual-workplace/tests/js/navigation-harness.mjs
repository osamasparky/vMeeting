// Extracts the REAL navigation/collision engine functions straight out of
// resources/views/office.blade.php (by function name, not line number, so
// it keeps working as the file is edited) and runs them against real room
// data dumped from the database, to prove buildNavigationRoute() can find a
// wall-collision-free, door-routed path between every room pair on a map.
//
// Usage: node tests/js/navigation-harness.mjs <maps.json>
//
// <maps.json> is an array of { id, name, tile_size, layout_data, rooms: [{id, name, bounds}] }
// as produced by the tinker dump used during this repair (see the repair
// command's own report for the equivalent data).

import fs from 'node:fs';
import path from 'node:path';
import vm from 'node:vm';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const OFFICE_BLADE = path.join(__dirname, '..', '..', 'resources', 'views', 'office.blade.php');

const FUNCTION_NAMES = [
    'getRoomById',
    'isPointInsideRoom',
    'isPointInsideAnyForbiddenRoom',
    'getRoomDoorPortal',
    'buildPortalOnSide', // added by the getRoomDoorPortal fix; harmless if absent (pre-fix baseline run)
    'getRoomDoors',
    'distPointToSegment',
    'segmentsIntersect',
    'distBetweenSegments',
    'getAllSolidWallSegments',
    'checkCapsuleWallCollision',
    'isPathClear',
    'findAStarPath',
    'simplifyPath',
    'validateCompleteRoute',
    'buildNavigationRoute',
];

const CONST_NAMES = ['AVATAR_COLLISION_RADIUS', 'NAV_GRID_STEP', 'MIN_ROOM_GAP_PX'];

function extractFunction(source, name) {
    const sigRe = new RegExp(`function\\s+${name}\\s*\\(`);
    const sigMatch = sigRe.exec(source);
    if (!sigMatch) return null;

    const start = sigMatch.index;
    const braceStart = source.indexOf('{', sigMatch.index);
    if (braceStart === -1) return null;

    let depth = 0;
    let i = braceStart;
    for (; i < source.length; i++) {
        const ch = source[i];
        if (ch === '{') depth++;
        else if (ch === '}') {
            depth--;
            if (depth === 0) { i++; break; }
        }
    }

    return source.slice(start, i);
}

function extractConst(source, name) {
    const re = new RegExp(`const\\s+${name}\\s*=\\s*([^;\\n]+);`);
    const m = re.exec(source);
    return m ? `const ${name} = ${m[1]};` : null;
}

function buildEngineSource() {
    const source = fs.readFileSync(OFFICE_BLADE, 'utf8');

    const parts = [];
    for (const name of CONST_NAMES) {
        const block = extractConst(source, name);
        if (block) parts.push(block);
    }

    const missing = [];
    for (const name of FUNCTION_NAMES) {
        const block = extractFunction(source, name);
        if (block) {
            parts.push(block);
        } else if (name !== 'buildPortalOnSide') {
            missing.push(name);
        }
    }

    if (missing.length) {
        throw new Error(`Could not extract required function(s) from office.blade.php: ${missing.join(', ')}`);
    }

    return parts.join('\n\n');
}

function runHarness(mapsFile) {
    const engineSource = buildEngineSource();
    const mapsData = JSON.parse(fs.readFileSync(mapsFile, 'utf8'));

    let totalPairs = 0;
    let failedPairs = 0;
    const failures = [];

    for (const map of mapsData) {
        const tileSize = map.tile_size || 16;
        const roomsInput = map.rooms.map((r) => ({ id: r.id, name: r.name, bounds: r.bounds }));

        // Mirrors office.blade.php:1214-1219 exactly: the real map canvas is
        // the pre-rendered background image's pixel size, not a synthetic
        // bounding box around the rooms — using a smaller synthetic canvas
        // here would falsely disqualify legitimate "auto" door candidates
        // via the outer-margin check and understate how much open routing
        // space genuinely exists around the room cluster.
        const bgW = map.layout_data && Number(map.layout_data.background_width);
        const bgH = map.layout_data && Number(map.layout_data.background_height);
        const maxX = Math.max(...roomsInput.map((r) => (r.bounds.x + r.bounds.width) * tileSize), 500);
        const maxY = Math.max(...roomsInput.map((r) => (r.bounds.y + r.bounds.height) * tileSize), 500);

        const sandbox = {
            TILE_SIZE: tileSize,
            MAP_WIDTH_PX: (bgW && bgW >= 500) ? bgW : maxX + 200,
            MAP_HEIGHT_PX: (bgH && bgH >= 500) ? bgH : maxY + 200,
            rooms: roomsInput,
            roomDoorStates: new Map(),
            roomDoorPortalsCache: new Map(),
            console,
        };
        vm.createContext(sandbox);
        vm.runInContext(engineSource, sandbox, { filename: 'office-engine.js' });

        const centerOf = (room) => ({
            x: (room.bounds.x + room.bounds.width / 2) * tileSize,
            y: (room.bounds.y + room.bounds.height / 2) * tileSize,
        });

        for (const a of roomsInput) {
            for (const b of roomsInput) {
                if (a.id === b.id) continue;
                totalPairs++;

                const roomA = sandbox.getRoomById(a.id);
                const roomB = sandbox.getRoomById(b.id);
                const route = sandbox.buildNavigationRoute(centerOf(a), centerOf(b), roomA, roomB);

                let ok = Array.isArray(route) && route.length > 0;
                if (ok) {
                    ok = sandbox.validateCompleteRoute([{ x: centerOf(a).x, y: centerOf(a).y }, ...route]);
                }

                if (!ok) {
                    failedPairs++;
                    failures.push(`${map.name}: ${a.name} -> ${b.name}`);
                }
            }
        }
    }

    return { totalPairs, failedPairs, failures };
}

const mapsFile = process.argv[2];
if (!mapsFile) {
    console.error('Usage: node navigation-harness.mjs <maps.json>');
    process.exit(2);
}

const result = runHarness(mapsFile);
console.log(`Route assertions: ${result.totalPairs}`);
console.log(`Failed: ${result.failedPairs}`);
if (result.failures.length) {
    console.log('Failures (first 30):');
    for (const f of result.failures.slice(0, 30)) console.log(`  - ${f}`);
}
process.exit(result.failedPairs > 0 ? 1 : 0);
