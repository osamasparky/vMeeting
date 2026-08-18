<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Domains\Workspace\Models\FurnitureItem;
use App\Domains\Workspace\Models\FurnitureCategory;
use App\Domains\Workspace\Models\MapObject;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

$catalogPath = database_path('data/furniture_catalog.json');
$catalog = json_decode(File::get($catalogPath), true);
$validSlugs = collect($catalog)->pluck('id')->toArray();

// Also keep standard basic slugs
$legacySlugs = ['desk', 'executive_desk', 'dining_table', 'cabinet', 'chair', 'executive_chair', 'stool', 'plant', 'water_cooler', 'whiteboard', 'screen', 'lamp', 'pingpong'];
$allValid = array_merge($validSlugs, $legacySlugs);

// Delete items that are no longer in the clean unique catalog
$deletedCount = FurnitureItem::whereNotIn('slug', $allValid)->delete();
echo "Removed {$deletedCount} duplicate/obsolete items from furniture_items table.\n";

// Delete categories with 0 items
$cats = FurnitureCategory::withCount('items')->get();
foreach ($cats as $cat) {
    if ($cat->items_count === 0) {
        $cat->delete();
        echo "Removed empty category: {$cat->name} ({$cat->slug})\n";
    }
}

// Run seeder to ensure all 108 unique items are synced properly
Artisan::call('db:seed', ['--class' => 'FurnitureSeeder']);
echo "FurnitureSeeder re-executed successfully.\n";

Cache::flush();
echo "All caches flushed.\n";
