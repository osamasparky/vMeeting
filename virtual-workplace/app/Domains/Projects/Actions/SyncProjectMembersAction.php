<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectMember;
use Illuminate\Support\Facades\DB;

/**
 * Set who works on a project. People picked from any department, or a whole
 * department at once in the picker, arrive here as user ids.
 *
 * Members who stay keep their row — role and project rates untouched. The
 * owner and the manager are always members. Past time entries carry their own
 * rates, so removing someone does not change what was logged.
 */
class SyncProjectMembersAction
{
    /** @param  array<int, string>  $userIds  organization members (validated by the request) */
    public function execute(Project $project, array $userIds): Project
    {
        $keep = collect($userIds)
            ->push($project->owner_id, $project->manager_id)
            ->filter()
            ->unique()
            ->values();

        DB::transaction(function () use ($project, $keep) {
            ProjectMember::where('project_id', $project->id)
                ->whereNotIn('user_id', $keep->all())
                ->delete();

            $existing = ProjectMember::where('project_id', $project->id)->pluck('user_id');
            foreach ($keep->diff($existing) as $userId) {
                ProjectMember::create([
                    'organization_id' => $project->organization_id,
                    'project_id' => $project->id,
                    'user_id' => $userId,
                    'project_role' => $userId === $project->manager_id ? 'manager' : 'contributor',
                ]);
            }
        });

        return $project->load('members.user');
    }
}
