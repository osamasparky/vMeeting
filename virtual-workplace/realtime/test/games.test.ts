import { GAMES, GameHub } from '../src/games/index.js';
import { SnipersGame, ROOF_Y, ROUNDS_TO_WIN, rayBox } from '../src/games/snipers.js';
import { RacingGame, TRACK, LAPS, locate, HALF_WIDTH } from '../src/games/racing.js';
import { KartsGame, LIVES } from '../src/games/karts.js';
import { PoolGame, POCKETS } from '../src/games/pool.js';
import { GamePlayer } from '../src/games/types.js';
import { QuizGame, openTicket, quizDef } from '../src/games/quiz.js';
import crypto from 'crypto';
import { RunGame, W as RUN_W, H as RUN_H, open as runOpen } from '../src/games/run.js';

let passed = 0;
function check(cond: unknown, msg: string): void {
  if (!cond) throw new Error(`❌ ${msg}`);
  passed++;
}
const run = (g: { step(dt: number): void }, seconds: number) => {
  for (let i = 0; i < Math.round(seconds * 30); i++) g.step(1 / 30);
};
const players = (n: number): GamePlayer[] => Array.from({ length: n }, (_, i) => ({ id: `u${i}`, name: `P${i}`, slot: i }));
const user = (id: string, extra: Record<string, unknown> = {}) => ({ userId: id, name: id, organizationId: 'org', mapId: 'map', ...extra });

console.log('🎮 Game tests');

// ── Hub: lobby, seats, host start, frames, leaving ──
{
  const sent: { to: string; type: string; payload: any }[] = [];
  const hub = new GameHub(GAMES, (to, e) => sent.push({ to, type: e.type, payload: e.payload }));
  const last = (to: string, type: string) => [...sent].reverse().find((m) => m.to === to && m.type === type)?.payload;

  hub.handle(user('a'), 'game.open', { machineId: 'm1', game: 'snipers' });
  hub.handle(user('b'), 'game.open', { machineId: 'm1', game: 'snipers' });
  check(hub.sessionCount() === 1, 'two people at one machine share a session');
  check(last('b', 'game.state').watching.length === 2, 'both watch before sitting');

  hub.handle(user('a'), 'game.sit', {});
  hub.handle(user('a'), 'game.start', {});
  check(last('a', 'game.state').status === 'lobby', 'snipers needs two players to start');

  hub.handle(user('b'), 'game.sit', {});
  hub.handle(user('b'), 'game.start', {});
  check(last('b', 'game.state').status === 'lobby', 'only the host (first seated) starts');
  hub.handle(user('a'), 'game.start', {});
  check(last('b', 'game.state').status === 'playing', 'host starts the match');

  const before = sent.length;
  hub.tick();
  check(sent.slice(before).filter((m) => m.type === 'game.frame').length === 2, 'each viewer gets the frame');

  hub.handle(user('b'), 'game.close', {});
  const st = last('a', 'game.state');
  check(st.status === 'lobby' && st.lastResults?.[0]?.id === 'a', 'a player leaving ends a 2-player match, the other wins');

  hub.handle(user('a'), 'game.close', {});
  check(hub.sessionCount() === 0, 'an empty machine is forgotten');

  hub.handle(user('g', { isGuest: true }), 'game.open', { machineId: 'm2', game: 'snipers' });
  hub.handle(user('x'), 'game.open', { machineId: 'bad id!', game: 'snipers' });
  hub.handle(user('y'), 'game.open', { machineId: 'm3', game: 'chess' });
  check(hub.sessionCount() === 0, 'guests, bad machine ids and unknown games are ignored');

  const sameName = new GameHub(GAMES, () => {});
  sameName.handle(user('a'), 'game.open', { machineId: 'm1', game: 'pool' });
  sameName.handle({ ...user('b'), mapId: 'other' }, 'game.open', { machineId: 'm1', game: 'pool' });
  check(sameName.sessionCount() === 2, 'machines on different floors are separate');
  console.log('   ✓ hub');
}

