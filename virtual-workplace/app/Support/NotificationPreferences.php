<?php

namespace App\Support;

use App\Domains\Identity\Models\User;

/**
 * Which events (tasks, meetings) a user wants by email. In-app notifications are always
 * delivered; these switches only control email.
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

        return array_map('boolval', array_merge(self::DEFAULTS, array_intersect_key($stored, self::DEFAULTS)));
    }

    public static function wantsEmail(User $user, string $type): bool
    {
        $key = self::TYPE_TO_KEY[$type] ?? null;

        return $key !== null && (self::for($user)[$key] ?? false);
    }
}
