<?php

namespace App\Support;

use Illuminate\Contracts\Translation\Loader;

/**
 * Wraps the framework's translation loader so the JSON phrases (__('...')) include the
 * edits made in the super-admin translations console — see TranslationOverrides.
 */
class TranslationOverrideLoader implements Loader
{
    public function __construct(private Loader $inner, private TranslationOverrides $overrides) {}

    public function load($locale, $group, $namespace = null)
    {
        $lines = $this->inner->load($locale, $group, $namespace);

        if ($group === '*' && $namespace === '*' && in_array($locale, TranslationOverrides::LOCALES, true)) {
            return $this->overrides->apply($locale, $lines);
        }

        return $lines;
    }

    public function addNamespace($namespace, $hint)
    {
        $this->inner->addNamespace($namespace, $hint);
    }

    public function addJsonPath($path)
    {
        $this->inner->addJsonPath($path);
    }

    public function namespaces()
    {
        return $this->inner->namespaces();
    }

    public function __call($method, $parameters)
    {
        return $this->inner->{$method}(...$parameters);
    }
}
