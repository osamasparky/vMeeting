import { Game, GameDef, GameInput, GamePlayer, GameResult, num } from './types.js';

/**
 * "إكس أو" — tic-tac-toe for two, first to WINS_NEEDED rounds (or the most
 * wins after MAX_ROUNDS). The starting player alternates each round; each
 * move has MOVE_SECONDS, and running out loses the round.
 */

export const WINS_NEEDED = 3;
const MAX_ROUNDS = 9;
export const MOVE_SECONDS = 15;
const BETWEEN = 2.2;
const SHOW_END = 3;
export const LINES = [[0, 1, 2], [3, 4, 5], [6, 7, 8], [0, 3, 6], [1, 4, 7], [2, 5, 8], [0, 4, 8], [2, 4, 6]];

export class TicTacToeGame implements Game {
  cells: string[] = new Array(9).fill('');
  private p: GamePlayer[];
  private wins: number[] = [0, 0];
  private round = 1;
  /** Index into p of whoever moves now; X is whoever started the round. */
  private turn = 0;
  private starter = 0;
  private clock = MOVE_SECONDS;
  private pause = 0;
  private line: number[] | null = null;
  private roundWinner: number | null = null; // -1 draw
  private over = false;
  private sinceEnd = 0;
  private quitter: number | null = null;

  constructor(players: GamePlayer[]) {
    this.p = players.slice(0, 2);
  }

  private mark(i: number): string {
    return i === this.starter ? 'X' : 'O';
  }

  input(_id: string, _i: GameInput): void {}

  action(id: string, data: unknown): void {
    if (this.over || this.pause > 0) return;
    const i = this.p.findIndex((x) => x.id === id);
    const cell = num((data as any)?.cell, -1);
    if (i !== this.turn || !Number.isInteger(cell) || cell < 0 || cell > 8 || this.cells[cell]) return;
    this.cells[cell] = this.mark(i);
    const line = LINES.find((l) => l.every((c) => this.cells[c] === this.mark(i)));
    if (line) return this.endRound(i, line);
    if (this.cells.every(Boolean)) return this.endRound(-1, null);
    this.turn = 1 - this.turn;
    this.clock = MOVE_SECONDS;
  }

  private endRound(winner: number, line: number[] | null): void {
    this.roundWinner = winner;
    this.line = line;
    if (winner >= 0) this.wins[winner]++;
    this.pause = BETWEEN;
    if (this.wins.some((w) => w >= WINS_NEEDED) || this.round >= MAX_ROUNDS) this.over = true;
  }

  leave(id: string): void {
    const i = this.p.findIndex((x) => x.id === id);
    if (i < 0 || this.over) return;
    this.quitter = i;
    this.over = true;
    this.sinceEnd = SHOW_END;
  }

  step(dt: number): void {
    if (this.over) {
      this.pause = Math.max(0, this.pause - dt);
      this.sinceEnd += dt;
      return;
    }
    if (this.pause > 0) {
      this.pause -= dt;
      if (this.pause <= 0) {
        this.round++;
        this.starter = 1 - this.starter;
        this.turn = this.starter;
        this.cells.fill('');
        this.line = null;
        this.roundWinner = null;
        this.clock = MOVE_SECONDS;
      }
      return;
    }
    this.clock -= dt;
    if (this.clock <= 0) this.endRound(1 - this.turn, null); // too slow: the other one takes the round
  }

  finished(): GameResult[] | null {
    if (!this.over || this.sinceEnd < SHOW_END) return null;
    const order = this.quitter !== null ? [1 - this.quitter, this.quitter] : this.wins[1] > this.wins[0] ? [1, 0] : [0, 1];
    const tie = this.quitter === null && this.wins[0] === this.wins[1];
    return order.filter((i) => this.p[i]).map((i, k) => ({ id: this.p[i].id, name: this.p[i].name, place: tie ? 1 : k + 1, score: String(this.wins[i]) }));
  }

  snapshot(): unknown {
    return {
      cells: this.cells.join(',') ,
      players: this.p.map((x, i) => ({ id: x.id, name: x.name, slot: x.slot, mark: this.mark(i), wins: this.wins[i] })),
      turn: this.p[this.turn]?.id ?? null,
      clock: Math.max(0, Math.ceil(this.clock)),
      round: this.round,
      line: this.line,
      roundWinner: this.roundWinner === null ? null : this.roundWinner < 0 ? 'draw' : this.p[this.roundWinner].id,
      pause: this.pause > 0,
      over: this.over,
      need: WINS_NEEDED,
    };
  }
}

export const tictactoe: GameDef = { key: 'tictactoe', minPlayers: 2, maxPlayers: 2, create: (p) => new TicTacToeGame(p) };
