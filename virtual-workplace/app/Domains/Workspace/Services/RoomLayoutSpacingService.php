<?php

namespace App\Domains\Workspace\Services;

use App\Domains\Workspace\Support\RoomBoundsGap;

/**
 * Repacks a set of room rectangles (in tile units) so that every pair of
 * rooms on the same map is separated by at least RoomBoundsGap::MIN_ROOM_GAP_PX
 * of walkable corridor. Prefers shrinking rooms toward their own footprint
 * (keeps each room anchored near its original position, since rooms sit on
 * a pre-rendered decorative background image) and only pushes a room when
 * shrinking would take it below the minimum usable size.
 */
class RoomLayoutSpacingService
{
    private const MAX_PASSES = 6;

    /**
     * Report-only: how many pairs currently violate the minimum gap, with
     * no changes made.
     *
     * @param  array<int, array{name:string, bounds:array}>  $rooms
     */
    public function analyze(array $rooms, int $tilePx, ?int $canvasTilesX = null, ?int $canvasTilesY = null): array
    {
        $boundsList = array_map(fn ($r) => $r['bounds'], $rooms);
        $violations = RoomBoundsGap::violatingPairs($boundsList, $tilePx);
        [$connected, $isolatedRooms] = $this->analyzeConnectivity($rooms, $tilePx);

        return [
            'violations' => $violations,
            'violationsCount' => count($violations),
            'connected' => $connected,
            'isolatedRooms' => $isolatedRooms,
        ];
    }

    /**
     * Repack the given rooms so every pair satisfies the minimum gap.
     * Returns the (possibly modified) rooms plus a report of what changed.
     *
     * @param  array<int, array{name:string, bounds:array}>  $rooms
     */
    public function repack(array $rooms, int $tilePx, ?int $canvasTilesX = null, ?int $canvasTilesY = null): array
    {
        $working = $rooms;
        $moves = [];
        $unresolved = [];

        $boundsBefore = array_map(fn ($r) => $r['bounds'], $working);
        $violationsBefore = count(RoomBoundsGap::violatingPairs($boundsBefore, $tilePx));

        for ($pass = 0; $pass < self::MAX_PASSES; $pass++) {
            $boundsList = array_map(fn ($r) => $r['bounds'], $working);
            $pairs = RoomBoundsGap::violatingPairs($boundsList, $tilePx);

            if (empty($pairs)) {
                break;
            }

            foreach ($pairs as [$i, $j]) {
                $gap = RoomBoundsGap::distanceBetween($working[$i]['bounds'], $working[$j]['bounds'], $tilePx);
                if ($gap >= RoomBoundsGap::MIN_ROOM_GAP_PX) {
                    continue; // already resolved earlier this pass
                }

                $resolved = $this->resolvePair($working, $i, $j, $tilePx, $canvasTilesX, $canvasTilesY, $moves);

                if (! $resolved) {
                    $unresolved[] = [
                        'roomA' => $working[$i]['name'],
                        'roomB' => $working[$j]['name'],
                        'reason' => 'Cannot satisfy the minimum gap without shrinking a room below '
                            .RoomBoundsGap::MIN_ROOM_SIDE_TILES.' tiles or pushing it outside the map canvas.',
                    ];
                }
            }
        }

        $boundsAfter = array_map(fn ($r) => $r['bounds'], $working);
        $violationsAfterPairs = RoomBoundsGap::violatingPairs($boundsAfter, $tilePx);

        // Anything still violating after the pass budget is exhausted but
        // wasn't explicitly recorded above (ran out of passes, not out of
        // options) still belongs in the report.
        $unresolvedKeys = array_map(fn ($u) => $u['roomA'].'|'.$u['roomB'], $unresolved);
        foreach ($violationsAfterPairs as [$i, $j]) {
            $key = $working[$i]['name'].'|'.$working[$j]['name'];
            $altKey = $working[$j]['name'].'|'.$working[$i]['name'];
            if (! in_array($key, $unresolvedKeys, true) && ! in_array($altKey, $unresolvedKeys, true)) {
                $unresolved[] = [
                    'roomA' => $working[$i]['name'],
                    'roomB' => $working[$j]['name'],
                    'reason' => 'Still violating after the maximum number of repack passes.',
                ];
            }
        }

        [$connected, $isolatedRooms] = $this->analyzeConnectivity($working, $tilePx);

        return [
            'rooms' => $working,
            'moves' => $moves,
            'unresolved' => $unresolved,
            'violationsBefore' => $violationsBefore,
            'violationsAfter' => count($violationsAfterPairs),
            'connected' => $connected,
            'isolatedRooms' => $isolatedRooms,
        ];
    }

