<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use function Illuminate\Support\defer;

/**
 * Tells the realtime service which users have new notifications, so their
 * open bells refresh at once instead of waiting for the next poll.
 *
 * Users are collected during the request (the instance is scoped, see
 * AppServiceProvider) and sent in one call after the response. Only ids
 * travel: the browser fetches the notifications itself through the
 * authenticated web API. If the service is down the bell still polls, so
 * failures are logged and otherwise ignored.
 */
class RealtimeNotifier
{
    /** @var array<string, true> */
    private array $pending = [];

    public static function queue(string $userId): void
    {
        if (! config('services.realtime.secret')) {
            return;
        }

        $notifier = app(self::class);
        $notifier->pending[$userId] = true;
        // Named, so it is registered once however many notices the request sends.
        defer(fn () => $notifier->flush(), 'realtime-notifications');
    }

    public function flush(): void
    {
        $userIds = array_keys($this->pending);
        $this->pending = [];
        if ($userIds === []) {
            return;
        }

        try {
            Http::timeout(2)
                ->withHeaders(['X-Realtime-Secret' => (string) config('services.realtime.secret')])
                ->post(rtrim((string) config('services.realtime.http_url'), '/').'/internal/notifications', ['user_ids' => $userIds]);
        } catch (\Throwable $e) {
            Log::info('Realtime notification ping skipped: '.$e->getMessage());
        }
    }
}
