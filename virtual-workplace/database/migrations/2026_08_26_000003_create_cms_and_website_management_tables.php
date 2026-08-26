<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title_en');
            $table->string('title_ar');
            $table->string('meta_title_en')->nullable();
            $table->string('meta_title_ar')->nullable();
            $table->text('meta_desc_en')->nullable();
            $table->text('meta_desc_ar')->nullable();
            $table->string('og_image')->nullable();
            $table->string('status')->default('published'); // published, draft, scheduled
            $table->timestamps();
        });

        Schema::create('cms_media_assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('asset_type')->default('image'); // image, video, 3d_glb, 3d_gltf, lottie, audio
            $table->string('file_path');
            $table->string('thumbnail_path')->nullable();
            $table->string('dimensions')->nullable();
            $table->string('file_size')->nullable();
            $table->json('tags')->nullable();
            $table->string('version_tag')->nullable(); // e.g. hero-office-v1
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('cms_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('cms_pages')->cascadeOnDelete();
            $table->string('section_type'); // hero_3d, spatial_presence, meetings, floorplan_editor, ai_generator, collaboration, productivity, company_workspace, pricing, testimonials, cta, faq, footer
            $table->string('section_key')->index();
            $table->string('title_en')->nullable();
            $table->string('title_ar')->nullable();
            $table->text('subtitle_en')->nullable();
            $table->text('subtitle_ar')->nullable();
            $table->string('badge_en')->nullable();
            $table->string('badge_ar')->nullable();
            $table->json('content')->nullable();
            $table->foreignId('media_asset_id')->nullable()->constrained('cms_media_assets')->nullOnDelete();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['page_id', 'display_order']);
        });

        Schema::create('cms_theme_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key')->unique();
            $table->longText('setting_value')->nullable();
            $table->timestamps();
        });

        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('flag_key')->unique();
            $table->string('name_en');
            $table->string('name_ar');
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('category')->default('general');
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flags');
        Schema::dropIfExists('cms_theme_settings');
        Schema::dropIfExists('cms_sections');
        Schema::dropIfExists('cms_media_assets');
        Schema::dropIfExists('cms_pages');
    }
};
