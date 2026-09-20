<?php

namespace Tests\Feature;

use App\Domains\Identity\Models\User;
use App\Domains\Workspace\Models\FurnitureCategory;
use Database\Seeders\FurnitureSeeder;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SuperAdminFurnitureTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(FurnitureSeeder::class);

        $this->superAdmin = User::factory()->create([
            'email' => 'info@meemdtt.com',
        ]);

        $this->regularUser = User::factory()->create([
            'email' => 'regular@company.local',
        ]);
    }

    public function test_non_superadmin_cannot_access_furniture_catalog_management(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/superadmin/furniture');
        $response->assertRedirect(route('dashboard'));
    }

    public function test_superadmin_can_access_furniture_catalog_page(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/superadmin/furniture');
        $response->assertStatus(200);
        $response->assertSee('Furniture & Assets Catalog');
    }

    public function test_superadmin_can_create_category(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/superadmin/furniture/category', [
            'name' => 'Lounge Seating',
            'icon' => '🛋️',
            'order' => 5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('furniture_categories', [
            'name' => 'Lounge Seating',
            'icon' => '🛋️',
        ]);
    }

    public function test_superadmin_can_create_furniture_item(): void
    {
        $category = FurnitureCategory::first();

        $response = $this->actingAs($this->superAdmin)->post('/superadmin/furniture/item', [
            'name' => 'Executive Leather Chair',
            'category_id' => $category->id,
            'icon' => '🪑',
            'width' => 2,
            'height' => 2,
            'collision' => 1,
            'colors' => '#00b4b3, #012c41',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('furniture_items', [
            'name' => 'Executive Leather Chair',
            'category_id' => $category->id,
            'width' => 2,
            'height' => 2,
        ]);
    }

    public function test_unsafe_svg_upload_with_embedded_script_is_rejected(): void
    {
        $category = FurnitureCategory::first();

        $maliciousSvg = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert("XSS")</script><rect width="10" height="10"/></svg>';
        $file = UploadedFile::fake()->createWithContent('malicious.svg', $maliciousSvg);

        $response = $this->actingAs($this->superAdmin)->post('/superadmin/furniture/item', [
            'name' => 'Malicious Chair',
            'category_id' => $category->id,
            'image' => $file,
            'width' => 1,
            'height' => 1,
        ]);

        $response->assertSessionHasErrors(['image']);
        $this->assertDatabaseMissing('furniture_items', [
            'name' => 'Malicious Chair',
        ]);
    }

    public function test_uploading_a_php_file_as_a_cms_asset_is_rejected(): void
    {
        $shell = UploadedFile::fake()->createWithContent('shell.php', '<?php system($_GET["c"]); ?>');

        $response = $this->actingAs($this->superAdmin)->post('/superadmin/cms/assets/upload', [
            'name' => 'Malicious Asset',
            'asset_type' => 'image',
            'file' => $shell,
        ]);

        $response->assertSessionHasErrors(['file']);
        $this->assertDatabaseMissing('cms_media_assets', ['name' => 'Malicious Asset']);
        $this->assertFileDoesNotExist(public_path('uploads/cms/'.$shell->hashName()));
    }

    public function test_an_ordinary_cms_image_asset_can_be_uploaded(): void
    {
        $image = UploadedFile::fake()->image('hero.png', 200, 200);

        $response = $this->actingAs($this->superAdmin)->post('/superadmin/cms/assets/upload', [
            'name' => 'Hero Image',
            'asset_type' => 'image',
            'file' => $image,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cms_media_assets', ['name' => 'Hero Image', 'asset_type' => 'image']);
    }

    public function test_super_admin_email_allowlist_grants_nobody_when_unset(): void
    {
        // The allowlist is normally sourced from SUPER_ADMIN_EMAILS (set in
        // phpunit.xml for this suite, matching $this->superAdmin's email).
        // Simulate a deployment where it was never set: config empty means
        // grant nobody, not fall back to a hardcoded email list.
        config(['services.super_admin_emails' => '']);

        $response = $this->actingAs($this->superAdmin)->get('/superadmin/furniture');
        $response->assertRedirect(route('dashboard'));
    }
}
