import crypto from 'crypto';
import { WebSocket } from 'ws';

/**
 * NotificationHub
 *
 * Bell connections (`?channel=notifications`) live here, apart from office
 * presence: a user may hold any number of them (one per open tab) without
 * replacing their office session, and they never appear as occupants.
 *
 * Laravel tells the hub "these users have something new" over HTTP; the hub
 * forwards a content-free `notification.new` ping and the browser fetches
 * the notifications itself through the authenticated web API.
 */
export class NotificationHub {
  private sockets: Map<string, Set<WebSocket>> = new Map();

  public add(userId: string, ws: WebSocket): void {
    if (!this.sockets.has(userId)) this.sockets.set(userId, new Set());
    this.sockets.get(userId)!.add(ws);
  }

  public remove(userId: string, ws: WebSocket): void {
    const set = this.sockets.get(userId);
    if (!set) return;
    set.delete(ws);
    if (set.size === 0) this.sockets.delete(userId);
  }

  /** @returns how many sockets were pinged */
  public publish(userIds: string[]): number {
    let sent = 0;
    const message = JSON.stringify({ type: 'notification.new', payload: {} });
    for (const userId of new Set(userIds)) {
      for (const ws of this.sockets.get(userId) ?? []) {
        if (ws.readyState === WebSocket.OPEN) {
          ws.send(message);
          sent++;
        }
      }
    }
    return sent;
  }

  public connectionCount(): number {
    let n = 0;
    for (const set of this.sockets.values()) n += set.size;
    return n;
  }
}

/** Constant-time check of the shared secret Laravel sends on internal calls. */
export function secretMatches(provided: string | undefined, secret: string | undefined): boolean {
  if (!provided || !secret) return false;
  const a = Buffer.from(provided);
  const b = Buffer.from(secret);
  return a.length === b.length && crypto.timingSafeEqual(a, b);
}

/** Parses `{ "user_ids": [...] }`; returns null when the body is not that shape. */
export function parsePublishBody(raw: string): string[] | null {
  try {
    const body = JSON.parse(raw);
    if (!body || !Array.isArray(body.user_ids) || body.user_ids.length > 1000) return null;
    const ids = body.user_ids.filter((v: unknown): v is string => typeof v === 'string' && v.length > 0 && v.length <= 64);
    return ids.length === body.user_ids.length ? ids : null;
  } catch {
    return null;
  }
}
