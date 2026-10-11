<?php

namespace App\Console\Commands;

use App\Domains\Tenancy\Models\Plan;
use App\Domains\Workspace\Models\OfficeTemplate;
use App\Domains\Workspace\Support\MapGeometry;
use App\Domains\Workspace\Support\RoomBoundsGap;
use App\Domains\Workspace\Templates\OfficeTemplateDesigner;
use App\Domains\Workspace\Templates\TemplateFloorRenderer;
use Illuminate\Console\Command;

/**
 * Replaces the Free and Business office templates with the designed layouts: rooms, catalogue
 * furniture and a floor image. Companies are not touched; the super admin pushes a template to
 * companies from the template page ("Sync").
 */
class RebuildOfficeTemplatesCommand extends Command
{
    protected $signature = 'office-templates:rebuild
                            {--plan=* : free and/or business (default: both)}
                            {--apply : Write the templates (default: check and report only)}';

    protected $description = 'Rebuild the Free and Business office templates from the catalogue furniture';

    public function handle(): int
    {
        $plans = $this->option('plan') ?: ['free', 'business'];
        $designer = new OfficeTemplateDesigner;
        $tile = OfficeTemplateDesigner::TILE;
        $ok = true;

        foreach ($plans as $slug) {
            if (! in_array($slug, ['free', 'business'], true)) {
                $this->error("Unknown plan: {$slug}");

                return self::FAILURE;
            }
            $design = $designer->design($slug);
            $layout = ['background_width' => $design['width'] * $tile, 'background_height' => $design['height'] * $tile];

            // The same rules the editor enforces.
            $bounds = array_column($design['rooms'], 'bounds');
            $outside = array_filter($bounds, fn ($b) => ! MapGeometry::roomFitsMap($b, $layout, $tile));
            $tooClose = RoomBoundsGap::violatingPairs($bounds, $tile);
            $this->line(sprintf('%s: %d rooms, %d pieces of furniture, %dx%d tiles', $slug, count($design['rooms']), count($design['objects']), $design['width'], $design['height']));
            if ($outside || $tooClose) {
                $ok = false;
                $this->error(sprintf('  %d rooms outside the map, %d pairs too close', count($outside), count($tooClose)));

                continue;
            }
            // A half-furnished office is worse than the old one: seed the catalogue first.
            if ($designer->missing) {
                $ok = false;
                $this->error('  missing from the furniture catalogue: '.implode(', ', array_values($designer->missing)));

                continue;
            }

            if (! $this->option('apply')) {
                continue;
            }

            $url = (new TemplateFloorRenderer)->render($design, public_path("images/maps/templates/{$slug}-office.jpg"));
            $url .= '?v='.substr(md5_file(public_path(ltrim($url, '/'))), 0, 8);
            $plan = Plan::where('slug', $slug)->first();
            $template = OfficeTemplate::getForPlan($plan ?? new Plan(['slug' => $slug]));

            $template->forceFill([
                'name' => $design['name'],
                'description' => $design['description'],
                'plan_id' => $plan?->id ?? $template->plan_id,
                'plan_slug' => $slug,
                'background_image_url' => $url,
                'width' => $design['width'],
                'height' => $design['height'],
                'tile_size' => $tile,
                'layout_data' => $layout + ['background_image_url' => $url, 'walls' => [], 'obstacles' => [], 'theme' => 'ulaspace_template'],
                'rooms_data' => $design['rooms'],
                'objects_data' => $design['objects'],
                'is_active' => true,
            ])->save();
            $this->info("  saved template {$template->slug} → {$url}");
        }

        if (! $this->option('apply')) {
            $this->line('Check only — re-run with --apply to write the templates.');
        }

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
