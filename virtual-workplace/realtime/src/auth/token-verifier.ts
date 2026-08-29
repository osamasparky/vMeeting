import crypto from 'crypto';
import { TokenPayload } from '../events/event-types.js';

export class TokenVerifier {
  private secrets: string[];

  constructor(secret?: string) {
    const candidateSecrets = [
      secret,
      process.env.REALTIME_SECRET,
      process.env.APP_KEY,
      'nextspace_super_secure_realtime_jwt_secret_key_2026',
      'base64:9fj2ZRPjCy3ClL13gPaYCv9gl8GsE8APwzVK8EceIRM='
    ].filter(Boolean) as string[];

    this.secrets = [...new Set(candidateSecrets)];
  }

  /**
   * Verify and decode HMAC-SHA256 token minted by Laravel RealtimeTokenService.
   */
  public verify(token: string): TokenPayload | null {
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
    const normalizeB64 = (s: string) => s.replace(/-/g, '+').replace(/_/g, '/');
    const normHeader = normalizeB64(headerB64);
    const normPayload = normalizeB64(payloadB64);
    const normSignature = normalizeB64(signatureB64);

    // Verify HMAC signature against candidate secrets
    let signatureMatched = false;
    for (const sec of this.secrets) {
      const rawSig = crypto.createHmac('sha256', sec).update(`${headerB64}.${payloadB64}`).digest();
      const expectedB64 = rawSig.toString('base64');
      const expectedB64Url = rawSig.toString('base64url');

      if (
        expectedB64 === signatureB64 ||
        expectedB64 === normSignature ||
        expectedB64Url === signatureB64 ||
        rawSig.toString('base64').replace(/=/g, '') === signatureB64.replace(/=/g, '')
      ) {
        signatureMatched = true;
        break;
      }
    }

    if (!signatureMatched) {
      return null;
    }

    try {
      const payloadJson = Buffer.from(normPayload, 'base64').toString('utf-8');
      const payload: TokenPayload = JSON.parse(payloadJson);

      // Check expiration
      if (payload.exp && payload.exp < Math.floor(Date.now() / 1000)) {
        return null;
      }

      return payload;
    } catch {
      return null;
    }
  }
}
