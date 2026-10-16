import { Game, GameDef, GameInput, GamePlayer, GameResult, r1 } from './types.js';

/**
 * "على السطح" — two buildings, a gap between them, two buttons: jump and
 * shoot. The rifle sways on its own, so timing the shot is the skill. A hit
 * shoves the target back; whoever falls off loses the round. First to
 * ROUNDS_TO_WIN takes the match (about a minute).
 *
 * Field: 960 × 540, y grows downward, a player's (x, y) is their feet.
 */

export const FIELD = { w: 960, h: 540 };
export const ROOFS = [
  { x1: 40, x2: 400 },
  { x1: 560, x2: 920 },
];
export const ROOF_Y = 380;

const GRAVITY = 1800;
const JUMP_V = -720;
const GROUND_DRAG = 9; // per second, exponential
const AIR_DRAG = 0.6;
const HALF_W = 14;
const HEIGHT = 56;
const SHOT_COOLDOWN = 0.7;
const KNOCK_X = 540;
const KNOCK_Y = -260;
const FALL_Y = 640;
const ROUND_SECONDS = 30;
const BETWEEN_ROUNDS = 1.6;
export const ROUNDS_TO_WIN = 3;
const SPAWN_X = [300, 660, 160, 800];

interface Sniper {
  p: GamePlayer;
  x: number;
  y: number;
  vx: number;
  vy: number;
  dir: 1 | -1;
  onGround: boolean;
  alive: boolean;
  gone: boolean;
  cooldown: number;
  phase: number;
  wins: number;
  keys: GameInput;
  prev: GameInput;
}

interface Shot { x1: number; y1: number; x2: number; y2: number; ttl: number; hit: boolean }

export class SnipersGame implements Game {
  private s: Sniper[];
  private shots: Shot[] = [];
  private t = 0;
  private roundT = 0;
  private pause = 1.2; // a beat before the first round
  private round = 1;
  private lastWinner: string | null = null;
  private done: GameResult[] | null = null;

  constructor(players: GamePlayer[]) {
    this.s = players.map((p) => ({
      p, x: 0, y: 0, vx: 0, vy: 0, dir: 1, onGround: true, alive: true, gone: false,
      cooldown: 0, phase: p.slot * 1.7, wins: 0, keys: {}, prev: {},
    }));
    this.resetRound();
  }

  /** Rifle angle above the horizon, radians; it sways by itself. */
  aim(sn: Sniper): number {
    return 0.55 * Math.sin(this.t * 2.6 + sn.phase) + 0.1;
  }

  input(id: string, i: GameInput): void {
    const sn = this.s.find((x) => x.p.id === id);
    if (sn) sn.keys = i;
  }

  leave(id: string): void {
    const sn = this.s.find((x) => x.p.id === id);
    if (!sn) return;
    sn.gone = true;
    sn.alive = false;
    if (this.s.filter((x) => !x.gone).length < 2) this.end();
  }

  step(dt: number): void {
    if (this.done) return;
    this.t += dt;
    this.shots = this.shots.filter((sh) => (sh.ttl -= dt) > 0);

    if (this.pause > 0) {
      this.pause -= dt;
      if (this.pause <= 0 && this.lastWinner !== null) this.resetRound();
      if (this.pause > 0) {
        for (const sn of this.s) sn.prev = { ...sn.keys };
        return;
      }
    }

    this.roundT += dt;
    for (const sn of this.s) {
      if (!sn.alive) continue;
      const pressed = (k: keyof GameInput) => !!sn.keys[k] && !sn.prev[k];
      if (pressed('up') && sn.onGround) {
        sn.vy = JUMP_V;
        sn.onGround = false;
      }
      sn.cooldown = Math.max(0, sn.cooldown - dt);
      if (pressed('a') && sn.cooldown === 0) this.fire(sn);
      sn.prev = { ...sn.keys };
    }

    for (const sn of this.s) {
      if (!sn.alive) continue;
      sn.vy += GRAVITY * dt;
      sn.vx *= Math.exp(-(sn.onGround ? GROUND_DRAG : AIR_DRAG) * dt);
      const oldY = sn.y;
      sn.x += sn.vx * dt;
      sn.y += sn.vy * dt;

      const roof = ROOFS.find((r) => sn.x >= r.x1 && sn.x <= r.x2);
      sn.onGround = false;
      if (roof && oldY <= ROOF_Y && sn.y >= ROOF_Y && sn.vy >= 0) {
        sn.y = ROOF_Y;
        sn.vy = 0;
        sn.onGround = true;
      }
      // Below the roofline the buildings are walls: the gap only goes down.
      if (sn.y > ROOF_Y + 2 && !sn.onGround) {
        if (sn.x < ROOFS[0].x2 + HALF_W && sn.x > ROOFS[0].x2 - 30) sn.x = ROOFS[0].x2 + HALF_W;
        if (sn.x > ROOFS[1].x1 - HALF_W && sn.x < ROOFS[1].x1 + 30) sn.x = ROOFS[1].x1 - HALF_W;
      }
      if (sn.y > FALL_Y || sn.x < -60 || sn.x > FIELD.w + 60) sn.alive = false;
    }

    const standing = this.s.filter((x) => x.alive);
    const present = this.s.filter((x) => !x.gone);
    if (standing.length <= 1 || this.roundT > ROUND_SECONDS) {
      const winner = standing.length === 1 ? standing[0] : null;
      if (winner) winner.wins++;
      this.lastWinner = winner?.p.id ?? '';
      if (present.some((x) => x.wins >= ROUNDS_TO_WIN)) return this.end();
      this.round++;
      this.pause = BETWEEN_ROUNDS;
    }
  }

