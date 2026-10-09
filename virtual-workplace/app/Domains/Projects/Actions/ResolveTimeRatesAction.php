<?php

namespace App\Domains\Projects\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\ProjectMember;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;

class ResolveTimeRatesAction
{
    /**
     * Snapshot the cost/billing rates for a time entry: the project-specific
     * override wins, otherwise fall back to the organization member's rates.
     *
     * @return array{cost_rate: float|string, billing_rate: float|string}
     */
    public function execute(Organization $organization, string $projectId, User $user): array
    {
        $projectMember = ProjectMember::where('project_id', $projectId)->where('user_id', $user->id)->first();

        if ($projectMember && ($projectMember->cost_rate > 0 || $projectMember->billing_rate > 0)) {
            return [
                'cost_rate' => $projectMember->cost_rate ?? 0.00,
                'billing_rate' => $projectMember->billing_rate ?? 0.00,
            ];
        }

        $orgMember = OrganizationMember::where('organization_id', $organization->id)->where('user_id', $user->id)->first();

        return [
            'cost_rate' => $orgMember->cost_rate ?? 0.00,
            'billing_rate' => $orgMember->billing_rate ?? 0.00,
        ];
    }
}