    /**
     * Attempt to separate rooms $i and $j to satisfy the minimum gap,
     * mutating $working in place and appending to $moves. Returns false if
     * neither shrinking nor pushing could resolve the pair.
     */
    private function resolvePair(
        array &$working,
        int $i,
        int $j,
        int $tilePx,
        ?int $canvasTilesX,
        ?int $canvasTilesY,
        array &$moves
    ): bool {
        $a = $working[$i]['bounds'];
        $b = $working[$j]['bounds'];

        $ax1 = $a['x'];
        $ax2 = $a['x'] + $a['width'];
        $ay1 = $a['y'];
        $ay2 = $a['y'] + $a['height'];
        $bx1 = $b['x'];
        $bx2 = $b['x'] + $b['width'];
        $by1 = $b['y'];
        $by2 = $b['y'] + $b['height'];

        $dxPx = max($bx1 - $ax2, $ax1 - $bx2) * $tilePx;
        $dyPx = max($by1 - $ay2, $ay1 - $by2) * $tilePx;

        // The axis achieving the larger (less negative) separation is the
        // one closest to already satisfying the gap, so it needs the least
        // extra movement.
        $axis = ($dxPx >= $dyPx) ? 'x' : 'y';

        $gapPx = max($dxPx, $dyPx);
        $neededTiles = (int) ceil((RoomBoundsGap::MIN_ROOM_GAP_PX - $gapPx) / $tilePx);
        if ($neededTiles < 1) {
            $neededTiles = 1;
        }

        // Identify which room sits at the lower coordinate along the axis
        // ("low", faces the other room from below/left) vs the higher one
        // ("high", faces the other room from above/right).
        if ($axis === 'x') {
            $lowIsA = $a['x'] <= $b['x'];
        } else {
            $lowIsA = $a['y'] <= $b['y'];
        }
        $lowIdx = $lowIsA ? $i : $j;
        $highIdx = $lowIsA ? $j : $i;

        if ($this->tryShrink($working, $lowIdx, $highIdx, $axis, $neededTiles, $moves)) {
            return true;
        }

        if ($this->tryPush($working, $lowIdx, $highIdx, $axis, $neededTiles, $canvasTilesX, $canvasTilesY, $moves)) {
            return true;
        }

        return false;
    }

    private function tryShrink(array &$working, int $lowIdx, int $highIdx, string $axis, int $neededTiles, array &$moves): bool
    {
        $sizeKey = $axis === 'x' ? 'width' : 'height';

        $sizeLow = $working[$lowIdx]['bounds'][$sizeKey];
        $sizeHigh = $working[$highIdx]['bounds'][$sizeKey];

        // Larger share of the shrink goes to whichever room has more room
        // to spare.
        if ($sizeLow >= $sizeHigh) {
            $shrinkLow = (int) ceil($neededTiles / 2);
            $shrinkHigh = (int) floor($neededTiles / 2);
        } else {
            $shrinkHigh = (int) ceil($neededTiles / 2);
            $shrinkLow = (int) floor($neededTiles / 2);
        }

        $newSizeLow = $sizeLow - $shrinkLow;
        $newSizeHigh = $sizeHigh - $shrinkHigh;

        if ($newSizeLow < RoomBoundsGap::MIN_ROOM_SIDE_TILES || $newSizeHigh < RoomBoundsGap::MIN_ROOM_SIDE_TILES) {
            return false;
        }

        if ($shrinkLow > 0) {
            $before = $working[$lowIdx]['bounds'];
            // Low room shrinks from its far edge (the one facing the other
            // room) — its own x/y anchor doesn't move.
            $working[$lowIdx]['bounds'][$sizeKey] = $newSizeLow;
            $moves[] = [
                'name' => $working[$lowIdx]['name'],
                'from' => $before,
                'to' => $working[$lowIdx]['bounds'],
                'reason' => 'shrink-'.$axis,
            ];
        }

        if ($shrinkHigh > 0) {
            $before = $working[$highIdx]['bounds'];
            $posKey = $axis === 'x' ? 'x' : 'y';
            // High room shrinks from its near edge: move the anchor forward
            // and reduce size by the same amount so its far edge stays put.
            $working[$highIdx]['bounds'][$posKey] += $shrinkHigh;
            $working[$highIdx]['bounds'][$sizeKey] = $newSizeHigh;
            $moves[] = [
                'name' => $working[$highIdx]['name'],
                'from' => $before,
                'to' => $working[$highIdx]['bounds'],
                'reason' => 'shrink-'.$axis,
            ];
        }

        return true;
    }

