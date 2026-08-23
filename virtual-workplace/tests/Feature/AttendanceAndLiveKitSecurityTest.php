<?php

namespace Tests\Feature;

use App\Domains\Identity\Models\User;
use App\Domains\Meetings\Services\LiveKitTokenService;
use App\Domains\People\Models\AttendanceSession;
use App\Domains\People\Services\AttendanceService;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use App\Domains\Workspace\Models\Floor;
use App\Domains\Workspace\Models\Map;
use App\Domains\Workspace\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceAndLiveKitSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organization $organization;
    protected Room $room;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlansSeeder::class);
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $action = app(\App\Domains\Tenancy\Actions\CreateOrganizationAction::class);

        $this->user = User::factory()->create([
            'email' => 'member@example.com',
            'name' => 'Test Member',
        ]);

        $this->organization = $action->execute(['name' => 'Acme Corp'], $this->user);

        $floor = Floor::create([
            'organization_id' => $this->organization->id,
            'name' => 'Main Floor',
            'level' => 1,
        ]);

        $map = Map::create([
            'organization_id' => $this->organization->id,
            'floor_id' => $floor->id,
            'name' => 'Default Map',
            'status' => 'published',
        ]);

        $this->room = Room::create([
            'organization_id' => $this->organization->id,
            'map_id' => $map->id,
            'name' => 'Executive Boardroom',
            'type' => 'meeting',
            'access_mode' => 'public',
            'capacity' => 10,
            'bounds' => [
                'x' => 100,
                'y' => 100,
                'width' => 200,
                'height' => 150,
            ],
        ]);
    }

    public function test_attendance_service_tracks_enter_heartbeat_and_leave(): void
    {
        $service = app(AttendanceService::class);

        // 1. Enter room
        $session = $service->startSession($this->user, $this->organization, $this->room->id);
        $this->assertInstanceOf(AttendanceSession::class, $session);
        $this->assertEquals('active', $session->status);
        $this->assertEquals($this->room->id, $session->room_id);
        $this->assertNull($session->ended_at);

        // 2. Heartbeat
        $updated = $service->recordHeartbeat($this->user, $this->organization, $this->room->id, 120);
        $this->assertEquals(120, $updated->duration_seconds);

        // 3. Leave room
        $closed = $service->endSession($this->user, $this->organization, $this->room->id);
        $this->assertEquals('completed', $closed->status);
        $this->assertNotNull($closed->ended_at);
    }

    public function test_attendance_service_cleans_up_stale_sessions(): void
    {
        $service = app(AttendanceService::class);

        $session = AttendanceSession::create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'status' => 'active',
            'started_at' => now()->subMinutes(30),
            'last_heartbeat_at' => now()->subMinutes(15),
        ]);

        $cleanedCount = $service->cleanupStaleSessions($this->user->id);
        $this->assertEquals(1, $cleanedCount);

        $session->refresh();
        $this->assertEquals('timed_out', $session->status);
        $this->assertNotNull($session->ended_at);
        $this->assertGreaterThan(0, $session->duration_seconds);
    }

    public function test_attendance_http_endpoints(): void
    {
        $this->actingAs($this->user);

        // 1. Log enter
        $response = $this->postJson('/api/office/attendance/log', [
            'room_id' => $this->room->id,
            'action' => 'enter',
        ]);
        $response->assertOk()->assertJson(['status' => 'logged']);

        $this->assertDatabaseHas('attendance_sessions', [
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'status' => 'active',
        ]);

        // 2. Log heartbeat
        $response = $this->postJson('/api/office/attendance/log', [
            'room_id' => $this->room->id,
            'action' => 'heartbeat',
            'duration_seconds' => 60,
        ]);
        $response->assertOk();

        // 3. Get summary
        $summary = $this->getJson('/api/office/attendance/summary?period=week');
        $summary->assertOk()
            ->assertJsonStructure([
                'period',
                'total_seconds',
                'total_hours',
                'sessions_count',
                'daily_breakdown',
            ]);
    }

    public function test_livekit_token_service_generates_valid_signed_token(): void
    {
        $tokenService = app(LiveKitTokenService::class);
        $token = $tokenService->generateRoomToken($this->user, $this->room, true);

        $this->assertNotEmpty($token);
        $parts = explode('.', $token);
        $this->assertCount(3, $parts, 'LiveKit token must be a valid JWT with 3 parts');

        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        $this->assertEquals($this->user->id, $payload['sub']);
        $this->assertEquals($this->user->name, $payload['name']);
        $this->assertArrayHasKey('video', $payload);
        $this->assertTrue($payload['video']['roomJoin']);
        $this->assertEquals("org_{$this->organization->id}_room_{$this->room->id}", $payload['video']['room']);
    }

    public function test_livekit_guest_token_generation(): void
    {
        $tokenService = app(LiveKitTokenService::class);
        $token = $tokenService->generateGuestRoomToken(
            'guest_12345',
            'Guest Alex',
            $this->room
        );

        $this->assertNotEmpty($token);
        $parts = explode('.', $token);
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        $this->assertEquals('guest_12345', $payload['sub']);
        $this->assertEquals('Guest Alex', $payload['name']);
        $this->assertTrue($payload['video']['roomJoin']);
    }

    public function test_livekit_room_token_http_endpoint(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson("/organizations/{$this->organization->id}/rooms/{$this->room->id}/livekit-token");
        $response->assertOk()
            ->assertJsonStructure([
                'token',
                'livekit_host',
                'room_name',
                'participant_identity',
            ]);
    }
}
