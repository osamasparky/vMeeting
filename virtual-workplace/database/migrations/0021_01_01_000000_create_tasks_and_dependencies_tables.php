<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignUuid('phase_id')->nullable()->constrained('project_phases')->nullOnDelete();
            $table->foreignUuid('milestone_id')->nullable()->constrained('project_milestones')->nullOnDelete();
            $table->foreignUuid('parent_task_id')->nullable()->constrained('tasks')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('task_number')->default(1);
            $table->string('task_type', 32)->default('task'); // task, bug, feature, improvement
            $table->string('status', 32)->default('backlog'); // backlog, ready, in_progress, review, qa, done
            $table->string('priority', 32)->default('medium'); // low, medium, high, urgent
            $table->foreignUuid('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('estimated_hours', 8, 2)->default(0.00);
            $table->boolean('is_billable')->default(true);
            $table->integer('order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'project_id', 'status']);
            $table->index(['assignee_id', 'status', 'due_date']);
            $table->index('parent_task_id');
        });

        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignUuid('depends_on_task_id')->constrained('tasks')->cascadeOnDelete();
            $table->string('dependency_type', 32)->default('finish_to_start'); // finish_to_start, start_to_start, finish_to_finish
            $table->timestamps();

            $table->unique(['task_id', 'depends_on_task_id']);
            $table->index('organization_id');
        });

        Schema::create('task_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->string('title');
            $table->boolean('is_completed')->default(false);
            $table->foreignUuid('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['task_id', 'order']);
            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_checklist_items');
        Schema::dropIfExists('task_dependencies');
        Schema::dropIfExists('tasks');
    }
};
