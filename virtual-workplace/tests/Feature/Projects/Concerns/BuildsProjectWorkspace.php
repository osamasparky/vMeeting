<?php

namespace Tests\Feature\Projects\Concerns;

use App\Domains\Administration\Models\Role;
use App\Domains\Identity\Models\User;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Models\Task;
use App\Domains\Tenancy\Actions\CreateOrganizationAction;
use App\Domains\Tenancy\Models\Organization;
use App\Domains\Tenancy\Models\OrganizationMember;
use Database\Seeders\PlansSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;

/**
 * Two tenants with an owner, a manager and two employees in the first, one
 * project each, and a task assigned to the first employee.
 */
trait BuildsProjectWorkspace
{
    protected User $owner;

    protected User $manager;

    protected User $employee;

    protected User $otherEmployee;

    protected User $outsider;

    protected Organization $org;

    protected Organization $otherOrg;

    protected Project $project;

    protected Project $foreignProject;

    protected Task $task;

    protected int $taskSeq = 0;

    protected function buildProjectWorkspace(): void
    {
        $this->seed(PlansSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        $create = app(CreateOrganizationAction::class);

        $this->owner = User::factory()->create();
        $this->org = $create->execute(['name' => 'Owner Co'], $this->owner);

        $this->manager = $this->addMember($this->org, 'manager');
        $this->employee = $this->addMember($this->org, 'employee');
        $this->otherEmployee = $this->addMember($this->org, 'employee');

        $this->outsider = User::factory()->create();
        $this->otherOrg = $create->execute(['name' => 'Other Co'], $this->outsider);

        $this->project = Project::create([
            'organization_id' => $this->org->id,
            'name' => 'Main Project',
            'code' => 'MAIN',
            'owner_id' => $this->owner->id,
            'manager_id' => $this->manager->id,
            'status' => 'active',
        ]);

        $this->foreignProject = Project::create([
            'organization_id' => $this->otherOrg->id,
            'name' => 'Foreign Project',
            'owner_id' => $this->outsider->id,
            'status' => 'active',
        ]);

        $this->task = $this->makeTask(['assignee_id' => $this->employee->id, 'reporter_id' => $this->employee->id]);
    }

    protected function addMember(Organization $org, string $roleSlug): User
    {
        $user = User::factory()->create();
        OrganizationMember::create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'role_id' => Role::where('slug', $roleSlug)->whereNull('organization_id')->value('id'),
            'status' => 'active',
            'joined_at' => now(),
        ]);

        return $user;
    }

    protected function makeTask(array $attrs = []): Task
    {
        $this->taskSeq++;

        return Task::create(array_merge([
            'organization_id' => $this->org->id,
            'project_id' => $this->project->id,
            'title' => "Task {$this->taskSeq}",
            'task_number' => $this->taskSeq,
            'reporter_id' => $this->owner->id,
            'status' => 'in_progress',
        ], $attrs));
    }

    protected function api(string $path): string
    {
        return "/api/v1/organizations/{$this->org->id}{$path}";
    }
}
