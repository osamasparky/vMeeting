<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Leave requests. A member asks for leave over a date range; the company
 * owner approves or rejects it. `days` counts working days only (the home
 * office's working days, minus official holidays). Each member has an annual
 * leave entitlement per calendar year.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20);            // annual, sick, unpaid, other
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedSmallInteger('days');  // working days in the range
            $table->text('reason')->nullable();
            $table->string('status', 20)->default('pending'); // pending, approved, rejected, cancelled
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'user_id', 'start_date']);
        });

        Schema::table('organization_members', function (Blueprint $table) {
            $table->unsignedSmallInteger('annual_leave_days')->default(21)->after('home_floor_id');
        });
    }

    public function down(): void
    {
        Schema::table('organization_members', function (Blueprint $table) {
            $table->dropColumn('annual_leave_days');
        });

        Schema::dropIfExists('leave_requests');
    }
};
