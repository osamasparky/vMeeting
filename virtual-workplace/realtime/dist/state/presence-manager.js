"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.PresenceManager = void 0;
const ws_1 = require("ws");
const proximity_calculator_js_1 = require("./proximity-calculator.js");
/**
 * PresenceManager
 *
 * Manages active connected clients, spatial positions, room occupants, and map rooms.
 *
 * ── HORIZONTAL SCALING ROADMAP (Redis Adapter Backlog) ──
 * For multi-instance horizontal scaling behind an L4/L7 load balancer:
 * 1. State Storage: Replace in-memory `clients` & `mapRooms` with Redis Hashes:
 *    - `hset office:user:{userId} mapId orgId position status lastActive`
 *    - `sadd office:map:{orgId}:{mapId}:occupants userId`
 * 2. Cross-Instance Broadcasting: Use Redis Pub/Sub:
 *    - Channel `office:map:{orgId}:{mapId}:events`
 *    - Every node subscribes to active local rooms and relays messages to local WebSocket clients.
 * 3. Heartbeat / TTL: Expire user keys with `EXPIRE office:user:{userId} 60`.
 */
class PresenceManager {
    // Map of ws -> ClientConnection
    clients = new Map();
    // Map of `${orgId}:${mapId}` -> Set of WebSocket
    mapRooms = new Map();
    proximityCalculator;
    constructor() {
        this.proximityCalculator = new proximity_calculator_js_1.ProximityCalculator(150);
    }
    /**
     * Register a new client connection with strict session locking.
     * If a previous active connection exists for the same userId (across any map or org),
     * the previous connection is gracefully terminated and replaced.
     */
    registerClient(ws, token) {
        // 1. Enforce single active session per user across all organizations and maps
        for (const [otherWs, otherConn] of this.clients.entries()) {
            if (otherWs !== ws && otherConn.user.userId === token.sub) {
                console.log(`[WS] Duplicate session detected for user ${otherConn.user.name} (${token.sub}). Replacing previous session.`);
                // Notify old connection
                this.send(otherWs, {
                    type: 'session.replaced',
                    payload: {
                        reason: 'Your account was opened in another window, tab, or office branch.',
                        timestamp: new Date().toISOString(),
                    },
                });
                // Broadcast departure from previous map if applicable
                if (otherConn.user.mapId) {
                    const oldMapId = otherConn.user.mapId;
                    const oldOrgId = otherConn.user.organizationId;
                    this.leaveMap(otherWs);
                    this.broadcastToMap(oldOrgId, oldMapId, {
                        type: 'user.left',
                        payload: { userId: token.sub, mapId: oldMapId },
                    });
                }
                try {
                    otherWs.close(4003, 'Session replaced by new connection');
                }
                catch (e) { }
                this.clients.delete(otherWs);
            }
        }
        const user = {
            userId: token.sub,
            organizationId: token.organization_id,
            mapId: '',
            name: token.name,
            avatarUrl: token.avatar_url,
            status: 'available',
            position: { x: 100, y: 100, direction: 'down', isMoving: false },
            currentRoomId: null,
            currentZoneId: null,
            lastActive: Date.now(),
        };
        const connection = {
            ws,
            user,
            lastPing: Date.now(),
        };
        this.clients.set(ws, connection);
        return connection;
    }
    joinMap(ws, mapId, initialPos, gender) {
        const conn = this.clients.get(ws);
        if (!conn)
            return null;
        // Leave previous map if any on this socket
        if (conn.user.mapId) {
            this.leaveMap(ws);
        }
        conn.user.mapId = mapId;
        if (gender) {
            conn.user.gender = gender;
        }
        if (initialPos) {
            conn.user.position = { ...initialPos };
        }
        const roomKey = `${conn.user.organizationId}:${mapId}`;
        if (!this.mapRooms.has(roomKey)) {
            this.mapRooms.set(roomKey, new Set());
        }
        this.mapRooms.get(roomKey).add(ws);
        return conn.user;
    }
    leaveMap(ws) {
        const conn = this.clients.get(ws);
        if (!conn || !conn.user.mapId)
            return null;
        const { mapId, userId, organizationId } = conn.user;
        const roomKey = `${organizationId}:${mapId}`;
        const room = this.mapRooms.get(roomKey);
        if (room) {
            room.delete(ws);
            if (room.size === 0) {
                this.mapRooms.delete(roomKey);
            }
        }
        conn.user.mapId = '';
        conn.user.currentRoomId = null;
        return { mapId, userId, orgId: organizationId };
    }
    removeClient(ws) {
        const leftInfo = this.leaveMap(ws);
        this.clients.delete(ws);
        return leftInfo;
    }
    getMapOccupants(organizationId, mapId) {
        const occupants = [];
        for (const conn of this.clients.values()) {
            if (conn.user.organizationId === organizationId) {
                if (!mapId || conn.user.mapId === mapId) {
                    occupants.push(conn.user);
                }
            }
        }
        return occupants;
    }
    getOrganizationOccupants(organizationId) {
        const occupants = [];
        for (const conn of this.clients.values()) {
            if (conn.user.organizationId === organizationId) {
                occupants.push(conn.user);
            }
        }
        return occupants;
    }
    getOrganizationMapOccupancyCounts(organizationId) {
        const counts = {};
        for (const conn of this.clients.values()) {
            if (conn.user.organizationId === organizationId && conn.user.mapId) {
                counts[conn.user.mapId] = (counts[conn.user.mapId] || 0) + 1;
            }
        }
        return counts;
    }
    updatePosition(ws, position) {
        const conn = this.clients.get(ws);
        if (!conn)
            return null;
        conn.user.position = { ...position };
        conn.user.lastActive = Date.now();
        return conn.user;
    }
    updateStatus(ws, status) {
        const conn = this.clients.get(ws);
        if (!conn)
            return null;
        conn.user.status = status;
        conn.user.lastActive = Date.now();
        return conn.user;
    }
    setRoom(ws, roomId) {
        const conn = this.clients.get(ws);
        if (!conn)
            return null;
        conn.user.currentRoomId = roomId;
        conn.user.lastActive = Date.now();
        return conn.user;
    }
    calculateProximity(user) {
        const occupants = this.getMapOccupants(user.organizationId, user.mapId);
        return this.proximityCalculator.getNearbyUsers(user, occupants);
    }
    /**
     * Broadcast an event to all users in the same organization and map.
     */
    broadcastToMap(organizationId, mapId, event, excludeWs) {
        const messageStr = JSON.stringify(event);
        for (const [socket, conn] of this.clients.entries()) {
            if (socket !== excludeWs &&
                conn.user.organizationId === organizationId &&
                (!mapId || conn.user.mapId === mapId) &&
                socket.readyState === ws_1.WebSocket.OPEN) {
                socket.send(messageStr);
            }
        }
    }
    /**
     * Broadcast an event across the entire organization (e.g. global chat, organization presence).
     */
    broadcastToOrganization(organizationId, event, excludeWs) {
        const messageStr = JSON.stringify(event);
        for (const [socket, conn] of this.clients.entries()) {
            if (socket !== excludeWs &&
                conn.user.organizationId === organizationId &&
                socket.readyState === ws_1.WebSocket.OPEN) {
                socket.send(messageStr);
            }
        }
    }
    /**
     * Find the WebSocket belonging to a specific user ID.
     */
    findUserSocket(userId) {
        for (const [ws, conn] of this.clients.entries()) {
            if (conn.user.userId === userId) {
                return ws;
            }
        }
        return null;
    }
    /**
     * Get all occupants currently inside a specific room along with their sockets.
     */
    getRoomOccupants(organizationId, mapId, roomId) {
        const occupants = this.getMapOccupants(organizationId, mapId);
        const result = [];
        for (const u of occupants) {
            if (u.currentRoomId === roomId) {
                const ws = this.findUserSocket(u.userId);
                if (ws) {
                    result.push({ user: u, ws });
                }
            }
        }
        return result;
    }
    /**
     * Send event directly to a single client socket.
     */
    send(ws, event) {
        if (ws.readyState === ws_1.WebSocket.OPEN) {
            ws.send(JSON.stringify(event));
        }
    }
}
exports.PresenceManager = PresenceManager;
