<?php

namespace App\Domains\Projects\Controllers;

use App\Domains\Projects\Actions\CreateTaskAction;
use App\Domains\Projects\Models\IntakeRequest;
use App\Domains\Projects\Models\Project;
use App\Domains\Tenancy\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Triage of outside requests: list, accept (→ task), decline, snooze, mark duplicate; link settings. */
class IntakeController extends Controller
{
    public function index(Request $request, Organization $organization, Project $project): JsonResponse
    {
        $this->guard($organization, $project);

        $view = $request->query('view', 'open');
        $query = $project->intakeRequests()->with(['task:id,task_number,title', 'duplicateOf:id,task_number,title'])->latest();
        $view === 'open' ? $query->open() : $query->where('status', $view);

        return response()->json([
            'requests' => $query->limit(200)->get(),
            'open_count' => $project->intakeRequests()->open()->count(),
            'settings' => $this->settingsPayload($project),
        ]);
    }

    public function updateSettings(Request $request, Organization $organization, Project $project): JsonResponse
    {
        $this->guard($organization, $project);
        $validated = $request->validate([
            'enabled' => ['sometimes', 'boolean'],
            'regenerate' => ['sometimes', 'boolean'],
        ]);

        if (($validated['regenerate'] ?? false) || ! $project->intake_token) {
            $project->forceFill(['intake_token' => Str::random(40)]);
        }
        if (array_key_exists('enabled', $validated)) {
            $project->forceFill(['intake_enabled' => $validated['enabled']]);
        }
        $project->save();

        return response()->json(['settings' => $this->settingsPayload($project)]);
    }

    public function accept(Request $request, Organization $organization, Project $project, IntakeRequest $intake, CreateTaskAction $createTask): JsonResponse
    {
        $this->guardRequest($organization, $project, $intake);
        $validated = $request->validate([
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'assignee_id' => ['nullable', Rule::exists('organization_members', 'user_id')->where('organization_id', $organization->id)],
        ]);

        $task = DB::transaction(function () use ($intake, $project, $organization, $validated, $createTask) {
            $from = $intake->requester_name.($intake->requester_email ? " <{$intake->requester_email}>" : '');
            $task = $createTask->execute([
                'project_id' => $project->id,
                'title' => $intake->title,
                'description' => trim(($intake->description ?? '')."\n\n— ".__('pm.requested_by', ['name' => $from])),
                'priority' => $validated['priority'] ?? 'medium',
                'assignee_id' => $validated['assignee_id'] ?? null,
            ], $organization, Auth::user());

            $intake->update(['status' => 'accepted', 'task_id' => $task->id] + $this->reviewed());

            return $task;
        });

        return response()->json(['message' => 'Request accepted.', 'task' => $task->only(['id', 'task_number', 'title'])]);
    }

    public function decline(Request $request, Organization $organization, Project $project, IntakeRequest $intake): JsonResponse
    {
        $this->guardRequest($organization, $project, $intake);
        $validated = $request->validate(['reason' => ['nullable', 'string', 'max:1000']]);

        $intake->update(['status' => 'declined', 'decline_reason' => $validated['reason'] ?? null] + $this->reviewed());

        return response()->json(['message' => 'Request declined.']);
    }

    public function snooze(Request $request, Organization $organization, Project $project, IntakeRequest $intake): JsonResponse
    {
        $this->guardRequest($organization, $project, $intake);
        $validated = $request->validate(['until' => ['required', 'date', 'after:today']]);

        $intake->update(['status' => 'snoozed', 'snoozed_until' => $validated['until']] + $this->reviewed());

        return response()->json(['message' => 'Request snoozed.']);
    }

    public function duplicate(Request $request, Organization $organization, Project $project, IntakeRequest $intake): JsonResponse
    {
        $this->guardRequest($organization, $project, $intake);
        $validated = $request->validate([
            'task_id' => ['required', Rule::exists('tasks', 'id')->where('project_id', $project->id)->whereNull('archived_at')],
        ]);

        $intake->update(['status' => 'duplicate', 'duplicate_of_task_id' => $validated['task_id']] + $this->reviewed());

        return response()->json(['message' => 'Marked as duplicate.']);
    }

    private function reviewed(): array
    {
        return ['reviewed_by' => Auth::id(), 'reviewed_at' => now()];
    }

    private function settingsPayload(Project $project): array
    {
        return [
            'enabled' => (bool) $project->intake_enabled,
            'url' => $project->intake_token ? route('intake.show', $project->intake_token) : null,
        ];
    }

    private function guard(Organization $organization, Project $project): void
    {
        abort_unless($project->organization_id === $organization->id, 404);
    }

    private function guardRequest(Organization $organization, Project $project, IntakeRequest $intake): void
    {
        abort_unless($project->organization_id === $organization->id && $intake->project_id === $project->id, 404);
        abort_unless(in_array($intake->status, ['pending', 'snoozed'], true), 422, __('pm.intake_already_handled'));
    }
}
