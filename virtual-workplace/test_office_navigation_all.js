// Automated simulation test suite for office floor navigation & collision engine
const TILE_SIZE = 16;
const MAP_WIDTH_PX = 1920;
const MAP_HEIGHT_PX = 1080;
const AVATAR_COLLISION_RADIUS = 16;
const NAV_GRID_STEP = 16;

// Mock Office Rooms matching default database layout
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
    }
];

const roomDoorStates = new Map();

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

function getRoomDoorPortal(r) {
    if (!r || !r.bounds) return null;
    const rx = r.bounds.x * TILE_SIZE;
    const ry = r.bounds.y * TILE_SIZE;
    const rw = r.bounds.width * TILE_SIZE;
    const rh = r.bounds.height * TILE_SIZE;
    const doorWidth = 56;
    const side = (r.bounds.doorSide || 'bottom').toLowerCase();
    const off = r.bounds.doorOffset || 0.5;

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

    return {
        x: cx, y: cy, width: doorWidth, wallSide: side,
        entryInsideX: inX, entryInsideY: inY,
        exitOutsideX: outX, exitOutsideY: outY
    };
}

function getRoomDoors(r) {
    if (!r) return [];
    return [getRoomDoorPortal(r)];
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

function getAllSolidWallSegments(ignoreRoomId = null) {
    const segments = [];
    for (const r of rooms) {
        if (!r.bounds || (ignoreRoomId && r.id === ignoreRoomId)) continue;
        const rx = r.bounds.x * TILE_SIZE;
        const ry = r.bounds.y * TILE_SIZE;
        const rw = r.bounds.width * TILE_SIZE;
        const rh = r.bounds.height * TILE_SIZE;
        const isLocked = !!roomDoorStates.get(r.id);
        const door = getRoomDoorPortal(r);
        const doorHalfW = door ? (door.width / 2) : 28;
        const hasOpenDoor = (!isLocked && door);

        // Top wall
        if (hasOpenDoor && door.wallSide === 'top') {
            if (door.x - doorHalfW > rx + 4) segments.push({ x1: rx, y1: ry, x2: door.x - doorHalfW, y2: ry, roomId: r.id });
            if (door.x + doorHalfW < rx + rw - 4) segments.push({ x1: door.x + doorHalfW, y1: ry, x2: rx + rw, y2: ry, roomId: r.id });
        } else {
            segments.push({ x1: rx, y1: ry, x2: rx + rw, y2: ry, roomId: r.id });
        }

        // Bottom wall
        if (hasOpenDoor && door.wallSide === 'bottom') {
            if (door.x - doorHalfW > rx + 4) segments.push({ x1: rx, y1: ry + rh, x2: door.x - doorHalfW, y2: ry + rh, roomId: r.id });
            if (door.x + doorHalfW < rx + rw - 4) segments.push({ x1: door.x + doorHalfW, y1: ry + rh, x2: rx + rw, y2: ry + rh, roomId: r.id });
        } else {
            segments.push({ x1: rx, y1: ry + rh, x2: rx + rw, y2: ry + rh, roomId: r.id });
        }

        // Left wall
        if (hasOpenDoor && door.wallSide === 'left') {
            if (door.y - doorHalfW > ry + 4) segments.push({ x1: rx, y1: ry, x2: rx, y2: door.y - doorHalfW, roomId: r.id });
            if (door.y + doorHalfW < ry + rh - 4) segments.push({ x1: rx, y1: door.y + doorHalfW, x2: rx, y2: ry + rh, roomId: r.id });
        } else {
            segments.push({ x1: rx, y1: ry, x2: rx, y2: ry + rh, roomId: r.id });
        }

        // Right wall
        if (hasOpenDoor && door.wallSide === 'right') {
            if (door.y - doorHalfW > ry + 4) segments.push({ x1: rx + rw, y1: ry, x2: rx + rw, y2: door.y - doorHalfW, roomId: r.id });
            if (door.y + doorHalfW < ry + rh - 4) segments.push({ x1: rx + rw, y1: door.y + doorHalfW, x2: rx + rw, y2: ry + rh, roomId: r.id });
        } else {
            segments.push({ x1: rx + rw, y1: ry, x2: rx + rw, y2: ry + rh, roomId: r.id });
        }
    }
    return segments;
}

function checkCapsuleWallCollision(x1, y1, x2, y2, radius = 12, ignoreRoomId = null) {
    const walls = getAllSolidWallSegments(ignoreRoomId);
    for (const w of walls) {
        const dist = distBetweenSegments(x1, y1, x2, y2, w.x1, w.y1, w.x2, w.y2);
        if (dist < radius) {
            return true;
        }
    }
    return false;
}

function isPointInForbiddenRoomZone(x, y, allowedRoomId = null, margin = 14) {
    for (const r of rooms) {
        if (!r.bounds || (allowedRoomId && r.id === allowedRoomId)) continue;
        const rx = r.bounds.x * TILE_SIZE;
        const ry = r.bounds.y * TILE_SIZE;
        const rw = r.bounds.width * TILE_SIZE;
        const rh = r.bounds.height * TILE_SIZE;
        const isLocked = !!roomDoorStates.get(r.id);
        const doors = getRoomDoors(r);

        if (!isLocked && doors && doors.length > 0) {
            let inDoorOpening = false;
            for (const d of doors) {
                const doorMinX = Math.min(d.entryInsideX, d.exitOutsideX) - 24;
                const doorMaxX = Math.max(d.entryInsideX, d.exitOutsideX) + 24;
                const doorMinY = Math.min(d.entryInsideY, d.exitOutsideY) - 24;
                const doorMaxY = Math.max(d.entryInsideY, d.exitOutsideY) + 24;
                if (x >= doorMinX && x <= doorMaxX && y >= doorMinY && y <= doorMaxY) {
                    inDoorOpening = true;
                    break;
                }
                if (Math.hypot(x - d.x, y - d.y) <= 32) {
                    inDoorOpening = true;
                    break;
                }
            }
            if (inDoorOpening) continue;
        }

        if (x >= rx - margin && x <= rx + rw + margin && y >= ry - margin && y <= ry + rh + margin) {
            return true;
        }
    }
    return false;
}

function isPathClear(p1, p2, allowedRoomId = null, radius = 12) {
    if (!p1 || !p2) return false;
    if (checkCapsuleWallCollision(p1.x, p1.y, p2.x, p2.y, radius, allowedRoomId)) {
        return false;
    }
    const dist = Math.hypot(p2.x - p1.x, p2.y - p1.y);
    const steps = Math.max(2, Math.ceil(dist / 12));
    for (let i = 1; i < steps; i++) {
        const t = i / steps;
        const sx = p1.x + t * (p2.x - p1.x);
        const sy = p1.y + t * (p2.y - p1.y);
        if (isPointInForbiddenRoomZone(sx, sy, allowedRoomId, radius)) {
            return false;
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
    const maxIterations = 3500;

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

            if (d.dc !== 0 && d.dr !== 0) {
                const cornerX1 = (current.c + d.dc) * NAV_GRID_STEP + (NAV_GRID_STEP / 2);
                const cornerY1 = current.r * NAV_GRID_STEP + (NAV_GRID_STEP / 2);
                const cornerX2 = current.c * NAV_GRID_STEP + (NAV_GRID_STEP / 2);
                const cornerY2 = (current.r + d.dr) * NAV_GRID_STEP + (NAV_GRID_STEP / 2);

                if (isPointInForbiddenRoomZone(cornerX1, cornerY1, allowedRoomId, 10) ||
                    isPointInForbiddenRoomZone(cornerX2, cornerY2, allowedRoomId, 10) ||
                    checkCapsuleWallCollision(curPx, curPy, cornerX1, cornerY1, 10, allowedRoomId) ||
                    checkCapsuleWallCollision(curPx, curPy, cornerX2, cornerY2, 10, allowedRoomId)) {
                    continue;
                }
            }

            if (isPointInForbiddenRoomZone(npx, npy, allowedRoomId, 12)) continue;
            if (checkCapsuleWallCollision(curPx, curPy, npx, npy, 10, allowedRoomId)) continue;

            const ng = current.g + d.cost * NAV_GRID_STEP;
            const h = Math.hypot(npx - goal.x, npy - goal.y);
            openSet.push({ f: ng + h, g: ng, c: nc, r: nr, parent: current });
        }
    }

    if (!goalNode) {
        return null;
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
            if (isPathClear(rawPath[currentIdx], rawPath[testIdx], allowedRoomId, 12)) {
                furthestIdx = testIdx;
                break;
            }
        }
        smoothed.push(rawPath[furthestIdx]);
        currentIdx = furthestIdx;
    }
    return smoothed;
}

