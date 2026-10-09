<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_sprints', function (Blueprint $table) {
            $table->string('goal', 255)->nullable()->after('name');
            $table->timestamp('started_at')->nullable()->after('status');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            // Frozen burndown at completion — carried-over tasks leave the
            // sprint, so it can't be recomputed afterwards.
            $table->json('burndown_snapshot')->nullable()->after('retrospective_notes');
        });
    }

    public function down(): void
    {
        Schema::table('project_sprints', function (Blueprint $table) {
            $table->dropColumn(['goal', 'started_at', 'completed_at', 'burndown_snapshot']);
        });
    }
};
