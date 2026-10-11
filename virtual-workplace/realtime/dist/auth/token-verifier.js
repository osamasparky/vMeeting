"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.TokenVerifier = void 0;
const crypto_1 = __importDefault(require("crypto"));
class TokenVerifier {
    secrets;
    constructor(secret) {
        // Laravel signs with REALTIME_SECRET only. No APP_KEY or built-in fallback: a secret
        // that is written in the source would let anyone mint a token for any user.
        const candidateSecrets = [secret, process.env.REALTIME_SECRET].filter(Boolean);
        if (candidateSecrets.length === 0) {
            console.error('[Auth] REALTIME_SECRET is not set: every office connection will be refused.');
        }
        this.secrets = [...new Set(candidateSecrets)];
    }
    /**
     * Verify and decode HMAC-SHA256 token minted by Laravel RealtimeTokenService.
     */
    verify(token) {
        if (!token || typeof token !== 'string') {
            return null;
        }
        // Clean token: unescape query string spaces
        const cleanToken = token.trim().replace(/ /g, '+');
        const parts = cleanToken.split('.');
        if (parts.length !== 3) {
            return null;
        }
        const [headerB64, payloadB64, signatureB64] = parts;
        // Normalize base64 vs base64url
        const normalizeB64 = (s) => s.replace(/-/g, '+').replace(/_/g, '/');
        const normHeader = normalizeB64(headerB64);
        const normPayload = normalizeB64(payloadB64);
        const normSignature = normalizeB64(signatureB64);
        // Verify HMAC signature against candidate secrets
        let signatureMatched = false;
        for (const sec of this.secrets) {
            const rawSig = crypto_1.default.createHmac('sha256', sec).update(`${headerB64}.${payloadB64}`).digest();
            const expectedB64 = rawSig.toString('base64');
            const expectedB64Url = rawSig.toString('base64url');
            if (expectedB64 === signatureB64 ||
                expectedB64 === normSignature ||
                expectedB64Url === signatureB64 ||
                rawSig.toString('base64').replace(/=/g, '') === signatureB64.replace(/=/g, '')) {
                signatureMatched = true;
                break;
            }
        }
        if (!signatureMatched) {
            return null;
        }
        try {
            const payloadJson = Buffer.from(normPayload, 'base64').toString('utf-8');
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
