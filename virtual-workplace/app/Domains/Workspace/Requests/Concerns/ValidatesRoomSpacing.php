<?php

namespace App\Domains\Workspace\Requests\Concerns;

use App\Domains\Workspace\Models\Map;
use App\Domains\Workspace\Models\Room;
use App\Domains\Workspace\Support\RoomBoundsGap;
use Illuminate\Contracts\Validation\Validator;

/**
 * Rejects a room create/update whose bounds would leave less than
 * RoomBoundsGap::MIN_ROOM_GAP_PX of walkable corridor to a sibling room on
 * the same map — the avatar navigation engine can never route through a
 * gap narrower than that, regardless of how good its pathfinding is.
 */
trait ValidatesRoomSpacing
{
    protected function validateRoomSpacing(Validator $validator, ?string $mapId, ?string $ignoreRoomId, ?array $bounds): void
    {
        if (! $mapId || ! $bounds) {
            return;
        }

        $map = Map::find($mapId);
        $tilePx = $map?->tile_size ?: RoomBoundsGap::CANONICAL_TILE_PX;

        $siblings = Room::where('map_id', $mapId)
            ->when($ignoreRoomId, fn ($q) => $q->where('id', '!=', $ignoreRoomId))
            ->get(['id', 'name', 'bounds']);

        foreach ($siblings as $sibling) {
            if (! RoomBoundsGap::satisfiesMinGap($bounds, $sibling->bounds, $tilePx)) {
                $validator->errors()->add('bounds', __(
                    'This room must be at least :gap tiles away from ":name" to leave a walkable corridor.',
                    ['gap' => RoomBoundsGap::minGapTiles($tilePx), 'name' => $sibling->name]
                ));

                return;
            }
        }
    }
}
