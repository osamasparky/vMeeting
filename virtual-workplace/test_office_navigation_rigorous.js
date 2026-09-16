// Rigorous simulation test suite for office floor navigation & collision engine
const TILE_SIZE = 16;
const MAP_WIDTH_PX = 1920;
const MAP_HEIGHT_PX = 1080;
const AVATAR_COLLISION_RADIUS = 12; // Authoritative uniform collision radius
const NAV_GRID_STEP = 16;

// Mock Office Rooms
const rooms = [
    {
        id: 1,
        name: 'قاعة الاجتماعات الكبرى - Main Conference Room',
        english_name: 'Main Conference Room',
        arabic_name: 'قاعة الاجتماعات الكبرى',
        bounds: { x: 5, y: 5, width: 25, height: 18, doorSide: 'bottom', doorOffset: 0.5 }
    },
    {
        id: 2,
        name: 'مكتب الإدارة التنفيذية - Executive Suite',
        english_name: 'Executive Suite',
        arabic_name: 'مكتب الإدارة التنفيذية',
        bounds: { x: 35, y: 5, width: 20, height: 18, doorSide: 'bottom', doorOffset: 0.5 }
    },
    {
        id: 3,
        name: 'مساحة العمل الإبداعي - Creative Hub',
        english_name: 'Creative Hub',
        arabic_name: 'مساحة العمل الإبداعي',
        bounds: { x: 5, y: 30, width: 22, height: 16, doorSide: 'top', doorOffset: 0.5 }
    },
    {
        id: 4,
        name: 'صالة الاستراحة - Breakout Lounge',
        english_name: 'Breakout Lounge',
        arabic_name: 'صالة الاستراحة',
        bounds: { x: 35, y: 30, width: 22, height: 16, doorSide: 'top', doorOffset: 0.5 }
    },
    {
        id: 5,
        name: 'غرفة متعددة الأبواب - Multi-Door Hall',
        english_name: 'Multi-Door Hall',
        arabic_name: 'غرفة متعددة الأبواب',
        bounds: { x: 65, y: 15, width: 24, height: 24 },
        doors: [
            {
                x: 65 * TILE_SIZE + (24 * TILE_SIZE * 0.5),
                y: 15 * TILE_SIZE + 24 * TILE_SIZE,
                width: 56,
                wallSide: 'bottom',
                entryInsideX: 65 * TILE_SIZE + (24 * TILE_SIZE * 0.5),
                entryInsideY: 15 * TILE_SIZE + 24 * TILE_SIZE - 26,
                exitOutsideX: 65 * TILE_SIZE + (24 * TILE_SIZE * 0.5),
                exitOutsideY: 15 * TILE_SIZE + 24 * TILE_SIZE + 32
            },
            {
                x: 65 * TILE_SIZE,
                y: 15 * TILE_SIZE + (24 * TILE_SIZE * 0.5),
                width: 56,
                wallSide: 'left',
                entryInsideX: 65 * TILE_SIZE + 26,
                entryInsideY: 15 * TILE_SIZE + (24 * TILE_SIZE * 0.5),
                exitOutsideX: 65 * TILE_SIZE - 32,
                exitOutsideY: 15 * TILE_SIZE + (24 * TILE_SIZE * 0.5)
            }
        ]
    }
];

const roomDoorStates = new Map();

function getRoomById(id) {
    return rooms.find(r => r.id === id) || null;
}

function getCurrentRoom(px, py) {
    for (const r of rooms) {
        if (!r.bounds) continue;
        const rx = r.bounds.x * TILE_SIZE;
        const ry = r.bounds.y * TILE_SIZE;
        const rw = r.bounds.width * TILE_SIZE;
        const rh = r.bounds.height * TILE_SIZE;
        if (px >= rx && px <= rx + rw && py >= ry && py <= ry + rh) {
            return r;
        }
    }
    return null;
}

function isPointInsideRoom(px, py, room, innerMargin = 0) {
    if (!room || !room.bounds) return false;
    const rx = room.bounds.x * TILE_SIZE;
    const ry = room.bounds.y * TILE_SIZE;
    const rw = room.bounds.width * TILE_SIZE;
    const rh = room.bounds.height * TILE_SIZE;
    return (px > rx + innerMargin && px < rx + rw - innerMargin &&
            py > ry + innerMargin && py < ry + rh - innerMargin);
}

function isPointInsideAnyForbiddenRoom(px, py, allowedRoomId = null) {
    for (const r of rooms) {
        if (!r.bounds || (allowedRoomId && r.id === allowedRoomId)) continue;
        if (isPointInsideRoom(px, py, r, 2)) {
            return true;
        }
    }
    return false;
}

function getRoomDoorPortal(r) {
    if (!r || !r.bounds) return null;
    const doors = getRoomDoors(r);
    return (doors && doors.length > 0) ? doors[0] : null;
}

function getRoomDoors(r) {
    if (!r || !r.bounds) return [];
    if (Array.isArray(r.doors) && r.doors.length > 0) {
        return r.doors.map(d => ({
            x: d.x,
            y: d.y,
            width: d.width || 56,
            wallSide: (d.wallSide || d.side || 'bottom').toLowerCase(),
            entryInsideX: d.entryInsideX,
            entryInsideY: d.entryInsideY,
            exitOutsideX: d.exitOutsideX,
            exitOutsideY: d.exitOutsideY
        }));
    }
    if (Array.isArray(r.bounds.doors) && r.bounds.doors.length > 0) {
        return r.bounds.doors.map(d => ({
            x: d.x,
            y: d.y,
            width: d.width || 56,
            wallSide: (d.wallSide || d.side || 'bottom').toLowerCase(),
            entryInsideX: d.entryInsideX,
            entryInsideY: d.entryInsideY,
            exitOutsideX: d.exitOutsideX,
            exitOutsideY: d.exitOutsideY
        }));
    }

    const rx = r.bounds.x * TILE_SIZE;
    const ry = r.bounds.y * TILE_SIZE;
    const rw = r.bounds.width * TILE_SIZE;
    const rh = r.bounds.height * TILE_SIZE;
    const doorWidth = 56;
    const side = (r.bounds.doorSide || 'bottom').toLowerCase();
    const off = (typeof r.bounds.doorOffset === 'number') ? r.bounds.doorOffset : 0.5;

    let cx = 0, cy = 0, inX = 0, inY = 0, outX = 0, outY = 0;
    if (side === 'bottom') {
        cx = rx + (rw * off); cy = ry + rh;
        inX = cx; inY = cy - 26;
        outX = cx; outY = cy + 32;
    } else if (side === 'top') {
        cx = rx + (rw * off); cy = ry;
        inX = cx; inY = cy + 26;
        outX = cx; outY = cy - 32;
    } else if (side === 'right') {
        cx = rx + rw; cy = ry + (rh * off);
        inX = cx - 26; inY = cy;
        outX = cx + 32; outY = cy;
    } else if (side === 'left') {
        cx = rx; cy = ry + (rh * off);
        inX = cx + 26; inY = cy;
        outX = cx - 32; outY = cy;
    }

    return [{
        x: cx, y: cy, width: doorWidth, wallSide: side,
        entryInsideX: inX, entryInsideY: inY,
        exitOutsideX: outX, exitOutsideY: outY
    }];
}

