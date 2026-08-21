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
        Schema::table('meetings', function (Blueprint $table) {
            $table->foreignUuid('project_id')->nullable()->after('room_id')->constrained('projects')->nullOnDelete();
            $table->timestamp('scheduled_at')->nullable()->after('status');
            $table->integer('duration_minutes')->default(30)->after('scheduled_at');
            $table->text('description')->nullable()->after('title');
            $table->string('scope', 32)->default('general')->after('type'); // 'project', 'general'
            $table->boolean('reminders_sent')->default(false)->after('settings');

            $table->index(['organization_id', 'scheduled_at']);
            $table->index(['project_id']);
        });

        Schema::table('organization_settings', function (Blueprint $table) {
            $table->json('smtp_settings')->nullable()->after('policies');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_settings', function (Blueprint $table) {
            $table->dropColumn('smtp_settings');
        });

        Schema::table('meetings', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn([
                'project_id',
                'scheduled_at',
                'duration_minutes',
                'description',
                'scope',
                'reminders_sent',
            ]);
        });
    }
};
