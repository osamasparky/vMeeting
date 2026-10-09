<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Approving timesheets is the company owner's power alone. Strip
     * timesheets.approve from every role except company_admin/super_admin
     * (the Manager seed role used to carry it).
     */
    public function up(): void
    {
        $permissionId = DB::table('permissions')->where('key', 'timesheets.approve')->value('id');
        if (! $permissionId) {
            return;
        }

        $nonOwnerRoleIds = DB::table('roles')
            ->whereNotIn('slug', ['company_admin', 'super_admin'])
            ->pluck('id');

        DB::table('role_permissions')
            ->where('permission_id', $permissionId)
            ->whereIn('role_id', $nonOwnerRoleIds)
            ->delete();
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('key', 'timesheets.approve')->value('id');
        $managerRoleIds = DB::table('roles')->where('slug', 'manager')->pluck('id');

        foreach ($managerRoleIds as $roleId) {
            DB::table('role_permissions')->insertOrIgnore(['role_id' => $roleId, 'permission_id' => $permissionId]);
        }
    }
};
