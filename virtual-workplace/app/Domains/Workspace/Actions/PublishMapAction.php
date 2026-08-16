<?php

namespace App\Domains\Workspace\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Workspace\Models\Map;
use App\Domains\Workspace\Models\MapVersion;
use Illuminate\Support\Facades\DB;

class PublishMapAction
{
    public function execute(Map $map, ?User $publishedBy = null): Map
    {
        return DB::transaction(function () use ($map, $publishedBy) {
            $latestVersion = MapVersion::where('map_id', $map->id)->max('version') ?? 0;
            $newVersion = max($map->version, $latestVersion + 1);

            // Snapshot current version into map_versions
            MapVersion::create([
                'map_id' => $map->id,
                'organization_id' => $map->organization_id,
                'published_by' => $publishedBy?->id,
                'version' => $newVersion,
                'layout_snapshot' => [
                    'width' => $map->width,
                    'height' => $map->height,
                    'tile_size' => $map->tile_size,
                    'layout_data' => $map->layout_data,
                    'rooms' => $map->rooms()->get()->toArray(),
                    'zones' => $map->zones()->get()->toArray(),
                    'objects' => $map->objects()->get()->toArray(),
                ],
            ]);

            // Unpublish previous published maps on the same floor if any
            Map::where('floor_id', $map->floor_id)
                ->where('id', '!=', $map->id)
                ->where('status', 'published')
                ->update(['status' => 'draft']);

            // Update map status and increment version for next draft
            $map->update([
                'status' => 'published',
                'version' => $newVersion,
                'published_at' => now(),
            ]);

            return $map->fresh(['floor', 'rooms', 'zones', 'objects']);
        });
    }
}
