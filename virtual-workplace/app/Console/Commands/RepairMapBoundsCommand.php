<?php

namespace App\Console\Commands;

use App\Domains\Workspace\Models\Map;
use App\Domains\Workspace\Models\Room;
use App\Domains\Workspace\Support\MapGeometry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Brings maps back in line with their floor images:
 * - layout_data.background_width/height are set from the real image file
 *   (older maps kept a 1200x708 default while showing a different image);
 * - rooms that hang off the image are moved (and if needed shrunk) onto it,
 *   because their walls and doors are unreachable in the office otherwise.
 *
 * Dry-run by default; nothing is written without --apply.
 */
class RepairMapBoundsCommand extends Command
{
    protected $signature = 'maps:repair-bounds
                            {--apply : Persist changes (default is a dry-run report)}
                            {--force : Skip the production confirmation}
                            {--map= : Restrict to one map id}';

    protected $description = 'Sync stored floor image sizes and move rooms that lie outside the floor image back onto it';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        if ($apply && app()->environment('production') && ! $this->option('force')
            && ! $this->confirm('You are about to modify maps in PRODUCTION. Continue?', false)) {
            $this->warn('Aborted.');

            return self::FAILURE;
        }

        $maps = Map::withoutGlobalScopes()
            ->when($this->option('map'), fn ($q, $id) => $q->whereKey($id))
            ->get();

        $rows = [];
        foreach ($maps as $map) {
            $layout = is_array($map->layout_data) ? $map->layout_data : [];
            $tile = (int) ($map->tile_size ?: 16);
            [$w, $h] = $this->imageSize($layout['background_image_url'] ?? null) ?? [(float) ($layout['background_width'] ?? 0), (float) ($layout['background_height'] ?? 0)];

            $sizeChanged = $w > 0 && $h > 0
                && ((int) ($layout['background_width'] ?? 0) !== (int) $w || (int) ($layout['background_height'] ?? 0) !== (int) $h);
            if ($sizeChanged) {
                $layout['background_width'] = (int) $w;
                $layout['background_height'] = (int) $h;
            }

            $moved = [];
            $rooms = Room::withoutGlobalScopes()->where('map_id', $map->id)->get();
            foreach ($rooms as $room) {
                $b = $room->bounds;
                if (! is_array($b) || MapGeometry::roomFitsMap($b, $layout, $tile)) {
                    continue;
                }
                $moved[] = [$room, self::fitBounds($b, (int) floor($w / $tile), (int) floor($h / $tile)), $b];
            }

            if (! $sizeChanged && ! $moved) {
                continue;
            }

            $rows[] = [
                $map->name,
                $sizeChanged ? "{$w}x{$h}" : '-',
                implode('; ', array_map(fn ($m) => sprintf('%s: %d,%d %dx%d → %d,%d %dx%d', $m[0]->name, $m[2]['x'], $m[2]['y'], $m[2]['width'], $m[2]['height'], $m[1]['x'], $m[1]['y'], $m[1]['width'], $m[1]['height']), $moved)) ?: '-',
            ];

            if ($apply) {
                DB::transaction(function () use ($map, $layout, $moved) {
                    $map->forceFill(['layout_data' => $layout])->save();
                    foreach ($moved as [$room, $nb]) {
                        $room->forceFill(['bounds' => $nb])->save();
                    }
                });
            }
        }

        if (! $rows) {
            $this->info('All maps already match their floor images.');

            return self::SUCCESS;
        }

        $this->table(['Map', 'Image size set to', 'Rooms moved onto the image'], $rows);
        $this->line($apply ? 'Changes applied.' : 'Dry run — re-run with --apply to write these changes.');

        return self::SUCCESS;
    }

    /**
     * Crop the part of a room that hangs off the image, keeping it where it is (so it
     * can't land on a neighbour). Only a room entirely off the image is moved back on.
     */
    public static function fitBounds(array $b, int $maxX, int $maxY): array
    {
        $x = (int) round($b['x'] ?? 0);
        $y = (int) round($b['y'] ?? 0);
        $width = (int) round($b['width'] ?? 1);
        $height = (int) round($b['height'] ?? 1);

        $x = max(0, $x);
        $y = max(0, $y);
        if ($x >= $maxX) {
            $width = min($width, $maxX);
            $x = $maxX - $width;
        }
        if ($y >= $maxY) {
            $height = min($height, $maxY);
            $y = $maxY - $height;
        }

        return array_merge($b, [
            'x' => $x,
            'y' => $y,
            'width' => max(1, min($width, $maxX - $x)),
            'height' => max(1, min($height, $maxY - $y)),
        ]);
    }

    /** Real size of a floor image stored under public/, or null when it can't be read. */
    private function imageSize(?string $url): ?array
    {
        if (! $url || ! str_starts_with($url, '/')) {
            return null;
        }
        $path = public_path(ltrim(strtok($url, '?'), '/'));
        if (! is_file($path)) {
            return null;
        }
        $info = @getimagesize($path);

        return $info ? [(float) $info[0], (float) $info[1]] : null;
    }
}
