<?php

namespace Tests\Feature;

use App\Domains\Identity\Models\User;
use App\Domains\Meetings\Models\Meeting;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\Organization;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Regression coverage for DashboardController::dashboard() and
 * ::storeScheduledMeeting(), whose logic was extracted into
 * BuildOrganizationDashboardAction and ScheduleMeetingAction respectively.
 * Neither endpoint had any prior test coverage.
 */
class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->user = User::factory()->create(['email' => 'dashboard-owner@example.com']);
        $this->organization = app(CreateOrganizationAction::class)->execute(['name' => 'Dashboard Co'], $this->user);
    }

    public function test_dashboard_loads_with_expected_view_data_and_provisions_default_workspace(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertOk();
        $response->assertViewIs('dashboard');
        $response->assertViewHas('organization', fn ($org) => $org->id === $this->organization->id);
        $response->assertViewHas('stats');
        $response->assertViewHas('projectMembersMap');

        // ensureDefaultWorkspace() should have provisioned a default floor and departments.
        $this->assertGreaterThan(0, $this->organization->floors()->count());
        $this->assertGreaterThan(0, $this->organization->departments()->count());
    }

    public function test_general_meeting_can_be_scheduled_with_explicit_attendees(): void
    {
        Mail::fake();

        $attendee = User::factory()->create(['email' => 'attendee@example.com']);

        $response = $this->actingAs($this->user)->post('/meetings/schedule', [
            'title' => 'Weekly Sync',
            'scope' => 'general',
            'scheduled_at' => now()->addDay()->toDateTimeString(),
            'attendee_ids' => [$attendee->id],
        ]);

        $response->assertRedirect('/dashboard#meetings');
        $this->assertDatabaseHas('meetings', [
            'organization_id' => $this->organization->id,
            'title' => 'Weekly Sync',
            'scope' => 'general',
        ]);

        $meeting = Meeting::where('title', 'Weekly Sync')->firstOrFail();
        $this->assertDatabaseHas('meeting_participants', [
            'meeting_id' => $meeting->id,
            'user_id' => $this->user->id,
            'role' => 'host',
        ]);
        $this->assertDatabaseHas('meeting_participants', [
            'meeting_id' => $meeting->id,
            'user_id' => $attendee->id,
            'role' => 'participant',
        ]);
    }
}
