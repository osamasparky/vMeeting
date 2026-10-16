<?php

namespace App\Domains\Workspace\Games;

use RuntimeException;

/**
 * A sealed quiz for the realtime server. The person starting the game carries
 * it from here to the quiz machine, but it is encrypted with the secret the
 * app shares with the realtime server (AES-256-GCM, key = SHA-256 of
 * REALTIME_SECRET), so nobody can read the answers in their browser or
 * tamper with the questions. It names its organization and expires.
 */
class QuizTicket
{
    public const TTL_SECONDS = 300;

    /** @param array{title: string, questions: list<array{q: string, c: list<string>, a: int, s: int}>} $quiz */
    public static function seal(array $quiz, string $organizationId): string
    {
        $secret = (string) config('services.realtime.secret');
        if ($secret === '') {
            throw new RuntimeException('REALTIME_SECRET is not set; quizzes cannot be sealed.');
        }
        $key = hash('sha256', $secret, true);
        $iv = random_bytes(12);
        $tag = '';
        $plain = json_encode(['org' => $organizationId, 'exp' => time() + self::TTL_SECONDS] + $quiz, JSON_UNESCAPED_UNICODE);
        $cipher = openssl_encrypt($plain, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

        return base64_encode($iv.$tag.$cipher);
    }

    /** For tests: the inverse of seal(), as the realtime server does it. */
    public static function open(string $ticket): ?array
    {
        $raw = base64_decode($ticket, true);
        if ($raw === false || strlen($raw) < 29) {
            return null;
        }
        $key = hash('sha256', (string) config('services.realtime.secret'), true);
        $plain = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', $key, OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));

        return $plain === false ? null : json_decode($plain, true);
    }
}
