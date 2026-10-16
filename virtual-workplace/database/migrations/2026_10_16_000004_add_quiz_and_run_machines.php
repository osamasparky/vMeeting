<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/** The quiz machine (Kahoot-style) and the maze chase "اهرب!" join the Games category. Insert-only. */
return new class extends Migration
{
    private const MACHINES = [
        ['slug' => 'arcade_quiz', 'name' => 'سؤال وجواب — Quiz', 'game' => 'quiz', 'icon' => '❓'],
        ['slug' => 'arcade_run', 'name' => 'اهرب! — Run!', 'game' => 'run', 'icon' => '👻'],
    ];

    public function up(): void
    {
        $categoryId = DB::table('furniture_categories')->where('slug', 'games')->value('id');
        if (! $categoryId) {
            return;
        }
        $now = now();
        foreach (self::MACHINES as $m) {
            if (DB::table('furniture_items')->where('slug', $m['slug'])->exists()) {
                continue;
            }
            $image = "/images/furniture/games/{$m['game']}.svg";
            DB::table('furniture_items')->insert([
                'category_id' => $categoryId,
                'name' => $m['name'],
                'slug' => $m['slug'],
                'image_url' => $image,
                'thumbnail_url' => $image,
                'icon' => $m['icon'],
                'width' => 2,
                'height' => 2,
                'collision' => true,
                'elevation' => 1,
                'interaction_type' => 'game',
                'interaction_config' => json_encode(['game' => $m['game']]),
                'colors' => json_encode([]),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        Cache::forget('furniture_catalog_active');
        Cache::forget('furniture_categories_with_items');
    }

    public function down(): void
    {
        DB::table('furniture_items')->whereIn('slug', array_column(self::MACHINES, 'slug'))->delete();
        Cache::forget('furniture_catalog_active');
        Cache::forget('furniture_categories_with_items');
    }
};
