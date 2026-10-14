<?php

namespace App\Domains\CMS\Support;

use App\Domains\CMS\Models\CmsPage;
use App\Domains\CMS\Models\CmsSection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;

/**
 * Read side of the CMS for public views.
 *
 * section() returns the active section, null when an optional section is
 * hidden, and the schema defaults when a required one (header, footer, auth
 * panels) has no row yet — so those screens always render.
 */
class Cms
{
    public static function section(string $key): ?CmsSection
    {
        $def = CmsSchema::section($key);
        if (! $def) {
            return null;
        }

        $section = self::activeSections($def['page'])->get($key);
        if (! $section && ! empty($def['required'])) {
            return CmsSchema::makeDefault($key);
        }

        return $section;
    }

    /** @return Collection<string, CmsSection> */
    public static function activeSections(string $slug): Collection
    {
        // Loaded once per request: the header, footer and page body share the query.
        $cache = request()->attributes;
        $key = 'cms.sections.'.$slug;
        if (! $cache->has($key)) {
            try {
                $sections = CmsPage::with('activeSections.mediaAsset')
                    ->where('slug', $slug)
                    ->where('status', 'published')
                    ->first()?->activeSections->keyBy('section_key') ?? collect();
            } catch (QueryException $e) {
                // Website copy must never take down log-in: fall back to the defaults.
                report($e);
                $sections = collect();
            }
            $cache->set($key, $sections);
        }

        return $cache->get($key);
    }
}
