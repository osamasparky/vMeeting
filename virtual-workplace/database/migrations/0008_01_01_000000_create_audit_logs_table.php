<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('organization_id')->nullable()->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // created, updated, deleted, login, etc.
            $table->string('target_type')->nullable(); // Model class
            $table->string('target_id')->nullable(); // Model ID (string for UUID compat)
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'created_at']);
            $table->index('actor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
