<?php

namespace App\Console\Commands;

use App\Domains\Projects\Models\Timesheet;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * One-off repair for durations written while Carbon 3's signed diffInSeconds()
 * was called in the wrong order: timers were stored as 1 second and closed
 * attendance sessions as 0. Recomputes duration_seconds from ended_at - started_at
 * wherever the stored value is shorter than the real span.
 */
class RepairTimeDurationsCommand extends Command
{
    protected $signature = 'time:repair-durations {--dry-run : Report what would change without writing}';

    protected $description = 'Recompute time entry and attendance durations that were truncated by the reversed Carbon diff bug';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $entries = $this->repairTable('time_entries', $dryRun);
        $sessions = $this->repairTable('attendance_sessions', $dryRun);

        $sheets = 0;
        if (! $dryRun && $entries['timesheet_ids'] !== []) {
            Timesheet::withoutGlobalScopes()->whereIn('id', $entries['timesheet_ids'])->get()
                ->each(function (Timesheet $sheet) use (&$sheets) {
                    $sheet->recalculateTotals();
                    $sheets++;
                });
        }

        $this->table(['Table', 'Rows repaired', 'Hours restored', 'Skipped (start after end)'], [
            ['time_entries', $entries['rows'], round($entries['seconds'] / 3600, 2), $entries['skewed']],
            ['attendance_sessions', $sessions['rows'], round($sessions['seconds'] / 3600, 2), $sessions['skewed']],
        ]);
        $this->info($dryRun
            ? 'Dry run — nothing was written.'
            : "Done. Recalculated totals on {$sheets} timesheet(s).");

        return self::SUCCESS;
    }

    /**
     * @return array{rows: int, skewed: int, seconds: int, timesheet_ids: list<string>}
     */
    private function repairTable(string $table, bool $dryRun): array
    {
        $rows = 0;
        $skewed = 0;
        $seconds = 0;
        $timesheetIds = [];
        $hasTimesheet = $table === 'time_entries';

        DB::table($table)
            ->whereNotNull('started_at')
            ->whereNotNull('ended_at')
            ->orderBy('id')
            ->chunk(500, function ($chunk) use ($table, $dryRun, $hasTimesheet, &$rows, &$skewed, &$seconds, &$timesheetIds) {
                foreach ($chunk as $row) {
                    $real = (int) Carbon::parse($row->started_at)->diffInSeconds(Carbon::parse($row->ended_at));
                    if ($real < 0) {
                        // Start after end (legacy timezone-skewed rows): no trustworthy span.
                        $skewed++;

                        continue;
                    }

                    $stored = (int) ($row->duration_seconds ?? 0);

                    if ($real <= $stored) {
                        continue;
                    }

                    $rows++;
                    $seconds += $real - $stored;
                    if ($hasTimesheet && $row->timesheet_id) {
                        $timesheetIds[$row->timesheet_id] = $row->timesheet_id;
                    }

                    if (! $dryRun) {
                        DB::table($table)->where('id', $row->id)->update(['duration_seconds' => $real]);
                    }
                }
            });

        return ['rows' => $rows, 'skewed' => $skewed, 'seconds' => $seconds, 'timesheet_ids' => array_values($timesheetIds)];
    }
}
