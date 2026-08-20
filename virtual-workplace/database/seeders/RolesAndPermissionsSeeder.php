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
            ['key' => 'organizations.manage', 'description' => 'Manage organization settings', 'group' => 'Organization'],
            ['key' => 'organizations.view', 'description' => 'View organization details', 'group' => 'Organization'],

            // Members
            ['key' => 'members.invite', 'description' => 'Invite new members', 'group' => 'Members'],
            ['key' => 'members.manage', 'description' => 'Manage members (roles, suspend)', 'group' => 'Members'],
            ['key' => 'members.view', 'description' => 'View member list', 'group' => 'Members'],

            // Departments & Teams
            ['key' => 'departments.manage', 'description' => 'Create/edit departments', 'group' => 'People'],
            ['key' => 'teams.manage', 'description' => 'Create/edit teams', 'group' => 'People'],

            // Rooms
            ['key' => 'rooms.manage', 'description' => 'Create/edit/delete rooms', 'group' => 'Workspace'],
            ['key' => 'rooms.access_private', 'description' => 'Access private rooms', 'group' => 'Workspace'],

            // Maps
            ['key' => 'maps.manage', 'description' => 'Edit and publish maps', 'group' => 'Workspace'],
            ['key' => 'maps.view', 'description' => 'View maps', 'group' => 'Workspace'],

            // Analytics
            ['key' => 'analytics.view', 'description' => 'View analytics dashboard', 'group' => 'Analytics'],

            // Audit
            ['key' => 'audit.view', 'description' => 'View audit logs', 'group' => 'Administration'],

            // Billing
            ['key' => 'billing.manage', 'description' => 'Manage billing/subscription', 'group' => 'Billing'],

            // Guests
            ['key' => 'guests.invite', 'description' => 'Invite guests to rooms', 'group' => 'Guests'],

            // Project Management: Projects
            ['key' => 'projects.view', 'description' => 'View projects', 'group' => 'Projects'],
            ['key' => 'projects.create', 'description' => 'Create projects', 'group' => 'Projects'],
            ['key' => 'projects.edit', 'description' => 'Edit projects', 'group' => 'Projects'],
            ['key' => 'projects.delete', 'description' => 'Delete projects', 'group' => 'Projects'],

            // Project Management: Tasks
            ['key' => 'tasks.view', 'description' => 'View tasks', 'group' => 'Tasks'],
            ['key' => 'tasks.create', 'description' => 'Create tasks and subtasks', 'group' => 'Tasks'],
            ['key' => 'tasks.edit', 'description' => 'Edit tasks and update status', 'group' => 'Tasks'],
            ['key' => 'tasks.assign', 'description' => 'Assign tasks to team members', 'group' => 'Tasks'],
            ['key' => 'tasks.delete', 'description' => 'Delete tasks', 'group' => 'Tasks'],

            // Project Management: Time Tracking & Timers
            ['key' => 'time.view', 'description' => 'View time entries', 'group' => 'Time'],
            ['key' => 'time.create', 'description' => 'Start timer / log manual time', 'group' => 'Time'],
            ['key' => 'time.edit', 'description' => 'Edit own time entries', 'group' => 'Time'],
            ['key' => 'time.delete', 'description' => 'Delete time entries', 'group' => 'Time'],

            // Project Management: Timesheets
            ['key' => 'timesheets.view', 'description' => 'View timesheets', 'group' => 'Timesheets'],
            ['key' => 'timesheets.submit', 'description' => 'Submit timesheets for approval', 'group' => 'Timesheets'],
            ['key' => 'timesheets.approve', 'description' => 'Approve or reject timesheets', 'group' => 'Timesheets'],

            // Project Management: Reports & Financials
            ['key' => 'reports.view', 'description' => 'View project & workload reports', 'group' => 'Reports'],
            ['key' => 'reports.financials', 'description' => 'View project financial metrics & rates', 'group' => 'Reports'],
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
                    'projects.view', 'projects.create', 'projects.edit', 'projects.delete',
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
                    'projects.view', 'projects.create', 'projects.edit',
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
            'guest' => [
                'name' => 'Guest',
                'permissions' => [],
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
