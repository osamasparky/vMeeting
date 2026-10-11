"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const http_1 = __importDefault(require("http"));
const url_1 = __importDefault(require("url"));
const ws_1 = require("ws");
const dotenv_1 = __importDefault(require("dotenv"));
const token_verifier_js_1 = require("./auth/token-verifier.js");
const presence_manager_js_1 = require("./state/presence-manager.js");
const notification_hub_js_1 = require("./notifications/notification-hub.js");
const shared_state_js_1 = require("./state/shared-state.js");
dotenv_1.default.config();
const PORT = parseInt(process.env.REALTIME_PORT || '8080', 10);
const HOST = process.env.REALTIME_HOST || '0.0.0.0';
const notificationHub = new notification_hub_js_1.NotificationHub();
const server = http_1.default.createServer((req, res) => {
    // Laravel → "these users have new notifications". Authenticated by the shared secret.
    if (req.url === '/internal/notifications' && req.method === 'POST') {
        if (!(0, notification_hub_js_1.secretMatches)(req.headers['x-realtime-secret'], process.env.REALTIME_SECRET)) {
            res.writeHead(401);
            res.end();
            return;
        }
        let raw = '';
        req.on('data', (chunk) => {
            raw += chunk;
            if (raw.length > 100_000)
                req.destroy();
        });
        req.on('end', () => {
            const userIds = (0, notification_hub_js_1.parsePublishBody)(raw);
            if (!userIds) {
                res.writeHead(422, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ error: 'Expected {"user_ids": [...]}' }));
                return;
            }
            const delivered = notificationHub.publish(userIds);
            res.writeHead(202, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify({ delivered }));
        });
        return;
    }
    if (req.url === '/health' || req.url === '/') {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ status: 'ok', service: 'virtual-workplace-realtime', timestamp: new Date().toISOString() }));
        return;
    }
    res.writeHead(404);
    res.end();
});
const wss = new ws_1.WebSocketServer({ server });
const tokenVerifier = new token_verifier_js_1.TokenVerifier(process.env.REALTIME_SECRET);
const presence = new presence_manager_js_1.PresenceManager();
const shared = new shared_state_js_1.SharedState();
/** Once nobody is left in a locked room its door reopens for everyone (rooms rest unlocked). */
function reopenIfEmpty(orgId, mapId, roomId) {
    if (!roomId || !mapId || !shared.isDoorClosed(orgId, mapId, roomId))
        return;
    if (presence.getRoomOccupants(orgId, mapId, roomId).length > 0)
        return;
    shared.setDoor(orgId, mapId, roomId, false);
    presence.broadcastToMap(orgId, mapId, { type: 'room.door_updated', payload: { roomId, isClosed: false, toggledBy: 'system' } });
}
/**
 * Who receives a chat line (and its bubble):
 * - global: members on the map (guests neither send nor receive it);
 * - room:   people in that room plus hosts anywhere on the map; the sender must be inside;
 * - guest:  only the people in the room right now (the guest's own channel), no hosts outside.
 * Returns null when the sender may not post to that scope.
 */
