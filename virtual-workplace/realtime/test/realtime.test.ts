import crypto from 'crypto';
import { spawn } from 'child_process';
import { WebSocket } from 'ws';
import { NotificationHub, parsePublishBody, secretMatches } from '../src/notifications/notification-hub.js';
import { TokenVerifier } from '../src/auth/token-verifier.js';
import { ProximityCalculator } from '../src/state/proximity-calculator.js';
import { OfficeUser } from '../src/events/event-types.js';

const SECRET = 'super-secret-virtual-workplace-key-2026';

function createMockToken(userId: string, name: string, orgId: string, role = 'member'): string {
  const header = Buffer.from(JSON.stringify({ typ: 'JWT', alg: 'HS256' })).toString('base64');
  const payload = Buffer.from(
    JSON.stringify({
      sub: userId,
      name,
      email: `${userId}@test.com`,
      organization_id: orgId,
      role,
      iat: Math.floor(Date.now() / 1000),
      exp: Math.floor(Date.now() / 1000) + 3600,
    })
  ).toString('base64');

  const signature = crypto.createHmac('sha256', SECRET).update(`${header}.${payload}`).digest('base64');
  return `${header}.${payload}.${signature}`;
}

async function runTests() {
  console.log('🧪 Starting Realtime Service Unit & Integration Tests...\n');

  // Test 1: Token Verifier
  console.log('1. Testing Token Verification...');
  const verifier = new TokenVerifier(SECRET);
  const validToken = createMockToken('user-1', 'Alice', 'org-1');
  const decoded = verifier.verify(validToken);

  if (!decoded || decoded.sub !== 'user-1' || decoded.organization_id !== 'org-1') {
    throw new Error('❌ Token verification failed for valid token');
  }

  const invalidToken = validToken + 'tampered';
  if (verifier.verify(invalidToken) !== null) {
    throw new Error('❌ Token verification should reject tampered token');
  }
  console.log('   ✓ Valid and tampered tokens handled correctly.');

  // Test 2: Spatial Proximity Calculation
  console.log('2. Testing Spatial Proximity Calculation...');
  const proximity = new ProximityCalculator(150);

  const alice: OfficeUser = {
    userId: 'alice',
    organizationId: 'org-1',
    mapId: 'map-1',
    name: 'Alice',
    status: 'available',
    position: { x: 100, y: 100 },
    currentRoomId: null,
    currentZoneId: null,
    lastActive: Date.now(),
  };

  const bobNear: OfficeUser = {
    userId: 'bob',
    organizationId: 'org-1',
    mapId: 'map-1',
    name: 'Bob',
    status: 'available',
    position: { x: 150, y: 120 }, // Distance ~53.8 units <= 150
    currentRoomId: null,
    currentZoneId: null,
    lastActive: Date.now(),
  };

  const charlieFar: OfficeUser = {
    userId: 'charlie',
    organizationId: 'org-1',
    mapId: 'map-1',
    name: 'Charlie',
    status: 'available',
    position: { x: 800, y: 800 }, // Distance >> 150
    currentRoomId: null,
    currentZoneId: null,
    lastActive: Date.now(),
  };

  const nearbyForAlice = proximity.getNearbyUsers(alice, [alice, bobNear, charlieFar]);
  if (!nearbyForAlice.includes('bob') || nearbyForAlice.includes('charlie')) {
    throw new Error(`❌ Proximity calculation incorrect: ${JSON.stringify(nearbyForAlice)}`);
  }
  console.log('   ✓ Spatial radius correctly includes Bob and excludes distant Charlie.');

  // Test 3: Private Room Audio Isolation
  console.log('3. Testing Private Room Audio Isolation...');
  const aliceInRoom: OfficeUser = { ...alice, currentRoomId: 'room-boardroom' };
  const bobInSameRoom: OfficeUser = { ...bobNear, currentRoomId: 'room-boardroom' };
  const charlieInOpenFloor: OfficeUser = { ...bobNear, userId: 'charlie', position: { x: 105, y: 105 }, currentRoomId: null };

  const roomNearby = proximity.getNearbyUsers(aliceInRoom, [aliceInRoom, bobInSameRoom, charlieInOpenFloor]);
  if (!roomNearby.includes('bob') || roomNearby.includes('charlie')) {
    throw new Error('❌ Room audio isolation failed: outside user leaked into private room');
  }

  const openFloorNearby = proximity.getNearbyUsers(charlieInOpenFloor, [aliceInRoom, bobInSameRoom, charlieInOpenFloor]);
  if (openFloorNearby.includes('alice') || openFloorNearby.includes('bob')) {
    throw new Error('❌ Open floor user heard private room occupants');
  }
  console.log('   ✓ Private room occupants completely isolated from open floor.');

  // Test 4: Notification hub (unit)
  console.log('4. Testing Notification Hub...');
  const hub = new NotificationHub();
  const sent: string[] = [];
  const fakeSocket = (tag: string) => ({ readyState: WebSocket.OPEN, send: (m: string) => sent.push(`${tag}:${m}`) }) as unknown as WebSocket;
  const tabA = fakeSocket('a1');
  const tabB = fakeSocket('a2');
  hub.add('alice', tabA);
  hub.add('alice', tabB);
  hub.add('bob', fakeSocket('b1'));
  if (hub.publish(['alice', 'alice']) !== 2 || sent.length !== 2 || !sent.every((m) => m.includes('notification.new'))) {
    throw new Error(`❌ Hub should ping each of Alice's tabs once: ${JSON.stringify(sent)}`);
  }
  hub.remove('alice', tabA);
  if (hub.publish(['alice']) !== 1 || hub.connectionCount() !== 2) {
    throw new Error('❌ Hub should forget closed tabs');
  }
  if (!secretMatches('s3cret', 's3cret') || secretMatches('s3cret', 'other') || secretMatches(undefined, 's3cret') || secretMatches('x', undefined)) {
    throw new Error('❌ Secret check is wrong');
  }
  if (parsePublishBody('{"user_ids":["a","b"]}')?.length !== 2 || parsePublishBody('{"user_ids":[1]}') !== null || parsePublishBody('nope') !== null) {
    throw new Error('❌ Publish body parsing is wrong');
  }
  console.log('   ✓ Pings reach every open tab once; secret and body are validated.');

  // Test 5: Real server — a bell connection must not replace the office session
  console.log('5. Testing bell channel against a live server...');
  await liveServerTest();
  console.log('   ✓ Office session survives a bell connection; Laravel pings reach the bell only with the secret.');

  // Test 6: Door and knock rules on a live server
  console.log('6. Testing door and knock permissions...');
  await doorRulesTest();
  console.log('   ✓ Only people inside lock a door; guests never do; empty rooms may be reopened; only insiders answer knocks.');

  // Test 7: Shared state a late joiner must see
  console.log('7. Testing shared doors and whiteboards...');
  await sharedStateTest();
  console.log('   ✓ Late joiners see locked doors; empty rooms reopen; boards replay cleaned strokes and shared notes.');

  console.log('\n✨ All Realtime Service Tests Passed Successfully! (7/7 suites)');
}

