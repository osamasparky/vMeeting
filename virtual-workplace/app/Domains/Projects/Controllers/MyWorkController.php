<?php

namespace App\Domains\Projects\Controllers;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectUpdate;
use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * One person's work across every project: the planner list and the
 * personal dashboard widgets.
 */
class MyWorkController extends Controller
{
    public const WIDGETS = ['summary', 'upcoming', 'by_state', 'completed_trend', 'time_week', 'by_project', 'project_health'];

    public const DEFAULT_WIDGETS = ['summary', 'upcoming', 'by_state', 'completed_trend', 'time_week', 'project_health'];

    /** Open tasks for the planner: assigned to me, created by me, or followed by me. */
    public function tasks(Request $request, Organization $organization): JsonResponse
    {
        $validated = $request->validate([
            'scope' => ['nullable', Rule::in(['assigned', 'created', 'following'])],
            'lang' => ['nullable', Rule::in(['ar', 'en'])],
        ]);
        $scope = $validated['scope'] ?? 'assigned';
        $userId = Auth::id();
        // API routes skip the web locale middleware; the page passes its language (state names).
        if (! empty($validated['lang'])) {
            app()->setLocale($validated['lang']);
        }

        $tasks = $this->scoped($organization, $userId, $scope)
            ->where('status', '!=', Task::STATUS_DONE)
            ->with(['project:id,name,code', 'state:id,name', 'labels:id,name,tone'])
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->orderByRaw("case priority when 'urgent' then 0 when 'high' then 1 when 'medium' then 2 else 3 end")
            ->limit(300)
            ->get()
            ->map(fn (Task $t) => [
                'id' => $t->id,
                'number' => $t->task_number,
                'title' => $t->title,
                'status' => $t->status,
                'state' => $t->state ? $t->state->displayName() : null,
                'priority' => $t->priority,
                'due' => $t->due_date?->toDateString(),
                'project' => $t->project ? ['id' => $t->project->id, 'name' => $t->project->name, 'code' => $t->project->code] : null,
                'labels' => $t->labels->map(fn ($l) => ['id' => $l->id, 'name' => $l->name, 'tone' => $l->tone])->values(),
            ]);

        $doneThisWeek = $this->scoped($organization, $userId, $scope)
            ->where('status', Task::STATUS_DONE)
            ->where('completed_at', '>=', now()->startOfWeek())
            ->count();

        return response()->json(['tasks' => $tasks, 'done_this_week' => $doneThisWeek, 'today' => now()->toDateString()]);
    }

    public function dashboard(Request $request, Organization $organization): JsonResponse
    {
        $membership = $this->membership($request);
        $widgets = $this->layout($membership);

        $data = [];
        foreach ($widgets as $widget) {
            $data[$widget] = $this->widgetData($widget, $organization, Auth::id());
        }

        return response()->json(['widgets' => $widgets, 'available' => self::WIDGETS, 'data' => $data]);
    }

    public function saveLayout(Request $request, Organization $organization): JsonResponse
    {
        $validated = $request->validate([
            'widgets' => ['present', 'array', 'max:'.count(self::WIDGETS)],
            'widgets.*' => ['distinct', Rule::in(self::WIDGETS)],
        ]);

        $membership = $this->membership($request);
        $membership->forceFill(['dashboard_widgets' => array_values($validated['widgets'])])->save();

        return $this->dashboard($request, $organization);
    }

    private function membership(Request $request): OrganizationMember
    {
        $membership = $request->get('current_membership');
        abort_unless($membership instanceof OrganizationMember, 403);

        return $membership;
    }

    /** @return list<string> */
    private function layout(OrganizationMember $membership): array
    {
        $stored = $membership->dashboard_widgets;

        return is_array($stored) ? array_values(array_intersect($stored, self::WIDGETS)) : self::DEFAULT_WIDGETS;
    }

    private function scoped(Organization $organization, string $userId, string $scope): Builder
    {
        $query = Task::where('organization_id', $organization->id);

        return match ($scope) {
            'created' => $query->where('reporter_id', $userId),
            'following' => $query->whereHas('watchers', fn ($w) => $w->where('users.id', $userId)),
            default => $query->assignedTo($userId),
        };
    }

