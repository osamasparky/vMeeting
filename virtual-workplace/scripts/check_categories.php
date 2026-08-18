<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

Illuminate\Support\Facades\Cache::flush();

$cats = App\Domains\Workspace\Models\FurnitureCategory::withCount('items')->get();
echo "Total categories in DB: " . $cats->count() . "\n";
foreach ($cats as $c) {
    echo "  Cat: {$c->name} ({$c->slug}) | Icon: {$c->icon} | Items count: {$c->items_count}\n";
}
