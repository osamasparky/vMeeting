/**
 * Strict Typed WebSocket Event Contracts for Virtual Workplace
 * Matches Blueprint §G
 */

export interface TokenPayload {
  sub: string; // user_id
  name: string;
  email: string;
  avatar_url?: string;
  organization_id: string;
  role: string;
  iat: number;
  exp: number;
}

export interface UserPosition {
  x: number;
  y: number;
  direction?: 'up' | 'down' | 'left' | 'right';
  isMoving?: boolean;
}

export type UserStatus = 'available' | 'busy' | 'away' | 'focus' | 'offline';

export interface OfficeUser {
  userId: string;
  organizationId: string;
  mapId: string;
  name: string;
  avatarUrl?: string;
  gender?: string;
  status: UserStatus;
  position: UserPosition;
  currentRoomId?: string | null;
  currentZoneId?: string | null;
  lastActive: number;
}

// ── Inbound Client Events ──

export interface InboundJoinMap {
  type: 'map.join';
  payload: {
    mapId: string;
    initialPosition?: UserPosition;
    gender?: string;
  };
}

export interface InboundPositionUpdate {
  type: 'position.update';
  payload: UserPosition;
}

export interface InboundStatusUpdate {
  type: 'status.update';
  payload: {
    status: UserStatus;
  };
}

export interface InboundRoomEnter {
  type: 'room.enter';
  payload: {
    roomId: string;
  };
}

export interface InboundRoomLeave {
  type: 'room.leave';
  payload: {
    roomId: string;
  };
}

export interface InboundChatMessage {
  type: 'chat.send';
  payload: {
    channelId: string;
    body: string;
  };
}

export interface InboundRoomKnock {
  type: 'room.knock';
  payload: {
    roomId: string;
    roomName?: string;
  };
}

export interface InboundRoomKnockResponse {
  type: 'room.knock_response';
  payload: {
    roomId: string;
    requesterUserId: string;
    approved: boolean;
  };
}

export interface InboundWebRtcSignal {
  type: 'webrtc.signal';
  payload: {
    targetUserId: string;
    signal: any;
  };
}

export interface InboundRoomDoorToggle {
  type: 'room.door_toggle';
  payload: {
    roomId: string;
    isClosed: boolean;
  };
}

export interface InboundAvatarUpdate {
  type: 'avatar.update';
  payload: {
    gender: string;
  };
}

export interface InboundPresentationStart {
  type: 'presentation.start';
  payload?: any;
}

export interface InboundPresentationStop {
  type: 'presentation.stop';
  payload?: any;
}

export interface InboundMediaState {
  type: 'media.state';
  payload: {
    camActive: boolean;
    micActive: boolean;
  };
}

export interface InboundChatBubble {
  type: 'chat.bubble';
  payload: {
    text: string;
  };
}

export interface InboundUserReaction {
  type: 'user.reaction';
  payload: {
    emoji: string;
  };
}

export interface InboundUserWave {
  type: 'user.wave';
  payload: {
    targetUserId: string;
  };
}

export interface InboundWhiteboardDraw {
  type: 'whiteboard.draw';
  payload: {
    roomId: string;
    stroke: any;
  };
}

export interface InboundWhiteboardClear {
  type: 'whiteboard.clear';
  payload: {
    roomId: string;
  };
}

export interface InboundUserSit {
  type: 'user.sit';
  payload: {
    isSitting: boolean;
    furnitureId?: string;
    seatPosition?: UserPosition;
  };
}

export type InboundEvent =
  | InboundJoinMap
  | InboundPositionUpdate
  | InboundStatusUpdate
  | InboundAvatarUpdate
  | InboundPresentationStart
  | InboundPresentationStop
  | InboundMediaState
  | InboundRoomEnter
  | InboundRoomLeave
  | InboundChatMessage
  | InboundChatBubble
  | InboundUserReaction
  | InboundUserWave
  | InboundWhiteboardDraw
  | InboundWhiteboardClear
  | InboundUserSit
  | InboundRoomKnock
  | InboundRoomKnockResponse
  | InboundRoomDoorToggle
  | InboundWebRtcSignal;

// ── Outbound Server Events ──

export interface OutboundWelcome {
  type: 'welcome';
  payload: {
    user: OfficeUser;
    occupants: OfficeUser[];
  };
}

