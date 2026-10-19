<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

/**
 * Phrases the platform owner edits in the super-admin translations console.
 *
 * lang/{locale}.json ships with the code and is replaced on every deploy (and is not
 * writable by the web server on live), so edits are kept in one JSON file under
 * storage/ and laid over the shipped phrases when they load: shipped phrases, minus the
 * deleted keys, plus the edited ones.
 */
class TranslationOverrides
{
    public const LOCALES = ['ar', 'en'];

    /** @var array{deleted: list<string>, ar: array<string,string>, en: array<string,string>}|null */
    private ?array $data = null;

    private int|false $mtime = 0;

    /** @var array<string, array<string,string>> */
    private array $base = [];

    /** Unsaved changes are pending; don't reload over them. */
    private bool $dirty = false;

    public function __construct(private ?string $path = null) {}

    public function path(): string
    {
        return $this->path ?? storage_path('app/translations/overrides.json');
    }

    /** The phrases a locale ships with, before any edits. */
    public function base(string $locale): array
    {
        if (! isset($this->base[$locale])) {
            $file = lang_path($locale.'.json');
            $this->base[$locale] = is_file($file) ? (json_decode((string) file_get_contents($file), true) ?: []) : [];
        }

        return $this->base[$locale];
    }

    /** What the console shows and the translator uses for this locale. */
    public function merged(string $locale): array
    {
        return $this->apply($locale, $this->base($locale));
    }

    /** Lay the edits for this locale over already-loaded phrases. */
    public function apply(string $locale, array $lines): array
    {
        $data = $this->data();
        foreach ($data['deleted'] as $key) {
            unset($lines[$key]);
        }

        return array_merge($lines, $data[$locale] ?? []);
    }

    /** Store a phrase; a value equal to the shipped one drops the override. */
    public function set(string $key, string $locale, string $value): void
    {
        $data = $this->data();
        $data['deleted'] = array_values(array_diff($data['deleted'], [$key]));
        $base = $this->base($locale);
        if (array_key_exists($key, $base) && $base[$key] === $value) {
            unset($data[$locale][$key]);
        } else {
            $data[$locale][$key] = $value;
        }
        $this->data = $data;
        $this->dirty = true;
    }

    public function delete(string $key): void
    {
        $data = $this->data();
        foreach (self::LOCALES as $locale) {
            unset($data[$locale][$key]);
        }
        $shipped = array_filter(self::LOCALES, fn ($l) => array_key_exists($key, $this->base($l)));
        if ($shipped && ! in_array($key, $data['deleted'], true)) {
            $data['deleted'][] = $key;
        }
        $this->data = $data;
        $this->dirty = true;
    }

    public function save(): void
    {
        File::ensureDirectoryExists(dirname($this->path()));
        $json = json_encode($this->data(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (file_put_contents($this->path(), $json, LOCK_EX) === false) {
            throw new \RuntimeException('Could not write '.$this->path());
        }
        $this->dirty = false;
        clearstatcache(true, $this->path());
        $this->mtime = filemtime($this->path());
    }

    private function data(): array
    {
        // Re-read when the file changes, so long-running queue workers see new edits.
        $file = $this->path();
        clearstatcache(true, $file);
        $mtime = is_file($file) ? filemtime($file) : 0;
        if ($this->data !== null && ! $this->dirty && $mtime !== $this->mtime) {
            $this->data = null;
        }

        if ($this->data === null) {
            $this->mtime = $mtime;
            $decoded = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
            $decoded = is_array($decoded) ? $decoded : [];
            $this->data = ['deleted' => array_values($decoded['deleted'] ?? [])];
            foreach (self::LOCALES as $locale) {
                $this->data[$locale] = $decoded[$locale] ?? [];
            }
        }

        return $this->data;
    }
}
