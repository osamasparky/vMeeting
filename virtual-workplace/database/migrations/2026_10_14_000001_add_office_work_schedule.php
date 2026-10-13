<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Office working hours: which days are working days, when the day starts and
 * ends, and how late an arrival may be before it counts as late. Each member
 * belongs to a home office whose schedule (and timezone) applies to them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('floors', function (Blueprint $table) {
            // Carbon day-of-week numbers: 0 = Sunday … 6 = Saturday. Null means the default (Sun–Thu).
            $table->json('work_days')->nullable()->after('timezone');
            $table->string('work_start', 5)->default('09:00')->after('work_days');
            $table->string('work_end', 5)->default('17:00')->after('work_start');
            $table->unsignedSmallInteger('late_grace_minutes')->default(15)->after('work_end');
        });

        Schema::table('organization_members', function (Blueprint $table) {
            $table->uuid('home_floor_id')->nullable()->after('role_id');
        });
    }

    public function down(): void
    {
        Schema::table('organization_members', function (Blueprint $table) {
            $table->dropColumn('home_floor_id');
        });

        Schema::table('floors', function (Blueprint $table) {
            $table->dropColumn(['work_days', 'work_start', 'work_end', 'late_grace_minutes']);
        });
    }
};
