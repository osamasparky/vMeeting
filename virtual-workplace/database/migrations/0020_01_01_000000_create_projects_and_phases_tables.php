<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 32)->nullable();
            $table->text('description')->nullable();
            $table->foreignUuid('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('status', 32)->default('active'); // planning, active, on_hold, completed, cancelled
            $table->string('priority', 32)->default('medium'); // low, medium, high, urgent
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('budget_amount', 12, 2)->nullable();
            $table->decimal('planned_hours', 8, 2)->nullable();
            $table->string('color', 16)->default('#3b82f6');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'created_at']);
        });

        Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('project_role', 32)->default('contributor'); // manager, lead, contributor, viewer
            $table->decimal('cost_rate', 8, 2)->nullable();
            $table->decimal('billing_rate', 8, 2)->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
            $table->index('organization_id');
        });

        Schema::create('project_phases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->integer('order')->default(0);
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status', 32)->default('active');
            $table->timestamps();

            $table->index(['project_id', 'order']);
            $table->index('organization_id');
        });

        Schema::create('project_milestones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->date('due_date')->nullable();
            $table->string('status', 32)->default('pending'); // pending, completed
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'due_date']);
            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_milestones');
        Schema::dropIfExists('project_phases');
        Schema::dropIfExists('project_members');
        Schema::dropIfExists('projects');
    }
};
