<?php

namespace App\Domains\Workspace\Support;

/**
 * Shared geometry primitive for room-to-room spacing checks.
 *
 * Two rooms on the same map must either share a wall (gap of exactly 0) or
 * leave a walkable corridor of at least MIN_ROOM_GAP_PX. A gap in between
 * looks walkable but is too narrow for the avatar navigation engine
 * (resources/views/office.blade.php) to route through, and overlapping rooms
 * are never valid. Rooms that share a wall stay reachable because the office
 * places each auto door on a wall that opens onto free floor. See
 * RoomLayoutSpacingService for the derivation of the 48px constant.
 */
final class RoomBoundsGap
{
    /**
     * Minimum walkable corridor width between two room rects, in pixels,
     * at the canonical runtime tile size (16px). Mirrored as
     * MIN_ROOM_GAP_PX in resources/views/office.blade.php.
     */
    public const MIN_ROOM_GAP_PX = 48;

    /**
     * A room narrower than this (in tiles) can't host the 56px door portal
     * office.blade.php's getRoomDoorPortal() draws.
     */
    public const MIN_ROOM_SIDE_TILES = 4;

    /**
     * The tile size actually used at render time by
     * App\Http\Controllers\Web\OfficeController, regardless of whatever a
     * map/template's own `tile_size` column says.
     */
    public const CANONICAL_TILE_PX = 16;

    /**
     * Axis-aligned bounding box separation between two room bounds, in
     * pixels. Two rects are separated as soon as they're separated on
     * either axis, so this is max(dx, dy) across axes — not a Euclidean
     * distance. A negative value means the rects overlap by that many
     * pixels.
     *
     * @param  array{x:int|float,y:int|float,width:int|float,height:int|float}  $a
     * @param  array{x:int|float,y:int|float,width:int|float,height:int|float}  $b
     */
    public static function distanceBetween(array $a, array $b, int $tilePx): float
    {
        $ax1 = $a['x'] * $tilePx;
        $ay1 = $a['y'] * $tilePx;
        $ax2 = $ax1 + $a['width'] * $tilePx;
        $ay2 = $ay1 + $a['height'] * $tilePx;

        $bx1 = $b['x'] * $tilePx;
        $by1 = $b['y'] * $tilePx;
        $bx2 = $bx1 + $b['width'] * $tilePx;
        $by2 = $by1 + $b['height'] * $tilePx;

        $dx = max($bx1 - $ax2, $ax1 - $bx2);
        $dy = max($by1 - $ay2, $ay1 - $by2);

        return max($dx, $dy);
    }

    /**
     * The minimum gap, in whole tiles, required at the given tile size.
     */
    public static function minGapTiles(int $tilePx): int
    {
        return (int) ceil(self::MIN_ROOM_GAP_PX / $tilePx);
    }

    /**
     * Sub-pixel tolerance for "shares a wall", since bounds may be floats.
     */
    public const SHARED_WALL_TOLERANCE_PX = 0.5;

    public static function satisfiesMinGap(array $a, array $b, int $tilePx): bool
    {
        return self::gapIsAllowed(self::distanceBetween($a, $b, $tilePx));
    }

    /**
     * A gap is allowed when the rooms share a wall or keep a full corridor.
     */
    public static function gapIsAllowed(float $gapPx): bool
    {
        return abs($gapPx) <= self::SHARED_WALL_TOLERANCE_PX || $gapPx >= self::MIN_ROOM_GAP_PX;
    }

    /**
     * All pairs of rooms whose bounds violate the minimum gap, worst first.
     *
     * @param  array<int, array{x:int|float,y:int|float,width:int|float,height:int|float}>  $roomsBounds
     * @return array<int, array{0:int,1:int,2:float}> [roomIndexA, roomIndexB, gapPx]
     */
    public static function violatingPairs(array $roomsBounds, int $tilePx): array
    {
        $pairs = [];
        $count = count($roomsBounds);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $gap = self::distanceBetween($roomsBounds[$i], $roomsBounds[$j], $tilePx);
                if (! self::gapIsAllowed($gap)) {
                    $pairs[] = [$i, $j, $gap];
                }
            }
        }

        usort($pairs, fn ($a, $b) => $a[2] <=> $b[2]);

        return $pairs;
    }
}
