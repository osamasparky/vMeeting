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
        // 1. Add per-seat fields to plans table
        Schema::table('plans', function (Blueprint $table) {
            if (! Schema::hasColumn('plans', 'is_per_seat')) {
                $table->boolean('is_per_seat')->default(false)->after('price');
            }
            if (! Schema::hasColumn('plans', 'min_seats')) {
                $table->integer('min_seats')->default(2)->after('is_per_seat');
            }
            if (! Schema::hasColumn('plans', 'max_seats')) {
                $table->integer('max_seats')->nullable()->after('min_seats');
            }
        });

        // 2. Add seat and pricing columns to subscriptions table
        Schema::table('subscriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('subscriptions', 'seats')) {
                $table->integer('seats')->default(1)->after('plan_id');
            }
            if (! Schema::hasColumn('subscriptions', 'billing_cycle')) {
                $table->string('billing_cycle', 20)->default('monthly')->after('seats');
            }
            if (! Schema::hasColumn('subscriptions', 'price_per_seat')) {
                $table->decimal('price_per_seat', 10, 2)->nullable()->after('billing_cycle');
            }
        });

        // 3. Add seat and request_type columns to subscription_requests table
        Schema::table('subscription_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('subscription_requests', 'seats')) {
                $table->integer('seats')->default(1)->after('plan_id');
            }
            if (! Schema::hasColumn('subscription_requests', 'price_per_seat')) {
                $table->decimal('price_per_seat', 10, 2)->nullable()->after('seats');
            }
            if (! Schema::hasColumn('subscription_requests', 'request_type')) {
                $table->string('request_type', 50)->default('new_subscription')->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['is_per_seat', 'min_seats', 'max_seats']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['seats', 'billing_cycle', 'price_per_seat']);
        });

        Schema::table('subscription_requests', function (Blueprint $table) {
            $table->dropColumn(['seats', 'price_per_seat', 'request_type']);
        });
    }
};
