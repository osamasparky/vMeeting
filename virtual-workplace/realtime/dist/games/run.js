"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.run = exports.RunGame = exports.open = exports.MAZE = exports.ROUND_SECONDS = exports.H = exports.W = void 0;
/**
 * "اهرب!" — a maze chase for 2–8 (after Runman on Gaming Couch). Everyone
 * starts as a runner collecting coins while two computer ghosts hunt them.
 * A runner who is caught becomes a ghost they steer themselves and hunts the
 * others. The round ends when one runner is left or after ROUND_SECONDS.
 *
 * Score: runners +10 a coin and +5 a second survived, the last runner +300;
 * ghosts +150 a catch. Coordinates are in tiles (the client draws 40 px each).
 */
exports.W = 21;
exports.H = 13;
exports.ROUND_SECONDS = 90;
const COUNTDOWN = 3;
const RUNNER_SPEED = 4.6;
const PLAYER_GHOST_SPEED = 4.5;
const AI_SPEED_START = 3.9;
const AI_SPEED_GAIN = 0.35; // per 20 s, so the ghosts get bolder
const CATCH_DIST = 0.6;
const COIN = 10;
const PER_SECOND = 5;
const LAST_ONE = 300;
const PER_CATCH = 150;
/**
 * Walls: the border, a pillar on every even/even tile, and a few extra
 * segments — only ever on even rows, so every odd row stays open end to end
 * and the whole maze stays connected with plenty of loops.
 */
