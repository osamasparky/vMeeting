<?php

namespace Database\Seeders;

use App\Domains\Workspace\Models\FurnitureCategory;
use App\Domains\Workspace\Models\FurnitureItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class FullFurnitureCatalogSeeder extends Seeder
{
    /**
     * Seed complete furniture catalog into the database.
     */
    public function run(): void
    {
        $jsonPath = database_path('seeders/furniture_catalog_data.json');
        if (!File::exists($jsonPath)) {
            $this->command->error("Furniture catalog data file not found at: {$jsonPath}");
            return;
        }

        $data = json_decode(File::get($jsonPath), true);
        if (!$data || empty($data['categories']) || empty($data['items'])) {
            $this->command->error("Invalid catalog data JSON.");
            return;
        }

        $categoriesMap = [];

        DB::transaction(function () use ($data, &$categoriesMap) {
            // 1. Seed / Update Categories
            foreach ($data['categories'] as $slug => $catData) {
                $category = FurnitureCategory::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $catData['name'],
                        'icon' => $catData['icon'] ?? '🪑',
                        'order' => $catData['order'] ?? 0,
                    ]
                );
                $categoriesMap[$slug] = $category->id;
            }

            // 2. Seed Furniture Items in Batches
            $chunks = array_chunk($data['items'], 100);
            foreach ($chunks as $chunk) {
                foreach ($chunk as $item) {
                    $catId = $categoriesMap[$item['category_slug']] ?? null;
                    if (!$catId) continue;

                    FurnitureItem::updateOrCreate(
                        ['slug' => $item['slug']],
                        [
                            'category_id' => $catId,
                            'name' => $item['name'],
                            'image_url' => $item['image_url'],
                            'thumbnail_url' => $item['thumbnail_url'] ?? $item['image_url'],
                            'icon' => $item['icon'] ?? '🪑',
                            'width' => $item['width'] ?? 1,
                            'height' => $item['height'] ?? 1,
                            'collision' => $item['collision'] ?? true,
                            'elevation' => $item['elevation'] ?? 1,
                            'interaction_type' => $item['interaction_type'] ?? 'none',
                            'interaction_config' => $item['interaction_config'] ?? null,
                            'colors' => $item['colors'] ?? ['#3B82F6', '#10B981'],
                            'is_active' => true,
                        ]
                    );
                }
            }
        });

        // Clear catalog caches
        \Illuminate\Support\Facades\Cache::forget('furniture_categories_with_items');
        \Illuminate\Support\Facades\Cache::forget('furniture_catalog_active');

        $count = FurnitureItem::count();
        $catsCount = FurnitureCategory::count();
        $this->command->info("Successfully seeded {$count} furniture items across {$catsCount} categories!");
    }
}
