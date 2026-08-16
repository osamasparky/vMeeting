<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo_url')->nullable();
            $table->string('timezone')->default('UTC');
            $table->enum('status', ['active', 'suspended', 'deactivated'])->default('active');
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('organization_settings', function (Blueprint $table) {
            $table->foreignUuid('organization_id')->primary()->constrained('organizations')->cascadeOnDelete();
            $table->json('branding')->nullable();
            $table->json('policies')->nullable();
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans');
            $table->enum('status', ['active', 'cancelled', 'past_due', 'trialing'])->default('active');
            $table->timestamp('current_period_end')->nullable();
            $table->timestamps();

            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('organization_settings');
        Schema::dropIfExists('organizations');
    }
};
