<?php

namespace App\Domains\Projects\Controllers;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Projects\Models\Timesheet;
use App\Domains\Tenancy\Models\Organization;
use App\Support\SpreadsheetExport;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

/**
 * CSV / Excel downloads. Each export applies the same visibility rules as the
 * matching list endpoint, so nobody can download more than they could read.
 */
class ExportController extends Controller
{
    public function tasks(Request $request, Organization $organization, Project $project): Response
    {
        abort_unless($project->organization_id === $organization->id, 404);
        $format = $this->format($request);

        $tasks = $project->tasks()
            ->with(['assignee:id,name', 'assignees:id,name', 'labels:id,name', 'milestone:id,name', 'sprint:id,name', 'timeEntries:id,task_id,duration_seconds'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->query('priority')))
            ->when($request->filled('label_id'), fn ($q) => $q->whereHas('labels', fn ($l) => $l->where('project_labels.id', $request->integer('label_id'))))
            ->when($request->filled('assignee_id'), fn ($q) => $q->assignedTo((string) $request->query('assignee_id')))
            ->get();

        $headers = [__('pm.col_number'), __('pm.col_title'), __('Status'), __('Priority'), __('pm.assignees'), __('pm.labels'),
            __('Milestone'), __('pm.sprints'), __('pm.col_start'), __('Due Date'), __('pm.col_estimate'), __('pm.col_logged'), __('pm.col_created'), __('pm.col_completed')];

        $rows = $tasks->map(fn (Task $t) => [
            $t->task_number,
            $t->title,
            __(Str::headline($t->status)),
            __(ucfirst($t->priority)),
            $t->assignees->pluck('name')->whenEmpty(fn ($c) => $c->push($t->assignee?->name))->filter()->implode('، '),
            $t->labels->pluck('name')->implode('، '),
            $t->milestone?->name,
            $t->sprint?->name,
            $t->start_date?->format('Y-m-d'),
            $t->due_date?->format('Y-m-d'),
            (float) $t->estimated_hours,
            round($t->timeEntries->sum('duration_seconds') / 3600, 2),
            $t->created_at?->format('Y-m-d'),
            $t->completed_at?->format('Y-m-d'),
        ]);

        return SpreadsheetExport::download($format, Str::slug($project->code ?: 'project').'-tasks', $headers, $rows, $this->rtl());
    }

    public function timeEntries(Request $request, Organization $organization): Response
    {
        $format = $this->format($request);
        $membership = $request->get('current_membership');

        $query = TimeEntry::forOrganization($organization->id)
            ->with(['project:id,name', 'task:id,title,task_number', 'user:id,name'])
            ->orderBy('started_at');

        // Same rule as GET /time/entries: plain employees only see their own time.
        if (! $membership || (! $membership->hasPermission('reports.view') && $membership->role?->slug === 'employee')) {
            $query->where('user_id', Auth::id());
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->query('user_id'));
        }
        $query->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->query('project_id')))
            ->when($request->filled('from_date'), fn ($q) => $q->whereDate('started_at', '>=', $request->query('from_date')))
            ->when($request->filled('to_date'), fn ($q) => $q->whereDate('started_at', '<=', $request->query('to_date')));

        $headers = [__('pm.date'), __('pm.col_member'), __('Project'), __('pm.col_title'), __('pm.col_from'), __('pm.col_to'), __('pm.col_hours'),
            __('pm.col_billable'), __('pm.col_cost_rate'), __('pm.col_billing_rate'), __('pm.col_cost'), __('pm.col_revenue'), __('Status')];

        $rows = $query->get()->map(function (TimeEntry $e) {
            $hours = round($e->duration_seconds / 3600, 2);

            return [
                $e->started_at?->format('Y-m-d'),
                $e->user?->name,
                $e->project?->name,
                $e->task ? '#'.$e->task->task_number.' '.$e->task->title : $e->description,
                $e->started_at?->format('H:i'),
                $e->ended_at?->format('H:i'),
                $hours,
                $e->is_billable ? __('pm.yes') : __('pm.no'),
                (float) $e->cost_rate,
                (float) $e->billing_rate,
                round($hours * (float) $e->cost_rate, 2),
                $e->is_billable ? round($hours * (float) $e->billing_rate, 2) : 0.0,
                __(ucfirst($e->status)),
            ];
        });

        return SpreadsheetExport::download($format, 'time-entries', $headers, $rows, $this->rtl());
    }

    public function timesheets(Request $request, Organization $organization): Response
    {
        $format = $this->format($request);
        $membership = $request->get('current_membership');

        $query = Timesheet::forOrganization($organization->id)->with(['user:id,name', 'reviewer:id,name'])->orderByDesc('period_start');
        // Same rule as GET /timesheets.
        if (! $membership || (! $membership->hasPermission('timesheets.approve') && $membership->role?->slug === 'employee')) {
            $query->where('user_id', Auth::id());
        }
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')));

        $headers = [__('pm.col_period_start'), __('pm.col_period_end'), __('pm.col_member'), __('Status'), __('pm.col_total_hours'), __('pm.col_billable_hours'), __('pm.col_reviewer'), __('pm.col_submitted')];

        $rows = $query->get()->map(fn (Timesheet $s) => [
            $s->period_start?->format('Y-m-d'),
            $s->period_end?->format('Y-m-d'),
            $s->user?->name,
            __(ucfirst($s->status)),
            (float) $s->total_hours,
            (float) $s->billable_hours,
            $s->reviewer?->name,
            $s->submitted_at?->format('Y-m-d'),
        ]);

        return SpreadsheetExport::download($format, 'timesheets', $headers, $rows, $this->rtl());
    }

    private function format(Request $request): string
    {
        $request->validate([
            'format' => ['nullable', Rule::in(SpreadsheetExport::FORMATS)],
            'lang' => ['nullable', Rule::in(['ar', 'en'])],
        ]);
        // API routes skip the web locale middleware; the page passes its language.
        if ($request->filled('lang')) {
            app()->setLocale($request->query('lang'));
        }

        return $request->query('format', 'xlsx');
    }

    private function rtl(): bool
    {
        return app()->getLocale() === 'ar';
    }
}
