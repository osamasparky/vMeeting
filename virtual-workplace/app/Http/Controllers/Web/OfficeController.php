<?php

namespace App\Http\Controllers\Web;

use App\Domains\Administration\Models\AuditLog;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Identity\Services\RealtimeTokenService;
use App\Domains\Notifications\Services\NotificationService;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Workspace\Models\Floor;
use App\Domains\Workspace\Models\FurnitureItem;
use App\Domains\Workspace\Models\Map;
use App\Domains\Workspace\Models\Room;
use App\Domains\Workspace\Services\AiMapGeneratorService;
use App\Http\Controllers\Controller;
use App\Services\FileUploadService;
use Database\Seeders\BlueprintOfficeSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OfficeController extends Controller
{
    /**
     * Show the interactive Virtual Office floor with multi-branch switcher and room access guard.
     */
    public function office(\App\Domains\Identity\Services\RealtimeTokenService $tokenService)
    {
        $user = Auth::user();

        $membership = OrganizationMember::where('user_id', $user->id)
            ->whereIn('status', ['active', 'invited'])
            ->with(['organization.plan', 'role.permissions', 'offices', 'rooms'])
            ->first();

        if (!$membership) {
            return redirect()->route('login')->with('error', 'No active organization found.');
        }

        if ($membership->status === 'invited') {
            $membership->update(['status' => 'active']);
        }

        $organization = $membership->organization;
        $this->ensureDefaultWorkspace($organization);

        $requestedOfficeId = request('office');
        $allOffices = $organization->offices()->with(['rooms', 'activeMap'])->get();

        // Determine user allowed offices
        $isFullAdmin = $membership->role?->slug === 'company_admin' || $membership->role?->slug === 'super_admin' || $user->isSuperAdmin();
        $userAllowedOffices = $isFullAdmin || $membership->offices()->count() === 0
            ? $allOffices
            : $membership->offices()->with(['rooms', 'activeMap'])->get();

        if ($requestedOfficeId) {
            $floor = $organization->floors()->find($requestedOfficeId);
            if (!$floor) {
                return redirect()->route('office')->with('error', __('Requested office branch not found.'));
            }
            if (!$membership->hasOfficeAccess($floor->id)) {
                return redirect()->route('dashboard')->with('error', __('You do not have access permission to enter this office branch (ليس لديك صلاحية لدخول هذا الفرع).'));
            }
        } else {
            // UNIFIED: Use the organization's primary default floor for all members to ensure they meet in the same office
            $orgDefaultFloor = $allOffices->firstWhere('is_default', true) ?: $allOffices->first();
            $floor = ($orgDefaultFloor && $membership->hasOfficeAccess($orgDefaultFloor->id))
                ? $orgDefaultFloor
                : ($userAllowedOffices->firstWhere('is_default', true) ?: $userAllowedOffices->first() ?: $organization->floors()->first());
        }

        if (!$floor) {
            return redirect()->route('dashboard')->with('error', __('No active office available.'));
        }

        $map = $organization->maps()->where('floor_id', $floor->id)->where('status', 'published')->latest('published_at')->first()
            ?? $organization->maps()->where('floor_id', $floor->id)->latest()->first();

        if (!$map) {
            // Auto generate initial map for this office
            $map = $organization->maps()->create([
                'floor_id' => $floor->id,
                'name' => $floor->name . ' Blueprint',
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
        $wsUrl = env('REALTIME_WS_URL', 'ws://127.0.0.1:8080');

        $furnitureItems = Cache::remember('furniture_catalog_active', 86400, function () {
            return \App\Domains\Workspace\Models\FurnitureItem::where('is_active', true)->get();
        });

        return view('office', compact('user', 'organization', 'membership', 'floor', 'map', 'allOffices', 'userAllowedOffices', 'userAllowedRoomIds', 'realtimeToken', 'wsUrl', 'furnitureItems'));
    }

    /**
     * Generate an AI-powered Office Blueprint & Spatial Rooms layout.
     */
    public function generateAiOffice(Request $request, \App\Domains\Workspace\Services\AiMapGeneratorService $aiMapService)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with(['organization.plan', 'role.permissions'])->first();
        if (!$membership) abort(403);

        if (!$membership->hasPermission('maps.manage') && $membership->role?->slug !== 'company_admin' && !$user->isSuperAdmin()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => __('Unauthorized: only organization admins can generate workplace blueprints.')], 403);
            }
            abort(403, 'Unauthorized.');
        }

        $organization = $membership->organization;

        $validated = $request->validate([
            'style' => ['nullable', 'string', 'in:modern_glass_luxury,scandinavian_minimalist,silicon_valley_tech,executive_corporate,warm_wood_botanical,cyberpunk_neon'],
            'meeting_rooms' => ['nullable', 'integer', 'min:0', 'max:8'],
            'office_rooms' => ['nullable', 'integer', 'min:1', 'max:10'],
            'desks_per_office' => ['nullable', 'integer', 'min:1', 'max:20'],
            'thinking_rooms' => ['nullable', 'integer', 'min:0', 'max:6'],
            'rest_areas' => ['nullable', 'integer', 'min:0', 'max:4'],
            'theaters' => ['nullable', 'integer', 'min:0', 'max:2'],
            'floor_name' => ['nullable', 'string', 'max:100'],
            'target_floor_id' => ['nullable', 'string'],
        ]);

        try {
            $result = $aiMapService->generate($organization, $validated, $user);

            \App\Domains\Administration\Models\AuditLog::create([
                'organization_id' => $organization->id,
                'actor_id' => $user->id,
                'action' => 'map.ai_generated',
                'metadata' => [
                    'floor_id' => $result['floor']->id ?? null,
                    'map_id' => $result['map']->id ?? null,
                    'style' => $validated['style'] ?? 'modern_glass_luxury',
                    'rooms_count' => $result['rooms_count'] ?? 0,
                    'is_mock' => $result['is_mock'] ?? false,
                ],
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'floor' => $result['floor'],
                    'map' => $result['map'],
                    'background_image_url' => $result['background_image_url'],
                    'rooms' => $result['map']->rooms ?? [],
                    'is_mock' => $result['is_mock'] ?? false,
                ]);
            }

            return redirect()->route('editor', ['office' => $result['floor']->id])
                ->with('success', $result['message']);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            $errors = $ve->errors();
            $firstErr = reset($errors)[0] ?? __('Validation error.');
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $firstErr], 422);
            }
            return back()->with('error', $firstErr);
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Create a new Office branch for the organization.
     */
    public function storeOffice(Request $request)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with(['organization.plan', 'role.permissions'])->first();
        if (!$membership) abort(403);

        if (!$membership->hasPermission('maps.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized: only organization admins can create offices.');
        }

        $organization = $membership->organization;

        if ($organization->hasReachedOfficeLimit()) {
            return back()->with('error', __('Office limit reached for your current plan (:limit offices max). Please upgrade your subscription.', [
                'limit' => $organization->plan?->max_offices ?? 1
            ]));
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'city_location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if (!empty($validated['is_default'])) {
            $organization->floors()->update(['is_default' => false]);
        }

        $floor = $organization->floors()->create([
            'name' => $validated['name'],
            'city_location' => $validated['city_location'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_default' => !empty($validated['is_default']),
            'order' => $organization->floors()->count() + 1,
        ]);

        // Create default published map
        $map = $organization->maps()->create([
            'floor_id' => $floor->id,
            'name' => $floor->name . ' Blueprint',
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

        \App\Domains\Administration\Models\AuditLog::create([
            'organization_id' => $organization->id,
            'actor_id' => $user->id,
            'action' => 'office.created',
            'metadata' => [
                'office_id' => $floor->id,
                'name' => $floor->name,
                'city' => $floor->city_location,
            ],
        ]);

        return back()->with('success', __("Office ':name' created successfully!", ['name' => $floor->name]));
    }

    /**
     * Update Office branch details.
     */
    public function updateOffice(Request $request, \App\Domains\Workspace\Models\Floor $floor)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with(['organization', 'role.permissions'])->first();
        if (!$membership || $floor->organization_id !== $membership->organization_id) abort(403);

        if (!$membership->hasPermission('maps.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'city_location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if (!empty($validated['is_default'])) {
            $membership->organization->floors()->where('id', '!=', $floor->id)->update(['is_default' => false]);
        }

        $floor->update([
            'name' => $validated['name'],
            'city_location' => $validated['city_location'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_default' => !empty($validated['is_default']),
        ]);

        return back()->with('success', __("Office ':name' updated successfully.", ['name' => $floor->name]));
    }

    /**
     * Delete an Office branch.
     */
    public function deleteOffice(\App\Domains\Workspace\Models\Floor $floor)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)->with(['organization', 'role.permissions'])->first();
        if (!$membership || $floor->organization_id !== $membership->organization_id) abort(403);

        if (!$membership->hasPermission('maps.manage') && $membership->role?->slug !== 'company_admin') {
            abort(403, 'Unauthorized');
        }

        if ($membership->organization->floors()->count() <= 1) {
            return back()->with('error', __('You cannot delete the only remaining office branch.'));
        }

        $officeName = $floor->name;
        $floor->rooms()->delete();
        $floor->maps()->delete();
        $floor->delete();

        return back()->with('success', __("Office ':name' deleted successfully.", ['name' => $officeName]));
    }

    /**
     * Show the Visual Office Map Editor & Floor Designer.
     */
    public function editor()
    {
        $user = Auth::user();

        $membership = OrganizationMember::where('user_id', $user->id)
            ->whereIn('status', ['active', 'invited'])
            ->with(['organization.plan'])
            ->first();

        if (!$membership) {
            return redirect()->route('login')->with('error', 'No active organization found.');
        }

        if ($membership->status === 'invited') {
            $membership->update(['status' => 'active']);
        }

        if (!$membership->hasPermission('maps.manage') && $membership->role?->slug !== 'company_admin') {
            return redirect()->route('dashboard')->with('error', __('Unauthorized: You do not have permission to access the Floor Map Editor.'));
        }

        $organization = $membership->organization;
        $this->ensureDefaultWorkspace($organization);

        $requestedOfficeId = request('office');
        if ($requestedOfficeId) {
            $floor = $organization->floors()->where('id', $requestedOfficeId)->first() ?? $organization->defaultOffice() ?? $organization->floors()->first();
        } else {
            $floor = $organization->defaultOffice() ?? $organization->floors()->first();
        }

        if (!$floor) {
            $floor = $organization->floors()->create([
                'name' => $organization->name . ' HQ',
                'is_default' => true,
                'order' => 1,
            ]);
        }

        $map = $organization->maps()->where('floor_id', $floor->id)->where('status', 'published')->latest('published_at')->first()
            ?? $organization->maps()->where('floor_id', $floor->id)->latest()->first();

        if (!$map) {
            $map = $organization->maps()->create([
                'floor_id' => $floor->id,
                'name' => $floor->name . ' Blueprint',
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

        $map->load(['rooms', 'zones', 'objects', 'versions']);
        $floors = $organization->floors()->orderBy('is_default', 'desc')->orderBy('name', 'asc')->get();

        $furnitureCategories = Cache::remember('furniture_categories_with_items', 86400, function () {
            return \App\Domains\Workspace\Models\FurnitureCategory::with('items')
                ->orderBy('order', 'asc')
                ->get();
        });

        $furnitureItems = Cache::remember('furniture_catalog_active', 86400, function () {
            return \App\Domains\Workspace\Models\FurnitureItem::where('is_active', true)->get();
        });

        $plan = $organization->plan;
        $aiStyles = (new \App\Domains\Workspace\Services\AiMapGeneratorService())->getStyles();

        return view('editor', compact('user', 'organization', 'floor', 'floors', 'map', 'furnitureCategories', 'furnitureItems', 'plan', 'aiStyles'));
    }

    /**
     * Upload custom floorplan background image via web session.
     */
    public function uploadMapBackground(Request $request, \App\Domains\Workspace\Models\Map $map)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)
            ->where('organization_id', $map->organization_id)
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $file = $request->file('image') ?? $request->file('background');

        if (!$file) {
            return response()->json(['message' => 'No image file provided.'], 422);
        }

        $request->validate([
            'image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:51200'],
            'background' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:51200'],
        ]);

        $filename = 'floorplan_' . $map->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        
        $destDir = public_path('images/maps');
        if (!file_exists($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $file->move($destDir, $filename);
        $url = '/images/maps/' . $filename;

        $layoutData = $map->layout_data ?? [];
        $layoutData['background_image_url'] = $url;

        $imageSize = @getimagesize(public_path('images/maps/' . $filename));
        if ($imageSize) {
            $layoutData['background_width'] = $imageSize[0];
            $layoutData['background_height'] = $imageSize[1];
        }

        $map->update([
            'layout_data' => $layoutData,
        ]);

        return response()->json([
            'message' => 'Floorplan uploaded successfully.',
            'image_url' => $url,
            'map' => $map->fresh(['floor', 'rooms', 'zones', 'objects']),
        ]);
    }

    /**
     * Remove custom floorplan and revert to system default.
     */
    public function deleteMapBackground(Request $request, \App\Domains\Workspace\Models\Map $map)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)
            ->where('organization_id', $map->organization_id)
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $layoutData = $map->layout_data ?? [];
        unset($layoutData['background_image_url']);
        unset($layoutData['background_width']);
        unset($layoutData['background_height']);

        $map->update([
            'layout_data' => $layoutData,
        ]);

        return response()->json([
            'message' => 'Floorplan removed successfully. Reverted to default.',
            'map' => $map->fresh(['floor', 'rooms', 'zones', 'objects']),
        ]);
    }

    /**
     * Completely clear all furniture objects and reset floorplan for a fresh canvas.
     */
    public function clearEditorMap(Request $request, \App\Domains\Workspace\Models\Map $map)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)
            ->where('organization_id', $map->organization_id)
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        // Delete all furniture objects
        \App\Domains\Workspace\Models\MapObject::where('map_id', $map->id)->delete();

        // Clear custom background image
        $layoutData = $map->layout_data ?? [];
        unset($layoutData['background_image_url']);
        unset($layoutData['background_width']);
        unset($layoutData['background_height']);

        $map->update([
            'layout_data' => $layoutData,
        ]);

        return response()->json([
            'message' => 'Canvas completely cleared. Ready for new layout.',
            'map' => $map->fresh(['floor', 'rooms', 'zones', 'objects']),
        ]);
    }

    /**
     * Save draft map objects and layout data via web session.
     */
    public function saveEditorMap(Request $request, \App\Domains\Workspace\Models\Map $map)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)
            ->where('organization_id', $map->organization_id)
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:120',
            'layout_data' => 'nullable|array',
            'rooms' => 'nullable|array',
            'objects' => 'nullable|array',
        ]);

        if (!empty($validated['name'])) {
            $map->name = $validated['name'];
        }
        if (isset($validated['layout_data'])) {
            $map->layout_data = array_merge($map->layout_data ?? [], $validated['layout_data']);
        }
        $map->tile_size = 16;
        $map->save();

        if (isset($validated['rooms'])) {
            $org = $map->organization;
            $maxRooms = ($org && $org->plan && $org->plan->room_limit > 0) ? $org->plan->room_limit : 0;
            if ($maxRooms > 0 && count($validated['rooms']) > $maxRooms) {
                return response()->json([
                    'message' => __("Your subscription plan allows a maximum of :limit rooms. Please upgrade your subscription plan to save :count rooms.", [
                        'limit' => $maxRooms,
                        'count' => count($validated['rooms']),
                    ])
                ], 403);
            }

            \App\Domains\Workspace\Models\Room::where('map_id', $map->id)->delete();
            foreach ($validated['rooms'] as $r) {
                \App\Domains\Workspace\Models\Room::create([
                    'id' => (!empty($r['id']) && strlen($r['id']) === 36 && str_contains($r['id'], '-')) ? $r['id'] : (string) \Illuminate\Support\Str::uuid(),
                    'organization_id' => $map->organization_id,
                    'map_id' => $map->id,
                    'name' => $r['name'] ?? 'Meeting Room',
                    'type' => $r['type'] ?? 'meeting',
                    'access_mode' => $r['access_mode'] ?? 'public',
                    'capacity' => $r['capacity'] ?? 10,
                    'color' => $r['color'] ?? '#4F9B5F',
                    'bounds' => $r['bounds'] ?? ['x' => 1, 'y' => 1, 'width' => 8, 'height' => 6],
                    'metadata' => $r['metadata'] ?? [],
                ]);
            }
        }

        if (isset($validated['objects'])) {
            \App\Domains\Workspace\Models\MapObject::where('map_id', $map->id)->delete();
            foreach ($validated['objects'] as $obj) {
                \App\Domains\Workspace\Models\MapObject::create([
                    'map_id' => $map->id,
                    'type' => $obj['type'] ?? 'desk',
                    'name' => $obj['name'] ?? null,
                    'position' => $obj['position'] ?? ['x' => 0, 'y' => 0],
                    'size' => $obj['size'] ?? ['width' => 1, 'height' => 1],
                    'rotation' => $obj['rotation'] ?? 0,
                    'color' => $obj['color'] ?? null,
                    'interaction_config' => $obj['interaction_config'] ?? null,
                ]);
            }
        }

        return response()->json([
            'message' => 'Map saved successfully.',
            'map' => $map->fresh(['floor', 'rooms', 'zones', 'objects']),
        ]);
    }

    /**
     * Publish map via web session.
     */
    public function publishEditorMap(Request $request, \App\Domains\Workspace\Models\Map $map, \App\Domains\Workspace\Actions\PublishMapAction $action)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)
            ->where('organization_id', $map->organization_id)
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $publishedMap = $action->execute($map, $user);

        return response()->json([
            'message' => 'Map published successfully.',
            'map' => $publishedMap,
        ]);
    }

    /**
     * Create room via web session.
     */
    public function saveEditorRoom(Request $request)
    {
        $user = Auth::user();
        $orgId = $request->input('organization_id');
        $membership = OrganizationMember::where('user_id', $user->id)
            ->where('organization_id', $orgId)
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $org = $membership->organization;
        if ($org->hasReachedRoomLimit()) {
            $limit = $org->plan ? $org->plan->room_limit : 3;
            return response()->json([
                'message' => __("You have reached the maximum room limit (:limit) for your subscription plan. Please upgrade your plan to create more rooms.", ['limit' => $limit])
            ], 403);
        }

        $validated = $request->validate([
            'organization_id' => 'required|uuid',
            'map_id' => 'required|uuid',
            'name' => 'required|string|max:100',
            'type' => 'required|string',
            'access_mode' => 'nullable|string|in:public,private',
            'capacity' => 'nullable|integer|min:1|max:200',
            'color' => 'nullable|string|max:20',
            'bounds' => 'required|array',
            'metadata' => 'nullable|array',
        ]);

        $room = \App\Domains\Workspace\Models\Room::create($validated);

        return response()->json([
            'message' => 'Room created successfully.',
            'room' => $room,
        ], 201);
    }

    /**
     * Update or create room via web session.
     */
    public function updateEditorRoom(Request $request, $room)
    {
        $user = Auth::user();
        $roomModel = $room instanceof \App\Domains\Workspace\Models\Room ? $room : \App\Domains\Workspace\Models\Room::find($room);

        if (!$roomModel) {
            $orgId = $request->input('organization_id');
            $membership = OrganizationMember::where('user_id', $user->id)
                ->where('organization_id', $orgId)
                ->first();

            if (!$membership) {
                return response()->json(['message' => 'Unauthorized access.'], 403);
            }

            $org = $membership->organization;
            if ($org->hasReachedRoomLimit()) {
                $limit = $org->plan ? $org->plan->room_limit : 3;
                return response()->json([
                    'message' => __("You have reached the maximum room limit (:limit) for your subscription plan. Please upgrade your plan to create more rooms.", ['limit' => $limit])
                ], 403);
            }

            $validated = $request->validate([
                'organization_id' => 'required|uuid',
                'map_id' => 'required|uuid',
                'name' => 'required|string|max:100',
                'type' => 'required|string',
                'access_mode' => 'nullable|string',
                'capacity' => 'nullable|integer',
                'color' => 'nullable|string',
                'bounds' => 'required|array',
                'metadata' => 'nullable|array',
            ]);

            $roomModel = \App\Domains\Workspace\Models\Room::create($validated);

            return response()->json([
                'message' => 'Room created successfully.',
                'room' => $roomModel,
            ]);
        }

        $membership = OrganizationMember::where('user_id', $user->id)
            ->where('organization_id', $roomModel->organization_id)
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $roomModel->update($request->only([
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
            'room' => $roomModel->fresh(),
        ]);
    }

    /**
     * Delete room via web session.
     */
    public function deleteEditorRoom(Request $request, \App\Domains\Workspace\Models\Room $room)
    {
        $user = Auth::user();
        $membership = OrganizationMember::where('user_id', $user->id)
            ->where('organization_id', $room->organization_id)
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        $room->delete();

        return response()->json([
            'message' => 'Room deleted successfully.'
        ]);
    }

    /**
     * Helper to guarantee default floor, map, and Nanobanaba isometric blueprint layout exist.
     */
    private function ensureDefaultWorkspace(\App\Domains\Tenancy\Models\Organization $organization): void
    {
        if ($organization->floors()->count() === 0) {
            $seeder = new \Database\Seeders\BlueprintOfficeSeeder();
            $seeder->seedOrganizationOffice($organization);
        }

        if ($organization->departments()->count() === 0) {
            $eng = \App\Domains\People\Models\Department::create([
                'organization_id' => $organization->id,
                'name' => 'Engineering & Technology',
            ]);
            \App\Domains\People\Models\Team::create(['organization_id' => $organization->id, 'department_id' => $eng->id, 'name' => 'Frontend Team']);
            \App\Domains\People\Models\Team::create(['organization_id' => $organization->id, 'department_id' => $eng->id, 'name' => 'Backend & Cloud']);

            $sales = \App\Domains\People\Models\Department::create([
                'organization_id' => $organization->id,
                'name' => 'Sales & Business Growth',
            ]);
            \App\Domains\People\Models\Team::create(['organization_id' => $organization->id, 'department_id' => $sales->id, 'name' => 'Enterprise Sales']);

            $design = \App\Domains\People\Models\Department::create([
                'organization_id' => $organization->id,
                'name' => 'Product & Design',
            ]);
            \App\Domains\People\Models\Team::create(['organization_id' => $organization->id, 'department_id' => $design->id, 'name' => 'UI / UX Design']);
        }
    }

    /**
     * List all persistent documents and files for a specific room.
     */
    public function listRoomFiles(\App\Domains\Tenancy\Models\Organization $organization, \App\Domains\Workspace\Models\Room $room)
    {
        $files = \App\Domains\Workspace\Models\RoomFile::where('organization_id', $organization->id)
            ->where('room_id', $room->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'files' => $files,
        ]);
    }

    /**
     * Upload a persistent document or file to a specific room.
     */
    public function uploadRoomFile(Request $request, \App\Domains\Tenancy\Models\Organization $organization, \App\Domains\Workspace\Models\Room $room)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // max 50MB
        ]);

        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $mime = $uploadedFile->getMimeType();
        $size = $uploadedFile->getSize();
        $filename = 'room_file_' . \Illuminate\Support\Str::uuid() . '.' . ($uploadedFile->getClientOriginalExtension() ?: 'bin');
        $path = $uploadedFile->storeAs("public/room_files/{$organization->id}/{$room->id}", $filename);
        $url = \Illuminate\Support\Facades\Storage::url($path);

        $user = Auth::user();

        $roomFile = \App\Domains\Workspace\Models\RoomFile::create([
            'organization_id' => $organization->id,
            'room_id' => $room->id,
            'uploaded_by_user_id' => $user?->id,
            'uploader_name' => $user?->name ?: 'Team Member',
            'name' => $originalName,
            'file_path' => $path,
            'file_url' => $url,
            'file_size' => $size,
            'mime_type' => $mime,
        ]);

        return response()->json([
            'message' => 'File uploaded successfully.',
            'file' => $roomFile,
        ], 201);
    }

    /**
     * Delete a persistent file from a room.
     */
    public function deleteRoomFile(\App\Domains\Tenancy\Models\Organization $organization, \App\Domains\Workspace\Models\Room $room, \App\Domains\Workspace\Models\RoomFile $file)
    {
        if ($file->organization_id !== $organization->id || $file->room_id !== $room->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        \Illuminate\Support\Facades\Storage::delete($file->file_path);
        $file->delete();

        return response()->json([
            'message' => 'File deleted successfully.',
        ]);
    }

    /**
     * Send a direct wave notification to another team member.
     */
    public function sendDirectWave(Request $request)
     {
         $user = Auth::user();
         $validated = $request->validate([
             'target_user_id' => 'required|exists:users,id',
             'room_name' => 'nullable|string|max:100',
         ]);

         if ($validated['target_user_id'] === $user->id) {
             return response()->json(['message' => 'Cannot wave to yourself'], 422);
         }

         $notification = \App\Domains\Notifications\Services\NotificationService::notifyWave(
             $validated['target_user_id'],
             $user,
             $validated['room_name'] ?? null
         );

         return response()->json([
             'success' => true,
             'message' => __('Wave sent successfully!'),
         ]);
     }

     /**
      * Send a door knock notification for a private room.
      */
     public function sendDoorKnock(Request $request)
     {
         $user = Auth::user();
         $validated = $request->validate([
             'room_id' => 'required|exists:rooms,id',
         ]);

         $room = \App\Domains\Workspace\Models\Room::find($validated['room_id']);
         if (!$room) {
             return response()->json(['message' => 'Room not found'], 404);
         }

         // Find occupants or organization admins
         $occupants = \App\Domains\Identity\Models\User::where('current_room_id', $room->id)->get();
         if ($occupants->isEmpty()) {
             // Notify room or organization members
             $occupants = $room->organization ? $room->organization->users()->limit(3)->get() : collect([$user]);
         }

         foreach ($occupants as $occupant) {
             if ($occupant->id !== $user->id) {
                 \App\Domains\Notifications\Services\NotificationService::notifyDoorKnock($room, $occupant, $user);
             }
         }

         return response()->json([
             'success' => true,
             'message' => __('Knock sent to room occupants!'),
         ]);
     }
}
