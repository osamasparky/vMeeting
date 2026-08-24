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
        Schema::create('office_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->default('Standard Smart Office Blueprint');
            $table->string('slug')->unique()->default('default-office');
            $table->text('description')->nullable();
            $table->string('background_image_url')->default('/images/office_floorplan.jpg');
            $table->unsignedInteger('width')->default(32);
            $table->unsignedInteger('height')->default(26);
            $table->unsignedInteger('tile_size')->default(32);
            $table->json('layout_data')->nullable();
            $table->json('rooms_data')->nullable(); // Preconfigured default rooms
            $table->json('objects_data')->nullable(); // Preconfigured furniture objects
            $table->boolean('is_default')->default(true);
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_templates');
    }
};
