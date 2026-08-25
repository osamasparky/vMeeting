<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Alter meetings.status column from enum to varchar(32) so it accepts 'scheduled', 'cancelled', 'active', 'ended', 'pending'
        try {
            DB::statement("ALTER TABLE `meetings` MODIFY COLUMN `status` VARCHAR(32) NOT NULL DEFAULT 'active'");
        } catch (\Throwable $e) {
            // Fallback for sqlite / non-mysql
            if (Schema::hasColumn('meetings', 'status')) {
                Schema::table('meetings', function (Blueprint $table) {
                    $table->string('status', 32)->default('active')->change();
                });
            }
        }

        // 2. Create project_files table
        if (!Schema::hasTable('project_files')) {
            Schema::create('project_files', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
                $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('file_name');
                $table->string('file_path');
                $table->string('file_url')->nullable();
                $table->unsignedBigInteger('file_size')->default(0);
                $table->string('mime_type', 128)->nullable();
                $table->timestamps();

                $table->index(['project_id', 'created_at']);
                $table->index('organization_id');
            });
        }

        // 3. Add approval columns to tasks table
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'approval_status')) {
                $table->string('approval_status', 32)->default('none')->after('status');
            }
            if (!Schema::hasColumn('tasks', 'approved_by')) {
                $table->foreignUuid('approved_by')->nullable()->after('approval_status')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('tasks', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('tasks', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('approved_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'approved_by')) {
                $table->dropForeign(['approved_by']);
            }
            $table->dropColumn([
                'approval_status',
                'approved_by',
                'approved_at',
                'rejection_reason',
            ]);
        });

        Schema::dropIfExists('project_files');
    }
};
