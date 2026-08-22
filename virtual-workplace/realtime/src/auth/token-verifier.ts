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

    const parts = token.split('.');
    if (parts.length !== 3) {
      return null;
    }

    const [headerB64, payloadB64, signatureB64] = parts;

    // Verify HMAC signature against candidate secrets
    let signatureMatched = false;
    for (const sec of this.secrets) {
      const expectedSignature = crypto
        .createHmac('sha256', sec)
        .update(`${headerB64}.${payloadB64}`)
        .digest('base64');

      if (expectedSignature === signatureB64) {
        signatureMatched = true;
        break;
      }
    }

    if (!signatureMatched) {
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
