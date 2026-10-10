<?php

namespace App\Http\Controllers\Web;

use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Projects\Models\IntakeRequest;
use App\Domains\Projects\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * The public request form behind a project's intake link. No account is
 * needed; a hidden honeypot field and rate limiting keep bots out.
 */
class PublicIntakeController extends Controller
{
    public function show(Request $request, string $token): View
    {
        $project = $this->project($token);
        $this->arabicFirst($request);

        return view('intake.form', ['project' => $project, 'sent' => session('intake_sent', false)]);
    }

    public function store(Request $request, string $token)
    {
        $project = $this->project($token);
        $this->arabicFirst($request);

        // Bots fill every field; people never see this one.
        if ($request->filled('website')) {
            return redirect()->route('intake.show', $token)->with('intake_sent', true);
        }

        $validated = $request->validate([
            'requester_name' => ['required', 'string', 'max:120'],
            'requester_email' => ['nullable', 'email', 'max:190'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $intake = IntakeRequest::create($validated + [
            'organization_id' => $project->organization_id,
            'project_id' => $project->id,
            'status' => 'pending',
        ]);

        foreach (collect([$project->manager_id, $project->owner_id])->filter()->unique() as $userId) {
            NotificationService::send(
                $userId,
                'task_intake',
                fn () => __('📥 New request for :project: :title', ['project' => $project->name, 'title' => $intake->title]),
                Str::limit(($intake->description ?? '').' — '.$intake->requester_name, 160),
                '/projects/'.$project->id.'#intake',
                ['project_id' => $project->id, 'intake_id' => $intake->id],
                '📥',
                $project->organization_id,
            );
        }

        return redirect()->route('intake.show', $token)->with('intake_sent', true);
    }

    /** Visitors who haven't picked a language get Arabic, the product's primary language. */
    private function arabicFirst(Request $request): void
    {
        if (! $request->has('lang') && ! $request->session()->has('locale') && ! $request->cookie('locale')) {
            app()->setLocale('ar');
        }
    }

    private function project(string $token): Project
    {
        return Project::withoutGlobalScopes()
            ->whereNull('archived_at')
            ->where('intake_enabled', true)
            ->where('intake_token', $token)
            ->firstOrFail();
    }
}
