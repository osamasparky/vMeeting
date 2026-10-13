<?php

namespace Tests\Feature\Hr\Concerns;

use App\Domains\Administration\Models\Role;
use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Workspace\Models\Floor;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;

/**
 * An organization with its owner (company_admin), one employee and a main
 * office in Riyadh working Sun–Thu 09:00–17:00 with 15 minutes' grace.
 */
trait BuildsHrWorkspace
{
    protected User $owner;

    protected User $employee;

    protected Organization $organization;

    protected Floor $office;

    protected function buildHrWorkspace(): void
    {
        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->owner = User::factory()->create();
        $this->organization = app(CreateOrganizationAction::class)->execute(['name' => 'Acme'], $this->owner);
        $this->organization->plan?->update(['max_offices' => 10]);

        $this->employee = User::factory()->create();
        OrganizationMember::create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->employee->id,
            'role_id' => Role::where('slug', 'employee')->value('id'),
            'status' => 'active',
        ]);

        $this->office = Floor::create([
            'organization_id' => $this->organization->id,
            'name' => 'Riyadh HQ',
            'timezone' => 'Asia/Riyadh',
            'is_default' => true,
            'order' => 0,
        ]);
        Floor::where('organization_id', $this->organization->id)->where('id', '!=', $this->office->id)->update(['is_default' => false]);
    }

    protected function memberOf(User $user): OrganizationMember
    {
        return OrganizationMember::where('organization_id', $this->organization->id)->where('user_id', $user->id)->firstOrFail();
    }
}
