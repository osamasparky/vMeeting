<?php

namespace Database\Seeders;

use App\Domains\Administration\Models\Permission;
use App\Domains\Administration\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // ── Permissions ──
        $permissions = [
            // Organizations
            ['key' => 'organizations.manage', 'description' => 'Manage organization settings, logo, timezone, and SMTP mail configuration', 'group' => 'Organization'],
            ['key' => 'organizations.view', 'description' => 'View organization profile and workspace details', 'group' => 'Organization'],

            // Members
            ['key' => 'members.invite', 'description' => 'Invite new team members via email', 'group' => 'Members'],
            ['key' => 'members.manage', 'description' => 'Manage member roles, job titles, departments, passwords, and suspension', 'group' => 'Members'],
            ['key' => 'members.view', 'description' => 'View member roster and team directory', 'group' => 'Members'],

            // Departments & Teams
            ['key' => 'departments.manage', 'description' => 'Create, edit, and organize departments', 'group' => 'People'],
            ['key' => 'teams.manage', 'description' => 'Create, edit, and organize teams', 'group' => 'People'],

            // Rooms & Virtual Office
            ['key' => 'rooms.manage', 'description' => 'Create, edit, configure room doors, and delete rooms', 'group' => 'Workspace'],
            ['key' => 'rooms.access_private', 'description' => 'Access locked and private office rooms', 'group' => 'Workspace'],

            // Maps & Visual Editor
            ['key' => 'maps.manage', 'description' => 'Access visual floor map designer, place furniture, and publish map versions', 'group' => 'Workspace'],
            ['key' => 'maps.view', 'description' => 'View interactive office floor and navigate avatars', 'group' => 'Workspace'],

            // Guests & Public Collaboration
            ['key' => 'guests.invite', 'description' => 'Generate guest meeting invitation links with expiration control', 'group' => 'Guests'],

            // Project Management: Projects
            ['key' => 'projects.view', 'description' => 'View project portfolio list and milestones', 'group' => 'Projects'],
            ['key' => 'projects.create', 'description' => 'Create new projects and initiatives', 'group' => 'Projects'],
            ['key' => 'projects.edit', 'description' => 'Edit project details, phases, milestones, docs, sprints, and goals', 'group' => 'Projects'],
            ['key' => 'projects.manage', 'description' => 'Full project governance, manager assignment, and settings', 'group' => 'Projects'],
            ['key' => 'projects.delete', 'description' => 'Delete projects and archive records', 'group' => 'Projects'],

            // Project Management: Tasks
            ['key' => 'tasks.view', 'description' => 'View tasks and Kanban boards', 'group' => 'Tasks'],
            ['key' => 'tasks.create', 'description' => 'Create tasks, subtasks, and checklists', 'group' => 'Tasks'],
            ['key' => 'tasks.edit', 'description' => 'Edit assigned tasks, status, and checklist items', 'group' => 'Tasks'],
            ['key' => 'tasks.assign', 'description' => 'Assign tasks to team members and manage company-wide task orders', 'group' => 'Tasks'],
            ['key' => 'tasks.delete', 'description' => 'Delete tasks and work orders', 'group' => 'Tasks'],

            // Project Management: Time Tracking & Timers
            ['key' => 'time.view', 'description' => 'View logged hours and time entries', 'group' => 'Time'],
            ['key' => 'time.create', 'description' => 'Start live stopwatch timer and log manual time', 'group' => 'Time'],
            ['key' => 'time.edit', 'description' => 'Edit own time entry notes and logged duration', 'group' => 'Time'],
            ['key' => 'time.delete', 'description' => 'Delete own time logs', 'group' => 'Time'],

            // Project Management: Timesheets
            ['key' => 'timesheets.view', 'description' => 'View weekly timesheets', 'group' => 'Timesheets'],
            ['key' => 'timesheets.submit', 'description' => 'Submit weekly timesheets for approval', 'group' => 'Timesheets'],
            ['key' => 'timesheets.approve', 'description' => 'Review, approve, lock, or reject submitted timesheets', 'group' => 'Timesheets'],

            // Analytics, Financials & Reports
            ['key' => 'analytics.view', 'description' => 'View workspace analytics, activity curves, and donut charts', 'group' => 'Analytics'],
            ['key' => 'reports.view', 'description' => 'View project progress reports and workload matrix', 'group' => 'Reports'],
            ['key' => 'reports.financials', 'description' => 'View project labor costs, billable revenues, hourly rates, budget variance, and gross margins', 'group' => 'Reports'],

            // Audit Logs
            ['key' => 'audit.view', 'description' => 'View organization audit log trails and compliance logs', 'group' => 'Administration'],

            // Billing & Subscriptions
            ['key' => 'billing.manage', 'description' => 'Manage subscription plans, seat capacity, payment renewal, and workspace upgrades', 'group' => 'Billing'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['key' => $perm['key']], $perm);
        }

        // ── System Roles ──
        $roles = [
            'super_admin' => [
                'name' => 'Super Admin',
                'permissions' => '*', // All permissions
            ],
            'company_admin' => [
                'name' => 'Company Admin',
                'permissions' => [
                    'organizations.manage', 'organizations.view',
                    'members.invite', 'members.manage', 'members.view',
                    'departments.manage', 'teams.manage',
                    'rooms.manage', 'rooms.access_private',
                    'maps.manage', 'maps.view',
                    'analytics.view', 'audit.view',
                    'billing.manage', 'guests.invite',
                    'projects.view', 'projects.create', 'projects.edit', 'projects.manage', 'projects.delete',
                    'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.assign', 'tasks.delete',
                    'time.view', 'time.create', 'time.edit', 'time.delete',
                    'timesheets.view', 'timesheets.submit', 'timesheets.approve',
                    'reports.view', 'reports.financials',
                ],
            ],
            'manager' => [
                'name' => 'Manager',
                'permissions' => [
                    'organizations.view',
                    'members.invite', 'members.view',
                    'departments.manage', 'teams.manage',
                    'rooms.manage', 'rooms.access_private',
                    'maps.view',
                    'analytics.view',
                    'guests.invite',
                    'projects.view', 'projects.create', 'projects.edit', 'projects.manage',
                    'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.assign', 'tasks.delete',
                    'time.view', 'time.create', 'time.edit', 'time.delete',
                    'timesheets.view', 'timesheets.submit', 'timesheets.approve',
                    'reports.view',
                ],
            ],
            'employee' => [
                'name' => 'Employee',
                'permissions' => [
                    'organizations.view',
                    'members.view',
                    'maps.view',
                    'projects.view',
                    'tasks.view', 'tasks.create', 'tasks.edit',
                    'time.view', 'time.create', 'time.edit',
                    'timesheets.view', 'timesheets.submit',
                ],
            ],
            'contractor' => [
                'name' => 'Contractor',
                'permissions' => [
                    'organizations.view',
                    'maps.view',
                    'projects.view',
                    'tasks.view', 'tasks.edit',
                    'time.view', 'time.create', 'time.edit',
                    'timesheets.view', 'timesheets.submit',
                ],
            ],
            'guest' => [
                'name' => 'Guest',
                'permissions' => [
                    'maps.view',
                ],
            ],
        ];

        $allPermissionIds = Permission::pluck('id', 'key');

        foreach ($roles as $slug => $roleData) {
            $role = Role::updateOrCreate(
                ['slug' => $slug, 'organization_id' => null],
                [
                    'name' => $roleData['name'],
                    'is_system' => true,
                ]
            );

            if ($roleData['permissions'] === '*') {
                $role->permissions()->sync($allPermissionIds->values());
            } else {
                $permIds = $allPermissionIds->only($roleData['permissions'])->values();
                $role->permissions()->sync($permIds);
            }
        }
    }
}
