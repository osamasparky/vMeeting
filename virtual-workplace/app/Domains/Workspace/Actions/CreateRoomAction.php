<?php

namespace App\Domains\Workspace\Actions;

use App\Domains\Tenancy\Models\Organization;
use App\Domains\Workspace\Models\Room;
use Illuminate\Support\Facades\DB;

class CreateRoomAction
{
    public function execute(array $data, Organization $organization): Room
    {
        return DB::transaction(function () use ($data, $organization) {
            return Room::create([
                'organization_id' => $organization->id,
                'map_id' => $data['map_id'],
                'name' => $data['name'],
                'type' => $data['type'] ?? 'meeting',
                'access_mode' => $data['access_mode'] ?? 'public',
                'capacity' => $data['capacity'] ?? 10,
                'color' => $data['color'] ?? '#3B82F6',
                'bounds' => $data['bounds'],
                'metadata' => $data['metadata'] ?? null,
            ]);
        });
    }
}