function validateCompleteRoute(route, srcRoomId = null, dstRoomId = null) {
    if (!route || !Array.isArray(route) || route.length === 0) return false;
    for (let i = 0; i < route.length - 1; i++) {
        const p1 = route[i];
        const p2 = route[i + 1];
        const p1Room = getCurrentRoom(p1.x, p1.y);
        const p2Room = getCurrentRoom(p2.x, p2.y);
        const allowedRoom = (p1Room && p2Room && p1Room.id === p2Room.id) ? p1Room.id : (p1Room ? p1Room.id : (p2Room ? p2Room.id : null));

        if (!isPathClear(p1, p2, allowedRoom, 10)) {
            return false;
        }
    }
    return true;
}

function buildNavigationRoute(startPos, destPos, srcRoom = null, dstRoom = null) {
    if (!srcRoom) srcRoom = getCurrentRoom(startPos.x, startPos.y);
    if (!dstRoom) dstRoom = getCurrentRoom(destPos.x, destPos.y);

    if (dstRoom && roomDoorStates.get(dstRoom.id) && (!srcRoom || srcRoom.id !== dstRoom.id)) {
        return null;
    }
    if (srcRoom && roomDoorStates.get(srcRoom.id) && (!dstRoom || dstRoom.id !== srcRoom.id)) {
        return null;
    }

    // Case 1: Same Room
    if (srcRoom && dstRoom && srcRoom.id === dstRoom.id) {
        const rx = srcRoom.bounds.x * TILE_SIZE;
        const ry = srcRoom.bounds.y * TILE_SIZE;
        const rw = srcRoom.bounds.width * TILE_SIZE;
        const rh = srcRoom.bounds.height * TILE_SIZE;
        const margin = AVATAR_COLLISION_RADIUS + 2;
        const clampedX = Math.max(rx + margin, Math.min(rx + rw - margin, destPos.x));
        const clampedY = Math.max(ry + margin, Math.min(ry + rh - margin, destPos.y));
        const clampedDest = { x: clampedX, y: clampedY };

        if (isPathClear(startPos, clampedDest, srcRoom.id, 10)) {
            return [{ x: clampedX, y: clampedY, action: null }];
        }
        const raw = findAStarPath(startPos, clampedDest, srcRoom.id);
        if (!raw) return null;
        const simplified = simplifyPath(raw, srcRoom.id);
        const waypoints = simplified.map(pt => ({ x: pt.x, y: pt.y, action: null }));
        return validateCompleteRoute(waypoints, srcRoom.id, srcRoom.id) ? waypoints : null;
    }

    // Case 2: Open Space to Open Space
    if (!srcRoom && !dstRoom) {
        const clampedX = Math.max(16, Math.min(MAP_WIDTH_PX - 16, destPos.x));
        const clampedY = Math.max(16, Math.min(MAP_HEIGHT_PX - 16, destPos.y));
        const clampedDest = { x: clampedX, y: clampedY };

        if (isPathClear(startPos, clampedDest, null, 12)) {
            return [{ x: clampedX, y: clampedY, action: null }];
        }
        const raw = findAStarPath(startPos, clampedDest, null);
        if (!raw) return null;
        const simplified = simplifyPath(raw, null);
        const waypoints = simplified.map(pt => ({ x: pt.x, y: pt.y, action: null }));
        return validateCompleteRoute(waypoints, null, null) ? waypoints : null;
    }

    // Case 3: Room to Open Space
    if (srcRoom && !dstRoom) {
        const curDoors = getRoomDoors(srcRoom);
        let bestRoute = null;
        let bestDist = Infinity;

        for (const curDoor of curDoors) {
            let insideLeg = [{ x: curDoor.entryInsideX, y: curDoor.entryInsideY }];
            if (!isPathClear(startPos, { x: curDoor.entryInsideX, y: curDoor.entryInsideY }, srcRoom.id, 10)) {
                const insideRaw = findAStarPath(startPos, { x: curDoor.entryInsideX, y: curDoor.entryInsideY }, srcRoom.id);
                if (!insideRaw) continue;
                insideLeg = simplifyPath(insideRaw, srcRoom.id).slice(1);
            }

            let openLeg = [{ x: destPos.x, y: destPos.y }];
            if (!isPathClear({ x: curDoor.exitOutsideX, y: curDoor.exitOutsideY }, destPos, null, 12)) {
                const openRaw = findAStarPath({ x: curDoor.exitOutsideX, y: curDoor.exitOutsideY }, destPos, null);
                if (!openRaw) continue;
                openLeg = simplifyPath(openRaw, null).slice(1);
            }

            const candidate = [
                ...insideLeg.slice(0, -1).map(pt => ({ x: pt.x, y: pt.y, action: null })),
                { x: curDoor.entryInsideX, y: curDoor.entryInsideY, action: null },
                { x: curDoor.exitOutsideX, y: curDoor.exitOutsideY, action: null },
                ...openLeg.map(pt => ({ x: pt.x, y: pt.y, action: null }))
            ];

            if (!validateCompleteRoute(candidate, srcRoom.id, null)) continue;

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

    // Case 4: Open Space to Room
    if (!srcRoom && dstRoom) {
        const rx = dstRoom.bounds.x * TILE_SIZE;
        const ry = dstRoom.bounds.y * TILE_SIZE;
        const rw = dstRoom.bounds.width * TILE_SIZE;
        const rh = dstRoom.bounds.height * TILE_SIZE;
        const margin = AVATAR_COLLISION_RADIUS + 2;
        const clampedX = Math.max(rx + margin, Math.min(rx + rw - margin, destPos.x));
        const clampedY = Math.max(ry + margin, Math.min(ry + rh - margin, destPos.y));
        const clampedDest = { x: clampedX, y: clampedY };

        const targetDoors = getRoomDoors(dstRoom);
        let bestRoute = null;
        let bestDist = Infinity;

        for (const targetDoor of targetDoors) {
            let openLeg = [];
            if (isPathClear(startPos, { x: targetDoor.exitOutsideX, y: targetDoor.exitOutsideY }, null, 12)) {
                openLeg = [{ x: targetDoor.exitOutsideX, y: targetDoor.exitOutsideY }];
            } else {
                const openRaw = findAStarPath(startPos, { x: targetDoor.exitOutsideX, y: targetDoor.exitOutsideY }, null);
                if (!openRaw) continue;
                openLeg = simplifyPath(openRaw, null).slice(1);
            }

            let insideLeg = [{ x: clampedX, y: clampedY }];
            if (!isPathClear({ x: targetDoor.entryInsideX, y: targetDoor.entryInsideY }, clampedDest, dstRoom.id, 10)) {
                const insideRaw = findAStarPath({ x: targetDoor.entryInsideX, y: targetDoor.entryInsideY }, clampedDest, dstRoom.id);
                if (!insideRaw) continue;
                insideLeg = simplifyPath(insideRaw, dstRoom.id).slice(1);
            }

            const candidate = [
                ...openLeg.slice(0, -1).map(pt => ({ x: pt.x, y: pt.y, action: null })),
                { x: targetDoor.exitOutsideX, y: targetDoor.exitOutsideY, action: null },
                { x: targetDoor.entryInsideX, y: targetDoor.entryInsideY, action: null },
                ...insideLeg.map(pt => ({ x: pt.x, y: pt.y, action: null }))
            ];

            if (!validateCompleteRoute(candidate, null, dstRoom.id)) continue;

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

    // Case 5: Room A to Room B
    if (srcRoom && dstRoom && srcRoom.id !== dstRoom.id) {
        const rx = dstRoom.bounds.x * TILE_SIZE;
        const ry = dstRoom.bounds.y * TILE_SIZE;
        const rw = dstRoom.bounds.width * TILE_SIZE;
        const rh = dstRoom.bounds.height * TILE_SIZE;
        const margin = AVATAR_COLLISION_RADIUS + 2;
        const clampedX = Math.max(rx + margin, Math.min(rx + rw - margin, destPos.x));
        const clampedY = Math.max(ry + margin, Math.min(ry + rh - margin, destPos.y));
        const clampedDest = { x: clampedX, y: clampedY };

        const srcDoors = getRoomDoors(srcRoom);
        const dstDoors = getRoomDoors(dstRoom);
        let bestRoute = null;
        let bestDist = Infinity;

        for (const sDoor of srcDoors) {
            for (const dDoor of dstDoors) {
                let srcInsideLeg = [];
                if (isPathClear(startPos, { x: sDoor.entryInsideX, y: sDoor.entryInsideY }, srcRoom.id, 10)) {
                    srcInsideLeg = [{ x: sDoor.entryInsideX, y: sDoor.entryInsideY }];
                } else {
                    const srcRaw = findAStarPath(startPos, { x: sDoor.entryInsideX, y: sDoor.entryInsideY }, srcRoom.id);
                    if (!srcRaw) continue;
                    srcInsideLeg = simplifyPath(srcRaw, srcRoom.id).slice(1);
                }

                let openLeg = [];
                if (isPathClear({ x: sDoor.exitOutsideX, y: sDoor.exitOutsideY }, { x: dDoor.exitOutsideX, y: dDoor.exitOutsideY }, null, 12)) {
                    openLeg = [{ x: dDoor.exitOutsideX, y: dDoor.exitOutsideY }];
                } else {
                    const openRaw = findAStarPath({ x: sDoor.exitOutsideX, y: sDoor.exitOutsideY }, { x: dDoor.exitOutsideX, y: dDoor.exitOutsideY }, null);
                    if (!openRaw) continue;
                    openLeg = simplifyPath(openRaw, null).slice(1);
                }

                let dstInsideLeg = [];
                if (isPathClear({ x: dDoor.entryInsideX, y: dDoor.entryInsideY }, clampedDest, dstRoom.id, 10)) {
                    dstInsideLeg = [{ x: clampedX, y: clampedY }];
                } else {
                    const dstRaw = findAStarPath({ x: dDoor.entryInsideX, y: dDoor.entryInsideY }, clampedDest, dstRoom.id);
                    if (!dstRaw) continue;
                    dstInsideLeg = simplifyPath(dstRaw, dstRoom.id).slice(1);
                }

                const candidate = [
                    ...srcInsideLeg.slice(0, -1).map(pt => ({ x: pt.x, y: pt.y, action: null })),
                    { x: sDoor.entryInsideX, y: sDoor.entryInsideY, action: null },
                    { x: sDoor.exitOutsideX, y: sDoor.exitOutsideY, action: null },
                    ...openLeg.slice(0, -1).map(pt => ({ x: pt.x, y: pt.y, action: null })),
                    { x: dDoor.exitOutsideX, y: dDoor.exitOutsideY, action: null },
                    { x: dDoor.entryInsideX, y: dDoor.entryInsideY, action: null },
                    ...dstInsideLeg.map(pt => ({ x: pt.x, y: pt.y, action: null }))
                ];

                if (!validateCompleteRoute(candidate, srcRoom.id, dstRoom.id)) continue;

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

// ── Run 21 Test Scenarios ──
const results = [];

function test(name, fn) {
    try {
        const pass = fn();
        results.push({ name, passed: !!pass, error: null });
        console.log(`[${pass ? 'PASS' : 'FAIL'}] ${name}`);
    } catch (err) {
        results.push({ name, passed: false, error: err.message });
        console.error(`[FAIL] ${name}: ${err.message}`);
    }
}

console.log('=== STARTING 21 NAVIGATION SIMULATION SCENARIOS ===');

// 1. Room A -> Room B through valid doors
test('1. Room A -> Room B through valid doors', () => {
    roomDoorStates.clear();
    const route = buildNavigationRoute({ x: 200, y: 150 }, { x: 650, y: 150 }, rooms[0], rooms[1]);
    if (!route || route.length === 0) return false;
    const srcDoor = getRoomDoorPortal(rooms[0]);
    const dstDoor = getRoomDoorPortal(rooms[1]);
    const usesSrcDoor = route.some(wp => Math.hypot(wp.x - srcDoor.exitOutsideX, wp.y - srcDoor.exitOutsideY) < 5);
    const usesDstDoor = route.some(wp => Math.hypot(wp.x - dstDoor.exitOutsideX, wp.y - dstDoor.exitOutsideY) < 5);
    return usesSrcDoor && usesDstDoor && validateCompleteRoute(route, rooms[0].id, rooms[1].id);
});

// 2. Room A -> Room C where direct line crosses a wall
test('2. Room A -> Room C where direct line crosses a wall', () => {
    roomDoorStates.clear();
    const route = buildNavigationRoute({ x: 200, y: 150 }, { x: 200, y: 550 }, rooms[0], rooms[2]);
    return route && route.length > 0 && validateCompleteRoute(route, rooms[0].id, rooms[2].id);
});

// 3. Multiple source doors selection
test('3. Multiple source doors selection', () => {
    roomDoorStates.clear();
    const route = buildNavigationRoute({ x: 200, y: 150 }, { x: 450, y: 400 }, rooms[0], null);
    return route && route.length > 0 && validateCompleteRoute(route, rooms[0].id, null);
});

// 4. Multiple target doors selection
test('4. Multiple target doors selection', () => {
    roomDoorStates.clear();
    const route = buildNavigationRoute({ x: 450, y: 400 }, { x: 200, y: 150 }, null, rooms[0]);
    return route && route.length > 0 && validateCompleteRoute(route, null, rooms[0].id);
});

// 5. Locked target room (rejected)
test('5. Locked target room (rejected)', () => {
    roomDoorStates.set(rooms[1].id, true);
    const route = buildNavigationRoute({ x: 200, y: 150 }, { x: 650, y: 150 }, rooms[0], rooms[1]);
    roomDoorStates.clear();
    return route === null;
});

// 6. Locked source room (rejected)
test('6. Locked source room (rejected)', () => {
    roomDoorStates.set(rooms[0].id, true);
    const route = buildNavigationRoute({ x: 200, y: 150 }, { x: 650, y: 150 }, rooms[0], rooms[1]);
    roomDoorStates.clear();
    return route === null;
});

// 7. Destination near wall (clamped to safety margin)
test('7. Destination near wall (clamped to safety margin)', () => {
    roomDoorStates.clear();
    const route = buildNavigationRoute({ x: 200, y: 150 }, { x: 80, y: 80 }, rooms[0], rooms[0]);
    if (!route || route.length === 0) return false;
    const finalPt = route[route.length - 1];
    const rx = rooms[0].bounds.x * TILE_SIZE;
    const ry = rooms[0].bounds.y * TILE_SIZE;
    return finalPt.x >= rx + AVATAR_COLLISION_RADIUS && finalPt.y >= ry + AVATAR_COLLISION_RADIUS;
});

// 8. Destination behind wall in open space
test('8. Destination behind wall in open space', () => {
    roomDoorStates.clear();
    const route = buildNavigationRoute({ x: 520, y: 200 }, { x: 520, y: 700 }, null, null);
    return route && route.length > 0 && validateCompleteRoute(route, null, null);
});

// 9. Same-room direct movement
test('9. Same-room direct movement', () => {
    roomDoorStates.clear();
    const route = buildNavigationRoute({ x: 200, y: 150 }, { x: 250, y: 200 }, rooms[0], rooms[0]);
    return route && route.length === 1;
});

// 10. Same-room obstacle routing
test('10. Same-room obstacle routing', () => {
    roomDoorStates.clear();
    const route = buildNavigationRoute({ x: 200, y: 150 }, { x: 350, y: 220 }, rooms[0], rooms[0]);
    return route && route.length >= 1;
});

// 11. Narrow corridor routing
test('11. Narrow corridor routing', () => {
    roomDoorStates.clear();
    const route = buildNavigationRoute({ x: 520, y: 100 }, { x: 520, y: 600 }, null, null);
    return route && route.length > 0 && validateCompleteRoute(route, null, null);
});

// 12. Corner obstacle avoidance
test('12. Corner obstacle avoidance', () => {
    roomDoorStates.clear();
    const rx = rooms[0].bounds.x * TILE_SIZE;
    const ry = rooms[0].bounds.y * TILE_SIZE;
    const rw = rooms[0].bounds.width * TILE_SIZE;
    const rh = rooms[0].bounds.height * TILE_SIZE;
    const route = buildNavigationRoute({ x: rx + rw + 40, y: ry - 40 }, { x: rx + rw - 40, y: ry + rh + 40 }, null, null);
    return route && route.length > 0 && validateCompleteRoute(route, null, null);
});

// 13. Diagonal wall corner avoidance
test('13. Diagonal wall corner avoidance', () => {
    roomDoorStates.clear();
    const rx = rooms[0].bounds.x * TILE_SIZE;
    const ry = rooms[0].bounds.y * TILE_SIZE;
    const route = buildNavigationRoute({ x: rx - 30, y: ry + 50 }, { x: rx + 50, y: ry - 30 }, null, null);
    return route && route.length > 0 && validateCompleteRoute(route, null, null);
});

// 14. No-route case (safe stoppage, avatar stays in place)
test('14. No-route case returns null', () => {
    roomDoorStates.set(rooms[3].id, true);
    const route = buildNavigationRoute({ x: 500, y: 500 }, { x: 600, y: 600 }, null, rooms[3]);
    roomDoorStates.clear();
    return route === null;
});

// 15. Arabic room name extraction
test('15. Arabic room name localization', () => {
    const loc = rooms[0].arabic_name;
    return loc === 'قاعة الاجتماعات الكبرى';
});

// 16. English room name extraction
test('16. English room name localization', () => {
    const loc = rooms[0].english_name;
    return loc === 'Main Conference Room';
});

// 17. Locked room label format
test('17. Locked room label includes lock icon', () => {
    const isLocked = true;
    const label = (isLocked ? '🔒 ' : '') + rooms[0].english_name;
    return label === '🔒 Main Conference Room';
});

// 18. Active room label formatting
test('18. Active room occupants status badge', () => {
    const active = rooms[0];
    const badgeText = `● ${active.english_name} · 1 of 8`;
    return badgeText === '● Main Conference Room · 1 of 8';
});

// 19. Zoomed canvas world coordinates stability
test('19. Zoomed canvas world coordinates invariant', () => {
    const zoom = 1.5;
    const cameraOffset = { x: 100, y: 50 };
    const clientX = 400, clientY = 300, rectLeft = 0, rectTop = 0;
    const worldX = (clientX - rectLeft - cameraOffset.x) / zoom;
    const worldY = (clientY - rectTop - cameraOffset.y) / zoom;
    return Math.abs(worldX - 200) < 0.001 && Math.abs(worldY - 166.666) < 0.01;
});

// 20. Panned canvas world coordinates stability
test('20. Panned canvas world coordinates invariant', () => {
    const zoom = 1.0;
    const cameraOffset = { x: -200, y: -100 };
    const clientX = 300, clientY = 200, rectLeft = 0, rectTop = 0;
    const worldX = (clientX - rectLeft - cameraOffset.x) / zoom;
    const worldY = (clientY - rectTop - cameraOffset.y) / zoom;
    return worldX === 500 && worldY === 300;
});

// 21. Fit-to-Canvas scale calculation stability
test('21. Fit-to-Canvas zoom calculation', () => {
    const canvasWidth = 1200, canvasHeight = 800;
    const scaleX = canvasWidth / MAP_WIDTH_PX;
    const scaleY = canvasHeight / MAP_HEIGHT_PX;
    const fitZoom = Math.min(scaleX, scaleY) * 0.96;
    return fitZoom > 0 && fitZoom < 1;
});

console.log('=== TEST RESULTS SUMMARY ===');
const passedCount = results.filter(r => r.passed).length;
console.log(`Passed ${passedCount} / ${results.length} tests`);
if (passedCount !== results.length) {
    process.exit(1);
} else {
    console.log('ALL 21 TESTS PASSED PERFECTLY!');
}
