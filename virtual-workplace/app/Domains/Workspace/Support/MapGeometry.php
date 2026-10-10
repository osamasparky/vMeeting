<?php

namespace App\Domains\Workspace\Support;

/**
 * Walls and obstacles drawn in the map editor (layout_data.walls / .obstacles),
 * in tiles. The office treats them as solid. Anything that isn't a well-formed
 * shape is dropped, so a malformed save can't break movement for everyone.
 */
class MapGeometry
{
    public const MAX_WALLS = 1000;

    public const MAX_OBSTACLES = 500;

    private const MAX_COORD = 2000; // tiles

    /**
     * Whether a room's bounds (tiles) lie on the floor image. Without a known image
     * size only negative positions are rejected. One tile of slack absorbs rounding
     * (the AI generator lays rooms on a 75x45 grid over a 1200x708 image).
     */
    public static function roomFitsMap(mixed $bounds, ?array $layout, int $tilePx = 16): bool
    {
        if (! is_array($bounds)) {
            return false;
        }
        foreach (['x', 'y', 'width', 'height'] as $k) {
            if (! isset($bounds[$k]) || ! is_numeric($bounds[$k])) {
                return false;
            }
        }
        if ($bounds['x'] < 0 || $bounds['y'] < 0 || $bounds['width'] < 1 || $bounds['height'] < 1) {
            return false;
        }

        $bw = (float) ($layout['background_width'] ?? 0);
        $bh = (float) ($layout['background_height'] ?? 0);
        if ($bw < 100 || $bh < 100) {
            return true;
        }

        return $bw / $tilePx + 1 >= $bounds['x'] + $bounds['width']
            && $bh / $tilePx + 1 >= $bounds['y'] + $bounds['height'];
    }

    public static function sanitize(array $layout): array
    {
        $num = fn ($v) => is_numeric($v) ? max(0, min(self::MAX_COORD, round((float) $v, 2))) : null;

        $walls = [];
        foreach (array_slice((array) ($layout['walls'] ?? []), 0, self::MAX_WALLS) as $w) {
            if (! is_array($w)) {
                continue;
            }
            $p = array_map($num, [$w['x1'] ?? null, $w['y1'] ?? null, $w['x2'] ?? null, $w['y2'] ?? null]);
            if (in_array(null, $p, true) || ($p[0] === $p[2] && $p[1] === $p[3])) {
                continue;
            }
            $walls[] = ['x1' => $p[0], 'y1' => $p[1], 'x2' => $p[2], 'y2' => $p[3]];
        }

        $obstacles = [];
        foreach (array_slice((array) ($layout['obstacles'] ?? []), 0, self::MAX_OBSTACLES) as $o) {
            if (! is_array($o)) {
                continue;
            }
            $r = array_map($num, [$o['x'] ?? null, $o['y'] ?? null, $o['width'] ?? null, $o['height'] ?? null]);
            if (in_array(null, $r, true) || $r[2] <= 0 || $r[3] <= 0) {
                continue;
            }
            $obstacles[] = ['x' => $r[0], 'y' => $r[1], 'width' => $r[2], 'height' => $r[3]];
        }

        $layout['walls'] = $walls;
        $layout['obstacles'] = $obstacles;

        return $layout;
    }
}
