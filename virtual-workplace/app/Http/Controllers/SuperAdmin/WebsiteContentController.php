<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Domains\CMS\Models\CmsMediaAsset;
use App\Domains\CMS\Models\CmsPage;
use App\Domains\CMS\Models\CmsSection;
use App\Domains\CMS\Support\CmsSchema;
use App\Domains\Tenancy\Models\Plan;
use App\Http\Controllers\Controller;
use App\Services\FileUploadService;
use Illuminate\Http\Request;

/**
 * Super Admin — Website Content: pages, sections and the image library.
 * Moved verbatim from SuperAdminController (refactor phase 7).
 */
class WebsiteContentController extends Controller
{
    /**
     * List the website pages the CMS manages.
     */
    public function cmsPages()
    {
        $order = array_keys(CmsSchema::pages());
        $pages = CmsPage::withCount(['sections', 'activeSections'])
            ->whereIn('slug', $order)
            ->get()
            ->sortBy(fn ($p) => array_search($p->slug, $order));

        return view('superadmin.cms.pages', compact('pages'));
    }

    /**
     * Edit a page's sections.
     */
    public function editCmsPage(CmsPage $page)
    {
        $pageDef = CmsSchema::page($page->slug);
        abort_unless($pageDef, 404);

        $page->load(['sections' => fn ($q) => $q->orderBy('display_order'), 'sections.mediaAsset']);
        $sections = $page->sections->filter(fn ($s) => isset($pageDef['sections'][$s->section_key]));
        $assets = CmsMediaAsset::where('is_active', true)->where('asset_type', 'image')->latest()->get();
        $plans = Plan::orderBy('price')->get(['slug', 'name']);

        return view('superadmin.cms.page_edit', compact('page', 'pageDef', 'sections', 'assets', 'plans'));
    }

    /**
     * Update a page's search-engine title and description.
     */
    public function updateCmsPage(Request $request, CmsPage $page)
    {
        abort_if(empty(CmsSchema::page($page->slug)['seo']), 404);

        $page->update($request->validate([
            'meta_title_ar' => ['nullable', 'string', 'max:255'],
            'meta_desc_ar' => ['nullable', 'string', 'max:500'],
        ]));

        return back()->with('success', __('Page settings saved.'));
    }

    /**
     * Update a section from its CmsSchema definition.
     */
    public function updateCmsSection(Request $request, CmsSection $section)
    {
        $def = CmsSchema::section($section->section_key);
        abort_unless($def, 404);

        $request->validate([
            'image_file' => ['nullable', 'file', 'image', 'max:10240'],
            'media_asset_id' => ['nullable', 'integer', 'exists:cms_media_assets,id'],
        ]);

        $input = (array) $request->input('fields', []);
        $content = [];
        $data = [];
        $errors = [];

        foreach ($def['fields'] as $key => $fieldDef) {
            if ($fieldDef['type'] === 'repeater') {
                $rows = [];
                foreach ((array) ($input[$key] ?? []) as $row) {
                    $clean = [];
                    foreach ($fieldDef['fields'] as $subKey => $subDef) {
                        $clean[$subKey] = $this->cleanCmsValue($row[$subKey] ?? null, $subDef, $fieldDef['label'].' → '.$subDef['label'], $errors);
                    }
                    if (array_filter($clean, fn ($v) => $v !== null && $v !== '')) {
                        $rows[] = $clean;
                    }
                }
                $content[$key] = $rows;

                continue;
            }

            $value = $this->cleanCmsValue($input[$key] ?? null, $fieldDef, $fieldDef['label'], $errors);
            if (CmsSchema::isColumn($key)) {
                $data[$key] = $value;
            } else {
                $content[$key] = $value;
            }
        }

        if ($errors) {
            return back()->withErrors($errors)->withInput()->with('cms_section', $section->section_key);
        }

        if (! empty($def['image'])) {
            if ($request->boolean('remove_image')) {
                $data['media_asset_id'] = null;
            } elseif ($request->hasFile('image_file')) {
                $file = $request->file('image_file');
                $extension = strtolower($file->getClientOriginalExtension());
                if ($rejection = FileUploadService::rejectionReasonFor($file, $extension)) {
                    return back()->withErrors(['image_file' => $rejection])->with('cms_section', $section->section_key);
                }
                $stored = FileUploadService::moveToPublicUploads($file, public_path('uploads/cms'), 'section_'.$section->section_key, $extension);
                $data['media_asset_id'] = CmsMediaAsset::create([
                    'name' => $def['name_en'].' image',
                    'asset_type' => 'image',
                    'file_path' => '/uploads/cms/'.$stored['filename'],
                    'file_size' => $stored['size'],
                    'version_tag' => 'v'.time(),
                    'tags' => ['section', $section->section_key],
                    'is_active' => true,
                ])->id;
            } else {
                $data['media_asset_id'] = $request->input('media_asset_id') ?: null;
            }
        }

        if (empty($def['required'])) {
            $data['is_active'] = $request->boolean('is_active');
        }

        $data['content'] = $content;
        $section->update($data);

        return back()->with('success', __(':section saved.', ['section' => $def['name_en']]))->with('cms_section', $section->section_key);
    }