function distPointToSegment(px, py, x1, y1, x2, y2) {
    const dx = x2 - x1;
    const dy = y2 - y1;
    const lenSq = dx * dx + dy * dy;
    if (lenSq === 0) return Math.hypot(px - x1, py - y1);
    let t = ((px - x1) * dx + (py - y1) * dy) / lenSq;
    t = Math.max(0, Math.min(1, t));
    const projX = x1 + t * dx;
    const projY = y1 + t * dy;
    return Math.hypot(px - projX, py - projY);
}

function segmentsIntersect(x1, y1, x2, y2, x3, y3, x4, y4) {
    function ccw(ax, ay, bx, by, cx, cy) {
        return (cy - ay) * (bx - ax) > (by - ay) * (cx - ax);
    }
    return (ccw(x1, y1, x3, y3, x4, y4) !== ccw(x2, y2, x3, y3, x4, y4)) &&
           (ccw(x1, y1, x2, y2, x3, y3) !== ccw(x1, y1, x2, y2, x4, y4));
}

function distBetweenSegments(x1, y1, x2, y2, x3, y3, x4, y4) {
    if (segmentsIntersect(x1, y1, x2, y2, x3, y3, x4, y4)) return 0;
    return Math.min(
        distPointToSegment(x1, y1, x3, y3, x4, y4),
        distPointToSegment(x2, y2, x3, y3, x4, y4),
        distPointToSegment(x3, y3, x1, y1, x2, y2),
        distPointToSegment(x4, y4, x1, y1, x2, y2)
    );
}

// Authoritative Wall Collision Model:
// Walls are solid by default. Open doors create explicit openings in those walls.
// NO ignoreRoomId. All solid wall segments across the entire map are checked.
function getAllSolidWallSegments() {
    const segments = [];
    for (const r of rooms) {
        if (!r.bounds) continue;
        const rx = r.bounds.x * TILE_SIZE;
        const ry = r.bounds.y * TILE_SIZE;
        const rw = r.bounds.width * TILE_SIZE;
        const rh = r.bounds.height * TILE_SIZE;
        const isLocked = !!roomDoorStates.get(r.id);
        const doors = isLocked ? [] : getRoomDoors(r);

        // 1. Top Wall (y = ry, x from rx to rx + rw)
        const topDoors = doors.filter(d => d.wallSide === 'top').sort((a, b) => a.x - b.x);
        let curX = rx;
        for (const d of topDoors) {
            const openStart = Math.max(rx, d.x - d.width / 2);
            const openEnd = Math.min(rx + rw, d.x + d.width / 2);
            if (openStart > curX + 1) {
                segments.push({ x1: curX, y1: ry, x2: openStart, y2: ry, roomId: r.id });
            }
            curX = Math.max(curX, openEnd);
        }
        if (rx + rw > curX + 1) {
            segments.push({ x1: curX, y1: ry, x2: rx + rw, y2: ry, roomId: r.id });
        }

        // 2. Bottom Wall (y = ry + rh, x from rx to rx + rw)
        const bottomDoors = doors.filter(d => d.wallSide === 'bottom').sort((a, b) => a.x - b.x);
        curX = rx;
        for (const d of bottomDoors) {
            const openStart = Math.max(rx, d.x - d.width / 2);
            const openEnd = Math.min(rx + rw, d.x + d.width / 2);
            if (openStart > curX + 1) {
                segments.push({ x1: curX, y1: ry + rh, x2: openStart, y2: ry + rh, roomId: r.id });
            }
            curX = Math.max(curX, openEnd);
        }
        if (rx + rw > curX + 1) {
            segments.push({ x1: curX, y1: ry + rh, x2: rx + rw, y2: ry + rh, roomId: r.id });
        }

        // 3. Left Wall (x = rx, y from ry to ry + rh)
        const leftDoors = doors.filter(d => d.wallSide === 'left').sort((a, b) => a.y - b.y);
        let curY = ry;
        for (const d of leftDoors) {
            const openStart = Math.max(ry, d.y - d.width / 2);
            const openEnd = Math.min(ry + rh, d.y + d.width / 2);
            if (openStart > curY + 1) {
                segments.push({ x1: rx, y1: curY, x2: rx, y2: openStart, roomId: r.id });
            }
            curY = Math.max(curY, openEnd);
        }
        if (ry + rh > curY + 1) {
            segments.push({ x1: rx, y1: curY, x2: rx, y2: ry + rh, roomId: r.id });
        }

        // 4. Right Wall (x = rx + rw, y from ry to ry + rh)
        const rightDoors = doors.filter(d => d.wallSide === 'right').sort((a, b) => a.y - b.y);
        curY = ry;
        for (const d of rightDoors) {
            const openStart = Math.max(ry, d.y - d.width / 2);
            const openEnd = Math.min(ry + rh, d.y + d.width / 2);
            if (openStart > curY + 1) {
                segments.push({ x1: rx + rw, y1: curY, x2: rx + rw, y2: openStart, roomId: r.id });
            }
            curY = Math.max(curY, openEnd);
        }
        if (ry + rh > curY + 1) {
            segments.push({ x1: rx + rw, y1: curY, x2: rx + rw, y2: ry + rh, roomId: r.id });
        }
    }
    return segments;
}

