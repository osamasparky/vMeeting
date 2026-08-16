<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_members', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles');
            $table->enum('status', ['invited', 'active', 'suspended'])->default('invited');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'user_id']);
            $table->index('organization_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_members');
    }
};
