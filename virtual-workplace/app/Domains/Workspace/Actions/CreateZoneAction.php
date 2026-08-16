<?php

namespace App\Domains\Workspace\Actions;

use App\Domains\Tenancy\Models\Organization;
use App\Domains\Workspace\Models\Zone;
use Illuminate\Support\Facades\DB;

class CreateZoneAction
{
    public function execute(array $data, Organization $organization): Zone
    {
        return DB::transaction(function () use ($data, $organization) {
            return Zone::create([
                'organization_id' => $organization->id,
                'map_id' => $data['map_id'],
                'name' => $data['name'],
                'type' => $data['type'] ?? 'audio',
                'shape_type' => $data['shape_type'] ?? 'rectangle',
                'shape_data' => $data['shape_data'],
                'audible_radius' => $data['audible_radius'] ?? 150.0,
                'metadata' => $data['metadata'] ?? null,
            ]);
        });
    }
}
