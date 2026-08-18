"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.PresenceManager = void 0;
const ws_1 = require("ws");
const proximity_calculator_js_1 = require("./proximity-calculator.js");
class PresenceManager {
    // Map of ws -> ClientConnection
    clients = new Map();
    // Map of `${orgId}:${mapId}` -> Set of WebSocket
    mapRooms = new Map();
    proximityCalculator;
    constructor() {
        this.proximityCalculator = new proximity_calculator_js_1.ProximityCalculator(150);
    }
    registerClient(ws, token) {
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
    joinMap(ws, mapId, initialPos) {
        const conn = this.clients.get(ws);
        if (!conn)
            return null;
        // Leave previous map if any
        if (conn.user.mapId) {
            this.leaveMap(ws);
        }
        conn.user.mapId = mapId;
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
                occupants.push(conn.user);
            }
        }
        return occupants;
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
            if (socket !== excludeWs && conn.user.organizationId === organizationId && socket.readyState === ws_1.WebSocket.OPEN) {
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
