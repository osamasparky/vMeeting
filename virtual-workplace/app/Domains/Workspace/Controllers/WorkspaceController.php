<?php

namespace App\Domains\Workspace\Controllers;

use App\Domains\Tenancy\Models\Organization;
use App\Domains\Workspace\Actions\CreateFloorAction;
use App\Domains\Workspace\Actions\CreateMapAction;
use App\Domains\Workspace\Actions\CreateRoomAction;
use App\Domains\Workspace\Actions\CreateZoneAction;
use App\Domains\Workspace\Actions\PublishMapAction;
use App\Domains\Workspace\Actions\SyncMapObjectsAction;
use App\Domains\Workspace\Models\Floor;
use App\Domains\Workspace\Models\Map;
use App\Domains\Workspace\Models\Room;
use App\Domains\Workspace\Models\Zone;
use App\Domains\Workspace\Requests\CreateFloorRequest;
use App\Domains\Workspace\Requests\CreateMapRequest;
use App\Domains\Workspace\Requests\CreateRoomRequest;
use App\Domains\Workspace\Requests\CreateZoneRequest;
use App\Domains\Workspace\Requests\SyncMapObjectsRequest;
use App\Domains\Workspace\Requests\UpdateMapRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class WorkspaceController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    // FLOORS
    // ══════════════════════════════════════════════════════════════

    public function listFloors(Organization $organization): JsonResponse
    {
        $floors = $organization->floors()
            ->with(['maps' => function ($q) {
                $q->select('id', 'floor_id', 'organization_id', 'name', 'status', 'version', 'width', 'height');
            }])
            ->orderBy('order')
            ->get();

        return response()->json([
            'floors' => $floors,
        ]);
    }

    public function createFloor(CreateFloorRequest $request, Organization $organization, CreateFloorAction $action): JsonResponse
    {
        $floor = $action->execute($request->validated(), $organization);

        return response()->json([
            'message' => 'Floor created successfully.',
            'floor' => $floor,
        ], 201);
    }

    // ══════════════════════════════════════════════════════════════
    // MAPS
    // ══════════════════════════════════════════════════════════════

    public function listMaps(Organization $organization): JsonResponse
    {
        $maps = $organization->maps()
            ->with('floor:id,name,order')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'maps' => $maps,
        ]);
    }

    public function createMap(CreateMapRequest $request, Organization $organization, CreateMapAction $action): JsonResponse
    {
        $map = $action->execute($request->validated(), $organization);

        return response()->json([
            'message' => 'Map created successfully.',
            'map' => $map,
        ], 201);
    }

    public function showMap(Organization $organization, Map $map): JsonResponse
    {
        // Tenant check
        if ($map->organization_id !== $organization->id) {
            return response()->json(['message' => 'Unauthorized map access.'], 403);
        }

        $map->load([
            'floor',
            'rooms',
            'zones',
            'objects',
            'versions:id,map_id,version,published_by,created_at',
        ]);

        return response()->json([
            'map' => $map,
        ]);
    }

    public function updateMap(UpdateMapRequest $request, Organization $organization, Map $map): JsonResponse
    {
        if ($map->organization_id !== $organization->id) {
            return response()->json(['message' => 'Unauthorized map access.'], 403);
        }

        $map->update($request->validated());

        return response()->json([
            'message' => 'Map updated successfully.',
            'map' => $map->fresh(['floor', 'rooms', 'zones', 'objects']),
        ]);
    }

    public function publishMap(Organization $organization, Map $map, PublishMapAction $action): JsonResponse
    {
        if ($map->organization_id !== $organization->id) {
            return response()->json(['message' => 'Unauthorized map access.'], 403);
        }

        $publishedMap = $action->execute($map, Auth::user());

        return response()->json([
            'message' => 'Map published successfully.',
            'map' => $publishedMap,
        ]);
    }

    public function getMapVersions(Organization $organization, Map $map): JsonResponse
    {
        if ($map->organization_id !== $organization->id) {
            return response()->json(['message' => 'Unauthorized map access.'], 403);
        }

        $versions = $map->versions()
            ->with('publishedByUser:id,name,email')
            ->get();

        return response()->json([
            'versions' => $versions,
        ]);
    }

    public function uploadBackground(\Illuminate\Http\Request $request, Organization $organization, Map $map): JsonResponse
    {
        if ($map->organization_id !== $organization->id) {
            return response()->json(['message' => 'Unauthorized map access.'], 403);
        }

        $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ]);

        $file = $request->file('image');
        $filename = 'floorplan_' . $map->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('maps', $filename, 'public');
        $url = \Illuminate\Support\Facades\Storage::url($path);

        $layoutData = $map->layout_data ?? [];
        $layoutData['background_image_url'] = $url;

        $imageSize = @getimagesize($file->getRealPath());
        if ($imageSize) {
            $layoutData['background_width'] = $imageSize[0];
            $layoutData['background_height'] = $imageSize[1];
        }

        $map->update([
            'layout_data' => $layoutData,
        ]);

        return response()->json([
            'message' => 'Background floorplan uploaded successfully.',
            'image_url' => $url,
            'map' => $map->fresh(['floor', 'rooms', 'zones', 'objects']),
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // ROOMS
    // ══════════════════════════════════════════════════════════════

    public function createRoom(CreateRoomRequest $request, Organization $organization, CreateRoomAction $action): JsonResponse
    {
        $room = $action->execute($request->validated(), $organization);

        return response()->json([
            'message' => 'Room created successfully.',
            'room' => $room,
        ], 201);
    }

    public function updateRoom(\Illuminate\Http\Request $request, Organization $organization, Room $room): JsonResponse
    {
        if ($room->organization_id !== $organization->id) {
            return response()->json(['message' => 'Unauthorized room access.'], 403);
        }

        $room->update($request->only([
            'name',
            'type',
            'access_mode',
            'capacity',
            'color',
            'bounds',
            'metadata'
        ]));

        return response()->json([
            'message' => 'Room updated successfully.',
            'room' => $room->fresh(),
        ]);
    }

    public function deleteRoom(Organization $organization, Room $room): JsonResponse
    {
        if ($room->organization_id !== $organization->id) {
            return response()->json(['message' => 'Unauthorized room access.'], 403);
        }

        $room->delete();

        return response()->json([
            'message' => 'Room deleted successfully.',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // ZONES
    // ══════════════════════════════════════════════════════════════

    public function createZone(CreateZoneRequest $request, Organization $organization, CreateZoneAction $action): JsonResponse
    {
        $zone = $action->execute($request->validated(), $organization);

        return response()->json([
            'message' => 'Zone created successfully.',
            'zone' => $zone,
        ], 201);
    }

    public function deleteZone(Organization $organization, Zone $zone): JsonResponse
    {
        if ($zone->organization_id !== $organization->id) {
            return response()->json(['message' => 'Unauthorized zone access.'], 403);
        }

        $zone->delete();

        return response()->json([
            'message' => 'Zone deleted successfully.',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // MAP OBJECTS
    // ══════════════════════════════════════════════════════════════

    public function syncObjects(SyncMapObjectsRequest $request, Organization $organization, Map $map, SyncMapObjectsAction $action): JsonResponse
    {
        if ($map->organization_id !== $organization->id) {
            return response()->json(['message' => 'Unauthorized map access.'], 403);
        }

        $objects = $action->execute($map, $request->validated()['objects']);

        return response()->json([
            'message' => 'Map objects synced successfully.',
            'objects' => $objects,
        ]);
    }
}