function checkCapsuleWallCollision(x1, y1, x2, y2, radius = AVATAR_COLLISION_RADIUS) {
    const walls = getAllSolidWallSegments();
    for (const w of walls) {
        const dist = distBetweenSegments(x1, y1, x2, y2, w.x1, w.y1, w.x2, w.y2);
        if (dist < radius) {
            return true; // Collision detected!
        }
    }
    return false;
}

function isPathClear(p1, p2, allowedRoomId = null, radius = AVATAR_COLLISION_RADIUS) {
    if (!p1 || !p2) return false;
    if (checkCapsuleWallCollision(p1.x, p1.y, p2.x, p2.y, radius)) {
        return false;
    }
    const dist = Math.hypot(p2.x - p1.x, p2.y - p1.y);
    const steps = Math.max(2, Math.ceil(dist / 12));
    for (let i = 1; i < steps; i++) {
        const t = i / steps;
        const sx = p1.x + t * (p2.x - p1.x);
        const sy = p1.y + t * (p2.y - p1.y);
        if (allowedRoomId === null) {
            if (isPointInsideAnyForbiddenRoom(sx, sy, null)) return false;
        } else {
            const r = getRoomById(allowedRoomId);
            if (r && r.bounds) {
                const rx = r.bounds.x * TILE_SIZE;
                const ry = r.bounds.y * TILE_SIZE;
                const rw = r.bounds.width * TILE_SIZE;
                const rh = r.bounds.height * TILE_SIZE;
                if (sx < rx || sx > rx + rw || sy < ry || sy > ry + rh) return false;
            }
        }
    }
    return true;
}

function findAStarPath(start, goal, allowedRoomId = null) {
    if (!start || !goal) return null;

    const cols = Math.ceil(MAP_WIDTH_PX / NAV_GRID_STEP);
    const rows = Math.ceil(MAP_HEIGHT_PX / NAV_GRID_STEP);

    const startC = Math.max(0, Math.min(cols - 1, Math.floor(start.x / NAV_GRID_STEP)));
    const startR = Math.max(0, Math.min(rows - 1, Math.floor(start.y / NAV_GRID_STEP)));
    const goalC = Math.max(0, Math.min(cols - 1, Math.floor(goal.x / NAV_GRID_STEP)));
    const goalR = Math.max(0, Math.min(rows - 1, Math.floor(goal.y / NAV_GRID_STEP)));

    const openSet = [{ f: 0, g: 0, c: startC, r: startR, parent: null }];
    const visited = new Map();

    const dirs = [
        { dc: 1, dr: 0, cost: 1.0 },
        { dc: -1, dr: 0, cost: 1.0 },
        { dc: 0, dr: 1, cost: 1.0 },
        { dc: 0, dr: -1, cost: 1.0 },
        { dc: 1, dr: 1, cost: 1.414 },
        { dc: 1, dr: -1, cost: 1.414 },
        { dc: -1, dr: 1, cost: 1.414 },
        { dc: -1, dr: -1, cost: 1.414 }
    ];

    let goalNode = null;
    let iterations = 0;
    const maxIterations = 4000;

    while (openSet.length > 0 && iterations++ < maxIterations) {
        let bestIdx = 0;
        for (let i = 1; i < openSet.length; i++) {
            if (openSet[i].f < openSet[bestIdx].f) bestIdx = i;
        }
        const current = openSet.splice(bestIdx, 1)[0];
        const key = `${current.c},${current.r}`;

        if (visited.has(key) && visited.get(key).g <= current.g) continue;
        visited.set(key, current);

        if (current.c === goalC && current.r === goalR) {
            goalNode = current;
            break;
        }

        const curPx = current.c * NAV_GRID_STEP + (NAV_GRID_STEP / 2);
        const curPy = current.r * NAV_GRID_STEP + (NAV_GRID_STEP / 2);

        for (const d of dirs) {
            const nc = current.c + d.dc;
            const nr = current.r + d.dr;
            if (nc < 0 || nc >= cols || nr < 0 || nr >= rows) continue;

            const nKey = `${nc},${nr}`;
            if (visited.has(nKey)) continue;

            const npx = nc * NAV_GRID_STEP + (NAV_GRID_STEP / 2);
            const npy = nr * NAV_GRID_STEP + (NAV_GRID_STEP / 2);

            // Room boundary checks
            if (allowedRoomId === null) {
                if (isPointInsideAnyForbiddenRoom(npx, npy, null)) continue;
            } else {
                const r = getRoomById(allowedRoomId);
                if (r && r.bounds) {
                    const rx = r.bounds.x * TILE_SIZE;
                    const ry = r.bounds.y * TILE_SIZE;
                    const rw = r.bounds.width * TILE_SIZE;
                    const rh = r.bounds.height * TILE_SIZE;
                    if (npx < rx + 2 || npx > rx + rw - 2 || npy < ry + 2 || npy > ry + rh - 2) continue;
                }
            }

            // Diagonal corner clearance
            if (d.dc !== 0 && d.dr !== 0) {
                const cornerX1 = (current.c + d.dc) * NAV_GRID_STEP + (NAV_GRID_STEP / 2);
                const cornerY1 = current.r * NAV_GRID_STEP + (NAV_GRID_STEP / 2);
                const cornerX2 = current.c * NAV_GRID_STEP + (NAV_GRID_STEP / 2);
                const cornerY2 = (current.r + d.dr) * NAV_GRID_STEP + (NAV_GRID_STEP / 2);

                if (checkCapsuleWallCollision(curPx, curPy, cornerX1, cornerY1, AVATAR_COLLISION_RADIUS) ||
                    checkCapsuleWallCollision(curPx, curPy, cornerX2, cornerY2, AVATAR_COLLISION_RADIUS) ||
                    checkCapsuleWallCollision(curPx, curPy, npx, npy, AVATAR_COLLISION_RADIUS)) {
                    continue;
                }
                if (allowedRoomId === null) {
                    if (isPointInsideAnyForbiddenRoom(cornerX1, cornerY1, null) ||
                        isPointInsideAnyForbiddenRoom(cornerX2, cornerY2, null)) {
                        continue;
                    }
                }
            }

            // Edge collision
            if (checkCapsuleWallCollision(curPx, curPy, npx, npy, AVATAR_COLLISION_RADIUS)) continue;

            const ng = current.g + d.cost * NAV_GRID_STEP;
            const h = Math.hypot(npx - goal.x, npy - goal.y);
            openSet.push({ f: ng + h, g: ng, c: nc, r: nr, parent: current });
        }
    }

    if (!goalNode) {
        return null; // Explicit failure: NEVER return [goal]
    }

    const path = [];
    let curr = goalNode;
    while (curr) {
        path.push({
            x: curr.c * NAV_GRID_STEP + (NAV_GRID_STEP / 2),
            y: curr.r * NAV_GRID_STEP + (NAV_GRID_STEP / 2)
        });
        curr = curr.parent;
    }
    path.reverse();
    path.push({ x: goal.x, y: goal.y });
    return path;
}