// ── Snipers ──
{
  check(rayBox(0, 0, 1, 0, 10, -5, 20, 5) === 10, 'ray hits a box straight ahead');
  check(rayBox(0, 0, 1, 0, 10, 5, 20, 15) === null, 'ray misses a box above');

  const g = new SnipersGame(players(2)) as any;
  run(g, 1.5); // opening pause
  const [a, b] = g.s;
  check(a.y === ROOF_Y && b.y === ROOF_Y, 'both start on a roof');
  check(a.dir === 1 && b.dir === -1, 'they face each other');

  // Shove b off the far edge: it falls and a wins the round.
  b.x = 915;
  b.vx = 400;
  run(g, 1.5);
  check(a.wins === 1 && !b.alive, 'falling off loses the round');

  for (let r = 0; r < ROUNDS_TO_WIN + 1 && !g.finished(); r++) {
    run(g, 2);
    g.s[1].x = 915;
    g.s[1].vx = 400;
    run(g, 1.5);
  }
  const res = g.finished();
  check(res && res[0].id === 'u0' && res[0].score === String(ROUNDS_TO_WIN), `first to ${ROUNDS_TO_WIN} wins the match`);

  // A hit pushes the target back.
  const h = new SnipersGame(players(2)) as any;
  run(h, 1.5);
  h.s[0].x = 380;
  h.s[1].x = 420;
  h.s[1].y = ROOF_Y + 300; // keep it from mattering where it lands
  h.s[1].y = ROOF_Y;
  h.s[1].x = 395;
  h.fire(h.s[0]);
  check(h.s[1].vx > 0, 'a shot knocks the target away');
  console.log('   ✓ snipers');
}

// ── Racing ──
{
  check(TRACK.length > 1500, 'the track has a sensible length');
  const p = TRACK.pts[40];
  check(locate(p.x, p.y).dist < 1, 'a centreline point is on the track');

  const g = new RacingGame(players(2)) as any;
  run(g, 3.2); // countdown
  const car = g.cars[0];
  // Drive car 0 around by teleporting along the centreline: laps count, the race ends.
  let i = 0;
  while (!car.finishedAt && i < TRACK.pts.length * (LAPS + 1)) {
    const q = TRACK.pts[i % TRACK.pts.length];
    car.x = q.x;
    car.y = q.y;
    car.v = 0;
    g.step(1 / 30);
    i++;
  }
  check(car.finishedAt !== null, `${LAPS} laps finish the race`);
  check(g.snapshot().c.find((c: any) => c.id === 'u0').pos === 1, 'the finisher leads');

  // Backwards does not count.
  const back = new RacingGame(players(1)) as any;
  run(back, 3.2);
  const c2 = back.cars[0];
  const start = c2.progress;
  for (let k = TRACK.pts.length - 1; k > TRACK.pts.length - 60; k--) {
    c2.x = TRACK.pts[k].x;
    c2.y = TRACK.pts[k].y;
    back.step(1 / 30);
  }
  check(c2.progress < start, 'driving backwards loses progress');

  // Off the road you are slow.
  const off = new RacingGame(players(1)) as any;
  run(off, 3.2);
  const c3 = off.cars[0];
  c3.x = 500;
  c3.y = 440; // inside the loop, far from the asphalt
  check(locate(c3.x, c3.y).dist > HALF_WIDTH, 'test spot is off the road');
  c3.v = 300;
  off.input('u0', { up: true });
  run(off, 1);
  check(c3.v <= 150, 'grass caps the speed');
  console.log('   ✓ racing');
}

// ── Karts ──
{
  let seed = 1;
  const rnd = () => ((seed = (seed * 16807) % 2147483647) / 2147483647);
  const g = new KartsGame(players(2), rnd) as any;
  run(g, 2.1);
  const [a, b] = g.k;
  a.x = 440; a.y = 300; a.vx = 250; a.vy = 0; a.a = 0;
  b.x = 470; b.y = 300; b.vx = 0; b.vy = 0;
  g.step(1 / 30);
  check(b.vx > 100, 'a bump sends the other kart flying');

  b.x = 450 + 300; // off the platform
  g.step(1 / 30);
  check(b.lives === LIVES - 1 && a.kos === 1, 'falling off costs a life and credits the bumper');

  for (let n = 0; n < LIVES && !g.finished(); n++) {
    run(g, 2);
    g.k[1].x = 450 + 300;
    g.step(1 / 30);
  }
  run(g, 0.1);
  const res = g.finished();
  check(res && res[0].id === 'u0', 'last kart on the platform wins');
  console.log('   ✓ karts');
}