    private function widgetData(string $widget, Organization $organization, string $userId): array
    {
        $mine = fn () => $this->scoped($organization, $userId, 'assigned');
        $open = fn () => $mine()->where('status', '!=', Task::STATUS_DONE);
        $today = now()->startOfDay();

        return match ($widget) {
            'summary' => [
                'open' => $open()->count(),
                'overdue' => $open()->whereDate('due_date', '<', $today)->count(),
                'due_week' => $open()->whereBetween('due_date', [$today->toDateString(), $today->copy()->endOfWeek()->toDateString()])->count(),
                'done_week' => $mine()->where('status', Task::STATUS_DONE)->where('completed_at', '>=', now()->startOfWeek())->count(),
            ],

            'upcoming' => $open()->whereNotNull('due_date')->with('project:id,name,code')
                ->orderBy('due_date')->limit(6)->get()
                ->map(fn (Task $t) => [
                    'id' => $t->id, 'title' => $t->title, 'due' => $t->due_date->toDateString(), 'priority' => $t->priority,
                    'project' => $t->project ? ['id' => $t->project->id, 'code' => $t->project->code] : null,
                ])->all(),

            'by_state' => collect(Task::STATUSES)
                ->reject(fn ($s) => $s === Task::STATUS_DONE)
                ->map(fn ($s) => ['key' => $s, 'value' => 0])
                ->keyBy('key')
                ->merge($open()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status')
                    ->map(fn ($c, $s) => ['key' => $s, 'value' => (int) $c]))
                ->values()->all(),

            'completed_trend' => $this->dailySeries(
                14,
                $mine()->where('status', Task::STATUS_DONE)->where('completed_at', '>=', now()->subDays(13)->startOfDay())
                    ->pluck('completed_at')->map(fn ($d) => $d->toDateString())->countBy()->all(),
            ),

            'time_week' => (function () use ($organization, $userId) {
                $start = now()->startOfWeek();
                $byDay = TimeEntry::where('organization_id', $organization->id)->where('user_id', $userId)
                    ->where('started_at', '>=', $start)->get(['started_at', 'duration_seconds'])
                    ->groupBy(fn ($e) => $e->started_at->toDateString())
                    ->map(fn ($g) => round($g->sum('duration_seconds') / 3600, 1))->all();
                $days = collect(range(0, 6))->map(fn ($i) => $start->copy()->addDays($i)->toDateString())
                    ->map(fn ($d) => ['date' => $d, 'value' => $byDay[$d] ?? 0])->all();

                return ['days' => $days, 'total' => round(array_sum(array_column($days, 'value')), 1)];
            })(),

            'by_project' => $open()->whereNotNull('project_id')->selectRaw('project_id, count(*) as c')->groupBy('project_id')
                ->orderByDesc('c')->limit(6)->get()
                ->map(fn ($r) => ['key' => $r->project_id, 'label' => Project::find($r->project_id)?->name ?? '—', 'value' => (int) $r->c])
                ->all(),

            'project_health' => Project::where('organization_id', $organization->id)
                ->where(fn ($q) => $q->where('manager_id', $userId)->orWhereHas('members', fn ($m) => $m->where('user_id', $userId)))
                ->orderBy('name')->limit(8)->get(['id', 'name', 'code'])
                ->map(function (Project $p) {
                    $update = ProjectUpdate::where('project_id', $p->id)->latest()->first(['health', 'created_at']);

                    return ['id' => $p->id, 'name' => $p->name, 'code' => $p->code, 'health' => $update?->health, 'updated' => $update?->created_at?->toDateString()];
                })->all(),
        };
    }

    /** @param  array<string, int>  $counts */
    private function dailySeries(int $days, array $counts): array
    {
        return collect(range($days - 1, 0))
            ->map(fn ($ago) => now()->subDays($ago)->toDateString())
            ->map(fn ($d) => ['date' => $d, 'value' => $counts[$d] ?? 0])
            ->all();
    }
}
