<?php

namespace Tests\Feature;

use App\Domains\Administration\Models\Role;
use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\OrganizationMember;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Web pages find "the user's organisation" with OrganizationMember::forUser().
 * For a user in several organisations it must always be the same one: the
 * first they joined — not whichever row the database happens to return.
 */
class CurrentMembershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_a_user_in_two_organisations_resolves_to_the_first_one_joined(): void
    {
        $user = User::factory()->create();
        $employee = Role::where('slug', 'employee')->value('id');
        $first = app(CreateOrganizationAction::class)->execute(['name' => 'First Company'], User::factory()->create());
        $second = app(CreateOrganizationAction::class)->execute(['name' => 'Second Company'], User::factory()->create());

        // The newer membership is stored first, so without a fixed order the database would return it.
        OrganizationMember::create(['organization_id' => $second->id, 'user_id' => $user->id, 'role_id' => $employee, 'status' => 'active']);
        $this->travelTo(now()->subDays(10));
        OrganizationMember::create(['organization_id' => $first->id, 'user_id' => $user->id, 'role_id' => $employee, 'status' => 'active']);
        $this->travelBack();
        $this->assertSame($second->id, OrganizationMember::where('user_id', $user->id)->first()->organization_id, 'precondition: unordered lookup returns the newer membership');

        $this->assertSame($first->id, OrganizationMember::forUser($user->id)->first()->organization_id);
        $this->assertSame([$first->id, $second->id], OrganizationMember::forUser($user->id)->pluck('organization_id')->all());

        // The web pages act in that organisation.
        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee('First Company')->assertDontSee('Second Company');
    }

    public function test_a_single_membership_is_unchanged(): void
    {
        $user = User::factory()->create();
        $org = app(CreateOrganizationAction::class)->execute(['name' => 'Only Company'], $user);

        $this->assertSame($org->id, OrganizationMember::forUser($user->id)->first()->organization_id);
        $this->assertNull(OrganizationMember::forUser(null)->first());
    }
}
