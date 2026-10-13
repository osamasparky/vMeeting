<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * MySQL (with explicit_defaults_for_timestamp off) gives the first NOT NULL
 * TIMESTAMP column of a table DEFAULT CURRENT_TIMESTAMP ON UPDATE
 * CURRENT_TIMESTAMP. Here that hit attendance_sessions.started_at,
 * time_entries.started_at, active_timers.started_at and
 * guest_invitations.expires_at: every update of the row — a heartbeat,
 * closing a session, approving an entry — reset the start to "now", so
 * office sessions measured ~0 seconds and approved time jumped to the
 * approval day.
 *
 * Drops ON UPDATE from every TIMESTAMP column except updated_at, keeping
 * the column's nullability and default. SQLite has no such behaviour.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $columns = DB::select("SELECT TABLE_NAME AS t, COLUMN_NAME AS c, IS_NULLABLE AS n, COLUMN_DEFAULT AS d
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND DATA_TYPE = 'timestamp'
              AND EXTRA LIKE '%on update%' AND COLUMN_NAME <> 'updated_at'");

        foreach ($columns as $col) {
            $nullable = $col->n === 'YES' ? 'NULL' : 'NOT NULL';
            $default = $col->d === null ? ($col->n === 'YES' ? 'DEFAULT NULL' : '') : 'DEFAULT CURRENT_TIMESTAMP';
            DB::statement("ALTER TABLE `{$col->t}` MODIFY `{$col->c}` TIMESTAMP {$nullable} {$default}");
        }
    }

    public function down(): void
    {
        // The auto-update was never intended; nothing to restore.
    }
};
