<?php

namespace Tests\Feature;

use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Workspace\Models\Floor;
use App\Domains\Workspace\Models\Map;
use App\Domains\Workspace\Models\Room;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Architecture Audit Phase 6 (consistency pass): named rate limiters, an
 * explicit CORS stance, uniform JSON errors on /api, and the upload
 * denylist on the two session-less upload endpoints that lacked it.
 */
class Phase6ConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Organization $organization;

    protected Room $room;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['email' => 'phase6-admin@example.com']);
        $this->organization = app(CreateOrganizationAction::class)->execute(['name' => 'Phase Six Co'], $this->admin);

        $floor = Floor::create(['organization_id' => $this->organization->id, 'name' => 'Floor 1']);
        $map = Map::create(['organization_id' => $this->organization->id, 'floor_id' => $floor->id, 'name' => 'Map', 'status' => 'published']);
        $this->room = Room::create([
            'organization_id' => $this->organization->id,
            'map_id' => $map->id,
            'name' => 'Room A',
            'bounds' => ['x' => 0, 'y' => 0, 'width' => 10, 'height' => 8],
        ]);
    }

    // ── Rate limiting ────────────────────────────────────────────────

    public function test_every_named_limiter_is_registered(): void
    {
        foreach (['api', 'expensive', 'uploads', 'chat', 'notifications', 'guest-token', 'public'] as $name) {
            $this->assertNotNull(RateLimiter::limiter($name), "Rate limiter '{$name}' is not registered.");
        }
    }

    public function test_expensive_endpoints_are_limited_to_five_requests_a_minute(): void
    {
        $this->actingAs($this->admin);

        // No API key supplied -> a cheap 422 each time (never reaches
        // OpenAI), but every attempt still counts against the limiter.
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/organization/ai-test')->assertStatus(422);
        }

        $this->postJson('/organization/ai-test')->assertStatus(429);
    }

    public function test_public_guest_token_lookups_are_rate_limited_per_ip(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->get('/guest/join/not-a-real-token')->assertOk();
        }

        $this->get('/guest/join/not-a-real-token')->assertStatus(429);
    }

    // ── CORS ─────────────────────────────────────────────────────────

    public function test_cors_is_same_origin_only_by_default(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'https://evil.example',
            'Access-Control-Request-Method' => 'GET',
        ])->options('/api/v1/auth/me');

        $this->assertNull($response->headers->get('Access-Control-Allow-Origin'));
    }

    public function test_cors_allows_an_explicitly_configured_origin(): void
    {
        config(['cors.allowed_origins' => ['https://app.example.test']]);

        $response = $this->withHeaders([
            'Origin' => 'https://app.example.test',
            'Access-Control-Request-Method' => 'GET',
        ])->options('/api/v1/auth/me');

        $this->assertSame('https://app.example.test', $response->headers->get('Access-Control-Allow-Origin'));
    }

    // ── Uniform API errors ───────────────────────────────────────────

    public function test_unauthenticated_api_calls_get_json_even_without_an_accept_header(): void
    {
        $response = $this->get('/api/v1/auth/me');

        $response->assertStatus(401);
        $response->assertHeader('Content-Type', 'application/json');
    }

    // ── Session-less upload endpoints ───────────────────────────────

    public function test_room_file_upload_rejects_executable_files_even_without_a_session(): void
    {
        Storage::fake('local');

        $response = $this->postJson(
            "/organizations/{$this->organization->id}/rooms/{$this->room->id}/files",
            ['file' => UploadedFile::fake()->createWithContent('shell.php', '<?php system($_GET["c"]); ?>')]
        );

        $response->assertStatus(422);
        $this->assertDatabaseCount('room_files', 0);
    }

    public function test_room_file_upload_rejects_a_room_from_another_organization(): void
    {
        Storage::fake('local');

        $otherOwner = User::factory()->create(['email' => 'phase6-other@example.com']);
        $otherOrg = app(CreateOrganizationAction::class)->execute(['name' => 'Other Co'], $otherOwner);

        $response = $this->postJson(
            "/organizations/{$otherOrg->id}/rooms/{$this->room->id}/files",
            ['file' => UploadedFile::fake()->create('notes.txt', 5, 'text/plain')]
        );

        $response->assertStatus(403);
        $this->assertDatabaseCount('room_files', 0);
    }

    public function test_room_file_upload_still_accepts_an_ordinary_document(): void
    {
        Storage::fake('local');

        $response = $this->postJson(
            "/organizations/{$this->organization->id}/rooms/{$this->room->id}/files",
            ['file' => UploadedFile::fake()->create('agenda.pdf', 20, 'application/pdf')]
        );

        $response->assertStatus(201);
        $this->assertDatabaseHas('room_files', ['room_id' => $this->room->id, 'name' => 'agenda.pdf']);
    }

    public function test_chat_attachment_upload_rejects_executable_files(): void
    {
        Storage::fake('local');

        $response = $this->postJson(
            "/organizations/{$this->organization->id}/chat/upload",
            ['file' => UploadedFile::fake()->createWithContent('run.phtml', '<?php echo 1;')]
        );

        $response->assertStatus(422);
    }
}