    private function tryPush(
        array &$working,
        int $lowIdx,
        int $highIdx,
        string $axis,
        int $neededTiles,
        ?int $canvasTilesX,
        ?int $canvasTilesY,
        array &$moves
    ): bool {
        $posKey = $axis === 'x' ? 'x' : 'y';
        $sizeKey = $axis === 'x' ? 'width' : 'height';
        $canvasLimit = $axis === 'x' ? $canvasTilesX : $canvasTilesY;

        // Prefer pushing the smaller of the two rooms (least disruptive);
        // tie-break toward the "high" room.
        $sizeLow = $working[$lowIdx]['bounds'][$sizeKey];
        $sizeHigh = $working[$highIdx]['bounds'][$sizeKey];
        $preferPushHigh = $sizeHigh <= $sizeLow;

        $attempts = $preferPushHigh ? [$highIdx, $lowIdx] : [$lowIdx, $highIdx];

        foreach ($attempts as $mover) {
            $before = $working[$mover]['bounds'];

            if ($mover === $highIdx) {
                $newPos = $before[$posKey] + $neededTiles;
                $fits = $canvasLimit === null || ($newPos + $before[$sizeKey]) <= $canvasLimit;
            } else {
                $newPos = $before[$posKey] - $neededTiles;
                $fits = $newPos >= 0;
            }

            if ($fits) {
                $working[$mover]['bounds'][$posKey] = $newPos;
                $moves[] = [
                    'name' => $working[$mover]['name'],
                    'from' => $before,
                    'to' => $working[$mover]['bounds'],
                    'reason' => 'push-'.$axis,
                ];

                return true;
            }
        }

        return false;
    }

    /**
     * Coarse connectivity smoke-check: computes each room's door portal
     * (same simple side/offset formula the JS engine uses for an explicit
     * door), rasterizes the map at a 16px step marking cells near a wall
     * as blocked (mirroring getAllSolidWallSegments/checkCapsuleWallCollision
     * at low resolution), and flood-fills from the first room's door to
     * confirm every room's door is reachable. Deliberately approximate —
     * a smoke alarm for the repair command's report, not the authority.
     * The Node harness in the verification step runs the real engine code.
     *
     * @return array{0: bool, 1: array<int, string>} [connected, isolatedRoomNames]
     */
    private function analyzeConnectivity(array $rooms, int $tilePx): array
    {
        if (count($rooms) < 2) {
            return [true, []];
        }

        $rasterStep = 16; // mirrors NAV_GRID_STEP in office.blade.php, independent of $tilePx
        $avatarRadius = 12; // mirrors AVATAR_COLLISION_RADIUS

        $doors = [];
        $segments = [];
        $maxX = 0;
        $maxY = 0;

        foreach ($rooms as $room) {
            $b = $room['bounds'];
            $rx = $b['x'] * $tilePx;
            $ry = $b['y'] * $tilePx;
            $rw = $b['width'] * $tilePx;
            $rh = $b['height'] * $tilePx;
            $maxX = max($maxX, $rx + $rw);
            $maxY = max($maxY, $ry + $rh);

            $door = $this->doorPortalFor($b, $rx, $ry, $rw, $rh);
            $doors[] = $door;

            foreach ($this->wallSegmentsFor($rx, $ry, $rw, $rh, $door) as $seg) {
                $segments[] = $seg;
            }
        }

        $canvasPx = max($maxX, $maxY) + $rasterStep * 4;
        $cols = (int) ceil($canvasPx / $rasterStep);
        $rows = $cols;

        // Bound the raster so a pathological input can't blow up memory.
        if ($cols * $rows > 200000) {
            return [true, []]; // skip the smoke check rather than stall the command
        }

        $blocked = [];
        $isBlockedCell = function (int $c, int $r) use (&$blocked, $rasterStep, $segments, $avatarRadius) {
            $key = $c.','.$r;
            if (isset($blocked[$key])) {
                return $blocked[$key];
            }
            $px = $c * $rasterStep + $rasterStep / 2;
            $py = $r * $rasterStep + $rasterStep / 2;
            foreach ($segments as $seg) {
                if ($this->distPointToSegment($px, $py, $seg[0], $seg[1], $seg[2], $seg[3]) < $avatarRadius) {
                    return $blocked[$key] = true;
                }
            }

            return $blocked[$key] = false;
        };

        // BFS from the first room's exit point.
        $startCol = (int) floor($doors[0]['exitX'] / $rasterStep);
        $startRow = (int) floor($doors[0]['exitY'] / $rasterStep);

        $visited = [];
        $queue = [[$startCol, $startRow]];
        $visited[$startCol.','.$startRow] = true;

        while ($queue) {
            [$c, $r] = array_shift($queue);
            foreach ([[1, 0], [-1, 0], [0, 1], [0, -1]] as [$dc, $dr]) {
                $nc = $c + $dc;
                $nr = $r + $dr;
                if ($nc < 0 || $nr < 0 || $nc >= $cols || $nr >= $rows) {
                    continue;
                }
                $key = $nc.','.$nr;
                if (isset($visited[$key]) || $isBlockedCell($nc, $nr)) {
                    continue;
                }
                $visited[$key] = true;
                $queue[] = [$nc, $nr];
            }
        }

        $isolated = [];
        foreach ($rooms as $idx => $room) {
            $col = (int) floor($doors[$idx]['exitX'] / $rasterStep);
            $row = (int) floor($doors[$idx]['exitY'] / $rasterStep);
            if (! isset($visited[$col.','.$row])) {
                $isolated[] = $room['name'];
            }
        }

        return [empty($isolated), $isolated];
    }

