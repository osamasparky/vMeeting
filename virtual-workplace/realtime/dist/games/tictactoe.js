"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.tictactoe = exports.TicTacToeGame = exports.LINES = exports.MOVE_SECONDS = exports.WINS_NEEDED = void 0;
const types_js_1 = require("./types.js");
/**
 * "إكس أو" — tic-tac-toe for two, first to WINS_NEEDED rounds (or the most
 * wins after MAX_ROUNDS). The starting player alternates each round; each
 * move has MOVE_SECONDS, and running out loses the round.
 */
exports.WINS_NEEDED = 3;
const MAX_ROUNDS = 9;
exports.MOVE_SECONDS = 15;
const BETWEEN = 2.2;
const SHOW_END = 3;
exports.LINES = [[0, 1, 2], [3, 4, 5], [6, 7, 8], [0, 3, 6], [1, 4, 7], [2, 5, 8], [0, 4, 8], [2, 4, 6]];
class TicTacToeGame {
    cells = new Array(9).fill('');
    p;
    wins = [0, 0];
    round = 1;
    /** Index into p of whoever moves now; X is whoever started the round. */
    turn = 0;
    starter = 0;
    clock = exports.MOVE_SECONDS;
    pause = 0;
    line = null;
    roundWinner = null; // -1 draw
    over = false;
    sinceEnd = 0;
    quitter = null;
    constructor(players) {
        this.p = players.slice(0, 2);
    }
    mark(i) {
        return i === this.starter ? 'X' : 'O';
    }
    input(_id, _i) { }
    action(id, data) {
        if (this.over || this.pause > 0)
            return;
        const i = this.p.findIndex((x) => x.id === id);
        const cell = (0, types_js_1.num)(data?.cell, -1);
        if (i !== this.turn || !Number.isInteger(cell) || cell < 0 || cell > 8 || this.cells[cell])
            return;
        this.cells[cell] = this.mark(i);
        const line = exports.LINES.find((l) => l.every((c) => this.cells[c] === this.mark(i)));
        if (line)
            return this.endRound(i, line);
        if (this.cells.every(Boolean))
            return this.endRound(-1, null);
        this.turn = 1 - this.turn;
        this.clock = exports.MOVE_SECONDS;
    }
    endRound(winner, line) {
        this.roundWinner = winner;
        this.line = line;
        if (winner >= 0)
            this.wins[winner]++;
        this.pause = BETWEEN;
        if (this.wins.some((w) => w >= exports.WINS_NEEDED) || this.round >= MAX_ROUNDS)
            this.over = true;
    }
    leave(id) {
        const i = this.p.findIndex((x) => x.id === id);
        if (i < 0 || this.over)
            return;
        this.quitter = i;
        this.over = true;
        this.sinceEnd = SHOW_END;
    }
    step(dt) {
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
                this.clock = exports.MOVE_SECONDS;
            }
            return;
        }
        this.clock -= dt;
        if (this.clock <= 0)
            this.endRound(1 - this.turn, null); // too slow: the other one takes the round
    }
    finished() {
        if (!this.over || this.sinceEnd < SHOW_END)
            return null;
        const order = this.quitter !== null ? [1 - this.quitter, this.quitter] : this.wins[1] > this.wins[0] ? [1, 0] : [0, 1];
        const tie = this.quitter === null && this.wins[0] === this.wins[1];
        return order.filter((i) => this.p[i]).map((i, k) => ({ id: this.p[i].id, name: this.p[i].name, place: tie ? 1 : k + 1, score: String(this.wins[i]) }));
    }
    snapshot() {
        return {
            cells: this.cells.join(','),
            players: this.p.map((x, i) => ({ id: x.id, name: x.name, slot: x.slot, mark: this.mark(i), wins: this.wins[i] })),
            turn: this.p[this.turn]?.id ?? null,
            clock: Math.max(0, Math.ceil(this.clock)),
            round: this.round,
            line: this.line,
            roundWinner: this.roundWinner === null ? null : this.roundWinner < 0 ? 'draw' : this.p[this.roundWinner].id,
            pause: this.pause > 0,
            over: this.over,
            need: exports.WINS_NEEDED,
        };
    }
}
exports.TicTacToeGame = TicTacToeGame;
exports.tictactoe = { key: 'tictactoe', minPlayers: 2, maxPlayers: 2, create: (p) => new TicTacToeGame(p) };
