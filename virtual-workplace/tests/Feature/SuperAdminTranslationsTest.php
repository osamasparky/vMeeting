<?php

namespace Tests\Feature;

use App\Domains\Identity\Models\User;
use App\Support\TranslationOverrides;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The translations console must never write lang/*.json (read-only on live, replaced by
 * deploys); edits go to a storage overlay that the translator picks up.
 */
class SuperAdminTranslationsTest extends TestCase
{
    use RefreshDatabase;

    private string $overlay;

    private string $arBefore;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->overlay = storage_path('framework/testing/translation-overrides-'.uniqid().'.json');
        $this->app->bind(TranslationOverrides::class, fn () => new TranslationOverrides($this->overlay));
        $this->arBefore = file_get_contents(lang_path('ar.json'));
    }

    protected function tearDown(): void
    {
        @unlink($this->overlay);
        parent::tearDown();
    }

    private function superAdmin(): User
    {
        return User::factory()->create(['email' => 'info@meemdtt.com']);
    }

    public function test_saving_a_phrase_goes_to_the_overlay_not_the_lang_file(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/superadmin/translations', ['keys' => ['Save'], 'ar' => ['احفظ الآن'], 'en' => ['Save now']])
            ->assertSessionHas('success');

        $this->assertSame($this->arBefore, file_get_contents(lang_path('ar.json')));
        $stored = json_decode(file_get_contents($this->overlay), true);
        $this->assertSame('احفظ الآن', $stored['ar']['Save']);

        $overrides = new TranslationOverrides($this->overlay);
        $this->assertSame('احفظ الآن', $overrides->merged('ar')['Save']);
        $this->assertSame('احفظ الآن', $overrides->apply('ar', ['Save' => 'حفظ'])['Save']);
    }

    public function test_add_and_delete_phrases(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post('/superadmin/translations/add', ['key' => 'Brand new phrase', 'ar' => 'عبارة جديدة'])
            ->assertSessionHas('success');
        $this->assertSame('عبارة جديدة', (new TranslationOverrides($this->overlay))->merged('ar')['Brand new phrase']);

        $this->actingAs($admin)->post('/superadmin/translations/delete', ['key' => 'Save'])->assertSessionHas('success');
        $merged = (new TranslationOverrides($this->overlay))->merged('ar');
        $this->assertArrayNotHasKey('Save', $merged);
        $this->assertSame($this->arBefore, file_get_contents(lang_path('ar.json')));

        // Re-adding a deleted shipped key brings it back.
        $this->actingAs($admin)->post('/superadmin/translations/add', ['key' => 'Save', 'ar' => 'حفظ'])->assertSessionHas('success');
        $this->assertSame('حفظ', (new TranslationOverrides($this->overlay))->merged('ar')['Save']);
    }

    public function test_translator_uses_the_overlay(): void
    {
        $overrides = new TranslationOverrides($this->overlay);
        $overrides->set('Save', 'ar', 'احفظ الآن');
        $overrides->save();

        $this->app->forgetInstance('translator');
        $this->app->forgetInstance('translation.loader');
        app()->setLocale('ar');
        $this->assertSame('احفظ الآن', __('Save'));
    }

    public function test_non_superadmin_cannot_save(): void
    {
        $this->actingAs(User::factory()->create(['email' => 'someone@company.local']))
            ->post('/superadmin/translations', ['keys' => ['Save'], 'ar' => ['x']]);

        $this->assertFileDoesNotExist($this->overlay);
    }
}
