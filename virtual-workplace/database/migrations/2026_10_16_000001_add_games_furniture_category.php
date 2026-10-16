<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Office arcade: a "Games" category in the map editor's furniture drawer with
 * one arcade machine per game. Placing a machine on a map makes it playable —
 * the office opens the game named in interaction_config.game (refereed by the
 * realtime server). Insert-only: an existing slug is left as it is.
 */
return new class extends Migration
{
    private const MACHINES = [
        ['slug' => 'arcade_snipers', 'name' => 'على السطح — Rooftop', 'game' => 'snipers', 'icon' => '🎯'],
        ['slug' => 'arcade_racing', 'name' => 'سباق — Racing', 'game' => 'racing', 'icon' => '🏁'],
        ['slug' => 'arcade_karts', 'name' => 'سيارات التصادم — Bumper Karts', 'game' => 'karts', 'icon' => '🏎️'],
        ['slug' => 'arcade_pool', 'name' => 'بلياردو — 8-Ball Pool', 'game' => 'pool', 'icon' => '🎱'],
    ];

    public function up(): void
    {
        $now = now();
        $categoryId = DB::table('furniture_categories')->where('slug', 'games')->value('id')
            ?? DB::table('furniture_categories')->insertGetId([
                'name' => 'Games', 'slug' => 'games', 'icon' => '🕹️',
                'order' => (int) DB::table('furniture_categories')->max('order') + 1,
                'created_at' => $now, 'updated_at' => $now,
            ]);

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
        if (! DB::table('furniture_items')->where('category_id', DB::table('furniture_categories')->where('slug', 'games')->value('id'))->exists()) {
            DB::table('furniture_categories')->where('slug', 'games')->delete();
        }
        Cache::forget('furniture_catalog_active');
        Cache::forget('furniture_categories_with_items');
    }
};
