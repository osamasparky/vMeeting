<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('recurrence_rule', 32)->nullable()->after('metadata'); // daily, weekly, biweekly, monthly, quarterly
            $table->integer('recurrence_interval')->default(1)->after('recurrence_rule');
            $table->date('recurrence_ends_at')->nullable()->after('recurrence_interval');
            $table->timestamp('last_recurred_at')->nullable()->after('recurrence_ends_at');
            
            $table->index(['recurrence_rule', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['recurrence_rule', 'status']);
            $table->dropColumn([
                'recurrence_rule',
                'recurrence_interval',
                'recurrence_ends_at',
                'last_recurred_at',
            ]);
        });
    }
};
