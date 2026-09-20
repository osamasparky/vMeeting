<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Purely additive: a nullable deleted_at column. Existing rows are
     * unaffected (all read as "not deleted"). See ADR-007 / Architecture
     * Audit §5, §10, §15 — Organization::delete() previously hard-deleted
     * the row with no recovery path, cascading through ~40 related tables.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
