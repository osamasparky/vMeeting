<?php

namespace App\Domains\CMS\Services;

use App\Domains\CMS\Models\CmsThemeSetting;

class ThemeEngineService
{
    public static function getThemeTokens(): array
    {
        $defaults = [
            'color_deep_space' => '#071A16',
            'color_dark_green' => '#0B2922',
            'color_emerald' => '#13A879',
            'color_mint' => '#6FE7C2',
            'color_soft_mint' => '#DDF8EF',
            'color_white' => '#FFFFFF',
            'color_text_dark' => '#10231F',
            'color_text_light' => '#F4FBF7',
            'color_text_muted' => '#8BA69C',
            'font_family_latin' => "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
            'font_family_arabic' => "'Cairo', 'Inter', sans-serif",
            'radius_btn' => '12px',
            'radius_card' => '20px',
            'glass_blur' => '24px',
            'glass_bg' => 'rgba(11, 41, 34, 0.72)',
            'glass_border' => 'rgba(111, 231, 194, 0.15)',
        ];

        $stored = CmsThemeSetting::all()->pluck('setting_value', 'setting_key')->toArray();

        return array_merge($defaults, $stored);
    }

    public static function generateCssVariables(): string
    {
        $tokens = self::getThemeTokens();

        $css = ":root {\n";
        $css .= "  --ns-deep-space: {$tokens['color_deep_space']};\n";
        $css .= "  --ns-dark-green: {$tokens['color_dark_green']};\n";
        $css .= "  --ns-emerald: {$tokens['color_emerald']};\n";
        $css .= "  --ns-mint: {$tokens['color_mint']};\n";
        $css .= "  --ns-soft-mint: {$tokens['color_soft_mint']};\n";
        $css .= "  --ns-white: {$tokens['color_white']};\n";
        $css .= "  --ns-text-dark: {$tokens['color_text_dark']};\n";
        $css .= "  --ns-text-light: {$tokens['color_text_light']};\n";
        $css .= "  --ns-text-muted: {$tokens['color_text_muted']};\n";
        $css .= "  --ns-font-latin: {$tokens['font_family_latin']};\n";
        $css .= "  --ns-font-arabic: {$tokens['font_family_arabic']};\n";
        $css .= "  --ns-radius-btn: {$tokens['radius_btn']};\n";
        $css .= "  --ns-radius-card: {$tokens['radius_card']};\n";
        $css .= "  --ns-glass-blur: {$tokens['glass_blur']};\n";
        $css .= "  --ns-glass-bg: {$tokens['glass_bg']};\n";
        $css .= "  --ns-glass-border: {$tokens['glass_border']};\n";
        $css .= "  --ns-gradient-emerald: linear-gradient(135deg, {$tokens['color_emerald']} 0%, {$tokens['color_mint']} 100%);\n";
        $css .= "  --ns-gradient-dark: linear-gradient(180deg, {$tokens['color_deep_space']} 0%, {$tokens['color_dark_green']} 100%);\n";
        $css .= "  --ns-shadow-glow: 0 0 30px rgba(19, 168, 121, 0.35);\n";
        $css .= "  --ns-shadow-card: 0 16px 40px rgba(0, 0, 0, 0.45), 0 0 1px rgba(111, 231, 194, 0.25);\n";
        $css .= "}\n";

        return $css;
    }
}
