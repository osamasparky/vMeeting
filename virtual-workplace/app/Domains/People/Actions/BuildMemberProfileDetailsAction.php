<?php

namespace App\Domains\People\Actions;

use App\Domains\People\Models\Department;
use App\Domains\People\Models\Team;
use App\Domains\People\Models\UserProfile;
use App\Domains\Projects\Models\ActiveTimer;
use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Tenancy\Models\OrganizationMember;

/**
 * Builds the full "team member profile" panel payload: bio/contact detail,
 * department/team, assigned tasks, recent time entries, and summary stats.
 * Extracted from OrganizationSettingsController::getMemberProfileDetails().
 * See Architecture Audit §8/§15.
 */
class BuildMemberProfileDetailsAction
{
    public function execute(OrganizationMember $member): array
    {
        $targetUser = $member->user;
        $profile = UserProfile::where('user_id', $targetUser->id)
            ->where('organization_id', $member->organization_id)
            ->first();

        $dept = $profile && $profile->department_id ? Department::find($profile->department_id) : null;
        $team = $profile && $profile->team_id ? Team::find($profile->team_id) : null;

        // Fetch tasks assigned to this member in this organization
        $tasks = Task::where('organization_id', $member->organization_id)
            ->where('assignee_id', $targetUser->id)
            ->with(['project:id,name,code', 'checklistItems'])
            ->orderBy('due_date')
            ->latest()
            ->get()
            ->map(function ($t) {
                $totalChecklist = $t->checklistItems->count();
                $doneChecklist = $t->checklistItems->where('is_completed', true)->count();

                return [
                    'id' => $t->id,
                    'task_number' => $t->task_number,
                    'title' => $t->title,
                    'status' => $t->status,
                    'priority' => $t->priority,
                    'project' => $t->project ? [
                        'id' => $t->project->id,
                        'name' => $t->project->name,
                        'code' => $t->project->code ?? 'PRJ',
                    ] : null,
                    'due_date' => $t->due_date ? $t->due_date->format('M d, Y') : null,
                    'is_overdue' => $t->due_date && $t->due_date->isPast() && $t->status !== 'done',
                    'estimated_hours' => (float) ($t->estimated_hours ?? 0),
                    'actual_hours' => (float) ($t->actual_hours ?? 0),
                    'checklist_count' => $totalChecklist,
                    'checklist_done' => $doneChecklist,
                ];
            });

        // Fetch time entries logged by this member in this organization
        $timeEntries = TimeEntry::where('organization_id', $member->organization_id)
            ->where('user_id', $targetUser->id)
            ->with(['project:id,name,code', 'task:id,task_number,title'])
            ->latest('started_at')
            ->take(20)
            ->get()
            ->map(function ($te) {
                return [
                    'id' => $te->id,
                    'date' => $te->started_at ? $te->started_at->format('M d, Y') : '—',
                    'duration_formatted' => $te->formattedDuration(),
                    'description' => $te->description ?? 'General Work Session',
                    'project_name' => $te->project?->name ?? 'General',
                    'task_title' => $te->task ? ('#'.$te->task->task_number.' '.$te->task->title) : '—',
                    'is_billable' => (bool) $te->is_billable,
                ];
            });

        $totalDurationSeconds = TimeEntry::where('organization_id', $member->organization_id)
            ->where('user_id', $targetUser->id)
            ->sum('duration_seconds');
        $totalHoursLogged = round($totalDurationSeconds / 3600, 1);

        $activeTimer = ActiveTimer::where('user_id', $targetUser->id)
            ->with(['project:id,name,code', 'task:id,task_number,title'])
            ->first();

        return [
            'member' => [
                'id' => $member->id,
                'user_id' => $targetUser->id,
                'name' => $targetUser->name,
                'nickname' => $targetUser->nickname,
                'email' => $targetUser->email,
                'avatar_url' => $targetUser->avatar_url,
                'role_name' => $member->role?->name ?? 'Member',
                'role_slug' => $member->role?->slug ?? 'employee',
                'role_id' => $member->role_id,
                'status' => $member->status,
                'joined_at' => $member->joined_at ? $member->joined_at->format('M d, Y') : ($member->created_at ? $member->created_at->format('M d, Y') : '—'),
                'allowed_office_ids' => $member->offices->pluck('id')->toArray(),
                'allowed_room_ids' => $member->rooms->pluck('id')->toArray(),
            ],
            'profile' => [
                'job_title' => $profile?->job_title ?? $member->role?->name ?? 'Team Member',
                'department_id' => $profile?->department_id,
                'team_id' => $profile?->team_id,
                'department_name' => $dept?->name,
                'team_name' => $team?->name,
                'work_mode' => $profile?->work_mode ?? 'remote',
                'phone' => $profile?->phone,
                'date_of_birth' => $profile?->date_of_birth ? $profile->date_of_birth->format('M d, Y') : null,
                'bio' => $profile?->bio,
                'skills' => $profile?->skills ? array_filter(array_map('trim', explode(',', $profile->skills))) : [],
                'hobbies' => $profile?->hobbies ? array_filter(array_map('trim', explode(',', $profile->hobbies))) : [],
                'notes' => $profile?->notes,
                'social_links' => (array) ($profile?->social_links ?? []),
            ],
            'stats' => [
                'total_tasks' => $tasks->count(),
                'completed_tasks' => $tasks->where('status', 'done')->count(),
                'in_progress_tasks' => $tasks->where('status', 'in_progress')->count(),
                'pending_tasks' => $tasks->whereNotIn('status', ['done', 'in_progress'])->count(),
                'total_hours_logged' => $totalHoursLogged,
                'active_timer' => $activeTimer ? [
                    'id' => $activeTimer->id,
                    'started_at' => $activeTimer->started_at->toIso8601String(),
                    'project_name' => $activeTimer->project?->name,
                    'task_title' => $activeTimer->task ? ('#'.$activeTimer->task->task_number.' '.$activeTimer->task->title) : null,
                ] : null,
            ],
            'tasks' => $tasks,
            'time_entries' => $timeEntries,
        ];
    }
}
