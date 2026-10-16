import { Game, GameDef, GameInput, GamePlayer, GameResult, num } from './types.js';
import { Color, Move, Position, START_FEN, apply, fromFen, inCheck, insufficientMaterial, kingSquare, legalMoves, positionKey } from './chess-rules.js';

/**
 * "شطرنج" — chess for two with a 5-minute clock each (blitz, so a game stays
 * short). First seat plays white. The server checks every move; clients get
 * the legal moves to highlight. Ends by mate, stalemate, the automatic draws,
 * time, resignation or an accepted draw offer. The final position stays up
 * for SHOW_END seconds before the lobby returns.
 */

export const CLOCK_SECONDS = 5 * 60;
const SHOW_END = 5;

type Outcome = { winner: Color | null; reason: 'mate' | 'stalemate' | 'material' | 'fifty' | 'repetition' | 'time' | 'resign' | 'agreed' | 'left' };

export class ChessGame implements Game {
  pos: Position;
  private sides: Record<Color, GamePlayer>;
  private clocks: Record<Color, number> = { w: CLOCK_SECONDS, b: CLOCK_SECONDS };
  private seen = new Map<string, number>();
  private legal: Move[];
  private last: [number, number] | null = null;
  private drawOffer: Color | null = null;
  private outcome: Outcome | null = null;
  private sinceEnd = 0;
  private moves = 0;

  constructor(players: GamePlayer[], fen = START_FEN) {
    this.sides = { w: players[0], b: players[1] ?? players[0] };
    this.pos = fromFen(fen);
    this.legal = legalMoves(this.pos);
    this.seen.set(positionKey(this.pos), 1);
  }

  private colorOf(id: string): Color | null {
    return this.sides.w.id === id ? 'w' : this.sides.b.id === id ? 'b' : null;
  }

  input(_id: string, _i: GameInput): void {
    // Moves come through action().
  }

  action(id: string, data: unknown): void {
    if (this.outcome) return;
    const me = this.colorOf(id);
    if (!me) return;
    const d = (data && typeof data === 'object' ? data : {}) as Record<string, unknown>;
    const them: Color = me === 'w' ? 'b' : 'w';

    if (d.resign === true) return this.end({ winner: them, reason: 'resign' });
    if (d.draw === 'offer') { this.drawOffer = me; return; }
    if (d.draw === 'accept' && this.drawOffer === them) return this.end({ winner: null, reason: 'agreed' });
    if (d.draw === 'decline' && this.drawOffer === them) { this.drawOffer = null; return; }

    if (me !== this.pos.turn) return;
    const from = num(d.from, -1);
    const to = num(d.to, -1);
    const promo = typeof d.promo === 'string' && 'qrbn'.includes(d.promo) && d.promo.length === 1 ? (d.promo as Move['promo']) : 'q';
    const move = this.legal.find((m) => m.from === from && m.to === to && (!m.promo || m.promo === promo));
    if (!move) return;

    this.pos = apply(this.pos, move);
    this.last = [move.from, move.to];
    this.moves++;
    // An offer stands until the other side moves (declining by playing on).
    if (this.drawOffer === them) this.drawOffer = null;
    const key = positionKey(this.pos);
    this.seen.set(key, (this.seen.get(key) ?? 0) + 1);
    this.legal = legalMoves(this.pos);

    if (this.legal.length === 0) return this.end(inCheck(this.pos) ? { winner: me, reason: 'mate' } : { winner: null, reason: 'stalemate' });
    if (insufficientMaterial(this.pos)) return this.end({ winner: null, reason: 'material' });
    if (this.pos.halfmove >= 100) return this.end({ winner: null, reason: 'fifty' });
    if ((this.seen.get(key) ?? 0) >= 3) return this.end({ winner: null, reason: 'repetition' });
  }

  leave(id: string): void {
    const me = this.colorOf(id);
    if (!me || this.outcome) return;
    this.end({ winner: me === 'w' ? 'b' : 'w', reason: 'left' });
    this.sinceEnd = SHOW_END; // nobody to show it to: finish now
  }

  step(dt: number): void {
    if (this.outcome) {
      this.sinceEnd += dt;
      return;
    }
    // The clock only runs once white has made the first move.
    if (this.moves === 0) return;
    const side = this.pos.turn;
    this.clocks[side] = Math.max(0, this.clocks[side] - dt);
    if (this.clocks[side] === 0) this.end({ winner: side === 'w' ? 'b' : 'w', reason: 'time' });
  }

  private end(o: Outcome): void {
    this.outcome = o;
    this.drawOffer = null;
  }

  finished(): GameResult[] | null {
    if (!this.outcome || this.sinceEnd < SHOW_END) return null;
    const { winner } = this.outcome;
    const order: Color[] = winner === 'b' ? ['b', 'w'] : ['w', 'b'];
    const label = (c: Color) => (winner === null ? '½' : winner === c ? '1' : '0');
    return order.filter((c, i) => i === 0 || this.sides[c].id !== this.sides[order[0]].id)
      .map((c, i) => ({ id: this.sides[c].id, name: this.sides[c].name, place: winner === null ? 1 : i + 1, score: label(c) }));
  }

  snapshot(): unknown {
    return {
      board: this.pos.board.join(''),
      turn: this.pos.turn,
      white: { id: this.sides.w.id, name: this.sides.w.name, slot: this.sides.w.slot },
      black: { id: this.sides.b.id, name: this.sides.b.name, slot: this.sides.b.slot },
      clocks: { w: Math.ceil(this.clocks.w), b: Math.ceil(this.clocks.b) },
      legal: this.outcome ? [] : this.legal.filter((m) => !m.promo || m.promo === 'q').map((m) => [m.from, m.to, m.promo ? 1 : 0]),
      last: this.last,
      check: inCheck(this.pos) ? kingSquare(this.pos, this.pos.turn) : -1,
      drawOffer: this.drawOffer,
      moves: this.moves,
      outcome: this.outcome,
    };
  }
}

export const chess: GameDef = { key: 'chess', minPlayers: 2, maxPlayers: 2, create: (p) => new ChessGame(p) };
