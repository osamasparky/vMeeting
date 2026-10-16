import { GameDef } from './types.js';
import { snipers } from './snipers.js';
import { racing } from './racing.js';
import { karts } from './karts.js';
import { pool } from './pool.js';
import { quizDef } from './quiz.js';
import { run } from './run.js';

/** Every game a machine can run, by the key stored on the furniture item. */
export function buildGames(secret: string | undefined): Record<string, GameDef> {
  return { snipers, racing, karts, pool, run, quiz: quizDef(secret) };
}

/** Without the secret: everything but the quiz (which can then never start). */
export const GAMES: Record<string, GameDef> = buildGames(undefined);

export { GameHub, TICK_HZ } from './game-hub.js';
