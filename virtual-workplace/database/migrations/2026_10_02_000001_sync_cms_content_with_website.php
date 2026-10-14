<?php

use App\Domains\CMS\Support\CmsSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Aligns the CMS with what the website actually renders (CmsSchema):
 * adds the sections that were hardcoded in the views (feature strip, quote,
 * header, footer, login/sign-up panels), removes the five sections the site
 * never showed, and drops the unused theme colour/font/radius tokens.
 * Text already edited in the CMS is kept.
 */
return new class extends Migration
{
    public function up(): void
    {
        CmsSchema::sync();

        DB::table('cms_theme_settings')->whereIn('setting_key', [
            'color_deep_space', 'color_dark_green', 'color_emerald', 'color_mint',
            'color_soft_mint', 'color_white', 'color_text_dark', 'color_text_light',
            'color_text_muted', 'font_family_latin', 'font_family_arabic',
            'radius_btn', 'radius_card', 'glass_blur', 'glass_bg', 'glass_border',
            'main_navigation',
        ])->delete();
    }

    public function down(): void
    {
        // Content migration; nothing to restore.
    }
};
