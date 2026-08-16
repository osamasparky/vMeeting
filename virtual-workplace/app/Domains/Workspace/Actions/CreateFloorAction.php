<?php

namespace App\Domains\Workspace\Actions;

use App\Domains\Tenancy\Models\Organization;
use App\Domains\Workspace\Models\Floor;
use Illuminate\Support\Facades\DB;

class CreateFloorAction
{
    public function execute(array $data, Organization $organization): Floor
    {
        return DB::transaction(function () use ($data, $organization) {
            $nextOrder = $data['order'] ?? ($organization->floors()->max('order') + 1);

            return Floor::create([
                'organization_id' => $organization->id,
                'name' => $data['name'],
                'order' => $nextOrder,
            ]);
        });
    }
}