// ── Pool ──
{
  const g = new PoolGame(players(2)) as any;
  check(g.balls.length === 16, 'cue ball and 15 racked');
  g.action('u1', { angle: 0, power: 1 });
  check(g.phase === 'aim', 'only the shooter may shoot');
  g.action('u0', { angle: 0, power: 1 });
  run(g, 12);
  check(g.phase === 'aim', 'the table settles after the break');

  // Cue ball into a pocket: foul, turn passes, cue back on the table.
  const f = new PoolGame(players(2)) as any;
  const pk = POCKETS[0];
  f.balls[0].x = pk.x + 40;
  f.balls[0].y = pk.y + 40;
  f.action('u0', { angle: Math.atan2(-1, -1), power: 0.4 });
  run(f, 6);
  check(f.snapshot().turn === 'u1' && !f.balls[0].in, 'a scratch passes the turn and respots the cue');

  // Potting the 8 too early loses.
  const e = new PoolGame(players(2)) as any;
  const eight = e.balls.find((x: any) => x.n === 8);
  for (const ball of e.balls) if (ball.n !== 0 && ball.n !== 8) ball.in = true;
  e.groups.u0 = 'solids';
  e.groups.u1 = 'stripes';
  e.balls.find((x: any) => x.n === 1).in = false; // u0 still has a solid
  e.balls.find((x: any) => x.n === 1).x = 900; // out of the way
  e.balls.find((x: any) => x.n === 1).y = 480;
  eight.x = POCKETS[2].x - 60;
  eight.y = POCKETS[2].y + 60;
  e.balls[0].x = eight.x - 60;
  e.balls[0].y = eight.y + 60;
  e.action('u0', { angle: Math.atan2(-1, 1), power: 0.5 });
  run(e, 6);
  const r = e.finished();
  check(r && r[0].id === 'u1', 'potting the 8 before your group loses');
  console.log('   ✓ pool');
}

// ── Quiz ──
{
  const SECRET = 'quiz-secret';
  // Sealed the way App\Domains\Workspace\Games\QuizTicket does it.
  const seal = (data: object, secret = SECRET) => {
    const key = crypto.createHash('sha256').update(secret).digest();
    const iv = crypto.randomBytes(12);
    const c = crypto.createCipheriv('aes-256-gcm', key, iv);
    const body = Buffer.concat([c.update(JSON.stringify(data), 'utf8'), c.final()]);
    return Buffer.concat([iv, c.getAuthTag(), body]).toString('base64');
  };
  const quiz = { title: 'Q', questions: [
    { q: 'one', c: ['a', 'b', 'c', 'd'], a: 1, s: 10 },
    { q: 'two', c: ['yes', 'no'], a: 0, s: 10 },
  ] };
  const exp = Math.floor(Date.now() / 1000) + 60;
  check(openTicket(seal({ org: 'org', exp, ...quiz }), SECRET, 'org')?.questions.length === 2, 'a sealed ticket opens');
  check(openTicket(seal({ org: 'other', exp, ...quiz }), SECRET, 'org') === null, "another company's ticket is refused");
  check(openTicket(seal({ org: 'org', exp: exp - 120, ...quiz }), SECRET, 'org') === null, 'an expired ticket is refused');
  check(openTicket(seal({ org: 'org', exp, ...quiz }, 'wrong'), SECRET, 'org') === null, 'a forged ticket is refused');
  const sealed = seal({ org: 'org', exp, ...quiz });
  check(openTicket(sealed.slice(0, -4) + 'AAAA', SECRET, 'org') === null, 'a tampered ticket is refused');

  // Hub: the host must bring a valid ticket.
  const sent: any[] = [];
  const hub = new GameHub({ quiz: quizDef(SECRET) }, (to, e) => sent.push({ to, ...e }));
  hub.handle(user('a'), 'game.open', { machineId: 'qm', game: 'quiz' });
  hub.handle(user('a'), 'game.sit', {});
  hub.handle(user('a'), 'game.start', { ticket: 'nonsense' });
  check(sent.some((m) => m.type === 'game.error'), 'a bad ticket does not start the quiz');
  hub.handle(user('a'), 'game.start', { ticket: seal({ org: 'org', exp, ...quiz }) });
  check([...sent].reverse().find((m) => m.type === 'game.state').payload.status === 'playing', 'a good ticket starts it (solo is fine)');

  // Scoring: fast right answers score more, streaks add, the answer stays hidden until the reveal.
  const g = new QuizGame(players(3), quiz) as any;
  run(g, 3.1); // intro
  check(g.snapshot().phase === 'question' && g.snapshot().correct === null, 'question phase hides the answer');
  g.action('u0', { choice: 1 }); // right, instantly
  run(g, 4);
  g.action('u1', { choice: 1 }); // right, 4 s in
  g.action('u1', { choice: 2 }); // a second answer is ignored
  g.action('u2', { choice: 3 }); // wrong
  g.step(1 / 30); // everyone has answered → reveal now, not after 10 s
  const r = g.snapshot();
  check(r.phase === 'reveal' && r.correct === 1, 'all answered: reveal early with the right answer');
  check(r.gained.u0 > r.gained.u1 && r.gained.u1 >= 500 && r.gained.u2 === 0, 'faster right answers score more; wrong scores 0');
  run(g, 5.1);
  g.action('u0', { choice: 0 });
  run(g, 10.1);
  check(g.snapshot().gained.u0 > 900 && g.seats[0].streak === 2, 'a streak builds and adds a bonus');
  run(g, 5.1);
  const res = g.finished();
  check(res && res[0].id === 'u0' && res.length === 3, 'the quiz ends with standings');
  console.log('   ✓ quiz');
}

