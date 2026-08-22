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
        // 1. Add max_offices to plans table if not exists
        if (!Schema::hasColumn('plans', 'max_offices')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->integer('max_offices')->default(1)->after('seat_limit');
            });
        }

        // 2. Add extra branch details to floors table
        Schema::table('floors', function (Blueprint $table) {
            if (!Schema::hasColumn('floors', 'city_location')) {
                $table->string('city_location')->nullable()->after('name');
            }
            if (!Schema::hasColumn('floors', 'description')) {
                $table->text('description')->nullable()->after('city_location');
            }
            if (!Schema::hasColumn('floors', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('description');
            }
        });

        // 3. Member Office Access (which branches a user can enter)
        if (!Schema::hasTable('member_office_access')) {
            Schema::create('member_office_access', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_member_id')->constrained('organization_members')->onDelete('cascade');
                $table->uuid('floor_id');
                $table->foreign('floor_id')->references('id')->on('floors')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['organization_member_id', 'floor_id'], 'mem_office_unique');
            });
        }

        // 4. Member Room Access (which specific rooms a user can enter)
        if (!Schema::hasTable('member_room_access')) {
            Schema::create('member_room_access', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_member_id')->constrained('organization_members')->onDelete('cascade');
                $table->uuid('room_id');
                $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['organization_member_id', 'room_id'], 'mem_room_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_room_access');
        Schema::dropIfExists('member_office_access');

        Schema::table('floors', function (Blueprint $table) {
            $table->dropColumn(['city_location', 'description', 'is_default']);
        });

        if (Schema::hasColumn('plans', 'max_offices')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->dropColumn('max_offices');
            });
        }
    }
};
