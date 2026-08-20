<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_members', function (Blueprint $table) {
            $table->decimal('cost_rate', 8, 2)->default(0.00)->after('status');
            $table->decimal('billing_rate', 8, 2)->default(0.00)->after('cost_rate');
            $table->decimal('weekly_capacity_hours', 5, 2)->default(40.00)->after('billing_rate');
        });
    }

    public function down(): void
    {
        Schema::table('organization_members', function (Blueprint $table) {
            $table->dropColumn(['cost_rate', 'billing_rate', 'weekly_capacity_hours']);
        });
    }
};
