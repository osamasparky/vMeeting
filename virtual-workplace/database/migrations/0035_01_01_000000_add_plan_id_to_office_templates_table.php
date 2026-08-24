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
        Schema::table('office_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('office_templates', 'plan_id')) {
                $table->foreignId('plan_id')->nullable()->after('slug')->constrained('plans')->nullOnDelete();
            }
            if (!Schema::hasColumn('office_templates', 'plan_slug')) {
                $table->string('plan_slug')->nullable()->after('plan_id')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('office_templates', function (Blueprint $table) {
            if (Schema::hasColumn('office_templates', 'plan_id')) {
                $table->dropForeign(['plan_id']);
                $table->dropColumn('plan_id');
            }
            if (Schema::hasColumn('office_templates', 'plan_slug')) {
                $table->dropColumn('plan_slug');
            }
        });
    }
};
