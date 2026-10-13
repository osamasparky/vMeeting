<?php

namespace App\Domains\People\Services;

/**
 * Interval arithmetic on [start, end) pairs of unix timestamps.
 *
 * Attendance and task time are both lists of intervals. Adding them up naively
 * double-counts wherever they overlap (two office sessions open at once, a task
 * timer running while in the office), so totals go through union() instead.
 */
class TimeIntervals
{
    /** Clip each interval to [from, to); drops the ones that fall outside. */
    public static function clip(array $intervals, int $from, int $to): array
    {
        $out = [];
        foreach ($intervals as [$start, $end]) {
            $start = max($start, $from);
            $end = min($end, $to);
            if ($end > $start) {
                $out[] = [$start, $end];
            }
        }

        return $out;
    }

    /** Merge overlapping intervals; the result is sorted and disjoint. */
    public static function merge(array $intervals): array
    {
        $intervals = array_values(array_filter($intervals, fn ($i) => $i[1] > $i[0]));
        usort($intervals, fn ($a, $b) => $a[0] <=> $b[0]);

        $merged = [];
        foreach ($intervals as [$start, $end]) {
            $last = count($merged) - 1;
            if ($last >= 0 && $start <= $merged[$last][1]) {
                $merged[$last][1] = max($merged[$last][1], $end);
            } else {
                $merged[] = [$start, $end];
            }
        }

        return $merged;
    }

    /** Seconds covered by at least one interval. */
    public static function union(array $intervals): int
    {
        return array_sum(array_map(fn ($i) => $i[1] - $i[0], self::merge($intervals)));
    }

    /** Seconds covered by both lists. */
    public static function overlap(array $a, array $b): int
    {
        return max(0, self::union($a) + self::union($b) - self::union(array_merge($a, $b)));
    }
}
