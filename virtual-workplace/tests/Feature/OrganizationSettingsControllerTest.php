<?php

namespace Tests\Feature;

use App\Domains\Identity\Models\User;
use App\Domains\People\Models\Department;
use App\Domains\People\Models\Team;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\Plan;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Regression coverage for OrganizationSettingsController. Four live bugs
 * were found and fixed here, none of which had any prior test coverage:
 *  - storeDepartment() was entirely swallowed by an unclosed docblock
 *    comment, so the method didn't exist — "Create Department" 500'd.
 *  - submitBankTransferPayment() referenced SubscriptionRequest with no
 *    import, resolving to a nonexistent class in this controller's own
 *    namespace — bank-transfer payment submission 500'd.
 *  - updateTeam() was routed but never implemented anywhere — "Edit Team"
 *    500'd.
 * storeMember()/getMemberProfileDetails() also gained regression coverage
 * since their logic was extracted into Actions.
 */
class OrganizationSettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['email' => 'settings-admin@example.com']);
        $this->organization = app(CreateOrganizationAction::class)->execute(['name' => 'Settings Co'], $this->admin);
    }

    public function test_a_department_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)->post('/departments', [
            'name' => 'Customer Success',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('departments', [
            'organization_id' => $this->organization->id,
            'name' => 'Customer Success',
        ]);
    }

    public function test_a_team_can_be_created_and_then_updated(): void
    {
        $department = Department::create(['organization_id' => $this->organization->id, 'name' => 'Engineering']);
        $otherDepartment = Department::create(['organization_id' => $this->organization->id, 'name' => 'Sales']);

        $storeResponse = $this->actingAs($this->admin)->post('/teams', [
            'name' => 'Platform Team',
            'department_id' => $department->id,
        ]);
        $storeResponse->assertRedirect();
        $team = Team::where('name', 'Platform Team')->firstOrFail();

        $updateResponse = $this->actingAs($this->admin)->put("/teams/{$team->id}", [
            'name' => 'Platform & Infra Team',
            'department_id' => $otherDepartment->id,
        ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => 'Platform & Infra Team',
            'department_id' => $otherDepartment->id,
        ]);
    }

    public function test_a_bank_transfer_payment_can_be_submitted(): void
    {
        $paidPlan = Plan::where('price', '>', 0)->firstOrFail();
        $receipt = UploadedFile::fake()->image('receipt.jpg');

        $response = $this->actingAs($this->admin)->post("/billing/payment/{$paidPlan->id}/submit", [
            'sender_name' => 'Settings Admin',
            'bank_name' => 'Test Bank',
            'transfer_reference' => 'REF-12345',
            'amount' => (string) $paidPlan->price,
            'currency' => 'SAR',
            'billing_cycle' => 'monthly',
            'transfer_date' => now()->toDateString(),
            'receipt' => $receipt,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('subscription_requests', [
            'organization_id' => $this->organization->id,
            'plan_id' => $paidPlan->id,
            'transfer_reference' => 'REF-12345',
            'status' => 'pending',
        ]);
    }

    public function test_a_member_can_be_invited_with_profile_and_office_access(): void
    {
        $employeeRole = \App\Domains\Administration\Models\Role::where('slug', 'employee')->whereNull('organization_id')->firstOrFail();

        $response = $this->actingAs($this->admin)->post('/organization/members/create', [
            'name' => 'New Hire',
            'email' => 'new-hire@example.com',
            'role_id' => $employeeRole->id,
            'job_title' => 'Support Engineer',
        ]);

        $response->assertRedirect('/dashboard#members');
        $this->assertDatabaseHas('users', ['email' => 'new-hire@example.com']);

        $newUser = User::where('email', 'new-hire@example.com')->firstOrFail();
        $this->assertDatabaseHas('organization_members', [
            'organization_id' => $this->organization->id,
            'user_id' => $newUser->id,
            'role_id' => $employeeRole->id,
        ]);
        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $newUser->id,
            'organization_id' => $this->organization->id,
            'job_title' => 'Support Engineer',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'organization_id' => $this->organization->id,
            'action' => 'member.created',
        ]);
    }

    public function test_member_profile_details_returns_stats_and_profile(): void
    {
        $member = \App\Domains\Tenancy\Models\OrganizationMember::where('organization_id', $this->organization->id)
            ->where('user_id', $this->admin->id)
            ->firstOrFail();

        $response = $this->actingAs($this->admin)->getJson("/organization/members/{$member->id}/details");

        $response->assertOk()
            ->assertJsonPath('member.user_id', $this->admin->id)
            ->assertJsonStructure(['member', 'profile', 'stats', 'tasks', 'time_entries']);
    }
}
