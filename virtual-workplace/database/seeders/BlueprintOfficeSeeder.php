<?php

namespace Database\Seeders;

use App\Domains\Tenancy\Models\Organization;
use App\Domains\Workspace\Models\Floor;
use App\Domains\Workspace\Models\Map;
use App\Domains\Workspace\Models\MapObject;
use App\Domains\Workspace\Models\Room;
use Illuminate\Database\Seeder;

class BlueprintOfficeSeeder extends Seeder
{
    /**
     * Run the blueprint seeder to construct the complete Nanobanaba virtual office layout.
     */
    public function run(?Organization $organization = null): void
    {
        $organizations = $organization ? collect([$organization]) : Organization::all();

        foreach ($organizations as $org) {
            $this->seedOrganizationOffice($org);
        }
    }

    /**
     * Instance the Nanobanaba Blueprint for a given organization.
     */
    public function seedOrganizationOffice(Organization $organization): void
    {
        // 1. Ensure Floor
        $floor = Floor::where('organization_id', $organization->id)->first();
        if (!$floor) {
            $floor = Floor::create([
                'organization_id' => $organization->id,
                'name' => 'الدور الرئيسي - Main Office Floor',
                'order' => 1,
            ]);
        }

        // 2. Ensure Map (32x26 Tiles Grid)
        $map = $organization->maps()->where('floor_id', $floor->id)->first();
        if (!$map) {
            $map = Map::create([
                'organization_id' => $organization->id,
                'floor_id' => $floor->id,
                'name' => 'المكتب الذكي المفتوح - Nanobanaba HQ Blueprint',
                'status' => 'published',
                'version' => 2,
                'width' => 32,
                'height' => 26,
                'tile_size' => 32,
                'layout_data' => [
                    'theme' => 'open_spatial_blueprint',
                    'wall_sign_text' => 'COLLABORATIVE SESSIONS HQ',
                    'boardroom_sign' => 'BOARD ROOM - 10 Seats',
                ],
                'published_at' => now(),
            ]);
        } else {
            $map->update([
                'width' => 32,
                'height' => 26,
                'layout_data' => [
                    'theme' => 'open_spatial_blueprint',
                    'wall_sign_text' => 'COLLABORATIVE SESSIONS HQ',
                    'boardroom_sign' => 'BOARD ROOM - 10 Seats',
                ],
            ]);
        }

        // 3. Clear old objects for this map to refresh blueprint furniture
        MapObject::where('map_id', $map->id)->delete();

        // 4. Seed the 5 Logical Blueprint Rooms if no rooms exist
        if ($organization->rooms()->count() === 0) {
            // Zone A: Board Room (10 Seats) - Top Right
            Room::create([
                'organization_id' => $organization->id,
                'map_id' => $map->id,
                'name' => 'قاعة مجلس الإدارة - Board Room (10 Seats)',
                'type' => 'meeting',
                'access_mode' => 'public',
                'capacity' => 10,
                'color' => '#3F7D4F',
                'bounds' => ['x' => 17, 'y' => 1, 'width' => 14, 'height' => 11],
            ]);

            // Zone B: Collaborative Sessions HQ (Lounge) - Top Left
            Room::create([
                'organization_id' => $organization->id,
                'map_id' => $map->id,
                'name' => 'ركن العمل الجماعي - Collaborative Sessions HQ',
                'type' => 'meeting',
                'access_mode' => 'public',
                'capacity' => 12,
                'color' => '#245C3A',
                'bounds' => ['x' => 1, 'y' => 1, 'width' => 15, 'height' => 11],
            ]);

            // Zone C: Private Focus Pods - Foreground Left
            Room::create([
                'organization_id' => $organization->id,
                'map_id' => $map->id,
                'name' => 'محطات العمل الفردية - Private Focus Pods',
                'type' => 'private',
                'access_mode' => 'public',
                'capacity' => 4,
                'color' => '#4F9B5F',
                'bounds' => ['x' => 1, 'y' => 13, 'width' => 15, 'height' => 12],
            ]);

            // Zone D: Maker & Tech Hub - Middle Center
            Room::create([
                'organization_id' => $organization->id,
                'map_id' => $map->id,
                'name' => 'الركن التكنولوجي - Maker / Tech Workbenches',
                'type' => 'meeting',
                'access_mode' => 'public',
                'capacity' => 6,
                'color' => '#D6A23A',
                'bounds' => ['x' => 17, 'y' => 13, 'width' => 14, 'height' => 6],
            ]);

            // Zone E: Reception & Living Wall Lounge - Foreground Right
            Room::create([
                'organization_id' => $organization->id,
                'map_id' => $map->id,
                'name' => 'الاستقبال والجدار النباتي - Reception & Botanical Lounge',
                'type' => 'reception',
                'access_mode' => 'public',
                'capacity' => 8,
                'color' => '#4F9B5F',
                'bounds' => ['x' => 17, 'y' => 20, 'width' => 14, 'height' => 5],
            ]);
        }

        // 5. Seed Discrete Manipulatable Furniture Assets (Nanobanaba Framework)
        $objects = [
            // ════════════════════════════════════════════════════════════
            // 🌟 ZONE 1: BOARD ROOM (CONFERENCE HALL - 10 SEATS)
            // ════════════════════════════════════════════════════════════
            // Long Oak Conference Table
            [
                'name' => 'Oak Boardroom Table',
                'type' => 'conference_table',
                'x' => 20, 'y' => 4, 'w' => 8, 'h' => 3, 'rot' => 0,
                'color' => '#D8B589',
                'interaction_config' => ['material' => 'light_oak', 'seats' => 10],
            ],
            // 10 White Executive Chairs
            ['name' => 'White Executive Chair 1', 'type' => 'chair_white', 'x' => 21, 'y' => 3, 'w' => 1, 'h' => 1, 'rot' => 180, 'color' => '#FFFFFF'],
            ['name' => 'White Executive Chair 2', 'type' => 'chair_white', 'x' => 23, 'y' => 3, 'w' => 1, 'h' => 1, 'rot' => 180, 'color' => '#FFFFFF'],
            ['name' => 'White Executive Chair 3', 'type' => 'chair_white', 'x' => 25, 'y' => 3, 'w' => 1, 'h' => 1, 'rot' => 180, 'color' => '#FFFFFF'],
            ['name' => 'White Executive Chair 4', 'type' => 'chair_white', 'x' => 27, 'y' => 3, 'w' => 1, 'h' => 1, 'rot' => 180, 'color' => '#FFFFFF'],
            ['name' => 'White Executive Chair 5', 'type' => 'chair_white', 'x' => 21, 'y' => 7, 'w' => 1, 'h' => 1, 'rot' => 0, 'color' => '#FFFFFF'],
            ['name' => 'White Executive Chair 6', 'type' => 'chair_white', 'x' => 23, 'y' => 7, 'w' => 1, 'h' => 1, 'rot' => 0, 'color' => '#FFFFFF'],
            ['name' => 'White Executive Chair 7', 'type' => 'chair_white', 'x' => 25, 'y' => 7, 'w' => 1, 'h' => 1, 'rot' => 0, 'color' => '#FFFFFF'],
            ['name' => 'White Executive Chair 8', 'type' => 'chair_white', 'x' => 27, 'y' => 7, 'w' => 1, 'h' => 1, 'rot' => 0, 'color' => '#FFFFFF'],
            ['name' => 'White Executive Chair 9 (Head)', 'type' => 'chair_white', 'x' => 19, 'y' => 5, 'w' => 1, 'h' => 1, 'rot' => 90, 'color' => '#FFFFFF'],
            ['name' => 'White Executive Chair 10 (Head)', 'type' => 'chair_white', 'x' => 28, 'y' => 5, 'w' => 1, 'h' => 1, 'rot' => 270, 'color' => '#FFFFFF'],

            // Strategy Whiteboards & Wall Screens
            [
                'name' => 'Strategy Planning Board',
                'type' => 'whiteboard_strategy',
                'x' => 24, 'y' => 1, 'w' => 4, 'h' => 1, 'rot' => 0,
                'color' => '#FFFFFF',
                'interaction_config' => ['title' => 'STRATEGY PLANNING', 'diagram' => 'flowchart'],
            ],
            [
                'name' => 'Presentation Wall Display',
                'type' => 'presentation_screen',
                'x' => 18, 'y' => 1, 'w' => 3, 'h' => 1, 'rot' => 0,
                'color' => '#1E293B',
            ],
            // Glass Wall Sign
            [
                'name' => 'Board Room Glass Label',
                'type' => 'wall_text',
                'x' => 19, 'y' => 10, 'w' => 5, 'h' => 1, 'rot' => 0,
                'color' => '#3F7D4F',
                'interaction_config' => ['text' => 'BOARD ROOM - 10 Seats'],
            ],
            // Potted Botanicals in Conference
            ['name' => 'Monstera Pot Corner', 'type' => 'plant_monstera', 'x' => 29, 'y' => 2, 'w' => 1, 'h' => 1, 'rot' => 0, 'color' => '#4F9B5F'],
            ['name' => 'Sansevieria Snake Plant', 'type' => 'plant_snake', 'x' => 30, 'y' => 9, 'w' => 1, 'h' => 1, 'rot' => 0, 'color' => '#3F7D4F'],

            // ════════════════════════════════════════════════════════════
            // 🌟 ZONE 2: COLLABORATIVE SESSIONS HQ (LOUNGE & STAIRS)
            // ════════════════════════════════════════════════════════════
            // Feature Wood Wall & 3D Editable Sign
            [
                'name' => 'Wood Feature Wall Panel',
                'type' => 'wood_panel_wall',
                'x' => 4, 'y' => 1, 'w' => 7, 'h' => 1, 'rot' => 0,
                'color' => '#C49A6C',
                'interaction_config' => ['text' => 'COLLABORATIVE SESSIONS HQ'],
            ],
            // Wooden Staircase to 2nd Floor
            [
                'name' => 'Upper Floor Wooden Stairs',
                'type' => 'stairs_wood',
                'x' => 12, 'y' => 1, 'w' => 3, 'h' => 4, 'rot' => 0,
                'color' => '#C49A6C',
            ],
            // Cream 3-Seater Sofa
            [
                'name' => 'Cream Lounge Sofa',
                'type' => 'sofa_cream',
                'x' => 5, 'y' => 3, 'w' => 3, 'h' => 2, 'rot' => 0,
                'color' => '#F4EFE6',
            ],
            // Sage Green Armchairs
            ['name' => 'Sage Green Armchair 1', 'type' => 'armchair_sage', 'x' => 9, 'y' => 3, 'w' => 2, 'h' => 2, 'rot' => 270, 'color' => '#8BA888'],
            ['name' => 'Sage Green Armchair 2', 'type' => 'armchair_sage', 'x' => 7, 'y' => 7, 'w' => 2, 'h' => 2, 'rot' => 0, 'color' => '#8BA888'],
            // Light Oak Coffee Table
            [
                'name' => 'Oak Coffee Table',
                'type' => 'coffee_table_oak',
                'x' => 6, 'y' => 5, 'w' => 2, 'h' => 1, 'rot' => 0,
                'color' => '#D8B589',
            ],
            // Left Wall Sprint Whiteboard
            [
                'name' => 'Collab Brainstorm Whiteboard',
                'type' => 'whiteboard_sprint',
                'x' => 1, 'y' => 4, 'w' => 1, 'h' => 4, 'rot' => 90,
                'color' => '#FFFFFF',
                'interaction_config' => ['title' => 'SPRINT IDEATION'],
            ],
            // Potted Trees
            ['name' => 'Fiddle Leaf Fig Tree', 'type' => 'plant_ficus', 'x' => 3, 'y' => 3, 'w' => 1, 'h' => 1, 'rot' => 0, 'color' => '#4F9B5F'],
            ['name' => 'Stairs Ficus Plant', 'type' => 'plant_ficus', 'x' => 11, 'y' => 3, 'w' => 1, 'h' => 1, 'rot' => 0, 'color' => '#4F9B5F'],

            // ════════════════════════════════════════════════════════════
            // 🌟 ZONE 3: MAKER & TECH HUB (WORKBENCHES & 3D PRINTERS)
            // ════════════════════════════════════════════════════════════
            // Team Bench Desks
            [
                'name' => 'Tech Team Workbench 1',
                'type' => 'tech_workbench',
                'x' => 19, 'y' => 14, 'w' => 4, 'h' => 2, 'rot' => 0,
                'color' => '#D8B589',
                'interaction_config' => ['equipment' => 'laptops_screens'],
            ],
            [
                'name' => 'Tech Team Workbench 2',
                'type' => 'tech_workbench',
                'x' => 24, 'y' => 14, 'w' => 4, 'h' => 2, 'rot' => 0,
                'color' => '#D8B589',
                'interaction_config' => ['equipment' => '3d_printers'],
            ],
            // White Chairs at Workbenches
            ['name' => 'Workbench Chair 1', 'type' => 'chair_white', 'x' => 20, 'y' => 13, 'w' => 1, 'h' => 1, 'rot' => 180, 'color' => '#FFFFFF'],
            ['name' => 'Workbench Chair 2', 'type' => 'chair_white', 'x' => 22, 'y' => 13, 'w' => 1, 'h' => 1, 'rot' => 180, 'color' => '#FFFFFF'],
            ['name' => 'Workbench Chair 3', 'type' => 'chair_white', 'x' => 25, 'y' => 16, 'w' => 1, 'h' => 1, 'rot' => 0, 'color' => '#FFFFFF'],
            ['name' => 'Workbench Chair 4', 'type' => 'chair_white', 'x' => 27, 'y' => 16, 'w' => 1, 'h' => 1, 'rot' => 0, 'color' => '#FFFFFF'],

            // Wooden Storage Shelf & Sticky Notes Stand
            [
                'name' => 'Oak Library Bookshelf',
                'type' => 'shelf_wood',
                'x' => 12, 'y' => 10, 'w' => 3, 'h' => 2, 'rot' => 0,
                'color' => '#C49A6C',
            ],
            [
                'name' => 'Sticky Notes Updates Stand',
                'type' => 'mobile_board_notes',
                'x' => 14, 'y' => 9, 'w' => 1, 'h' => 2, 'rot' => 90,
                'color' => '#FFFFFF',
                'interaction_config' => ['title' => 'UPDATES'],
            ],

            // ════════════════════════════════════════════════════════════
            // 🌟 ZONE 4: PRIVATE FOCUS DESK PODS (4 WORKSTATIONS)
            // ════════════════════════════════════════════════════════════
            // Pod 1 (Top Left Pod)
            [
                'name' => 'Focus Pod 1 (Desk & Dual Monitor)',
                'type' => 'pod_workstation',
                'x' => 2, 'y' => 14, 'w' => 3, 'h' => 2, 'rot' => 0,
                'color' => '#D8B589',
                'interaction_config' => ['station_number' => 1],
            ],
            ['name' => 'Pod 1 Chair', 'type' => 'chair_white', 'x' => 3, 'y' => 16, 'w' => 1, 'h' => 1, 'rot' => 0, 'color' => '#FFFFFF'],
            ['name' => 'Pod 1 Glass Partition', 'type' => 'glass_partition', 'x' => 1, 'y' => 14, 'w' => 5, 'h' => 4, 'rot' => 0, 'color' => 'rgba(255,255,255,0.4)'],

            // Pod 2 (Top Right Pod)
            [
                'name' => 'Focus Pod 2 (Desk & Dual Monitor)',
                'type' => 'pod_workstation',
                'x' => 7, 'y' => 14, 'w' => 3, 'h' => 2, 'rot' => 0,
                'color' => '#D8B589',
                'interaction_config' => ['station_number' => 2],
            ],
            ['name' => 'Pod 2 Chair', 'type' => 'chair_white', 'x' => 8, 'y' => 16, 'w' => 1, 'h' => 1, 'rot' => 0, 'color' => '#FFFFFF'],
            ['name' => 'Pod 2 Glass Partition', 'type' => 'glass_partition', 'x' => 6, 'y' => 14, 'w' => 5, 'h' => 4, 'rot' => 0, 'color' => 'rgba(255,255,255,0.4)'],

            // Pod 3 (Bottom Left Pod)
            [
                'name' => 'Focus Pod 3 (Desk & Dual Monitor)',
                'type' => 'pod_workstation',
                'x' => 2, 'y' => 19, 'w' => 3, 'h' => 2, 'rot' => 0,
                'color' => '#D8B589',
                'interaction_config' => ['station_number' => 3],
            ],
            ['name' => 'Pod 3 Chair', 'type' => 'chair_white', 'x' => 3, 'y' => 21, 'w' => 1, 'h' => 1, 'rot' => 0, 'color' => '#FFFFFF'],
            ['name' => 'Pod 3 Glass Partition', 'type' => 'glass_partition', 'x' => 1, 'y' => 19, 'w' => 5, 'h' => 4, 'rot' => 0, 'color' => 'rgba(255,255,255,0.4)'],

            // Pod 4 (Bottom Right Pod)
            [
                'name' => 'Focus Pod 4 (Desk & Dual Monitor)',
                'type' => 'pod_workstation',
                'x' => 7, 'y' => 19, 'w' => 3, 'h' => 2, 'rot' => 0,
                'color' => '#D8B589',
                'interaction_config' => ['station_number' => 4],
            ],
            ['name' => 'Pod 4 Chair', 'type' => 'chair_white', 'x' => 8, 'y' => 21, 'w' => 1, 'h' => 1, 'rot' => 0, 'color' => '#FFFFFF'],
            ['name' => 'Pod 4 Glass Partition', 'type' => 'glass_partition', 'x' => 6, 'y' => 19, 'w' => 5, 'h' => 4, 'rot' => 0, 'color' => 'rgba(255,255,255,0.4)'],

            // Plants between Pods
            ['name' => 'Pod Corridor Monstera', 'type' => 'plant_monstera', 'x' => 11, 'y' => 17, 'w' => 1, 'h' => 1, 'rot' => 0, 'color' => '#4F9B5F'],
            ['name' => 'Corner Pod Planter', 'type' => 'plant_snake', 'x' => 11, 'y' => 22, 'w' => 1, 'h' => 1, 'rot' => 0, 'color' => '#3F7D4F'],

            // ════════════════════════════════════════════════════════════
            // 🌟 ZONE 5: RECEPTION & LUSH LIVING PLANT WALL
            // ════════════════════════════════════════════════════════════
            // The Botanical Living Wall (الجدار الحي النباتي)
            [
                'name' => 'Lush Vertical Living Plant Wall',
                'type' => 'living_wall',
                'x' => 18, 'y' => 19, 'w' => 5, 'h' => 2, 'rot' => 0,
                'color' => '#2D6A4F',
                'interaction_config' => ['foliage_density' => 'dense', 'lighting' => 'ambient_warm'],
            ],
            // White & Oak Reception Desk ("WELCOME")
            [
                'name' => 'Modern Reception Counter',
                'type' => 'reception_counter',
                'x' => 20, 'y' => 21, 'w' => 4, 'h' => 2, 'rot' => 0,
                'color' => '#F4EFE6',
                'interaction_config' => ['sign' => 'R WELCOME', 'has_receptionist' => true],
            ],
            ['name' => 'Receptionist Ergonomic Chair', 'type' => 'chair_white', 'x' => 21, 'y' => 20, 'w' => 1, 'h' => 1, 'rot' => 180, 'color' => '#FFFFFF'],

            // Secondary Guest Waiting Lounge (Right Corner)
            [
                'name' => 'Lobby Cream Sofa',
                'type' => 'sofa_cream',
                'x' => 26, 'y' => 21, 'w' => 3, 'h' => 2, 'rot' => 0,
                'color' => '#F4EFE6',
            ],
            ['name' => 'Lobby Sage Armchair', 'type' => 'armchair_sage', 'x' => 29, 'y' => 22, 'w' => 2, 'h' => 2, 'rot' => 270, 'color' => '#8BA888'],
            ['name' => 'Lobby Wood Coffee Table', 'type' => 'coffee_table_oak', 'x' => 27, 'y' => 23, 'w' => 2, 'h' => 1, 'rot' => 0, 'color' => '#D8B589'],
            ['name' => 'Lobby Sunlit Window Ficus', 'type' => 'plant_ficus', 'x' => 30, 'y' => 20, 'w' => 1, 'h' => 1, 'rot' => 0, 'color' => '#4F9B5F'],
        ];

        foreach ($objects as $objData) {
            MapObject::create([
                'organization_id' => $organization->id,
                'map_id' => $map->id,
                'name' => $objData['name'],
                'type' => $objData['type'],
                'position' => [
                    'x' => $objData['x'],
                    'y' => $objData['y'],
                    'rotation' => $objData['rot'],
                ],
                'size' => [
                    'width' => $objData['w'],
                    'height' => $objData['h'],
                ],
                'collision' => true,
                'interaction_config' => array_merge($objData['interaction_config'] ?? [], [
                    'color' => $objData['color'] ?? '#3b82f6',
                ]),
            ]);
        }
    }
}
