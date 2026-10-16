import { Game, GameDef, GameInput, GamePlayer, GameResult, clamp, num, r1 } from './types.js';

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

export const FIELD = { w: 1000, h: 560 };
export const CLOTH = { x1: 60, y1: 60, x2: 940, y2: 500 };
export const BALL_R = 12;
export const POCKET_R = 25;
export const POCKETS = [
  { x: CLOTH.x1, y: CLOTH.y1 }, { x: 500, y: CLOTH.y1 - 4 }, { x: CLOTH.x2, y: CLOTH.y1 },
  { x: CLOTH.x1, y: CLOTH.y2 }, { x: 500, y: CLOTH.y2 + 4 }, { x: CLOTH.x2, y: CLOTH.y2 },
];
const CUE_SPOT = { x: 270, y: 280 };
const RACK_X = 700;
const FRICTION = 230; // px/s² of rolling slow-down
const MAX_SHOT = 1500;
const CUSHION = 0.82;
const SUBSTEPS = 6;
export const SHOT_CLOCK = 30;
const MAX_SECONDS = 8 * 60;

type Group = 'solids' | 'stripes';
const groupOf = (n: number): Group | null => (n >= 1 && n <= 7 ? 'solids' : n >= 9 && n <= 15 ? 'stripes' : null);

interface Ball { n: number; x: number; y: number; vx: number; vy: number; in: boolean }

export class PoolGame implements Game {
  balls: Ball[] = [];
  private players: GamePlayer[];
  private turn = 0;
  private groups: Record<string, Group | null> = {};
  private phase: 'aim' | 'rolling' = 'aim';
  private clock = SHOT_CLOCK;
  private t = 0;
  private firstHit: number | null = null;
  private potted: number[] = [];
  private message: string | null = 'break';
  private done: GameResult[] | null = null;

  constructor(players: GamePlayer[]) {
    this.players = players.slice(0, 2);
    for (const p of this.players) this.groups[p.id] = null;
    this.rack();
  }

  private rack(): void {
    this.balls = [{ n: 0, x: CUE_SPOT.x, y: CUE_SPOT.y, vx: 0, vy: 0, in: false }];
    // Standard triangle: 8 in the middle of the third row, a solid and a stripe in the back corners.
    const order = [1, 9, 2, 10, 8, 3, 11, 4, 12, 5, 13, 6, 14, 15, 7];
    let i = 0;
    for (let row = 0; row < 5; row++) {
      for (let k = 0; k <= row; k++) {
        const x = RACK_X + row * BALL_R * 1.75;
        const y = CUE_SPOT.y + (k - row / 2) * BALL_R * 2.02;
        this.balls.push({ n: order[i++], x, y, vx: 0, vy: 0, in: false });
      }
    }
  }

  private get shooter(): GamePlayer {
    return this.players[this.turn];
  }

  input(_id: string, _i: GameInput): void {
    // Pool is shot by action(); held keys are not used.
  }

  action(id: string, data: unknown): void {
    if (this.done || this.phase !== 'aim' || this.shooter.id !== id) return;
    const d = (data && typeof data === 'object' ? data : {}) as Record<string, unknown>;
    const angle = num(d.angle);
    const power = clamp(num(d.power), 0.05, 1);
    const cue = this.balls[0];
    cue.vx = Math.cos(angle) * power * MAX_SHOT;
    cue.vy = Math.sin(angle) * power * MAX_SHOT;
    this.phase = 'rolling';
    this.firstHit = null;
    this.potted = [];
    this.message = null;
  }

  leave(id: string): void {
    if (this.done) return;
    const stay = this.players.find((p) => p.id !== id);
    if (stay) this.win(stay.id);
  }

  step(dt: number): void {
    if (this.done) return;
    this.t += dt;
    if (this.t > MAX_SECONDS) return this.timeUp();

    if (this.phase === 'aim') {
      this.clock -= dt;
      if (this.clock <= 0) {
        this.message = 'timeout';
        this.pass();
      }
      return;
    }

    const h = dt / SUBSTEPS;
    for (let s = 0; s < SUBSTEPS; s++) this.physics(h);
    if (this.balls.every((b) => b.in || (b.vx === 0 && b.vy === 0))) this.resolve();
  }