    /**
     * Trim a submitted field value and reject unsafe links.
     */
    private function cleanCmsValue(mixed $value, array $fieldDef, string $label, array &$errors): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }
        $value = mb_substr(trim((string) $value), 0, $fieldDef['type'] === 'textarea' ? 2000 : 500);

        if ($fieldDef['type'] === 'url' && $value !== '' && ! preg_match('~^(/|#|https?://|mailto:)~i', $value)) {
            $errors[] = __(':field must start with /, #, https:// or mailto:', ['field' => $label]);
        }
        if ($fieldDef['type'] === 'icon') {
            $value = preg_replace('/[^a-z0-9_]/', '', strtolower($value));
        }
        if ($fieldDef['type'] === 'select' && ! array_key_exists($value, $fieldDef['options'])) {
            $value = $fieldDef['default'] ?? null;
        }

        return $value;
    }

    /**
     * Show or hide an optional section on the website.
     */
    public function toggleCmsSection(CmsSection $section)
    {
        $def = CmsSchema::section($section->section_key);
        abort_if(! $def || ! empty($def['required']), 404);

        $section->update(['is_active' => ! $section->is_active]);

        return back()->with('success', __('Section visibility updated.'))->with('cms_section', $section->section_key);
    }

    /**
     * Website image library.
     */
    public function cmsAssets()
    {
        $assets = CmsMediaAsset::with('sections:id,section_key,media_asset_id')
            ->where('asset_type', 'image')
            ->orderBy('created_at', 'desc')
            ->paginate(24);

        return view('superadmin.cms.assets', compact('assets'));
    }

    /**
     * Upload an image to the library.
     */
    public function uploadCmsAsset(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        if ($rejection = FileUploadService::rejectionReasonFor($file, $extension)) {
            return back()->withErrors(['file' => $rejection]);
        }
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'avif'], true)) {
            return back()->withErrors(['file' => __('Only image files can be added to the website library.')]);
        }

        $stored = FileUploadService::moveToPublicUploads($file, public_path('uploads/cms'), 'asset', $extension);

        CmsMediaAsset::create([
            'name' => $request->input('name'),
            'asset_type' => 'image',
            'file_path' => '/uploads/cms/'.$stored['filename'],
            'file_size' => $stored['size'],
            'version_tag' => 'v1',
            'tags' => [],
            'is_active' => true,
        ]);

        return back()->with('success', __('Image uploaded.'));
    }

    /**
     * Delete an image from the library. Sections using it fall back to their placeholder.
     */
    public function deleteCmsAsset(CmsMediaAsset $asset)
    {
        CmsSection::where('media_asset_id', $asset->id)->update(['media_asset_id' => null]);

        if (str_starts_with($asset->file_path, '/uploads/cms/')) {
            $fullPath = public_path(ltrim($asset->file_path, '/'));
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
        $asset->delete();

        return back()->with('success', __('Image deleted.'));
    }
}
