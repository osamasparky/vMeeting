"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.pool = exports.PoolGame = exports.SHOT_CLOCK = exports.POCKETS = exports.POCKET_R = exports.BALL_R = exports.CLOTH = exports.FIELD = void 0;
const types_js_1 = require("./types.js");
/**
 * "بلياردو" — 8-ball for two. The shooter aims and sends one shot (angle and
 * power); the table rolls until every ball stops, then the rules decide whose
 * turn it is. Simplified rules:
 *  - the first ball potted (without a foul) gives the shooter that group;
 *  - potting one of your own keeps your turn;
 *  - fouls (cue ball potted, nothing hit, or the first ball hit not yours)
 *    pass the turn, and a potted cue ball goes back to its spot;
 *  - potting the 8 wins only once your group is cleared, otherwise it loses.
 * Each shot has SHOT_CLOCK seconds; the match ends at MAX_SECONDS with the
 * player who has cleared more of their group ahead.
 *
 * Field: 1000 × 560; cloth inside [CLOTH.x1..x2] × [CLOTH.y1..y2].
 */
exports.FIELD = { w: 1000, h: 560 };
exports.CLOTH = { x1: 60, y1: 60, x2: 940, y2: 500 };
exports.BALL_R = 12;
exports.POCKET_R = 25;
exports.POCKETS = [
    { x: exports.CLOTH.x1, y: exports.CLOTH.y1 }, { x: 500, y: exports.CLOTH.y1 - 4 }, { x: exports.CLOTH.x2, y: exports.CLOTH.y1 },
    { x: exports.CLOTH.x1, y: exports.CLOTH.y2 }, { x: 500, y: exports.CLOTH.y2 + 4 }, { x: exports.CLOTH.x2, y: exports.CLOTH.y2 },
];
const CUE_SPOT = { x: 270, y: 280 };
const RACK_X = 700;
const FRICTION = 230; // px/s² of rolling slow-down
const MAX_SHOT = 1500;
const CUSHION = 0.82;
const SUBSTEPS = 6;
exports.SHOT_CLOCK = 30;
const MAX_SECONDS = 8 * 60;
const groupOf = (n) => (n >= 1 && n <= 7 ? 'solids' : n >= 9 && n <= 15 ? 'stripes' : null);
class PoolGame {
    balls = [];
    players;
    turn = 0;
    groups = {};
    phase = 'aim';
    clock = exports.SHOT_CLOCK;
    t = 0;
    firstHit = null;
    potted = [];
    message = 'break';
    done = null;
    constructor(players) {
        this.players = players.slice(0, 2);
        for (const p of this.players)
            this.groups[p.id] = null;
        this.rack();
    }
    rack() {
        this.balls = [{ n: 0, x: CUE_SPOT.x, y: CUE_SPOT.y, vx: 0, vy: 0, in: false }];
        // Standard triangle: 8 in the middle of the third row, a solid and a stripe in the back corners.
        const order = [1, 9, 2, 10, 8, 3, 11, 4, 12, 5, 13, 6, 14, 15, 7];
        let i = 0;
        for (let row = 0; row < 5; row++) {
            for (let k = 0; k <= row; k++) {
                const x = RACK_X + row * exports.BALL_R * 1.75;
                const y = CUE_SPOT.y + (k - row / 2) * exports.BALL_R * 2.02;
                this.balls.push({ n: order[i++], x, y, vx: 0, vy: 0, in: false });
            }
        }
    }
    get shooter() {
        return this.players[this.turn];
    }
    input(_id, _i) {
        // Pool is shot by action(); held keys are not used.
    }
    action(id, data) {
        if (this.done || this.phase !== 'aim' || this.shooter.id !== id)
            return;
        const d = (data && typeof data === 'object' ? data : {});
        const angle = (0, types_js_1.num)(d.angle);
        const power = (0, types_js_1.clamp)((0, types_js_1.num)(d.power), 0.05, 1);
        const cue = this.balls[0];
        cue.vx = Math.cos(angle) * power * MAX_SHOT;
        cue.vy = Math.sin(angle) * power * MAX_SHOT;
        this.phase = 'rolling';
        this.firstHit = null;
        this.potted = [];
        this.message = null;
    }
    leave(id) {
        if (this.done)
            return;
        const stay = this.players.find((p) => p.id !== id);
        if (stay)
            this.win(stay.id);
    }
    step(dt) {
        if (this.done)
            return;
        this.t += dt;
        if (this.t > MAX_SECONDS)
            return this.timeUp();
        if (this.phase === 'aim') {
            this.clock -= dt;
            if (this.clock <= 0) {
                this.message = 'timeout';
                this.pass();
            }
            return;
        }
        const h = dt / SUBSTEPS;
        for (let s = 0; s < SUBSTEPS; s++)
            this.physics(h);
        if (this.balls.every((b) => b.in || (b.vx === 0 && b.vy === 0)))
            this.resolve();
    }
    physics(h) {
        const live = this.balls.filter((b) => !b.in);
        for (const b of live) {
            const sp = Math.hypot(b.vx, b.vy);
            if (sp === 0)
                continue;
            const ns = Math.max(0, sp - FRICTION * h);
            if (ns < 3) {
                b.vx = b.vy = 0;
                continue;
            }
            b.vx *= ns / sp;
            b.vy *= ns / sp;
            b.x += b.vx * h;
            b.y += b.vy * h;
        }
        for (let i = 0; i < live.length; i++) {
            for (let j = i + 1; j < live.length; j++) {
                const a = live[i];
                const b = live[j];
                const dx = b.x - a.x;
                const dy = b.y - a.y;
                const d = Math.hypot(dx, dy);
                if (d === 0 || d >= exports.BALL_R * 2)
                    continue;
                const nx = dx / d;
                const ny = dy / d;
                const overlap = exports.BALL_R * 2 - d;
                a.x -= nx * overlap / 2;
                a.y -= ny * overlap / 2;
                b.x += nx * overlap / 2;
                b.y += ny * overlap / 2;
                const rel = (a.vx - b.vx) * nx + (a.vy - b.vy) * ny;
                if (rel <= 0)
                    continue;
                // Equal masses, nearly elastic: swap the normal components.
                const imp = rel * 0.97;
                a.vx -= imp * nx;
                a.vy -= imp * ny;
                b.vx += imp * nx;
                b.vy += imp * ny;
                if (this.firstHit === null) {
                    if (a.n === 0)
                        this.firstHit = b.n;
                    else if (b.n === 0)
                        this.firstHit = a.n;
                }
            }
        }
        for (const b of live) {
            if (exports.POCKETS.some((p) => Math.hypot(p.x - b.x, p.y - b.y) < exports.POCKET_R)) {
                b.in = true;
                b.vx = b.vy = 0;
                this.potted.push(b.n);
                continue;
            }
            if (b.x < exports.CLOTH.x1 + exports.BALL_R) {
                b.x = exports.CLOTH.x1 + exports.BALL_R;
                b.vx = Math.abs(b.vx) * CUSHION;
            }
            if (b.x > exports.CLOTH.x2 - exports.BALL_R) {
                b.x = exports.CLOTH.x2 - exports.BALL_R;
                b.vx = -Math.abs(b.vx) * CUSHION;
            }
            if (b.y < exports.CLOTH.y1 + exports.BALL_R) {
                b.y = exports.CLOTH.y1 + exports.BALL_R;
                b.vy = Math.abs(b.vy) * CUSHION;
            }
            if (b.y > exports.CLOTH.y2 - exports.BALL_R) {
                b.y = exports.CLOTH.y2 - exports.BALL_R;
                b.vy = -Math.abs(b.vy) * CUSHION;
            }
        }
    }
    remaining(g) {
        return this.balls.filter((b) => !b.in && groupOf(b.n) === g).length;
    }
    /** All balls stopped: apply the rules to what happened during the shot. */
    resolve() {
        const me = this.shooter;
        const myGroup = this.groups[me.id];
        const scratch = this.potted.includes(0);
        const clearedBefore = myGroup !== null && this.remaining(myGroup) === 0 && !this.potted.some((n) => groupOf(n) === myGroup);
        if (this.potted.includes(8)) {
            const legal = !scratch && myGroup !== null && this.remaining(myGroup) === 0;
            return legal ? this.win(me.id) : this.win(this.other(me).id);
        }
        let foul = scratch || this.firstHit === null;
        if (!foul && myGroup !== null) {
            const wanted = clearedBefore ? 8 : null;
            foul = wanted !== null ? this.firstHit !== 8 : groupOf(this.firstHit) !== myGroup;
        }
        if (scratch) {
            const cue = this.balls[0];
            cue.in = false;
            cue.vx = cue.vy = 0;
            cue.x = CUE_SPOT.x;
            cue.y = CUE_SPOT.y;
            while (this.balls.some((b) => b !== cue && !b.in && Math.hypot(b.x - cue.x, b.y - cue.y) < exports.BALL_R * 2))
                cue.y += exports.BALL_R * 2;
        }
        const pottedGroups = this.potted.map(groupOf).filter((g) => g !== null);
        if (!foul && myGroup === null && pottedGroups.length) {
            const g = pottedGroups[0];
            this.groups[me.id] = g;
            const o = this.other(me);
            if (o)
                this.groups[o.id] = g === 'solids' ? 'stripes' : 'solids';
        }
        const mine = this.groups[me.id];
        const keep = !foul && pottedGroups.some((g) => mine === null || g === mine);
        this.message = foul ? 'foul' : null;
        if (keep) {
            this.phase = 'aim';
            this.clock = exports.SHOT_CLOCK;
        }
        else {
            this.pass();
        }
    }
    other(p) {
        return this.players.find((x) => x.id !== p.id) ?? p;
    }
    pass() {
        if (this.players.length > 1)
            this.turn = 1 - this.turn;
        this.phase = 'aim';
        this.clock = exports.SHOT_CLOCK;
    }
    win(id) {
        const winner = this.players.find((p) => p.id === id);
        const loser = this.players.find((p) => p.id !== id);
        this.done = [
            { id: winner.id, name: winner.name, place: 1, score: '🏆' },
            ...(loser ? [{ id: loser.id, name: loser.name, place: 2, score: '—' }] : []),
        ];
    }
    timeUp() {
        const cleared = (p) => {
            const g = this.groups[p.id];
            return g ? 7 - this.remaining(g) : 0;
        };
        const [a, b] = this.players;
        this.win(!b || cleared(a) >= cleared(b) ? a.id : b.id);
    }
    finished() {
        return this.done;
    }
    snapshot() {
        return {
            turn: this.shooter.id,
            phase: this.phase,
            clock: Math.ceil(this.clock),
            message: this.message,
            groups: this.groups,
            players: this.players.map((p) => ({ id: p.id, name: p.name, slot: p.slot })),
            b: this.balls.filter((b) => !b.in).map((b) => [b.n, (0, types_js_1.r1)(b.x), (0, types_js_1.r1)(b.y)]),
        };
    }
}
exports.PoolGame = PoolGame;
exports.pool = { key: 'pool', minPlayers: 2, maxPlayers: 2, create: (p) => new PoolGame(p) };
