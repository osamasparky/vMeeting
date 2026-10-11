<?php

namespace App\Domains\People\Services;

use App\Domains\Guests\Models\GuestInvitation;
use App\Domains\Identity\Models\User;
use App\Domains\Meetings\Models\Meeting;
use App\Domains\People\Models\AttendanceSession;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Projects\Models\Timesheet;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Workspace\Models\Room;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * What the dashboard shows about the company right now, from real data:
 * who is in the office, and the figures an owner checks every day.
 *
 * "Online" means the virtual office sent a heartbeat in the last ONLINE_WINDOW_SECONDS
 * (it sends one every 45 seconds while someone is in the office).
 */
class WorkspacePulse
{
    public const ONLINE_WINDOW_SECONDS = 120;

    public const FIGURES_TTL_SECONDS = 60;

    /**
     * People in the office now, newest arrival first.
     *
     * @return Collection<int, array{id: string, name: string, avatar_url: ?string, room: ?string, since: string}>
     */
    public function onlineNow(Organization $organization): Collection
    {
        $sessions = AttendanceSession::withoutGlobalScopes()
            ->where('organization_id', $organization->id)
            ->where('status', 'active')
            ->whereNull('ended_at')
            ->where('last_heartbeat_at', '>=', now()->subSeconds(self::ONLINE_WINDOW_SECONDS))
            ->orderByDesc('started_at')
            ->get(['user_id', 'room_id', 'started_at'])
            ->unique('user_id');
        if ($sessions->isEmpty()) {
            return collect();
        }

        $users = User::whereIn('id', $sessions->pluck('user_id'))->get(['id', 'name', 'avatar_url'])->keyBy('id');
        $rooms = Room::withoutGlobalScopes()->whereIn('id', $sessions->pluck('room_id')->filter())->pluck('name', 'id');

        return $sessions->filter(fn ($s) => $users->has($s->user_id))->map(fn ($s) => [
            'id' => $s->user_id,
            'name' => $users[$s->user_id]->name,
            'avatar_url' => $users[$s->user_id]->avatar_url,
            'room_id' => $s->room_id,
            'room' => $s->room_id ? ($rooms[$s->room_id] ?? null) : null,
            'since' => $s->started_at?->toIso8601String(),
        ])->values();
    }

    /** Figures for the company owner (or anyone who manages the organization). One aggregate query per table. */
    public function ownerKpis(Organization $organization, Collection $online, ?int $roomCount = null): array
    {
        // Company figures change slowly: kept for a minute per company. Who is online is always live.
        $figures = Cache::remember("dashboard:owner-kpis:{$organization->id}", self::FIGURES_TTL_SECONDS,
            fn () => $this->companyFigures($organization, $roomCount));

        return [
            'online' => $online->count(),
            'rooms_in_use' => $online->pluck('room_id')->filter()->unique()->count(),
        ] + $figures;
    }

