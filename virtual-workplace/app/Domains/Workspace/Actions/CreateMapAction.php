<?php

namespace App\Domains\Workspace\Actions;

use App\Domains\Tenancy\Models\Organization;
use App\Domains\Workspace\Models\Map;
use Illuminate\Support\Facades\DB;

class CreateMapAction
{
    public function execute(array $data, Organization $organization): Map
    {
        return DB::transaction(function () use ($data, $organization) {
            $defaultLayout = $data['layout_data'] ?? [
                'ground' => array_fill(0, $data['height'] ?? 30, array_fill(0, $data['width'] ?? 40, 1)),
                'walls' => [],
                'furniture' => [],
            ];

            return Map::create([
                'organization_id' => $organization->id,
                'floor_id' => $data['floor_id'],
                'name' => $data['name'],
                'status' => 'draft',
                'version' => 1,
                'width' => $data['width'] ?? 40,
                'height' => $data['height'] ?? 30,
                'tile_size' => $data['tile_size'] ?? 32,
                'layout_data' => $defaultLayout,
            ]);
        });
    }
}
