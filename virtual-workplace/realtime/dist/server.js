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
dotenv_1.default.config();
const PORT = parseInt(process.env.REALTIME_PORT || '8080', 10);
const HOST = process.env.REALTIME_HOST || '0.0.0.0';
const server = http_1.default.createServer((req, res) => {
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
                    const { channelId, body } = event.payload;
                    const user = conn.user;
                    if (!user.mapId)
                        break;
                    presence.broadcastToMap(user.organizationId, user.mapId, {
                        type: 'chat.message',
                        payload: {
                            channelId,
                            senderId: user.userId,
                            senderName: user.name,
                            body,
                            timestamp: new Date().toISOString(),
                        },
                    });
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
                    // Restrict guest users from closing or opening doors
                    if (user.name.includes('(Guest)')) {
                        console.log(`[WS] Blocked guest ${user.name} from modifying door state.`);
                        break;
                    }
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
                    const user = conn.user;
                    if (!user.mapId)
                        break;
                    presence.broadcastToMap(user.organizationId, user.mapId, {
                        type: 'chat.bubble',
                        payload: {
                            userId: user.userId,
                            userName: user.name,
                            text: event.payload.text,
                        },
                    });
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
                    const { roomId, stroke } = event.payload;
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
                case 'whiteboard.clear': {
                    const user = conn.user;
                    if (!user.mapId)
                        break;
                    const { roomId } = event.payload;
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
                    // Deprecated: WebRTC media plane migrated to LiveKit SFU.
                    console.log(`[WS] webrtc.signal received from ${conn.user.name} - media is managed via LiveKit SFU.`);
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
        const left = presence.removeClient(ws);
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
