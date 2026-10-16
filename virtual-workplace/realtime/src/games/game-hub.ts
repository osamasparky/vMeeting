import { Game, GameDef, GameInput, GamePlayer, GameResult } from './types.js';

/** Who is asking: enough of the office user to seat them and reach them. */
export interface GameUser {
  userId: string;
  name: string;
  organizationId: string;
  mapId: string;
  isGuest?: boolean;
}

type Send = (userId: string, event: { type: string; payload: unknown }) => void;

interface Session {
  key: string;
  machineId: string;
  def: GameDef;
  status: 'lobby' | 'playing';
  players: GamePlayer[];
  /** Everyone with the machine's window open, seated or watching. */
  viewers: Map<string, string>;
  game: Game | null;
  lastResults: GameResult[] | null;
  startedAt: number;
}

export const TICK_HZ = 30;
/** Hard stop for any match, whatever the game says. */
const MAX_MATCH_SECONDS = 6 * 60;

const MACHINE_ID = /^[\w:.-]{1,80}$/;

/**
 * One session per game machine on a map. People open the machine (watch),
 * take a seat, and the first seated player starts the match; the hub then
 * steps the game TICK_HZ times a second and sends every viewer the frame.
 * Transport-free: it is handed a send function, and tests drive tick().
 */
export class GameHub {
  private sessions = new Map<string, Session>();
  /** userId → session key: a person is at one machine at a time. */
  private at = new Map<string, string>();

  constructor(private readonly defs: Record<string, GameDef>, private readonly send: Send) {}

  handle(user: GameUser, type: string, payload: any): void {
    if (user.isGuest || !user.mapId) return;
    switch (type) {
      case 'game.open':
        return this.open(user, payload?.machineId, payload?.game);
      case 'game.close':
        return this.removeUser(user.userId);
      case 'game.sit':
        return this.sit(user);
      case 'game.stand':
        return this.stand(user.userId);
      case 'game.start':
        return this.start(user.userId);
      case 'game.input':
        return this.input(user.userId, payload?.input);
      case 'game.action':
        return this.action(user.userId, payload?.data);
    }
  }

  /** Disconnects, leaving the map, or closing the window. */
  removeUser(userId: string): void {
    const s = this.sessionOf(userId);
    this.at.delete(userId);
    if (!s) return;
    s.viewers.delete(userId);
    this.dropPlayer(s, userId);
    if (s.viewers.size === 0) {
      this.sessions.delete(s.key);
      return;
    }
    this.broadcastState(s);
  }

  /** Advance every running match; called TICK_HZ times a second. */
  tick(dt = 1 / TICK_HZ): void {
    for (const s of this.sessions.values()) {
      if (s.status !== 'playing' || !s.game) continue;
      s.game.step(dt);
      const frame = { type: 'game.frame', payload: { machineId: s.machineId, s: s.game.snapshot() } };
      for (const id of s.viewers.keys()) this.send(id, frame);

      const timedOut = (Date.now() - s.startedAt) / 1000 > MAX_MATCH_SECONDS;
      const results = s.game.finished() ?? (timedOut ? this.forfeitAll(s) : null);
      if (results) this.finish(s, results);
    }
  }

  sessionCount(): number {
    return this.sessions.size;
  }

  // ── internals ──

  private open(user: GameUser, machineId: unknown, gameKey: unknown): void {
    if (typeof machineId !== 'string' || !MACHINE_ID.test(machineId) || typeof gameKey !== 'string') return;
    const def = this.defs[gameKey];
    if (!def) return;

    const key = `${user.organizationId}:${user.mapId}:${machineId}`;
    const current = this.at.get(user.userId);
    if (current && current !== key) this.removeUser(user.userId);

    let s = this.sessions.get(key);
    if (!s) {
      s = { key, machineId, def, status: 'lobby', players: [], viewers: new Map(), game: null, lastResults: null, startedAt: 0 };
      this.sessions.set(key, s);
    }
    s.viewers.set(user.userId, user.name);
    this.at.set(user.userId, key);
    this.broadcastState(s);
  }

  private sit(user: GameUser): void {
    const s = this.sessionOf(user.userId);
    if (!s || s.status !== 'lobby' || s.players.some((p) => p.id === user.userId)) return;
    if (s.players.length >= s.def.maxPlayers) return;
    const taken = new Set(s.players.map((p) => p.slot));
    let slot = 0;
    while (taken.has(slot)) slot++;
    s.players.push({ id: user.userId, name: user.name, slot });
    this.broadcastState(s);
  }

  private stand(userId: string): void {
    const s = this.sessionOf(userId);
    if (!s) return;
    this.dropPlayer(s, userId);
    this.broadcastState(s);
  }

  /** The host is the first person seated; they start once enough have sat down. */
  private start(userId: string): void {
    const s = this.sessionOf(userId);
    if (!s || s.status !== 'lobby' || s.players[0]?.id !== userId) return;
    if (s.players.length < s.def.minPlayers) return;
    s.game = s.def.create(s.players.map((p) => ({ ...p })));
    s.status = 'playing';
    s.startedAt = Date.now();
    s.lastResults = null;
    this.broadcastState(s);
  }

  private input(userId: string, raw: unknown): void {
    const s = this.sessionOf(userId);
    if (!s?.game || s.status !== 'playing' || !s.players.some((p) => p.id === userId)) return;
    const i = (raw && typeof raw === 'object' ? raw : {}) as Record<string, unknown>;
    const clean: GameInput = {
      up: i.up === true, down: i.down === true, left: i.left === true, right: i.right === true, a: i.a === true, b: i.b === true,
    };
    s.game.input(userId, clean);
  }

  private action(userId: string, data: unknown): void {
    const s = this.sessionOf(userId);
    if (!s?.game?.action || s.status !== 'playing' || !s.players.some((p) => p.id === userId)) return;
    s.game.action(userId, data);
  }

  private dropPlayer(s: Session, userId: string): void {
    if (!s.players.some((p) => p.id === userId)) return;
    if (s.status === 'playing' && s.game) {
      s.game.leave(userId);
      const results = s.game.finished();
      s.players = s.players.filter((p) => p.id !== userId);
      if (results) this.finish(s, results, false);
      else if (s.players.length === 0) this.finish(s, [], false);
      return;
    }
    s.players = s.players.filter((p) => p.id !== userId);
  }

  private finish(s: Session, results: GameResult[], announce = true): void {
    s.status = 'lobby';
    s.game = null;
    s.lastResults = results;
    // Seats stay taken so the same group can go again with one press.
    if (announce || s.viewers.size) this.broadcastState(s);
  }

  private forfeitAll(s: Session): GameResult[] {
    return s.players.map((p, i) => ({ id: p.id, name: p.name, place: i + 1, score: '—' }));
  }

  private sessionOf(userId: string): Session | undefined {
    const key = this.at.get(userId);
    return key ? this.sessions.get(key) : undefined;
  }

  private broadcastState(s: Session): void {
    const payload = {
      machineId: s.machineId,
      game: s.def.key,
      status: s.status,
      min: s.def.minPlayers,
      max: s.def.maxPlayers,
      players: s.players.map((p) => ({ id: p.id, name: p.name, slot: p.slot })),
      hostId: s.players[0]?.id ?? null,
      watching: [...s.viewers.entries()].filter(([id]) => !s.players.some((p) => p.id === id)).map(([id, name]) => ({ id, name })),
      lastResults: s.lastResults,
      meta: s.game?.meta?.() ?? null,
    };
    for (const id of s.viewers.keys()) this.send(id, { type: 'game.state', payload });
  }
}