  private physics(h: number): void {
    const live = this.balls.filter((b) => !b.in);
    for (const b of live) {
      const sp = Math.hypot(b.vx, b.vy);
      if (sp === 0) continue;
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
        if (d === 0 || d >= BALL_R * 2) continue;
        const nx = dx / d;
        const ny = dy / d;
        const overlap = BALL_R * 2 - d;
        a.x -= nx * overlap / 2;
        a.y -= ny * overlap / 2;
        b.x += nx * overlap / 2;
        b.y += ny * overlap / 2;
        const rel = (a.vx - b.vx) * nx + (a.vy - b.vy) * ny;
        if (rel <= 0) continue;
        // Equal masses, nearly elastic: swap the normal components.
        const imp = rel * 0.97;
        a.vx -= imp * nx;
        a.vy -= imp * ny;
        b.vx += imp * nx;
        b.vy += imp * ny;
        if (this.firstHit === null) {
          if (a.n === 0) this.firstHit = b.n;
          else if (b.n === 0) this.firstHit = a.n;
        }
      }
    }

    for (const b of live) {
      if (POCKETS.some((p) => Math.hypot(p.x - b.x, p.y - b.y) < POCKET_R)) {
        b.in = true;
        b.vx = b.vy = 0;
        this.potted.push(b.n);
        continue;
      }
      if (b.x < CLOTH.x1 + BALL_R) { b.x = CLOTH.x1 + BALL_R; b.vx = Math.abs(b.vx) * CUSHION; }
      if (b.x > CLOTH.x2 - BALL_R) { b.x = CLOTH.x2 - BALL_R; b.vx = -Math.abs(b.vx) * CUSHION; }
      if (b.y < CLOTH.y1 + BALL_R) { b.y = CLOTH.y1 + BALL_R; b.vy = Math.abs(b.vy) * CUSHION; }
      if (b.y > CLOTH.y2 - BALL_R) { b.y = CLOTH.y2 - BALL_R; b.vy = -Math.abs(b.vy) * CUSHION; }
    }
  }

  private remaining(g: Group): number {
    return this.balls.filter((b) => !b.in && groupOf(b.n) === g).length;
  }

  /** All balls stopped: apply the rules to what happened during the shot. */
  private resolve(): void {
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
      foul = wanted !== null ? this.firstHit !== 8 : groupOf(this.firstHit!) !== myGroup;
    }

    if (scratch) {
      const cue = this.balls[0];
      cue.in = false;
      cue.vx = cue.vy = 0;
      cue.x = CUE_SPOT.x;
      cue.y = CUE_SPOT.y;
      while (this.balls.some((b) => b !== cue && !b.in && Math.hypot(b.x - cue.x, b.y - cue.y) < BALL_R * 2)) cue.y += BALL_R * 2;
    }

    const pottedGroups = this.potted.map(groupOf).filter((g): g is Group => g !== null);
    if (!foul && myGroup === null && pottedGroups.length) {
      const g = pottedGroups[0];
      this.groups[me.id] = g;
      const o = this.other(me);
      if (o) this.groups[o.id] = g === 'solids' ? 'stripes' : 'solids';
    }

    const mine = this.groups[me.id];
    const keep = !foul && pottedGroups.some((g) => mine === null || g === mine);
    this.message = foul ? 'foul' : null;
    if (keep) {
      this.phase = 'aim';
      this.clock = SHOT_CLOCK;
    } else {
      this.pass();
    }
  }

  private other(p: GamePlayer): GamePlayer {
    return this.players.find((x) => x.id !== p.id) ?? p;
  }

  private pass(): void {
    if (this.players.length > 1) this.turn = 1 - this.turn;
    this.phase = 'aim';
    this.clock = SHOT_CLOCK;
  }

  private win(id: string): void {
    const winner = this.players.find((p) => p.id === id)!;
    const loser = this.players.find((p) => p.id !== id);
    this.done = [
      { id: winner.id, name: winner.name, place: 1, score: '🏆' },
      ...(loser ? [{ id: loser.id, name: loser.name, place: 2, score: '—' }] : []),
    ];
  }

  private timeUp(): void {
    const cleared = (p: GamePlayer) => {
      const g = this.groups[p.id];
      return g ? 7 - this.remaining(g) : 0;
    };
    const [a, b] = this.players;
    this.win(!b || cleared(a) >= cleared(b) ? a.id : b.id);
  }

  finished(): GameResult[] | null {
    return this.done;
  }

  snapshot(): unknown {
    return {
      turn: this.shooter.id,
      phase: this.phase,
      clock: Math.ceil(this.clock),
      message: this.message,
      groups: this.groups,
      players: this.players.map((p) => ({ id: p.id, name: p.name, slot: p.slot })),
      b: this.balls.filter((b) => !b.in).map((b) => [b.n, r1(b.x), r1(b.y)]),
    };
  }
}

export const pool: GameDef = { key: 'pool', minPlayers: 2, maxPlayers: 2, create: (p) => new PoolGame(p) };