export interface OutboundAvatarUpdated {
  type: 'avatar.updated';
  payload: {
    userId: string;
    gender: string;
  };
}

export interface OutboundPresentationStarted {
  type: 'presentation.started';
  payload: {
    presenterId: string;
    presenterName: string;
  };
}

export interface OutboundPresentationStopped {
  type: 'presentation.stopped';
  payload: {
    presenterId: string;
  };
}

export interface OutboundUserJoined {
  type: 'user.joined';
  payload: OfficeUser;
}

export interface OutboundUserLeft {
  type: 'user.left';
  payload: {
    userId: string;
    mapId: string;
  };
}

export interface OutboundPositionUpdated {
  type: 'position.updated';
  payload: {
    userId: string;
    position: UserPosition;
  };
}

export interface OutboundPresenceUpdated {
  type: 'presence.updated';
  payload: {
    userId: string;
    status: UserStatus;
  };
}

export interface OutboundRoomEntered {
  type: 'room.entered';
  payload: {
    userId: string;
    roomId: string;
  };
}

export interface OutboundRoomLeft {
  type: 'room.left';
  payload: {
    userId: string;
    roomId: string;
  };
}

export interface OutboundProximityChanged {
  type: 'proximity.changed';
  payload: {
    nearbyUserIds: string[];
    audioGroupId: string;
  };
}

export interface OutboundChatMessage {
  type: 'chat.message';
  payload: {
    channelId: string;
    senderId: string;
    senderName: string;
    body: string;
    timestamp: string;
  };
}

export interface OutboundRoomKnockRequest {
  type: 'room.knock_request';
  payload: {
    roomId: string;
    roomName: string;
    requesterUserId: string;
    requesterName: string;
  };
}

export interface OutboundRoomKnockResult {
  type: 'room.knock_result';
  payload: {
    roomId: string;
    approved: boolean;
    responderName: string;
  };
}

export interface OutboundRoomDoorUpdated {
  type: 'room.door_updated';
  payload: {
    roomId: string;
    isClosed: boolean;
    toggledBy: string;
  };
}

export interface OutboundWebRtcSignal {
  type: 'webrtc.signal';
  payload: {
    senderUserId: string;
    senderName: string;
    signal: any;
  };
}

export interface OutboundError {
  type: 'error';
  payload: {
    code: string;
    message: string;
  };
}

export interface OutboundMediaStateUpdated {
  type: 'media.state_updated';
  payload: {
    userId: string;
    camActive: boolean;
    micActive: boolean;
  };
}

export interface OutboundChatBubble {
  type: 'chat.bubble';
  payload: {
    userId: string;
    userName: string;
    text: string;
  };
}

export interface OutboundUserReaction {
  type: 'user.reaction';
  payload: {
    userId: string;
    userName: string;
    emoji: string;
  };
}

export interface OutboundUserWave {
  type: 'user.wave';
  payload: {
    senderUserId: string;
    senderName: string;
    targetUserId: string;
  };
}

export interface OutboundWhiteboardDraw {
  type: 'whiteboard.draw';
  payload: {
    roomId: string;
    senderUserId: string;
    stroke: any;
  };
}

export interface OutboundWhiteboardClear {
  type: 'whiteboard.clear';
  payload: {
    roomId: string;
    clearedBy: string;
  };
}

export interface OutboundUserSit {
  type: 'user.sit_updated';
  payload: {
    userId: string;
    isSitting: boolean;
    furnitureId?: string;
    seatPosition?: UserPosition;
  };
}

export type OutboundEvent =
  | OutboundWelcome
  | OutboundUserJoined
  | OutboundUserLeft
  | OutboundPositionUpdated
  | OutboundPresenceUpdated
  | OutboundAvatarUpdated
  | OutboundPresentationStarted
  | OutboundPresentationStopped
  | OutboundMediaStateUpdated
  | OutboundChatBubble
  | OutboundUserReaction
  | OutboundUserWave
  | OutboundWhiteboardDraw
  | OutboundWhiteboardClear
  | OutboundUserSit
  | OutboundRoomEntered
  | OutboundRoomLeft
  | OutboundProximityChanged
  | OutboundChatMessage
  | OutboundRoomKnockRequest
  | OutboundRoomKnockResult
  | OutboundRoomDoorUpdated
  | OutboundWebRtcSignal
  | OutboundError;



