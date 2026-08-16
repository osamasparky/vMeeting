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
        Schema::create('channels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->enum('type', ['dm', 'room', 'broadcast'])->default('room');
            $table->string('name')->nullable();
            $table->foreignUuid('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->timestamps();

            $table->index('organization_id');
            $table->index(['organization_id', 'type']);
        });

        Schema::create('channel_members', function (Blueprint $table) {
            $table->foreignUuid('channel_id')->constrained('channels')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->primary(['channel_id', 'user_id']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('channel_id')->constrained('channels')->cascadeOnDelete();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->json('attachments')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index(['channel_id', 'created_at']);
        });

        Schema::create('message_reads', function (Blueprint $table) {
            $table->foreignUuid('message_id')->constrained('messages')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('read_at')->useCurrent();

            $table->primary(['message_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_reads');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('channel_members');
        Schema::dropIfExists('channels');
    }
};
