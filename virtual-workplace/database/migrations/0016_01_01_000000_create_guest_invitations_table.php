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
        Schema::create('guest_invitations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignUuid('invited_by')->constrained('users')->cascadeOnDelete();
            $table->string('guest_name');
            $table->string('guest_email')->nullable();
            $table->string('token', 64)->unique();
            $table->enum('status', ['pending', 'approved', 'denied', 'expired'])->default('pending');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index('organization_id');
            $table->index('token');
            $table->index(['room_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_invitations');
    }
};