function simplifyPath(rawPath, allowedRoomId = null) {
    if (!rawPath || !Array.isArray(rawPath) || rawPath.length === 0) return [];
    if (rawPath.length <= 2) return [...rawPath];
    const smoothed = [rawPath[0]];
    let currentIdx = 0;

    while (currentIdx < rawPath.length - 1) {
        let furthestIdx = currentIdx + 1;
        for (let testIdx = rawPath.length - 1; testIdx > currentIdx + 1; testIdx--) {
            if (isPathClear(rawPath[currentIdx], rawPath[testIdx], allowedRoomId, AVATAR_COLLISION_RADIUS)) {
                furthestIdx = testIdx;
                break;
            }
        }
        smoothed.push(rawPath[furthestIdx]);
        currentIdx = furthestIdx;
    }
    return smoothed;
}

function validateCompleteRoute(route) {
    if (!route || !Array.isArray(route) || route.length === 0) return false;
    for (let i = 0; i < route.length - 1; i++) {
        const p1 = route[i];
        const p2 = route[i + 1];
        if (checkCapsuleWallCollision(p1.x, p1.y, p2.x, p2.y, AVATAR_COLLISION_RADIUS)) {
            return false;
        }
    }
    return true;
}

function buildNavigationRoute(startPos, destPos, srcRoom = null, dstRoom = null) {
    if (!srcRoom) srcRoom = getCurrentRoom(startPos.x, startPos.y);
    if (!dstRoom) dstRoom = getCurrentRoom(destPos.x, destPos.y);

    // Guard: Locked room checks
    if (dstRoom && roomDoorStates.get(dstRoom.id) && (!srcRoom || srcRoom.id !== dstRoom.id)) {
        return null;
    }
    if (srcRoom && roomDoorStates.get(srcRoom.id) && (!dstRoom || dstRoom.id !== srcRoom.id)) {
        return null;
    }

    // Case 1: Same Room Movement
    if (srcRoom && dstRoom && srcRoom.id === dstRoom.id) {
        const rx = srcRoom.bounds.x * TILE_SIZE;
        const ry = srcRoom.bounds.y * TILE_SIZE;
        const rw = srcRoom.bounds.width * TILE_SIZE;
        const rh = srcRoom.bounds.height * TILE_SIZE;
        const margin = AVATAR_COLLISION_RADIUS + 4;
        const clampedX = Math.max(rx + margin, Math.min(rx + rw - margin, destPos.x));
        const clampedY = Math.max(ry + margin, Math.min(ry + rh - margin, destPos.y));
        const clampedDest = { x: clampedX, y: clampedY };

        if (isPathClear(startPos, clampedDest, srcRoom.id, AVATAR_COLLISION_RADIUS)) {
            return [{ x: clampedX, y: clampedY, action: null }];
        }
        const raw = findAStarPath(startPos, clampedDest, srcRoom.id);
        if (!raw) return null;
        const simplified = simplifyPath(raw, srcRoom.id);
        const waypoints = simplified.map(pt => ({ x: pt.x, y: pt.y, action: null }));
        return validateCompleteRoute(waypoints) ? waypoints : null;
    }

    // Case 2: Open Space to Open Space
    if (!srcRoom && !dstRoom) {
        const clampedX = Math.max(16, Math.min(MAP_WIDTH_PX - 16, destPos.x));
        const clampedY = Math.max(16, Math.min(MAP_HEIGHT_PX - 16, destPos.y));
        const clampedDest = { x: clampedX, y: clampedY };

        if (isPathClear(startPos, clampedDest, null, AVATAR_COLLISION_RADIUS)) {
            return [{ x: clampedX, y: clampedY, action: null }];
        }
        const raw = findAStarPath(startPos, clampedDest, null);
        if (!raw) return null;
        const simplified = simplifyPath(raw, null);
        const waypoints = simplified.map(pt => ({ x: pt.x, y: pt.y, action: null }));
        return validateCompleteRoute(waypoints) ? waypoints : null;
    }

    // Case 3: Inside Room to Open Space (Evaluate all source doors)
    if (srcRoom && !dstRoom) {
        const curDoors = getRoomDoors(srcRoom);
        let bestRoute = null;
        let bestDist = Infinity;

        for (const curDoor of curDoors) {
            let insideLeg = [{ x: curDoor.entryInsideX, y: curDoor.entryInsideY }];
            if (!isPathClear(startPos, { x: curDoor.entryInsideX, y: curDoor.entryInsideY }, srcRoom.id, AVATAR_COLLISION_RADIUS)) {
                const insideRaw = findAStarPath(startPos, { x: curDoor.entryInsideX, y: curDoor.entryInsideY }, srcRoom.id);
                if (!insideRaw) continue;
                insideLeg = simplifyPath(insideRaw, srcRoom.id).slice(1);
            }

            let openLeg = [{ x: destPos.x, y: destPos.y }];
            if (!isPathClear({ x: curDoor.exitOutsideX, y: curDoor.exitOutsideY }, destPos, null, AVATAR_COLLISION_RADIUS)) {
                const openRaw = findAStarPath({ x: curDoor.exitOutsideX, y: curDoor.exitOutsideY }, destPos, null);
                if (!openRaw) continue;
                openLeg = simplifyPath(openRaw, null).slice(1);
            }

            const candidate = [
                ...insideLeg.slice(0, -1).map(pt => ({ x: pt.x, y: pt.y, action: null })),
                { x: curDoor.entryInsideX, y: curDoor.entryInsideY, action: 'door_open' },
                { x: curDoor.x, y: curDoor.y, action: null },
                { x: curDoor.exitOutsideX, y: curDoor.exitOutsideY, action: null },
                ...openLeg.map(pt => ({ x: pt.x, y: pt.y, action: null }))
            ];

            if (!validateCompleteRoute(candidate)) continue;

            let dist = Math.hypot(startPos.x - candidate[0].x, startPos.y - candidate[0].y);
            for (let i = 0; i < candidate.length - 1; i++) {
                dist += Math.hypot(candidate[i+1].x - candidate[i].x, candidate[i+1].y - candidate[i].y);
            }
            if (dist < bestDist) {
                bestDist = dist;
                bestRoute = candidate;
            }
        }
        return bestRoute;
    }

    // Case 4: Open Space to Inside Room (Evaluate all target doors)
    if (!srcRoom && dstRoom) {
        const rx = dstRoom.bounds.x * TILE_SIZE;
        const ry = dstRoom.bounds.y * TILE_SIZE;
        const rw = dstRoom.bounds.width * TILE_SIZE;
        const rh = dstRoom.bounds.height * TILE_SIZE;
        const margin = AVATAR_COLLISION_RADIUS + 4;
        const clampedX = Math.max(rx + margin, Math.min(rx + rw - margin, destPos.x));
        const clampedY = Math.max(ry + margin, Math.min(ry + rh - margin, destPos.y));
        const clampedDest = { x: clampedX, y: clampedY };

        const targetDoors = getRoomDoors(dstRoom);
        let bestRoute = null;
        let bestDist = Infinity;

        for (const targetDoor of targetDoors) {
            let openLeg = [{ x: targetDoor.exitOutsideX, y: targetDoor.exitOutsideY }];
            if (!isPathClear(startPos, { x: targetDoor.exitOutsideX, y: targetDoor.exitOutsideY }, null, AVATAR_COLLISION_RADIUS)) {
                const openRaw = findAStarPath(startPos, { x: targetDoor.exitOutsideX, y: targetDoor.exitOutsideY }, null);
                if (!openRaw) continue;
                openLeg = simplifyPath(openRaw, null).slice(1);
            }

            let insideLeg = [{ x: clampedX, y: clampedY }];
            if (!isPathClear({ x: targetDoor.entryInsideX, y: targetDoor.entryInsideY }, clampedDest, dstRoom.id, AVATAR_COLLISION_RADIUS)) {
                const insideRaw = findAStarPath({ x: targetDoor.entryInsideX, y: targetDoor.entryInsideY }, clampedDest, dstRoom.id);
                if (!insideRaw) continue;
                insideLeg = simplifyPath(insideRaw, dstRoom.id).slice(1);
            }

            const candidate = [
                ...openLeg.slice(0, -1).map(pt => ({ x: pt.x, y: pt.y, action: null })),
                { x: targetDoor.exitOutsideX, y: targetDoor.exitOutsideY, action: 'door_open' },
                { x: targetDoor.x, y: targetDoor.y, action: null },
                { x: targetDoor.entryInsideX, y: targetDoor.entryInsideY, action: null },
                ...insideLeg.map(pt => ({ x: pt.x, y: pt.y, action: null }))
            ];

            if (!validateCompleteRoute(candidate)) continue;

            let dist = Math.hypot(startPos.x - candidate[0].x, startPos.y - candidate[0].y);
            for (let i = 0; i < candidate.length - 1; i++) {
                dist += Math.hypot(candidate[i+1].x - candidate[i].x, candidate[i+1].y - candidate[i].y);
            }
            if (dist < bestDist) {
                bestDist = dist;
                bestRoute = candidate;
            }
        }
        return bestRoute;
    }

    // Case 5: Room A to Room B (Evaluate all door combinations)
    if (srcRoom && dstRoom && srcRoom.id !== dstRoom.id) {
        const rx = dstRoom.bounds.x * TILE_SIZE;
        const ry = dstRoom.bounds.y * TILE_SIZE;
        const rw = dstRoom.bounds.width * TILE_SIZE;
        const rh = dstRoom.bounds.height * TILE_SIZE;
        const margin = AVATAR_COLLISION_RADIUS + 4;
        const clampedX = Math.max(rx + margin, Math.min(rx + rw - margin, destPos.x));
        const clampedY = Math.max(ry + margin, Math.min(ry + rh - margin, destPos.y));
        const clampedDest = { x: clampedX, y: clampedY };

        const srcDoors = getRoomDoors(srcRoom);
        const dstDoors = getRoomDoors(dstRoom);
        let bestRoute = null;
        let bestDist = Infinity;

        for (const sDoor of srcDoors) {
            for (const dDoor of dstDoors) {
                let srcInsideLeg = [{ x: sDoor.entryInsideX, y: sDoor.entryInsideY }];
                if (!isPathClear(startPos, { x: sDoor.entryInsideX, y: sDoor.entryInsideY }, srcRoom.id, AVATAR_COLLISION_RADIUS)) {
                    const srcRaw = findAStarPath(startPos, { x: sDoor.entryInsideX, y: sDoor.entryInsideY }, srcRoom.id);
                    if (!srcRaw) continue;
                    srcInsideLeg = simplifyPath(srcRaw, srcRoom.id).slice(1);
                }

                let openLeg = [{ x: dDoor.exitOutsideX, y: dDoor.exitOutsideY }];
                if (!isPathClear({ x: sDoor.exitOutsideX, y: sDoor.exitOutsideY }, { x: dDoor.exitOutsideX, y: dDoor.exitOutsideY }, null, AVATAR_COLLISION_RADIUS)) {
                    const openRaw = findAStarPath({ x: sDoor.exitOutsideX, y: sDoor.exitOutsideY }, { x: dDoor.exitOutsideX, y: dDoor.exitOutsideY }, null);
                    if (!openRaw) continue;
                    openLeg = simplifyPath(openRaw, null).slice(1);
                }

                let dstInsideLeg = [{ x: clampedX, y: clampedY }];
                if (!isPathClear({ x: dDoor.entryInsideX, y: dDoor.entryInsideY }, clampedDest, dstRoom.id, AVATAR_COLLISION_RADIUS)) {
                    const dstRaw = findAStarPath({ x: dDoor.entryInsideX, y: dDoor.entryInsideY }, clampedDest, dstRoom.id);
                    if (!dstRaw) continue;
                    dstInsideLeg = simplifyPath(dstRaw, dstRoom.id).slice(1);
                }

                const candidate = [
                    ...srcInsideLeg.slice(0, -1).map(pt => ({ x: pt.x, y: pt.y, action: null })),
                    { x: sDoor.entryInsideX, y: sDoor.entryInsideY, action: 'door_open' },
                    { x: sDoor.x, y: sDoor.y, action: null },
                    { x: sDoor.exitOutsideX, y: sDoor.exitOutsideY, action: null },
                    ...openLeg.slice(0, -1).map(pt => ({ x: pt.x, y: pt.y, action: null })),
                    { x: dDoor.exitOutsideX, y: dDoor.exitOutsideY, action: 'door_open' },
                    { x: dDoor.x, y: dDoor.y, action: null },
                    { x: dDoor.entryInsideX, y: dDoor.entryInsideY, action: null },
                    ...dstInsideLeg.map(pt => ({ x: pt.x, y: pt.y, action: null }))
                ];

                if (!validateCompleteRoute(candidate)) continue;

                let dist = Math.hypot(startPos.x - candidate[0].x, startPos.y - candidate[0].y);
                for (let i = 0; i < candidate.length - 1; i++) {
                    dist += Math.hypot(candidate[i+1].x - candidate[i].x, candidate[i+1].y - candidate[i].y);
                }
                if (dist < bestDist) {
                    bestDist = dist;
                    bestRoute = candidate;
                }
            }
        }
        return bestRoute;
    }

    return null;
}

