<?php

namespace Tests\Feature\Hr;

use App\Domains\Identity\Models\User;
use App\Domains\People\Services\LeaveService;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Hr\Concerns\BuildsHrWorkspace;
use Tests\TestCase;

class EmploymentRecordTest extends TestCase
{
    use BuildsHrWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildHrWorkspace();
    }

    private function payload(array $extra = []): array
    {
        $member = $this->memberOf($this->employee);

        return ['name' => $this->employee->name, 'email' => $this->employee->email, 'role_id' => $member->role_id, 'status' => 'active'] + $extra;
    }

    public function test_the_owner_records_hire_date_type_manager_and_leave_entitlement(): void
    {
        $member = $this->memberOf($this->employee);

        $this->actingAs($this->owner)->put(route('organization.members.update', $member), $this->payload([
            'hire_date' => '2024-03-01', 'employment_type' => 'full_time', 'manager_user_id' => $this->owner->id, 'annual_leave_days' => 30,
        ]))->assertSessionHasNoErrors();

        $member->refresh();
        $this->assertSame('2024-03-01', $member->hire_date->format('Y-m-d'));
        $this->assertSame('full_time', $member->employment_type);
        $this->assertSame($this->owner->id, $member->manager_user_id);
        $this->assertSame(30, $member->annual_leave_days);
        $this->assertSame(30, app(LeaveService::class)->balance($this->employee->id, $this->organization->id, 2026)['entitled']);

        $details = $this->actingAs($this->owner)->getJson(route('organization.members.details', $member))->assertOk()->json('member');
        $this->assertSame('2024-03-01', $details['hire_date']);
        $this->assertSame($this->owner->name, $details['manager_name']);
        $this->assertSame(__('Full-time'), $details['employment_type_label']);
    }

    public function test_leaving_the_entitlement_blank_keeps_it(): void
    {
        $member = $this->memberOf($this->employee);
        $member->update(['annual_leave_days' => 25]);

        $this->actingAs($this->owner)->put(route('organization.members.update', $member), $this->payload(['annual_leave_days' => '']))
            ->assertSessionHasNoErrors();
        $this->assertSame(25, $member->fresh()->annual_leave_days);
    }

    public function test_invalid_employment_values_are_refused(): void
    {
        $member = $this->memberOf($this->employee);
        $outsider = User::factory()->create();
        app(CreateOrganizationAction::class)->execute(['name' => 'Other'], $outsider);

        $this->actingAs($this->owner)->put(route('organization.members.update', $member), $this->payload(['manager_user_id' => $this->employee->id]))
            ->assertSessionHasErrors('manager_user_id'); // not their own manager
        $this->actingAs($this->owner)->put(route('organization.members.update', $member), $this->payload(['manager_user_id' => $outsider->id]))
            ->assertSessionHasErrors('manager_user_id'); // not someone outside the company
        $this->actingAs($this->owner)->put(route('organization.members.update', $member), $this->payload(['employment_type' => 'freelance']))
            ->assertSessionHasErrors('employment_type');
        $this->actingAs($this->owner)->put(route('organization.members.update', $member), $this->payload(['annual_leave_days' => 400]))
            ->assertSessionHasErrors('annual_leave_days');

        $this->assertNull($member->fresh()->manager_user_id);
    }

    public function test_an_employee_cannot_edit_the_employment_record(): void
    {
        $member = $this->memberOf($this->employee);

        $this->actingAs($this->employee)->put(route('organization.members.update', $member), $this->payload(['annual_leave_days' => 365]))
            ->assertForbidden();
        $this->assertNull($member->fresh()->annual_leave_days, 'still on the office default');
    }
}
