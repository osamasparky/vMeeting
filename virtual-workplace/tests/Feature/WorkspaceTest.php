<?php

namespace Tests\Feature;

use App\Domains\Administration\Models\Role;
use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Workspace\Models\Floor;
use App\Domains\Workspace\Models\Map;
use App\Domains\Workspace\Models\Room;
use App\Domains\Workspace\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\PlansSeeder::class);
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->user = User::factory()->create([
            'email' => 'admin@workspace.test',
        ]);

        $createOrgAction = app(CreateOrganizationAction::class);
        $this->organization = $createOrgAction->execute(
            ['name' => 'Acme Corp'],
            $this->user
        );
    }

    public function test_can_create_and_list_floors(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/v1/organizations/{$this->organization->id}/floors", [
            'name' => 'Ground Floor',
            'order' => 1,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('floor.name', 'Ground Floor')
            ->assertJsonPath('floor.order', 1);

        $listResponse = $this->getJson("/api/v1/organizations/{$this->organization->id}/floors");
        $listResponse->assertStatus(200)
            ->assertJsonCount(1, 'floors');
    }

    public function test_can_create_update_and_publish_map_with_versioning(): void
    {
        Sanctum::actingAs($this->user);

        // 1. Create floor
        $floor = Floor::create([
            'organization_id' => $this->organization->id,
            'name' => 'HQ Main Floor',
            'order' => 1,
        ]);

        // 2. Create map draft
        $mapResponse = $this->postJson("/api/v1/organizations/{$this->organization->id}/maps", [
            'floor_id' => $floor->id,
            'name' => 'Default Office Layout',
            'width' => 40,
            'height' => 30,
        ]);

        $mapResponse->assertStatus(201)
            ->assertJsonPath('map.status', 'draft')
            ->assertJsonPath('map.version', 1);

        $mapId = $mapResponse->json('map.id');

        // 3. Create room in map
        $roomResponse = $this->postJson("/api/v1/organizations/{$this->organization->id}/rooms", [
            'map_id' => $mapId,
            'name' => 'Conference Room A',
            'type' => 'meeting',
            'access_mode' => 'public',
            'capacity' => 12,
            'bounds' => ['x' => 5, 'y' => 5, 'width' => 8, 'height' => 6],
        ]);
        $roomResponse->assertStatus(201);

        // 4. Create zone in map
        $zoneResponse = $this->postJson("/api/v1/organizations/{$this->organization->id}/zones", [
            'map_id' => $mapId,
            'name' => 'Quiet Lounge',
            'type' => 'quiet',
            'shape_type' => 'rectangle',
            'shape_data' => ['x' => 15, 'y' => 5, 'width' => 6, 'height' => 6],
        ]);
        $zoneResponse->assertStatus(201);

        // 5. Sync map objects
        $syncResponse = $this->postJson("/api/v1/organizations/{$this->organization->id}/maps/{$mapId}/objects/sync", [
            'objects' => [
                [
                    'type' => 'desk',
                    'name' => 'Developer Desk 1',
                    'position' => ['x' => 10, 'y' => 10],
                    'collision' => true,
                ],
                [
                    'type' => 'chair',
                    'name' => 'Ergo Chair',
                    'position' => ['x' => 10, 'y' => 11],
                    'collision' => false,
                ],
            ],
        ]);
        $syncResponse->assertStatus(200)
            ->assertJsonCount(2, 'objects');

        // 6. Publish map first time (v1)
        $publishResponse = $this->postJson("/api/v1/organizations/{$this->organization->id}/maps/{$mapId}/publish");
        $publishResponse->assertStatus(200)
            ->assertJsonPath('map.status', 'published');

        // 7. Publish map second time (v2)
        $publishResponse2 = $this->postJson("/api/v1/organizations/{$this->organization->id}/maps/{$mapId}/publish");
        $publishResponse2->assertStatus(200)
            ->assertJsonPath('map.status', 'published');

        // 8. Verify version snapshot history exists (2 versions)
        $versionsResponse = $this->getJson("/api/v1/organizations/{$this->organization->id}/maps/{$mapId}/versions");
        $versionsResponse->assertStatus(200)
            ->assertJsonCount(2, 'versions');
    }

    public function test_tenant_isolation_prevents_access_to_other_org_maps(): void
    {
        // Create second org and user
        $otherUser = User::factory()->create(['email' => 'other@corp.test']);
        $createOrgAction = app(CreateOrganizationAction::class);
        $otherOrg = $createOrgAction->execute(['name' => 'Other Corp'], $otherUser);

        $floor = Floor::create([
            'organization_id' => $this->organization->id,
            'name' => 'Private Floor',
        ]);

        $map = Map::create([
            'organization_id' => $this->organization->id,
            'floor_id' => $floor->id,
            'name' => 'Secret Layout',
            'status' => 'draft',
        ]);

        // Other user attempts to view map in org A
        Sanctum::actingAs($otherUser);

        $response = $this->getJson("/api/v1/organizations/{$this->organization->id}/maps/{$map->id}");
        $response->assertStatus(403);
    }

    public function test_authenticated_user_can_access_map_editor_view(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/editor');
        $response->assertStatus(200)
            ->assertSee('Map Editor')
            ->assertSee('Acme Corp');
    }
}