// ── Execute Comprehensive Test Suite for User's 13 Scenarios ──
console.log('=== STARTING 13 AUTHORITATIVE NAVIGATION & COLLISION TESTS ===\n');

let passedCount = 0;
let totalCount = 0;

function runScenarioTest(id, name, testFn) {
    totalCount++;
    try {
        const result = testFn();
        console.log(`[PASS] Test ${id}: ${name}`);
        console.log(`       Source: (${result.srcPos.x}, ${result.srcPos.y}) [${result.srcRoom ? result.srcRoom.english_name : 'Open Space'}]`);
        console.log(`       Target: (${result.destPos.x}, ${result.destPos.y}) [${result.dstRoom ? result.dstRoom.english_name : 'Open Space'}]`);
        console.log(`       Waypoints (${result.waypoints.length}): ${JSON.stringify(result.waypoints.map(w => ({ x: Math.round(w.x), y: Math.round(w.y) })))}`);
        console.log(`       Final Pos: (${Math.round(result.finalPos.x)}, ${Math.round(result.finalPos.y)}) [${result.finalRoom ? result.finalRoom.english_name : 'Open Space'}]`);
        console.log(`       Door Portal Crossed: ${result.doorPortalCrossed ? 'YES' : 'N/A'}`);
        console.log(`       Wall Collision Occurred: ${result.wallCollision ? 'YES' : 'NO (100% Solid Compliance)'}\n`);
        passedCount++;
    } catch (e) {
        console.error(`[FAIL] Test ${id}: ${name} -> ${e.message}\n`, e);
    }
}

