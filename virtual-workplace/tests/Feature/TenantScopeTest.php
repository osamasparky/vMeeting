<?php

namespace Tests\Feature;

use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\Project;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\Organization;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Architecture Audit §7/§15, ADR-002: TenantScope is a flag-gated global
 * scope on BelongsToOrganization models. These tests lock in that it is a
 * genuine no-op until explicitly enabled, and behaves correctly once it is.
 */
class TenantScopeTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected Organization $orgA;

    protected Organization $orgB;

    protected Project $projectA;

    protected Project $projectB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->owner = User::factory()->create(['email' => 'owner@tenant-scope.test']);
        $this->orgA = app(CreateOrganizationAction::class)->execute(['name' => 'Org A'], $this->owner);
        $this->orgB = app(CreateOrganizationAction::class)->execute(['name' => 'Org B'], $this->owner);

        $this->projectA = Project::create([
            'organization_id' => $this->orgA->id,
            'name' => 'Project A',
            'code' => 'ORG-A',
            'owner_id' => $this->owner->id,
            'status' => 'active',
            'priority' => 'medium',
        ]);

        $this->projectB = Project::create([
            'organization_id' => $this->orgB->id,
            'name' => 'Project B',
            'code' => 'ORG-B',
            'owner_id' => $this->owner->id,
            'status' => 'active',
            'priority' => 'medium',
        ]);
    }

    public function test_flag_off_by_default_leaves_queries_unrestricted(): void
    {
        config(['tenancy.enforce_global_scope' => false]);

        // No behavior change: a query with no organization filter still sees both orgs' rows.
        $this->assertCount(2, Project::all());
        $this->assertNotNull(Project::find($this->projectB->id));
    }

    public function test_flag_on_with_bound_tenant_hides_other_organizations_rows(): void
    {
        config(['tenancy.enforce_global_scope' => true]);

        request()->merge(['current_organization' => $this->orgA]);

        $visible = Project::all();

        $this->assertCount(1, $visible);
        $this->assertTrue($visible->contains('id', $this->projectA->id));
        $this->assertNull(Project::find($this->projectB->id));
    }

    public function test_flag_on_with_no_bound_tenant_is_unrestricted(): void
    {
        config(['tenancy.enforce_global_scope' => true]);

        // Simulates a SuperAdmin route or console/queue context: no
        // current_organization bound, so nothing to scope to — must not
        // silently restrict to zero rows.
        $this->assertCount(2, Project::all());
    }

    public function test_explicit_escape_hatch_bypasses_the_scope_even_when_enabled(): void
    {
        config(['tenancy.enforce_global_scope' => true]);

        request()->merge(['current_organization' => $this->orgA]);

        $all = Project::withoutTenantScope()->get();

        $this->assertCount(2, $all);
    }
}
