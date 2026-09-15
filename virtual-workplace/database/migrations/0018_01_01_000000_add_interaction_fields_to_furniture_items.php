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
        Schema::table('furniture_items', function (Blueprint $table) {
            if (! Schema::hasColumn('furniture_items', 'interaction_type')) {
                $table->string('interaction_type', 64)->default('none')->after('colors');
            }
            if (! Schema::hasColumn('furniture_items', 'interaction_config')) {
                $table->json('interaction_config')->nullable()->after('interaction_type');
            }
            if (! Schema::hasColumn('furniture_items', 'elevation')) {
                $table->integer('elevation')->default(1)->after('interaction_config');
            }
            if (! Schema::hasColumn('furniture_items', 'thumbnail_url')) {
                $table->string('thumbnail_url')->nullable()->after('image_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('furniture_items', function (Blueprint $table) {
            $table->dropColumn(['interaction_type', 'interaction_config', 'elevation', 'thumbnail_url']);
        });
    }
};