    /**
     * Mirrors the simple door-portal formula editor.blade.php previews and
     * the fixed office.blade.php uses for an explicit doorSide/doorOffset.
     */
    private function doorPortalFor(array $bounds, float $rx, float $ry, float $rw, float $rh): array
    {
        $side = strtolower($bounds['doorSide'] ?? 'bottom');
        if (! in_array($side, ['top', 'bottom', 'left', 'right'], true)) {
            $side = 'bottom';
        }
        $offset = is_numeric($bounds['doorOffset'] ?? null) ? (float) $bounds['doorOffset'] : 0.5;

        return match ($side) {
            'top' => ['side' => 'top', 'cx' => $rx + $rw * $offset, 'cy' => $ry, 'exitX' => $rx + $rw * $offset, 'exitY' => $ry - 32],
            'left' => ['side' => 'left', 'cx' => $rx, 'cy' => $ry + $rh * $offset, 'exitX' => $rx - 32, 'exitY' => $ry + $rh * $offset],
            'right' => ['side' => 'right', 'cx' => $rx + $rw, 'cy' => $ry + $rh * $offset, 'exitX' => $rx + $rw + 32, 'exitY' => $ry + $rh * $offset],
            default => ['side' => 'bottom', 'cx' => $rx + $rw * $offset, 'cy' => $ry + $rh, 'exitX' => $rx + $rw * $offset, 'exitY' => $ry + $rh + 32],
        };
    }

    /**
     * The 4 wall segments of a room with a door-width gap cut out of the
     * wall the door sits on. Mirrors getAllSolidWallSegments in
     * office.blade.php, simplified to a single door per room (the only
     * case that exists today).
     *
     * @return array<int, array{0:float,1:float,2:float,3:float}>
     */
    private function wallSegmentsFor(float $rx, float $ry, float $rw, float $rh, array $door): array
    {
        $doorWidth = 56;
        $segments = [];

        $sides = [
            'top' => [$rx, $ry, $rx + $rw, $ry],
            'bottom' => [$rx, $ry + $rh, $rx + $rw, $ry + $rh],
            'left' => [$rx, $ry, $rx, $ry + $rh],
            'right' => [$rx + $rw, $ry, $rx + $rw, $ry + $rh],
        ];

        foreach ($sides as $side => [$x1, $y1, $x2, $y2]) {
            if ($side !== $door['side']) {
                $segments[] = [$x1, $y1, $x2, $y2];

                continue;
            }

            $horizontal = $side === 'top' || $side === 'bottom';
            if ($horizontal) {
                $openStart = max($x1, $door['cx'] - $doorWidth / 2);
                $openEnd = min($x2, $door['cx'] + $doorWidth / 2);
                if ($openStart > $x1 + 1) {
                    $segments[] = [$x1, $y1, $openStart, $y1];
                }
                if ($x2 > $openEnd + 1) {
                    $segments[] = [$openEnd, $y1, $x2, $y1];
                }
            } else {
                $openStart = max($y1, $door['cy'] - $doorWidth / 2);
                $openEnd = min($y2, $door['cy'] + $doorWidth / 2);
                if ($openStart > $y1 + 1) {
                    $segments[] = [$x1, $y1, $x1, $openStart];
                }
                if ($y2 > $openEnd + 1) {
                    $segments[] = [$x1, $openEnd, $x1, $y2];
                }
            }
        }

        return $segments;
    }

    private function distPointToSegment(float $px, float $py, float $x1, float $y1, float $x2, float $y2): float
    {
        $dx = $x2 - $x1;
        $dy = $y2 - $y1;
        $lenSq = $dx * $dx + $dy * $dy;
        if ($lenSq == 0.0) {
            return sqrt(($px - $x1) ** 2 + ($py - $y1) ** 2);
        }
        $t = max(0, min(1, (($px - $x1) * $dx + ($py - $y1) * $dy) / $lenSq));
        $projX = $x1 + $t * $dx;
        $projY = $y1 + $t * $dy;

        return sqrt(($px - $projX) ** 2 + ($py - $projY) ** 2);
    }
}
