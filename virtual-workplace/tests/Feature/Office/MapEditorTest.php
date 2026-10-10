<?php

namespace Tests\Feature\Office;

use App\Domains\Workspace\Models\Floor;
use App\Domains\Workspace\Models\Map;
use App\Domains\Workspace\Models\Room;
use App\Domains\Workspace\Support\MapGeometry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

class MapEditorTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    private Map $map;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
        $floor = Floor::withoutGlobalScopes()->create(['organization_id' => $this->org->id, 'name' => 'HQ', 'level' => 1]);
        $this->map = Map::withoutGlobalScopes()->create(['organization_id' => $this->org->id, 'floor_id' => $floor->id, 'name' => 'Map', 'status' => 'published']);
    }

    public function test_walls_and_obstacles_are_saved_cleaned(): void
    {
        $this->actingAs($this->owner)->postJson("/editor/maps/{$this->map->id}/save", [
            'layout_data' => [
                'background_width' => 1200,
                'walls' => [
                    ['x1' => 1, 'y1' => 2, 'x2' => 10, 'y2' => 2],
                    ['x1' => 'evil', 'y1' => 0, 'x2' => 1, 'y2' => 1],   // not numeric
                    ['x1' => 3, 'y1' => 3, 'x2' => 3, 'y2' => 3],        // zero length
                    ['x1' => -5, 'y1' => 4, 'x2' => 9999, 'y2' => 4],   // clamped
                ],
                'obstacles' => [['x' => 5, 'y' => 6, 'width' => 2, 'height' => 1], ['x' => 1, 'y' => 1, 'width' => 0, 'height' => 3]],
            ],
            'rooms' => [], 'objects' => [],
        ])->assertSuccessful();

        $layout = $this->map->fresh()->layout_data;
        $this->assertSame(1200, $layout['background_width']);
        $this->assertCount(2, $layout['walls']);
        $this->assertEquals(['x1' => 0, 'y1' => 4, 'x2' => 2000, 'y2' => 4], $layout['walls'][1]);
        $this->assertEquals([['x' => 5, 'y' => 6, 'width' => 2, 'height' => 1]], $layout['obstacles']);
    }

    public function test_too_many_shapes_are_capped(): void
    {
        $walls = array_fill(0, MapGeometry::MAX_WALLS + 50, ['x1' => 0, 'y1' => 0, 'x2' => 1, 'y2' => 0]);
        $this->assertCount(MapGeometry::MAX_WALLS, MapGeometry::sanitize(['walls' => $walls])['walls']);
    }

    public function test_members_without_the_map_permission_cannot_change_the_map(): void
    {
        $room = Room::withoutGlobalScopes()->create([
            'organization_id' => $this->org->id, 'map_id' => $this->map->id, 'name' => 'R', 'type' => 'meeting',
            'access_mode' => 'public', 'capacity' => 4, 'bounds' => ['x' => 1, 'y' => 1, 'width' => 5, 'height' => 5],
        ]);

        $this->actingAs($this->employee);
        $this->postJson("/editor/maps/{$this->map->id}/save", ['layout_data' => ['walls' => []]])->assertForbidden();
        $this->postJson("/editor/maps/{$this->map->id}/clear")->assertForbidden();
        $this->postJson("/editor/maps/{$this->map->id}/publish")->assertForbidden();
        $this->postJson('/editor/rooms', ['organization_id' => $this->org->id, 'map_id' => $this->map->id, 'name' => 'X', 'bounds' => ['x' => 1, 'y' => 1, 'width' => 2, 'height' => 2]])->assertForbidden();
        $this->deleteJson("/editor/rooms/{$room->id}")->assertForbidden();
        $this->assertNotNull(Room::withoutGlobalScopes()->find($room->id));

        // The company admin may.
        $this->actingAs($this->owner)->postJson("/editor/maps/{$this->map->id}/save", ['layout_data' => ['walls' => []]])->assertSuccessful();
    }
}
