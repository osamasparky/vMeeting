<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every column below has a real declared foreign key (confirmed via
 * PRAGMA foreign_key_list on the local SQLite dev DB), but SQLite — this
 * app's configured default connection, per .env / .env.example — does not
 * automatically index FK-constrained columns the way MySQL/InnoDB does.
 * Purely additive: indexes only, no data or column changes.
 * See Architecture Audit §11/§15.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->withIndexes([
            'organizations' => ['plan_id'],
            'subscriptions' => ['plan_id'],
            'subscription_requests' => ['user_id', 'plan_id'],
            'organization_members' => ['role_id'],
            'departments' => ['parent_department_id'],
            'teams' => ['department_id'],
            'user_profiles' => ['department_id', 'team_id'],
            'audit_logs' => ['target_id'],
            'channels' => ['room_id'],
            'messages' => ['sender_id'],
            'recordings' => ['user_id'],
            'projects' => ['owner_id', 'manager_id', 'department_id'],
            'active_timers' => ['project_id', 'task_id'],
            'task_comments' => ['user_id'],
            'task_attachments' => ['user_id'],
            'project_documents' => ['parent_document_id'],
            'project_goals' => ['owner_id'],
            'project_goal_targets' => ['goal_id'],
            'room_files' => ['uploaded_by_user_id'],
            'attendance_sessions' => ['room_id'],
            'workplace_notifications' => ['organization_id'],
            'office_templates' => ['plan_id'],
            'meetings' => ['room_id'],
            'project_files' => ['user_id'],
            'tasks' => ['phase_id', 'milestone_id', 'reporter_id', 'team_id', 'sprint_id'],
            'cms_sections' => ['media_asset_id'],
        ], add: true);
    }

    public function down(): void
    {
        $this->withIndexes([
            'organizations' => ['plan_id'],
            'subscriptions' => ['plan_id'],
            'subscription_requests' => ['user_id', 'plan_id'],
            'organization_members' => ['role_id'],
            'departments' => ['parent_department_id'],
            'teams' => ['department_id'],
            'user_profiles' => ['department_id', 'team_id'],
            'audit_logs' => ['target_id'],
            'channels' => ['room_id'],
            'messages' => ['sender_id'],
            'recordings' => ['user_id'],
            'projects' => ['owner_id', 'manager_id', 'department_id'],
            'active_timers' => ['project_id', 'task_id'],
            'task_comments' => ['user_id'],
            'task_attachments' => ['user_id'],
            'project_documents' => ['parent_document_id'],
            'project_goals' => ['owner_id'],
            'project_goal_targets' => ['goal_id'],
            'room_files' => ['uploaded_by_user_id'],
            'attendance_sessions' => ['room_id'],
            'workplace_notifications' => ['organization_id'],
            'office_templates' => ['plan_id'],
            'meetings' => ['room_id'],
            'project_files' => ['user_id'],
            'tasks' => ['phase_id', 'milestone_id', 'reporter_id', 'team_id', 'sprint_id'],
            'cms_sections' => ['media_asset_id'],
        ], add: false);
    }

    /**
     * @param  array<string, string[]>  $columnsByTable
     */
    private function withIndexes(array $columnsByTable, bool $add): void
    {
        foreach ($columnsByTable as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table, $columns, $add) {
                foreach ($columns as $column) {
                    if (! Schema::hasColumn($table, $column)) {
                        continue;
                    }
                    if ($add) {
                        $blueprint->index($column);
                    } else {
                        $blueprint->dropIndex([$column]);
                    }
                }
            });
        }
    }
};
