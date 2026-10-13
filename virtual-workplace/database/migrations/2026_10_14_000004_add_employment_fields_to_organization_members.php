<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The employment record kept on a membership: when the member was hired, on
 * what basis, and who they report to. (joined_at is when they joined the
 * workspace, not the company.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_members', function (Blueprint $table) {
            $table->date('hire_date')->nullable()->after('annual_leave_days');
            $table->string('employment_type', 20)->nullable()->after('hire_date'); // full_time, part_time, contract, intern
            $table->uuid('manager_user_id')->nullable()->after('employment_type');
        });
    }

    public function down(): void
    {
        Schema::table('organization_members', function (Blueprint $table) {
            $table->dropColumn(['hire_date', 'employment_type', 'manager_user_id']);
        });
    }
};
