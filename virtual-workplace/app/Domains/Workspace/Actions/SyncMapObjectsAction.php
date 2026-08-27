<?php

namespace App\Domains\Workspace\Actions;

use App\Domains\Workspace\Models\Map;
use App\Domains\Workspace\Models\MapObject;
use Illuminate\Support\Facades\DB;

class SyncMapObjectsAction
{
    public function execute(Map $map, array $objectsData): array
    {
        return DB::transaction(function () use ($map, $objectsData) {
            $savedObjects = [];
            $existingIds = [];

            foreach ($objectsData as $item) {
                if (! empty($item['id'])) {
                    $object = MapObject::where('id', $item['id'])
                        ->where('map_id', $map->id)
                        ->first();

                    if ($object) {
                        $object->update([
                            'type' => $item['type'],
                            'name' => $item['name'] ?? null,
                            'position' => $item['position'],
                            'size' => $item['size'] ?? null,
                            'collision' => $item['collision'] ?? false,
                            'interaction_config' => $item['interaction_config'] ?? null,
                        ]);
                        $savedObjects[] = $object;
                        $existingIds[] = $object->id;

                        continue;
                    }
                }

                $newObject = MapObject::create([
                    'map_id' => $map->id,
                    'organization_id' => $map->organization_id,
                    'type' => $item['type'],
                    'name' => $item['name'] ?? null,
                    'position' => $item['position'],
                    'size' => $item['size'] ?? null,
                    'collision' => $item['collision'] ?? false,
                    'interaction_config' => $item['interaction_config'] ?? null,
                ]);

                $savedObjects[] = $newObject;
                $existingIds[] = $newObject->id;
            }

            // Remove any objects from database that were removed in the editor
            MapObject::where('map_id', $map->id)
                ->whereNotIn('id', $existingIds)
                ->delete();

            return $savedObjects;
        });
    }
}
