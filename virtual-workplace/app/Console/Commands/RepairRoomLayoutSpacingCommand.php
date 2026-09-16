<?php

namespace App\Console\Commands;

use App\Domains\Workspace\Models\Map;
use App\Domains\Workspace\Models\OfficeTemplate;
use App\Domains\Workspace\Models\Room;
use App\Domains\Workspace\Services\RoomLayoutSpacingService;
use App\Domains\Workspace\Support\RoomBoundsGap;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * One-off repair tool for room layouts whose gaps are too narrow for the
 * avatar to walk through (see RoomLayoutSpacingService / RoomBoundsGap).
 * Dry-run by default; nothing is written without --apply.
 */
class RepairRoomLayoutSpacingCommand extends Command
{
    protected $signature = 'rooms:repair-spacing
                            {--apply : Persist changes (default is dry-run/report-only)}
                            {--templates : Include office_templates.rooms_data}
                            {--maps : Include provisioned rooms grouped by map_id}
                            {--map= : Restrict to one map id}
                            {--org= : Restrict to one organization id}';

    protected $description = 'Detect and repair room layouts whose gaps are too narrow for the avatar to walk through';

    private array $backup = [];

    public function handle(RoomLayoutSpacingService $service): int
    {
        $apply = (bool) $this->option('apply');
        $doTemplates = (bool) $this->option('templates');
        $doMaps = (bool) $this->option('maps');

        if (! $doTemplates && ! $doMaps) {
            $doTemplates = true;
            $doMaps = true;
        }

        if ($apply && app()->environment('production')) {
            if (! $this->confirm('You are about to modify room layouts in PRODUCTION. Continue?', false)) {
                $this->warn('Aborted.');

                return self::FAILURE;
            }
        }

        $ok = true;

        if ($doTemplates) {
            $ok = $this->repairTemplates($service, $apply) && $ok;
        }

        if ($doMaps) {
            $ok = $this->repairMaps($service, $apply) && $ok;
        }

        if ($apply && ! empty($this->backup)) {
            $path = 'room-spacing-backup-'.now()->format('Ymd_His').'.json';
            Storage::disk('local')->put($path, json_encode($this->backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->info("Backup of previous bounds written to storage/app/{$path}");
        }

        $this->line('');
        if (! $apply) {
            $this->comment('Dry run only — no changes written. Re-run with --apply to persist.');
        }

        if (! $ok) {
            $this->error('One or more targets still have unresolved spacing violations. See table(s) above.');
        }

        return $ok ? self::SUCCESS : self::FAILURE;
    }

    private function repairTemplates(RoomLayoutSpacingService $service, bool $apply): bool
    {
        $ok = true;
        $tilePx = RoomBoundsGap::CANONICAL_TILE_PX;

        foreach (OfficeTemplate::all() as $template) {
            $rooms = $template->rooms_data ?? [];
            if (empty($rooms)) {
                continue;
            }

            $report = $service->repack($rooms, $tilePx, $template->width, $template->height);

            $this->printReport("Template \"{$template->name}\" ({$template->id})  tile={$tilePx}px (canonical)", $report);

            if (! empty($report['moves']) && $apply) {
                $this->backup[] = [
                    'type' => 'template',
                    'id' => $template->id,
                    'rooms_data' => $rooms,
                ];

                DB::transaction(function () use ($template, $report) {
                    $template->rooms_data = $report['rooms'];
                    $template->save();
                });
            }

            if ($report['violationsAfter'] > 0) {
                $ok = false;
            }
        }

        return $ok;
    }

    private function repairMaps(RoomLayoutSpacingService $service, bool $apply): bool
    {
        $ok = true;

        $query = Map::query()->with('rooms');
        if ($mapId = $this->option('map')) {
            $query->where('id', $mapId);
        }
        if ($orgId = $this->option('org')) {
            $query->where('organization_id', $orgId);
        }

        foreach ($query->get() as $map) {
            $roomModels = $map->rooms;
            if ($roomModels->isEmpty()) {
                continue;
            }

            $tilePx = $map->tile_size ?: RoomBoundsGap::CANONICAL_TILE_PX;
            [$canvasTilesX, $canvasTilesY] = $this->canvasTilesFor($map, $tilePx);

            $rooms = $roomModels->map(fn (Room $r) => ['name' => $r->name, 'bounds' => $r->bounds])->values()->all();

            $report = $service->repack($rooms, $tilePx, $canvasTilesX, $canvasTilesY);

            $this->printReport("Map \"{$map->name}\" ({$map->id})  tile={$tilePx}px", $report);

            if (! empty($report['moves']) && $apply) {
                DB::transaction(function () use ($roomModels, $report) {
                    foreach ($roomModels->values() as $idx => $roomModel) {
                        $newBounds = $report['rooms'][$idx]['bounds'];
                        if ($newBounds !== $roomModel->bounds) {
                            $this->backup[] = [
                                'type' => 'room',
                                'id' => $roomModel->id,
                                'map_id' => $roomModel->map_id,
                                'bounds' => $roomModel->bounds,
                            ];
                            $roomModel->bounds = $newBounds;
                            $roomModel->save();
                        }
                    }
                });
            }

            if ($report['violationsAfter'] > 0) {
                $ok = false;
            }
        }

        return $ok;
    }

    private function canvasTilesFor(Map $map, int $tilePx): array
    {
        $bgW = $map->layout_data['background_width'] ?? null;
        $bgH = $map->layout_data['background_height'] ?? null;

        $canvasTilesX = ($bgW && $bgW >= 500) ? (int) floor($bgW / $tilePx) : $map->width;
        $canvasTilesY = ($bgH && $bgH >= 500) ? (int) floor($bgH / $tilePx) : $map->height;

        return [$canvasTilesX, $canvasTilesY];
    }

    private function printReport(string $heading, array $report): void
    {
        $this->line('');
        $this->info($heading);
        $this->line("  violations before: {$report['violationsBefore']}    after: {$report['violationsAfter']}");
        $this->line('  connectivity (approximate, assumes default bottom-side doors): '
            .($report['connected'] ? 'OK' : 'possibly broken — '.implode(', ', $report['isolatedRooms']).' (the live JS auto-picks a smarter side; not authoritative — see the Node harness)'));

        if (! empty($report['moves'])) {
            $rows = [];
            foreach ($report['moves'] as $move) {
                $rows[] = [
                    $move['name'],
                    $this->formatBounds($move['from']),
                    $this->formatBounds($move['to']),
                    $move['reason'],
                ];
            }
            $this->table(['Room', 'Before', 'After', 'Action'], $rows);
        } else {
            $this->line('  no changes needed.');
        }

        if (! empty($report['unresolved'])) {
            $this->warn('  unresolved:');
            foreach ($report['unresolved'] as $u) {
                $this->warn("    - {$u['roomA']} vs {$u['roomB']}: {$u['reason']}");
            }
        }
    }

    private function formatBounds(array $b): string
    {
        return "{$b['x']},{$b['y']} {$b['width']}x{$b['height']}";
    }
}
