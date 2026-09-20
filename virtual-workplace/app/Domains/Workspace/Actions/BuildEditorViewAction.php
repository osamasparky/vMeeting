<?php

namespace App\Domains\Workspace\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Workspace\Models\FurnitureCategory;
use App\Domains\Workspace\Models\FurnitureItem;
use App\Domains\Workspace\Services\AiMapGeneratorService;
use Illuminate\Support\Facades\Cache;

/**
 * Resolves the target floor/map (creating either if this organization has
 * none yet) and builds everything the map editor view needs. Extracted
 * from OfficeController::editor(). See Architecture Audit §8/§15.
 */
class BuildEditorViewAction
{
    public function execute(User $user, OrganizationMember $membership, ?string $requestedOfficeId): array
    {
        $organization = $membership->organization;

        if ($requestedOfficeId) {
            $floor = $organization->floors()->where('id', $requestedOfficeId)->first() ?? $organization->defaultOffice() ?? $organization->floors()->first();
        } else {
            $floor = $organization->defaultOffice() ?? $organization->floors()->first();
        }

        if (! $floor) {
            $floor = $organization->floors()->create([
                'name' => $organization->name.' HQ',
                'is_default' => true,
                'order' => 1,
            ]);
        }

        $map = $organization->maps()->where('floor_id', $floor->id)->where('status', 'published')->latest('published_at')->first()
            ?? $organization->maps()->where('floor_id', $floor->id)->latest()->first();

        if (! $map) {
            $map = $organization->maps()->create([
                'floor_id' => $floor->id,
                'name' => $floor->name.' Blueprint',
                'status' => 'published',
                'version' => 1,
                'width' => 75,
                'height' => 45,
                'tile_size' => 16,
                'layout_data' => [
                    'theme' => 'open_spatial_blueprint',
                    'wall_sign_text' => strtoupper($floor->name),
                    'background_width' => 1200,
                    'background_height' => 708,
                ],
                'published_at' => now(),
            ]);
        }

        $map->load(['rooms', 'zones', 'objects', 'versions']);
        $floors = $organization->floors()->orderBy('is_default', 'desc')->orderBy('name', 'asc')->get();

        $furnitureCategories = Cache::remember('furniture_categories_with_items', 86400, function () {
            return FurnitureCategory::with('items')
                ->orderBy('order', 'asc')
                ->get();
        });

        $furnitureItems = Cache::remember('furniture_catalog_active', 86400, function () {
            return FurnitureItem::where('is_active', true)->get();
        });

        $plan = $organization->plan;
        $aiStyles = (new AiMapGeneratorService)->getStyles();

        return [
            'user' => $user,
            'organization' => $organization,
            'floor' => $floor,
            'floors' => $floors,
            'map' => $map,
            'furnitureCategories' => $furnitureCategories,
            'furnitureItems' => $furnitureItems,
            'plan' => $plan,
            'aiStyles' => $aiStyles,
        ];
    }
}
