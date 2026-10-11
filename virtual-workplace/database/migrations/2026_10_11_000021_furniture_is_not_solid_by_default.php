<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Furniture used to be marked solid by default, though the office ignored it. Now that solid
 * furniture blocks avatars, everything starts walk-through and is made solid one item at a
 * time in the editor ("Solid" switch).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('furniture_items')->update(['collision' => false]);
        DB::table('map_objects')->update(['collision' => false]);
        Schema::table('furniture_items', fn (Blueprint $t) => $t->boolean('collision')->default(false)->change());
    }

    public function down(): void
    {
        Schema::table('furniture_items', fn (Blueprint $t) => $t->boolean('collision')->default(true)->change());
    }
};
