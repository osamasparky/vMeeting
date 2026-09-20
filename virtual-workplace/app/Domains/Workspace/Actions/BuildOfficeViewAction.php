<?php

namespace App\Domains\Workspace\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Identity\Services\RealtimeTokenService;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Tenancy\Models\OrganizationSetting;
use App\Domains\Workspace\Models\Floor;
use App\Domains\Workspace\Models\FurnitureItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Given an already-access-checked floor, builds everything the `office`
 * view needs: the published map (auto-creating one if none exists yet),
 * which rooms this user may enter, their realtime token, and the cached
 * furniture catalog. Extracted from OfficeController::office(). See
 * Architecture Audit §8/§15.
 */
class BuildOfficeViewAction
{
    public function execute(
        User $user,
        Organization $organization,
        OrganizationMember $membership,
        Floor $floor,
        bool $isFullAdmin,
        Collection $allOffices,
        Collection $userAllowedOffices,
        RealtimeTokenService $tokenService
    ): array {
        $map = $organization->maps()->where('floor_id', $floor->id)->where('status', 'published')->latest('published_at')->first()
            ?? $organization->maps()->where('floor_id', $floor->id)->latest()->first();

        if (! $map) {
            // Auto generate initial map for this office
            $map = $organization->maps()->create([
                'floor_id' => $floor->id,
                'name' => $floor->name.' Blueprint',
                'status' => 'published',
                'version' => 1,
                'width' => 32,
                'height' => 26,
                'tile_size' => 16,
                'layout_data' => [
                    'theme' => 'open_spatial_blueprint',
                    'wall_sign_text' => strtoupper($floor->name),
                ],
                'published_at' => now(),
            ]);
        }

        $map->load(['rooms', 'zones', 'objects']);

        // Determine allowed room IDs for this user
        $userAllowedRoomIds = [];
        if ($isFullAdmin) {
            $userAllowedRoomIds = $map->rooms->pluck('id')->toArray();
        } else {
            $assignedRoomIds = $membership->rooms()->pluck('rooms.id')->toArray();
            if (count($assignedRoomIds) > 0) {
                $userAllowedRoomIds = $assignedRoomIds;
            } else {
                // If no specific room restrictions assigned, allow all public rooms in this map
                $userAllowedRoomIds = $map->rooms->where('access_mode', '!=', 'private')->pluck('id')->toArray();
            }
        }

        $realtimeToken = $tokenService->generateToken($user, $organization);
        $wsUrl = env('REALTIME_WS_URL', env('VITE_REALTIME_WS_URL', 'ws://127.0.0.1:8080'));

        $furnitureItems = Cache::remember('furniture_catalog_active', 86400, function () {
            return FurnitureItem::where('is_active', true)->get();
        });

        $attendancePolicy = optional($organization->settings)->getAttendancePolicy()
            ?? OrganizationSetting::getAttendancePolicy();

        return [
            'user' => $user,
            'organization' => $organization,
            'membership' => $membership,
            'floor' => $floor,
            'map' => $map,
            'allOffices' => $allOffices,
            'userAllowedOffices' => $userAllowedOffices,
            'userAllowedRoomIds' => $userAllowedRoomIds,
            'realtimeToken' => $realtimeToken,
            'wsUrl' => $wsUrl,
            'furnitureItems' => $furnitureItems,
            'attendancePolicy' => $attendancePolicy,
        ];
    }
}
