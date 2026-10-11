<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sticky notes used images hot-linked from assets.kumospace.com, so they vanished whenever that
 * site was down. They now use our own images in public/images/furniture/sticky/.
 */
return new class extends Migration
{
    private const MAP = [
        'https://assets.kumospace.com/furniture/decor/sticky-yellow-01/yella-large_240.png' => '/images/furniture/sticky/sticky-yellow.svg',
        'https://assets.kumospace.com/furniture/decor/sticky-orange-01/orange-large_240.png' => '/images/furniture/sticky/sticky-orange.svg',
        'https://assets.kumospace.com/furniture/decor/sticky-purple-01/purple-large_240.png' => '/images/furniture/sticky/sticky-purple.svg',
        'https://assets.kumospace.com/furniture/decor/sticky-green-01/green-large_240.png' => '/images/furniture/sticky/sticky-green.svg',
        'https://assets.kumospace.com/furniture/decor/sticky-blue-01/blue-large_240.png' => '/images/furniture/sticky/sticky-blue.svg',
        'https://assets.kumospace.com/furniture/decor/sticky-red-01/salmon-large_240.png' => '/images/furniture/sticky/sticky-red.svg',
    ];

    /** The catalogue's small previews of the same notes. */
    private const THUMBS = [
        'https://assets.kumospace.com/furniture/decor/sticky-yellow-01/thumb.png' => '/images/furniture/sticky/sticky-yellow.svg',
        'https://assets.kumospace.com/furniture/decor/sticky-orange-01/thumb.png' => '/images/furniture/sticky/sticky-orange.svg',
        'https://assets.kumospace.com/furniture/decor/sticky-purple-01/thumb.png' => '/images/furniture/sticky/sticky-purple.svg',
        'https://assets.kumospace.com/furniture/decor/sticky-green-01/thumb.png' => '/images/furniture/sticky/sticky-green.svg',
        'https://assets.kumospace.com/furniture/decor/sticky-blue-01/thumb.png' => '/images/furniture/sticky/sticky-blue.svg',
        'https://assets.kumospace.com/furniture/decor/sticky-red-01/thumb.png' => '/images/furniture/sticky/sticky-red.svg',
    ];

    public function up(): void
    {
        $this->swap(self::MAP);
        if (Schema::hasTable('furniture_items') && Schema::hasColumn('furniture_items', 'thumbnail_url')) {
            foreach (self::MAP + self::THUMBS as $from => $to) {
                DB::table('furniture_items')->where('thumbnail_url', $from)->update(['thumbnail_url' => $to]);
            }
        }
    }

    public function down(): void
    {
        $this->swap(array_flip(self::MAP));
        if (Schema::hasTable('furniture_items') && Schema::hasColumn('furniture_items', 'thumbnail_url')) {
            foreach (array_flip(self::MAP) as $from => $to) {
                DB::table('furniture_items')->where('thumbnail_url', $from)->update(['thumbnail_url' => $to]);
            }
        }
    }

    private function swap(array $map): void
    {
        if (Schema::hasTable('furniture_items')) {
            foreach ($map as $from => $to) {
                DB::table('furniture_items')->where('image_url', $from)->update(['image_url' => $to]);
            }
        }

        if (! Schema::hasTable('map_objects')) {
            return;
        }
        $hasImageColumn = Schema::hasColumn('map_objects', 'image_url');
        DB::table('map_objects')->where('interaction_config', 'like', '%sticky-%')
            ->orWhere(fn ($q) => $hasImageColumn ? $q->where('image_url', 'like', '%sticky-%') : $q->whereRaw('1 = 0'))
            ->orderBy('id')
            ->each(function ($row) use ($map, $hasImageColumn) {
                $changes = [];
                $config = json_decode((string) $row->interaction_config, true);
                if (is_array($config) && isset($config['image_url'], $map[$config['image_url']])) {
                    $config['image_url'] = $map[$config['image_url']];
                    $changes['interaction_config'] = json_encode($config);
                }
                if ($hasImageColumn && isset($row->image_url, $map[$row->image_url])) {
                    $changes['image_url'] = $map[$row->image_url];
                }
                if ($changes) {
                    DB::table('map_objects')->where('id', $row->id)->update($changes);
                }
            });
    }
};
