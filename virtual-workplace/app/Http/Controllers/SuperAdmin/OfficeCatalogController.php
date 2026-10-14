<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Domains\CMS\Support\CmsSchema;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\Plan;
use App\Domains\Workspace\Models\FurnitureCategory;
use App\Domains\Workspace\Models\FurnitureItem;
use App\Domains\Workspace\Models\MapObject;
use App\Domains\Workspace\Models\OfficeTemplate;
use App\Domains\Workspace\Models\Room;
use App\Domains\Workspace\Requests\StoreFurnitureCategoryRequest;
use App\Domains\Workspace\Requests\StoreFurnitureItemRequest;
use App\Domains\Workspace\Support\RoomBoundsGap;
use App\Http\Controllers\Controller;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Super Admin — The furniture catalogue and the plan office templates.
 * Moved verbatim from SuperAdminController (refactor phase 7).
 */
class OfficeCatalogController extends Controller
{
    /**
     * Upload global default office blueprint for the platform.
     */
    public function uploadDefaultBlueprint(Request $request)
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:15360'],
        ]);

        $file = $request->file('image');
        $dest = public_path('images');
        FileUploadService::ensureDirectory($dest, 0755);
        FileUploadService::protectDirectoryFromExecution($dest);

        // Copy to both default locations so all views pick it up immediately
        $file->move($dest, 'office_floorplan.jpg');
        copy($dest.'/office_floorplan.jpg', $dest.'/isometric_office_blueprint.jpg');

        return back()->with('success', 'Global system default office blueprint updated successfully for all organizations.');
    }

    /**
     * Furniture & Assets Management.
     */
    public function furniture(Request $request)
    {
        $user = Auth::user();
        $categories = FurnitureCategory::withCount('items')
            ->orderBy('order', 'asc')
            ->get();

        $selectedCategoryId = $request->input('category_id');
        $query = FurnitureItem::with('category');

        if ($selectedCategoryId) {
            $query->where('category_id', $selectedCategoryId);
        }

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $items = $query->latest()->paginate(24);

        $stats = [
            'total_items' => FurnitureItem::count(),
            'total_categories' => $categories->count(),
            'custom_uploads' => FurnitureItem::whereNotNull('image_url')->count(),
        ];

        return view('superadmin.furniture', compact('user', 'categories', 'items', 'stats', 'selectedCategoryId'));
    }

    /**
     * Store new Furniture Category.
     */
    public function storeFurnitureCategory(StoreFurnitureCategoryRequest $request)
    {
        $validated = $request->validated();

        FurnitureCategory::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']).'-'.Str::random(3),
            'icon' => $validated['icon'] ?? '🪑',
            'order' => $validated['order'] ?? 0,
        ]);

        $this->invalidateFurnitureCache();

        return back()->with('success', 'Furniture Category created successfully.');
    }

    /**
     * Update Furniture Category.
     */
    public function updateFurnitureCategory(StoreFurnitureCategoryRequest $request, FurnitureCategory $category)
    {
        $validated = $request->validated();

        $category->update([
            'name' => $validated['name'],
            'icon' => $validated['icon'] ?? $category->icon,
            'order' => $validated['order'] ?? $category->order,
        ]);

        $this->invalidateFurnitureCache();

        return back()->with('success', 'Furniture Category updated successfully.');
    }

    /**
     * Delete Furniture Category.
     */
    public function deleteFurnitureCategory(FurnitureCategory $category)
    {
        $category->items()->delete();
        $category->delete();

        $this->invalidateFurnitureCache();

        return back()->with('success', 'Furniture Category deleted successfully.');
    }

    /**
     * Store/Upload new Furniture Item.
     */
    public function storeFurnitureItem(StoreFurnitureItemRequest $request)
    {
        $validated = $request->validated();

        $imageUrl = $validated['image_url'] ?? null;

        if ($request->hasFile('image')) {
            [$uploadedUrl, $error] = $this->storeFurnitureImage($request->file('image'));
            if ($error) {
                return back()->withErrors(['image' => $error]);
            }
            $imageUrl = $uploadedUrl;
        }

        $colorsArr = ! empty($validated['colors'])
            ? array_map('trim', explode(',', $validated['colors']))
            : ['#00b4b3', '#012c41'];

        FurnitureItem::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']).'-'.Str::random(4),
            'image_url' => $imageUrl,
            'icon' => $validated['icon'] ?? '🪑',
            'width' => $validated['width'],
            'height' => $validated['height'],
            'collision' => $request->has('collision'),
            'colors' => $colorsArr,
            'is_active' => true,
        ]);

        $this->invalidateFurnitureCache();

        return back()->with('success', 'New furniture asset uploaded and added to the catalog successfully.');
    }

    /**
     * Update Furniture Item.
     */
    public function updateFurnitureItem(StoreFurnitureItemRequest $request, FurnitureItem $item)
    {
        $validated = $request->validated();

        $imageUrl = $item->image_url;

        if ($request->hasFile('image')) {
            [$uploadedUrl, $error] = $this->storeFurnitureImage($request->file('image'));
            if ($error) {
                return back()->withErrors(['image' => $error]);
            }
            $imageUrl = $uploadedUrl;
        } elseif (! empty($validated['image_url'])) {
            $imageUrl = $validated['image_url'];
        }

        $colorsArr = ! empty($validated['colors'])
            ? array_map('trim', explode(',', $validated['colors']))
            : $item->colors;

        $item->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'image_url' => $imageUrl,
            'icon' => $validated['icon'] ?? $item->icon,
            'width' => $validated['width'],
            'height' => $validated['height'],
            'collision' => $request->has('collision'),
            'colors' => $colorsArr,
            'is_active' => $request->has('is_active') ? true : $item->is_active,
        ]);

        $this->invalidateFurnitureCache();

        return back()->with('success', "Furniture item {$item->name} updated successfully.");
    }

    /**
     * Delete Furniture Item.
     */
    public function deleteFurnitureItem(FurnitureItem $item)
    {
        $item->delete();

        $this->invalidateFurnitureCache();

        return back()->with('success', 'Furniture item removed from catalog.');
    }

    /**
     * Invalidate cached furniture catalog in memory / cache store.
     */
    private function invalidateFurnitureCache(): void
    {
        Cache::forget('furniture_catalog_active');
        Cache::forget('furniture_categories_with_items');
    }

    /**
     * Validate and store a furniture item's uploaded image (PNG/WebP/JPG/SVG,
     * with SVG content scanned for embedded scripts). Returns
     * [url, null] on success or [null, errorMessage] on rejection.
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function storeFurnitureImage($file): array
    {
        $extension = strtolower($file->guessExtension() ?: 'png');
        if (! in_array($extension, ['png', 'webp', 'jpg', 'jpeg', 'svg'], true)) {
            return [null, 'Invalid image format. Allowed formats: PNG, WebP, JPG, SVG.'];
        }

        if ($rejection = FileUploadService::rejectionReasonFor($file, $extension)) {
            return [null, $rejection];
        }

        $destinationPath = public_path('uploads/furniture');
        $stored = FileUploadService::moveToPublicUploads($file, $destinationPath, 'furn', $extension);

        return ['/uploads/furniture/'.$stored['filename'], null];
    }

    /**
     * Default Office Template & Rooms Designer (SuperAdmin).
     */
    public function defaultTemplate(Request $request)
    {
        $user = Auth::user();
        $allPlans = Plan::where('is_active', true)->orderBy('price', 'asc')->get();

        $selectedPlanSlug = $request->query('plan', 'free');
        $selectedPlan = $allPlans->firstWhere('slug', $selectedPlanSlug) ?: $allPlans->first();

        $template = OfficeTemplate::getForPlan($selectedPlan);
        $totalCompanies = Organization::count();
        $planCompaniesCount = $selectedPlan ? Organization::where('plan_id', $selectedPlan->id)->count() : 0;

        return view('superadmin.default_template', compact('user', 'template', 'allPlans', 'selectedPlan', 'totalCompanies', 'planCompaniesCount'));
    }

    /**
     * Update default office template meta & dimensions.
     */
    public function updateDefaultTemplate(Request $request)
    {
        $template = $request->filled('template_id')
            ? OfficeTemplate::findOrFail($request->input('template_id'))
            : OfficeTemplate::getDefault();

        $validated = $request->validate([
            'template_id' => ['nullable', 'uuid'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'background_image_url' => ['nullable', 'string', 'max:500'],
            'width' => ['required', 'integer', 'min:10', 'max:200'],
            'height' => ['required', 'integer', 'min:10', 'max:200'],
            'tile_size' => ['required', 'integer', 'in:16,32,48,64'],
        ]);

        $layoutData = $template->layout_data ?? [];
        if (! empty($validated['background_image_url'])) {
            $layoutData['background_image_url'] = $validated['background_image_url'];
        }

        $template->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'background_image_url' => $validated['background_image_url'] ?? $template->background_image_url,
            'width' => $validated['width'],
            'height' => $validated['height'],
            'tile_size' => $validated['tile_size'],
            'layout_data' => $layoutData,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', __('Office Template blueprint settings updated successfully.'));
    }

    /**
     * Add or update a default room in the system template.
     */
    public function saveTemplateRoom(Request $request)
    {
        $template = $request->filled('template_id')
            ? OfficeTemplate::findOrFail($request->input('template_id'))
            : OfficeTemplate::getDefault();

        $validated = $request->validate([
            'template_id' => ['nullable', 'uuid'],
            'room_index' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'string', 'in:meeting,private,breakout,lounge,reception'],
            'access_mode' => ['required', 'string', 'in:public,knock,locked'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'color' => ['nullable', 'string', 'max:30'],
            'x' => ['required', 'integer', 'min:0'],
            'y' => ['required', 'integer', 'min:0'],
            'width' => ['required', 'integer', 'min:1'],
            'height' => ['required', 'integer', 'min:1'],
            'audio_isolation' => ['nullable', 'boolean'],
        ]);

        $rooms = $template->rooms_data ?: [];

        $newRoom = [
            'name' => $validated['name'],
            'type' => $validated['type'],
            'access_mode' => $validated['access_mode'],
            'capacity' => (int) $validated['capacity'],
            'color' => $validated['color'] ?: '#3F7D4F',
            'bounds' => [
                'x' => (int) $validated['x'],
                'y' => (int) $validated['y'],
                'width' => (int) $validated['width'],
                'height' => (int) $validated['height'],
            ],
            'metadata' => [
                'audio_isolation' => $request->has('audio_isolation') ? (bool) $request->input('audio_isolation') : true,
            ],
        ];

        if ($request->filled('room_index') && isset($rooms[(int) $request->input('room_index')])) {
            $rooms[(int) $request->input('room_index')] = $newRoom;
        } else {
            $rooms[] = $newRoom;
        }

        $template->update(['rooms_data' => array_values($rooms)]);

        return back()->with('success', __('Room saved in the office template.'));
    }

    /**
     * Delete a default room from the template.
     */
    public function deleteTemplateRoom(Request $request, int $roomIndex)
    {
        $template = $request->filled('template_id')
            ? OfficeTemplate::findOrFail($request->input('template_id'))
            : OfficeTemplate::getDefault();

        $rooms = $template->rooms_data ?: [];

        if (isset($rooms[$roomIndex])) {
            unset($rooms[$roomIndex]);
            $template->update(['rooms_data' => array_values($rooms)]);

            return back()->with('success', __('Room removed from office template.'));
        }

        return back()->with('error', __('Room not found in template.'));
    }

    /**
     * Bulk save all visually drawn & renamed rooms to the default office template.
     */
    public function saveAllTemplateRooms(Request $request)
    {
        $template = $request->filled('template_id')
            ? OfficeTemplate::findOrFail($request->input('template_id'))
            : OfficeTemplate::getDefault();

        $validated = $request->validate([
            'template_id' => ['nullable', 'uuid'],
            'rooms' => ['present', 'array'],
            'rooms.*.name' => ['required', 'string', 'max:120'],
            'rooms.*.type' => ['nullable', 'string', 'in:meeting,private,breakout,lounge,reception'],
            'rooms.*.access_mode' => ['nullable', 'string', 'in:public,knock,locked'],
            'rooms.*.capacity' => ['nullable', 'integer', 'min:1', 'max:100'],
            'rooms.*.color' => ['nullable', 'string', 'max:30'],
            'rooms.*.bounds' => ['required', 'array'],
            'rooms.*.bounds.x' => ['required', 'integer', 'min:0'],
            'rooms.*.bounds.y' => ['required', 'integer', 'min:0'],
            'rooms.*.bounds.width' => ['required', 'integer', 'min:1'],
            'rooms.*.bounds.height' => ['required', 'integer', 'min:1'],
            'rooms.*.bounds.doorSide' => ['nullable', 'string', 'in:auto,top,bottom,left,right'],
            'rooms.*.bounds.doorOffset' => ['nullable', 'numeric', 'min:0.05', 'max:0.95'],
            'rooms.*.metadata' => ['nullable', 'array'],
        ]);

        $roomsFormatted = [];
        foreach ($validated['rooms'] as $r) {
            $roomsFormatted[] = [
                'name' => $r['name'],
                'type' => $r['type'] ?? 'meeting',
                'access_mode' => $r['access_mode'] ?? 'public',
                'capacity' => (int) ($r['capacity'] ?? 8),
                'color' => $r['color'] ?? '#3F7D4F',
                'bounds' => array_filter([
                    'x' => (int) ($r['bounds']['x'] ?? 0),
                    'y' => (int) ($r['bounds']['y'] ?? 0),
                    'width' => (int) ($r['bounds']['width'] ?? 10),
                    'height' => (int) ($r['bounds']['height'] ?? 8),
                    'doorSide' => $r['bounds']['doorSide'] ?? null,
                    'doorOffset' => $r['bounds']['doorOffset'] ?? null,
                ], fn ($v) => $v !== null),
                'metadata' => [
                    'audio_isolation' => isset($r['metadata']['audio_isolation']) ? (bool) $r['metadata']['audio_isolation'] : true,
                ],
            ];
        }

        $tilePx = RoomBoundsGap::CANONICAL_TILE_PX;
        $violations = RoomBoundsGap::violatingPairs(array_map(fn ($r) => $r['bounds'], $roomsFormatted), $tilePx);
        if (! empty($violations)) {
            $names = array_map(fn ($r) => $r['name'], $roomsFormatted);
            $pairs = array_map(fn ($v) => "{$names[$v[0]]} / {$names[$v[1]]}", array_slice($violations, 0, 5));

            return response()->json([
                'success' => false,
                'message' => __('These rooms must either share a wall or be at least :gap tiles apart: :pairs', [
                    'gap' => RoomBoundsGap::minGapTiles($tilePx),
                    'pairs' => implode(', ', $pairs),
                ]),
            ], 422);
        }

        $template->update([
            'rooms_data' => $roomsFormatted,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('All office rooms updated and saved successfully!'),
            'rooms' => $roomsFormatted,
        ]);
    }

    /**
     * Upload custom blueprint background image for the template.
     */
    public function uploadTemplateBackground(Request $request)
    {
        $request->validate([
            'template_id' => ['nullable', 'uuid'],
            'background' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:51200'],
        ]);

        $template = $request->filled('template_id')
            ? OfficeTemplate::findOrFail($request->input('template_id'))
            : OfficeTemplate::getDefault();

        $file = $request->file('background');
        $filename = 'template_floorplan_'.($template->plan_slug ?: 'default').'_'.time().'.'.$file->getClientOriginalExtension();

        $destDir = public_path('images/maps');
        FileUploadService::ensureDirectory($destDir, 0755);
        FileUploadService::protectDirectoryFromExecution($destDir);
        $file->move($destDir, $filename);
        $url = '/images/maps/'.$filename;

        $layoutData = $template->layout_data ?? [];
        $layoutData['background_image_url'] = $url;

        $template->update([
            'background_image_url' => $url,
            'layout_data' => $layoutData,
        ]);

        return back()->with('success', __('Office Blueprint background image updated successfully.'));
    }

    /**
     * Sync and propagate the Template rooms & layout to companies.
     */
    public function syncTemplateToOrganizations(Request $request)
    {
        $template = $request->filled('template_id')
            ? OfficeTemplate::findOrFail($request->input('template_id'))
            : OfficeTemplate::getDefault();

        $scope = $request->input('sync_scope', 'all');
        $query = Organization::query();

        if ($scope === 'plan_only' && $template->plan_id) {
            $query->where('plan_id', $template->plan_id);
        }

        $organizations = $query->get();
        $count = 0;

        foreach ($organizations as $org) {
            $floor = $org->floors()->first();
            if (! $floor) {
                $floor = $org->floors()->create([
                    'name' => 'الدور الرئيسي - Main Office Floor',
                    'order' => 1,
                ]);
            }

            $map = $org->maps()->where('floor_id', $floor->id)->first();
            $layoutData = $template->layout_data ?? [
                'theme' => 'open_spatial_blueprint',
                'background_image_url' => $template->background_image_url ?: '/images/office_floorplan.jpg',
            ];

            if (! $map) {
                $map = $org->maps()->create([
                    'floor_id' => $floor->id,
                    'name' => $template->name,
                    'status' => 'published',
                    'version' => 1,
                    'width' => $template->width,
                    'height' => $template->height,
                    'tile_size' => $template->tile_size,
                    'layout_data' => $layoutData,
                    'published_at' => now(),
                ]);
            } else {
                $map->update([
                    'width' => $template->width,
                    'height' => $template->height,
                    'tile_size' => $template->tile_size,
                    'layout_data' => $layoutData,
                ]);
            }

            // Remove untextured blue dummy objects
            MapObject::where('map_id', $map->id)
                ->whereNull('image_url')
                ->delete();

            // If requested to overwrite rooms or if organization has 0 rooms
            if ($request->has('overwrite_rooms') || $org->rooms()->count() === 0) {
                $org->rooms()->delete();
                $roomsList = $template->rooms_data ?: [];
                $maxAllowed = ($org->plan && $org->plan->room_limit > 0) ? $org->plan->room_limit : count($roomsList);
                $roomsList = array_slice($roomsList, 0, $maxAllowed);

                foreach ($roomsList as $rData) {
                    Room::create([
                        'organization_id' => $org->id,
                        'map_id' => $map->id,
                        'name' => $rData['name'] ?? 'Meeting Room',
                        'type' => $rData['type'] ?? 'meeting',
                        'access_mode' => $rData['access_mode'] ?? 'public',
                        'capacity' => $rData['capacity'] ?? 8,
                        'color' => $rData['color'] ?? '#3F7D4F',
                        'bounds' => $rData['bounds'] ?? ['x' => 1, 'y' => 1, 'width' => 10, 'height' => 10],
                        'metadata' => $rData['metadata'] ?? ['audio_isolation' => true],
                    ]);
                }

                // The rooms were reset from the template: its furniture comes with them.
                MapObject::where('map_id', $map->id)->delete();
                $template->placeFurniture($map, $roomsList);
            }

            $count++;
        }

        return back()->with('success', __('Office Template synchronized across :count organizations successfully.', ['count' => $count]));
    }

    /* ═══════════════════════════════════════════════════════════════
       WEBSITE CMS (SUPER ADMIN)
       Every editable page, section and field is defined in CmsSchema.
       ═══════════════════════════════════════════════════════════════ */
}