function chatRecipients(user, rawScope, rawRoomId) {
    const scope = rawScope === 'global' ? 'global' : rawScope === 'guest' ? 'guest' : 'room';
    const onMap = presence.getMapOccupants(user.organizationId, user.mapId);
    if (scope === 'global') {
        if (user.isGuest)
            return null;
        return { scope, roomId: null, recipients: onMap.filter((u) => !u.isGuest) };
    }
    const roomId = user.currentRoomId;
    if (!roomId || (typeof rawRoomId === 'string' && rawRoomId !== roomId))
        return null;
    const inRoom = onMap.filter((u) => u.currentRoomId === roomId);
    const recipients = scope === 'room' ? [...inRoom, ...onMap.filter((u) => u.isHost && u.currentRoomId !== roomId)] : inRoom;
    return { scope, roomId, recipients };
}
/** Send one event to each listed user (once per user), except the sender's own socket. */
function sendToEach(users, event, exceptWs) {
    const seen = new Set();
    for (const u of users) {
        if (seen.has(u.userId))
            continue;
        seen.add(u.userId);
        const target = presence.findUserSocket(u.userId);
        if (target && target !== exceptWs)
            presence.send(target, event);
    }
}
const boardId = (roomId) => (typeof roomId === 'string' && /^[\w-]{1,64}$/.test(roomId) ? roomId : 'global');
wss.on('connection', (ws, req) => {
    // Extract token from query parameters: ?token=...
    const parsedUrl = url_1.default.parse(req.url || '', true);
    const token = parsedUrl.query.token || req.headers['sec-websocket-protocol'];
    if (!token) {
        presence.send(ws, {
            type: 'error',
            payload: { code: 'AUTH_REQUIRED', message: 'Missing authentication token.' },
        });
        ws.close(4001, 'Authentication token required');
        return;
    }
    const tokenPayload = tokenVerifier.verify(token);
    if (!tokenPayload) {
        presence.send(ws, {
            type: 'error',
            payload: { code: 'INVALID_TOKEN', message: 'Invalid or expired authentication token.' },
        });
        ws.close(4002, 'Invalid authentication token');
        return;
    }
    // Bell connections only receive notification pings; they are not office presence,
    // so they never replace the user's office session or show up as occupants.
    if (parsedUrl.query.channel === 'notifications') {
        const userId = tokenPayload.sub;
        notificationHub.add(userId, ws);
        ws.on('close', () => notificationHub.remove(userId, ws));
        return;
    }
    // Register client session
    const conn = presence.registerClient(ws, tokenPayload);
    console.log(`[WS] User connected: ${conn.user.name} (${conn.user.userId}) in Org: ${conn.user.organizationId}`);
    // Handle incoming messages
    ws.on('message', (raw) => {
        try {
            const event = JSON.parse(raw.toString());
            switch (event.type) {
                case 'map.join': {
                    const { mapId, initialPosition, gender } = event.payload || {};
                    const user = presence.joinMap(ws, mapId, initialPosition, gender);
                    if (!user)
                        break;
                    const occupants = presence.getMapOccupants(user.organizationId, mapId);
                    // 1. Send welcome packet to newly joined client with current occupants
                    presence.send(ws, {
                        type: 'welcome',
                        payload: {
                            user,
                            occupants: occupants.filter((u) => u.userId !== user.userId),
                            closedDoors: shared.closedDoors(user.organizationId, mapId),
                        },
                    });
                    // 2. Broadcast user.joined to all other occupants in this map
                    presence.broadcastToMap(user.organizationId, mapId, { type: 'user.joined', payload: user }, ws);
                    // 3. Broadcast updated organization-wide map occupancy counts to all branches
                    const mapCounts = presence.getOrganizationMapOccupancyCounts(user.organizationId);
                    presence.broadcastToOrganization(user.organizationId, {
                        type: 'organization.map_occupancy',
                        payload: { counts: mapCounts },
                    });
                    console.log(`[WS] ${user.name} joined map ${mapId} (gender: ${user.gender || 'male'}, total occupants: ${occupants.length})`);
                    break;
                }
                case 'map.sync':
                case 'map.get_occupants': {
                    const conn = presence.getClient(ws);
                    if (!conn || !conn.user || !conn.user.mapId)
                        break;
                    const user = conn.user;
                    const occupants = presence.getMapOccupants(user.organizationId, user.mapId);
                    presence.send(ws, {
                        type: 'map.occupants_sync',
                        payload: {
                            mapId: user.mapId,
                            occupants: occupants.filter((u) => u.userId !== user.userId),
                        },
                    });
                    break;
                }
                case 'position.update': {
                    const user = presence.updatePosition(ws, event.payload);
                    if (!user || !user.mapId)
                        break;
                    // Broadcast position to all occupants on the same map
                    presence.broadcastToMap(user.organizationId, user.mapId, {
                        type: 'position.updated',
                        payload: {
                            userId: user.userId,
                            position: user.position,
                        },
                    }, ws);
                    // Calculate spatial proximity for audio/video groups
                    const nearbyUserIds = presence.calculateProximity(user);
                    presence.send(ws, {
                        type: 'proximity.changed',
                        payload: {
                            nearbyUserIds,
                            audioGroupId: user.currentRoomId || `zone:${user.currentZoneId || 'open'}`,
                        },
                    });
                    break;
                }
                case 'status.update': {
                    const user = presence.updateStatus(ws, event.payload.status);
                    if (!user || !user.mapId)
                        break;
                    presence.broadcastToMap(user.organizationId, user.mapId, {
                        type: 'presence.updated',
                        payload: {
                            userId: user.userId,
                            status: user.status,
                        },
                    });
                    break;
                }
                case 'avatar.update': {
                    const { gender } = event.payload || {};
                    const user = conn.user;
                    if (!user || !user.mapId)
                        break;
                    user.gender = gender || 'male';
                    presence.broadcastToMap(user.organizationId, user.mapId, {
                        type: 'avatar.updated',
                        payload: {
                            userId: user.userId,
                            gender: user.gender,
                        },
                    });
                    console.log(`[WS] ${user.name} switched avatar character to ${user.gender}`);
                    break;
                }
                case 'room.enter': {
                    const { roomId } = event.payload;
                    const user = presence.setRoom(ws, roomId);
                    if (!user || !user.mapId)
                        break;
                    presence.broadcastToMap(user.organizationId, user.mapId, {
                        type: 'room.entered',
                        payload: {
                            userId: user.userId,
                            roomId,
                        },
                    });
                    break;
                }
                case 'room.leave': {
                    const { roomId } = event.payload;
                    const user = presence.setRoom(ws, null);
                    if (!user || !user.mapId)
                        break;
                    reopenIfEmpty(user.organizationId, user.mapId, roomId);
                    presence.broadcastToMap(user.organizationId, user.mapId, {
                        type: 'room.left',
                        payload: {
                            userId: user.userId,
                            roomId,
                        },
                    });
                    break;
                }
                case 'chat.send': {
                    const user = conn.user;
                    if (!user.mapId)
                        break;
                    const body = typeof event.payload?.body === 'string' ? event.payload.body.slice(0, 2000) : '';
                    if (!body.trim())
                        break;
                    const route = chatRecipients(user, event.payload?.scope, event.payload?.roomId);
                    if (!route)
                        break;
                    sendToEach(route.recipients, {
                        type: 'chat.message',
                        payload: {
                            channelId: route.scope === 'global' ? 'global' : `${route.scope}:${route.roomId}`,
                            senderId: user.userId,
                            senderName: user.name,
                            senderIsGuest: !!user.isGuest,
                            body,
                            scope: route.scope,
                            roomId: route.roomId,
                            timestamp: new Date().toISOString(),
                        },
                    }, ws);
                    break;
                }
                case 'room.knock': {
                    const { roomId, roomName } = event.payload;
                    const user = conn.user;
                    if (!user.mapId)
                        break;
                    let occupants = presence.getRoomOccupants(user.organizationId, user.mapId, roomId);
                    if (occupants.length === 0) {
                        // Broadcast knock request to map occupants so any person in/managing the room receives it
                        const mapOccupants = presence.getMapOccupants(user.organizationId, user.mapId).filter((u) => u.userId !== user.userId);
                        for (const occ of mapOccupants) {
                            const occWs = presence.findUserSocket(occ.userId);
                            if (occWs) {
                                presence.send(occWs, {
                                    type: 'room.knock_request',
                                    payload: {
                                        roomId,
                                        roomName: roomName || 'Private Room',
                                        requesterUserId: user.userId,
                                        requesterName: user.name,
                                    },
                                });
                            }
                        }
                        console.log(`[WS] ${user.name} sent knock alert for room ${roomId} to ${mapOccupants.length} map colleagues.`);
                    }
                    else {
                        // Room has explicit occupants -> send knock alert to them
                        for (const occ of occupants) {
                            if (occ.user.userId !== user.userId) {
                                presence.send(occ.ws, {
                                    type: 'room.knock_request',
                                    payload: {
                                        roomId,
                                        roomName: roomName || 'Private Room',
                                        requesterUserId: user.userId,
                                        requesterName: user.name,
                                    },
                                });
                            }
                        }
                        console.log(`[WS] ${user.name} is knocking on door of room ${roomId} with ${occupants.length} occupants.`);
                    }
                    break;
                }
                case 'room.knock_response': {
                    const { roomId, requesterUserId, approved } = event.payload;
                    // Only someone inside the room can let a knocker in (or turn them away).
                    if (conn.user.isGuest || conn.user.currentRoomId !== roomId)
                        break;
                    const reqWs = presence.findUserSocket(requesterUserId);
                    if (reqWs) {
                        presence.send(reqWs, {
                            type: 'room.knock_result',
                            payload: {
                                roomId,
                                approved,
                                responderName: conn.user.name,
                            },
                        });
                        console.log(`[WS] Host ${conn.user.name} responded ${approved ? 'APPROVED' : 'DENIED'} to ${requesterUserId} for room ${roomId}`);
                    }
                    break;
                }
                case 'room.door_toggle': {
                    const { roomId, isClosed } = event.payload;
                    const user = conn.user;
                    if (!user.mapId)
                        break;
                    // Guests never change doors. Members change a door only from inside the room;
                    // anyone may re-open a door once nobody is left inside (empty rooms rest unlocked).
                    if (user.isGuest) {
                        console.log(`[WS] Blocked guest ${user.name} from modifying door state.`);
                        break;
                    }
                    if (typeof roomId !== 'string' || !roomId)
                        break;
                    const inside = user.currentRoomId === roomId;
                    const roomEmpty = presence.getRoomOccupants(user.organizationId, user.mapId, roomId).length === 0;
                    if (!inside && !(isClosed === false && roomEmpty)) {
                        console.log(`[WS] Blocked ${user.name} from changing door of room ${roomId} from outside.`);
                        break;
                    }
                    shared.setDoor(user.organizationId, user.mapId, roomId, isClosed === true);
                    presence.broadcastToMap(user.organizationId, user.mapId, {
                        type: 'room.door_updated',
                        payload: {
                            roomId,
                            isClosed,
                            toggledBy: user.name,
                        },
                    });
                    console.log(`[WS] ${user.name} set door of room ${roomId} to ${isClosed ? 'CLOSED/LOCKED' : 'OPEN'}`);
                    break;
                }
                case 'presentation.start': {
                    const user = conn.user;
                    if (!user.mapId)
                        break;
                    presence.broadcastToMap(user.organizationId, user.mapId, {
                        type: 'presentation.started',
                        payload: {
                            presenterId: user.userId,
                            presenterName: user.name,
                        },
                    }, ws);
                    console.log(`[WS] ${user.name} started screen presentation on map ${user.mapId}`);
                    break;
                }
                case 'presentation.stop': {
                    const user = conn.user;
                    if (!user.mapId)
                        break;
                    presence.broadcastToMap(user.organizationId, user.mapId, {
                        type: 'presentation.stopped',
                        payload: {
                            presenterId: user.userId,
                        },
                    }, ws);
                    console.log(`[WS] ${user.name} stopped screen presentation`);
                    break;
                }
                case 'media.state': {
                    const user = conn.user;
                    if (!user.mapId)
                        break;
                    user.camActive = !!event.payload.camActive;
                    user.micActive = !!event.payload.micActive;
                    presence.broadcastToMap(user.organizationId, user.mapId, {
                        type: 'media.state_updated',
                        payload: {
                            userId: user.userId,
                            camActive: user.camActive,
                            micActive: user.micActive,
                        },
                    }, ws);
                    break;
                }
                case 'chat.bubble': {
                    // The bubble over the avatar repeats the message, so it reaches exactly who the message did.
                    const { text, scope, roomId } = event.payload;
                    const user = conn.user;
                    if (!user.mapId)
                        break;
                    const route = chatRecipients(user, scope, roomId);
                    if (!route)
                        break;
                    sendToEach(route.recipients, {
                        type: 'chat.bubble',
                        payload: {
                            userId: user.userId,
                            userName: user.name,
                            text: typeof text === 'string' ? text.slice(0, 300) : '',
                            scope: route.scope,
                            roomId: route.roomId,
                        },
                    }, ws);
                    break;
                }
                case 'user.reaction': {
                    const user = conn.user;
                    if (!user.mapId)
                        break;
                    presence.broadcastToMap(user.organizationId, user.mapId, {
                        type: 'user.reaction',
                        payload: {
                            userId: user.userId,
                            userName: user.name,
                            emoji: event.payload.emoji,
                        },
                    });
                    break;
                }
                case 'user.wave': {
                    const user = conn.user;
                    const { targetUserId } = event.payload;
                    const targetWs = presence.findUserSocket(targetUserId);
                    if (targetWs) {
                        presence.send(targetWs, {
                            type: 'user.wave',
                            payload: {
                                senderUserId: user.userId,
                                senderName: user.name,
                                targetUserId,
                            },
                        });
                        console.log(`[WS] ${user.name} waved 👋 at ${targetUserId}`);
                    }
                    break;
                }
                case 'user.ring':
                case 'call.invite':
                case 'call.accept':
                case 'call.decline':
                case 'call.cancel':
                case 'dm.notify': {
                    const user = conn.user;
                    const p = event.payload || {};
                    const targetUserId = typeof p.targetUserId === 'string' ? p.targetUserId : '';
                    if (!targetUserId || targetUserId === user.userId)
                        break;
                    const targetWs = presence.findUserSocket(targetUserId);
                    const target = targetWs ? presence.getClient(targetWs) : undefined;
                    // Never relay across organizations.
                    if (!targetWs || !target || target.user.organizationId !== user.organizationId)
                        break;
                    const clip = (v, max) => (typeof v === 'string' ? v.slice(0, max) : undefined);
                    presence.send(targetWs, {
                        type: event.type,
                        payload: {
                            targetUserId,
                            senderUserId: user.userId,
                            senderName: user.name,
                            senderAvatarUrl: user.avatarUrl,
                            callId: clip(p.callId, 64),
                            reason: clip(p.reason, 32),
                            channelId: clip(p.channelId, 64),
                            preview: clip(p.preview, 140),
                        },
                    });
                    break;
                }
                case 'user.sit': {
                    const user = conn.user;
                    if (!user.mapId)
                        break;
                    const { isSitting, furnitureId, seatPosition } = event.payload;
                    if (seatPosition) {
                        user.position = { ...seatPosition };
                    }
                    presence.broadcastToMap(user.organizationId, user.mapId, {
                        type: 'user.sit_updated',
                        payload: {
                            userId: user.userId,
                            isSitting,
                            furnitureId,
                            seatPosition,
                        },
                    });
                    console.log(`[WS] ${user.name} is now ${isSitting ? 'SITTING at ' + furnitureId : 'STANDING'}`);
                    break;
                }
                case 'whiteboard.draw': {
                    const user = conn.user;
                    if (!user.mapId)
                        break;
                    const roomId = boardId(event.payload?.roomId);
                    const stroke = (0, shared_state_js_1.cleanStroke)(event.payload?.stroke);
                    if (!stroke)
                        break;
                    shared.addStroke(user.organizationId, user.mapId, roomId, stroke);
                    presence.broadcastToMap(user.organizationId, user.mapId, {
                        type: 'whiteboard.draw',
                        payload: {
                            roomId,
                            senderUserId: user.userId,
                            stroke,
                        },
                    }, ws);
                    break;
                }
                // Opening a board: send what's already on it (strokes and sticky notes).
                case 'whiteboard.sync': {
                    const user = conn.user;
                    if (!user.mapId)
                        break;
                    const roomId = boardId(event.payload?.roomId);
                    presence.send(ws, { type: 'whiteboard.history', payload: { roomId, ...shared.history(user.organizationId, user.mapId, roomId) } });
                    break;
                }
                case 'whiteboard.note_add': {
                    const user = conn.user;
                    if (!user.mapId)
                        break;
                    const roomId = boardId(event.payload?.roomId);
                    const note = (0, shared_state_js_1.cleanNote)(event.payload?.note, user.name);
                    if (!note)
                        break;
                    shared.addNote(user.organizationId, user.mapId, roomId, note);
                    presence.broadcastToMap(user.organizationId, user.mapId, { type: 'whiteboard.note_added', payload: { roomId, note } });
                    break;
                }
                case 'whiteboard.note_remove': {
                    const user = conn.user;
                    if (!user.mapId)
                        break;
                    const roomId = boardId(event.payload?.roomId);
                    const id = String(event.payload?.id ?? '');
                    if (shared.removeNote(user.organizationId, user.mapId, roomId, id)) {
                        presence.broadcastToMap(user.organizationId, user.mapId, { type: 'whiteboard.note_removed', payload: { roomId, id } });
                    }
                    break;
                }
                case 'whiteboard.clear': {
                    const user = conn.user;
                    if (!user.mapId)
                        break;
                    const roomId = boardId(event.payload?.roomId);
                    shared.clearStrokes(user.organizationId, user.mapId, roomId);
                    presence.broadcastToMap(user.organizationId, user.mapId, {
                        type: 'whiteboard.clear',
                        payload: {
                            roomId,
                            clearedBy: user.name,
                        },
                    }, ws);
                    break;
                }
                case 'webrtc.signal': {
                    const user = conn.user;
                    const { targetUserId, signal } = event.payload || {};
                    if (targetUserId) {
                        const targetWs = presence.findUserSocket(targetUserId);
                        if (targetWs) {
                            presence.send(targetWs, {
                                type: 'webrtc.signal',
                                payload: {
                                    senderUserId: user.userId,
                                    senderName: user.name,
                                    signal,
                                },
                            });
                        }
                    }
                    break;
                }
            }
        }
        catch (err) {
            console.error('[WS] Message parse error:', err);
        }
    });
    // Handle client disconnect
    ws.on('close', () => {
        const leftRoomId = conn.user.currentRoomId;
        const left = presence.removeClient(ws);
        if (left && left.mapId)
            reopenIfEmpty(left.orgId, left.mapId, leftRoomId);
        if (left && left.mapId) {
            presence.broadcastToMap(left.orgId, left.mapId, {
                type: 'user.left',
                payload: {
                    userId: left.userId,
                    mapId: left.mapId,
                },
            });
            const mapCounts = presence.getOrganizationMapOccupancyCounts(left.orgId);
            presence.broadcastToOrganization(left.orgId, {
                type: 'organization.map_occupancy',
                payload: { counts: mapCounts },
            });
            console.log(`[WS] User disconnected: ${conn.user.name} from map ${left.mapId}`);
        }
    });
});
// Periodic heartbeat / ping-pong
const interval = setInterval(() => {
    wss.clients.forEach((ws) => {
        if (ws.readyState === ws_1.WebSocket.OPEN) {
            ws.ping();
        }
    });
}, 30000);
wss.on('close', () => {
    clearInterval(interval);
});
server.listen(PORT, HOST, () => {
    console.log(`🚀 Virtual Workplace Realtime Service listening on ws://${HOST}:${PORT}`);
});
