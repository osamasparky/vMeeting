"use strict";
/**
 * Chess rules: legal moves (castling, en passant, promotion), check, mate,
 * stalemate and the automatic draws (50-move rule, threefold repetition,
 * insufficient material). Board squares 0..63, a1 = 0, h1 = 7, a8 = 56.
 * Pieces: "PNBRQK" white, "pnbrqk" black, "." empty.
 */
Object.defineProperty(exports, "__esModule", { value: true });
exports.sqName = exports.START_FEN = void 0;
exports.fromFen = fromFen;
exports.positionKey = positionKey;
exports.attacked = attacked;
exports.kingSquare = kingSquare;
exports.inCheck = inCheck;
exports.apply = apply;
exports.legalMoves = legalMoves;
exports.insufficientMaterial = insufficientMaterial;
exports.perft = perft;
exports.START_FEN = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';
const file = (sq) => sq & 7;
const rank = (sq) => sq >> 3;
const colorOf = (p) => (p === '.' ? null : p === p.toUpperCase() ? 'w' : 'b');
const sqName = (sq) => 'abcdefgh'[file(sq)] + (rank(sq) + 1);
exports.sqName = sqName;
const parseSq = (s) => (s === '-' ? -1 : s.charCodeAt(0) - 97 + (Number(s[1]) - 1) * 8);
function fromFen(fen) {
    const [placement, turn, castling, ep, half, full] = fen.split(' ');
    const board = new Array(64).fill('.');
    placement.split('/').forEach((row, i) => {
        let f = 0;
        for (const ch of row) {
            if (/\d/.test(ch))
                f += Number(ch);
            else
                board[(7 - i) * 8 + f++] = ch;
        }
    });
    return { board, turn: turn, castling: castling === '-' ? '' : castling, ep: parseSq(ep), halfmove: Number(half || 0), fullmove: Number(full || 1) };
}
/** Placement + side + castling + en passant: what counts for repetition. */
function positionKey(p) {
    return p.board.join('') + p.turn + p.castling + p.ep;
}
const KNIGHT = [[1, 2], [2, 1], [2, -1], [1, -2], [-1, -2], [-2, -1], [-2, 1], [-1, 2]];
const KING = [[1, 0], [1, 1], [0, 1], [-1, 1], [-1, 0], [-1, -1], [0, -1], [1, -1]];
const ROOK_DIRS = [[1, 0], [-1, 0], [0, 1], [0, -1]];
const BISHOP_DIRS = [[1, 1], [1, -1], [-1, 1], [-1, -1]];
function at(f, r) {
    return f >= 0 && f < 8 && r >= 0 && r < 8 ? r * 8 + f : -1;
}
/** Is square sq attacked by side `by`? */
function attacked(p, sq, by) {
    const b = p.board;
    const f = file(sq);
    const r = rank(sq);
    const own = (ch) => (by === 'w' ? ch.toUpperCase() : ch.toLowerCase());
    // Pawns attack diagonally forward (from their side's point of view).
    const pr = by === 'w' ? r - 1 : r + 1;
    for (const df of [-1, 1]) {
        const s = at(f + df, pr);
        if (s >= 0 && b[s] === own('p'))
            return true;
    }
    for (const [df, dr] of KNIGHT) {
        const s = at(f + df, r + dr);
        if (s >= 0 && b[s] === own('n'))
            return true;
    }
    for (const [df, dr] of KING) {
        const s = at(f + df, r + dr);
        if (s >= 0 && b[s] === own('k'))
            return true;
    }
    for (const [dirs, pieces] of [[ROOK_DIRS, ['r', 'q']], [BISHOP_DIRS, ['b', 'q']]]) {
        for (const [df, dr] of dirs) {
            for (let i = 1; i < 8; i++) {
                const s = at(f + df * i, r + dr * i);
                if (s < 0)
                    break;
                if (b[s] !== '.') {
                    if (pieces.some((x) => b[s] === own(x)))
                        return true;
                    break;
                }
            }
        }
    }
    return false;
}
function kingSquare(p, c) {
    return p.board.indexOf(c === 'w' ? 'K' : 'k');
}
function inCheck(p, c = p.turn) {
    return attacked(p, kingSquare(p, c), c === 'w' ? 'b' : 'w');
}
function pseudoMoves(p) {
    const out = [];
    const b = p.board;
    const me = p.turn;
    const them = me === 'w' ? 'b' : 'w';
    for (let sq = 0; sq < 64; sq++) {
        const pc = b[sq];
        if (colorOf(pc) !== me)
            continue;
        const f = file(sq);
        const r = rank(sq);
        const kind = pc.toLowerCase();
        if (kind === 'p') {
            const dir = me === 'w' ? 1 : -1;
            const startRank = me === 'w' ? 1 : 6;
            const lastRank = me === 'w' ? 7 : 0;
            const push = (to, extra = {}) => {
                if (rank(to) === lastRank)
                    for (const promo of ['q', 'r', 'b', 'n'])
                        out.push({ from: sq, to, promo, ...extra });
                else
                    out.push({ from: sq, to, ...extra });
            };
            const one = at(f, r + dir);
            if (one >= 0 && b[one] === '.') {
                push(one);
                const two = at(f, r + 2 * dir);
                if (r === startRank && b[two] === '.')
                    out.push({ from: sq, to: two });
            }
            for (const df of [-1, 1]) {
                const t = at(f + df, r + dir);
                if (t < 0)
                    continue;
                if (colorOf(b[t]) === them)
                    push(t);
                else if (t === p.ep)
                    out.push({ from: sq, to: t, ep: true });
            }
            continue;
        }
        if (kind === 'n' || kind === 'k') {
            for (const [df, dr] of kind === 'n' ? KNIGHT : KING) {
                const t = at(f + df, r + dr);
                if (t >= 0 && colorOf(b[t]) !== me)
                    out.push({ from: sq, to: t });
            }
            if (kind === 'k') {
                // Castling: rights, empty squares between, and not out of / through / into check.
                const home = me === 'w' ? 0 : 56;
                if (sq === home + 4 && !attacked(p, sq, them)) {
                    const [ks, qs] = me === 'w' ? ['K', 'Q'] : ['k', 'q'];
                    if (p.castling.includes(ks) && b[home + 5] === '.' && b[home + 6] === '.'
                        && !attacked(p, home + 5, them) && !attacked(p, home + 6, them))
                        out.push({ from: sq, to: home + 6, castle: true });
                    if (p.castling.includes(qs) && b[home + 3] === '.' && b[home + 2] === '.' && b[home + 1] === '.'
                        && !attacked(p, home + 3, them) && !attacked(p, home + 2, them))
                        out.push({ from: sq, to: home + 2, castle: true });
                }
            }
            continue;
        }
        const dirs = kind === 'r' ? ROOK_DIRS : kind === 'b' ? BISHOP_DIRS : [...ROOK_DIRS, ...BISHOP_DIRS];
        for (const [df, dr] of dirs) {
            for (let i = 1; i < 8; i++) {
                const t = at(f + df * i, r + dr * i);
                if (t < 0)
                    break;
                const c = colorOf(b[t]);
                if (c === me)
                    break;
                out.push({ from: sq, to: t });
                if (c === them)
                    break;
            }
        }
    }
    return out;
}
/** The position after a move (no legality check). */
function apply(p, m) {
    const b = p.board.slice();
    const pc = b[m.from];
    const kind = pc.toLowerCase();
    const captured = b[m.to];
    b[m.to] = m.promo ? (p.turn === 'w' ? m.promo.toUpperCase() : m.promo) : pc;
    b[m.from] = '.';
    if (m.ep)
        b[m.to + (p.turn === 'w' ? -8 : 8)] = '.';
    if (m.castle) {
        const home = p.turn === 'w' ? 0 : 56;
        if (m.to === home + 6) {
            b[home + 5] = b[home + 7];
            b[home + 7] = '.';
        }
        else {
            b[home + 3] = b[home];
            b[home] = '.';
        }
    }
    let castling = p.castling;
    const strip = (chars) => { for (const ch of chars)
        castling = castling.replace(ch, ''); };
    if (kind === 'k')
        strip(p.turn === 'w' ? 'KQ' : 'kq');
    for (const sq of [m.from, m.to]) {
        if (sq === 0)
            strip('Q');
        if (sq === 7)
            strip('K');
        if (sq === 56)
            strip('q');
        if (sq === 63)
            strip('k');
    }
    const ep = kind === 'p' && Math.abs(m.to - m.from) === 16 ? (m.from + m.to) / 2 : -1;
    return {
        board: b,
        turn: p.turn === 'w' ? 'b' : 'w',
        castling,
        ep,
        halfmove: kind === 'p' || captured !== '.' || m.ep ? 0 : p.halfmove + 1,
        fullmove: p.fullmove + (p.turn === 'b' ? 1 : 0),
    };
}
function legalMoves(p) {
    return pseudoMoves(p).filter((m) => !inCheck(apply(p, m), p.turn));
}
/** Neither side can possibly mate: K v K, K+minor v K, or bishops all on one colour. */
function insufficientMaterial(p) {
    const pieces = p.board.map((pc, sq) => ({ pc, sq })).filter((x) => x.pc !== '.' && x.pc.toLowerCase() !== 'k');
    if (pieces.length === 0)
        return true;
    if (pieces.some((x) => 'pqrPQR'.includes(x.pc)))
        return false;
    if (pieces.length === 1)
        return true; // a lone knight or bishop
    return pieces.every((x) => x.pc.toLowerCase() === 'b')
        && new Set(pieces.map((x) => (file(x.sq) + rank(x.sq)) % 2)).size === 1;
}
/** Count leaf positions to a depth: the standard way to prove a move generator correct. */
function perft(p, depth) {
    if (depth === 0)
        return 1;
    const moves = legalMoves(p);
    if (depth === 1)
        return moves.length;
    let n = 0;
    for (const m of moves)
        n += perft(apply(p, m), depth - 1);
    return n;
}
