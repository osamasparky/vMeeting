"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.racing = exports.RacingGame = exports.TRACK = exports.LAPS = exports.HALF_WIDTH = exports.FIELD = void 0;
exports.locate = locate;
const types_js_1 = require("./types.js");
/**
 * "سباق" — top-down racing, 1–8 cars, LAPS laps (about two minutes). Up
 * accelerates, down brakes / reverses, left-right steer, the action key is a
 * short nitro once per lap. Leaving the asphalt slows you right down.
 * The race ends when everyone is home, FINISH_GRACE seconds after the
 * winner, or at MAX_SECONDS.
 *
 * Field: 1000 × 620. The track is a closed Catmull-Rom curve through
 * CONTROL points; progress is measured along it, so cutting corners or
 * driving backwards never counts.
 */
exports.FIELD = { w: 1000, h: 620 };
exports.HALF_WIDTH = 46;
exports.LAPS = 3;
const COUNTDOWN = 3;
const FINISH_GRACE = 15;
const MAX_SECONDS = 180;
const ACCEL = 420;
const BRAKE = 640;
const MAX_SPEED = 340;
const MAX_REVERSE = -110;
const OFF_TRACK_MAX = 120;
const ROLL_DRAG = 0.6;
const STEER = 2.9;
const NITRO_SPEED = 470;
const NITRO_TIME = 1.2;
const CAR_R = 13;
const CONTROL = [
    [220, 530], [500, 535], [790, 525], [905, 445], [910, 300], [830, 190], [705, 225],
    [610, 330], [480, 335], [395, 205], [300, 105], [165, 120], [95, 250], [105, 430],
];
/** The centreline, sampled densely, with cumulative length at each point. */
exports.TRACK = buildTrack(CONTROL, 14);
function buildTrack(pts, per) {
    const out = [];
    const n = pts.length;
    for (let i = 0; i < n; i++) {
        const [p0, p1, p2, p3] = [pts[(i - 1 + n) % n], pts[i], pts[(i + 1) % n], pts[(i + 2) % n]];
        for (let k = 0; k < per; k++) {
            const t = k / per;
            const t2 = t * t;
            const t3 = t2 * t;
            const c = (a, b, cc, d) => 0.5 * (2 * b + (-a + cc) * t + (2 * a - 5 * b + 4 * cc - d) * t2 + (-a + 3 * b - 3 * cc + d) * t3);
            out.push({ x: c(p0[0], p1[0], p2[0], p3[0]), y: c(p0[1], p1[1], p2[1], p3[1]), s: 0 });
        }
    }
    let s = 0;
    for (let i = 0; i < out.length; i++) {
        out[i].s = s;
        const nx = out[(i + 1) % out.length];
        s += Math.hypot(nx.x - out[i].x, nx.y - out[i].y);
    }
    return { pts: out, length: s };
}
/** Nearest point on the centreline: distance from it and progress along it. */
function locate(x, y) {
    const p = exports.TRACK.pts;
    let best = { dist: Infinity, s: 0 };
    for (let i = 0; i < p.length; i++) {
        const a = p[i];
        const b = p[(i + 1) % p.length];
        const vx = b.x - a.x;
        const vy = b.y - a.y;
        const len2 = vx * vx + vy * vy || 1;
        const t = (0, types_js_1.clamp)(((x - a.x) * vx + (y - a.y) * vy) / len2, 0, 1);
        const d = Math.hypot(a.x + vx * t - x, a.y + vy * t - y);
        if (d < best.dist)
            best = { dist: d, s: a.s + Math.sqrt(len2) * t };
    }
    return best;
}
class RacingGame {
    cars;
    t = -COUNTDOWN;
    firstFinish = null;
    done = null;
    constructor(players) {
        const start = exports.TRACK.pts[0];
        const next = exports.TRACK.pts[3];
        const a = Math.atan2(next.y - start.y, next.x - start.x);
        this.cars = players.map((p) => {
            const row = Math.floor(p.slot / 2);
            const side = p.slot % 2 === 0 ? -1 : 1;
            const back = 34 + row * 34;
            const x = start.x - Math.cos(a) * back - Math.sin(a) * side * 18;
            const y = start.y - Math.sin(a) * back + Math.cos(a) * side * 18;
            const s = locate(x, y).s;
            return {
                p, x, y, a, v: 0, progress: s - exports.TRACK.length, lastS: s, lap: 0, nitro: 0, nitroUsedLap: -1,
                finishedAt: null, gone: false, keys: {},
            };
        });
    }
    input(id, i) {
        const c = this.cars.find((x) => x.p.id === id);
        if (c)
            c.keys = i;
    }
    leave(id) {
        const c = this.cars.find((x) => x.p.id === id);
        if (c)
            c.gone = true;
        if (this.cars.every((x) => x.gone || x.finishedAt !== null))
            this.end();
    }
    step(dt) {
        if (this.done)
            return;
        this.t += dt;
        if (this.t < 0)
            return; // lights still red
        for (const c of this.cars) {
            if (c.gone || c.finishedAt !== null) {
                c.v *= Math.exp(-3 * dt);
                this.move(c, dt);
                continue;
            }
            const k = c.keys;
            if (k.up)
                c.v += ACCEL * dt;
            else if (k.down)
                c.v -= (c.v > 0 ? BRAKE : ACCEL * 0.6) * dt;
            else
                c.v *= Math.exp(-ROLL_DRAG * dt);
            if (k.a && c.nitroUsedLap !== c.lap) {
                c.nitro = NITRO_TIME;
                c.nitroUsedLap = c.lap;
            }
            c.nitro = Math.max(0, c.nitro - dt);
            const onTrack = locate(c.x, c.y).dist <= exports.HALF_WIDTH;
            const top = c.nitro > 0 ? NITRO_SPEED : onTrack ? MAX_SPEED : OFF_TRACK_MAX;
            if (c.nitro > 0 && k.up)
                c.v = Math.max(c.v, top * 0.9);
            if (c.v > top)
                c.v -= Math.min(c.v - top, (onTrack ? 300 : 900) * dt);
            c.v = Math.max(c.v, MAX_REVERSE);
            const grip = (0, types_js_1.clamp)(Math.abs(c.v) / 120, 0, 1);
            const turn = (k.left ? -1 : 0) + (k.right ? 1 : 0);
            c.a += turn * STEER * grip * Math.sign(c.v || 1) * dt;
            this.move(c, dt);
        }
        this.collide();
        for (const c of this.cars) {
            if (c.finishedAt !== null || c.gone)
                continue;
            const { s } = locate(c.x, c.y);
            let ds = s - c.lastS;
            if (ds < -exports.TRACK.length / 2)
                ds += exports.TRACK.length; // crossed the line forwards
            if (ds > exports.TRACK.length / 2)
                ds -= exports.TRACK.length; // crossed it backwards
            c.lastS = s;
            c.progress += ds;
            c.lap = Math.max(c.lap, Math.floor(c.progress / exports.TRACK.length) + 1);
            if (c.progress >= exports.LAPS * exports.TRACK.length) {
                c.finishedAt = this.t;
                this.firstFinish ??= this.t;
            }
        }
        const racing = this.cars.filter((c) => !c.gone && c.finishedAt === null);
        if (racing.length === 0 || (this.firstFinish !== null && this.t - this.firstFinish > FINISH_GRACE) || this.t > MAX_SECONDS) {
            this.end();
        }
    }
    move(c, dt) {
        c.x = (0, types_js_1.clamp)(c.x + Math.cos(c.a) * c.v * dt, CAR_R, exports.FIELD.w - CAR_R);
        c.y = (0, types_js_1.clamp)(c.y + Math.sin(c.a) * c.v * dt, CAR_R, exports.FIELD.h - CAR_R);
    }
    /** Cars are discs: overlapping ones are pushed apart and both lose a little speed. */
    collide() {
        for (let i = 0; i < this.cars.length; i++) {
            for (let j = i + 1; j < this.cars.length; j++) {
                const a = this.cars[i];
                const b = this.cars[j];
                if (a.gone || b.gone)
                    continue;
                const dx = b.x - a.x;
                const dy = b.y - a.y;
                const d = Math.hypot(dx, dy);
                if (d > 0 && d < CAR_R * 2) {
                    const push = (CAR_R * 2 - d) / 2;
                    a.x -= (dx / d) * push;
                    a.y -= (dy / d) * push;
                    b.x += (dx / d) * push;
                    b.y += (dy / d) * push;
                    a.v *= 0.92;
                    b.v *= 0.92;
                }
            }
        }
    }
    /** Live standings: finished cars by time, then everyone by distance raced. */
    order() {
        return [...this.cars].sort((a, b) => Number(a.gone) - Number(b.gone)
            || (a.finishedAt ?? Infinity) - (b.finishedAt ?? Infinity)
            || b.progress - a.progress);
    }
    end() {
        this.done = this.order().map((c, i) => ({
            id: c.p.id, name: c.p.name, place: i + 1,
            score: c.finishedAt !== null ? `${c.finishedAt.toFixed(1)}s` : c.gone ? '—' : `${Math.min(c.lap, exports.LAPS)}/${exports.LAPS}`,
        }));
    }
    finished() {
        return this.done;
    }
    meta() {
        // Every 2nd sample is plenty to draw the road.
        return { half: exports.HALF_WIDTH, track: exports.TRACK.pts.filter((_, i) => i % 2 === 0).map((p) => [(0, types_js_1.r1)(p.x), (0, types_js_1.r1)(p.y)]) };
    }
    snapshot() {
        const order = this.order().map((c) => c.p.id);
        return {
            countdown: this.t < 0 ? Math.ceil(-this.t) : 0,
            time: (0, types_js_1.r1)(Math.max(0, this.t)),
            laps: exports.LAPS,
            c: this.cars.map((c) => ({
                id: c.p.id, slot: c.p.slot, name: c.p.name, x: (0, types_js_1.r1)(c.x), y: (0, types_js_1.r1)(c.y), a: Math.round(c.a * 100) / 100,
                lap: Math.min(c.lap, exports.LAPS), pos: order.indexOf(c.p.id) + 1, nitro: c.nitro > 0,
                nitroReady: c.nitroUsedLap !== c.lap, done: c.finishedAt !== null, gone: c.gone,
            })),
        };
    }
}
exports.RacingGame = RacingGame;
exports.racing = { key: 'racing', minPlayers: 1, maxPlayers: 8, create: (p) => new RacingGame(p) };
