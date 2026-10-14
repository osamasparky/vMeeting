<?php

namespace Tests\Feature;

use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** ICE servers come from config (TURN_* in .env) only — no credentials in code. */
class MeetingIceServersTest extends TestCase
{
    use RefreshDatabase;

    private function iceServers(): array
    {
        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $org = app(CreateOrganizationAction::class)->execute(['name' => 'Acme'], $owner);

        return $this->actingAs($owner)->getJson("/organizations/{$org->id}/webrtc/diagnostics-config")
            ->assertOk()->json('ice_servers');
    }

    public function test_configured_turn_gives_stun_and_turn_entries(): void
    {
        config(['services.turn' => ['url' => 'turn:turn.example.test:3478', 'username' => 'u', 'credential' => 'c']]);

        $this->assertSame([
            ['urls' => 'stun:turn.example.test:3478'],
            ['urls' => ['turn:turn.example.test:3478?transport=udp', 'turn:turn.example.test:3478?transport=tcp'], 'username' => 'u', 'credential' => 'c'],
        ], $this->iceServers());
    }

    public function test_no_turn_configured_gives_an_empty_list(): void
    {
        config(['services.turn' => ['url' => null, 'username' => null, 'credential' => null]]);

        $this->assertSame([], $this->iceServers());
    }
}
