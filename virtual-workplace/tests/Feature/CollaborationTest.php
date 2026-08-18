<?php

namespace Tests\Feature;

use App\Domains\Chat\Models\Channel;
use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Workspace\Models\Floor;
use App\Domains\Workspace\Models\Map;
use App\Domains\Workspace\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CollaborationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $colleague;
    protected Organization $organization;
    protected Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\PlansSeeder::class);
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->user = User::factory()->create(['email' => 'founder@acme.test']);
        $this->colleague = User::factory()->create(['email' => 'engineer@acme.test']);

        $createOrg = app(CreateOrganizationAction::class);
        $this->organization = $createOrg->execute(['name' => 'Acme Collaboration'], $this->user);

        // Add colleague as active member
        $memberRole = \App\Domains\Administration\Models\Role::where('name', 'Employee')->first();
        $this->organization->members()->create([
            'user_id' => $this->colleague->id,
            'role_id' => $memberRole->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $floor = Floor::create([
            'organization_id' => $this->organization->id,
            'name' => 'HQ 1',
        ]);

        $map = Map::create([
            'organization_id' => $this->organization->id,
            'floor_id' => $floor->id,
            'name' => 'Floor Map',
            'status' => 'published',
        ]);

        $this->room = Room::create([
            'organization_id' => $this->organization->id,
            'map_id' => $map->id,
            'name' => 'Live Conference Room',
            'bounds' => ['x' => 0, 'y' => 0, 'width' => 10, 'height' => 8],
        ]);
    }

    public function test_can_create_dm_and_exchange_messages(): void
    {
        Sanctum::actingAs($this->user);

        // 1. Get or create DM
        $dmResponse = $this->getJson("/api/v1/organizations/{$this->organization->id}/users/{$this->colleague->id}/dm");
        $dmResponse->assertStatus(200)
            ->assertJsonPath('channel.type', 'dm');

        $channelId = $dmResponse->json('channel.id');

        // 2. Send message
        $sendResponse = $this->postJson("/api/v1/organizations/{$this->organization->id}/channels/{$channelId}/messages", [
            'body' => 'Hey, are you free for a quick spatial sync?',
        ]);

        $sendResponse->assertStatus(201)
            ->assertJsonPath('message.body', 'Hey, are you free for a quick spatial sync?');

        // 3. Colleague views messages
        Sanctum::actingAs($this->colleague);
        $listResponse = $this->getJson("/api/v1/organizations/{$this->organization->id}/channels/{$channelId}/messages");
        $listResponse->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_start_meeting_and_mint_livekit_webrtc_token(): void
    {
        Sanctum::actingAs($this->user);

        // 1. Request LiveKit Token for Room
        $tokenResponse = $this->postJson("/api/v1/organizations/{$this->organization->id}/rooms/{$this->room->id}/livekit-token");
        $tokenResponse->assertStatus(200)
            ->assertJsonStructure(['token', 'livekit_host', 'room_name']);

        // 2. Create Meeting
        $meetingResponse = $this->postJson("/api/v1/organizations/{$this->organization->id}/meetings", [
            'title' => 'Q3 Architecture Sprint',
            'room_id' => $this->room->id,
            'type' => 'instant',
        ]);

        $meetingResponse->assertStatus(201)
            ->assertJsonPath('meeting.title', 'Q3 Architecture Sprint')
            ->assertJsonPath('meeting.status', 'active');

        $meetingId = $meetingResponse->json('meeting.id');

        // 3. End Meeting
        $endResponse = $this->postJson("/api/v1/organizations/{$this->organization->id}/meetings/{$meetingId}/end");
        $endResponse->assertStatus(200)
            ->assertJsonPath('meeting.status', 'ended');
    }

    public function test_can_create_and_validate_guest_invitation(): void
    {
        Sanctum::actingAs($this->user);

        // 1. Create Guest Invitation
        $inviteResponse = $this->postJson("/api/v1/organizations/{$this->organization->id}/rooms/{$this->room->id}/guest-invitations", [
            'guest_name' => 'Investor / Partner',
            'guest_email' => 'guest@venture.test',
            'expires_in_hours' => 12,
        ]);

        $inviteResponse->assertStatus(201)
            ->assertJsonStructure(['invitation', 'join_url']);

        $token = $inviteResponse->json('invitation.token');

        // 2. Public verification of invitation token (unauthenticated guest)
        $publicResponse = $this->getJson("/api/v1/guest-invitations/{$token}");
        $publicResponse->assertStatus(200)
            ->assertJsonPath('valid', true)
            ->assertJsonPath('invitation.guest_name', 'Investor / Partner')
            ->assertJsonPath('invitation.organization.name', 'Acme Collaboration');

        // 3. Guest opens web lobby screen
        $lobbyResponse = $this->get("/guest/join/{$token}");
        $lobbyResponse->assertStatus(200)
            ->assertSee('Acme Collaboration')
            ->assertSee('Live Conference Room');

        // 4. Guest enters virtual workplace
        $enterResponse = $this->post("/guest/join/{$token}", [
            'guest_name' => 'Investor / Partner',
        ]);
        $enterResponse->assertStatus(200)
            ->assertSee('GUEST ACCESS')
            ->assertSee('Investor / Partner');
    }

    public function test_host_and_guest_can_access_office_and_mint_realtime_presence_tokens(): void
    {
        // 1. Host (Member) enters Office View
        $hostResponse = $this->actingAs($this->user)->get('/office');
        $hostResponse->assertStatus(200)
            ->assertSee('Acme Collaboration')
            ->assertSee('office-canvas');

        // Host mints Realtime Token
        $hostTokenResp = $this->actingAs($this->user)->postJson("/api/v1/organizations/{$this->organization->id}/realtime-token");
        $hostTokenResp->assertStatus(200)
            ->assertJsonStructure(['token', 'ws_url']);

        // 2. Create Guest Invitation
        $invite = \App\Domains\Guests\Models\GuestInvitation::create([
            'organization_id' => $this->organization->id,
            'room_id' => $this->room->id,
            'invited_by' => $this->user->id,
            'token' => 'test-guest-token-123456789',
            'guest_name' => 'Sara Investor',
            'guest_email' => 'sara@invest.test',
            'expires_at' => now()->addHours(2),
            'status' => 'pending',
        ]);

        // 3. Guest Enters /office via Invitation Post
        $guestOfficeResp = $this->post("/guest/join/{$invite->token}", [
            'guest_name' => 'Sara Investor',
        ]);
        $guestOfficeResp->assertStatus(200)
            ->assertSee('Sara Investor')
            ->assertSee('GUEST ACCESS')
            ->assertSee('office-canvas');
    }

    public function test_can_upload_and_list_session_recordings(): void
    {
        Sanctum::actingAs($this->user);
        \Illuminate\Support\Facades\Storage::fake('public');

        $fakeVideo = \Illuminate\Http\UploadedFile::fake()->create('session_test.webm', 1024, 'video/webm');

        // 1. Upload Recording
        $uploadResp = $this->postJson("/api/v1/organizations/{$this->organization->id}/recordings", [
            'video' => $fakeVideo,
            'title' => 'Executive Strategic Meeting Q3',
            'room_id' => $this->room->id,
            'duration_seconds' => 125,
        ]);

        $uploadResp->assertStatus(201)
            ->assertJsonPath('recording.title', 'Executive Strategic Meeting Q3')
            ->assertJsonPath('recording.duration_seconds', 125);

        $recordingId = $uploadResp->json('recording.id');

        // 2. List Recordings
        $listResp = $this->getJson("/api/v1/organizations/{$this->organization->id}/recordings");
        $listResp->assertStatus(200)
            ->assertJsonCount(1, 'recordings');

        // 3. Delete Recording
        $delResp = $this->deleteJson("/api/v1/organizations/{$this->organization->id}/recordings/{$recordingId}");
        $delResp->assertStatus(200);

        $listAfter = $this->getJson("/api/v1/organizations/{$this->organization->id}/recordings");
        $listAfter->assertJsonCount(0, 'recordings');
    }
}