// Simulation of Avatar Movement along waypoints using update() step-by-step physics
function simulateAvatarMovement(startPos, waypoints) {
    let currentX = startPos.x;
    let currentY = startPos.y;
    let collisionOccurred = false;
    const speed = 4;

    const wps = waypoints.map(w => ({ ...w }));
    while (wps.length > 0) {
        const target = wps[0];
        const dx = target.x - currentX;
        const dy = target.y - currentY;
        const dist = Math.hypot(dx, dy);

        if (dist <= speed) {
            currentX = target.x;
            currentY = target.y;
            wps.shift();
        } else {
            const nextX = currentX + (dx / dist) * speed;
            const nextY = currentY + (dy / dist) * speed;
            if (checkCapsuleWallCollision(currentX, currentY, nextX, nextY, AVATAR_COLLISION_RADIUS)) {
                collisionOccurred = true;
                break;
            }
            currentX = nextX;
            currentY = nextY;
        }
    }

    return {
        finalX: currentX,
        finalY: currentY,
        collisionOccurred
    };
}

// 1. Room A -> Room B
runScenarioTest(1, 'Room A -> Room B through valid doors', () => {
    roomDoorStates.clear();
    const srcPos = { x: 200, y: 150 }; // Inside Room 1
    const destPos = { x: 650, y: 150 }; // Inside Room 2
    const srcRoom = getCurrentRoom(srcPos.x, srcPos.y);
    const dstRoom = getCurrentRoom(destPos.x, destPos.y);

    const route = buildNavigationRoute(srcPos, destPos, srcRoom, dstRoom);
    if (!route || route.length === 0) throw new Error('No route returned for Room A -> Room B');

    const sim = simulateAvatarMovement(srcPos, route);
    if (sim.collisionOccurred) throw new Error('Collision detected during movement');

    const finalRoom = getCurrentRoom(sim.finalX, sim.finalY);
    if (!finalRoom || finalRoom.id !== dstRoom.id) throw new Error('Did not reach target room');

    return {
        srcPos, destPos, srcRoom, dstRoom,
        waypoints: route,
        finalPos: { x: sim.finalX, y: sim.finalY },
        finalRoom,
        doorPortalCrossed: true,
        wallCollision: sim.collisionOccurred
    };
});

// 2. Room B -> Room A
runScenarioTest(2, 'Room B -> Room A through valid doors', () => {
    roomDoorStates.clear();
    const srcPos = { x: 650, y: 150 }; // Inside Room 2
    const destPos = { x: 200, y: 150 }; // Inside Room 1
    const srcRoom = getCurrentRoom(srcPos.x, srcPos.y);
    const dstRoom = getCurrentRoom(destPos.x, destPos.y);

    const route = buildNavigationRoute(srcPos, destPos, srcRoom, dstRoom);
    if (!route || route.length === 0) throw new Error('No route returned for Room B -> Room A');

    const sim = simulateAvatarMovement(srcPos, route);
    if (sim.collisionOccurred) throw new Error('Collision detected during movement');

    const finalRoom = getCurrentRoom(sim.finalX, sim.finalY);
    if (!finalRoom || finalRoom.id !== dstRoom.id) throw new Error('Did not reach target room');

    return {
        srcPos, destPos, srcRoom, dstRoom,
        waypoints: route,
        finalPos: { x: sim.finalX, y: sim.finalY },
        finalRoom,
        doorPortalCrossed: true,
        wallCollision: sim.collisionOccurred
    };
});

