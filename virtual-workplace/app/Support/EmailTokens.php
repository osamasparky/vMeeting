<?php

namespace App\Support;

/**
 * Design-system colours for HTML email. Mail clients don't support CSS custom
 * properties, so the semantic tokens are resolved to literal values from the
 * generated tokens.json (light mode) instead of being hard-coded in views.
 */
class EmailTokens
{
    private static ?array $cache = null;

    /** @return array<string, string> semantic name (e.g. 'surface/card') => colour */
    public static function light(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $json = json_decode(file_get_contents(resource_path('design-system/tokens.json')), true);
        $primitives = $json['collections']['Primitives']['variables'] ?? [];
        $semantic = $json['collections']['Color']['variables'] ?? [];

        $resolve = function (string $alias) use ($primitives): ?string {
            return $primitives[$alias]['modes']['Value'] ?? null;
        };

        $out = [];
        foreach ($semantic as $name => $var) {
            $light = $var['modes']['Light'] ?? null;
            if (is_array($light) && isset($light['alias'])) {
                $value = $resolve($light['alias']);
            } else {
                $value = is_string($light) ? $light : null;
            }
            if ($value) {
                $out[$name] = $value;
            }
        }

        return self::$cache = $out;
    }

    public static function get(string $name, string $fallbackToken = 'text/primary'): string
    {
        $all = self::light();

        return $all[$name] ?? $all[$fallbackToken] ?? 'inherit';
    }
}
