<?php

namespace App\Domains\Notifications\Services;

use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Models\WorkplaceNotification;
use App\Domains\Projects\Models\Task;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Workspace\Models\Meeting;
use App\Domains\Workspace\Models\Room;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send a general workplace notification to a user.
     */
    public static function send(
        $userId,
        string $type,
        string $title,
        ?string $body = null,
        ?string $actionUrl = null,
        array $data = [],
        string $icon = '🔔',
        ?string $orgId = null
    ): ?WorkplaceNotification {
        try {
            $targetUserId = $userId instanceof User ? $userId->id : $userId;

            if (!$targetUserId) {
                return null;
            }

            return WorkplaceNotification::create([
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
        } catch (\Throwable $e) {
            Log::error('Failed to send WorkplaceNotification: ' . $e->getMessage());
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

        $actorName = $actor ? $actor->name : __('A team member');
        $taskTitle = $task->title ?: __('Untitled Task');
        $projectName = $task->project ? $task->project->name : __('Workspace Project');

        $title = __('New Task Assigned: :task', ['task' => $taskTitle]);
        $body = __(':actor assigned you to task ":task" in project ":project".', [
            'actor' => $actorName,
            'task' => $taskTitle,
            'project' => $projectName,
        ]);

        $actionUrl = '/projects/hub?project=' . ($task->project_id ?? '') . '&task=' . $task->id;

        return self::send(
            $targetUserId,
            'task_assigned',
            $title,
            $body,
            $actionUrl,
            [
                'task_id' => $task->id,
                'project_id' => $task->project_id,
                'actor_id' => $actor ? $actor->id : null,
                'actor_name' => $actorName,
            ],
            '📋',
            $task->organization_id ?? ($task->project ? $task->project->organization_id : null)
        );
    }

    /**
     * Notify task assignee when task status changes or is updated.
     */
    public static function notifyTaskStatusChanged(Task $task, string $oldStatus, string $newStatus, ?User $actor = null): ?WorkplaceNotification
    {
        if (!$task->assignee_id) {
            return null;
        }

        if ($actor && $actor->id === $task->assignee_id) {
            return null;
        }

        $actorName = $actor ? $actor->name : __('A team member');
        $taskTitle = $task->title ?: __('Untitled Task');

        $title = __('Task Updated: :task', ['task' => $taskTitle]);
        $body = __(':actor changed status of task ":task" from :old to :new.', [
            'actor' => $actorName,
            'task' => $taskTitle,
            'old' => ucfirst(str_replace('_', ' ', $oldStatus)),
            'new' => ucfirst(str_replace('_', ' ', $newStatus)),
        ]);

        $actionUrl = '/projects/hub?project=' . ($task->project_id ?? '') . '&task=' . $task->id;

        return self::send(
            $task->assignee_id,
            'task_updated',
            $title,
            $body,
            $actionUrl,
            [
                'task_id' => $task->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ],
            '⚡',
            $task->organization_id
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

        $hostName = $host ? $host->name : __('Organizer');
        $meetingTitle = $meeting->title ?: __('Workspace Meeting');
        $timeFormatted = $meeting->start_time ? (is_string($meeting->start_time) ? $meeting->start_time : $meeting->start_time->format('Y-m-d H:i')) : __('Upcoming');

        $title = __('📅 Meeting Invitation: :meeting', ['meeting' => $meetingTitle]);
        $body = __(':host scheduled a meeting ":meeting" at :time.', [
            'host' => $hostName,
            'meeting' => $meetingTitle,
            'time' => $timeFormatted,
        ]);

        $actionUrl = '/dashboard?tab=calendar&meeting=' . $meeting->id;

        return self::send(
            $targetUserId,
            'meeting_scheduled',
            $title,
            $body,
            $actionUrl,
            [
                'meeting_id' => $meeting->id,
                'host_id' => $host ? $host->id : null,
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
        $requesterName = $requester instanceof User ? $requester->name : (is_string($requester) ? $requester : __('A colleague'));
        $roomName = $room->name ?: __('Private Room');

        $title = __('🚪 Knock on Door: :room', ['room' => $roomName]);
        $body = __(':requester is knocking on your room ":room" and requesting entry.', [
            'requester' => $requesterName,
            'room' => $roomName,
        ]);

        $actionUrl = '/office?room=' . $room->id;

        return self::send(
            $targetUserId,
            'door_knock',
            $title,
            $body,
            $actionUrl,
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
    public static function notifyWave($targetUser, User $sender, ?string $roomName = null): ?WorkplaceNotification
    {
        $targetUserId = $targetUser instanceof User ? $targetUser->id : $targetUser;

        $title = __('👋 Wave from :sender', ['sender' => $sender->name]);
        $body = $roomName
            ? __(':sender waved at you from :room.', ['sender' => $sender->name, 'room' => $roomName])
            : __(':sender waved at you in the workplace.', ['sender' => $sender->name]);

        $actionUrl = '/office';

        return self::send(
            $targetUserId,
            'wave',
            $title,
            $body,
            $actionUrl,
            [
                'sender_id' => $sender->id,
                'sender_name' => $sender->name,
            ],
            '👋'
        );
    }
}
