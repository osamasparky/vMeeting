<?php

namespace Tests\Feature;

use App\Domains\Administration\Models\Role;
use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\Task;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\OrganizationMember;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The All Tasks filter reads data-* attributes off each row and board card: they must
 * carry every assignee and the task number the search box promises.
 */
class DashboardAllTasksFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_rows_and_cards_carry_co_assignees_and_task_number(): void
    {
        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create(['email' => 'admin@tenant-a.com']);
        $helper = User::factory()->create(['email' => 'helper@tenant-a.com']);
        $org = app(CreateOrganizationAction::class)->execute(['name' => 'Tenant Alpha'], $admin);
        OrganizationMember::create([
            'organization_id' => $org->id,
            'user_id' => $helper->id,
            'role_id' => Role::where('slug', 'employee')->whereNull('organization_id')->value('id'),
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $project = Project::create(['organization_id' => $org->id, 'name' => 'Ops', 'code' => 'OPS', 'owner_id' => $admin->id, 'status' => 'active']);
        $task = Task::create([
            'organization_id' => $org->id, 'project_id' => $project->id, 'title' => 'Fix Printer',
            'status' => 'qa', 'priority' => 'high', 'assignee_id' => $admin->id, 'reporter_id' => $admin->id,
        ]);
        $task->assignees()->attach($helper->id);

        $html = $this->actingAs($admin)->get('/dashboard')->assertOk()->getContent();

        $number = $task->fresh()->task_number;
        $this->assertStringContainsString('data-title="fix printer #'.$number.' ops-'.sprintf('%02d', $number).'"', $html);
        $this->assertMatchesRegularExpression('/data-assignees="[^"]*'.preg_quote((string) $helper->id, '/').'/', $html);
        // The table row reports legacy "qa" as "review", matching the Review / QA filter option.
        $this->assertMatchesRegularExpression('/class="alltask-row"[^>]*data-status="review"/s', $html);
    }
}
