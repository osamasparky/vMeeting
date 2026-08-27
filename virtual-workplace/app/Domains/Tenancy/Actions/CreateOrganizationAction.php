<?php

namespace App\Domains\Tenancy\Actions;

use App\Domains\Administration\Models\Role;
use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Tenancy\Models\OrganizationSetting;
use App\Domains\Tenancy\Models\Plan;
use Illuminate\Support\Str;

class CreateOrganizationAction
{
    /**
     * Create a new organization and assign the creator as Company Admin.
     */
    public function execute(array $data, User $owner): Organization
    {
        // Determine plan (selected or default free)
        $selectedPlan = null;
        if (! empty($data['plan_id'])) {
            $selectedPlan = Plan::find($data['plan_id']);
        } elseif (! empty($data['plan_slug'])) {
            $selectedPlan = Plan::where('slug', $data['plan_slug'])->first();
        }
        if (! $selectedPlan) {
            $selectedPlan = Plan::where('slug', 'free')->first() ?? Plan::first();
        }

        $organization = Organization::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? Str::slug($data['name']).'-'.Str::random(4),
            'timezone' => $data['timezone'] ?? 'UTC',
            'plan_id' => $selectedPlan?->id,
        ]);

        // Create default settings
        OrganizationSetting::create([
            'organization_id' => $organization->id,
        ]);

        // Add owner as Company Admin
        $adminRole = Role::where('slug', 'company_admin')
            ->whereNull('organization_id')
            ->first();

        OrganizationMember::create([
            'organization_id' => $organization->id,
            'user_id' => $owner->id,
            'role_id' => $adminRole->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        return $organization->load('settings', 'plan');
    }
}
