<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-project workflow states. Each state belongs to one of the fixed
     * status groups (backlog, ready, in_progress, review, qa, done); the task's
     * `status` keeps holding the group, so approval, goals and reports are
     * unaffected — states only subdivide the groups.
     */
    public function up(): void
    {
        Schema::create('project_states', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name', 60);
            $table->string('group', 20);
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->index(['project_id', 'position']);
            $table->index('organization_id');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('state_id')->nullable()->after('status')->constrained('project_states')->nullOnDelete();
        });

        // Give every existing project the standard states and place its tasks.
        $defaults = [
            ['Backlog', 'backlog'], ['Ready', 'ready'], ['In Progress', 'in_progress'],
            ['Review', 'review'], ['Done', 'done'],
        ];
        $now = now();
        foreach (DB::table('projects')->select('id', 'organization_id')->get() as $project) {
            $ids = [];
            foreach ($defaults as $i => [$name, $group]) {
                $ids[$group] = DB::table('project_states')->insertGetId([
                    'organization_id' => $project->organization_id,
                    'project_id' => $project->id,
                    'name' => $name,
                    'group' => $group,
                    'position' => $i,
                    'is_default' => $group === 'backlog',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $ids['qa'] = $ids['review'];
            foreach ($ids as $group => $stateId) {
                DB::table('tasks')->where('project_id', $project->id)->where('status', $group)->update(['state_id' => $stateId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('state_id');
        });
        Schema::dropIfExists('project_states');
    }
};
