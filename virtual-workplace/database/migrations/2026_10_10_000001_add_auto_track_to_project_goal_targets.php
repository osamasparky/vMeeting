<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Auto-tracked targets (task/milestone counts, logged hours) used to be
     * recognised only by target_value <= 0 — which the first recalculation
     * overwrote, turning them manual from then on. Persist the intent instead.
     */
    public function up(): void
    {
        Schema::table('project_goal_targets', function (Blueprint $table) {
            $table->boolean('auto_track')->default(false)->after('target_type');
        });

        // 'hours' targets were always meant to read logged time.
        DB::table('project_goal_targets')->where('target_type', 'hours')->update(['auto_track' => true]);
    }

    public function down(): void
    {
        Schema::table('project_goal_targets', function (Blueprint $table) {
            $table->dropColumn('auto_track');
        });
    }
};
