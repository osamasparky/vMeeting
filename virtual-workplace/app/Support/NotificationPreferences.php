<?php

namespace App\Support;

use App\Domains\Identity\Models\User;

/**
 * Which events a user wants by email, and which show in their bell. Both are
 * stored on users.notification_preferences: email switches as top-level keys,
 * bell switches under "inapp".
 */
class NotificationPreferences
{
    /** Preference key => default. */
    public const DEFAULTS = [
        'assigned' => true,
        'comments' => true,
        'mentions' => true,
        'status' => true,
        'approvals' => true,
        'due' => true,
        'meetings' => true,
    ];

    /** Notification type (WorkplaceNotification::type) => preference key. */
    public const TYPE_TO_KEY = [
        'task_assigned' => 'assigned',
        'task_comment' => 'comments',
        'task_mention' => 'mentions',
        'task_updated' => 'status',
        'task_status' => 'status',
        'task_approval_request' => 'approvals',
        'task_approved' => 'approvals',
        'task_rejected' => 'approvals',
        'task_due_soon' => 'due',
        'task_overdue' => 'due',
        'task_intake' => 'assigned',
        'project_update' => 'status',
        'meeting_scheduled' => 'meetings',
    ];

    /** @return array<string, bool> */
    public static function for(User $user): array
    {
        $stored = is_array($user->notification_preferences) ? $user->notification_preferences : [];

        return array_map('boolval', array_merge(self::DEFAULTS, array_intersect_key(array_filter($stored, 'is_scalar'), self::DEFAULTS)));
    }

    public static function wantsEmail(User $user, string $type): bool
    {
        $key = self::TYPE_TO_KEY[$type] ?? null;

        return $key !== null && (self::for($user)[$key] ?? false);
    }

    /** Bell switches: every email category plus office pings, which never email. */
    public const INAPP_DEFAULTS = self::DEFAULTS + ['office' => true];

    /** Office interactions only ever show in the bell. */
    public const OFFICE_TYPES = ['wave', 'door_knock', 'user_ring'];

    /** @return array<string, bool> */
    public static function inApp(User $user): array
    {
        $stored = is_array($user->notification_preferences['inapp'] ?? null) ? $user->notification_preferences['inapp'] : [];

        return array_map('boolval', array_merge(self::INAPP_DEFAULTS, array_intersect_key($stored, self::INAPP_DEFAULTS)));
    }

    /** Whether the notice shows in the bell. Types without a category always do. */
    public static function wantsInApp(User $user, string $type): bool
    {
        $key = in_array($type, self::OFFICE_TYPES, true) ? 'office' : (self::TYPE_TO_KEY[$type] ?? null);

        return $key === null || (self::inApp($user)[$key] ?? true);
    }
}
