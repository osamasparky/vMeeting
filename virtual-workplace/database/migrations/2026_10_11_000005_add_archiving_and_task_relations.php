<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // "Delete" now archives: rows stay restorable until permanently removed.
        Schema::table('tasks', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->index();
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->index();
        });

        // Non-scheduling links between tasks (dependencies stay in task_dependencies).
        Schema::create('task_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignUuid('related_task_id')->constrained('tasks')->cascadeOnDelete();
            $table->string('type', 20); // relates_to, duplicate_of
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['task_id', 'related_task_id', 'type']);
            $table->index('related_task_id');
            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_relations');
        Schema::table('projects', fn (Blueprint $table) => $table->dropColumn('archived_at'));
        Schema::table('tasks', fn (Blueprint $table) => $table->dropColumn('archived_at'));
    }
};
