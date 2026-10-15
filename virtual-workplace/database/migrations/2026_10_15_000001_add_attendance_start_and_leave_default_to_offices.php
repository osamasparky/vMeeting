<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Two office-level HR settings:
 *  - attendance_starts_on: days before it are not counted as absence (the
 *    office went live on that day). Null means "count from when each member
 *    joined".
 *  - annual_leave_days: the yearly leave balance every member of the office
 *    gets. A member's own annual_leave_days now overrides it only when set,
 *    so the column becomes nullable; members still on the old untouched
 *    default (21) inherit the office value, which also starts at 21, so no
 *    balance changes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('floors', function (Blueprint $table) {
            $table->date('attendance_starts_on')->nullable()->after('late_grace_minutes');
            $table->unsignedSmallInteger('annual_leave_days')->default(21)->after('attendance_starts_on');
        });

        Schema::table('organization_members', function (Blueprint $table) {
            $table->unsignedSmallInteger('annual_leave_days')->nullable()->default(null)->change();
        });

        DB::table('organization_members')->where('annual_leave_days', 21)->update(['annual_leave_days' => null]);
    }

    public function down(): void
    {
        DB::table('organization_members')->whereNull('annual_leave_days')->update(['annual_leave_days' => 21]);

        Schema::table('organization_members', function (Blueprint $table) {
            $table->unsignedSmallInteger('annual_leave_days')->default(21)->nullable(false)->change();
        });

        Schema::table('floors', function (Blueprint $table) {
            $table->dropColumn(['attendance_starts_on', 'annual_leave_days']);
        });
    }
};
