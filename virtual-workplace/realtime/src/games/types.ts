/**
 * Office arcade: short multiplayer games played at a game machine placed on
 * the map. The server referees every game (clients only send their keys), so
 * everyone at the machine sees the same thing and nobody can cheat locally.
 */

export interface GamePlayer {
  id: string;
  name: string;
  /** Seat number from 0, fixed for the match: decides colour and start spot. */
  slot: number;
}

/** Held keys, sent on change. `a` is the action key, `b` the second one. */
export interface GameInput {
  up?: boolean;
  down?: boolean;
  left?: boolean;
  right?: boolean;
  a?: boolean;
  b?: boolean;
}

export interface GameResult {
  id: string;
  name: string;
  place: number;
  /** What the game counts: rounds won, laps time, knock-outs… shown as-is. */
  score: string;
}

export interface Game {
  /** Advance the simulation by dt seconds. */
  step(dt: number): void;
  input(playerId: string, input: GameInput): void;
  /** One-off moves that are not held keys (a pool shot). */
  action?(playerId: string, data: unknown): void;
  /** What clients draw; kept small, it goes out every tick. */
  snapshot(): unknown;
  /** Fixed data for the whole match (a track's shape), sent once with the state. */
  meta?(): unknown;
  /** Final standings once the match is over, otherwise null. */
  finished(): GameResult[] | null;
  /** A player walked away or disconnected mid-match. */
  leave(playerId: string): void;
}

/** Who is asking: enough of the office user to seat them and reach them. */
export interface GameUser {
  userId: string;
  name: string;
  organizationId: string;
  mapId: string;
  isGuest?: boolean;
}

export interface GameDef {
  key: string;
  minPlayers: number;
  maxPlayers: number;
  /**
   * Games that need something from the host to start (the quiz's sealed
   * questions) check it here; null refuses the start.
   */
  setup?(payload: unknown, host: GameUser): unknown | null;
  create(players: GamePlayer[], options?: unknown): Game;
}

export const clamp = (v: number, lo: number, hi: number): number => Math.max(lo, Math.min(hi, v));

export const num = (v: unknown, fallback = 0): number => (typeof v === 'number' && Number.isFinite(v) ? v : fallback);

/** Round for the wire: positions don't need more than 0.1 px. */
export const r1 = (v: number): number => Math.round(v * 10) / 10;
