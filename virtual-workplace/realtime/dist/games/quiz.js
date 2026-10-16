"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.QuizGame = void 0;
exports.openTicket = openTicket;
exports.quizDef = quizDef;
const crypto_1 = __importDefault(require("crypto"));
const types_js_1 = require("./types.js");
const INTRO = 3;
const REVEAL = 5;
const MAX_QUESTIONS = 50;
const STREAK_STEP = 100;
const STREAK_MAX = 500;
/** Open a ticket sealed by the app. Null if forged, for another org, expired or malformed. */
function openTicket(ticket, secret, orgId, now = Date.now()) {
    if (typeof ticket !== 'string' || !secret || ticket.length > 200_000)
        return null;
    try {
        const raw = Buffer.from(ticket, 'base64');
        if (raw.length < 29)
            return null;
        const key = crypto_1.default.createHash('sha256').update(secret).digest();
        const decipher = crypto_1.default.createDecipheriv('aes-256-gcm', key, raw.subarray(0, 12));
        decipher.setAuthTag(raw.subarray(12, 28));
        const plain = Buffer.concat([decipher.update(raw.subarray(28)), decipher.final()]).toString('utf8');
        const d = JSON.parse(plain);
        if (d.org !== orgId || typeof d.exp !== 'number' || d.exp * 1000 < now)
            return null;
        const questions = (Array.isArray(d.questions) ? d.questions : [])
            .slice(0, MAX_QUESTIONS)
            .filter((x) => typeof x?.q === 'string' && Array.isArray(x.c) && x.c.length >= 2 && x.c.length <= 4
            && Number.isInteger(x.a) && x.a >= 0 && x.a < x.c.length)
            .map((x) => ({ q: String(x.q).slice(0, 300), c: x.c.map((c) => String(c).slice(0, 80)), a: x.a, s: Math.max(5, Math.min(60, (0, types_js_1.num)(x.s, 20))) }));
        return questions.length ? { title: String(d.title || '').slice(0, 120), questions } : null;
    }
    catch {
        return null;
    }
}
class QuizGame {
    quiz;
    seats;
    phase = 'intro';
    i = 0;
    t = 0; // seconds into the current phase
    answers = new Map();
    gained = new Map();
    done = null;
    constructor(players, quiz) {
        this.quiz = quiz;
        this.seats = players.map((p) => ({ p, score: 0, streak: 0, gone: false }));
    }
    get question() {
        return this.quiz.questions[this.i];
    }
    input(_id, _i) {
        // Answers come in through action().
    }
    action(id, data) {
        if (this.phase !== 'question' || this.answers.has(id))
            return;
        const seat = this.seats.find((s) => s.p.id === id && !s.gone);
        const choice = (0, types_js_1.num)(data?.choice, -1);
        if (!seat || !Number.isInteger(choice) || choice < 0 || choice >= this.question.c.length)
            return;
        this.answers.set(id, { choice, at: this.t });
    }
    leave(id) {
        const s = this.seats.find((x) => x.p.id === id);
        if (s)
            s.gone = true;
        if (this.seats.every((x) => x.gone))
            this.end();
    }
    step(dt) {
        if (this.done)
            return;
        this.t += dt;
        if (this.phase === 'intro' && this.t >= INTRO)
            return this.startQuestion();
        if (this.phase === 'question') {
            const present = this.seats.filter((s) => !s.gone);
            if (this.t >= this.question.s || present.every((s) => this.answers.has(s.p.id)))
                this.reveal();
            return;
        }
        if (this.phase === 'reveal' && this.t >= REVEAL) {
            if (this.i + 1 >= this.quiz.questions.length)
                return this.end();
            this.i++;
            this.startQuestion();
        }
    }
    startQuestion() {
        this.phase = 'question';
        this.t = 0;
        this.answers.clear();
        this.gained.clear();
    }
    reveal() {
        const q = this.question;
        for (const s of this.seats) {
            const ans = this.answers.get(s.p.id);
            let pts = 0;
            if (ans && ans.choice === q.a) {
                s.streak++;
                pts = Math.round(1000 * (1 - 0.5 * Math.min(1, ans.at / q.s))) + Math.min(STREAK_MAX, STREAK_STEP * (s.streak - 1));
            }
            else {
                s.streak = 0;
            }
            s.score += pts;
            this.gained.set(s.p.id, pts);
        }
        this.phase = 'reveal';
        this.t = 0;
    }
    end() {
        const ranked = [...this.seats].sort((a, b) => Number(a.gone) - Number(b.gone) || b.score - a.score);
        this.done = ranked.map((s, i) => ({ id: s.p.id, name: s.p.name, place: i + 1, score: String(s.score) }));
    }
    finished() {
        return this.done;
    }
    meta() {
        return { title: this.quiz.title, total: this.quiz.questions.length };
    }
    snapshot() {
        const q = this.question;
        const reveal = this.phase === 'reveal';
        return {
            phase: this.phase,
            title: this.quiz.title,
            i: this.i,
            n: this.quiz.questions.length,
            left: this.phase === 'question' ? Math.max(0, Math.ceil(q.s - this.t)) : this.phase === 'intro' ? Math.ceil(INTRO - this.t) : 0,
            limit: q.s,
            // The question shows during the intro too, so people can read ahead; answers stay hidden until the reveal.
            q: q.q,
            c: q.c,
            answered: [...this.answers.keys()],
            correct: reveal ? q.a : null,
            picks: reveal ? Object.fromEntries([...this.answers].map(([id, a]) => [id, a.choice])) : null,
            gained: reveal ? Object.fromEntries(this.gained) : null,
            scores: this.seats.filter((s) => !s.gone).map((s) => ({ id: s.p.id, name: s.p.name, slot: s.p.slot, score: s.score, streak: s.streak }))
                .sort((a, b) => b.score - a.score),
        };
    }
}
exports.QuizGame = QuizGame;
/** The quiz def needs the server secret to open tickets, so it is built at startup. */
function quizDef(secret) {
    return {
        key: 'quiz',
        minPlayers: 1,
        maxPlayers: 10,
        setup: (payload, user) => openTicket(payload?.ticket, secret, user.organizationId),
        create: (players, options) => new QuizGame(players, options),
    };
}
