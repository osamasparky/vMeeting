<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Official holidays. A holiday belongs to one office (floor_id) or to every
 * office of the organization (floor_id null), and may span several days.
 * Holidays are not working days: no one is expected, no one is absent.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('office_holidays', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignUuid('floor_id')->nullable()->constrained('floors')->cascadeOnDelete();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->index(['organization_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_holidays');
    }
};
