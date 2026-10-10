/**
 * Where a room's door is. Shared by the office (movement, routing) and the map
 * editor (drawing, the door picker), so the door you place is the door you walk
 * through.
 *
 * A room may set bounds.doorSide (top/bottom/left/right or 'auto') and
 * bounds.doorOffset (0–1 along that wall). A chosen side is used unless its
 * door would open off the map or into another room; then the automatic choice
 * is used and the result says `redirected: true`, so the editor can warn.
 *
 * Coordinates are in map pixels; room bounds are in tiles.
 */
(function (global) {
    const DOOR_WIDTH = 56;
    const STEP_IN = 26;
    const STEP_OUT = 32;
    const OUTER_MARGIN = 40; // automatic doors keep a walkable strip to the map edge
    const AUTO_OFFSETS = [0.5, 0.75, 0.82, 0.25, 0.18, 0.65, 0.35];
    const SIDES = ['bottom', 'top', 'right', 'left'];

    function rect(room, tile) {
        return {
            x: room.bounds.x * tile,
            y: room.bounds.y * tile,
            w: room.bounds.width * tile,
            h: room.bounds.height * tile,
        };
    }

    function candidate(r, side, off) {
        let x, y, inX, inY, outX, outY;
        if (side === 'bottom') { x = r.x + r.w * off; y = r.y + r.h; inX = x; inY = y - STEP_IN; outX = x; outY = y + STEP_OUT; }
        else if (side === 'top') { x = r.x + r.w * off; y = r.y; inX = x; inY = y + STEP_IN; outX = x; outY = y - STEP_OUT; }
        else if (side === 'right') { x = r.x + r.w; y = r.y + r.h * off; inX = x - STEP_IN; inY = y; outX = x + STEP_OUT; outY = y; }
        else { x = r.x; y = r.y + r.h * off; inX = x + STEP_IN; inY = y; outX = x - STEP_OUT; outY = y; }
        return { side, offset: off, x, y, entryInsideX: inX, entryInsideY: inY, exitOutsideX: outX, exitOutsideY: outY };
    }

    function isOther(room, other) {
        return other !== room && !(room.id && other.id === room.id) && other.bounds;
    }

    /** Why this door can't be used, or null when it can. */
    function blockReason(room, cand, rooms, opts, automatic) {
        const { mapW, mapH, tile } = opts;
        if (cand.exitOutsideX < 0 || cand.exitOutsideY < 0 || cand.exitOutsideX > mapW || cand.exitOutsideY > mapH) return 'off-map';
        if (automatic && (cand.exitOutsideX < OUTER_MARGIN || cand.exitOutsideX > mapW - OUTER_MARGIN
            || cand.exitOutsideY < OUTER_MARGIN || cand.exitOutsideY > mapH - OUTER_MARGIN)) return 'map-edge';
        for (const other of rooms) {
            if (!isOther(room, other)) continue;
            const o = rect(other, tile);
            if (cand.exitOutsideX >= o.x - 6 && cand.exitOutsideX <= o.x + o.w + 6 && cand.exitOutsideY >= o.y - 6 && cand.exitOutsideY <= o.y + o.h + 6) return 'other-room';
            if (cand.x >= o.x - 4 && cand.x <= o.x + o.w + 4 && cand.y >= o.y - 4 && cand.y <= o.y + o.h + 4) return 'shared-wall';
        }
        return null;
    }

    function clearance(cand, room, rooms, tile) {
        let best = 99999;
        for (const other of rooms) {
            if (!isOther(room, other)) continue;
            const o = rect(other, tile);
            const dx = Math.max(o.x - cand.exitOutsideX, 0, cand.exitOutsideX - (o.x + o.w));
            const dy = Math.max(o.y - cand.exitOutsideY, 0, cand.exitOutsideY - (o.y + o.h));
            best = Math.min(best, Math.hypot(dx, dy));
        }
        return best;
    }

    function portal(cand, redirected, reason) {
        return {
            x: cand.x,
            y: cand.y,
            width: DOOR_WIDTH,
            height: 20,
            wallSide: cand.side,
            offset: cand.offset,
            entryInsideX: cand.entryInsideX,
            entryInsideY: cand.entryInsideY,
            exitOutsideX: cand.exitOutsideX,
            exitOutsideY: cand.exitOutsideY,
            redirected,
            reason: reason || null,
        };
    }

    /** The automatic door: faces the open middle of the map, away from other rooms. */
    function automatic(room, rooms, opts) {
        const r = rect(room, opts.tile);
        const centre = { x: opts.mapW / 2, y: opts.mapH / 2 };
        let best = null, bestScore = -Infinity;
        for (const side of SIDES) {
            for (const off of AUTO_OFFSETS) {
                const c = candidate(r, side, off);
                if (blockReason(room, c, rooms, opts, true)) continue;
                const score = (1200 - Math.hypot(c.exitOutsideX - centre.x, c.exitOutsideY - centre.y))
                    + clearance(c, room, rooms, opts.tile) * 4 + (off === 0.5 ? 25 : 0);
                if (score > bestScore) { bestScore = score; best = c; }
            }
        }
        if (best) return best;
        // Nothing is ideal: prefer any door that does not lead into another room, nearest the middle.
        const all = [];
        for (const side of SIDES) for (const off of AUTO_OFFSETS) all.push(candidate(r, side, off));
        const usable = all.filter(c => !['other-room', 'shared-wall'].includes(blockReason(room, c, rooms, opts, false)));
        const pool = usable.length ? usable : all;
        pool.sort((a, b) => Math.hypot(a.exitOutsideX - centre.x, a.exitOutsideY - centre.y) - Math.hypot(b.exitOutsideX - centre.x, b.exitOutsideY - centre.y));
        return pool[0];
    }

    /**
     * @param room   { id?, bounds: {x,y,width,height,doorSide?,doorOffset?} }
     * @param rooms  every room on the map (the room itself may be included)
     * @param opts   { mapW, mapH, tile }
     */
    function compute(room, rooms, opts) {
        if (!room || !room.bounds) return null;
        const b = room.bounds;
        const side = String(b.doorSide || b.door_side || room.doorSide || room.door_side || 'auto').toLowerCase();
        if (SIDES.includes(side)) {
            const off = typeof b.doorOffset === 'number' ? b.doorOffset : 0.5;
            const chosen = candidate(rect(room, opts.tile), side, off);
            const reason = blockReason(room, chosen, rooms, opts, false);
            if (!reason) return portal(chosen, false);
            return portal(automatic(room, rooms, opts), true, reason);
        }
        return portal(automatic(room, rooms, opts), false);
    }

    global.UlaRoomDoors = { compute, DOOR_WIDTH, SIDES };
})(window);