    private function companyFigures(Organization $organization, ?int $roomCount): array
    {
        $orgId = $organization->id;
        $today = now()->startOfDay();
        $weekStart = now()->startOfWeek();

        $members = OrganizationMember::where('organization_id', $orgId)
            ->whereHas('user', fn ($q) => $q->where('is_super_admin', false))
            ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active, SUM(CASE WHEN status = 'invited' THEN 1 ELSE 0 END) AS invited")
            ->first();

        $todaySessions = AttendanceSession::withoutGlobalScopes()->where('organization_id', $orgId)
            ->where(fn ($q) => $q->where('started_at', '>=', $today)->orWhereNull('ended_at')->orWhere('ended_at', '>=', $today))
            ->get(['user_id', 'started_at', 'ended_at', 'last_heartbeat_at']);
        $officeSecondsToday = $todaySessions->sum(function ($s) use ($today) {
            $start = $s->started_at && $s->started_at->gt($today) ? $s->started_at : $today;
            $end = $s->ended_at ?? $s->last_heartbeat_at ?? now();

            return max(0, $end->getTimestamp() - $start->getTimestamp());
        });

        $time = TimeEntry::withoutGlobalScopes()->where('organization_id', $orgId)->where('started_at', '>=', $weekStart)
            ->selectRaw('COALESCE(SUM(duration_seconds), 0) AS total, COALESCE(SUM(CASE WHEN is_billable = 1 THEN duration_seconds ELSE 0 END), 0) AS billable')
            ->first();

        $tasks = Task::withoutGlobalScopes()->where('organization_id', $orgId)->whereNull('archived_at')
            ->selectRaw("COUNT(*) AS total,
                SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) AS done,
                SUM(CASE WHEN status <> 'done' THEN 1 ELSE 0 END) AS open,
                SUM(CASE WHEN status <> 'done' AND due_date IS NOT NULL AND due_date < ? THEN 1 ELSE 0 END) AS overdue,
                SUM(CASE WHEN status = 'done' AND completed_at >= ? THEN 1 ELSE 0 END) AS done_week", [$today, $weekStart])
            ->first();

        $projects = Project::withoutGlobalScopes()->where('organization_id', $orgId)->whereNull('archived_at')->where('status', 'active')
            ->selectRaw('COUNT(*) AS active, SUM(CASE WHEN due_date IS NOT NULL AND due_date < ? AND completed_at IS NULL THEN 1 ELSE 0 END) AS late', [$today])
            ->first();

        $meetings = Meeting::withoutGlobalScopes()->where('organization_id', $orgId)->whereDate('scheduled_at', $today)
            ->selectRaw("COUNT(*) AS today, SUM(CASE WHEN status = 'live' THEN 1 ELSE 0 END) AS live")
            ->first();

        $seatLimit = (int) ($organization->plan?->seat_limit ?? 0);
        $activeMembers = (int) ($members->active ?? 0);
        $allTasks = (int) $tasks->total;

        return [
            'members' => $activeMembers,
            'invited' => (int) ($members->invited ?? 0),
            'present_today' => $todaySessions->pluck('user_id')->unique()->count(),
            'office_hours_today' => round($officeSecondsToday / 3600, 1),
            'hours_week' => round(((int) $time->total) / 3600, 1),
            'billable_hours_week' => round(((int) $time->billable) / 3600, 1),
            'timesheets_pending' => Timesheet::withoutGlobalScopes()->where('organization_id', $orgId)->where('status', 'submitted')->count(),
            'overdue_tasks' => (int) $tasks->overdue,
            'open_tasks' => (int) $tasks->open,
            'done_week' => (int) $tasks->done_week,
            'completion_rate' => $allTasks > 0 ? (int) round(((int) $tasks->done) / $allTasks * 100) : null,
            'active_projects' => (int) $projects->active,
            'late_projects' => (int) $projects->late,
            'meetings_today' => (int) $meetings->today,
            'meetings_live' => (int) $meetings->live,
            'rooms' => $roomCount ?? Room::withoutGlobalScopes()->where('organization_id', $orgId)->count(),
            'seats_used' => $activeMembers,
            'seat_limit' => $seatLimit > 0 ? $seatLimit : null,
            'guest_links' => GuestInvitation::withoutGlobalScopes()->where('organization_id', $orgId)
                ->where('status', 'pending')->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))->count(),
        ];
    }

    /** Figures about one person's own work (everyone sees these). */
    public function memberKpis(Organization $organization, User $user, Collection $online): array
    {
        $orgId = $organization->id;
        $today = now()->startOfDay();
        $mine = Task::withoutGlobalScopes()->where('organization_id', $orgId)->whereNull('archived_at')
            ->where('status', '!=', 'done')
            ->where(fn ($q) => $q->where('assignee_id', $user->id)->orWhereHas('assignees', fn ($a) => $a->where('users.id', $user->id)));

        return [
            'online' => $online->count(),
            'members' => OrganizationMember::where('organization_id', $orgId)->where('status', 'active')
                ->whereHas('user', fn ($q) => $q->where('is_super_admin', false))->count(),
            'meetings_today' => Meeting::withoutGlobalScopes()->where('organization_id', $orgId)->whereDate('scheduled_at', $today)->count(),
            'my_open_tasks' => (clone $mine)->count(),
            'my_overdue_tasks' => (clone $mine)->whereNotNull('due_date')->where('due_date', '<', $today)->count(),
            'my_hours_week' => round(TimeEntry::withoutGlobalScopes()->where('organization_id', $orgId)
                ->where('user_id', $user->id)->where('started_at', '>=', now()->startOfWeek())->sum('duration_seconds') / 3600, 1),
        ];
    }
}
