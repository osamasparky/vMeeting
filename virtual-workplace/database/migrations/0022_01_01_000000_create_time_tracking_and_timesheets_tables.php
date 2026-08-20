<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timesheets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->string('status', 32)->default('draft'); // draft, submitted, approved, rejected
            $table->decimal('total_hours', 8, 2)->default(0.00);
            $table->decimal('billable_hours', 8, 2)->default(0.00);
            $table->timestamp('submitted_at')->nullable();
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'user_id', 'period_start']);
            $table->index(['organization_id', 'status']);
        });

        Schema::create('time_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignUuid('task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->foreignUuid('timesheet_id')->nullable()->constrained('timesheets')->nullOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_billable')->default(true);
            $table->decimal('cost_rate', 8, 2)->default(0.00);
            $table->decimal('billing_rate', 8, 2)->default(0.00);
            $table->string('entry_type', 32)->default('timer'); // timer, manual
            $table->string('status', 32)->default('draft'); // draft, submitted, approved, rejected
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'user_id', 'started_at']);
            $table->index(['project_id', 'task_id']);
            $table->index('timesheet_id');
        });

        Schema::create('active_timers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignUuid('task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->timestamp('started_at');
            $table->text('description')->nullable();
            $table->boolean('is_billable')->default(true);
            $table->timestamps();

            // Strict database level uniqueness: exactly 1 active timer per user
            $table->unique('user_id');
            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('active_timers');
        Schema::dropIfExists('time_entries');
        Schema::dropIfExists('timesheets');
    }
};