// ── Run (اهرب!) ──
{
  // Every open tile can be reached from every other: no sealed-off corners.
  const openTiles: string[] = [];
  for (let y = 0; y < RUN_H; y++) for (let x = 0; x < RUN_W; x++) if (runOpen(x, y)) openTiles.push(`${x},${y}`);
  const seen = new Set(['1,1']);
  const q = [[1, 1]];
  while (q.length) {
    const [x, y] = q.shift()!;
    for (const [dx, dy] of [[1, 0], [-1, 0], [0, 1], [0, -1]]) {
      const k = `${x + dx},${y + dy}`;
      if (runOpen(x + dx, y + dy) && !seen.has(k)) { seen.add(k); q.push([x + dx, y + dy]); }
    }
  }
  check(seen.size === openTiles.length, 'the maze is fully connected');

  let seed = 7;
  const rnd = () => ((seed = (seed * 16807) % 2147483647) / 2147483647);
  const g = new RunGame(players(2), rnd) as any;
  run(g, 3.05); // countdown
  const a = g.movers[0];
  check(a.x === 1 && a.y === 1 && !a.ghost, 'runner A starts in a corner');
  g.input('u0', { right: true });
  run(g, 0.5);
  check(a.y === 1 && a.x > 2 && a.x < 4.5, 'holding right runs along the corridor');
  check(a.coins >= 2, 'coins on the way are picked up');
  g.input('u0', { right: false });
  g.input('u0', { left: true });
  const before = a.x;
  g.step(1 / 30);
  check(a.x < before, 'reversing is immediate');
  g.input('u0', { left: false });
  g.input('u0', { up: true });
  run(g, 1);
  check(a.y === 1, 'turning into a wall does nothing');

  // A ghost on top of a runner catches them; the runner becomes a ghost.
  const b = g.movers[1];
  const ai = g.movers.find((m: any) => m.ai);
  ai.x = b.x; ai.y = b.y;
  g.step(1 / 30);
  check(b.ghost === true, 'a caught runner becomes a ghost');
  const res = g.finished();
  check(res && res[0].id === 'u0', 'the last runner standing wins (and gets the bonus)');

  // A player ghost earns points for a catch.
  const h = new RunGame(players(4), rnd) as any;
  run(h, 3.05);
  const [p0, p1, p2] = h.movers;
  p0.ghost = true;
  p0.x = p1.x; p0.y = p1.y;
  h.step(1 / 30);
  check(p1.ghost && p0.catches === 1 && p0.score >= 150 && !h.finished(), 'ghost players score catches; the round goes on while 2+ run');
  check(!p2.ghost, 'others keep running');
  console.log('   ✓ run');
}

console.log(`\n✅ ${passed} game checks passed`);