async function withServer(run: (port: number) => Promise<void>): Promise<void> {
  const port = 19000 + Math.floor(Math.random() * 1000);
  const env = { ...process.env, REALTIME_PORT: String(port), REALTIME_HOST: '127.0.0.1', REALTIME_SECRET: SECRET };
  const child = spawn(process.execPath, ['--import', 'tsx', 'src/server.ts'], { env, stdio: ['ignore', 'pipe', 'pipe'] });
  try {
    await new Promise<void>((resolve, reject) => {
      const timer = setTimeout(() => reject(new Error('❌ Server did not start')), 15000);
      child.stdout.on('data', (d) => { if (String(d).includes('listening')) { clearTimeout(timer); resolve(); } });
      child.on('exit', (code) => reject(new Error(`❌ Server exited early (${code})`)));
    });
    await run(port);
  } finally {
    child.kill();
  }
}

async function doorRulesTest(): Promise<void> {
  await withServer(async (port) => {
    const connect = async (id: string, role = 'member') => {
      const ws = new WebSocket(`ws://127.0.0.1:${port}/?token=${encodeURIComponent(createMockToken(id, id, 'org-1', role))}`);
      const inbox: any[] = [];
      ws.on('message', (m) => inbox.push(JSON.parse(String(m))));
      await new Promise((r) => ws.once('open', r));
      ws.send(JSON.stringify({ type: 'map.join', payload: { mapId: 'map-1', initialPosition: { x: 10, y: 10 } } }));
      return { ws, inbox, send: (type: string, payload: any) => ws.send(JSON.stringify({ type, payload })) };
    };
    const pause = () => new Promise((r) => setTimeout(r, 250));
    const doorEvents = (c: { inbox: any[] }) => c.inbox.filter((e) => e.type === 'room.door_updated');

    const inside = await connect('alice');
    const outside = await connect('bob');
    const guest = await connect('guest_carol', 'Guest');
    await pause();
    inside.send('room.enter', { roomId: 'room-1' });
    await pause();

    outside.send('room.door_toggle', { roomId: 'room-1', isClosed: true });
    guest.send('room.door_toggle', { roomId: 'room-1', isClosed: true });
    await pause();
    if (doorEvents(inside).length !== 0) throw new Error('❌ An outsider or a guest changed the door');

    inside.send('room.door_toggle', { roomId: 'room-1', isClosed: true });
    await pause();
    if (doorEvents(outside).length !== 1 || doorEvents(outside)[0].payload.isClosed !== true) throw new Error('❌ The occupant could not lock the door');

    // Knock answers only count from inside the room.
    outside.send('room.knock_response', { roomId: 'room-1', requesterUserId: 'guest_carol', approved: true });
    await pause();
    if (guest.inbox.some((e) => e.type === 'room.knock_result')) throw new Error('❌ An outsider answered a knock');
    inside.send('room.knock_response', { roomId: 'room-1', requesterUserId: 'guest_carol', approved: true });
    await pause();
    if (!guest.inbox.some((e) => e.type === 'room.knock_result' && e.payload.approved === true)) throw new Error('❌ The occupant could not answer the knock');

    // Once the room is empty anyone may reopen it (rooms rest unlocked).
    inside.send('room.leave', { roomId: 'room-1' });
    await pause();
    outside.send('room.door_toggle', { roomId: 'room-1', isClosed: false });
    await pause();
    if (!doorEvents(inside).some((e) => e.payload.isClosed === false)) throw new Error('❌ An empty room could not be reopened');

    for (const c of [inside, outside, guest]) c.ws.close();
  });
}

