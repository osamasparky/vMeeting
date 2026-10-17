<?php

namespace App\Domains\Notifications\Support;

use App\Support\NotificationPreferences;
use Illuminate\Database\Eloquent\Builder;

/**
 * The notification categories the bell filters by (task, meeting, office),
 * in one place so the bell's endpoint and the assistant agree on them.
 */
class NotificationCategories
{
    public const FILTERS = ['all', 'unread', 'task', 'meeting', 'office'];

    public static function apply(Builder $query, string $filter): Builder
    {
        return match ($filter) {
            'unread' => $query->unread(),
            'task' => $query->where('type', 'like', 'task%'),
            'meeting' => $query->where('type', 'like', 'meeting%'),
            'office' => $query->where(fn ($w) => $w->whereIn('type', NotificationPreferences::OFFICE_TYPES)->orWhere('type', 'like', 'room%')),
            default => $query,
        };
    }

    /** Which category a notification type belongs to. */
    public static function of(?string $type): string
    {
        $type = (string) $type;
        if (str_starts_with($type, 'task')) {
            return 'task';
        }
        if (str_starts_with($type, 'meeting')) {
            return 'meeting';
        }
        if (in_array($type, NotificationPreferences::OFFICE_TYPES, true) || str_starts_with($type, 'room')) {
            return 'office';
        }

        return 'other';
    }
}
