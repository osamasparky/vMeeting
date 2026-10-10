<?php

namespace Tests\Feature\Notifications;

use App\Domains\Notifications\Models\WorkplaceNotification;
use App\Domains\People\Models\AttendanceSession;
use App\Domains\Workspace\Models\Floor;
use App\Domains\Workspace\Models\Map;
use App\Domains\Workspace\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

class SpatialNotificationsTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
    }

    private function room(string $orgId): Room
    {
        $floor = Floor::withoutGlobalScopes()->create(['organization_id' => $orgId, 'name' => 'Main', 'level' => 1]);
        $map = Map::withoutGlobalScopes()->create(['organization_id' => $orgId, 'floor_id' => $floor->id, 'name' => 'Map', 'status' => 'published']);

        return Room::withoutGlobalScopes()->create([
            'organization_id' => $orgId, 'map_id' => $map->id, 'name' => 'Focus', 'type' => 'meeting',
            'access_mode' => 'public', 'capacity' => 4, 'bounds' => ['x' => 0, 'y' => 0, 'width' => 10, 'height' => 10],
        ]);
    }

    public function test_api_feed_and_mark_read_use_the_bell_table(): void
    {
        $n = WorkplaceNotification::create(['user_id' => $this->employee->id, 'organization_id' => $this->org->id, 'type' => 'task_assigned', 'title' => 'Hi', 'data' => [], 'is_read' => false]);
        WorkplaceNotification::create(['user_id' => $this->manager->id, 'organization_id' => $this->org->id, 'type' => 'task_assigned', 'title' => 'Not yours', 'data' => [], 'is_read' => false]);

        Sanctum::actingAs($this->employee);
        $this->getJson('/api/v1/notifications')->assertOk()
            ->assertJsonPath('unread_count', 1)->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Hi');

        $this->postJson("/api/v1/notifications/{$n->id}/read")->assertOk();
        $this->assertTrue($n->fresh()->is_read);

        // Filtering by organization keeps that organization's notices.
        WorkplaceNotification::create(['user_id' => $this->employee->id, 'organization_id' => $this->otherOrg->id, 'type' => 'task_assigned', 'title' => 'Elsewhere', 'data' => [], 'is_read' => false]);
        $this->getJson('/api/v1/notifications?organization_id='.$this->org->id)->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('unread_count', 0);
        $this->getJson('/api/v1/notifications')->assertOk()->assertJsonCount(2, 'data');

        $other = WorkplaceNotification::where('user_id', $this->manager->id)->first();
        $this->postJson("/api/v1/notifications/{$other->id}/read")->assertNotFound();
    }

    public function test_api_wave_and_ring_reach_teammates_only(): void
    {
        Sanctum::actingAs($this->employee);

        $this->postJson($this->api('/interactions/wave'), ['target_user_id' => $this->manager->id])->assertOk();
        $this->postJson($this->api('/interactions/ring'), ['target_user_id' => $this->manager->id])->assertOk();
        $this->assertSame(['user_ring', 'wave'], WorkplaceNotification::where('user_id', $this->manager->id)->orderBy('type')->pluck('type')->all());
        $this->assertSame($this->org->id, WorkplaceNotification::where('type', 'wave')->value('organization_id'));

        // Someone from another organization, and yourself, are refused.
        $this->postJson($this->api('/interactions/wave'), ['target_user_id' => $this->outsider->id])->assertUnprocessable();
        $this->postJson($this->api('/interactions/ring'), ['target_user_id' => $this->employee->id])->assertUnprocessable();
        $this->assertSame(0, WorkplaceNotification::where('user_id', $this->outsider->id)->count());
    }

    public function test_api_knock_tells_the_people_inside(): void
    {
        $room = $this->room($this->org->id);
        AttendanceSession::withoutGlobalScopes()->create([
            'organization_id' => $this->org->id, 'user_id' => $this->manager->id, 'room_id' => $room->id,
            'status' => 'active', 'started_at' => now(), 'last_heartbeat_at' => now(),
        ]);

        Sanctum::actingAs($this->employee);
        $this->postJson($this->api('/interactions/knock'), ['room_id' => $room->id])->assertOk()->assertJsonPath('notified', 1);
        $this->assertSame('door_knock', WorkplaceNotification::where('user_id', $this->manager->id)->value('type'));
        $this->assertSame(0, WorkplaceNotification::where('user_id', $this->employee->id)->count());

        // Rooms of another organization are not found.
        $foreign = $this->room($this->otherOrg->id);
        $this->postJson($this->api('/interactions/knock'), ['room_id' => $foreign->id])->assertUnprocessable();
    }

    public function test_web_wave_is_limited_to_the_organization(): void
    {
        $this->actingAs($this->employee)->postJson('/api/notifications/wave', ['target_user_id' => $this->outsider->id])->assertUnprocessable();
        $this->actingAs($this->employee)->postJson('/api/notifications/wave', ['target_user_id' => $this->manager->id])->assertOk();
        $this->assertSame($this->org->id, WorkplaceNotification::where('user_id', $this->manager->id)->value('organization_id'));
    }
}
