<?php

namespace App\Domains\Meetings\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Meetings\Models\Meeting;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Tenancy\Models\Organization;
use App\Mail\MeetingInvitationMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Creates a scheduled meeting, adds the host and every relevant recipient
 * (a project's owner/manager/task-assignees, or an explicit attendee list)
 * as participants, sends in-app notifications, and emails invitations.
 * Extracted from DashboardController::storeScheduledMeeting(). See
 * Architecture Audit §8/§15.
 */
class ScheduleMeetingAction
{
    /**
     * @return array{meeting: Meeting, sent_count: int, tab: string}
     */
    public function execute(Organization $organization, User $creator, array $validated): array
    {
        $project = null;
        if ($validated['scope'] === 'project' && ! empty($validated['project_id'])) {
            $project = $organization->projects()->findOrFail($validated['project_id']);
        }

        $room = null;
        if (! empty($validated['room_id'])) {
            $room = $organization->rooms()->find($validated['room_id']);
        }
        if (! $room) {
            $room = $organization->rooms()->first();
        }

        $meeting = Meeting::create([
            'organization_id' => $organization->id,
            'room_id' => $room?->id,
            'project_id' => $project?->id,
            'created_by' => $creator->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => 'scheduled',
            'scope' => $validated['scope'],
            'status' => 'scheduled',
            'scheduled_at' => Carbon::parse($validated['scheduled_at']),
            'duration_minutes' => (int) ($validated['duration_minutes'] ?? 30),
            'livekit_room_name' => "meeting_{$organization->id}_".uniqid(),
            'settings' => [
                'allow_screen_share' => true,
                'allow_chat' => true,
            ],
        ]);

        // Host participant
        $meeting->participants()->create([
            'user_id' => $creator->id,
            'role' => 'host',
            'joined_at' => now(),
        ]);

        // Collect recipient users
        $recipients = collect();

        if ($validated['scope'] === 'project' && $project) {
            if ($project->owner_id && $project->owner) {
                $recipients->push($project->owner);
            }
            if ($project->manager_id && $project->manager) {
                $recipients->push($project->manager);
            }
            $taskAssigneeIds = $project->tasks()->whereNotNull('assignee_id')->pluck('assignee_id')->unique();
            $taskUsers = User::whereIn('id', $taskAssigneeIds)->get();
            $recipients = $recipients->concat($taskUsers)->unique('id');
        } elseif (! empty($validated['attendee_ids'])) {
            $recipients = User::whereIn('id', $validated['attendee_ids'])->get();
        }

        foreach ($recipients as $recipient) {
            if ($recipient->id !== $creator->id) {
                $meeting->participants()->firstOrCreate([
                    'user_id' => $recipient->id,
                ], [
                    'role' => 'participant',
                    'joined_at' => now(),
                ]);

                // Send live in-app & database notification
                NotificationService::notifyMeetingScheduled($meeting, $recipient, $creator);
            }
        }

        // Send Email Invitations
        $this->applyOrganizationSmtp($organization);
        $joinUrl = route('office');
        $sentCount = 0;

        foreach ($recipients as $recipient) {
            if (! empty($recipient->email)) {
                try {
                    Mail::to($recipient->email)->send(
                        new MeetingInvitationMail($meeting, $recipient, $joinUrl)
                    );
                    $sentCount++;
                } catch (\Throwable $e) {
                    Log::warning("Could not send meeting invitation email to {$recipient->email}: ".$e->getMessage());
                }
            }
        }

        return [
            'meeting' => $meeting,
            'sent_count' => $sentCount,
            'tab' => $validated['scope'] === 'project' ? 'projects' : 'meetings',
        ];
    }

    /**
     * Apply organization-level SMTP settings to current runtime configuration.
     */
    private function applyOrganizationSmtp(Organization $organization): void
    {
        $smtp = $organization->settings?->smtp_settings ?? [];
        if (! empty($smtp['host'])) {
            Config::set('mail.mailers.smtp.host', $smtp['host']);
            Config::set('mail.mailers.smtp.port', $smtp['port'] ?? 587);
            Config::set('mail.mailers.smtp.encryption', $smtp['encryption'] ?? 'tls');
            Config::set('mail.mailers.smtp.username', $smtp['username'] ?? null);
            Config::set('mail.mailers.smtp.password', $smtp['password'] ?? null);
            Config::set('mail.from.address', $smtp['from_address'] ?? env('MAIL_FROM_ADDRESS'));
            Config::set('mail.from.name', $smtp['from_name'] ?? $organization->name);
        }
    }
}