async function sharedStateTest(): Promise<void> {
  await withServer(async (port) => {
    const connect = async (id: string) => {
      const ws = new WebSocket(`ws://127.0.0.1:${port}/?token=${encodeURIComponent(createMockToken(id, id, 'org-1'))}`);
      const inbox: any[] = [];
      ws.on('message', (m) => inbox.push(JSON.parse(String(m))));
      await new Promise((r) => ws.once('open', r));
      ws.send(JSON.stringify({ type: 'map.join', payload: { mapId: 'map-1', initialPosition: { x: 10, y: 10 } } }));
      return { ws, inbox, send: (type: string, payload: any) => ws.send(JSON.stringify({ type, payload })) };
    };
    const pause = () => new Promise((r) => setTimeout(r, 250));

    const alice = await connect('alice');
    await pause();
    alice.send('room.enter', { roomId: 'room-1' });
    await pause();
    alice.send('room.door_toggle', { roomId: 'room-1', isClosed: true });
    alice.send('whiteboard.draw', { roomId: 'room-1', stroke: { tool: 'pen', color: 'red;evil', points: [[1, 2], [3, 4], ['x', 5]], extra: '<script>' } });
    alice.send('whiteboard.draw', { roomId: 'room-1', stroke: { tool: 'flamethrower', color: '#000000' } });
    alice.send('whiteboard.note_add', { roomId: 'room-1', note: { id: 'n1', content: 'Agenda', bg: 'url(x)' } });
    await pause();

    // Someone arriving later learns the door is locked and sees the board as it is.
    const bob = await connect('bob');
    await pause();
    const welcome = bob.inbox.find((e) => e.type === 'welcome');
    if (!welcome || JSON.stringify(welcome.payload.closedDoors) !== '["room-1"]') throw new Error(`❌ Late joiner missed the locked door: ${JSON.stringify(welcome?.payload)}`);

    bob.send('whiteboard.sync', { roomId: 'room-1' });
    await pause();
    const history = bob.inbox.find((e) => e.type === 'whiteboard.history');
    if (!history || history.payload.strokes.length !== 1 || history.payload.notes.length !== 1) throw new Error(`❌ Board history wrong: ${JSON.stringify(history)}`);
    const stroke = history.payload.strokes[0];
    if (stroke.color !== '#0F172A' || stroke.extra !== undefined || stroke.points.length !== 2) throw new Error(`❌ Stroke not cleaned: ${JSON.stringify(stroke)}`);
    if (history.payload.notes[0].bg !== '#FEF08A' || history.payload.notes[0].author !== 'alice') throw new Error('❌ Note not cleaned');

    // Another room's board is separate.
    bob.send('whiteboard.sync', { roomId: 'room-2' });
    await pause();
    const other = bob.inbox.filter((e) => e.type === 'whiteboard.history').pop();
    if (other.payload.roomId !== 'room-2' || other.payload.strokes.length !== 0) throw new Error('❌ Boards leaked between rooms');

    // Removing a note reaches everyone; clearing empties the stored strokes.
    bob.send('whiteboard.note_remove', { roomId: 'room-1', id: 'n1' });
    alice.send('whiteboard.clear', { roomId: 'room-1' });
    await pause();
    if (!alice.inbox.some((e) => e.type === 'whiteboard.note_removed' && e.payload.id === 'n1')) throw new Error('❌ Note removal not broadcast');
    bob.send('whiteboard.sync', { roomId: 'room-1' });
    await pause();
    const cleared = bob.inbox.filter((e) => e.type === 'whiteboard.history').pop();
    if (cleared.payload.strokes.length !== 0 || cleared.payload.notes.length !== 0) throw new Error('❌ Board not cleared');

    // The last person leaving a locked room unlocks it for everyone.
    alice.ws.close();
    await pause();
    if (!bob.inbox.some((e) => e.type === 'room.door_updated' && e.payload.roomId === 'room-1' && e.payload.isClosed === false)) throw new Error('❌ Empty locked room stayed locked');
    const carol = await connect('carol');
    await pause();
    if (carol.inbox.find((e) => e.type === 'welcome')?.payload.closedDoors.length !== 0) throw new Error('❌ Reopened door still reported locked');

    bob.ws.close();
    carol.ws.close();
  });
}

