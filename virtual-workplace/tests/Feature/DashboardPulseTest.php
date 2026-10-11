<?php

namespace Tests\Feature;

use App\Domains\People\Models\AttendanceSession;
use App\Domains\Projects\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Projects\Concerns\BuildsProjectWorkspace;
use Tests\TestCase;

class DashboardPulseTest extends TestCase
{
    use BuildsProjectWorkspace, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildProjectWorkspace();
    }

    private function inOffice($user, int $heartbeatSecondsAgo): void
    {
        AttendanceSession::withoutGlobalScopes()->create([
            'organization_id' => $this->org->id, 'user_id' => $user->id, 'status' => 'active',
            'started_at' => now()->subHour(), 'last_heartbeat_at' => now()->subSeconds($heartbeatSecondsAgo),
        ]);
    }

    public function test_online_now_lists_only_people_with_a_recent_heartbeat(): void
    {
        $this->inOffice($this->employee, 30);
        $this->inOffice($this->otherEmployee, 600); // left the tab open long ago: not online

        $res = $this->actingAs($this->owner)->getJson('/dashboard/pulse')->assertOk();
        $this->assertSame(1, $res->json('count'));
        $this->assertSame($this->employee->id, $res->json('online.0.id'));
    }

    public function test_owner_sees_company_kpis_and_employees_only_their_own(): void
    {
        $this->inOffice($this->employee, 10);
        Task::withoutGlobalScopes()->where('organization_id', $this->org->id)->update(['due_date' => now()->subDays(3), 'status' => 'in_progress']);

        $owner = $this->actingAs($this->owner)->get('/dashboard')->assertOk();
        $owner->assertSee(__('dash.kpi_title'))->assertSee('href="'.route('dashboard').'#all-tasks"', false);
        $this->assertSame(1, $owner->viewData('ownerKpis')['online']);
        $this->assertGreaterThanOrEqual(1, $owner->viewData('ownerKpis')['overdue_tasks']);

        $employee = $this->actingAs($this->employee)->get('/dashboard')->assertOk();
        $this->assertNull($employee->viewData('ownerKpis'));
        $employee->assertDontSee(__('dash.kpi_title'));
        $this->assertGreaterThanOrEqual(1, $employee->viewData('memberKpis')['my_overdue_tasks']);
    }
}
