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
        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans', 'max_guest_invitations')) {
                $table->integer('max_guest_invitations')->default(5)->after('room_limit');
            }
            if (!Schema::hasColumn('plans', 'max_room_capacity')) {
                $table->integer('max_room_capacity')->default(10)->after('max_guest_invitations');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['max_guest_invitations', 'max_room_capacity']);
        });
    }
};
