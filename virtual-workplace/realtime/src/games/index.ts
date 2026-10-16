import { GameDef } from './types.js';
import { snipers } from './snipers.js';
import { racing } from './racing.js';
import { karts } from './karts.js';
import { pool } from './pool.js';

/** Every game a machine can run, by the key stored on the furniture item. */
export const GAMES: Record<string, GameDef> = { snipers, racing, karts, pool };

export { GameHub, TICK_HZ } from './game-hub.js';
