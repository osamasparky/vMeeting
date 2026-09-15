<?php

use App\Domains\Workspace\Models\FurnitureCategory;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Cache;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

Cache::flush();

$cats = FurnitureCategory::withCount('items')->get();
echo 'Total categories in DB: '.$cats->count()."\n";
foreach ($cats as $c) {
    echo "  Cat: {$c->name} ({$c->slug}) | Icon: {$c->icon} | Items count: {$c->items_count}\n";
}
