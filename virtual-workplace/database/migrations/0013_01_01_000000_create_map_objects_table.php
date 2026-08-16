<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('map_objects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('map_id')->constrained('maps')->cascadeOnDelete();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('type', 64); // desk, chair, wall, door, plant, furniture, whiteboard, screen, custom
            $table->string('name')->nullable();
            $table->json('position'); // { "x": 10, "y": 8, "z": 0, "rotation": 0 }
            $table->json('size')->nullable(); // { "width": 1, "height": 1 }
            $table->boolean('collision')->default(false);
            $table->json('interaction_config')->nullable(); // e.g. { "type": "sit", "action": "open_whiteboard" }
            $table->timestamps();

            $table->index('organization_id');
            $table->index('map_id');
            $table->index(['map_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('map_objects');
    }
};
