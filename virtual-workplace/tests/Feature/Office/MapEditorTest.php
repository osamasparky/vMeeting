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

    public function test_rooms_must_lie_on_the_floor_image(): void
    {
        $this->map->forceFill(['layout_data' => ['background_width' => 1200, 'background_height' => 720]])->save(); // 75 x 45 tiles
        $this->actingAs($this->owner);

        // Bulk save: a room hanging off the image is refused and nothing is written.
        $this->postJson("/editor/maps/{$this->map->id}/save", [
            'name' => 'Renamed',
            'rooms' => [['name' => 'Lounge', 'bounds' => ['x' => 2, 'y' => 30, 'width' => 20, 'height' => 25]]],
        ])->assertStatus(422)->assertJsonFragment(['message' => __('These rooms are outside the floor image: :names. Move them inside the map first.', ['names' => 'Lounge'])]);
        $this->assertSame('Map', $this->map->fresh()->name);

        // Non-numeric bounds are refused too.
        $this->postJson("/editor/maps/{$this->map->id}/save", ['rooms' => [['name' => 'X', 'bounds' => ['x' => 'a', 'y' => 1, 'width' => 2, 'height' => 2]]]])->assertStatus(422);

        // Single-room endpoint applies the same rule; a room inside the image is fine.
        $room = fn ($y) => ['organization_id' => $this->org->id, 'map_id' => $this->map->id, 'name' => 'R', 'type' => 'meeting', 'bounds' => ['x' => 2, 'y' => $y, 'width' => 10, 'height' => 10]];
        $this->postJson('/editor/rooms', $room(40))->assertStatus(422);
        $this->postJson('/editor/rooms', $room(5))->assertCreated();
    }

    public function test_room_fits_map_rules(): void
    {
        $layout = ['background_width' => 1200, 'background_height' => 708]; // 75 x 44.25 tiles
        $this->assertTrue(MapGeometry::roomFitsMap(['x' => 0, 'y' => 0, 'width' => 75, 'height' => 45], $layout)); // AI grid, within slack
        $this->assertFalse(MapGeometry::roomFitsMap(['x' => 70, 'y' => 0, 'width' => 10, 'height' => 5], $layout));
        $this->assertFalse(MapGeometry::roomFitsMap(['x' => -1, 'y' => 0, 'width' => 2, 'height' => 2], $layout));
        $this->assertTrue(MapGeometry::roomFitsMap(['x' => 500, 'y' => 500, 'width' => 2, 'height' => 2], [])); // unknown image size
    }

    public function test_repair_command_crops_overhanging_rooms_in_place(): void
    {
        $this->map->forceFill(['tile_size' => 16, 'layout_data' => ['background_width' => 1200, 'background_height' => 708]])->save();
        $hanging = Room::withoutGlobalScopes()->create([
            'organization_id' => $this->org->id, 'map_id' => $this->map->id, 'name' => 'Low', 'type' => 'meeting',
            'access_mode' => 'public', 'capacity' => 4, 'bounds' => ['x' => 2, 'y' => 30, 'width' => 20, 'height' => 25, 'doorSide' => 'top'],
        ]);
        $gone = Room::withoutGlobalScopes()->create([
            'organization_id' => $this->org->id, 'map_id' => $this->map->id, 'name' => 'Off', 'type' => 'meeting',
            'access_mode' => 'public', 'capacity' => 4, 'bounds' => ['x' => 90, 'y' => 5, 'width' => 10, 'height' => 10],
        ]);

        $this->artisan('maps:repair-bounds')->assertSuccessful();
        $this->assertSame(25, $hanging->fresh()->bounds['height'], 'dry run writes nothing');

        $this->artisan('maps:repair-bounds', ['--apply' => true])->assertSuccessful();
        $this->assertEquals(['x' => 2, 'y' => 30, 'width' => 20, 'height' => 14, 'doorSide' => 'top'], $hanging->fresh()->bounds);
        $this->assertEquals(['x' => 65, 'y' => 5, 'width' => 10, 'height' => 10], $gone->fresh()->bounds);
        $this->artisan('maps:repair-bounds')->expectsOutput('All maps already match their floor images.')->assertSuccessful();
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

    public function test_saving_twice_keeps_new_rooms_and_object_settings(): void
    {
        $this->actingAs($this->owner);
        $payload = fn (?string $roomId) => [
            'rooms' => [['id' => $roomId, 'name' => 'New', 'bounds' => ['x' => 2, 'y' => 2, 'width' => 6, 'height' => 6]]],
            'objects' => [['type' => 'rug', 'name' => 'Rug', 'position' => ['x' => 3, 'y' => 3], 'width' => 2, 'height' => 2, 'elevation' => 0, 'interaction_type' => 'drink']],
        ];

        $first = $this->postJson("/editor/maps/{$this->map->id}/save", $payload(null))->assertSuccessful()->json('room_ids');
        $this->assertCount(1, $first);

        // The editor sends the id back on the next save: same room, not a new one.
        $second = $this->postJson("/editor/maps/{$this->map->id}/save", $payload($first[0]))->assertSuccessful()->json('room_ids');
        $this->assertSame($first, $second);
        $this->assertSame(1, Room::withoutGlobalScopes()->where('map_id', $this->map->id)->count());

        $object = $this->map->fresh()->objects()->first();
        $this->assertSame(0, $object->elevation);
        $this->assertSame('drink', $object->interaction_type);
    }
}