exports.MAZE = (() => {
    const extra = new Set(['3,2', '17,2', '7,4', '13,4', '5,6', '15,6', '9,8', '11,8', '3,10', '17,10', '9,2', '11,10']);
    const grid = [];
    for (let y = 0; y < exports.H; y++) {
        const row = [];
        for (let x = 0; x < exports.W; x++) {
            const border = x === 0 || y === 0 || x === exports.W - 1 || y === exports.H - 1;
            row.push(border || (x % 2 === 0 && y % 2 === 0) || extra.has(`${x},${y}`));
        }
        grid.push(row);
    }
    return grid;
})();
const SPAWNS = [[1, 1], [19, 11], [19, 1], [1, 11], [9, 1], [11, 11], [1, 5], [19, 7]];
const AI_SPAWNS = [[10, 5], [10, 7]];
const DV = { up: [0, -1], down: [0, 1], left: [-1, 0], right: [1, 0] };
const OPP = { up: 'down', down: 'up', left: 'right', right: 'left' };
const DIRS = Object.keys(DV);
const open = (x, y) => x >= 0 && y >= 0 && x < exports.W && y < exports.H && !exports.MAZE[y][x];
exports.open = open;
class RunGame {
    rnd;
    movers;
    coins;
    t = -COUNTDOWN;
    done = null;
    constructor(players, rnd = Math.random) {
        this.rnd = rnd;
        this.movers = players.map((p, i) => this.mover(p.id, p.name, p.slot, SPAWNS[i % SPAWNS.length], false));
        AI_SPAWNS.forEach((s, i) => this.movers.push(this.mover(`ai${i + 1}`, '👻', -1, s, true)));
        this.coins = new Uint8Array(exports.W * exports.H);
        for (let y = 0; y < exports.H; y++)
            for (let x = 0; x < exports.W; x++)
                if ((0, exports.open)(x, y))
                    this.coins[y * exports.W + x] = 1;
        for (const m of this.movers)
            this.coins[m.y * exports.W + m.x] = 0;
    }
    mover(id, name, slot, [x, y], ai) {
        return { id, name, slot, x, y, dir: null, want: null, ghost: ai, ai, score: 0, coins: 0, catches: 0, caughtAt: null, gone: false, prev: {} };
    }
    runners() {
        return this.movers.filter((m) => !m.ghost && !m.gone);
    }
    input(id, i) {
        const m = this.movers.find((x) => x.id === id);
        if (!m)
            return;
        // The newly pressed arrow wins and is remembered until the turn is possible.
        for (const d of DIRS)
            if (i[d] && !m.prev[d])
                m.want = d;
        if (!m.want || !i[m.want]) {
            const held = DIRS.find((d) => i[d]);
            if (held)
                m.want = held;
        }
        m.prev = { ...i };
    }
    leave(id) {
        const m = this.movers.find((x) => x.id === id);
        if (m)
            m.gone = true;
        this.checkEnd();
    }
    step(dt) {
        if (this.done)
            return;
        this.t += dt;
        if (this.t < 0)
            return;
        const aiSpeed = AI_SPEED_START + AI_SPEED_GAIN * Math.floor(this.t / 20);
        for (const m of this.movers) {
            if (m.gone)
                continue;
            if (m.ai)
                m.want = this.chase(m);
            this.move(m, (m.ai ? aiSpeed : m.ghost ? PLAYER_GHOST_SPEED : RUNNER_SPEED) * dt);
            if (!m.ghost) {
                const k = Math.round(m.y) * exports.W + Math.round(m.x);
                if (this.coins[k]) {
                    this.coins[k] = 0;
                    m.coins++;
                    m.score += COIN;
                }
            }
        }
        for (const g of this.movers) {
            if (!g.ghost || g.gone)
                continue;
            for (const r of this.runners()) {
                if (Math.hypot(g.x - r.x, g.y - r.y) < CATCH_DIST)
                    this.catch(r, g);
            }
        }
        this.checkEnd();
    }
    /** Tile-by-tile movement: turns happen on tile centres, reversing any time. */
    move(m, dist) {
        if (m.want && m.dir && m.want === OPP[m.dir])
            m.dir = m.want;
        let guard = 0;
        while (dist > 1e-6 && guard++ < 8) {
            const cx = Math.round(m.x);
            const cy = Math.round(m.y);
            const atCentre = Math.abs(m.x - cx) < 1e-6 && Math.abs(m.y - cy) < 1e-6;
            if (atCentre) {
                m.x = cx;
                m.y = cy;
                if (m.want && (0, exports.open)(cx + DV[m.want][0], cy + DV[m.want][1]))
                    m.dir = m.want;
                if (!m.dir || !(0, exports.open)(cx + DV[m.dir][0], cy + DV[m.dir][1])) {
                    m.dir = null;
                    return;
                }
            }
            if (!m.dir)
                return;
            const [dx, dy] = DV[m.dir];
            // Distance to the next tile centre ahead (a whole tile when standing on one).
            const p = dx !== 0 ? m.x : m.y;
            const ahead = (dx || dy) > 0 ? (Math.ceil(p) - p || 1) : (p - Math.floor(p) || 1);
            const leg = Math.min(dist, ahead);
            m.x += dx * leg;
            m.y += dy * leg;
            dist -= leg;
            if (Math.abs(m.x - Math.round(m.x)) < 1e-6)
                m.x = Math.round(m.x);
            if (Math.abs(m.y - Math.round(m.y)) < 1e-6)
                m.y = Math.round(m.y);
        }
    }
    /** A computer ghost heads for the nearest runner by maze distance, with a little randomness. */
    chase(g) {
        const cx = Math.round(g.x);
        const cy = Math.round(g.y);
        const options = DIRS.filter((d) => (0, exports.open)(cx + DV[d][0], cy + DV[d][1]) && (d !== (g.dir ? OPP[g.dir] : null)));
        const choices = options.length ? options : DIRS.filter((d) => (0, exports.open)(cx + DV[d][0], cy + DV[d][1]));
        if (!choices.length)
            return null;
        if (this.rnd() < 0.12)
            return choices[Math.floor(this.rnd() * choices.length)];
        const dist = this.distances();
        let best = choices[0];
        let bestD = Infinity;
        for (const d of choices) {
            const v = dist[(cy + DV[d][1]) * exports.W + (cx + DV[d][0])];
            if (v < bestD) {
                bestD = v;
                best = d;
            }
        }
        return best;
    }
    /** Steps from every open tile to the nearest runner (one BFS from all runners at once). */
    distances() {
        const d = new Int32Array(exports.W * exports.H).fill(1 << 20);
        const queue = [];
        for (const r of this.runners()) {
            const k = Math.round(r.y) * exports.W + Math.round(r.x);
            d[k] = 0;
            queue.push(k);
        }
        for (let qi = 0; qi < queue.length; qi++) {
            const k = queue[qi];
            const x = k % exports.W;
            const y = (k - x) / exports.W;
            for (const dir of DIRS) {
                const nx = x + DV[dir][0];
                const ny = y + DV[dir][1];
                const nk = ny * exports.W + nx;
                if ((0, exports.open)(nx, ny) && d[nk] > d[k] + 1) {
                    d[nk] = d[k] + 1;
                    queue.push(nk);
                }
            }
        }
        return d;
    }
    catch(r, by) {
        r.ghost = true;
        r.caughtAt = this.t;
        r.score += Math.floor(this.t) * PER_SECOND;
        if (!by.ai) {
            by.catches++;
            by.score += PER_CATCH;
        }
    }
    checkEnd() {
        if (this.done)
            return;
        const people = this.movers.filter((m) => !m.ai && !m.gone);
        const left = this.runners();
        const startedWith = this.movers.filter((m) => !m.ai).length;
        const over = this.t >= exports.ROUND_SECONDS || left.length === 0 || (startedWith > 1 && left.length <= 1) || people.length === 0;
        if (!over)
            return;
        for (const r of left) {
            r.score += Math.floor(Math.max(0, this.t)) * PER_SECOND;
            if (left.length === 1 && startedWith > 1)
                r.score += LAST_ONE;
        }
        const ranked = this.movers.filter((m) => !m.ai).sort((a, b) => Number(a.gone) - Number(b.gone) || b.score - a.score);
        this.done = ranked.map((m, i) => ({ id: m.id, name: m.name, place: i + 1, score: String(m.score) }));
    }
    finished() {
        return this.done;
    }
    meta() {
        return { w: exports.W, h: exports.H, walls: exports.MAZE.map((row) => row.map((w) => (w ? '#' : '.')).join('')) };
    }
    snapshot() {
        return {
            countdown: this.t < 0 ? Math.ceil(-this.t) : 0,
            timeLeft: Math.max(0, Math.ceil(exports.ROUND_SECONDS - Math.max(0, this.t))),
            coins: Array.from(this.coins).join(''),
            m: this.movers.filter((m) => !m.gone).map((m) => ({
                id: m.id, name: m.name, slot: m.slot, x: Math.round(m.x * 100) / 100, y: Math.round(m.y * 100) / 100,
                dir: m.dir, ghost: m.ghost, ai: m.ai, score: m.score,
            })),
            runners: this.runners().length,
        };
    }
}
exports.RunGame = RunGame;
exports.run = { key: 'run', minPlayers: 2, maxPlayers: 8, create: (p) => new RunGame(p) };
