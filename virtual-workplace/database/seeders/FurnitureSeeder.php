<?php

namespace Database\Seeders;

use App\Domains\Workspace\Models\FurnitureCategory;
use App\Domains\Workspace\Models\FurnitureItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class FurnitureSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seating
        $seating = FurnitureCategory::firstOrCreate(['slug' => 'seating'], [
            'name' => 'Seating & Sofas',
            'icon' => '🪑',
            'order' => 1,
        ]);

        $seatingItems = [
            ['name' => 'Ergo Chair', 'slug' => 'chair', 'icon' => '🪑', 'width' => 1, 'height' => 1, 'collision' => false, 'colors' => ['#00b4b3', '#006847', '#f57b36', '#d20005']],
            ['name' => 'Lounge Sofa', 'slug' => 'sofa', 'icon' => '🛋️', 'width' => 2, 'height' => 1, 'collision' => true, 'colors' => ['#012c41', '#00726c', '#ffd136']],
            ['name' => 'Bean Bag Chair', 'slug' => 'beanbag', 'icon' => '🟡', 'width' => 1, 'height' => 1, 'collision' => false, 'colors' => ['#ffd136', '#00b4b3', '#f57b36']],
            ['name' => 'Booth Corner', 'slug' => 'booth', 'icon' => '🟥', 'width' => 2, 'height' => 2, 'collision' => true, 'colors' => ['#d20005', '#004862']],
        ];

        foreach ($seatingItems as $item) {
            FurnitureItem::firstOrCreate(['slug' => $item['slug']], [
                'category_id' => $seating->id,
                'name' => $item['name'],
                'icon' => $item['icon'],
                'width' => $item['width'],
                'height' => $item['height'],
                'collision' => $item['collision'],
                'colors' => $item['colors'],
                'is_active' => true,
            ]);
        }

        // 2. Tables & Desks
        $tables = FurnitureCategory::firstOrCreate(['slug' => 'tables'], [
            'name' => 'Tables & Desks',
            'icon' => '🖥️',
            'order' => 2,
        ]);

        $tableItems = [
            ['name' => 'Workstation', 'slug' => 'desk', 'icon' => '💻', 'width' => 2, 'height' => 1, 'collision' => true, 'colors' => ['#5c4033', '#004862']],
            ['name' => 'Executive Desk', 'slug' => 'executive_desk', 'icon' => '🖥️', 'width' => 2, 'height' => 2, 'collision' => true, 'colors' => ['#271610', '#00726c']],
            ['name' => 'Conference Table', 'slug' => 'dining_table', 'icon' => '🍽️', 'width' => 3, 'height' => 2, 'collision' => true, 'colors' => ['#3e2723', '#00b4b3']],
            ['name' => 'Filing Cabinets', 'slug' => 'cabinet', 'icon' => '🗄️', 'width' => 1, 'height' => 1, 'collision' => true, 'colors' => ['#64748b', '#a7c545']],
        ];

        foreach ($tableItems as $item) {
            FurnitureItem::firstOrCreate(['slug' => $item['slug']], [
                'category_id' => $tables->id,
                'name' => $item['name'],
                'icon' => $item['icon'],
                'width' => $item['width'],
                'height' => $item['height'],
                'collision' => $item['collision'],
                'colors' => $item['colors'],
                'is_active' => true,
            ]);
        }

        // 3. Plants & Decor
        $decor = FurnitureCategory::firstOrCreate(['slug' => 'decor'], [
            'name' => 'Plants & Workplace Decor',
            'icon' => '🪴',
            'order' => 3,
        ]);

        $decorItems = [
            ['name' => 'Decor Plant', 'slug' => 'plant', 'icon' => '🪴', 'width' => 1, 'height' => 1, 'collision' => false, 'colors' => ['#006847', '#a7c545']],
            ['name' => 'Water Cooler', 'slug' => 'water_cooler', 'icon' => '🚰', 'width' => 1, 'height' => 1, 'collision' => true, 'colors' => ['#00b4b3']],
            ['name' => 'Whiteboard', 'slug' => 'whiteboard', 'icon' => '📋', 'width' => 2, 'height' => 1, 'collision' => true, 'colors' => ['#ffffff']],
            ['name' => 'AV Screen', 'slug' => 'screen', 'icon' => '📺', 'width' => 2, 'height' => 1, 'collision' => true, 'colors' => ['#012c41']],
            ['name' => 'Floor Lamp', 'slug' => 'lamp', 'icon' => '💡', 'width' => 1, 'height' => 1, 'collision' => false, 'colors' => ['#ffd136']],
            ['name' => 'Ping Pong Table', 'slug' => 'pingpong', 'icon' => '🏓', 'width' => 3, 'height' => 2, 'collision' => true, 'colors' => ['#00b4b3', '#006847']],
        ];

        foreach ($decorItems as $item) {
            FurnitureItem::firstOrCreate(['slug' => $item['slug']], [
                'category_id' => $decor->id,
                'name' => $item['name'],
                'icon' => $item['icon'],
                'width' => $item['width'],
                'height' => $item['height'],
                'collision' => $item['collision'],
                'colors' => $item['colors'],
                'is_active' => true,
            ]);
        }

        // 4. Import 3D Furniture Catalog from database/data/furniture_catalog.json
        $catalogPath = database_path('data/furniture_catalog.json');
        if (File::exists($catalogPath)) {
            $catalog = json_decode(File::get($catalogPath), true);
            if (is_array($catalog)) {
                $categoryMeta = [
                    'desks' => ['name' => 'Desks & Workstations', 'icon' => '🖥️', 'order' => 1],
                    'chairs' => ['name' => 'Office Chairs & Seating', 'icon' => '🪑', 'order' => 2],
                    'conference_tables' => ['name' => 'Meeting & Conference Tables', 'icon' => '🤝', 'order' => 3],
                    'lounge' => ['name' => 'Lounge & Sofas', 'icon' => '🛋️', 'order' => 4],
                    'storage' => ['name' => 'Storage & Cabinets', 'icon' => '🗄️', 'order' => 5],
                    'reception' => ['name' => 'Reception & Counters', 'icon' => '🛎️', 'order' => 6],
                    'hardware' => ['name' => 'Technology & Hardware', 'icon' => '💻', 'order' => 7],
                    'presentation' => ['name' => 'Whiteboards & AV Screens', 'icon' => '📊', 'order' => 8],
                    'pods' => ['name' => 'Focus Pods & Booths', 'icon' => '🎧', 'order' => 9],
                    'dividers' => ['name' => 'Partitions & Dividers', 'icon' => '🧱', 'order' => 10],
                    'lamps' => ['name' => 'Lighting & Lamps', 'icon' => '💡', 'order' => 11],
                    'plants' => ['name' => 'Indoor Plants & Greenery', 'icon' => '🪴', 'order' => 12],
                    'kitchen' => ['name' => 'Breakroom & Kitchen', 'icon' => '☕', 'order' => 13],
                    'facilities' => ['name' => 'Safety & Facilities', 'icon' => '🧯', 'order' => 14],
                    'doors' => ['name' => 'Doors & Entryways', 'icon' => '🚪', 'order' => 15],
                    'windows' => ['name' => 'Windows & Blinds', 'icon' => '🪟', 'order' => 16],
                    'walls' => ['name' => 'Walls & Partitions', 'icon' => '🧱', 'order' => 17],
                    'zones' => ['name' => 'Virtual Markers & HUDs', 'icon' => '📍', 'order' => 18],
                ];

                foreach ($catalog as $entry) {
                    $catSlug = $entry['subcategory'] ?? ($entry['category'] ?? 'desks');
                    $meta = $categoryMeta[$catSlug] ?? [
                        'name' => ucwords(str_replace('_', ' ', $catSlug)),
                        'icon' => '🪑',
                        'order' => 20
                    ];

                    $category = FurnitureCategory::firstOrCreate(
                        ['slug' => $catSlug],
                        ['name' => $meta['name'], 'icon' => $meta['icon'], 'order' => $meta['order']]
                    );
                    $category->update([
                        'name' => $meta['name'],
                        'icon' => $meta['icon'],
                        'order' => $meta['order']
                    ]);

                    FurnitureItem::updateOrCreate(
                        ['slug' => $entry['id']],
                        [
                            'category_id' => $category->id,
                            'name' => $entry['name'],
                            'image_url' => $entry['asset']['image'] ?? null,
                            'icon' => $meta['icon'],
                            'width' => $entry['footprint']['width_tiles'] ?? 2,
                            'height' => $entry['footprint']['height_tiles'] ?? 1,
                            'collision' => $entry['behavior']['collision'] ?? true,
                            'colors' => [$entry['appearance']['color'] ?? '#3b82f6'],
                            'is_active' => true,
                        ]
                    );
                }
            }
        }

        Cache::forget('furniture_catalog_active');
        Cache::forget('furniture_categories_with_items');
        Cache::flush();
    }
}


