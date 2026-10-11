"use strict";
/**
 * Office state that everyone on a map shares and late joiners must see:
 * - which room doors are locked (the browser used to be the only place this lived);
 * - each room's whiteboard: strokes and sticky notes.
 *
 * Kept in memory per organization + map + room. A server restart clears it, which
 * matches the presence data held alongside it.
 */
Object.defineProperty(exports, "__esModule", { value: true });
exports.SharedState = exports.MAX_NOTES = exports.MAX_STROKES = void 0;
exports.cleanStroke = cleanStroke;
exports.cleanNote = cleanNote;
exports.MAX_STROKES = 3000;
exports.MAX_NOTES = 200;
const MAX_POINTS = 2000;
const TOOLS = new Set(['pen', 'highlighter', 'eraser', 'line', 'rect', 'circle', 'arrow', 'text']);
const COLOR = /^#[0-9a-fA-F]{6}$/;
const num = (v) => (typeof v === 'number' && Number.isFinite(v) ? Math.round(v * 10) / 10 : undefined);
/** A stroke as drawn by the client, reduced to known fields; null when unusable. */
function cleanStroke(raw) {
    if (!raw || typeof raw !== 'object' || !TOOLS.has(raw.tool))
        return null;
    const stroke = { tool: raw.tool, color: COLOR.test(raw.color) ? raw.color : '#0F172A' };
    for (const k of ['startX', 'startY', 'endX', 'endY', 'rad']) {
        const v = num(raw[k]);
        if (v !== undefined)
            stroke[k] = v;
    }
    if (raw.tool === 'text')
        stroke.text = String(raw.text ?? '').slice(0, 300);
    if (Array.isArray(raw.points)) {
        stroke.points = raw.points
            .slice(0, MAX_POINTS)
            .map((p) => (Array.isArray(p) ? [num(p[0]), num(p[1])] : [undefined, undefined]))
            .filter((p) => p[0] !== undefined && p[1] !== undefined);
    }
    return stroke;
}
function cleanNote(raw, author) {
    const content = String(raw?.content ?? '').trim().slice(0, 500);
    if (!content)
        return null;
    const color = (v, fallback) => (typeof v === 'string' && COLOR.test(v) ? v : fallback);
    return {
        id: typeof raw?.id === 'string' && /^[\w-]{1,64}$/.test(raw.id) ? raw.id : `n_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`,
        content,
        bg: color(raw?.bg, '#FEF08A'),
        border: color(raw?.border, '#FACC15'),
        text: color(raw?.text, '#713F12'),
        author: author.slice(0, 120),
        createdAt: new Date().toISOString(),
    };
}
class SharedState {
    doors = new Map();
    boards = new Map();
    key(orgId, mapId, roomId) {
        return `${orgId}:${mapId}:${roomId}`;
    }
    // ── Doors ──
    setDoor(orgId, mapId, roomId, closed) {
        const k = this.key(orgId, mapId, roomId);
        if (closed)
            this.doors.set(k, true);
        else
            this.doors.delete(k);
    }
    isDoorClosed(orgId, mapId, roomId) {
        return this.doors.get(this.key(orgId, mapId, roomId)) === true;
    }
    closedDoors(orgId, mapId) {
        const prefix = `${orgId}:${mapId}:`;
        return [...this.doors.keys()].filter((k) => k.startsWith(prefix)).map((k) => k.slice(prefix.length));
    }
    // ── Whiteboards ──
    board(orgId, mapId, roomId) {
        const k = this.key(orgId, mapId, roomId);
        let b = this.boards.get(k);
        if (!b) {
            b = { strokes: [], notes: [] };
            this.boards.set(k, b);
        }
        return b;
    }
    addStroke(orgId, mapId, roomId, stroke) {
        const b = this.board(orgId, mapId, roomId);
        b.strokes.push(stroke);
        if (b.strokes.length > exports.MAX_STROKES)
            b.strokes.splice(0, b.strokes.length - exports.MAX_STROKES);
    }
    clearStrokes(orgId, mapId, roomId) {
        this.board(orgId, mapId, roomId).strokes = [];
    }
    addNote(orgId, mapId, roomId, note) {
        const b = this.board(orgId, mapId, roomId);
        b.notes = [note, ...b.notes.filter((n) => n.id !== note.id)].slice(0, exports.MAX_NOTES);
    }
    removeNote(orgId, mapId, roomId, id) {
        const b = this.board(orgId, mapId, roomId);
        const before = b.notes.length;
        b.notes = b.notes.filter((n) => n.id !== id);
        return b.notes.length !== before;
    }
    history(orgId, mapId, roomId) {
        const b = this.boards.get(this.key(orgId, mapId, roomId));
        return { strokes: b ? b.strokes : [], notes: b ? b.notes : [] };
    }
}
exports.SharedState = SharedState;