  private fire(sn: Sniper): void {
    sn.cooldown = SHOT_COOLDOWN;
    const a = this.aim(sn);
    const ox = sn.x + sn.dir * 12;
    const oy = sn.y - 38;
    const dx = sn.dir * Math.cos(a);
    const dy = -Math.sin(a);
    let best: { t: number; target: Sniper } | null = null;
    for (const o of this.s) {
      if (o === sn || !o.alive) continue;
      const t = rayBox(ox, oy, dx, dy, o.x - HALF_W, o.y - HEIGHT, o.x + HALF_W, o.y);
      if (t !== null && (!best || t < best.t)) best = { t, target: o };
    }
    const len = best ? best.t : 1400;
    this.shots.push({ x1: ox, y1: oy, x2: ox + dx * len, y2: oy + dy * len, ttl: 0.12, hit: !!best });
    if (best) {
      best.target.vx += sn.dir * KNOCK_X;
      best.target.vy += KNOCK_Y;
      best.target.onGround = false;
    }
  }

  private resetRound(): void {
    this.roundT = 0;
    this.lastWinner = null;
    for (const sn of this.s) {
      sn.x = SPAWN_X[sn.p.slot % SPAWN_X.length];
      sn.y = ROOF_Y;
      sn.vx = sn.vy = 0;
      sn.dir = sn.x < FIELD.w / 2 ? 1 : -1;
      sn.onGround = true;
      sn.alive = !sn.gone;
      sn.cooldown = 0.3;
    }
  }

  private end(): void {
    const ranked = [...this.s].sort((a, b) => Number(a.gone) - Number(b.gone) || b.wins - a.wins);
    this.done = ranked.map((sn, i) => ({ id: sn.p.id, name: sn.p.name, place: i + 1, score: String(sn.wins) }));
  }

  finished(): GameResult[] | null {
    return this.done;
  }

  snapshot(): unknown {
    return {
      round: this.round,
      pause: this.pause > 0,
      lastWinner: this.lastWinner,
      timeLeft: Math.max(0, Math.ceil(ROUND_SECONDS - this.roundT)),
      p: this.s.map((sn) => ({
        id: sn.p.id, slot: sn.p.slot, name: sn.p.name, x: r1(sn.x), y: r1(sn.y), dir: sn.dir,
        aim: r1(this.aim(sn) * 100) / 100, alive: sn.alive, wins: sn.wins, ready: sn.cooldown === 0,
      })),
      shots: this.shots.map((sh) => ({ x1: r1(sh.x1), y1: r1(sh.y1), x2: r1(sh.x2), y2: r1(sh.y2), hit: sh.hit })),
    };
  }
}

/** Distance along a unit ray to an axis-aligned box, or null if it misses. */
export function rayBox(ox: number, oy: number, dx: number, dy: number, x1: number, y1: number, x2: number, y2: number): number | null {
  let tmin = 0;
  let tmax = Infinity;
  for (const [o, d, lo, hi] of [[ox, dx, x1, x2], [oy, dy, y1, y2]] as const) {
    if (Math.abs(d) < 1e-9) {
      if (o < lo || o > hi) return null;
    } else {
      let a = (lo - o) / d;
      let b = (hi - o) / d;
      if (a > b) [a, b] = [b, a];
      tmin = Math.max(tmin, a);
      tmax = Math.min(tmax, b);
      if (tmin > tmax) return null;
    }
  }
  return tmin;
}

export const snipers: GameDef = { key: 'snipers', minPlayers: 2, maxPlayers: 4, create: (p) => new SnipersGame(p) };
