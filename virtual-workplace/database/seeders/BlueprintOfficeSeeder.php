<?php

namespace Database\Seeders;

use App\Domains\Tenancy\Models\Organization;
use App\Domains\Workspace\Models\Floor;
use App\Domains\Workspace\Models\Map;
use App\Domains\Workspace\Models\MapObject;
use App\Domains\Workspace\Models\OfficeTemplate;
use App\Domains\Workspace\Models\Room;
use Illuminate\Database\Seeder;

class BlueprintOfficeSeeder extends Seeder
{
    /**
     * Run the blueprint seeder to construct the complete virtual office layout.
     */
    public function run(?Organization $organization = null): void
    {
        $organizations = $organization ? collect([$organization]) : Organization::all();

        foreach ($organizations as $org) {
            $this->seedOrganizationOffice($org);
        }
    }

    /**
     * Instance the SuperAdmin Office Template Blueprint for a given organization.
     */
    public function seedOrganizationOffice(Organization $organization): void
    {
        // 1. Get or initialize the SuperAdmin template tailored to organization's plan
        $template = OfficeTemplate::getForPlan($organization->plan);

        // 2. Ensure Floor
        $floor = Floor::where('organization_id', $organization->id)->first();
        if (!$floor) {
            $floor = Floor::create([
                'organization_id' => $organization->id,
                'name' => 'الدور الرئيسي - Main Office Floor',
                'order' => 1,
            ]);
        }

        // 3. Ensure Map
        $map = $organization->maps()->where('floor_id', $floor->id)->first();
        $layoutData = $template->layout_data ?? [
            'theme' => 'open_spatial_blueprint',
            'background_image_url' => $template->background_image_url ?: '/images/office_floorplan.jpg',
            'wall_sign_text' => 'COLLABORATIVE SESSIONS HQ',
            'boardroom_sign' => 'BOARD ROOM - 10 Seats',
        ];

        if (!isset($layoutData['background_image_url'])) {
            $layoutData['background_image_url'] = $template->background_image_url ?: '/images/office_floorplan.jpg';
        }

        if (!$map) {
            $map = Map::create([
                'organization_id' => $organization->id,
                'floor_id' => $floor->id,
                'name' => $template->name ?: 'المكتب الذكي المفتوح - Nanobanaba HQ Blueprint',
                'status' => 'published',
                'version' => 2,
                'width' => $template->width ?: 32,
                'height' => $template->height ?: 26,
                'tile_size' => $template->tile_size ?: 32,
                'layout_data' => $layoutData,
                'published_at' => now(),
            ]);
        } else {
            $map->update([
                'width' => $template->width ?: 32,
                'height' => $template->height ?: 26,
                'tile_size' => $template->tile_size ?: 32,
                'layout_data' => $layoutData,
            ]);
        }

        // 4. Clear any old untextured blue dummy objects
        MapObject::where('map_id', $map->id)
            ->whereNull('image_url')
            ->delete();

        // 5. Seed Rooms from the Template up to organization plan limit
        if ($organization->rooms()->count() === 0) {
            $roomsList = $template->rooms_data ?: [];
            $maxAllowed = ($organization->plan && $organization->plan->room_limit > 0) ? $organization->plan->room_limit : count($roomsList);
            $roomsList = array_slice($roomsList, 0, $maxAllowed);

            foreach ($roomsList as $rData) {
                Room::create([
                    'organization_id' => $organization->id,
                    'map_id' => $map->id,
                    'name' => $rData['name'] ?? 'Meeting Room',
                    'type' => $rData['type'] ?? 'meeting',
                    'access_mode' => $rData['access_mode'] ?? 'public',
                    'capacity' => $rData['capacity'] ?? 8,
                    'color' => $rData['color'] ?? '#3F7D4F',
                    'bounds' => $rData['bounds'] ?? ['x' => 1, 'y' => 1, 'width' => 10, 'height' => 10],
                    'metadata' => $rData['metadata'] ?? ['audio_isolation' => true],
                ]);
            }
        }
    }
}
