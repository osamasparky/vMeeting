<?php

namespace App\Domains\Projects\Controllers;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectDocument;
use App\Domains\Projects\Models\ProjectDocumentVersion;
use App\Domains\Tenancy\Models\Organization;
use App\Support\HtmlSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

/** Read / edit a project document with version history and restore. */
class ProjectDocumentController extends Controller
{
    public function show(Organization $organization, Project $project, ProjectDocument $document): JsonResponse
    {
        $this->guard($organization, $project, $document);

        return response()->json(['document' => $this->present($document)]);
    }

    public function update(Request $request, Organization $organization, Project $project, ProjectDocument $document): JsonResponse
    {
        $this->guard($organization, $project, $document);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:500000'],
            'icon' => ['nullable', 'string', 'max:30'],
            'is_pinned' => ['sometimes', 'boolean'],
            // Optimistic lock: the version the editor started from.
            'base_version' => ['nullable', 'integer'],
        ]);

        if (isset($validated['base_version']) && $validated['base_version'] !== $document->version) {
            return response()->json([
                'message' => __('pm.doc_conflict'),
                'document' => $this->present($document),
            ], 409);
        }

        $changed = $document->saveRevision($validated['title'], HtmlSanitizer::clean($validated['content'] ?? ''), Auth::id());
        $document->fill(array_intersect_key($validated, array_flip(['icon', 'is_pinned'])))->save();

        return response()->json(['message' => $changed ? 'Saved.' : 'No changes.', 'document' => $this->present($document->fresh())]);
    }

    public function versions(Organization $organization, Project $project, ProjectDocument $document): JsonResponse
    {
        $this->guard($organization, $project, $document);

        return response()->json([
            'versions' => $document->versions()->with('user:id,name')->limit(100)->get(['id', 'document_id', 'version', 'title', 'user_id', 'created_at']),
        ]);
    }

    public function version(Organization $organization, Project $project, ProjectDocument $document, ProjectDocumentVersion $version): JsonResponse
    {
        $this->guard($organization, $project, $document);
        abort_unless($version->document_id === $document->id, 404);

        return response()->json(['version' => [
            'version' => $version->version,
            'title' => $version->title,
            'html' => HtmlSanitizer::forDisplay($version->content),
            'user' => $version->user?->only(['id', 'name']),
            'created_at' => $version->created_at,
        ]]);
    }

    public function restore(Organization $organization, Project $project, ProjectDocument $document, ProjectDocumentVersion $version): JsonResponse
    {
        $this->guard($organization, $project, $document);
        abort_unless($version->document_id === $document->id, 404);

        // Restoring is itself a new version, so nothing is ever lost.
        $document->saveRevision($version->title, HtmlSanitizer::forDisplay($version->content), Auth::id());

        return response()->json(['message' => 'Restored.', 'document' => $this->present($document->fresh())]);
    }

    private function present(ProjectDocument $document): array
    {
        $document->loadMissing(['author:id,name', 'lastEditor:id,name']);

        return [
            'id' => $document->id,
            'title' => $document->title,
            'icon' => $document->icon,
            'is_pinned' => $document->is_pinned,
            'version' => $document->version,
            'html' => HtmlSanitizer::forDisplay($document->content),
            'author' => $document->author?->only(['id', 'name']),
            'last_editor' => $document->lastEditor?->only(['id', 'name']),
            'updated_at' => $document->updated_at,
        ];
    }

    private function guard(Organization $organization, Project $project, ProjectDocument $document): void
    {
        abort_unless($project->organization_id === $organization->id && $document->project_id === $project->id, 404);
    }
}
