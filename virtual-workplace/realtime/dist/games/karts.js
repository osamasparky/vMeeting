"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.karts = exports.KartsGame = exports.LIVES = exports.CENTER = exports.FIELD = void 0;
const types_js_1 = require("./types.js");
/**
 * "سيارات التصادم" — bumper karts on a round platform, 2–8 players. Knock
 * the others off the edge; each kart has LIVES lives. Boxes give one boost:
 * press the action key for a burst of speed and a much harder hit. After
 * SHRINK_FROM seconds the platform starts to shrink. Last kart on wins
 * (about two minutes at most).
 *
 * Field: 900 × 600, platform centred.
 */
exports.FIELD = { w: 900, h: 600 };
exports.CENTER = { x: 450, y: 300 };
const R_START = 270;
const R_MIN = 140;
const SHRINK_FROM = 45;
const SHRINK_TO = 100;
const MAX_SECONDS = 150;
exports.LIVES = 3;
const KART_R = 17;
const ACCEL = 520;
const MAX_SPEED = 300;
const STEER = 3.3;
const SIDE_GRIP = 6; // how fast sideways slide dies out
const DRAG = 0.9;
const BOUNCE = 1.15;
const BOOST_TIME = 0.9;
const BOOST_SPEED = 520;
const BOOST_MASS = 3;
const RESPAWN_AFTER = 1.5;
const SHIELD_TIME = 1.5;
const BOX_EVERY = 4;
const MAX_BOXES = 3;
const KO_CREDIT_SECONDS = 3;
class KartsGame {
    rnd;
    k;
    boxes = [];
    nextBox = 2;
    boxSeq = 0;
    t = -2; // short countdown
    done = null;
    constructor(players, rnd = Math.random) {
        this.rnd = rnd;
        const n = players.length;
        this.k = players.map((p, i) => {
            const ang = (i / n) * Math.PI * 2;
            const x = exports.CENTER.x + Math.cos(ang) * R_START * 0.6;
            const y = exports.CENTER.y + Math.sin(ang) * R_START * 0.6;
            return {
                p, x, y, a: ang + Math.PI, vx: 0, vy: 0, lives: exports.LIVES, kos: 0, hasBoost: false, boost: 0, shield: 0, out: 0,
                lastHitBy: null, lastHitAt: -99, eliminatedAt: null, gone: false, keys: {},
            };
        });
    }
    radius() {
        if (this.t <= SHRINK_FROM)
            return R_START;
        const f = (0, types_js_1.clamp)((this.t - SHRINK_FROM) / (SHRINK_TO - SHRINK_FROM), 0, 1);
        return R_START - (R_START - R_MIN) * f;
    }
    input(id, i) {
        const k = this.k.find((x) => x.p.id === id);
        if (k)
            k.keys = i;
    }
    leave(id) {
        const k = this.k.find((x) => x.p.id === id);
        if (!k)
            return;
        k.gone = true;
        k.lives = 0;
        k.eliminatedAt ??= this.t;
        this.checkEnd();
    }
    step(dt) {
        if (this.done)
            return;
        this.t += dt;
        if (this.t < 0)
            return;
        const R = this.radius();
        this.nextBox -= dt;
        if (this.nextBox <= 0 && this.boxes.length < MAX_BOXES) {
            const ang = this.rnd() * Math.PI * 2;
            const d = Math.sqrt(this.rnd()) * (R - 40);
            this.boxes.push({ id: ++this.boxSeq, x: exports.CENTER.x + Math.cos(ang) * d, y: exports.CENTER.y + Math.sin(ang) * d });
            this.nextBox = BOX_EVERY;
        }
        for (const k of this.k) {
            if (k.lives <= 0)
                continue;
            if (k.out > 0) {
                k.out -= dt;
                if (k.out <= 0)
                    this.respawn(k);
                continue;
            }
            k.shield = Math.max(0, k.shield - dt);
            k.boost = Math.max(0, k.boost - dt);
            const i = k.keys;
            if (i.a && k.hasBoost) {
                k.hasBoost = false;
                k.boost = BOOST_TIME;
            }
            const speed = Math.hypot(k.vx, k.vy);
            const turn = (i.left ? -1 : 0) + (i.right ? 1 : 0);
            k.a += turn * STEER * (0, types_js_1.clamp)(speed / 80, 0.35, 1) * dt;
            const fx = Math.cos(k.a);
            const fy = Math.sin(k.a);
            const thrust = k.boost > 0 ? ACCEL * 2.2 : i.up ? ACCEL : i.down ? -ACCEL * 0.6 : 0;
            k.vx += fx * thrust * dt;
            k.vy += fy * thrust * dt;
            // Grip: keep the forward part of the velocity, bleed off the sideways part.
            const fwd = k.vx * fx + k.vy * fy;
            const sx = k.vx - fx * fwd;
            const sy = k.vy - fy * fwd;
            const keep = Math.exp(-SIDE_GRIP * dt);
            k.vx = fx * fwd + sx * keep;
            k.vy = fy * fwd + sy * keep;
            const drag = Math.exp(-DRAG * dt);
            k.vx *= drag;
            k.vy *= drag;
            const top = k.boost > 0 ? BOOST_SPEED : MAX_SPEED;
            const sp = Math.hypot(k.vx, k.vy);
            if (sp > top) {
                k.vx *= top / sp;
                k.vy *= top / sp;
            }
            k.x += k.vx * dt;
            k.y += k.vy * dt;
            this.boxes = this.boxes.filter((b) => {
                if (k.hasBoost || Math.hypot(b.x - k.x, b.y - k.y) > KART_R + 14)
                    return true;
                k.hasBoost = true;
                return false;
            });
        }
        this.collide();
        for (const k of this.k) {
            if (k.lives <= 0 || k.out > 0)
                continue;
            if (Math.hypot(k.x - exports.CENTER.x, k.y - exports.CENTER.y) > R)
                this.fall(k);
        }
        this.checkEnd();
    }
    collide() {
        const live = this.k.filter((k) => k.lives > 0 && k.out <= 0);
        for (let i = 0; i < live.length; i++) {
            for (let j = i + 1; j < live.length; j++) {
                const a = live[i];
                const b = live[j];
                if (a.shield > 0 || b.shield > 0)
                    continue;
                const dx = b.x - a.x;
                const dy = b.y - a.y;
                const d = Math.hypot(dx, dy);
                if (d <= 0 || d >= KART_R * 2)
                    continue;
                const nx = dx / d;
                const ny = dy / d;
                const overlap = KART_R * 2 - d;
                a.x -= nx * overlap / 2;
                a.y -= ny * overlap / 2;
                b.x += nx * overlap / 2;
                b.y += ny * overlap / 2;
                const ma = a.boost > 0 ? BOOST_MASS : 1;
                const mb = b.boost > 0 ? BOOST_MASS : 1;
                const rel = (a.vx - b.vx) * nx + (a.vy - b.vy) * ny;
                if (rel <= 0)
                    continue;
                // Restitution 0.9, then BOUNCE on top: bumper karts hit harder than physics would.
                const j2 = (BOUNCE * 1.9 * rel) / (1 / ma + 1 / mb);
                a.vx -= (j2 / ma) * nx;
                a.vy -= (j2 / ma) * ny;
                b.vx += (j2 / mb) * nx;
                b.vy += (j2 / mb) * ny;
                a.lastHitBy = b.p.id;
                a.lastHitAt = this.t;
                b.lastHitBy = a.p.id;
                b.lastHitAt = this.t;
            }
        }
    }
    fall(k) {
        k.lives--;
        k.out = RESPAWN_AFTER;
        k.hasBoost = false;
        k.boost = 0;
        if (k.lastHitBy && this.t - k.lastHitAt <= KO_CREDIT_SECONDS) {
            const by = this.k.find((x) => x.p.id === k.lastHitBy);
            if (by)
                by.kos++;
        }
        if (k.lives <= 0)
            k.eliminatedAt = this.t;
    }
    respawn(k) {
        const ang = this.rnd() * Math.PI * 2;
        k.x = exports.CENTER.x + Math.cos(ang) * 40;
        k.y = exports.CENTER.y + Math.sin(ang) * 40;
        k.a = ang;
        k.vx = k.vy = 0;
        k.shield = SHIELD_TIME;
        k.out = 0;
        k.lastHitBy = null;
    }
    checkEnd() {
        if (this.done)
            return;
        const alive = this.k.filter((k) => k.lives > 0);
        if (alive.length > 1 && this.t < MAX_SECONDS)
            return;
        // Survivors first (most lives, then KOs), then the rest by how long they lasted.
        const ranked = [...this.k].sort((a, b) => Number(a.gone) - Number(b.gone)
            || b.lives - a.lives
            || (b.eliminatedAt ?? Infinity) - (a.eliminatedAt ?? Infinity)
            || b.kos - a.kos);
        this.done = ranked.map((k, i) => ({ id: k.p.id, name: k.p.name, place: i + 1, score: `${k.kos} KO` }));
    }
    finished() {
        return this.done;
    }
    snapshot() {
        return {
            countdown: this.t < 0 ? Math.ceil(-this.t) : 0,
            timeLeft: Math.max(0, Math.ceil(MAX_SECONDS - Math.max(0, this.t))),
            r: (0, types_js_1.r1)(this.radius()),
            boxes: this.boxes.map((b) => ({ id: b.id, x: (0, types_js_1.r1)(b.x), y: (0, types_js_1.r1)(b.y) })),
            k: this.k.map((k) => ({
                id: k.p.id, slot: k.p.slot, name: k.p.name, x: (0, types_js_1.r1)(k.x), y: (0, types_js_1.r1)(k.y), a: Math.round(k.a * 100) / 100,
                lives: k.lives, kos: k.kos, boost: k.boost > 0, hasBoost: k.hasBoost, shield: k.shield > 0, out: k.out > 0,
            })),
        };
    }
}
exports.KartsGame = KartsGame;
exports.karts = { key: 'karts', minPlayers: 2, maxPlayers: 8, create: (p) => new KartsGame(p) };
