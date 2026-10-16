import { Game, GameDef, GameInput, GamePlayer, GameResult, clamp, r1 } from './types.js';

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

export const FIELD = { w: 1000, h: 620 };
export const HALF_WIDTH = 46;
export const LAPS = 3;
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

const CONTROL: [number, number][] = [
  [220, 530], [500, 535], [790, 525], [905, 445], [910, 300], [830, 190], [705, 225],
  [610, 330], [480, 335], [395, 205], [300, 105], [165, 120], [95, 250], [105, 430],
];

/** The centreline, sampled densely, with cumulative length at each point. */
export const TRACK = buildTrack(CONTROL, 14);

function buildTrack(pts: [number, number][], per: number) {
  const out: { x: number; y: number; s: number }[] = [];
  const n = pts.length;
  for (let i = 0; i < n; i++) {
    const [p0, p1, p2, p3] = [pts[(i - 1 + n) % n], pts[i], pts[(i + 1) % n], pts[(i + 2) % n]];
    for (let k = 0; k < per; k++) {
      const t = k / per;
      const t2 = t * t;
      const t3 = t2 * t;
      const c = (a: number, b: number, cc: number, d: number) =>
        0.5 * (2 * b + (-a + cc) * t + (2 * a - 5 * b + 4 * cc - d) * t2 + (-a + 3 * b - 3 * cc + d) * t3);
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
export function locate(x: number, y: number): { dist: number; s: number } {
  const p = TRACK.pts;
  let best = { dist: Infinity, s: 0 };
  for (let i = 0; i < p.length; i++) {
    const a = p[i];
    const b = p[(i + 1) % p.length];
    const vx = b.x - a.x;
    const vy = b.y - a.y;
    const len2 = vx * vx + vy * vy || 1;
    const t = clamp(((x - a.x) * vx + (y - a.y) * vy) / len2, 0, 1);
    const d = Math.hypot(a.x + vx * t - x, a.y + vy * t - y);
    if (d < best.dist) best = { dist: d, s: a.s + Math.sqrt(len2) * t };
  }
  return best;
}

interface Car {
  p: GamePlayer;
  x: number;
  y: number;
  a: number;
  v: number;
  /** Total distance raced along the track (laps × length + s). */
  progress: number;
  lastS: number;
  lap: number;
  nitro: number;
  nitroUsedLap: number;
  finishedAt: number | null;
  gone: boolean;
  keys: GameInput;
}

export class RacingGame implements Game {
  private cars: Car[];
  private t = -COUNTDOWN;
  private firstFinish: number | null = null;
  private done: GameResult[] | null = null;

  constructor(players: GamePlayer[]) {
    const start = TRACK.pts[0];
    const next = TRACK.pts[3];
    const a = Math.atan2(next.y - start.y, next.x - start.x);
    this.cars = players.map((p) => {
      const row = Math.floor(p.slot / 2);
      const side = p.slot % 2 === 0 ? -1 : 1;
      const back = 34 + row * 34;
      const x = start.x - Math.cos(a) * back - Math.sin(a) * side * 18;
      const y = start.y - Math.sin(a) * back + Math.cos(a) * side * 18;
      const s = locate(x, y).s;
      return {
        p, x, y, a, v: 0, progress: s - TRACK.length, lastS: s, lap: 0, nitro: 0, nitroUsedLap: -1,
        finishedAt: null, gone: false, keys: {},
      };
    });
  }

  input(id: string, i: GameInput): void {
    const c = this.cars.find((x) => x.p.id === id);
    if (c) c.keys = i;
  }

  leave(id: string): void {
    const c = this.cars.find((x) => x.p.id === id);
    if (c) c.gone = true;
    if (this.cars.every((x) => x.gone || x.finishedAt !== null)) this.end();
  }

  step(dt: number): void {
    if (this.done) return;
    this.t += dt;
    if (this.t < 0) return; // lights still red

    for (const c of this.cars) {
      if (c.gone || c.finishedAt !== null) {
        c.v *= Math.exp(-3 * dt);
        this.move(c, dt);
        continue;
      }
      const k = c.keys;
      if (k.up) c.v += ACCEL * dt;
      else if (k.down) c.v -= (c.v > 0 ? BRAKE : ACCEL * 0.6) * dt;
      else c.v *= Math.exp(-ROLL_DRAG * dt);

      if (k.a && c.nitroUsedLap !== c.lap) {
        c.nitro = NITRO_TIME;
        c.nitroUsedLap = c.lap;
      }
      c.nitro = Math.max(0, c.nitro - dt);

      const onTrack = locate(c.x, c.y).dist <= HALF_WIDTH;
      const top = c.nitro > 0 ? NITRO_SPEED : onTrack ? MAX_SPEED : OFF_TRACK_MAX;
      if (c.nitro > 0 && k.up) c.v = Math.max(c.v, top * 0.9);
      if (c.v > top) c.v -= Math.min(c.v - top, (onTrack ? 300 : 900) * dt);
      c.v = Math.max(c.v, MAX_REVERSE);

      const grip = clamp(Math.abs(c.v) / 120, 0, 1);
      const turn = (k.left ? -1 : 0) + (k.right ? 1 : 0);
      c.a += turn * STEER * grip * Math.sign(c.v || 1) * dt;
      this.move(c, dt);
    }
    this.collide();

    for (const c of this.cars) {
      if (c.finishedAt !== null || c.gone) continue;
      const { s } = locate(c.x, c.y);
      let ds = s - c.lastS;
      if (ds < -TRACK.length / 2) ds += TRACK.length; // crossed the line forwards
      if (ds > TRACK.length / 2) ds -= TRACK.length; // crossed it backwards
      c.lastS = s;
      c.progress += ds;
      c.lap = Math.max(c.lap, Math.floor(c.progress / TRACK.length) + 1);
      if (c.progress >= LAPS * TRACK.length) {
        c.finishedAt = this.t;
        this.firstFinish ??= this.t;
      }
    }

    const racing = this.cars.filter((c) => !c.gone && c.finishedAt === null);
    if (racing.length === 0 || (this.firstFinish !== null && this.t - this.firstFinish > FINISH_GRACE) || this.t > MAX_SECONDS) {
      this.end();
    }
  }

  private move(c: Car, dt: number): void {
    c.x = clamp(c.x + Math.cos(c.a) * c.v * dt, CAR_R, FIELD.w - CAR_R);
    c.y = clamp(c.y + Math.sin(c.a) * c.v * dt, CAR_R, FIELD.h - CAR_R);
  }

  /** Cars are discs: overlapping ones are pushed apart and both lose a little speed. */
  private collide(): void {
    for (let i = 0; i < this.cars.length; i++) {
      for (let j = i + 1; j < this.cars.length; j++) {
        const a = this.cars[i];
        const b = this.cars[j];
        if (a.gone || b.gone) continue;
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
  private order(): Car[] {
    return [...this.cars].sort((a, b) =>
      Number(a.gone) - Number(b.gone)
      || (a.finishedAt ?? Infinity) - (b.finishedAt ?? Infinity)
      || b.progress - a.progress);
  }

  private end(): void {
    this.done = this.order().map((c, i) => ({
      id: c.p.id, name: c.p.name, place: i + 1,
      score: c.finishedAt !== null ? `${c.finishedAt.toFixed(1)}s` : c.gone ? '—' : `${Math.min(c.lap, LAPS)}/${LAPS}`,
    }));
  }

  finished(): GameResult[] | null {
    return this.done;
  }

  meta(): unknown {
    // Every 2nd sample is plenty to draw the road.
    return { half: HALF_WIDTH, track: TRACK.pts.filter((_, i) => i % 2 === 0).map((p) => [r1(p.x), r1(p.y)]) };
  }

  snapshot(): unknown {
    const order = this.order().map((c) => c.p.id);
    return {
      countdown: this.t < 0 ? Math.ceil(-this.t) : 0,
      time: r1(Math.max(0, this.t)),
      laps: LAPS,
      c: this.cars.map((c) => ({
        id: c.p.id, slot: c.p.slot, name: c.p.name, x: r1(c.x), y: r1(c.y), a: Math.round(c.a * 100) / 100,
        lap: Math.min(c.lap, LAPS), pos: order.indexOf(c.p.id) + 1, nitro: c.nitro > 0,
        nitroReady: c.nitroUsedLap !== c.lap, done: c.finishedAt !== null, gone: c.gone,
      })),
    };
  }
}

export const racing: GameDef = { key: 'racing', minPlayers: 1, maxPlayers: 8, create: (p) => new RacingGame(p) };
