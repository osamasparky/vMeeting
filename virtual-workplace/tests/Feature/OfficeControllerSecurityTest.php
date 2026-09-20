<?php

namespace Tests\Feature;

use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\Organization;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * OfficeController::uploadObjectImage() allowed .svg uploads with zero
 * content scanning, moving them straight to the public webroot — an
 * exploitable stored-XSS vector, the same class of bug already fixed for
 * project/task attachments in Phase 0. These tests lock in the fix.
 */
class OfficeControllerSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->user = User::factory()->create(['email' => 'office-security@example.com']);
        $this->organization = app(CreateOrganizationAction::class)->execute(['name' => 'Office Security Co'], $this->user);
    }

    public function test_an_svg_with_an_embedded_script_is_rejected_as_a_custom_object_image(): void
    {
        $maliciousSvg = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(document.cookie)</script></svg>';
        $file = UploadedFile::fake()->createWithContent('evil.svg', $maliciousSvg);

        $response = $this->actingAs($this->user)->postJson('/editor/upload-object-image', [
            'image' => $file,
        ]);

        $response->assertStatus(422);
        $this->assertFileDoesNotExist(public_path('images/custom_objects/'.$file->hashName()));
    }

    public function test_an_ordinary_png_is_still_accepted_as_a_custom_object_image(): void
    {
        $file = UploadedFile::fake()->image('desk-icon.png', 64, 64);

        $response = $this->actingAs($this->user)->postJson('/editor/upload-object-image', [
            'image' => $file,
        ]);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertNotEmpty($response->json('url'));
    }
}
