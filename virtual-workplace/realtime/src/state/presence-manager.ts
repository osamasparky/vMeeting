import { WebSocket } from 'ws';
import {
  OfficeUser,
  OutboundEvent,
  TokenPayload,
  UserPosition,
  UserStatus,
} from '../events/event-types.js';
import { ProximityCalculator } from './proximity-calculator.js';

export interface ClientConnection {
  ws: WebSocket;
  user: OfficeUser;
  lastPing: number;
}

export class PresenceManager {
  // Map of ws -> ClientConnection
  private clients: Map<WebSocket, ClientConnection> = new Map();

  // Map of `${orgId}:${mapId}` -> Set of WebSocket
  private mapRooms: Map<string, Set<WebSocket>> = new Map();

  private proximityCalculator: ProximityCalculator;

  constructor() {
    this.proximityCalculator = new ProximityCalculator(150);
  }

  public registerClient(ws: WebSocket, token: TokenPayload): ClientConnection {
    const user: OfficeUser = {
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

    const connection: ClientConnection = {
      ws,
      user,
      lastPing: Date.now(),
    };

    this.clients.set(ws, connection);
    return connection;
  }

  public joinMap(ws: WebSocket, mapId: string, initialPos?: UserPosition): OfficeUser | null {
    const conn = this.clients.get(ws);
    if (!conn) return null;

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
    this.mapRooms.get(roomKey)!.add(ws);

    return conn.user;
  }

  public leaveMap(ws: WebSocket): { mapId: string; userId: string; orgId: string } | null {
    const conn = this.clients.get(ws);
    if (!conn || !conn.user.mapId) return null;

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

  public removeClient(ws: WebSocket): { mapId: string; userId: string; orgId: string } | null {
    const leftInfo = this.leaveMap(ws);
    this.clients.delete(ws);
    return leftInfo;
  }

  public getMapOccupants(organizationId: string, mapId?: string): OfficeUser[] {
    const occupants: OfficeUser[] = [];
    for (const conn of this.clients.values()) {
      if (conn.user.organizationId === organizationId) {
        occupants.push(conn.user);
      }
    }
    return occupants;
  }

  public updatePosition(ws: WebSocket, position: UserPosition): OfficeUser | null {
    const conn = this.clients.get(ws);
    if (!conn) return null;

    conn.user.position = { ...position };
    conn.user.lastActive = Date.now();
    return conn.user;
  }

  public updateStatus(ws: WebSocket, status: UserStatus): OfficeUser | null {
    const conn = this.clients.get(ws);
    if (!conn) return null;

    conn.user.status = status;
    conn.user.lastActive = Date.now();
    return conn.user;
  }

  public setRoom(ws: WebSocket, roomId: string | null): OfficeUser | null {
    const conn = this.clients.get(ws);
    if (!conn) return null;

    conn.user.currentRoomId = roomId;
    conn.user.lastActive = Date.now();
    return conn.user;
  }

  public calculateProximity(user: OfficeUser): string[] {
    const occupants = this.getMapOccupants(user.organizationId, user.mapId);
    return this.proximityCalculator.getNearbyUsers(user, occupants);
  }

  /**
   * Broadcast an event to all users in the same organization and map.
   */
  public broadcastToMap(
    organizationId: string,
    mapId: string,
    event: OutboundEvent,
    excludeWs?: WebSocket
  ): void {
    const messageStr = JSON.stringify(event);
    for (const [socket, conn] of this.clients.entries()) {
      if (socket !== excludeWs && conn.user.organizationId === organizationId && socket.readyState === WebSocket.OPEN) {
        socket.send(messageStr);
      }
    }
  }

  /**
   * Find the WebSocket belonging to a specific user ID.
   */
  public findUserSocket(userId: string): WebSocket | null {
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
  public getRoomOccupants(organizationId: string, mapId: string, roomId: string): { user: OfficeUser; ws: WebSocket }[] {
    const occupants = this.getMapOccupants(organizationId, mapId);
    const result: { user: OfficeUser; ws: WebSocket }[] = [];
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
  public send(ws: WebSocket, event: OutboundEvent): void {
    if (ws.readyState === WebSocket.OPEN) {
      ws.send(JSON.stringify(event));
    }
  }
}

