<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // People following a task: they hear about status changes and comments.
        Schema::create('task_watchers', function (Blueprint $table) {
            $table->foreignUuid('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['task_id', 'user_id']);
            $table->index('user_id');
        });

        Schema::table('tasks', function (Blueprint $table) {
            // The due date a reminder / overdue notice was already sent for,
            // so the hourly scheduler never repeats itself (and a moved due
            // date earns a fresh reminder).
            $table->date('due_reminder_sent_for')->nullable()->after('due_date');
            $table->date('overdue_notice_sent_for')->nullable()->after('due_reminder_sent_for');
        });

        // Per-user email choices; null means "defaults" (see NotificationPreferences).
        Schema::table('users', function (Blueprint $table) {
            $table->json('notification_preferences')->nullable();
        });

        // Existing reporters and assignees follow their tasks.
        $now = now();
        foreach (['reporter_id', 'assignee_id'] as $column) {
            DB::table('tasks')->whereNotNull($column)->select('id', $column)->orderBy('id')
                ->chunk(500, function ($tasks) use ($column, $now) {
                    DB::table('task_watchers')->insertOrIgnore($tasks->map(fn ($t) => [
                        'task_id' => $t->id,
                        'user_id' => $t->{$column},
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all());
                });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notification_preferences');
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['due_reminder_sent_for', 'overdue_notice_sent_for']);
        });
        Schema::dropIfExists('task_watchers');
    }
};
