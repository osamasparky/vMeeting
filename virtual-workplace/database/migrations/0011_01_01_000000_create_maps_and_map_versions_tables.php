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
        Schema::create('maps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('floor_id')->constrained('floors')->cascadeOnDelete();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('name')->default('Main Office');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->unsignedInteger('version')->default(1);
            $table->unsignedInteger('width')->default(40); // Grid width in tiles
            $table->unsignedInteger('height')->default(30); // Grid height in tiles
            $table->unsignedInteger('tile_size')->default(32); // Tile size in pixels
            $table->json('layout_data')->nullable(); // Tilemap / floor layers
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index('floor_id');
            $table->index(['organization_id', 'status']);
        });

        Schema::create('map_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('map_id')->constrained('maps')->cascadeOnDelete();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('version');
            $table->json('layout_snapshot');
            $table->timestamp('created_at')->useCurrent();

            $table->index('map_id');
            $table->index('organization_id');
            $table->unique(['map_id', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('map_versions');
        Schema::dropIfExists('maps');
    }
};
