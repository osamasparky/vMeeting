<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A project can take requests from outside the team through a public
        // link (shareable with clients and office guests).
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('intake_enabled')->default(false);
            $table->string('intake_token', 64)->nullable()->unique();
        });

        Schema::create('intake_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('requester_name', 120);
            $table->string('requester_email', 190)->nullable();
            $table->string('status', 20)->default('pending'); // pending, accepted, declined, snoozed, duplicate
            $table->date('snoozed_until')->nullable();
            $table->foreignUuid('task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->foreignUuid('duplicate_of_task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->text('decline_reason')->nullable();
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'status']);
            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intake_requests');
        Schema::table('projects', function (Blueprint $table) {
            $table->dropUnique(['intake_token']);
            $table->dropColumn(['intake_enabled', 'intake_token']);
        });
    }
};
