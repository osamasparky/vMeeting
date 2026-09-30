<?php

namespace Tests\Unit;

use App\Domains\Workspace\Support\RoomBoundsGap;
use PHPUnit\Framework\TestCase;

class RoomBoundsGapTest extends TestCase
{
    private const TILE = RoomBoundsGap::CANONICAL_TILE_PX;

    private function room(int $x, int $y = 0, int $w = 10, int $h = 10): array
    {
        return ['x' => $x, 'y' => $y, 'width' => $w, 'height' => $h];
    }

    public function test_rooms_sharing_a_wall_are_allowed(): void
    {
        $this->assertTrue(RoomBoundsGap::satisfiesMinGap($this->room(0), $this->room(10), self::TILE));
    }

    public function test_rooms_touching_only_at_a_corner_are_allowed(): void
    {
        $this->assertTrue(RoomBoundsGap::satisfiesMinGap($this->room(0, 0), $this->room(10, 10), self::TILE));
    }

    public function test_a_gap_too_narrow_to_walk_through_is_rejected(): void
    {
        $this->assertFalse(RoomBoundsGap::satisfiesMinGap($this->room(0), $this->room(11), self::TILE));
        $this->assertFalse(RoomBoundsGap::satisfiesMinGap($this->room(0), $this->room(12), self::TILE));
    }

    public function test_a_full_corridor_is_allowed(): void
    {
        $this->assertTrue(RoomBoundsGap::satisfiesMinGap($this->room(0), $this->room(13), self::TILE));
    }

    public function test_overlapping_rooms_are_rejected(): void
    {
        $this->assertFalse(RoomBoundsGap::satisfiesMinGap($this->room(0), $this->room(9), self::TILE));
    }

    public function test_violating_pairs_skips_rooms_that_share_a_wall(): void
    {
        $pairs = RoomBoundsGap::violatingPairs([$this->room(0), $this->room(10), $this->room(21)], self::TILE);

        $this->assertSame([[1, 2]], array_map(fn ($p) => [$p[0], $p[1]], $pairs));
    }
}
