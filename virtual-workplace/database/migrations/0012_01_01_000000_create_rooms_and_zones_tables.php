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
        Schema::create('rooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('map_id')->constrained('maps')->cascadeOnDelete();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['meeting', 'private', 'manager', 'support', 'client', 'reception'])->default('meeting');
            $table->enum('access_mode', ['public', 'private', 'role', 'invite'])->default('public');
            $table->unsignedInteger('capacity')->default(10);
            $table->string('color', 32)->nullable();
            $table->json('bounds'); // { "x": 5, "y": 5, "width": 8, "height": 6 }
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index('map_id');
        });

        Schema::create('zones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('map_id')->constrained('maps')->cascadeOnDelete();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['movement', 'audio', 'interaction', 'quiet'])->default('audio');
            $table->string('shape_type', 32)->default('rectangle'); // rectangle | polygon
            $table->json('shape_data'); // coordinates / bounding geometry
            $table->float('audible_radius')->nullable(); // For spatial audio falloff
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index('map_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zones');
        Schema::dropIfExists('rooms');
    }
};
