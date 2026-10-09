<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A named set of task filters + layout. Private to its creator unless
     * shared with everyone on the project.
     */
    public function up(): void
    {
        Schema::create('saved_views', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('project_id')->nullable()->constrained('projects')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('layout', 20)->default('kanban'); // kanban, tasks, calendar
            $table->json('filters')->nullable();
            $table->boolean('is_shared')->default(false);
            $table->timestamps();
            $table->index(['project_id', 'user_id']);
            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_views');
    }
};