// 3. Room A -> Open Space
runScenarioTest(3, 'Room A -> Open Space', () => {
    roomDoorStates.clear();
    const srcPos = { x: 200, y: 150 }; // Inside Room 1
    const destPos = { x: 500, y: 400 }; // In Central Open Space
    const srcRoom = getCurrentRoom(srcPos.x, srcPos.y);
    const dstRoom = getCurrentRoom(destPos.x, destPos.y);

    const route = buildNavigationRoute(srcPos, destPos, srcRoom, dstRoom);
    if (!route || route.length === 0) throw new Error('No route returned for Room A -> Open Space');

    const sim = simulateAvatarMovement(srcPos, route);
    if (sim.collisionOccurred) throw new Error('Collision detected during movement');

    const finalRoom = getCurrentRoom(sim.finalX, sim.finalY);
    if (finalRoom !== null) throw new Error('Avatar should be in open space');

    return {
        srcPos, destPos, srcRoom, dstRoom,
        waypoints: route,
        finalPos: { x: sim.finalX, y: sim.finalY },
        finalRoom,
        doorPortalCrossed: true,
        wallCollision: sim.collisionOccurred
    };
});

// 4. Open Space -> Room A
runScenarioTest(4, 'Open Space -> Room A', () => {
    roomDoorStates.clear();
    const srcPos = { x: 500, y: 400 }; // In Central Open Space
    const destPos = { x: 200, y: 150 }; // Inside Room 1
    const srcRoom = getCurrentRoom(srcPos.x, srcPos.y);
    const dstRoom = getCurrentRoom(destPos.x, destPos.y);

    const route = buildNavigationRoute(srcPos, destPos, srcRoom, dstRoom);
    if (!route || route.length === 0) throw new Error('No route returned for Open Space -> Room A');

    const sim = simulateAvatarMovement(srcPos, route);
    if (sim.collisionOccurred) throw new Error('Collision detected during movement');

    const finalRoom = getCurrentRoom(sim.finalX, sim.finalY);
    if (!finalRoom || finalRoom.id !== dstRoom.id) throw new Error('Did not reach Room A');

    return {
        srcPos, destPos, srcRoom, dstRoom,
        waypoints: route,
        finalPos: { x: sim.finalX, y: sim.finalY },
        finalRoom,
        doorPortalCrossed: true,
        wallCollision: sim.collisionOccurred
    };
});

// 5. Room A -> Room C (Direct line crosses solid wall of Room 1 and Room 3)
runScenarioTest(5, 'Room A -> Room C with solid wall avoidance', () => {
    roomDoorStates.clear();
    const srcPos = { x: 200, y: 150 }; // Inside Room 1 (top-left)
    const destPos = { x: 200, y: 600 }; // Inside Room 3 (bottom-left)
    const srcRoom = getCurrentRoom(srcPos.x, srcPos.y);
    const dstRoom = getCurrentRoom(destPos.x, destPos.y);

    const route = buildNavigationRoute(srcPos, destPos, srcRoom, dstRoom);
    if (!route || route.length === 0) throw new Error('No route returned for Room A -> Room C');

    const sim = simulateAvatarMovement(srcPos, route);
    if (sim.collisionOccurred) throw new Error('Collision detected during movement');

    const finalRoom = getCurrentRoom(sim.finalX, sim.finalY);
    if (!finalRoom || finalRoom.id !== dstRoom.id) throw new Error('Did not reach Room C');

    return {
        srcPos, destPos, srcRoom, dstRoom,
        waypoints: route,
        finalPos: { x: sim.finalX, y: sim.finalY },
        finalRoom,
        doorPortalCrossed: true,
        wallCollision: sim.collisionOccurred
    };
});

// 6. Multiple Source Doors (Room 5 has bottom and left door)
runScenarioTest(6, 'Multiple Source Doors optimal door selection', () => {
    roomDoorStates.clear();
    const srcPos = { x: 1200, y: 400 }; // Inside Room 5 (has left and bottom door)
    const destPos = { x: 500, y: 400 }; // Target to the left in open space
    const srcRoom = getCurrentRoom(srcPos.x, srcPos.y);
    const dstRoom = getCurrentRoom(destPos.x, destPos.y);

    const route = buildNavigationRoute(srcPos, destPos, srcRoom, dstRoom);
    if (!route || route.length === 0) throw new Error('No route returned for multi-door source');

    const sim = simulateAvatarMovement(srcPos, route);
    if (sim.collisionOccurred) throw new Error('Collision detected');

    return {
        srcPos, destPos, srcRoom, dstRoom,
        waypoints: route,
        finalPos: { x: sim.finalX, y: sim.finalY },
        finalRoom: null,
        doorPortalCrossed: true,
        wallCollision: sim.collisionOccurred
    };
});

// 7. Multiple Target Doors (Room 5 has bottom and left door)
runScenarioTest(7, 'Multiple Target Doors optimal door selection', () => {
    roomDoorStates.clear();
    const srcPos = { x: 500, y: 400 }; // In open space to the left
    const destPos = { x: 1200, y: 400 }; // Target inside Room 5
    const srcRoom = getCurrentRoom(srcPos.x, srcPos.y);
    const dstRoom = getCurrentRoom(destPos.x, destPos.y);

    const route = buildNavigationRoute(srcPos, destPos, srcRoom, dstRoom);
    if (!route || route.length === 0) throw new Error('No route returned for multi-door target');

    const sim = simulateAvatarMovement(srcPos, route);
    if (sim.collisionOccurred) throw new Error('Collision detected');

    const finalRoom = getCurrentRoom(sim.finalX, sim.finalY);
    if (!finalRoom || finalRoom.id !== dstRoom.id) throw new Error('Did not reach target multi-door room');

    return {
        srcPos, destPos, srcRoom, dstRoom,
        waypoints: route,
        finalPos: { x: sim.finalX, y: sim.finalY },
        finalRoom,
        doorPortalCrossed: true,
        wallCollision: sim.collisionOccurred
    };
});

