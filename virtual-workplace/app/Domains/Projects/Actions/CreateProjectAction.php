<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectMember;
use App\Domains\Tenancy\Models\Organization;
use Illuminate\Support\Facades\DB;

class CreateProjectAction
{
    public function execute(array $data, Organization $organization, User $creator): Project
    {
        return DB::transaction(function () use ($data, $organization, $creator) {
            $code = $data['code'] ?? null;
            if (empty($code)) {
                $count = Project::where('organization_id', $organization->id)->count() + 1;
                $code = 'PRJ-'.str_pad((string) $count, 3, '0', STR_PAD_LEFT);
            }

            $project = Project::create([
                'organization_id' => $organization->id,
                'name' => $data['name'],
                'code' => $code,
                'description' => $data['description'] ?? null,
                'owner_id' => $creator->id,
                'manager_id' => $data['manager_id'] ?? $creator->id,
                'department_id' => $data['department_id'] ?? null,
                'status' => $data['status'] ?? 'active',
                'priority' => $data['priority'] ?? 'medium',
                'start_date' => $data['start_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'budget_amount' => $data['budget_amount'] ?? null,
                'planned_hours' => $data['planned_hours'] ?? null,
                'color' => $data['color'] ?? '#3b82f6',
            ]);

            // Add creator / manager as project member
            ProjectMember::updateOrCreate(
                ['project_id' => $project->id, 'user_id' => $creator->id],
                [
                    'organization_id' => $organization->id,
                    'project_role' => 'manager',
                ]
            );

            // Add additional members if provided
            if (! empty($data['members']) && is_array($data['members'])) {
                foreach ($data['members'] as $m) {
                    ProjectMember::updateOrCreate(
                        ['project_id' => $project->id, 'user_id' => $m['user_id']],
                        [
                            'organization_id' => $organization->id,
                            'project_role' => $m['project_role'] ?? 'contributor',
                            'cost_rate' => $m['cost_rate'] ?? null,
                            'billing_rate' => $m['billing_rate'] ?? null,
                        ]
                    );
                }
            }

            return $project->load(['owner', 'manager', 'department', 'members.user']);
        });
    }
}
