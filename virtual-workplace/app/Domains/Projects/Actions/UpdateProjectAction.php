<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Projects\Models\Project;

class UpdateProjectAction
{
    public function execute(Project $project, array $data): Project
    {
        $memberIds = $data['member_ids'] ?? null;
        unset($data['member_ids']);

        if (isset($data['status']) && $data['status'] === 'completed' && $project->status !== 'completed') {
            $data['completed_at'] = now();
        } elseif (isset($data['status']) && $data['status'] !== 'completed') {
            $data['completed_at'] = null;
        }

        $project->update($data);

        if (is_array($memberIds)) {
            app(SyncProjectMembersAction::class)->execute($project, $memberIds);
        }

        return $project->fresh(['owner', 'manager', 'department', 'members.user']);
    }
}