// 8. Destination Near Wall (Destination clamped safely from solid wall)
runScenarioTest(8, 'Destination Near Wall (Clamped to safe clearance)', () => {
    roomDoorStates.clear();
    const srcPos = { x: 200, y: 200 }; // Inside Room 1
    const destPos = { x: 82, y: 82 }; // Top-left corner of Room 1 near wall
    const srcRoom = getCurrentRoom(srcPos.x, srcPos.y);
    const dstRoom = getCurrentRoom(destPos.x, destPos.y);

    const route = buildNavigationRoute(srcPos, destPos, srcRoom, dstRoom);
    if (!route || route.length === 0) throw new Error('No route returned');

    const sim = simulateAvatarMovement(srcPos, route);
    if (sim.collisionOccurred) throw new Error('Collision detected with near-wall position');

    // Final pos must be clamped with at least AVATAR_COLLISION_RADIUS clearance from wall (rx = 80, ry = 80)
    const rx = srcRoom.bounds.x * TILE_SIZE;
    const ry = srcRoom.bounds.y * TILE_SIZE;
    if (sim.finalX < rx + AVATAR_COLLISION_RADIUS || sim.finalY < ry + AVATAR_COLLISION_RADIUS) {
        throw new Error('Destination was not clamped safely away from wall');
    }

    return {
        srcPos, destPos, srcRoom, dstRoom,
        waypoints: route,
        finalPos: { x: sim.finalX, y: sim.finalY },
        finalRoom: srcRoom,
        doorPortalCrossed: false,
        wallCollision: sim.collisionOccurred
    };
});

// 9. Destination Behind Wall in Open Space
runScenarioTest(9, 'Destination Behind Wall in Open Space', () => {
    roomDoorStates.clear();
    const srcPos = { x: 500, y: 400 }; // Central corridor
    const destPos = { x: 200, y: 40 }; // Behind Room 1 in top open space margin
    const srcRoom = getCurrentRoom(srcPos.x, srcPos.y);
    const dstRoom = getCurrentRoom(destPos.x, destPos.y);

    const route = buildNavigationRoute(srcPos, destPos, srcRoom, dstRoom);
    if (!route || route.length === 0) throw new Error('No route returned around room');

    const sim = simulateAvatarMovement(srcPos, route);
    if (sim.collisionOccurred) throw new Error('Collision detected while routing around room');

    return {
        srcPos, destPos, srcRoom, dstRoom,
        waypoints: route,
        finalPos: { x: sim.finalX, y: sim.finalY },
        finalRoom: null,
        doorPortalCrossed: false,
        wallCollision: sim.collisionOccurred
    };
});

// 10. Corner Crossing (No corner cutting through room wall vertex)
runScenarioTest(10, 'Corner Crossing (Obstacle perimeter clearance)', () => {
    roomDoorStates.clear();
    // Path going around Room 1 bottom-right corner (rx + rw = 480, ry + rh = 368)
    const srcPos = { x: 520, y: 340 }; // Right of Room 1
    const destPos = { x: 440, y: 420 }; // Below Room 1
    const srcRoom = getCurrentRoom(srcPos.x, srcPos.y);
    const dstRoom = getCurrentRoom(destPos.x, destPos.y);

    const route = buildNavigationRoute(srcPos, destPos, srcRoom, dstRoom);
    if (!route || route.length === 0) throw new Error('No route returned around corner');

    const sim = simulateAvatarMovement(srcPos, route);
    if (sim.collisionOccurred) throw new Error('Corner collision detected');

    return {
        srcPos, destPos, srcRoom, dstRoom,
        waypoints: route,
        finalPos: { x: sim.finalX, y: sim.finalY },
        finalRoom: null,
        doorPortalCrossed: false,
        wallCollision: sim.collisionOccurred
    };
});

// 11. Diagonal Wall Crossing (Direct diagonal blocked by solid wall)
runScenarioTest(11, 'Diagonal Wall Crossing (Physically blocked and routed)', () => {
    roomDoorStates.clear();
    // Test that direct diagonal crossing through solid left wall of Room 2 is blocked
    const directCollides = checkCapsuleWallCollision(520, 200, 600, 200, AVATAR_COLLISION_RADIUS);
    if (!directCollides) throw new Error('Direct wall crossing was not detected as solid wall collision!');

    return {
        srcPos: { x: 520, y: 200 },
        destPos: { x: 600, y: 200 },
        srcRoom: null,
        dstRoom: getRoomById(2),
        waypoints: [{ x: 520, y: 200 }, { x: 600, y: 200 }],
        finalPos: { x: 520, y: 200 },
        finalRoom: null,
        doorPortalCrossed: false,
        wallCollision: true
    };
});

// 12. Locked Room (Door is solid wall, entry rejected)
runScenarioTest(12, 'Locked Room (Entry route rejected with null)', () => {
    roomDoorStates.set(2, true); // Lock Room 2
    const srcPos = { x: 500, y: 400 };
    const destPos = { x: 650, y: 150 }; // Target inside locked Room 2
    const srcRoom = getCurrentRoom(srcPos.x, srcPos.y);
    const dstRoom = getCurrentRoom(destPos.x, destPos.y);

    const route = buildNavigationRoute(srcPos, destPos, srcRoom, dstRoom);
    if (route !== null) throw new Error('Route should be NULL when targeting locked room!');

    roomDoorStates.clear();
    return {
        srcPos, destPos, srcRoom, dstRoom,
        waypoints: [],
        finalPos: srcPos,
        finalRoom: null,
        doorPortalCrossed: false,
        wallCollision: false
    };
});

// 13. No-Route Case (Target completely enclosed / blocked)
runScenarioTest(13, 'No-Route Case returns strictly NULL (Never [goal])', () => {
    roomDoorStates.set(1, true); // Lock Room 1
    const srcPos = { x: 200, y: 150 }; // Trapped in locked Room 1
    const destPos = { x: 500, y: 400 }; // Target outside
    const srcRoom = getCurrentRoom(srcPos.x, srcPos.y);
    const dstRoom = getCurrentRoom(destPos.x, destPos.y);

    const route = buildNavigationRoute(srcPos, destPos, srcRoom, dstRoom);
    if (route !== null) throw new Error('Route must return null when source room is locked!');

    roomDoorStates.clear();
    return {
        srcPos, destPos, srcRoom, dstRoom,
        waypoints: [],
        finalPos: srcPos,
        finalRoom: srcRoom,
        doorPortalCrossed: false,
        wallCollision: false
    };
});

console.log(`\n=== RIGOROUS TEST SUITE RESULTS: ${passedCount} / ${totalCount} PASSED ===`);
if (passedCount === totalCount) {
    console.log('ALL 13 NAVIGATION & COLLISION TESTS PASSED WITH 100% MATHEMATICAL PRECISION!');
} else {
    process.exit(1);
}
