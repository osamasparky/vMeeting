<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

/**
 * Copies furniture catalogue images (and sound effects) still loaded from an outside asset host
 * (config services.furniture_assets_source, env FURNITURE_ASSETS_SOURCE) onto this server
 * (public/images/furniture/catalog/…) and points the catalogue and every placed object at the
 * copies, so the office never depends on another site being up.
 *
 * Safe to re-run: files already copied are kept, and only images that were copied are re-pointed
 * (anything that failed keeps its original address). Dry-run by default.
 */
class LocalizeFurnitureImagesCommand extends Command
{
    protected $signature = 'furniture:localize-images
                            {--apply : Download the images and update the database (default: report only)}
                            {--force : Skip the production confirmation}';

    protected $description = 'Copy hot-linked furniture images onto this server and use the copies';


    private const LOCAL_DIR = 'images/furniture/catalog';

    private const MAX_BYTES = 5 * 1024 * 1024;

    private const MAX_AUDIO_BYTES = 20 * 1024 * 1024; // some sound effects are whole songs

    private string $host = '';

    public function handle(): int
    {
        $this->host = rtrim((string) config('services.furniture_assets_source'), '/').'/';
        if ($this->host === '/') {
            $this->info('No outside asset host is configured (FURNITURE_ASSETS_SOURCE); nothing to copy.');

            return self::SUCCESS;
        }
        $urls = $this->collectUrls();
        $this->info(count($urls).' furniture images are loaded from '.$this->host);
        if (! $urls) {
            return self::SUCCESS;
        }
        if (! $this->option('apply')) {
            $this->line('Dry run — re-run with --apply to copy them and update the database.');

            return self::SUCCESS;
        }
        if (app()->environment('production') && ! $this->option('force')
            && ! $this->confirm('Download the images and update furniture in PRODUCTION?', false)) {
            return self::FAILURE;
        }

        // 1. Copy every image we don't have yet, a few at a time.
        $map = [];
        $failed = [];
        $todo = [];
        foreach ($urls as $url) {
            $local = $this->localPath($url);
            if ($local === null) {
                $failed[] = $url;

                continue;
            }
            if (is_file(public_path($local))) {
                $map[$url] = '/'.$local;
            } else {
                $todo[$url] = $local;
            }
        }

        $bar = $this->output->createProgressBar(count($todo));
        foreach (array_chunk($todo, 8, true) as $chunk) {
            $responses = Http::pool(function ($pool) use ($chunk) {
                foreach ($chunk as $url => $local) {
                    $pool->as($url)->timeout(30)->retry(2, 500, throw: false)->get($url);
                }
            });
            foreach ($chunk as $url => $local) {
                $res = $responses[$url] ?? null;
                $ok = $res instanceof Response && $res->successful()
                    && $this->isMedia((string) $res->header('Content-Type'), $local)
                    && strlen($res->body()) > 0
                    && strlen($res->body()) <= (preg_match('#\.(mp3|ogg|wav)$#i', $local) ? self::MAX_AUDIO_BYTES : self::MAX_BYTES);
                if ($ok) {
                    File::ensureDirectoryExists(dirname(public_path($local)), 0755);
                    File::put(public_path($local), $res->body());
                    $map[$url] = '/'.$local;
                } else {
                    $failed[] = $url;
                }
                $bar->advance();
            }
        }
        $bar->finish();
        $this->newLine();

        // 2. Point the catalogue and placed furniture at the copies.
        $changed = DB::transaction(fn () => $this->rewrite($map));

        $this->info(count($map).' images are now served from this server; '.$changed.' database rows updated.');
        if ($failed) {
            $this->warn(count($failed).' could not be copied and keep their original address. Re-run later to retry.');
            foreach (array_slice($failed, 0, 10) as $url) {
                $this->line('  '.$url);
            }
        }

        return self::SUCCESS;
    }

    /** @return string[] */
    private function collectUrls(): array
    {
        $found = [];
        $add = function ($value) use (&$found, &$add) {
            if (is_string($value) && str_starts_with($value, $this->host)) {
                $found[$value] = true;
            } elseif (is_array($value)) {
                array_walk_recursive($value, function ($v) use (&$found) {
                    if (is_string($v) && str_starts_with($v, $this->host)) {
                        $found[$v] = true;
                    }
                });
            }
        };

        foreach (DB::table('furniture_items')->get(['image_url', 'thumbnail_url', 'interaction_config']) as $row) {
            $add($row->image_url);
            $add($row->thumbnail_url ?? null);
            $add(json_decode((string) $row->interaction_config, true));
        }
        foreach (DB::table('map_objects')->get(['interaction_config']) as $row) {
            $add(json_decode((string) $row->interaction_config, true));
        }

        return array_keys($found);
    }

    /** public/ path for a remote image, or null if the address isn't a plain file path. */
    private function localPath(string $url): ?string
    {
        $path = substr(strtok($url, '?'), strlen($this->host));
        if ($path === '' || str_contains($path, '..') || ! preg_match('#^[A-Za-z0-9._\-/]+\.(png|jpe?g|webp|gif|svg|mp3|ogg|wav)$#i', $path)) {
            return null;
        }

        return self::LOCAL_DIR.'/'.$path;
    }

    /** An image, or a sound effect (furniture that plays audio). */
    private function isMedia(string $contentType, string $local): bool
    {
        if (str_starts_with($contentType, 'image/') || str_starts_with($contentType, 'audio/')) {
            return true;
        }

        return $contentType === 'application/octet-stream' && preg_match('#\.(mp3|ogg|wav)$#i', $local) === 1;
    }

    private function rewrite(array $map): int
    {
        if (! $map) {
            return 0;
        }
        $swap = function ($value) use ($map, &$swap) {
            if (is_string($value)) {
                return $map[$value] ?? $value;
            }
            if (is_array($value)) {
                return array_map($swap, $value);
            }

            return $value;
        };

        $changed = 0;
        foreach (DB::table('furniture_items')->get(['id', 'image_url', 'thumbnail_url', 'interaction_config']) as $row) {
            $update = [];
            if (isset($map[$row->image_url])) {
                $update['image_url'] = $map[$row->image_url];
            }
            if (isset($row->thumbnail_url) && isset($map[$row->thumbnail_url])) {
                $update['thumbnail_url'] = $map[$row->thumbnail_url];
            }
            $config = json_decode((string) $row->interaction_config, true);
            if (is_array($config) && ($new = $swap($config)) !== $config) {
                $update['interaction_config'] = json_encode($new);
            }
            if ($update) {
                DB::table('furniture_items')->where('id', $row->id)->update($update);
                $changed++;
            }
        }
        foreach (DB::table('map_objects')->get(['id', 'interaction_config']) as $row) {
            $config = json_decode((string) $row->interaction_config, true);
            if (is_array($config) && ($new = $swap($config)) !== $config) {
                DB::table('map_objects')->where('id', $row->id)->update(['interaction_config' => json_encode($new)]);
                $changed++;
            }
        }

        return $changed;
    }
}
