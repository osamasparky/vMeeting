<?php

namespace Tests\Feature;

use App\Domains\Identity\Models\User;
use App\Domains\Tenancy\Models\OrganizationMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

/**
 * Every API request from the page checks the session's "password_hash_web" against the signed-in
 * user and logs the session out on a mismatch. Switching user (super admin entering a company, an
 * owner acting as a member, and back) must therefore move that hash to the new user.
 */
class ImpersonationSessionTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
        // Distinct passwords, so each user's hash is different (factory users share one).
        foreach ([$this->owner, $this->manager, $this->employee, $this->otherEmployee] as $i => $u) {
            $u->forceFill(['password' => bcrypt("pass-{$i}")])->save();
        }
    }

    public function test_entering_and_leaving_a_company_moves_the_session_to_the_new_user(): void
    {
        $super = User::factory()->create(['is_super_admin' => true, 'password' => bcrypt('super-pass')]);

        $this->actingAs($super)->withSession(['password_hash_web' => $super->getAuthPassword()])
            ->post("/superadmin/companies/{$this->org->id}/impersonate")
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('superadmin_impersonator_id', $super->id);
        $inside = auth()->user();
        $this->assertNotSame($super->id, $inside->id);
        $this->assertSame($inside->getAuthPassword(), session('password_hash_web'), 'the session follows the company admin');

        $this->post('/impersonate/leave')
            ->assertRedirect(route('superadmin.companies'))
            ->assertSessionHas('password_hash_web', $super->getAuthPassword());
        $this->assertAuthenticatedAs($super);
    }

    public function test_acting_as_a_member_and_returning_moves_the_session_too(): void
    {
        $member = OrganizationMember::where('organization_id', $this->org->id)->where('user_id', $this->employee->id)->first();

        $this->actingAs($this->owner)->withSession(['password_hash_web' => $this->owner->getAuthPassword()])
            ->post("/organization/members/{$member->id}/impersonate")
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('password_hash_web', $this->employee->getAuthPassword());
        $this->assertAuthenticatedAs($this->employee);

        $this->post('/organization/impersonate/leave')
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('password_hash_web', $this->owner->getAuthPassword());
        $this->assertAuthenticatedAs($this->owner);
    }
}
