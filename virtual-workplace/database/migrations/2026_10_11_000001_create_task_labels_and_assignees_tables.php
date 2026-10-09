<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Per-project labels. `tone` names a design-system tone (palm, gold,
        // terracotta, sand, stone) rather than a raw colour, so labels follow
        // the light/dark theme like every other surface.
        Schema::create('project_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name', 50);
            $table->string('tone', 20)->default('stone');
            $table->timestamps();
            $table->unique(['project_id', 'name']);
            $table->index('organization_id');
        });

        Schema::create('task_labels', function (Blueprint $table) {
            $table->foreignUuid('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('label_id')->constrained('project_labels')->cascadeOnDelete();
            $table->primary(['task_id', 'label_id']);
            $table->index('label_id');
        });

        // Every assignee of a task. tasks.assignee_id stays as the primary
        // (first) assignee so existing reports, policies and notifications
        // keep working unchanged.
        Schema::create('task_assignees', function (Blueprint $table) {
            $table->foreignUuid('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['task_id', 'user_id']);
            $table->index('user_id');
        });

        $now = now();
        DB::table('tasks')->whereNotNull('assignee_id')->select('id', 'assignee_id')->orderBy('id')
            ->chunk(500, function ($tasks) use ($now) {
                DB::table('task_assignees')->insertOrIgnore($tasks->map(fn ($t) => [
                    'task_id' => $t->id,
                    'user_id' => $t->assignee_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all());
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assignees');
        Schema::dropIfExists('task_labels');
        Schema::dropIfExists('project_labels');
    }
};
