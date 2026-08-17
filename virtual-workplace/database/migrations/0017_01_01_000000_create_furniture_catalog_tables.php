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
        Schema::create('furniture_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->default('🪑');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('furniture_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('furniture_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image_url')->nullable();
            $table->string('icon')->default('🪑');
            $table->integer('width')->default(1);
            $table->integer('height')->default(1);
            $table->boolean('collision')->default(true);
            $table->json('colors')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('category_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('furniture_items');
        Schema::dropIfExists('furniture_categories');
    }
};
