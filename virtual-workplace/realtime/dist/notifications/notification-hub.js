"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.NotificationHub = void 0;
exports.secretMatches = secretMatches;
exports.parsePublishBody = parsePublishBody;
const crypto_1 = __importDefault(require("crypto"));
const ws_1 = require("ws");
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
class NotificationHub {
    sockets = new Map();
    add(userId, ws) {
        if (!this.sockets.has(userId))
            this.sockets.set(userId, new Set());
        this.sockets.get(userId).add(ws);
    }
    remove(userId, ws) {
        const set = this.sockets.get(userId);
        if (!set)
            return;
        set.delete(ws);
        if (set.size === 0)
            this.sockets.delete(userId);
    }
    /** @returns how many sockets were pinged */
    publish(userIds) {
        let sent = 0;
        const message = JSON.stringify({ type: 'notification.new', payload: {} });
        for (const userId of new Set(userIds)) {
            for (const ws of this.sockets.get(userId) ?? []) {
                if (ws.readyState === ws_1.WebSocket.OPEN) {
                    ws.send(message);
                    sent++;
                }
            }
        }
        return sent;
    }
    connectionCount() {
        let n = 0;
        for (const set of this.sockets.values())
            n += set.size;
        return n;
    }
}
exports.NotificationHub = NotificationHub;
/** Constant-time check of the shared secret Laravel sends on internal calls. */
function secretMatches(provided, secret) {
    if (!provided || !secret)
        return false;
    const a = Buffer.from(provided);
    const b = Buffer.from(secret);
    return a.length === b.length && crypto_1.default.timingSafeEqual(a, b);
}
/** Parses `{ "user_ids": [...] }`; returns null when the body is not that shape. */
function parsePublishBody(raw) {
    try {
        const body = JSON.parse(raw);
        if (!body || !Array.isArray(body.user_ids) || body.user_ids.length > 1000)
            return null;
        const ids = body.user_ids.filter((v) => typeof v === 'string' && v.length > 0 && v.length <= 64);
        return ids.length === body.user_ids.length ? ids : null;
    }
    catch {
        return null;
    }
}
