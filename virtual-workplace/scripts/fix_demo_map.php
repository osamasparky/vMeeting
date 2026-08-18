<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Domains\Workspace\Models\Map;
use App\Domains\Workspace\Models\MapObject;
use App\Domains\Workspace\Models\FurnitureItem;

$map = Map::with(['rooms', 'objects'])->first();
if (!$map) {
    echo "No map found.\n";
    exit(0);
}

// Fetch 3D catalog items from DB
$catalog = FurnitureItem::all()->keyBy('slug');

// Remove corrupted / legacy unstyled objects
MapObject::where('map_id', $map->id)->delete();

// Place clean 3D furniture matching the rooms:
// Room 1: Boardroom Alpha (x: 2, y: 9, w: 8, h: 6)
// Room 2: Reception Room (x: 9, y: 5, w: 10, h: 3)
// Room 3: Design Studio (x: 16, y: 10, w: 8, h: 6)
// Room 4: Reception Room Lower (x: 2, y: 16, w: 8, h: 6)
// Room 5: Executive Office (x: 16, y: 17, w: 8, h: 6)

$newObjects = [
    // ── Boardroom Alpha ──
    [
        'type' => 'FUR-TBL-MTG-001',
        'name' => 'Boardroom Conference Table',
        'position' => ['x' => 4, 'y' => 11, 'rotation' => 0],
        'size' => ['width' => 4, 'height' => 2],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/meeting/table_meeting_01.png']
    ],
    [
        'type' => 'FUR-CHR-OFF-001',
        'name' => 'Boardroom Chair Top 1',
        'position' => ['x' => 4, 'y' => 10, 'rotation' => 180],
        'size' => ['width' => 1, 'height' => 1],
        'collision' => false,
        'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_01.png']
    ],
    [
        'type' => 'FUR-CHR-OFF-001',
        'name' => 'Boardroom Chair Top 2',
        'position' => ['x' => 6, 'y' => 10, 'rotation' => 180],
        'size' => ['width' => 1, 'height' => 1],
        'collision' => false,
        'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_01.png']
    ],
    [
        'type' => 'FUR-CHR-OFF-001',
        'name' => 'Boardroom Chair Bottom 1',
        'position' => ['x' => 4, 'y' => 13, 'rotation' => 0],
        'size' => ['width' => 1, 'height' => 1],
        'collision' => false,
        'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_01.png']
    ],
    [
        'type' => 'FUR-CHR-OFF-001',
        'name' => 'Boardroom Chair Bottom 2',
        'position' => ['x' => 6, 'y' => 13, 'rotation' => 0],
        'size' => ['width' => 1, 'height' => 1],
        'collision' => false,
        'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_01.png']
    ],
    [
        'type' => 'DEC-PLANT-IND-001',
        'name' => 'Boardroom Monstera Plant',
        'position' => ['x' => 2, 'y' => 14, 'rotation' => 0],
        'size' => ['width' => 1, 'height' => 1],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/decor/plants/plant_indoor_01.png']
    ],

    // ── Reception Room (Top) ──
    [
        'type' => 'FUR-RECP-CTR-001',
        'name' => 'Front Desk Reception Counter',
        'position' => ['x' => 12, 'y' => 5, 'rotation' => 0],
        'size' => ['width' => 4, 'height' => 1],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/furniture/reception/reception_counter_01.png']
    ],
    [
        'type' => 'FUR-LOUNG-SOF-001',
        'name' => 'Reception Waiting Sofa',
        'position' => ['x' => 9, 'y' => 6, 'rotation' => 90],
        'size' => ['width' => 2, 'height' => 1],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/furniture/lounge/lounge_01.png']
    ],

    // ── Design Studio ──
    [
        'type' => 'FUR-DESK-EMP-003',
        'name' => 'Design Dual Workstation',
        'position' => ['x' => 18, 'y' => 11, 'rotation' => 0],
        'size' => ['width' => 3, 'height' => 2],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/furniture/desks/desk_employee_modern_03.png']
    ],
    [
        'type' => 'TEC-OFF-EQP-001',
        'name' => 'Studio Dual Monitor Setup',
        'position' => ['x' => 19, 'y' => 11, 'rotation' => 0],
        'size' => ['width' => 1, 'height' => 1],
        'collision' => false,
        'interaction_config' => ['image_url' => '/assets/technology/tech_dual_monitor_01.png']
    ],
    [
        'type' => 'DEC-PLANT-IND-003',
        'name' => 'Studio Ficus Plant',
        'position' => ['x' => 22, 'y' => 14, 'rotation' => 0],
        'size' => ['width' => 1, 'height' => 1],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/decor/plants/plant_indoor_03.png']
    ],

    // ── Executive Office ──
    [
        'type' => 'FUR-DESK-EXE-001',
        'name' => 'Executive Walnut Desk',
        'position' => ['x' => 18, 'y' => 18, 'rotation' => 0],
        'size' => ['width' => 4, 'height' => 2],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/furniture/desks/desk_manager_executive_01.png']
    ],
    [
        'type' => 'FUR-CHR-OFF-004',
        'name' => 'Executive Leather Chair',
        'position' => ['x' => 19, 'y' => 17, 'rotation' => 180],
        'size' => ['width' => 1, 'height' => 1],
        'collision' => false,
        'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_04.png']
    ],
    [
        'type' => 'FUR-STOR-OFF-001',
        'name' => 'Executive Credenza Cabinet',
        'position' => ['x' => 16, 'y' => 17, 'rotation' => 90],
        'size' => ['width' => 1, 'height' => 3],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/furniture/storage/storage_cabinet_01.png']
    ],

    // ── Lower Lounge / Breakroom ──
    [
        'type' => 'FUR-LOUNG-SOF-003',
        'name' => 'Breakroom Sectional Sofa',
        'position' => ['x' => 4, 'y' => 18, 'rotation' => 0],
        'size' => ['width' => 3, 'height' => 2],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/furniture/lounge/lounge_03.png']
    ],
    [
        'type' => 'FUR-TBL-COF-001',
        'name' => 'Lounge Coffee Table',
        'position' => ['x' => 5, 'y' => 20, 'rotation' => 0],
        'size' => ['width' => 2, 'height' => 1],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/furniture/tables/table_side_01.png']
    ],
];

foreach ($newObjects as $obj) {
    MapObject::create([
        'map_id' => $map->id,
        'organization_id' => $map->organization_id,
        'type' => $obj['type'],
        'name' => $obj['name'],
        'position' => $obj['position'],
        'size' => $obj['size'],
        'collision' => $obj['collision'],
        'interaction_config' => $obj['interaction_config']
    ]);
}

echo "Successfully placed " . count($newObjects) . " clean 3D top-down objects on map!\n";
