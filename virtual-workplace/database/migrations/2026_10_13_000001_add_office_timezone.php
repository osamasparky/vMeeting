<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Working hours are counted in the office's local time, not UTC, so every
 * office (floor) carries its own timezone, and every attendance session
 * records which office it was in — open-space sessions have no room to tell.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('floors', function (Blueprint $table) {
            $table->string('timezone', 64)->nullable()->after('city_location');
        });

        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->uuid('floor_id')->nullable()->after('room_id');
            $table->index(['floor_id', 'started_at']);
        });

        // Existing offices start on their organization's timezone.
        $valid = timezone_identifiers_list();
        foreach (DB::table('organizations')->get(['id', 'timezone']) as $org) {
            DB::table('floors')->where('organization_id', $org->id)->whereNull('timezone')
                ->update(['timezone' => in_array($org->timezone, $valid, true) ? $org->timezone : 'UTC']);
        }
        DB::table('floors')->whereNull('timezone')->update(['timezone' => 'UTC']);

        // Existing room sessions belong to the office their room is in.
        $roomFloors = DB::table('rooms')->join('maps', 'maps.id', '=', 'rooms.map_id')
            ->whereNotNull('maps.floor_id')->pluck('maps.floor_id', 'rooms.id');
        foreach ($roomFloors as $roomId => $floorId) {
            DB::table('attendance_sessions')->where('room_id', $roomId)->whereNull('floor_id')->update(['floor_id' => $floorId]);
        }
    }

    public function down(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropIndex(['floor_id', 'started_at']);
            $table->dropColumn('floor_id');
        });

        Schema::table('floors', function (Blueprint $table) {
            $table->dropColumn('timezone');
        });
    }
};
