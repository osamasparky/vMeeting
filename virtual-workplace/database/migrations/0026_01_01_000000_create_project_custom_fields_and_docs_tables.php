<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Custom Field Definitions (ClickUp Custom Fields)
        Schema::create('custom_field_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->string('field_type', 30)->default('text'); // text, number, dropdown, currency, date, checkbox, rating, url
            $table->json('options')->nullable(); // For dropdown options, currency symbol, rating max, etc.
            $table->boolean('is_required')->default(false);
            $table->string('color', 20)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['organization_id', 'project_id']);
        });

        // 2. Task Custom Field Values
        Schema::create('task_custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('custom_field_definition_id')->constrained('custom_field_definitions')->cascadeOnDelete();
            $table->text('value_text')->nullable();
            $table->decimal('value_number', 14, 4)->nullable();
            $table->dateTime('value_date')->nullable();
            $table->boolean('value_boolean')->nullable();
            $table->json('value_json')->nullable();
            $table->timestamps();

            $table->unique(['task_id', 'custom_field_definition_id'], 'task_field_unique');
        });

        // 3. Project Documents & Knowledge Wiki (ClickUp Docs)
        Schema::create('project_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_document_id')->nullable()->constrained('project_documents')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->longText('content')->nullable();
            $table->string('icon', 30)->default('📄');
            $table->boolean('is_pinned')->default(false);
            $table->integer('version')->default(1);
            $table->timestamps();

            $table->index(['organization_id', 'project_id']);
        });

        // 4. Project Goals & Target Metrics (ClickUp Goals)
        Schema::create('project_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color', 20)->default('#245C3A');
            $table->dateTime('due_date')->nullable();
            $table->string('status', 30)->default('in_progress'); // in_progress, completed, behind, at_risk
            $table->decimal('progress_percentage', 5, 2)->default(0.00);
            $table->timestamps();

            $table->index(['organization_id', 'project_id']);
        });

        Schema::create('project_goal_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained('project_goals')->cascadeOnDelete();
            $table->string('title');
            $table->string('target_type', 30)->default('number'); // number, currency, tasks, percentage, boolean
            $table->decimal('start_value', 14, 2)->default(0.00);
            $table->decimal('target_value', 14, 2)->default(100.00);
            $table->decimal('current_value', 14, 2)->default(0.00);
            $table->string('unit', 30)->nullable(); // e.g. USD, Tasks, %, Points
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });

        // 5. Project Sprints (ClickUp Sprints)
        Schema::create('project_sprints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('status', 30)->default('planned'); // planned, active, completed, closed
            $table->integer('planned_points')->default(0);
            $table->integer('completed_points')->default(0);
            $table->text('retrospective_notes')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'project_id']);
        });

        // Add sprint_id and story_points to tasks table
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('sprint_id')->nullable()->after('project_id')->constrained('project_sprints')->nullOnDelete();
            $table->integer('story_points')->nullable()->after('estimated_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['sprint_id']);
            $table->dropColumn(['sprint_id', 'story_points']);
        });

        Schema::dropIfExists('project_sprints');
        Schema::dropIfExists('project_goal_targets');
        Schema::dropIfExists('project_goals');
        Schema::dropIfExists('project_documents');
        Schema::dropIfExists('task_custom_field_values');
        Schema::dropIfExists('custom_field_definitions');
    }
};
