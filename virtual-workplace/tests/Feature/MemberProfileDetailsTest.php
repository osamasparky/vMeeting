<?php

namespace Tests\Feature;

use App\Domains\Administration\Models\Role;
use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\OrganizationMember;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The member profile / edit form loads its details — including for members who have logged time. */
class MemberProfileDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_details_load_for_a_member_with_time_entries(): void
    {
        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $org = app(CreateOrganizationAction::class)->execute(['name' => 'Acme'], $owner);
        $employee = User::factory()->create();
        $member = OrganizationMember::create([
            'organization_id' => $org->id, 'user_id' => $employee->id,
            'role_id' => Role::where('slug', 'employee')->value('id'), 'status' => 'active',
        ]);
        $project = Project::create(['organization_id' => $org->id, 'name' => 'P', 'code' => 'P-1', 'owner_id' => $owner->id, 'status' => 'active', 'priority' => 'medium']);
        TimeEntry::create([
            'organization_id' => $org->id, 'user_id' => $employee->id, 'project_id' => $project->id,
            'started_at' => now()->subHours(3), 'ended_at' => now()->subMinutes(45), 'duration_seconds' => 2 * 3600 + 15 * 60,
            'entry_type' => TimeEntry::TYPE_MANUAL, 'status' => TimeEntry::STATUS_DRAFT,
        ]);

        $this->actingAs($owner)->getJson(route('organization.members.details', $member))
            ->assertOk()
            ->assertJsonPath('time_entries.0.duration_formatted', '2h 15m');
    }

    public function test_workspace_settings_save_for_a_company_without_a_settings_row(): void
    {
        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
        $owner = User::factory()->create();
        $org = app(CreateOrganizationAction::class)->execute(['name' => 'Acme'], $owner);
        $org->settings()->delete();

        $this->actingAs($owner)->post(route('organization.settings.update'), ['name' => 'Acme Renamed', 'timezone' => 'Africa/Cairo'])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('Acme Renamed', $org->fresh()->name);
        $this->assertNotNull($org->fresh()->settings);
    }
}
