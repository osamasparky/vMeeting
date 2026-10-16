"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.chess = exports.ChessGame = exports.CLOCK_SECONDS = void 0;
const types_js_1 = require("./types.js");
const chess_rules_js_1 = require("./chess-rules.js");
/**
 * "شطرنج" — chess for two with a 5-minute clock each (blitz, so a game stays
 * short). First seat plays white. The server checks every move; clients get
 * the legal moves to highlight. Ends by mate, stalemate, the automatic draws,
 * time, resignation or an accepted draw offer. The final position stays up
 * for SHOW_END seconds before the lobby returns.
 */
exports.CLOCK_SECONDS = 5 * 60;
const SHOW_END = 5;
class ChessGame {
    pos;
    sides;
    clocks = { w: exports.CLOCK_SECONDS, b: exports.CLOCK_SECONDS };
    seen = new Map();
    legal;
    last = null;
    drawOffer = null;
    outcome = null;
    sinceEnd = 0;
    moves = 0;
    constructor(players, fen = chess_rules_js_1.START_FEN) {
        this.sides = { w: players[0], b: players[1] ?? players[0] };
        this.pos = (0, chess_rules_js_1.fromFen)(fen);
        this.legal = (0, chess_rules_js_1.legalMoves)(this.pos);
        this.seen.set((0, chess_rules_js_1.positionKey)(this.pos), 1);
    }
    colorOf(id) {
        return this.sides.w.id === id ? 'w' : this.sides.b.id === id ? 'b' : null;
    }
    input(_id, _i) {
        // Moves come through action().
    }
    action(id, data) {
        if (this.outcome)
            return;
        const me = this.colorOf(id);
        if (!me)
            return;
        const d = (data && typeof data === 'object' ? data : {});
        const them = me === 'w' ? 'b' : 'w';
        if (d.resign === true)
            return this.end({ winner: them, reason: 'resign' });
        if (d.draw === 'offer') {
            this.drawOffer = me;
            return;
        }
        if (d.draw === 'accept' && this.drawOffer === them)
            return this.end({ winner: null, reason: 'agreed' });
        if (d.draw === 'decline' && this.drawOffer === them) {
            this.drawOffer = null;
            return;
        }
        if (me !== this.pos.turn)
            return;
        const from = (0, types_js_1.num)(d.from, -1);
        const to = (0, types_js_1.num)(d.to, -1);
        const promo = typeof d.promo === 'string' && 'qrbn'.includes(d.promo) && d.promo.length === 1 ? d.promo : 'q';
        const move = this.legal.find((m) => m.from === from && m.to === to && (!m.promo || m.promo === promo));
        if (!move)
            return;
        this.pos = (0, chess_rules_js_1.apply)(this.pos, move);
        this.last = [move.from, move.to];
        this.moves++;
        // An offer stands until the other side moves (declining by playing on).
        if (this.drawOffer === them)
            this.drawOffer = null;
        const key = (0, chess_rules_js_1.positionKey)(this.pos);
        this.seen.set(key, (this.seen.get(key) ?? 0) + 1);
        this.legal = (0, chess_rules_js_1.legalMoves)(this.pos);
        if (this.legal.length === 0)
            return this.end((0, chess_rules_js_1.inCheck)(this.pos) ? { winner: me, reason: 'mate' } : { winner: null, reason: 'stalemate' });
        if ((0, chess_rules_js_1.insufficientMaterial)(this.pos))
            return this.end({ winner: null, reason: 'material' });
        if (this.pos.halfmove >= 100)
            return this.end({ winner: null, reason: 'fifty' });
        if ((this.seen.get(key) ?? 0) >= 3)
            return this.end({ winner: null, reason: 'repetition' });
    }
    leave(id) {
        const me = this.colorOf(id);
        if (!me || this.outcome)
            return;
        this.end({ winner: me === 'w' ? 'b' : 'w', reason: 'left' });
        this.sinceEnd = SHOW_END; // nobody to show it to: finish now
    }
    step(dt) {
        if (this.outcome) {
            this.sinceEnd += dt;
            return;
        }
        // The clock only runs once white has made the first move.
        if (this.moves === 0)
            return;
        const side = this.pos.turn;
        this.clocks[side] = Math.max(0, this.clocks[side] - dt);
        if (this.clocks[side] === 0)
            this.end({ winner: side === 'w' ? 'b' : 'w', reason: 'time' });
    }
    end(o) {
        this.outcome = o;
        this.drawOffer = null;
    }
    finished() {
        if (!this.outcome || this.sinceEnd < SHOW_END)
            return null;
        const { winner } = this.outcome;
        const order = winner === 'b' ? ['b', 'w'] : ['w', 'b'];
        const label = (c) => (winner === null ? '½' : winner === c ? '1' : '0');
        return order.filter((c, i) => i === 0 || this.sides[c].id !== this.sides[order[0]].id)
            .map((c, i) => ({ id: this.sides[c].id, name: this.sides[c].name, place: winner === null ? 1 : i + 1, score: label(c) }));
    }
    snapshot() {
        return {
            board: this.pos.board.join(''),
            turn: this.pos.turn,
            white: { id: this.sides.w.id, name: this.sides.w.name, slot: this.sides.w.slot },
            black: { id: this.sides.b.id, name: this.sides.b.name, slot: this.sides.b.slot },
            clocks: { w: Math.ceil(this.clocks.w), b: Math.ceil(this.clocks.b) },
            legal: this.outcome ? [] : this.legal.filter((m) => !m.promo || m.promo === 'q').map((m) => [m.from, m.to, m.promo ? 1 : 0]),
            last: this.last,
            check: (0, chess_rules_js_1.inCheck)(this.pos) ? (0, chess_rules_js_1.kingSquare)(this.pos, this.pos.turn) : -1,
            drawOffer: this.drawOffer,
            moves: this.moves,
            outcome: this.outcome,
        };
    }
}
exports.ChessGame = ChessGame;
exports.chess = { key: 'chess', minPlayers: 2, maxPlayers: 2, create: (p) => new ChessGame(p) };