async function liveServerTest(): Promise<void> {
  const port = 18000 + Math.floor(Math.random() * 1000);
  const env = { ...process.env, REALTIME_PORT: String(port), REALTIME_HOST: '127.0.0.1', REALTIME_SECRET: SECRET };
  const child = spawn(process.execPath, ['--import', 'tsx', 'src/server.ts'], { env, stdio: ['ignore', 'pipe', 'pipe'] });
  try {
    await new Promise<void>((resolve, reject) => {
      const timer = setTimeout(() => reject(new Error('❌ Server did not start')), 15000);
      child.stdout.on('data', (d) => { if (String(d).includes('listening')) { clearTimeout(timer); resolve(); } });
      child.on('exit', (code) => reject(new Error(`❌ Server exited early (${code})`)));
    });

    const token = createMockToken('alice', 'Alice', 'org-1');
    const open = (url: string) => new Promise<WebSocket>((resolve, reject) => {
      const ws = new WebSocket(url);
      ws.once('open', () => resolve(ws));
      ws.once('error', reject);
    });

    const office = await open(`ws://127.0.0.1:${port}/?token=${encodeURIComponent(token)}`);
    let officeClosed = false;
    office.on('close', () => { officeClosed = true; });
    const officeMessages: string[] = [];
    office.on('message', (m) => officeMessages.push(String(m)));

    const bell = await open(`ws://127.0.0.1:${port}/?channel=notifications&token=${encodeURIComponent(token)}`);
    const bellMessages: string[] = [];
    bell.on('message', (m) => bellMessages.push(String(m)));
    await new Promise((r) => setTimeout(r, 300));

    if (officeClosed || officeMessages.some((m) => m.includes('session.replaced'))) {
      throw new Error('❌ Opening the bell channel replaced the office session');
    }

    const post = (secret: string | null) => fetch(`http://127.0.0.1:${port}/internal/notifications`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', ...(secret ? { 'X-Realtime-Secret': secret } : {}) },
      body: JSON.stringify({ user_ids: ['alice'] }),
    });

    if ((await post(null)).status !== 401 || (await post('wrong')).status !== 401) {
      throw new Error('❌ Internal endpoint accepted a call without the secret');
    }
    const ok = await post(SECRET);
    const body = await ok.json() as { delivered: number };
    await new Promise((r) => setTimeout(r, 200));
    if (ok.status !== 202 || body.delivered !== 1 || !bellMessages.some((m) => m.includes('notification.new'))) {
      throw new Error(`❌ Bell did not get the ping: ${ok.status} ${JSON.stringify(body)} ${JSON.stringify(bellMessages)}`);
    }
    if (officeMessages.some((m) => m.includes('notification.new'))) {
      throw new Error('❌ Office socket should not receive bell pings');
    }

    office.close();
    bell.close();
  } finally {
    child.kill();
  }
}

runTests().catch((err) => {
  console.error(err);
  process.exit(1);
});
