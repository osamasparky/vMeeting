"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.TokenVerifier = void 0;
const crypto_1 = __importDefault(require("crypto"));
class TokenVerifier {
    secret;
    constructor(secret) {
        this.secret = secret || process.env.REALTIME_SECRET || '';
        if (!this.secret) {
            console.warn('[SECURITY WARNING] REALTIME_SECRET is not configured. WebSocket tokens will fail verification.');
        }
    }
    /**
     * Verify and decode HMAC-SHA256 token minted by Laravel RealtimeTokenService.
     */
    verify(token) {
        if (!token || typeof token !== 'string') {
            return null;
        }
        const parts = token.split('.');
        if (parts.length !== 3) {
            return null;
        }
        const [headerB64, payloadB64, signatureB64] = parts;
        // Verify HMAC signature
        const expectedSignature = crypto_1.default
            .createHmac('sha256', this.secret)
            .update(`${headerB64}.${payloadB64}`)
            .digest('base64');
        if (expectedSignature !== signatureB64) {
            return null;
        }
        try {
            const payloadJson = Buffer.from(payloadB64, 'base64').toString('utf-8');
            const payload = JSON.parse(payloadJson);
            // Check expiration
            if (payload.exp && payload.exp < Math.floor(Date.now() / 1000)) {
                return null;
            }
            return payload;
        }
        catch {
            return null;
        }
    }
}
exports.TokenVerifier = TokenVerifier;
