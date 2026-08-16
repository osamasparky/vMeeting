<?php

namespace Tests\Feature;

use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Workspace\Models\Floor;
use App\Domains\Workspace\Models\Map;
use App\Domains\Workspace\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected User $userA;
    protected User $userB;
    protected Organization $orgA;
    protected Organization $orgB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlansSeeder::class);
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $action = app(CreateOrganizationAction::class);

        $this->userA = User::factory()->create(['email' => 'admin@tenant-a.com']);
        $this->orgA = $action->execute(['name' => 'Tenant Alpha'], $this->userA);

        $this->userB = User::factory()->create(['email' => 'admin@tenant-b.com']);
        $this->orgB = $action->execute(['name' => 'Tenant Beta'], $this->userB);
    }

    public function test_tenant_cannot_list_other_tenant_members(): void
    {
        Sanctum::actingAs($this->userA);

        // Attempt to list Tenant Beta members
        $response = $this->getJson("/api/v1/organizations/{$this->orgB->id}/members");
        $response->assertStatus(403);
    }

    public function test_tenant_cannot_access_other_tenant_settings(): void
    {
        Sanctum::actingAs($this->userA);

        // Attempt to view Tenant Beta settings
        $response = $this->getJson("/api/v1/organizations/{$this->orgB->id}/settings");
        $response->assertStatus(403);
    }

    public function test_tenant_cannot_mutate_other_tenant_workspace(): void
    {
        Sanctum::actingAs($this->userA);

        // Attempt to create a floor in Tenant Beta
        $response = $this->postJson("/api/v1/organizations/{$this->orgB->id}/floors", [
            'name' => 'Hacked Floor',
        ]);
        $response->assertStatus(403);
    }
}
