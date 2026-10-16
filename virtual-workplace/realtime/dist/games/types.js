"use strict";
/**
 * Office arcade: short multiplayer games played at a game machine placed on
 * the map. The server referees every game (clients only send their keys), so
 * everyone at the machine sees the same thing and nobody can cheat locally.
 */
Object.defineProperty(exports, "__esModule", { value: true });
exports.r1 = exports.num = exports.clamp = void 0;
const clamp = (v, lo, hi) => Math.max(lo, Math.min(hi, v));
exports.clamp = clamp;
const num = (v, fallback = 0) => (typeof v === 'number' && Number.isFinite(v) ? v : fallback);
exports.num = num;
/** Round for the wire: positions don't need more than 0.1 px. */
const r1 = (v) => Math.round(v * 10) / 10;
exports.r1 = r1;
