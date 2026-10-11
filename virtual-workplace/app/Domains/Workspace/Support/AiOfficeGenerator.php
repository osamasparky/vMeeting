<?php

namespace App\Domains\Workspace\Support;

use App\Domains\Administration\Models\SystemSetting;
use App\Domains\CMS\Models\FeatureFlag;
use App\Domains\Tenancy\Models\Organization;

/**
 * Whether a company may use the AI office generator. All three must allow it:
 * - the super admin's feature flag "ai_office_generator" (kill switch for the whole system);
 * - the super admin's AI settings, "Enable AI Office & Floorplan Generator Platform-wide";
 * - the company's plan, which lists "ai_office_generator" among its features.
 */
class AiOfficeGenerator
{
    public const PLAN_FEATURE = 'ai_office_generator';

    public static function availableFor(?Organization $organization): bool
    {
        // Asked several times while drawing one page (editor, rooms tab, settings): worked out once per request.
        $key = 'ai-office-generator:'.($organization?->id ?? '-');
        $attributes = request()->attributes;
        if (! $attributes->has($key)) {
            $attributes->set($key, self::decide($organization));
        }

        return (bool) $attributes->get($key);
    }

    private static function decide(?Organization $organization): bool
    {
        if (! FeatureFlag::isEnabled('ai_office_generator')) {
            return false;
        }

        $platform = SystemSetting::get('openai_settings', []);
        if (is_array($platform) && array_key_exists('is_enabled', $platform) && ! $platform['is_enabled']) {
            return false;
        }

        $features = $organization?->plan?->features ?? [];

        return in_array(self::PLAN_FEATURE, (array) $features, true);
    }
}
