import crypto from 'crypto';
import { WebSocket } from 'ws';
import { TokenVerifier } from '../src/auth/token-verifier.js';
import { ProximityCalculator } from '../src/state/proximity-calculator.js';
import { OfficeUser } from '../src/events/event-types.js';

const SECRET = 'super-secret-virtual-workplace-key-2026';

function createMockToken(userId: string, name: string, orgId: string): string {
  const header = Buffer.from(JSON.stringify({ typ: 'JWT', alg: 'HS256' })).toString('base64');
  const payload = Buffer.from(
    JSON.stringify({
      sub: userId,
      name,
      email: `${userId}@test.com`,
      organization_id: orgId,
      role: 'member',
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

  console.log('\n✨ All Realtime Service Tests Passed Successfully! (3/3 suites)');
}

runTests().catch((err) => {
  console.error(err);
  process.exit(1);
});
