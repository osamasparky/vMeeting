import crypto from 'crypto';
import { Game, GameDef, GameInput, GamePlayer, GameResult, num } from './types.js';

/**
 * "سؤال وجواب" — Kahoot-style quiz at the office quiz machine, 1–10 players.
 * The host picks a quiz; the app hands them a sealed ticket (see
 * App\Domains\Workspace\Games\QuizTicket) that only this server can open, so
 * the answers never reach a browser before the reveal.
 *
 * Each question: everyone picks one of 2–4 answers before time runs out.
 * A right answer scores up to 1000 — the faster, the more (never below 500)
 * — plus a streak bonus for consecutive right answers. Then the right answer
 * and the standings show for a few seconds, and on to the next question.
 */

export interface QuizQuestion { q: string; c: string[]; a: number; s: number }
export interface QuizData { title: string; questions: QuizQuestion[] }

const INTRO = 3;
const REVEAL = 5;
const MAX_QUESTIONS = 50;
const STREAK_STEP = 100;
const STREAK_MAX = 500;

/** Open a ticket sealed by the app. Null if forged, for another org, expired or malformed. */
export function openTicket(ticket: unknown, secret: string | undefined, orgId: string, now = Date.now()): QuizData | null {
  if (typeof ticket !== 'string' || !secret || ticket.length > 200_000) return null;
  try {
    const raw = Buffer.from(ticket, 'base64');
    if (raw.length < 29) return null;
    const key = crypto.createHash('sha256').update(secret).digest();
    const decipher = crypto.createDecipheriv('aes-256-gcm', key, raw.subarray(0, 12));
    decipher.setAuthTag(raw.subarray(12, 28));
    const plain = Buffer.concat([decipher.update(raw.subarray(28)), decipher.final()]).toString('utf8');
    const d = JSON.parse(plain);
    if (d.org !== orgId || typeof d.exp !== 'number' || d.exp * 1000 < now) return null;
    const questions: QuizQuestion[] = (Array.isArray(d.questions) ? d.questions : [])
      .slice(0, MAX_QUESTIONS)
      .filter((x: any) => typeof x?.q === 'string' && Array.isArray(x.c) && x.c.length >= 2 && x.c.length <= 4
        && Number.isInteger(x.a) && x.a >= 0 && x.a < x.c.length)
      .map((x: any) => ({ q: String(x.q).slice(0, 300), c: x.c.map((c: unknown) => String(c).slice(0, 80)), a: x.a, s: Math.max(5, Math.min(60, num(x.s, 20))) }));
    return questions.length ? { title: String(d.title || '').slice(0, 120), questions } : null;
  } catch {
    return null;
  }
}

interface Seat { p: GamePlayer; score: number; streak: number; gone: boolean }

export class QuizGame implements Game {
  private seats: Seat[];
  private phase: 'intro' | 'question' | 'reveal' = 'intro';
  private i = 0;
  private t = 0; // seconds into the current phase
  private answers = new Map<string, { choice: number; at: number }>();
  private gained = new Map<string, number>();
  private done: GameResult[] | null = null;

  constructor(players: GamePlayer[], private readonly quiz: QuizData) {
    this.seats = players.map((p) => ({ p, score: 0, streak: 0, gone: false }));
  }

  private get question(): QuizQuestion {
    return this.quiz.questions[this.i];
  }

  input(_id: string, _i: GameInput): void {
    // Answers come in through action().
  }

  action(id: string, data: unknown): void {
    if (this.phase !== 'question' || this.answers.has(id)) return;
    const seat = this.seats.find((s) => s.p.id === id && !s.gone);
    const choice = num((data as any)?.choice, -1);
    if (!seat || !Number.isInteger(choice) || choice < 0 || choice >= this.question.c.length) return;
    this.answers.set(id, { choice, at: this.t });
  }

  leave(id: string): void {
    const s = this.seats.find((x) => x.p.id === id);
    if (s) s.gone = true;
    if (this.seats.every((x) => x.gone)) this.end();
  }

  step(dt: number): void {
    if (this.done) return;
    this.t += dt;
    if (this.phase === 'intro' && this.t >= INTRO) return this.startQuestion();
    if (this.phase === 'question') {
      const present = this.seats.filter((s) => !s.gone);
      if (this.t >= this.question.s || present.every((s) => this.answers.has(s.p.id))) this.reveal();
      return;
    }
    if (this.phase === 'reveal' && this.t >= REVEAL) {
      if (this.i + 1 >= this.quiz.questions.length) return this.end();
      this.i++;
      this.startQuestion();
    }
  }

  private startQuestion(): void {
    this.phase = 'question';
    this.t = 0;
    this.answers.clear();
    this.gained.clear();
  }

  private reveal(): void {
    const q = this.question;
    for (const s of this.seats) {
      const ans = this.answers.get(s.p.id);
      let pts = 0;
      if (ans && ans.choice === q.a) {
        s.streak++;
        pts = Math.round(1000 * (1 - 0.5 * Math.min(1, ans.at / q.s))) + Math.min(STREAK_MAX, STREAK_STEP * (s.streak - 1));
      } else {
        s.streak = 0;
      }
      s.score += pts;
      this.gained.set(s.p.id, pts);
    }
    this.phase = 'reveal';
    this.t = 0;
  }

  private end(): void {
    const ranked = [...this.seats].sort((a, b) => Number(a.gone) - Number(b.gone) || b.score - a.score);
    this.done = ranked.map((s, i) => ({ id: s.p.id, name: s.p.name, place: i + 1, score: String(s.score) }));
  }

  finished(): GameResult[] | null {
    return this.done;
  }

  meta(): unknown {
    return { title: this.quiz.title, total: this.quiz.questions.length };
  }

  snapshot(): unknown {
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

/** The quiz def needs the server secret to open tickets, so it is built at startup. */
export function quizDef(secret: string | undefined): GameDef {
  return {
    key: 'quiz',
    minPlayers: 1,
    maxPlayers: 10,
    setup: (payload, user) => openTicket((payload as any)?.ticket, secret, user.organizationId),
    create: (players, options) => new QuizGame(players, options as QuizData),
  };
}
