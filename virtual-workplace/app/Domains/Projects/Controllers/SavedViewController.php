<?php

namespace App\Domains\Projects\Controllers;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\SavedView;
use App\Domains\Tenancy\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SavedViewController extends Controller
{
    public function index(Organization $organization, Project $project): JsonResponse
    {
        abort_unless($project->organization_id === $organization->id, 404);

        $views = SavedView::where('project_id', $project->id)
            ->visibleTo(Auth::id())
            ->with('user:id,name')
            ->orderBy('name')
            ->get();

        return response()->json(['views' => $views]);
    }

    public function store(Request $request, Organization $organization, Project $project): JsonResponse
    {
        abort_unless($project->organization_id === $organization->id, 404);

        $data = $this->validated($request);

        $view = SavedView::create([
            'organization_id' => $organization->id,
            'project_id' => $project->id,
            'user_id' => Auth::id(),
            'name' => $data['name'],
            'layout' => $data['layout'] ?? 'kanban',
            'filters' => $this->cleanFilters($data['filters'] ?? []),
            'is_shared' => $data['is_shared'] ?? false,
        ]);

        return response()->json(['message' => 'View saved.', 'view' => $view->load('user:id,name')], 201);
    }

    public function update(Request $request, Organization $organization, Project $project, SavedView $view): JsonResponse
    {
        $this->authorizeOwner($organization, $project, $view);

        $data = $this->validated($request, partial: true);
        if (array_key_exists('filters', $data)) {
            $data['filters'] = $this->cleanFilters($data['filters'] ?? []);
        }
        $view->update($data);

        return response()->json(['message' => 'View updated.', 'view' => $view->load('user:id,name')]);
    }

    public function destroy(Organization $organization, Project $project, SavedView $view): JsonResponse
    {
        $this->authorizeOwner($organization, $project, $view);

        $view->delete();

        return response()->json(['message' => 'View deleted.']);
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $req = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'name' => [$req, 'string', 'max:80'],
            'layout' => ['sometimes', 'string', Rule::in(SavedView::LAYOUTS)],
            'filters' => ['sometimes', 'nullable', 'array'],
            'filters.*' => ['nullable', 'string', 'max:120'],
            'is_shared' => ['sometimes', 'boolean'],
        ]);
    }

    /** Keep only known, non-empty filter keys. */
    private function cleanFilters(array $filters): array
    {
        return array_filter(Arr::only($filters, SavedView::FILTER_KEYS), fn ($v) => $v !== null && $v !== '');
    }

    /** Only the creator edits or deletes a view; project managers may remove shared ones. */
    private function authorizeOwner(Organization $organization, Project $project, SavedView $view): void
    {
        abort_unless($project->organization_id === $organization->id && $view->project_id === $project->id, 404);

        $user = Auth::user();
        $mayModerate = $view->is_shared && ($user->isSuperAdmin()
            || $project->manager_id === $user->id
            || request()->get('current_membership')?->hasPermission('projects.edit'));

        abort_unless($view->user_id === $user->id || $mayModerate, 403);
    }
}
