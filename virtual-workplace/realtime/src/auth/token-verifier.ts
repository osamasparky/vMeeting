import crypto from 'crypto';
import { TokenPayload } from '../events/event-types.js';

export class TokenVerifier {
  private secret: string;

  constructor(secret?: string) {
    this.secret = secret || process.env.REALTIME_SECRET || 'super-secret-virtual-workplace-key-2026';
  }

  /**
   * Verify and decode HMAC-SHA256 token minted by Laravel RealtimeTokenService.
   */
  public verify(token: string): TokenPayload | null {
    if (!token || typeof token !== 'string') {
      return null;
    }

    const parts = token.split('.');
    if (parts.length !== 3) {
      return null;
    }

    const [headerB64, payloadB64, signatureB64] = parts;

    // Verify HMAC signature
    const expectedSignature = crypto
      .createHmac('sha256', this.secret)
      .update(`${headerB64}.${payloadB64}`)
      .digest('base64');

    if (expectedSignature !== signatureB64) {
      return null;
    }

    try {
      const payloadJson = Buffer.from(payloadB64, 'base64').toString('utf-8');
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
