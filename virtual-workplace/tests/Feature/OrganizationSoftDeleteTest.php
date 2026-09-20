<?php

namespace Tests\Feature;

use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\Organization;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Architecture Audit §5/§10/§15, ADR-007: Organization::delete() previously
 * hard-deleted the row with no recovery path. These tests lock in the fix.
 */
class OrganizationSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_a_plain_delete_call_does_not_remove_the_organization_row(): void
    {
        $owner = User::factory()->create();
        $org = app(CreateOrganizationAction::class)->execute(['name' => 'Recoverable Co'], $owner);
        $orgId = $org->id;

        $org->delete();

        // Gone from default (non-trashed) queries...
        $this->assertNull(Organization::find($orgId));

        // ...but the row itself, and its child data, are still in the database.
        $this->assertDatabaseHas('organizations', ['id' => $orgId]);
        $this->assertNotNull(Organization::withTrashed()->find($orgId)->deleted_at);
    }

    public function test_a_soft_deleted_organization_can_be_restored(): void
    {
        $owner = User::factory()->create();
        $org = app(CreateOrganizationAction::class)->execute(['name' => 'Restore Me Co'], $owner);
        $orgId = $org->id;

        $org->delete();
        Organization::withTrashed()->find($orgId)->restore();

        $restored = Organization::find($orgId);
        $this->assertNotNull($restored);
        $this->assertNull($restored->deleted_at);
    }

    public function test_superadmin_delete_company_action_still_permanently_removes_it(): void
    {
        $superAdmin = User::factory()->create(['email' => 'info@meemdtt.com']);
        $owner = User::factory()->create();
        $org = app(CreateOrganizationAction::class)->execute(['name' => 'Truly Gone Co'], $owner);
        $orgId = $org->id;

        $response = $this->actingAs($superAdmin)->delete(route('superadmin.companies.delete', $org));

        $response->assertRedirect(route('superadmin.companies'));
        // Force-deleted, not soft-deleted: gone even from withTrashed().
        $this->assertNull(Organization::withTrashed()->find($orgId));
        $this->assertDatabaseMissing('organizations', ['id' => $orgId]);
    }
}
