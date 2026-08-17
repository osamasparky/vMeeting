<?php

namespace Database\Seeders;

use App\Domains\Workspace\Models\FurnitureCategory;
use App\Domains\Workspace\Models\FurnitureItem;
use Illuminate\Database\Seeder;

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
    }
}
