<?php

namespace App\Domains\Notifications\Services;

use App\Domains\Identity\Models\User;
use App\Domains\Meetings\Models\Meeting;
use App\Domains\Notifications\Models\WorkplaceNotification;
use App\Domains\Projects\Models\Task;
use App\Domains\Workspace\Models\Room;
use App\Support\NotificationPreferences;
use App\Support\TaskMailDispatcher;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /** The language a person reads notifications in: the one they last used, else the app default. */
    public static function localeFor(?string $userId): string
    {
        $locale = $userId ? User::whereKey($userId)->value('locale') : null;

        return in_array($locale, ['ar', 'en'], true) ? $locale : config('app.locale');
    }

    /** Run $build with the recipient's language active, then restore the current one. */
    public static function inLocaleOf(?string $userId, callable $build): mixed
    {
        $previous = app()->getLocale();
        app()->setLocale(self::localeFor($userId));
        try {
            return $build();
        } finally {
            app()->setLocale($previous);
        }
    }

    /**
     * Send a general workplace notification to a user.
     *
     * Pass $title / $body as closures (fn () => __('...')) so they are written
     * in the recipient's language; plain strings are stored as given.
     */
    public static function send(
        $userId,
        string $type,
        string|\Closure $title,
        string|\Closure|null $body = null,
        ?string $actionUrl = null,
        array $data = [],
        string $icon = '🔔',
        ?string $orgId = null
    ): ?WorkplaceNotification {
        try {
            $targetUserId = $userId instanceof User ? $userId->id : $userId;

            if (! $targetUserId) {
                return null;
            }

            [$title, $body] = self::inLocaleOf($targetUserId, fn () => [value($title), value($body)]);

            // A type the person muted in the bell is not stored, but its email still goes out if wanted.
            $recipient = User::find($targetUserId);
            $store = ! $recipient || NotificationPreferences::wantsInApp($recipient, $type);

            $notification = (new WorkplaceNotification)->forceFill([
                'user_id' => $targetUserId,
                'organization_id' => $orgId,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'icon' => $icon,
                'action_url' => $actionUrl,
                'data' => $data,
                'is_read' => false,
            ]);
            if ($store) {
                $notification->save();
            }

            // Task notifications also go out by email when the user wants them.
            TaskMailDispatcher::queue($notification);

            return $notification;
        } catch (\Throwable $e) {
            Log::error('Failed to send WorkplaceNotification: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Notify user when a task is assigned to them.
     */
    public static function notifyTaskAssigned(Task $task, $assignee, ?User $actor = null): ?WorkplaceNotification
    {
        $targetUserId = $assignee instanceof User ? $assignee->id : $assignee;

        // Don't notify if user assigned task to themselves
        if ($actor && $actor->id === $targetUserId) {
            return null;
        }

        $words = fn () => [
            'actor' => $actor ? $actor->name : __('A team member'),
            'task' => $task->title ?: __('Untitled Task'),
            'project' => $task->project ? $task->project->name : __('Workspace Project'),
        ];

        return self::send(
            $targetUserId,
            'task_assigned',
            fn () => __('New Task Assigned: :task', $words()),
            fn () => __(':actor assigned you to task ":task" in project ":project".', $words()),
            self::taskUrl($task->project_id, $task->id),
            [
                'task_id' => $task->id,
                'project_id' => $task->project_id,
                'actor_id' => $actor?->id,
                'actor_name' => $actor?->name,
            ],
            '📋',
            $task->organization_id ?? ($task->project ? $task->project->organization_id : null)
        );
    }

    /**
     * Notify participants when a meeting is scheduled.
     */
    public static function notifyMeetingScheduled(Meeting $meeting, $participant, ?User $host = null): ?WorkplaceNotification
    {
        $targetUserId = $participant instanceof User ? $participant->id : $participant;

        if ($host && $host->id === $targetUserId) {
            return null;
        }

        $sched = $meeting->scheduled_at ?? $meeting->start_time;
        $words = fn () => [
            'host' => $host ? $host->name : __('Organizer'),
            'meeting' => $meeting->title ?: __('Workspace Meeting'),
            'time' => $sched ? (is_string($sched) ? $sched : $sched->format('Y-m-d H:i')) : __('Upcoming'),
        ];

        return self::send(
            $targetUserId,
            'meeting_scheduled',
            fn () => __('📅 Meeting Invitation: :meeting', $words()),
            fn () => __(':host scheduled a meeting ":meeting" at :time.', $words()),
            '/dashboard#meetings',
            [
                'meeting_id' => $meeting->id,
                'host_id' => $host?->id,
                'room_id' => $meeting->room_id,
            ],
            '📅',
            $meeting->organization_id
        );
    }

    /**
     * Notify occupant when someone knocks on their private room door.
     */
    public static function notifyDoorKnock(Room $room, $occupant, $requester): ?WorkplaceNotification
    {
        $targetUserId = $occupant instanceof User ? $occupant->id : $occupant;
        $requesterName = $requester instanceof User ? $requester->name : (is_string($requester) ? $requester : null);
        $words = fn () => [
            'requester' => $requesterName ?? __('A colleague'),
            'room' => $room->name ?: __('Private Room'),
        ];

        return self::send(
            $targetUserId,
            'door_knock',
            fn () => __('🚪 Knock on Door: :room', $words()),
            fn () => __(':requester is knocking on your room ":room" and requesting entry.', $words()),
            '/office?room='.$room->id,
            [
                'room_id' => $room->id,
                'requester_name' => $requesterName,
            ],
            '🚪',
            $room->organization_id
        );
    }

    /**
     * Notify user when a colleague waves at them in the spatial office.
     */
    public static function notifyWave($targetUser, User $sender, ?string $roomName = null, ?string $orgId = null): ?WorkplaceNotification
    {
        $targetUserId = $targetUser instanceof User ? $targetUser->id : $targetUser;

        return self::send(
            $targetUserId,
            'wave',
            fn () => __('👋 Wave from :sender', ['sender' => $sender->name]),
            fn () => $roomName
                ? __(':sender waved at you from :room.', ['sender' => $sender->name, 'room' => $roomName])
                : __(':sender waved at you in the workplace.', ['sender' => $sender->name]),
            '/office',
            [
                'sender_id' => $sender->id,
                'sender_name' => $sender->name,
            ],
            '👋',
            $orgId
        );
    }

    /**
     * Send a custom workplace notification. Title and body may be closures,
     * evaluated in the recipient's language (see send()).
     */
    public static function notifyCustom(
        $targetUser,
        string $type,
        string|\Closure $title,
        string|\Closure|null $body = null,
        array $data = [],
        $actorId = null,
        string $icon = '🔔',
        ?string $actionUrl = null,
        ?string $orgId = null
    ): ?WorkplaceNotification {
        $targetUserId = $targetUser instanceof User ? $targetUser->id : $targetUser;
        $actorUserId = $actorId instanceof User ? $actorId->id : $actorId;

        if ($actorUserId && $targetUserId === $actorUserId) {
            return null;
        }

        if (! empty($data['task_id']) && empty($actionUrl)) {
            $actionUrl = ! empty($data['project_id']) ? self::taskUrl($data['project_id'], $data['task_id']) : '/dashboard#all-tasks';
        }

        return self::send(
            $targetUserId,
            $type,
            $title,
            $body,
            $actionUrl,
            $data,
            $icon,
            $orgId
        );
    }

    /** Link that opens the project hub with the task inspector showing. */
    public static function taskUrl(?string $projectId, string $taskId): string
    {
        return $projectId ? '/projects/'.$projectId.'?task='.$taskId : '/dashboard#all-tasks';
    }
}
