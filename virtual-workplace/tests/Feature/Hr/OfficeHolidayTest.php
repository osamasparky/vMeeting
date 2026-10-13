<?php

namespace Tests\Feature\Hr;

use App\Domains\Identity\Models\User;
use App\Domains\People\Models\OfficeHoliday;
use App\Domains\People\Services\HolidayCalendar;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Workspace\Models\Floor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Hr\Concerns\BuildsHrWorkspace;
use Tests\TestCase;

class OfficeHolidayTest extends TestCase
{
    use BuildsHrWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildHrWorkspace();
    }

    public function test_the_calendar_has_the_offices_own_and_the_company_wide_holidays(): void
    {
        $cairo = Floor::create(['organization_id' => $this->organization->id, 'name' => 'Cairo', 'timezone' => 'Africa/Cairo']);
        OfficeHoliday::create(['organization_id' => $this->organization->id, 'floor_id' => null, 'name' => 'Eid', 'start_date' => '2026-10-20', 'end_date' => '2026-10-22']);
        OfficeHoliday::create(['organization_id' => $this->organization->id, 'floor_id' => $this->office->id, 'name' => 'Saudi National Day', 'start_date' => '2026-10-25', 'end_date' => '2026-10-25']);
        OfficeHoliday::create(['organization_id' => $this->organization->id, 'floor_id' => $cairo->id, 'name' => 'Armed Forces Day', 'start_date' => '2026-10-06', 'end_date' => '2026-10-06']);

        $riyadh = app(HolidayCalendar::class)->between($this->organization->id, $this->office->id, '2026-10-01', '2026-10-31');
        $this->assertSame(['2026-10-20' => 'Eid', '2026-10-21' => 'Eid', '2026-10-22' => 'Eid', '2026-10-25' => 'Saudi National Day'], $riyadh);

        $cairoDays = app(HolidayCalendar::class)->between($this->organization->id, $cairo->id, '2026-10-01', '2026-10-21');
        $this->assertSame(['2026-10-06' => 'Armed Forces Day', '2026-10-20' => 'Eid', '2026-10-21' => 'Eid'], $cairoDays);
    }

    public function test_another_organizations_holidays_are_not_included(): void
    {
        $other = app(CreateOrganizationAction::class)->execute(['name' => 'Other'], User::factory()->create());
        OfficeHoliday::create(['organization_id' => $other->id, 'name' => 'Theirs', 'start_date' => '2026-10-20', 'end_date' => '2026-10-20']);

        $this->assertSame([], app(HolidayCalendar::class)->between($this->organization->id, $this->office->id, '2026-10-01', '2026-10-31'));
    }

    public function test_the_owner_adds_and_removes_a_holiday(): void
    {
        $this->actingAs($this->owner)->post(route('hr.holidays.store'), [
            'name' => 'Eid al-Fitr', 'start_date' => '2027-03-20', 'end_date' => '2027-03-23', 'floor_id' => $this->office->id,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $holiday = OfficeHoliday::where('name', 'Eid al-Fitr')->firstOrFail();
        $this->assertSame(4, $holiday->days());
        $this->assertSame($this->office->id, $holiday->floor_id);

        $this->actingAs($this->owner)->get(route('dashboard'))->assertOk()->assertDontSee('Eid al-Fitr'); // loaded on demand
        $this->actingAs($this->owner)->get(route('hr.panel', 'holidays'))->assertOk()->assertSee('Eid al-Fitr');
        $this->actingAs($this->employee)->get(route('hr.panel', 'holidays'))->assertForbidden();
        $this->actingAs($this->owner)->get('/hr/panel/nope')->assertNotFound();

        $this->actingAs($this->owner)->delete(route('hr.holidays.delete', $holiday))->assertRedirect();
        $this->assertModelMissing($holiday);
    }

    public function test_a_holiday_must_end_on_or_after_it_starts_and_last_at_most_sixty_days(): void
    {
        $this->actingAs($this->owner)->post(route('hr.holidays.store'), ['name' => 'X', 'start_date' => '2027-03-20', 'end_date' => '2027-03-19'])
            ->assertSessionHasErrors('end_date');
        $this->actingAs($this->owner)->post(route('hr.holidays.store'), ['name' => 'X', 'start_date' => '2027-01-01', 'end_date' => '2027-06-01'])
            ->assertSessionHasErrors('end_date');
        $this->assertSame(0, OfficeHoliday::count());
    }

    public function test_an_employee_cannot_manage_holidays(): void
    {
        $this->actingAs($this->employee)->post(route('hr.holidays.store'), ['name' => 'X', 'start_date' => '2027-01-01', 'end_date' => '2027-01-01'])
            ->assertForbidden();

        $holiday = OfficeHoliday::create(['organization_id' => $this->organization->id, 'name' => 'Eid', 'start_date' => '2027-01-01', 'end_date' => '2027-01-01']);
        $this->actingAs($this->employee)->delete(route('hr.holidays.delete', $holiday))->assertForbidden();
        $this->assertModelExists($holiday);
    }

    public function test_an_owner_cannot_touch_another_organizations_holiday_or_office(): void
    {
        $other = app(CreateOrganizationAction::class)->execute(['name' => 'Other'], User::factory()->create());
        $foreignOffice = Floor::create(['organization_id' => $other->id, 'name' => 'Theirs']);
        $theirs = OfficeHoliday::create(['organization_id' => $other->id, 'name' => 'Theirs', 'start_date' => '2027-01-01', 'end_date' => '2027-01-01']);

        $this->actingAs($this->owner)->delete(route('hr.holidays.delete', $theirs))->assertForbidden();
        $this->assertModelExists($theirs);

        $this->actingAs($this->owner)->post(route('hr.holidays.store'), ['name' => 'X', 'start_date' => '2027-01-01', 'end_date' => '2027-01-01', 'floor_id' => $foreignOffice->id])
            ->assertSessionHasErrors('floor_id');
    }
}
