<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Domains\Workspace\Models\Map;
use App\Domains\Workspace\Models\Room;
use App\Domains\Workspace\Models\Zone;
use App\Domains\Workspace\Models\MapObject;
use App\Domains\Workspace\Models\FurnitureItem;
use App\Domains\Tenancy\Models\Organization;
use Illuminate\Support\Facades\Cache;

$orgs = Organization::all();

foreach ($orgs as $org) {
    $floor = $org->floors()->firstOrCreate(
        ['name' => 'الدور الرئيسي - Main Floor'],
        ['order' => 1]
    );

    // Get or create published map (32 x 32 tiles)
    $map = $org->maps()->where('floor_id', $floor->id)->first();
    if (!$map) {
        $map = Map::create([
            'organization_id' => $org->id,
            'floor_id' => $floor->id,
            'name' => 'مكتب ميم الافتراضي - Meem Default Office',
            'status' => 'published',
            'version' => 1,
            'width' => 32,
            'height' => 32,
            'tile_size' => 32,
            'layout_data' => ['theme' => 'modern_dark'],
            'published_at' => now(),
        ]);
    } else {
        $map->update([
            'name' => 'مكتب ميم الافتراضي - Meem Default Office',
            'width' => 32,
            'height' => 32,
            'tile_size' => 32,
            'status' => 'published',
        ]);
    }

    // Clean old rooms, zones, objects for a fresh pixel-perfect Meem Office layout
    Room::where('map_id', $map->id)->delete();
    Zone::where('map_id', $map->id)->delete();
    MapObject::where('map_id', $map->id)->delete();

    // ══════════════════════════════════════════════════════════════
    // 1. CREATE ROOMS (Multi-Zone Meem Office Layout)
    // ══════════════════════════════════════════════════════════════
    
    // Room 1: Private Office A (Top Left: x=1, y=1, w=5, h=7)
    $roomPrivateA = Room::create([
        'organization_id' => $org->id,
        'map_id' => $map->id,
        'name' => 'مكتب خاص 1 - Private Office 1',
        'type' => 'private',
        'access_mode' => 'private',
        'capacity' => 2,
        'color' => '#3b82f6',
        'bounds' => ['x' => 1, 'y' => 1, 'width' => 5, 'height' => 7],
    ]);

    // Room 2: Private Office B (Top Left-Center: x=6, y=1, w=5, h=7)
    $roomPrivateB = Room::create([
        'organization_id' => $org->id,
        'map_id' => $map->id,
        'name' => 'مكتب خاص 2 - Private Office 2',
        'type' => 'private',
        'access_mode' => 'private',
        'capacity' => 2,
        'color' => '#3b82f6',
        'bounds' => ['x' => 6, 'y' => 1, 'width' => 5, 'height' => 7],
    ]);

    // Room 3: Glass Round Pod (Top Center: x=12, y=1, w=7, h=9)
    $roomGlassPod = Room::create([
        'organization_id' => $org->id,
        'map_id' => $map->id,
        'name' => 'كبسولة اجتماعات زجاجية - Glass Pod',
        'type' => 'meeting',
        'access_mode' => 'public',
        'capacity' => 6,
        'color' => '#06b6d4',
        'bounds' => ['x' => 12, 'y' => 1, 'width' => 7, 'height' => 9],
    ]);

    // Room 4: Presentation Stage & Auditorium (Top Right: x=20, y=1, w=11, h=19)
    $roomAuditorium = Room::create([
        'organization_id' => $org->id,
        'map_id' => $map->id,
        'name' => 'المسرح وقاعة العرض - Auditorium & Stage',
        'type' => 'meeting',
        'access_mode' => 'public',
        'capacity' => 25,
        'color' => '#8b5cf6',
        'bounds' => ['x' => 20, 'y' => 1, 'width' => 11, 'height' => 19],
    ]);

    // Room 5: Team Collaboration Studio (Middle Left: x=1, y=9, w=10, h=8)
    $roomCollab = Room::create([
        'organization_id' => $org->id,
        'map_id' => $map->id,
        'name' => 'استوديو العمل الجماعي - Collab Studio',
        'type' => 'meeting',
        'access_mode' => 'public',
        'capacity' => 8,
        'color' => '#10b981',
        'bounds' => ['x' => 1, 'y' => 9, 'width' => 10, 'height' => 8],
    ]);

    // Room 6: Huddle Meeting Room (Middle Left Lower: x=6, y=17, w=5, h=6)
    $roomHuddle = Room::create([
        'organization_id' => $org->id,
        'map_id' => $map->id,
        'name' => 'غرفة هادل السريعة - Huddle Room',
        'type' => 'meeting',
        'access_mode' => 'public',
        'capacity' => 4,
        'color' => '#f59e0b',
        'bounds' => ['x' => 6, 'y' => 17, 'width' => 5, 'height' => 6],
    ]);

    // Room 7: Welcome Reception & Lounge (Bottom Left: x=1, y=23, w=11, h=8)
    $roomReception = Room::create([
        'organization_id' => $org->id,
        'map_id' => $map->id,
        'name' => 'استقبال ولوبي ميم - Meem Welcome Lounge',
        'type' => 'reception',
        'access_mode' => 'public',
        'capacity' => 10,
        'color' => '#0d9488',
        'bounds' => ['x' => 1, 'y' => 23, 'width' => 11, 'height' => 8],
    ]);

    // Room 8: Executive Boardroom (Bottom Right: x=20, y=21, w=11, h=10)
    $roomBoardroom = Room::create([
        'organization_id' => $org->id,
        'map_id' => $map->id,
        'name' => 'قاعة مجلس الإدارة - Executive Boardroom',
        'type' => 'meeting',
        'access_mode' => 'public',
        'capacity' => 10,
        'color' => '#3b82f6',
        'bounds' => ['x' => 20, 'y' => 21, 'width' => 11, 'height' => 10],
    ]);

    // ══════════════════════════════════════════════════════════════
    // 2. AUDIO ZONES
    // ══════════════════════════════════════════════════════════════
    Zone::create([
        'organization_id' => $org->id,
        'map_id' => $map->id,
        'name' => 'منطقة بث المسرح - Auditorium Broadcast Zone',
        'type' => 'audio',
        'shape_type' => 'rectangle',
        'shape_data' => ['x' => 20, 'y' => 1, 'width' => 11, 'height' => 19],
        'audible_radius' => 260,
    ]);

    Zone::create([
        'organization_id' => $org->id,
        'map_id' => $map->id,
        'name' => 'منطقة اللوبي التفاعلية - Lounge Social Zone',
        'type' => 'audio',
        'shape_type' => 'rectangle',
        'shape_data' => ['x' => 1, 'y' => 23, 'width' => 11, 'height' => 8],
        'audible_radius' => 180,
    ]);

    // ══════════════════════════════════════════════════════════════
    // 3. 3D TOP-VIEW FURNITURE & OBJECT PLACEMENT
    // ══════════════════════════════════════════════════════════════
    $objects = [
        // ── TOP LEFT: Private Office 1 (x:1, y:1, w:5, h:7) ──
        [
            'type' => 'FUR-DESK-EMP-001',
            'name' => 'مكتب تنفيذي 1',
            'position' => ['x' => 2, 'y' => 2, 'rotation' => 0],
            'size' => ['width' => 3, 'height' => 2],
            'collision' => true,
            'interaction_config' => ['image_url' => '/assets/furniture/desks/desk_employee_modern_01.png']
        ],
        [
            'type' => 'TEC-OFF-EQP-002',
            'name' => 'لابتوب مكتب 1',
            'position' => ['x' => 2, 'y' => 2, 'rotation' => 0],
            'size' => ['width' => 1, 'height' => 1],
            'collision' => false,
            'interaction_config' => ['image_url' => '/assets/technology/tech_laptop_02.png']
        ],
        [
            'type' => 'FUR-CHR-OFF-001',
            'name' => 'كرسي مريح 1',
            'position' => ['x' => 3, 'y' => 4, 'rotation' => 0],
            'size' => ['width' => 1, 'height' => 1],
            'collision' => false,
            'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_01.png']
        ],
        [
            'type' => 'FUR-STOR-OFF-001',
            'name' => 'وحدة أدراج وكتب 1',
            'position' => ['x' => 5, 'y' => 1, 'rotation' => 0],
            'size' => ['width' => 1, 'height' => 2],
            'collision' => true,
            'interaction_config' => ['image_url' => '/assets/furniture/storage/storage_cabinet_01.png']
        ],

        // ── TOP LEFT-CENTER: Private Office 2 (x:6, y:1, w:5, h:7) ──
        [
            'type' => 'FUR-DESK-EMP-002',
            'name' => 'مكتب تنفيذي 2',
            'position' => ['x' => 7, 'y' => 2, 'rotation' => 0],
            'size' => ['width' => 3, 'height' => 2],
            'collision' => true,
            'interaction_config' => ['image_url' => '/assets/furniture/desks/desk_employee_modern_02.png']
        ],
        [
            'type' => 'TEC-OFF-EQP-001',
            'name' => 'شاشات مزدوجة 2',
            'position' => ['x' => 8, 'y' => 2, 'rotation' => 0],
            'size' => ['width' => 1, 'height' => 1],
            'collision' => false,
            'interaction_config' => ['image_url' => '/assets/technology/tech_dual_monitor_01.png']
        ],
        [
            'type' => 'FUR-CHR-OFF-002',
            'name' => 'كرسي مكتبي جلد 2',
            'position' => ['x' => 8, 'y' => 4, 'rotation' => 0],
            'size' => ['width' => 1, 'height' => 1],
            'collision' => false,
            'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_02.png']
        ],
        [
            'type' => 'DEC-PLANT-IND-001',
            'name' => 'نبتة زينة 2',
            'position' => ['x' => 10, 'y' => 2, 'rotation' => 0],
            'size' => ['width' => 1, 'height' => 1],
            'collision' => true,
            'interaction_config' => ['image_url' => '/assets/decor/plants/plant_indoor_01.png']
        ],

        // ── TOP CENTER: Glass Meeting Pod (x:12, y:1, w:7, h:9) ──
        [
            'type' => 'FUR-TBL-COF-001',
            'name' => 'طاولة كبسولة الاجتماعات',
            'position' => ['x' => 14, 'y' => 4, 'rotation' => 0],
            'size' => ['width' => 3, 'height' => 3],
            'collision' => true,
            'interaction_config' => ['image_url' => '/assets/furniture/tables/table_side_01.png']
        ],
        [
            'type' => 'FUR-CHR-OFF-003',
            'name' => 'كرسي اجتماعات شمال',
            'position' => ['x' => 15, 'y' => 2, 'rotation' => 180],
            'size' => ['width' => 1, 'height' => 1],
            'collision' => false,
            'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_03.png']
        ],
        [
            'type' => 'FUR-CHR-OFF-003',
            'name' => 'كرسي اجتماعات جنوب',
            'position' => ['x' => 15, 'y' => 7, 'rotation' => 0],
            'size' => ['width' => 1, 'height' => 1],
            'collision' => false,
            'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_03.png']
        ],
        [
            'type' => 'FUR-CHR-OFF-003',
            'name' => 'كرسي اجتماعات غرب',
            'position' => ['x' => 13, 'y' => 4, 'rotation' => 90],
            'size' => ['width' => 1, 'height' => 1],
            'collision' => false,
            'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_03.png']
        ],
        [
            'type' => 'FUR-CHR-OFF-003',
            'name' => 'كرسي اجتماعات شرق',
            'position' => ['x' => 17, 'y' => 4, 'rotation' => 270],
            'size' => ['width' => 1, 'height' => 1],
            'collision' => false,
            'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_03.png']
        ],
        [
            'type' => 'DEC-PLANT-IND-002',
            'name' => 'شجرة ديكور الكبسولة',
            'position' => ['x' => 17, 'y' => 2, 'rotation' => 0],
            'size' => ['width' => 1, 'height' => 1],
            'collision' => true,
            'interaction_config' => ['image_url' => '/assets/decor/plants/plant_indoor_02.png']
        ],

        // ── TOP RIGHT: Presentation Stage & Auditorium (x:20, y:1, w:11, h:19) ──
        [
            'type' => 'FUR-DESK-EXE-001',
            'name' => 'منصة المسرح الخشبية',
            'position' => ['x' => 22, 'y' => 3, 'rotation' => 0],
            'size' => ['width' => 7, 'height' => 4],
            'collision' => true,
            'interaction_config' => ['image_url' => '/assets/furniture/desks/desk_manager_executive_01.png']
        ],
        [
            'type' => 'BRD-MTG-WBD-001',
            'name' => 'شاشة وسبورة العرض التفاعلية',
            'position' => ['x' => 23, 'y' => 2, 'rotation' => 0],
            'size' => ['width' => 4, 'height' => 1],
            'collision' => true,
            'interaction_config' => ['image_url' => '/assets/meeting/board_office_01.png']
        ],
        [
            'type' => 'DEC-PLANT-IND-003',
            'name' => 'نبتة زاوية المسرح',
            'position' => ['x' => 29, 'y' => 2, 'rotation' => 0],
            'size' => ['width' => 1, 'height' => 1],
            'collision' => true,
            'interaction_config' => ['image_url' => '/assets/decor/plants/plant_indoor_03.png']
        ],
    ];

    // Audience Seating Grid (4 rows x 4 columns = 16 chairs)
    $chairXPositions = [22, 24, 26, 28];
    $chairYPositions = [10, 12, 14, 16];
    $chairIndex = 1;
    foreach ($chairYPositions as $row => $cy) {
        foreach ($chairXPositions as $col => $cx) {
            $objects[] = [
                'type' => 'FUR-CHR-OFF-004',
                'name' => "مقعد جمهور #{$chairIndex}",
                'position' => ['x' => $cx, 'y' => $cy, 'rotation' => 180],
                'size' => ['width' => 1, 'height' => 1],
                'collision' => false,
                'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_04.png']
            ];
            $chairIndex++;
        }
    }

    // ── MIDDLE LEFT: Team Collaboration Workstation (x:1, y:9, w:10, h:8) ──
    $objects[] = [
        'type' => 'FUR-TBL-MTG-002',
        'name' => 'طاولة العمل الجماعي الطويلة',
        'position' => ['x' => 3, 'y' => 11, 'rotation' => 0],
        'size' => ['width' => 6, 'height' => 3],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/meeting/table_meeting_02.png']
    ];
    $objects[] = [
        'type' => 'FUR-CHR-OFF-001',
        'name' => 'كرسي فريق علوي 1',
        'position' => ['x' => 4, 'y' => 10, 'rotation' => 180],
        'size' => ['width' => 1, 'height' => 1],
        'collision' => false,
        'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_01.png']
    ];
    $objects[] = [
        'type' => 'FUR-CHR-OFF-001',
        'name' => 'كرسي فريق علوي 2',
        'position' => ['x' => 7, 'y' => 10, 'rotation' => 180],
        'size' => ['width' => 1, 'height' => 1],
        'collision' => false,
        'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_01.png']
    ];
    $objects[] = [
        'type' => 'FUR-CHR-OFF-001',
        'name' => 'كرسي فريق سفلي 1',
        'position' => ['x' => 4, 'y' => 14, 'rotation' => 0],
        'size' => ['width' => 1, 'height' => 1],
        'collision' => false,
        'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_01.png']
    ];
    $objects[] = [
        'type' => 'FUR-CHR-OFF-001',
        'name' => 'كرسي فريق سفلي 2',
        'position' => ['x' => 7, 'y' => 14, 'rotation' => 0],
        'size' => ['width' => 1, 'height' => 1],
        'collision' => false,
        'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_01.png']
    ];
    $objects[] = [
        'type' => 'DEC-PLANT-IND-004',
        'name' => 'جدار نباتات طبيعية فاصل',
        'position' => ['x' => 1, 'y' => 10, 'rotation' => 0],
        'size' => ['width' => 1, 'height' => 5],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/decor/plants/plant_indoor_04.png']
    ];

    // ── MIDDLE CENTER: Central Lounge & Huddle (x:12, y:11, w:7, h:11) ──
    $objects[] = [
        'type' => 'FUR-LOUNG-SOF-001',
        'name' => 'أريكة جلدية وسطى علوية',
        'position' => ['x' => 13, 'y' => 11, 'rotation' => 0],
        'size' => ['width' => 2, 'height' => 2],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/furniture/lounge/lounge_01.png']
    ];
    $objects[] = [
        'type' => 'FUR-TBL-COF-002',
        'name' => 'طاولة قهوة وسطى',
        'position' => ['x' => 15, 'y' => 11, 'rotation' => 0],
        'size' => ['width' => 2, 'height' => 2],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/furniture/tables/table_side_02.png']
    ];
    $objects[] = [
        'type' => 'FUR-LOUNG-SOF-002',
        'name' => 'أريكة جلدية وسطى سفلية',
        'position' => ['x' => 13, 'y' => 15, 'rotation' => 180],
        'size' => ['width' => 2, 'height' => 2],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/furniture/lounge/lounge_02.png']
    ];

    // ── MIDDLE LOWER: Huddle Room (x:6, y:17, w:5, h:6) ──
    $objects[] = [
        'type' => 'BRD-MTG-WBD-002',
        'name' => 'لوح زجاجي للهادل',
        'position' => ['x' => 6, 'y' => 17, 'rotation' => 0],
        'size' => ['width' => 3, 'height' => 1],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/meeting/board_office_02.png']
    ];
    $objects[] = [
        'type' => 'FUR-TBL-COF-001',
        'name' => 'طاولة هادل دائرية',
        'position' => ['x' => 7, 'y' => 19, 'rotation' => 0],
        'size' => ['width' => 2, 'height' => 2],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/furniture/tables/table_side_01.png']
    ];
    $objects[] = [
        'type' => 'FUR-CHR-OFF-001',
        'name' => 'كرسي هادل 1',
        'position' => ['x' => 6, 'y' => 20, 'rotation' => 90],
        'size' => ['width' => 1, 'height' => 1],
        'collision' => false,
        'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_01.png']
    ];
    $objects[] = [
        'type' => 'FUR-CHR-OFF-001',
        'name' => 'كرسي هادل 2',
        'position' => ['x' => 9, 'y' => 20, 'rotation' => 270],
        'size' => ['width' => 1, 'height' => 1],
        'collision' => false,
        'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_01.png']
    ];

    // ── BOTTOM LEFT: Reception Lounge (x:1, y:23, w:11, h:8) ──
    $objects[] = [
        'type' => 'FUR-LOUNG-SOF-003',
        'name' => 'كنب استقبال فاخر ثلاثي',
        'position' => ['x' => 3, 'y' => 24, 'rotation' => 0],
        'size' => ['width' => 5, 'height' => 2],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/furniture/lounge/lounge_03.png']
    ];
    $objects[] = [
        'type' => 'FUR-TBL-COF-003',
        'name' => 'طاولة استقبال رخامية',
        'position' => ['x' => 4, 'y' => 27, 'rotation' => 0],
        'size' => ['width' => 3, 'height' => 2],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/furniture/tables/table_side_03.png']
    ];
    $objects[] = [
        'type' => 'FUR-CHR-OFF-005',
        'name' => 'كرسي بذراعين يسار',
        'position' => ['x' => 2, 'y' => 27, 'rotation' => 90],
        'size' => ['width' => 1, 'height' => 2],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_05.png']
    ];
    $objects[] = [
        'type' => 'FUR-CHR-OFF-005',
        'name' => 'كرسي بذراعين يمين',
        'position' => ['x' => 8, 'y' => 27, 'rotation' => 270],
        'size' => ['width' => 1, 'height' => 2],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_05.png']
    ];
    $objects[] = [
        'type' => 'BRK-KIT-EQP-001',
        'name' => 'مبرد مياه الاستقبال',
        'position' => ['x' => 10, 'y' => 24, 'rotation' => 0],
        'size' => ['width' => 1, 'height' => 1],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/breakroom/breakroom_01.png']
    ];
    $objects[] = [
        'type' => 'DEC-PLANT-IND-001',
        'name' => 'شجرة مونستيرا الاستقبال',
        'position' => ['x' => 1, 'y' => 24, 'rotation' => 0],
        'size' => ['width' => 1, 'height' => 1],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/decor/plants/plant_indoor_01.png']
    ];

    // ── BOTTOM RIGHT: Executive Boardroom (x:20, y:21, w:11, h:10) ──
    $objects[] = [
        'type' => 'FUR-TBL-MTG-001',
        'name' => 'طاولة مجلس الإدارة الكبرى',
        'position' => ['x' => 23, 'y' => 24, 'rotation' => 0],
        'size' => ['width' => 5, 'height' => 3],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/meeting/table_meeting_01.png']
    ];
    $objects[] = [
        'type' => 'FUR-CHR-OFF-002',
        'name' => 'كرسي إدارة علوي 1',
        'position' => ['x' => 24, 'y' => 23, 'rotation' => 180],
        'size' => ['width' => 1, 'height' => 1],
        'collision' => false,
        'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_02.png']
    ];
    $objects[] = [
        'type' => 'FUR-CHR-OFF-002',
        'name' => 'كرسي إدارة علوي 2',
        'position' => ['x' => 26, 'y' => 23, 'rotation' => 180],
        'size' => ['width' => 1, 'height' => 1],
        'collision' => false,
        'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_02.png']
    ];
    $objects[] = [
        'type' => 'FUR-CHR-OFF-002',
        'name' => 'كرسي إدارة سفلي 1',
        'position' => ['x' => 24, 'y' => 27, 'rotation' => 0],
        'size' => ['width' => 1, 'height' => 1],
        'collision' => false,
        'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_02.png']
    ];
    $objects[] = [
        'type' => 'FUR-CHR-OFF-002',
        'name' => 'كرسي إدارة سفلي 2',
        'position' => ['x' => 26, 'y' => 27, 'rotation' => 0],
        'size' => ['width' => 1, 'height' => 1],
        'collision' => false,
        'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_02.png']
    ];
    $objects[] = [
        'type' => 'FUR-CHR-OFF-002',
        'name' => 'كرسي إدارة يسار',
        'position' => ['x' => 22, 'y' => 25, 'rotation' => 90],
        'size' => ['width' => 1, 'height' => 1],
        'collision' => false,
        'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_02.png']
    ];
    $objects[] = [
        'type' => 'FUR-CHR-OFF-002',
        'name' => 'كرسي إدارة يمين',
        'position' => ['x' => 28, 'y' => 25, 'rotation' => 270],
        'size' => ['width' => 1, 'height' => 1],
        'collision' => false,
        'interaction_config' => ['image_url' => '/assets/furniture/chairs/chair_office_02.png']
    ];
    $objects[] = [
        'type' => 'DEC-PLANT-IND-003',
        'name' => 'شجرة قاعة الإدارة',
        'position' => ['x' => 29, 'y' => 22, 'rotation' => 0],
        'size' => ['width' => 1, 'height' => 1],
        'collision' => true,
        'interaction_config' => ['image_url' => '/assets/decor/plants/plant_indoor_03.png']
    ];

    foreach ($objects as $obj) {
        MapObject::create([
            'map_id' => $map->id,
            'organization_id' => $org->id,
            'type' => $obj['type'],
            'name' => $obj['name'],
            'position' => $obj['position'],
            'size' => $obj['size'],
            'collision' => $obj['collision'],
            'interaction_config' => $obj['interaction_config']
        ]);
    }

    echo "Successfully built Meem Master Office (مكتب ميم الافتراضي) for Org: {$org->name} with " . count($objects) . " 3D objects and 8 rooms!\n";
}

Cache::flush();
