<?php

namespace Tests\Feature\Projects;

use App\Domains\Administration\Models\Role;
use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\ActiveTimer;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\ProjectMember;
use App\Domains\Projects\Models\ProjectMilestone;
use App\Domains\Projects\Models\ProjectPhase;
use App\Domains\Projects\Models\Task;
use App\Domains\Projects\Models\TaskAttachment;
use App\Domains\Projects\Models\TaskChecklistItem;
use App\Domains\Projects\Models\TaskComment;
use App\Domains\Projects\Models\TaskDependency;
use App\Domains\Projects\Models\TimeEntry;
use App\Domains\Projects\Models\Timesheet;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase1FoundationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $user2;
    protected Organization $org;
    protected OrganizationMember $membership;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\PlansSeeder::class);
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $action = app(CreateOrganizationAction::class);
        $this->user = User::factory()->create(['email' => 'admin@virtualworkplace.test']);
        $this->user2 = User::factory()->create(['email' => 'employee@virtualworkplace.test']);

        $this->org = $action->execute(['name' => 'Acme Corporation'], $this->user);
        $this->membership = $this->org->members()->where('user_id', $this->user->id)->first();

        // Add user2 as employee
        $employeeRole = Role::where('slug', 'employee')->whereNull('organization_id')->first();
        OrganizationMember::create([
            'organization_id' => $this->org->id,
            'user_id' => $this->user2->id,
            'role_id' => $employeeRole->id,
            'status' => 'active',
            'cost_rate' => 45.00,
            'billing_rate' => 120.00,
            'weekly_capacity_hours' => 37.50,
            'joined_at' => now(),
        ]);
    }

    public function test_organization_member_rates_and_capacity(): void
    {
        $member = OrganizationMember::where('user_id', $this->user2->id)->first();

        $this->assertEquals(45.00, (float) $member->cost_rate);
        $this->assertEquals(120.00, (float) $member->billing_rate);
        $this->assertEquals(37.50, (float) $member->weekly_capacity_hours);
    }

    public function test_project_and_task_hierarchy(): void
    {
        // 1. Create Project
        $project = Project::create([
            'organization_id' => $this->org->id,
            'name' => 'Alpha SaaS Redesign',
            'code' => 'ALPHA-01',
            'description' => 'Complete virtual office overhaul',
            'owner_id' => $this->user->id,
            'manager_id' => $this->user->id,
            'status' => 'active',
            'priority' => 'high',
            'start_date' => now()->toDateString(),
            'due_date' => now()->addMonths(2)->toDateString(),
            'budget_amount' => 50000.00,
            'planned_hours' => 400.00,
        ]);

        $this->assertNotEmpty($project->id);
        $this->assertEquals('Alpha SaaS Redesign', $project->name);

        // 2. Add Project Member
        ProjectMember::create([
            'organization_id' => $this->org->id,
            'project_id' => $project->id,
            'user_id' => $this->user2->id,
            'project_role' => 'contributor',
            'cost_rate' => 45.00,
            'billing_rate' => 120.00,
        ]);

        $this->assertEquals(1, $project->members()->count());

        // 3. Create optional Phase & Milestone
        $phase = ProjectPhase::create([
            'organization_id' => $this->org->id,
            'project_id' => $project->id,
            'name' => 'Phase 1: Discovery',
            'order' => 1,
        ]);

        $milestone = ProjectMilestone::create([
            'organization_id' => $this->org->id,
            'project_id' => $project->id,
            'name' => 'Architecture Sign-off',
            'due_date' => now()->addWeeks(2)->toDateString(),
        ]);

        // 4. Create Parent Task
        $parentTask = Task::create([
            'organization_id' => $this->org->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'milestone_id' => $milestone->id,
            'title' => 'Design System Architecture',
            'task_type' => 'feature',
            'status' => Task::STATUS_IN_PROGRESS,
            'priority' => Task::PRIORITY_HIGH,
            'assignee_id' => $this->user2->id,
            'reporter_id' => $this->user->id,
            'estimated_hours' => 20.00,
            'is_billable' => true,
        ]);

        // 5. Create Subtask under Parent Task
        $subtask = Task::create([
            'organization_id' => $this->org->id,
            'project_id' => $project->id,
            'parent_task_id' => $parentTask->id,
            'title' => 'Build Color Palette Tokens',
            'task_type' => 'task',
            'status' => Task::STATUS_READY,
            'priority' => Task::PRIORITY_MEDIUM,
            'assignee_id' => $this->user2->id,
            'reporter_id' => $this->user->id,
            'estimated_hours' => 4.00,
            'is_billable' => true,
        ]);

        $this->assertEquals(1, $parentTask->subtasks()->count());
        $this->assertEquals($parentTask->id, $subtask->parentTask->id);

        // 6. Checklist item
        $checklistItem = TaskChecklistItem::create([
            'organization_id' => $this->org->id,
            'task_id' => $subtask->id,
            'title' => 'Dark mode CSS variables',
            'is_completed' => false,
            'order' => 1,
        ]);
        $this->assertEquals(1, $subtask->checklistItems()->count());

        // 7. Comment & Attachment
        $comment = TaskComment::create([
            'organization_id' => $this->org->id,
            'task_id' => $subtask->id,
            'user_id' => $this->user->id,
            'body' => 'Tokens look great!',
        ]);
        $this->assertEquals(1, $subtask->comments()->count());

        $attachment = TaskAttachment::create([
            'organization_id' => $this->org->id,
            'task_id' => $subtask->id,
            'user_id' => $this->user->id,
            'file_name' => 'tokens.json',
            'file_path' => 'projects/attachments/tokens.json',
            'file_size' => 2048,
            'mime_type' => 'application/json',
        ]);
        $this->assertEquals(1, $subtask->attachments()->count());
    }

    public function test_task_dependencies(): void
    {
        $project = Project::create([
            'organization_id' => $this->org->id,
            'name' => 'Dependency Test Project',
            'owner_id' => $this->user->id,
            'status' => 'active',
        ]);

        $taskA = Task::create([
            'organization_id' => $this->org->id,
            'project_id' => $project->id,
            'title' => 'Backend API Development',
            'reporter_id' => $this->user->id,
            'status' => Task::STATUS_IN_PROGRESS,
        ]);

        $taskB = Task::create([
            'organization_id' => $this->org->id,
            'project_id' => $project->id,
            'title' => 'Frontend Integration',
            'reporter_id' => $this->user->id,
            'status' => Task::STATUS_BACKLOG,
        ]);

        // Task B depends on Task A (Finish-to-Start)
        TaskDependency::create([
            'organization_id' => $this->org->id,
            'task_id' => $taskB->id,
            'depends_on_task_id' => $taskA->id,
            'dependency_type' => TaskDependency::TYPE_FINISH_TO_START,
        ]);

        $this->assertTrue($taskB->isBlocked());

        // Mark Task A as done
        $taskA->update(['status' => Task::STATUS_DONE]);
        $this->assertFalse($taskB->isBlocked());
    }

    public function test_time_entry_and_financial_calculations(): void
    {
        $project = Project::create([
            'organization_id' => $this->org->id,
            'name' => 'Billing Calculation Project',
            'owner_id' => $this->user->id,
            'status' => 'active',
        ]);

        $task = Task::create([
            'organization_id' => $this->org->id,
            'project_id' => $project->id,
            'title' => 'Core Engine Work',
            'reporter_id' => $this->user->id,
            'status' => Task::STATUS_IN_PROGRESS,
        ]);

        // Log 2 hours (7200 seconds) at cost_rate 50.00, billing_rate 150.00
        $entry = TimeEntry::create([
            'organization_id' => $this->org->id,
            'user_id' => $this->user2->id,
            'project_id' => $project->id,
            'task_id' => $task->id,
            'started_at' => now()->subHours(2),
            'ended_at' => now(),
            'duration_seconds' => 7200,
            'description' => 'Engine development',
            'is_billable' => true,
            'cost_rate' => 50.00,
            'billing_rate' => 150.00,
            'entry_type' => TimeEntry::TYPE_MANUAL,
            'status' => TimeEntry::STATUS_DRAFT,
        ]);

        $this->assertEquals(2.00, $entry->hours());
        $this->assertEquals(100.00, $entry->laborCost());
        $this->assertEquals(300.00, $entry->billableRevenue());

        // Project aggregated actual and billable hours
        $this->assertEquals(2.00, $project->actualHours());
        $this->assertEquals(2.00, $project->billableHours());
    }

    public function test_active_timer_database_unique_constraint(): void
    {
        $project = Project::create([
            'organization_id' => $this->org->id,
            'name' => 'Timer Concurrency Project',
            'owner_id' => $this->user->id,
            'status' => 'active',
        ]);

        // 1. Create first active timer for user
        ActiveTimer::create([
            'organization_id' => $this->org->id,
            'user_id' => $this->user->id,
            'project_id' => $project->id,
            'started_at' => now(),
        ]);

        $this->assertDatabaseHas('active_timers', [
            'user_id' => $this->user->id,
            'project_id' => $project->id,
        ]);

        // 2. Attempting to create a second active timer for the same user MUST violate database unique constraint
        $this->expectException(QueryException::class);

        ActiveTimer::create([
            'organization_id' => $this->org->id,
            'user_id' => $this->user->id,
            'project_id' => $project->id,
            'started_at' => now(),
        ]);
    }

    public function test_timesheet_creation_and_locking(): void
    {
        $timesheet = Timesheet::create([
            'organization_id' => $this->org->id,
            'user_id' => $this->user2->id,
            'period_start' => now()->startOfWeek()->toDateString(),
            'period_end' => now()->endOfWeek()->toDateString(),
            'status' => Timesheet::STATUS_DRAFT,
            'total_hours' => 38.50,
            'billable_hours' => 35.00,
        ]);

        $this->assertTrue($timesheet->isDraft());
        $this->assertFalse($timesheet->isLocked());

        $timesheet->update(['status' => Timesheet::STATUS_SUBMITTED]);
        $this->assertTrue($timesheet->isLocked());

        $timesheet->update(['status' => Timesheet::STATUS_APPROVED]);
        $this->assertTrue($timesheet->isApproved());
        $this->assertTrue($timesheet->isLocked());
    }

    public function test_project_management_permissions_seeded(): void
    {
        $companyAdmin = Role::where('slug', 'company_admin')->whereNull('organization_id')->first();
        $manager = Role::where('slug', 'manager')->whereNull('organization_id')->first();
        $employee = Role::where('slug', 'employee')->whereNull('organization_id')->first();

        // Company Admin has all project permissions
        $this->assertTrue($companyAdmin->hasPermission('projects.create'));
        $this->assertTrue($companyAdmin->hasPermission('tasks.create'));
        $this->assertTrue($companyAdmin->hasPermission('time.create'));
        $this->assertTrue($companyAdmin->hasPermission('timesheets.approve'));
        $this->assertTrue($companyAdmin->hasPermission('reports.financials'));

        // Manager has timesheet approval and projects view/create
        $this->assertTrue($manager->hasPermission('projects.create'));
        $this->assertTrue($manager->hasPermission('timesheets.approve'));
        $this->assertFalse($manager->hasPermission('reports.financials'));

        // Employee has personal task, time and timesheet submission
        $this->assertTrue($employee->hasPermission('projects.view'));
        $this->assertTrue($employee->hasPermission('tasks.create'));
        $this->assertTrue($employee->hasPermission('time.create'));
        $this->assertTrue($employee->hasPermission('timesheets.submit'));
        $this->assertFalse($employee->hasPermission('timesheets.approve'));
    }
}
