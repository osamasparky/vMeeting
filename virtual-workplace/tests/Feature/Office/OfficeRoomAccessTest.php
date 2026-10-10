<?php

namespace Tests\Feature\Office;

use App\Domains\Guests\Models\GuestInvitation;
use App\Domains\Workspace\Models\Floor;
use App\Domains\Workspace\Models\Map;
use App\Domains\Workspace\Models\Room;
use App\Domains\Workspace\Models\RoomFile;
use App\Domains\Workspace\Support\RoomAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

class OfficeRoomAccessTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    private Room $room;

    private Room $privateRoom;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
        Storage::fake();
        $this->room = $this->makeRoom($this->org->id, 'public');
        $this->privateRoom = $this->makeRoom($this->org->id, 'private');
    }

    private function makeRoom(string $orgId, string $mode): Room
    {
        $floor = Floor::withoutGlobalScopes()->create(['organization_id' => $orgId, 'name' => 'F'.Str::random(4), 'level' => 1]);
        $map = Map::withoutGlobalScopes()->create(['organization_id' => $orgId, 'floor_id' => $floor->id, 'name' => 'Map', 'status' => 'published']);

        return Room::withoutGlobalScopes()->create([
            'organization_id' => $orgId, 'map_id' => $map->id, 'name' => 'Room '.$mode, 'type' => 'meeting',
            'access_mode' => $mode, 'capacity' => 8, 'bounds' => ['x' => 2, 'y' => 2, 'width' => 10, 'height' => 8],
        ]);
    }

    private function tokenUrl(Room $room): string
    {
        return "/organizations/{$room->organization_id}/rooms/{$room->id}/livekit-token";
    }

    private function guestSession(Room $room, int $expiresIn = 3600): array
    {
        return [RoomAccess::GUEST_SESSION_KEY => [
            'organization_id' => $room->organization_id, 'room_id' => $room->id,
            'guest_id' => 'guest_abc', 'guest_name' => 'Visitor (Guest)', 'expires_at' => time() + $expiresIn,
        ]];
    }

    public function test_media_token_needs_a_member_or_an_invited_guest(): void
    {
        // No session at all: refused (it used to hand out a guest token).
        $this->postJson($this->tokenUrl($this->room), ['guest_name' => 'x'])->assertForbidden();

        // A member of the organization: allowed.
        $this->actingAs($this->employee)->postJson($this->tokenUrl($this->room))
            ->assertOk()->assertJsonPath('participant_identity', $this->employee->id)->assertJsonPath('is_host', false);
    }

    public function test_members_of_other_organizations_and_private_rooms_are_refused(): void
    {
        $this->actingAs($this->outsider)->postJson($this->tokenUrl($this->room))->assertForbidden();
        $this->actingAs($this->employee)->postJson($this->tokenUrl($this->privateRoom))->assertForbidden();

        // Company admins may enter private rooms and host the meeting.
        $this->actingAs($this->owner)->postJson($this->tokenUrl($this->privateRoom))->assertOk()->assertJsonPath('is_host', true);
    }

    public function test_a_guest_gets_only_the_invited_room_with_their_session_identity(): void
    {
        $this->withSession($this->guestSession($this->room))
            ->postJson($this->tokenUrl($this->room), ['guest_id' => 'spoofed', 'guest_name' => 'Spoof'])
            ->assertOk()->assertJsonPath('participant_identity', 'guest_abc');

        $this->withSession($this->guestSession($this->room))->postJson($this->tokenUrl($this->privateRoom))->assertForbidden();
        $this->withSession($this->guestSession($this->room, -10))->postJson($this->tokenUrl($this->room))->assertForbidden();
    }

    public function test_entering_through_an_invitation_records_the_guest(): void
    {
        $invitation = GuestInvitation::create([
            'organization_id' => $this->org->id, 'room_id' => $this->room->id, 'invited_by' => $this->owner->id,
            'guest_name' => 'Client', 'token' => Str::random(40), 'status' => 'approved', 'expires_at' => now()->addHour(),
        ]);

        $this->post("/guest/join/{$invitation->token}", ['guest_name' => 'Client'])->assertOk();
        $this->assertSame($this->room->id, session(RoomAccess::GUEST_SESSION_KEY)['room_id']);

        $this->postJson($this->tokenUrl($this->room))->assertOk();
    }

    public function test_room_files_follow_the_same_rule(): void
    {
        $url = "/organizations/{$this->org->id}/rooms/{$this->room->id}/files";

        $this->getJson($url)->assertForbidden();
        $this->post($url, ['file' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')], ['Accept' => 'application/json'])->assertForbidden();

        // A guest of this room may list and upload, not delete.
        $this->withSession($this->guestSession($this->room))
            ->post($url, ['file' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')], ['Accept' => 'application/json'])
            ->assertCreated()->assertJsonPath('file.uploader_name', 'Visitor (Guest)');
        $file = RoomFile::first();
        $this->withSession($this->guestSession($this->room))->deleteJson("{$url}/{$file->id}")->assertForbidden();

        // Another member may not delete someone else's file; a room manager may.
        $this->actingAs($this->employee)->getJson($url)->assertOk()->assertJsonCount(1, 'files');
        $this->actingAs($this->employee)->deleteJson("{$url}/{$file->id}")->assertForbidden();
        $this->actingAs($this->owner)->deleteJson("{$url}/{$file->id}")->assertOk();
        $this->assertSame(0, RoomFile::count());
    }
}
