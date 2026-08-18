<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$maps = App\Domains\Workspace\Models\Map::with(['rooms', 'objects'])->get();
foreach ($maps as $map) {
    echo "=== MAP {$map->id}: {$map->name} (Org: {$map->organization_id}) ===\n";
    echo "Rooms: " . $map->rooms->count() . "\n";
    foreach ($map->rooms as $r) {
        echo "  Room: {$r->name} (id: {$r->id}) | bounds: " . json_encode($r->bounds) . " | color: {$r->color}\n";
    }
    echo "Objects: " . $map->objects->count() . "\n";
    foreach ($map->objects as $o) {
        echo "  Obj: {$o->name} | type: {$o->type} | pos: " . json_encode($o->position) . " | size: {$o->width}x{$o->height} | img: {$o->image_url} | color: {$o->color} | rot: " . ($o->rotation ?? 0) . "\n";
    }
}
