<?php

namespace Tests\Feature;

use App\Domains\CMS\Models\CmsPage;
use App\Domains\CMS\Models\CmsSection;
use App\Domains\CMS\Support\CmsSchema;
use App\Domains\Identity\Models\User;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CmsWebsiteContentTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
        CmsSchema::sync();

        $this->superAdmin = User::factory()->create(['email' => 'info@meemdtt.com']);
    }

    private function section(string $key): CmsSection
    {
        return CmsSection::where('section_key', $key)->firstOrFail();
    }

    /** Current values of a section as the editor would submit them. */
    private function formFor(CmsSection $section): array
    {
        $fields = [];
        foreach (CmsSchema::section($section->section_key)['fields'] as $key => $def) {
            $fields[$key] = $section->field($key);
        }

        return ['fields' => $fields, 'is_active' => $section->is_active ? 1 : 0];
    }

    public function test_sync_creates_every_page_and_section_in_the_schema(): void
    {
        foreach (CmsSchema::pages() as $slug => $page) {
            $this->assertDatabaseHas('cms_pages', ['slug' => $slug, 'status' => 'published']);
            foreach (array_keys($page['sections']) as $key) {
                $this->assertDatabaseHas('cms_sections', ['section_key' => $key]);
            }
        }
    }

    public function test_sync_removes_sections_the_site_does_not_render_and_keeps_edits(): void
    {
        $home = CmsPage::where('slug', 'home')->first();
        CmsSection::create(['page_id' => $home->id, 'section_type' => 'faq', 'section_key' => 'home_faq', 'display_order' => 99]);
        $this->section('home_hero')->update(['title_ar' => 'عنوان معدّل']);

        CmsSchema::sync();

        $this->assertDatabaseMissing('cms_sections', ['section_key' => 'home_faq']);
        $this->assertSame('عنوان معدّل', $this->section('home_hero')->title_ar);
    }

    public function test_every_cms_screen_renders(): void
    {
        $this->actingAs($this->superAdmin)->get('/superadmin/cms/pages')->assertOk()->assertSee('الهيدر والفوتر');
        foreach (CmsPage::all() as $page) {
            $this->actingAs($this->superAdmin)->get("/superadmin/cms/pages/{$page->id}")->assertOk();
        }
        $this->actingAs($this->superAdmin)->get('/superadmin/cms/assets')->assertOk();
    }

    public function test_editing_the_hero_and_repeaters_changes_the_home_page(): void
    {
        $hero = $this->section('home_hero');
        $form = $this->formFor($hero);
        $form['fields']['title_ar'] = 'عنوان البانر الجديد';
        $this->actingAs($this->superAdmin)->put("/superadmin/cms/sections/{$hero->id}", $form)->assertRedirect();

        $features = $this->section('home_features');
        $form = $this->formFor($features);
        $form['fields']['items'][] = ['icon' => 'bolt', 'text_ar' => 'ميزة مضافة', 'text_en' => 'Added feature'];
        $form['fields']['items'][] = ['icon' => '', 'text_ar' => '', 'text_en' => '']; // empty row is dropped
        $this->actingAs($this->superAdmin)->put("/superadmin/cms/sections/{$features->id}", $form)->assertRedirect();

        $this->assertCount(5, $this->section('home_features')->items('items'));
        $this->get('/')->assertOk()->assertSee('عنوان البانر الجديد')->assertSee('ميزة مضافة');
    }

    public function test_header_and_footer_are_editable(): void
    {
        $footer = $this->section('site_footer');
        $form = $this->formFor($footer);
        $form['fields']['tagline_en'] = 'Carved in AlUla.';
        $this->actingAs($this->superAdmin)->put("/superadmin/cms/sections/{$footer->id}", $form);

        $header = $this->section('site_header');
        $form = $this->formFor($header);
        $form['fields']['nav'] = [['label_ar' => 'تواصل', 'label_en' => 'Contact', 'url' => '#contact']];
        $this->actingAs($this->superAdmin)->put("/superadmin/cms/sections/{$header->id}", $form);

        $this->get('/')->assertSee('Carved in AlUla.')->assertSee('#contact')->assertDontSee('#identity"', false);
    }

    public function test_hiding_a_section_removes_it_from_the_home_page(): void
    {
        $quote = $this->section('home_quote');
        $this->get('/')->assertSee($quote->field('quote_ar'));

        $this->actingAs($this->superAdmin)->post("/superadmin/cms/sections/{$quote->id}/toggle")->assertRedirect();

        $this->get('/')->assertDontSee($quote->field('quote_ar'));
    }

    public function test_required_sections_cannot_be_hidden(): void
    {
        $header = $this->section('site_header');
        $this->actingAs($this->superAdmin)->post("/superadmin/cms/sections/{$header->id}/toggle")->assertNotFound();
    }

    public function test_login_side_image_can_be_uploaded_and_is_shown(): void
    {
        $login = $this->section('auth_login');
        $form = $this->formFor($login);
        $form['image_file'] = UploadedFile::fake()->image('panel.jpg', 600, 800);

        $this->actingAs($this->superAdmin)->put("/superadmin/cms/sections/{$login->id}", $form)->assertRedirect();

        $login->refresh();
        $this->assertNotNull($login->media_asset_id);
        $path = $login->mediaAsset->file_path;

        auth()->logout();
        $this->get('/login')->assertOk()->assertSee('ula-auth-hero-image', false)->assertSee($path, false);

        @unlink(public_path(ltrim($path, '/')));
    }

    public function test_unsafe_links_are_rejected(): void
    {
        $cta = $this->section('home_cta');
        $form = $this->formFor($cta);
        $form['fields']['cta_primary_link'] = 'javascript:alert(1)';

        $this->actingAs($this->superAdmin)->put("/superadmin/cms/sections/{$cta->id}", $form)->assertSessionHasErrors();
        $this->assertSame('/register', $this->section('home_cta')->field('cta_primary_link'));
    }

    public function test_auth_pages_render_without_cms_rows(): void
    {
        CmsSection::whereIn('section_key', ['auth_login', 'auth_register'])->delete();

        $this->get('/login')->assertOk()->assertSee('أهلاً بعودتك');
        $this->get('/register')->assertOk()->assertSee('أنشئ حسابك');
    }
}
