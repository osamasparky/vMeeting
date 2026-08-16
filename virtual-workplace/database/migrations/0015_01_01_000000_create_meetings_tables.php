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
        Schema::create('meetings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', ['instant', 'scheduled'])->default('instant');
            $table->enum('status', ['pending', 'active', 'ended'])->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('livekit_room_name')->nullable();
            $table->string('recording_url')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index(['organization_id', 'status']);
        });

        Schema::create('meeting_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['host', 'co-host', 'participant'])->default('participant');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->timestamps();

            $table->index(['meeting_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_participants');
        Schema::dropIfExists('meetings');
    }
};
